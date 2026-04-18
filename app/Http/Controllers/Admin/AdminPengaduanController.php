<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use App\Models\User;
use App\Models\Log;
use App\Models\Notification;
use App\Services\DuplicateLifecycleService;
use App\Services\DispatchQueueService;
use App\Services\PengaduanDuplicateDetector;
use App\Services\SchoolMapService;
use App\Services\SlaService;
use App\Services\TicketStatusTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AdminPengaduanController extends Controller
{
    public function __construct(
        private readonly PengaduanDuplicateDetector $duplicateDetector,
        private readonly DuplicateLifecycleService $duplicateLifecycleService,
        private readonly TicketStatusTransitionService $statusTransitionService,
        private readonly SlaService $slaService,
        private readonly DispatchQueueService $dispatchQueueService,
        private readonly SchoolMapService $schoolMapService
    )
    {
        $this->middleware('throttle:40,1')->only(['markDuplicate', 'bulkStatus', 'bulkAssign']);
    }

    /**
     * Display a listing of all pengaduans
     */
    public function index(Request $request)
    {
        $query = Pengaduan::with(['kategori', 'gedung', 'ruangan.gedung', 'user', 'assignedTo', 'duplicateOf:id,kode_pengaduan']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by prioritas
        if ($request->filled('prioritas')) {
            $query->where('prioritas', $request->prioritas);
        }

        // Filter by kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Filter by gedung
        if ($request->filled('gedung_id')) {
            $query->where('gedung_id', $request->gedung_id);
        }

        // Filter by teknisi
        if ($request->filled('teknisi_id')) {
            $query->where('teknisi_id', $request->teknisi_id);
        }

        // Filter by manual duplicate marker
        if ($request->get('duplicate_state') === 'marked') {
            $query->whereNotNull('duplicate_of_id');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_pengaduan', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        $duplicateState = $request->get('duplicate_state');
        if (in_array($duplicateState, ['potential', 'normal'], true)) {
            $items = $query->get();
            $items->each(function (Pengaduan $pengaduan) {
                $pengaduan->setAttribute('has_potential_duplicate', $this->duplicateDetector->hasPotentialDuplicate($pengaduan));
            });

            $items = $items->filter(function (Pengaduan $pengaduan) use ($duplicateState) {
                if ($duplicateState === 'potential') {
                    return $pengaduan->has_potential_duplicate;
                }

                return !$pengaduan->has_potential_duplicate && !$pengaduan->is_marked_duplicate;
            })->values();

            $pengaduans = $this->paginateCollection($items, 15, $request);
        } else {
            $pengaduans = $query->paginate(15);
            $pengaduans->getCollection()->each(function (Pengaduan $pengaduan) {
                $pengaduan->setAttribute('has_potential_duplicate', $this->duplicateDetector->hasPotentialDuplicate($pengaduan));
            });
        }

        $kategoris = \App\Models\Kategori::where('is_active', true)->get();
        $gedungs = \App\Models\Gedung::where('is_active', true)->get();
        $teknisis = User::where('role', 'teknisi')->where('is_active', true)->get();

        return view('admin.pengaduan.index', compact('pengaduans', 'kategoris', 'gedungs', 'teknisis'));
    }

    /**
     * Display the specified pengaduan
     */
    public function show(Pengaduan $pengaduan)
    {
        $relations = [
            'kategori',
            'gedung',
            'ruangan.gedung',
            'user',
            'assignedTo',
            'photos',
            'feedbackDetail',
            'duplicateOf:id,kode_pengaduan',
            'duplicateMarker:id,name',
            'autoClosedFromMaster:id,kode_pengaduan',
            'reopenRequestedBy:id,name',
            'priorityAdjustedBy:id,name',
            'schoolMap',
            'schoolMapLayer.gedung',
        ];

        if (Schema::hasTable('pengaduan_schedules')) {
            $relations[] = 'schedules.changer:id,name';
        }

        $pengaduan->load($relations);

        if (!Schema::hasTable('pengaduan_schedules')) {
            $pengaduan->setRelation('schedules', collect());
        }

        $teknisis = User::where('role', 'teknisi')
            ->where('is_active', true)
            ->get();

        $duplicateCandidates = $this->duplicateDetector
            ->findActiveCandidatesForPengaduan($pengaduan, 5)
            ->loadMissing(['user:id,name', 'gedung:id,nama', 'ruangan:id,gedung_id,nama,lantai', 'ruangan.gedung:id,nama']);

        // Get logs for this pengaduan
        $logs = Log::where('pengaduan_id', $pengaduan->id)
            ->with('user')
            ->latest()
            ->get();

        $mapPayload = $this->schoolMapService->buildPengaduanMapPayload($pengaduan);

        return view('admin.pengaduan.show', compact('pengaduan', 'teknisis', 'logs', 'duplicateCandidates', 'mapPayload'));
    }

    /**
     * Update status of pengaduan
     */
    public function updateStatus(Request $request, Pengaduan $pengaduan)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,diverifikasi,diproses,selesai,ditolak',
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $pengaduan->status;
            $payload = $this->statusTransitionService->buildUpdatePayload($pengaduan, $validated['status'], [
                'catatan_admin' => $validated['catatan_admin'] ?? $pengaduan->catatan_admin,
            ]);
            $pengaduan->update($payload);

            Log::createLog(
                $pengaduan->id,
                Auth::id(),
                'status_change',
                "Status diubah dari {$oldStatus} ke {$validated['status']}"
            );

            Notification::send(
                $pengaduan->user_id,
                Notification::JENIS_STATUS_CHANGED,
                'Status Pengaduan Diperbarui',
                "Pengaduan #{$pengaduan->kode_pengaduan} telah diperbarui ke status: " . ucfirst($validated['status']),
                route('pengaduan.show', $pengaduan->kode_pengaduan)
            );

            $autoClosedCount = 0;
            if ($validated['status'] === Pengaduan::STATUS_SELESAI) {
                $autoClosedCount = $this->duplicateLifecycleService->handleMasterCompleted(
                    $pengaduan->fresh(),
                    Auth::id(),
                    'admin_update_status'
                );
            }

            if ($pengaduan->teknisi_id) {
                $this->dispatchQueueService->recomputeForTeknisi((int) $pengaduan->teknisi_id);
            }

            DB::commit();

            $message = 'Status pengaduan berhasil diperbarui';
            if ($autoClosedCount > 0) {
                $message .= " dan {$autoClosedCount} tiket duplikat ditutup otomatis";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Assign teknisi to pengaduan
     */
    public function assign(Request $request, Pengaduan $pengaduan)
    {
        $validated = $request->validate([
            'teknisi_id' => 'required|exists:users,id',
        ]);

        $teknisi = User::findOrFail($validated['teknisi_id']);

        // Check if teknisi
        if ($teknisi->role !== 'teknisi') {
            return back()->with('error', 'User yang dipilih bukan teknisi');
        }

        $payload = [
            'teknisi_id' => $validated['teknisi_id'],
            'assigned_at' => now(),
        ];

        if ($pengaduan->status === Pengaduan::STATUS_PENDING) {
            $payload = $this->statusTransitionService->buildUpdatePayload(
                $pengaduan,
                Pengaduan::STATUS_DIVERIFIKASI,
                $payload
            );
        }

        $pengaduan->update($payload);

        // Log activity
        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'assign',
            "Ditugaskan ke teknisi: {$teknisi->name}"
        );

        // Notify teknisi
        Notification::send(
            $teknisi->id,
            Notification::JENIS_ASSIGNED,
            'Pengaduan Baru Ditugaskan',
            "Anda ditugaskan untuk menangani pengaduan #{$pengaduan->kode_pengaduan}: {$pengaduan->judul}",
            route('teknisi.pengaduan.show', $pengaduan->kode_pengaduan)
        );

        // Notify pelapor
        Notification::send(
            $pengaduan->user_id,
            Notification::JENIS_ASSIGNED,
            'Teknisi Ditugaskan',
            "Pengaduan #{$pengaduan->kode_pengaduan} telah ditugaskan kepada teknisi {$teknisi->name}",
            route('pengaduan.show', $pengaduan->kode_pengaduan)
        );

        $this->dispatchQueueService->recomputeForTeknisi((int) $validated['teknisi_id']);

        return back()->with('success', "Pengaduan berhasil ditugaskan ke {$teknisi->name}");
    }

    /**
     * Reject pengaduan
     */
    public function reject(Request $request, Pengaduan $pengaduan)
    {
        $validated = $request->validate([
            'alasan_tolak' => 'required|string|max:1000',
        ], [
            'alasan_tolak.required' => 'Alasan penolakan harus diisi',
        ]);

        $payload = $this->statusTransitionService->buildUpdatePayload($pengaduan, Pengaduan::STATUS_DITOLAK, [
            'alasan_tolak' => $validated['alasan_tolak'],
        ]);
        $pengaduan->update($payload);

        // Log activity
        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'reject',
            "Pengaduan ditolak. Alasan: {$validated['alasan_tolak']}"
        );

        // Notify user
        Notification::send(
            $pengaduan->user_id,
            Notification::JENIS_STATUS_CHANGED,
            'Pengaduan Ditolak',
            "Maaf, pengaduan #{$pengaduan->kode_pengaduan} tidak dapat diproses. Alasan: {$validated['alasan_tolak']}",
            route('pengaduan.show', $pengaduan->kode_pengaduan)
        );

        if ($pengaduan->teknisi_id) {
            $this->dispatchQueueService->recomputeForTeknisi((int) $pengaduan->teknisi_id);
        }

        return back()->with('success', 'Pengaduan berhasil ditolak');
    }

    public function updatePrioritas(Request $request, Pengaduan $pengaduan)
    {
        if (in_array($pengaduan->status, [Pengaduan::STATUS_SELESAI, Pengaduan::STATUS_DITOLAK], true)) {
            return back()->with('error', 'Prioritas tidak dapat diubah pada tiket final.');
        }

        $validated = $request->validate([
            'prioritas' => 'required|in:rendah,sedang,tinggi,urgent',
            'prioritas_adjust_reason' => 'required|string|min:10|max:1000',
        ]);

        $oldPrioritas = $pengaduan->prioritas;
        $pengaduan->update([
            'prioritas' => $validated['prioritas'],
            'needs_priority_review' => false,
            'prioritas_adjusted_by' => Auth::id(),
            'prioritas_adjust_reason' => $validated['prioritas_adjust_reason'],
            'prioritas_adjusted_at' => now(),
            'sla_due_at' => $this->slaService->calculateDueAt($validated['prioritas'], $pengaduan->created_at),
        ]);

        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'priority_adjust',
            "Prioritas diubah dari {$oldPrioritas} ke {$validated['prioritas']}. Alasan: {$validated['prioritas_adjust_reason']}"
        );

        Notification::send(
            $pengaduan->user_id,
            Notification::JENIS_PRIORITY_ADJUSTED,
            'Urgensi Pengaduan Disesuaikan',
            "Admin menyesuaikan urgensi tiket #{$pengaduan->kode_pengaduan} menjadi {$validated['prioritas']}.",
            route('pengaduan.show', $pengaduan->kode_pengaduan)
        );

        if ($pengaduan->teknisi_id) {
            Notification::send(
                $pengaduan->teknisi_id,
                Notification::JENIS_PRIORITY_ADJUSTED,
                'Prioritas Tiket Diperbarui',
                "Prioritas tiket #{$pengaduan->kode_pengaduan} kini {$validated['prioritas']}.",
                route('teknisi.pengaduan.show', $pengaduan->kode_pengaduan)
            );
            $this->dispatchQueueService->recomputeForTeknisi((int) $pengaduan->teknisi_id);
        }

        return back()->with('success', 'Prioritas tiket berhasil diperbarui.');
    }

    public function forcePriority(Request $request, Pengaduan $pengaduan)
    {
        $validated = $request->validate([
            'prioritas' => 'required|in:rendah,sedang,tinggi,urgent',
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $oldPriority = $pengaduan->prioritas;
        $pengaduan->update([
            'prioritas' => $validated['prioritas'],
            'prioritas_adjusted_by' => Auth::id(),
            'prioritas_adjust_reason' => $validated['reason'],
            'prioritas_adjusted_at' => now(),
            'needs_priority_review' => false,
            'sla_due_at' => $this->slaService->calculateDueAt($validated['prioritas'], $pengaduan->created_at),
        ]);

        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'force_priority',
            "Prioritas dipaksa dari {$oldPriority} ke {$validated['prioritas']}. Alasan: {$validated['reason']}"
        );

        if ($pengaduan->teknisi_id) {
            $this->dispatchQueueService->recomputeForTeknisi((int) $pengaduan->teknisi_id);
        }

        return back()->with('success', 'Prioritas dipaksa dan antrian dihitung ulang.');
    }

    public function overloadBoard()
    {
        $summary = $this->dispatchQueueService->overloadSummary();

        $query = Pengaduan::query()
            ->with(['user:id,name', 'assignedTo:id,name', 'gedung:id,nama'])
            ->whereIn('status', [Pengaduan::STATUS_DIVERIFIKASI, Pengaduan::STATUS_DIPROSES]);

        if (
            Schema::hasColumn('pengaduans', 'triage_score')
            && Schema::hasColumn('pengaduans', 'queue_rank')
        ) {
            $query->orderByDesc('triage_score')->orderBy('queue_rank');
        } else {
            $query->latest();
        }

        $tickets = $query->limit(50)->get();

        return view('admin.pengaduan.overload-board', compact('summary', 'tickets'));
    }

    /**
     * Bulk update status
     */
    public function bulkStatus(Request $request)
    {
        $ids = $this->parseBulkIds($request->input('ids'));

        $validated = $request->validate([
            'status' => 'required|in:pending,diverifikasi,diproses,selesai,ditolak',
            'confirmation_text' => 'required|in:TERAPKAN',
        ], [
            'confirmation_text.in' => 'Konfirmasi aksi massal tidak valid.',
        ]);

        if (count($ids) === 0) {
            return back()->with('error', 'Pilih minimal satu tiket untuk aksi massal.');
        }

        DB::beginTransaction();
        try {
            $pengaduans = Pengaduan::whereIn('id', $ids)->get();
            $updatedCount = 0;

            foreach ($pengaduans as $pengaduan) {
                $oldStatus = $pengaduan->status;
                $payload = $this->statusTransitionService->buildUpdatePayload($pengaduan, $validated['status']);
                $pengaduan->update($payload);
                $updatedCount++;

                // Log each change
                Log::createLog(
                    $pengaduan->id,
                    Auth::id(),
                    'bulk_status_change',
                    "Status diubah dari {$oldStatus} ke {$validated['status']}"
                );

                // Notify users
                Notification::send(
                    $pengaduan->user_id,
                    Notification::JENIS_STATUS_CHANGED,
                    'Status Pengaduan Diperbarui',
                    "Pengaduan #{$pengaduan->kode_pengaduan} telah diperbarui ke status: " . ucfirst($validated['status']),
                    route('pengaduan.show', $pengaduan->kode_pengaduan)
                );

                if ($validated['status'] === Pengaduan::STATUS_SELESAI) {
                    $this->duplicateLifecycleService->handleMasterCompleted(
                        $pengaduan->fresh(),
                        Auth::id(),
                        'admin_bulk_status'
                    );
                }

                if ($pengaduan->teknisi_id) {
                    $this->dispatchQueueService->recomputeForTeknisi((int) $pengaduan->teknisi_id);
                }
            }

            DB::commit();

            return back()->with('success', $updatedCount . ' pengaduan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assign teknisi
     */
    public function bulkAssign(Request $request)
    {
        $ids = $this->parseBulkIds($request->input('ids'));

        $validated = $request->validate([
            'teknisi_id' => 'required|exists:users,id',
            'confirmation_text' => 'required|in:TERAPKAN',
        ], [
            'confirmation_text.in' => 'Konfirmasi aksi massal tidak valid.',
        ]);

        if (count($ids) === 0) {
            return back()->with('error', 'Pilih minimal satu tiket untuk aksi massal.');
        }

        $teknisi = User::findOrFail($validated['teknisi_id']);

        if ($teknisi->role !== 'teknisi') {
            return back()->with('error', 'User yang dipilih bukan teknisi');
        }

        DB::beginTransaction();
        try {
            $pengaduans = Pengaduan::whereIn('id', $ids)->get();
            $updatedCount = 0;

            foreach ($pengaduans as $pengaduan) {
                $payload = [
                    'teknisi_id' => $validated['teknisi_id'],
                    'assigned_at' => now(),
                ];

                if ($pengaduan->status === Pengaduan::STATUS_PENDING) {
                    $payload = $this->statusTransitionService->buildUpdatePayload(
                        $pengaduan,
                        Pengaduan::STATUS_DIVERIFIKASI,
                        $payload
                    );
                }

                $pengaduan->update($payload);
                $updatedCount++;

                // Log
                Log::createLog(
                    $pengaduan->id,
                    Auth::id(),
                    'bulk_assign',
                    "Ditugaskan ke teknisi: {$teknisi->name}"
                );
            }

            // Notify teknisi once
            Notification::send(
                $teknisi->id,
                Notification::JENIS_ASSIGNED,
                'Pengaduan Baru Ditugaskan',
                "Anda ditugaskan untuk menangani {$updatedCount} pengaduan baru",
                route('teknisi.pengaduan.index')
            );

            $this->dispatchQueueService->recomputeForTeknisi((int) $validated['teknisi_id']);

            DB::commit();

            return back()->with('success', "{$updatedCount} pengaduan berhasil ditugaskan ke {$teknisi->name}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mark a ticket as duplicate of another ticket.
     */
    public function markDuplicate(Request $request, Pengaduan $pengaduan)
    {
        $validated = $request->validate([
            'master_pengaduan_id' => 'required|exists:pengaduans,id',
            'duplicate_note' => 'nullable|string|max:1000',
        ]);

        if ((int) $validated['master_pengaduan_id'] === (int) $pengaduan->id) {
            return back()->with('error', 'Tiket tidak bisa ditandai sebagai duplikat dirinya sendiri.');
        }

        $master = Pengaduan::findOrFail($validated['master_pengaduan_id']);
        $rootMaster = $this->resolveDuplicateRoot($master);

        if ($rootMaster->id === $pengaduan->id) {
            return back()->with('error', 'Aksi ditolak karena menyebabkan siklus duplikat.');
        }

        if ($pengaduan->status === Pengaduan::STATUS_SELESAI && $pengaduan->feedbackDetail) {
            session()->flash('warning', 'Tiket ini sudah selesai dan memiliki feedback. Penandaan duplikat tetap disimpan, tanpa auto-close tambahan.');
        }

        $pengaduan->update([
            'duplicate_of_id' => $rootMaster->id,
            'duplicate_marked_by' => Auth::id(),
            'duplicate_marked_at' => now(),
            'duplicate_note' => $validated['duplicate_note'] ?? null,
        ]);

        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'mark_duplicate',
            "Ditandai sebagai duplikat dari #{$rootMaster->kode_pengaduan}",
            null,
            ['duplicate_of_id' => $rootMaster->id]
        );

        return back()->with('success', "Tiket berhasil ditandai sebagai duplikat dari #{$rootMaster->kode_pengaduan}.");
    }

    /**
     * Remove duplicate marker from a ticket.
     */
    public function unmarkDuplicate(Pengaduan $pengaduan)
    {
        if (!$pengaduan->duplicate_of_id) {
            return back()->with('error', 'Tiket ini belum ditandai sebagai duplikat.');
        }

        $previousMasterCode = $pengaduan->duplicateOf?->kode_pengaduan;

        $pengaduan->update([
            'duplicate_of_id' => null,
            'duplicate_marked_by' => null,
            'duplicate_marked_at' => null,
            'duplicate_note' => null,
        ]);

        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'unmark_duplicate',
            'Penandaan duplikat dibatalkan' . ($previousMasterCode ? " dari #{$previousMasterCode}" : '')
        );

        return back()->with('success', 'Penandaan duplikat berhasil dibatalkan.');
    }

    /**
     * Approve reopen request and move ticket back to diverifikasi.
     */
    public function approveReopen(Pengaduan $pengaduan)
    {
        if (!$pengaduan->has_reopen_request) {
            return back()->with('error', 'Tidak ada permintaan buka ulang untuk tiket ini.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $pengaduan->status;
            $payload = $this->statusTransitionService->buildUpdatePayload($pengaduan, Pengaduan::STATUS_DIVERIFIKASI, [
                'transition_context' => 'reopen_approved',
                'reopen_count' => (int) $pengaduan->reopen_count + 1,
                'reopen_requested_at' => null,
                'reopen_requested_by' => null,
                'reopen_reason' => null,
                'catatan_admin' => trim(($pengaduan->catatan_admin ? $pengaduan->catatan_admin."\n" : '').'Buka ulang disetujui admin pada '.now()->format('d/m/Y H:i')),
            ]);
            unset($payload['transition_context']);

            $pengaduan->update($payload);

            Log::createLog(
                $pengaduan->id,
                Auth::id(),
                Log::ACTION_REOPENED,
                "Permintaan buka ulang disetujui. Status {$oldStatus} -> diverifikasi"
            );

            Notification::send(
                $pengaduan->user_id,
                Notification::JENIS_STATUS_CHANGED,
                'Permintaan Buka Ulang Disetujui',
                "Pengaduan #{$pengaduan->kode_pengaduan} dibuka ulang dan akan diproses kembali.",
                route('pengaduan.show', $pengaduan->kode_pengaduan)
            );

            DB::commit();

            return back()->with('success', 'Permintaan buka ulang berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Export pengaduans
     */
    public function export(Request $request)
    {
        $query = Pengaduan::with(['kategori', 'gedung', 'ruangan.gedung', 'user', 'teknisi']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('prioritas')) {
            $query->where('prioritas', $request->prioritas);
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $pengaduans = $query->get();

        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pengaduan-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($pengaduans) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Kode',
                'Judul',
                'Kategori',
                'Lokasi',
                'Pelapor',
                'Urgensi',
                'Status',
                'Teknisi',
                'Tanggal Dibuat',
            ]);

            // Data rows
            foreach ($pengaduans as $p) {
                fputcsv($file, [
                    $p->kode_pengaduan,
                    $p->judul,
                    $p->kategori->nama ?? '-',
                    ($p->gedung->nama ?? '') . ' - ' . ($p->lokasi_detail ?? ''),
                    $p->user->name ?? '-',
                    ucfirst($p->prioritas),
                    ucfirst($p->status),
                    $p->assignedTo->name ?? '-',
                    $p->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function resolveDuplicateRoot(Pengaduan $pengaduan): Pengaduan
    {
        $current = $pengaduan;
        $visited = [];

        while ($current->duplicate_of_id) {
            if (isset($visited[$current->id])) {
                break;
            }

            $visited[$current->id] = true;
            $next = Pengaduan::find($current->duplicate_of_id);
            if (!$next) {
                break;
            }
            $current = $next;
        }

        return $current;
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * @return array<int, int>
     */
    private function parseBulkIds(mixed $rawIds): array
    {
        if (is_string($rawIds)) {
            $decoded = json_decode($rawIds, true);
            $rawIds = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($rawIds)) {
            throw ValidationException::withMessages([
                'ids' => 'Format ID untuk aksi massal tidak valid.',
            ]);
        }

        $ids = collect($rawIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (count($ids) > 100) {
            throw ValidationException::withMessages([
                'ids' => 'Maksimal 100 tiket per aksi massal.',
            ]);
        }

        return $ids;
    }
}
