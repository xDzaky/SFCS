<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use App\Models\PengaduanSchedule;
use App\Models\Log;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use App\Services\DuplicateLifecycleService;
use App\Services\DispatchQueueService;
use App\Services\SchoolMapService;
use App\Services\TicketStatusTransitionService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeknisiPengaduanController extends Controller
{
    public function __construct(
        private readonly DuplicateLifecycleService $duplicateLifecycleService,
        private readonly TicketStatusTransitionService $statusTransitionService,
        private readonly DispatchQueueService $dispatchQueueService,
        private readonly SchoolMapService $schoolMapService
    )
    {
    }

    /**
     * Display list of assigned pengaduans
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Pengaduan::with(['kategori', 'gedung', 'ruangan.gedung', 'user', 'photos'])
            ->where('teknisi_id', $user->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: show non-completed
            $query->whereNotIn('status', ['selesai', 'ditolak']);
        }

        // Filter by prioritas
        if ($request->filled('prioritas')) {
            $query->where('prioritas', $request->prioritas);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_pengaduan', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%");
            });
        }

        // Sort by urgensi (high first) then by date
        $pengaduans = $query
            ->orderByRaw("FIELD(prioritas, 'urgent', 'tinggi', 'sedang', 'rendah')")
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('teknisi.pengaduan.index', compact('pengaduans'));
    }

    /**
     * Display the specified pengaduan
     */
    public function show(Pengaduan $pengaduan)
    {
        $user = Auth::user();

        // Check if assigned to this teknisi
        if ($pengaduan->teknisi_id != $user->id && !$user->isAdmin()) {
            abort(403, 'Pengaduan ini tidak ditugaskan kepada Anda');
        }

        $relations = [
            'kategori',
            'subKategori',
            'gedung',
            'ruangan.gedung',
            'user',
            'photos',
            'feedbackDetail',
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

        // Get logs for this pengaduan
        $logs = Log::where('pengaduan_id', $pengaduan->id)
            ->with('user')
            ->latest()
            ->get();

        $mapPayload = $this->schoolMapService->buildPengaduanMapPayload($pengaduan);

        return view('teknisi.pengaduan.show', compact('pengaduan', 'logs', 'mapPayload'));
    }

    /**
     * Update status to "diproses"
     */
    public function updateStatus(Request $request, Pengaduan $pengaduan)
    {
        $user = Auth::user();

        // Check if assigned to this teknisi
        if ($pengaduan->teknisi_id != $user->id && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:diproses',
            'catatan_teknisi' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $pengaduan->status;

        $payload = $this->statusTransitionService->buildUpdatePayload($pengaduan, $validated['status'], [
            'catatan_teknisi' => $validated['catatan_teknisi'] ?? $pengaduan->catatan_teknisi,
        ]);
        $pengaduan->update($payload);

        // Log activity
        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'teknisi_update',
            "Status diubah dari {$oldStatus} ke {$validated['status']} oleh teknisi"
        );

        // Notify user
        Notification::send(
            $pengaduan->user_id,
            Notification::JENIS_STATUS_CHANGED,
            'Pengaduan Sedang Diproses',
            "Pengaduan #{$pengaduan->kode_pengaduan} sedang dikerjakan oleh teknisi {$user->name}",
            route('pengaduan.show', $pengaduan->kode_pengaduan)
        );

        $this->dispatchQueueService->recomputeForTeknisi((int) $pengaduan->teknisi_id);

        return back()->with('success', 'Status berhasil diperbarui');
    }

    /**
     * Mark pengaduan as complete
     */
    public function complete(Request $request, Pengaduan $pengaduan)
    {
        $user = Auth::user();

        // Check if assigned to this teknisi
        if ($pengaduan->teknisi_id != $user->id && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'catatan_teknisi' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $payload = $this->statusTransitionService->buildUpdatePayload($pengaduan, Pengaduan::STATUS_SELESAI, [
                'catatan_teknisi' => $validated['catatan_teknisi'],
            ]);
            $pengaduan->update($payload);

            $catatanLog = $validated['catatan_teknisi'] ? " Catatan: {$validated['catatan_teknisi']}" : '';
            Log::createLog(
                $pengaduan->id,
                Auth::id(),
                'teknisi_complete',
                "Pengaduan ditandai selesai oleh teknisi.{$catatanLog}"
            );

            Notification::send(
                $pengaduan->user_id,
                Notification::JENIS_STATUS_CHANGED,
                'Pengaduan Selesai',
                "Pengaduan #{$pengaduan->kode_pengaduan} telah selesai dikerjakan. Silakan berikan feedback Anda.",
                route('pengaduan.show', $pengaduan->kode_pengaduan)
            );

            Notification::send(
                $pengaduan->user_id,
                Notification::JENIS_FEEDBACK_REMINDER,
                'Berikan Feedback',
                "Jangan lupa berikan feedback untuk pengaduan #{$pengaduan->kode_pengaduan} yang sudah selesai.",
                route('pengaduan.show', $pengaduan->kode_pengaduan)
            );

            $autoClosedCount = $this->duplicateLifecycleService->handleMasterCompleted(
                $pengaduan->fresh(),
                Auth::id(),
                'teknisi_complete'
            );

            $this->dispatchQueueService->recomputeForTeknisi((int) $pengaduan->teknisi_id);

            DB::commit();

            $message = 'Pengaduan berhasil ditandai selesai';
            if ($autoClosedCount > 0) {
                $message .= " dan {$autoClosedCount} tiket duplikat ditutup otomatis";
            }

            return redirect()->route('teknisi.pengaduan.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reschedule(Request $request, Pengaduan $pengaduan)
    {
        if (!$this->isRescheduleSchemaReady()) {
            return back()->with('error', 'Fitur reschedule belum aktif karena migrasi belum lengkap.');
        }

        $user = Auth::user();
        if ((int) $pengaduan->teknisi_id !== (int) $user->id && !$user->isAdmin()) {
            abort(403);
        }

        if (!in_array($pengaduan->status, [Pengaduan::STATUS_DIVERIFIKASI, Pengaduan::STATUS_DIPROSES], true)) {
            return back()->with('error', 'Reschedule hanya untuk tiket aktif.');
        }

        $validated = $request->validate([
            'planned_start_at' => 'required|date|after:now',
            'planned_end_at' => 'required|date|after:planned_start_at',
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $maxHours = match ($pengaduan->prioritas) {
            Pengaduan::PRIORITAS_URGENT => (int) Setting::getValue('urgent_max_reschedule_hours', 24),
            Pengaduan::PRIORITAS_TINGGI => (int) Setting::getValue('high_max_reschedule_hours', 48),
            default => 168,
        };

        $newStart = Carbon::parse($validated['planned_start_at']);
        if ($newStart->greaterThan(now()->copy()->addHours($maxHours))) {
            return back()->with('error', "Jadwal baru melebihi batas maksimal {$maxHours} jam untuk prioritas {$pengaduan->prioritas}.");
        }

        DB::beginTransaction();
        try {
            PengaduanSchedule::create([
                'pengaduan_id' => $pengaduan->id,
                'from_start' => $pengaduan->planned_start_at,
                'to_start' => $validated['planned_start_at'],
                'from_end' => $pengaduan->planned_end_at,
                'to_end' => $validated['planned_end_at'],
                'reason' => $validated['reason'],
                'changed_by' => (int) $user->id,
            ]);

            $delayMinutes = max(0, now()->diffInMinutes($newStart, false));
            $delayThreshold = (int) Setting::getValue('overload_delay_threshold_minutes', 60);

            $pengaduan->update([
                'planned_start_at' => $validated['planned_start_at'],
                'planned_end_at' => $validated['planned_end_at'],
                'reschedule_count' => (int) $pengaduan->reschedule_count + 1,
                'last_reschedule_reason' => $validated['reason'],
                'last_rescheduled_by' => (int) $user->id,
                'last_rescheduled_at' => now(),
                'delay_minutes' => $delayMinutes,
                'is_overload_delayed' => $delayMinutes > $delayThreshold,
            ]);

            Log::createLog(
                $pengaduan->id,
                (int) $user->id,
                'reschedule',
                "Jadwal diubah ke {$newStart->format('d/m/Y H:i')} oleh teknisi. Alasan: {$validated['reason']}"
            );

            Notification::send(
                (int) $pengaduan->user_id,
                Notification::JENIS_RESCHEDULED,
                'Jadwal Penanganan Diubah',
                "Tiket #{$pengaduan->kode_pengaduan} dijadwalkan ulang ke {$newStart->format('d/m/Y H:i')}.",
                route('pengaduan.show', $pengaduan->kode_pengaduan)
            );

            $admins = User::query()->whereIn('role', ['admin', 'superadmin'])->pluck('id');
            foreach ($admins as $adminId) {
                Notification::send(
                    (int) $adminId,
                    Notification::JENIS_RESCHEDULED,
                    'Teknisi Reschedule Ticket',
                    "Tiket #{$pengaduan->kode_pengaduan} dijadwalkan ulang oleh {$user->name}.",
                    route('admin.pengaduan.show', $pengaduan->kode_pengaduan)
                );
            }

            if ($delayMinutes > $delayThreshold) {
                $kepseks = User::query()->where('role', 'kepsek')->where('is_active', true)->pluck('id');
                foreach ($kepseks as $kepsekId) {
                    Notification::send(
                        (int) $kepsekId,
                        Notification::JENIS_OVERLOAD_ALERT,
                        'Delay Penanganan Tiket',
                        "Tiket #{$pengaduan->kode_pengaduan} mengalami estimasi delay {$delayMinutes} menit.",
                        route('dashboard')
                    );
                }
            }

            $this->dispatchQueueService->recomputeForTeknisi((int) $pengaduan->teknisi_id);

            DB::commit();
            return back()->with('success', 'Jadwal penanganan berhasil diubah.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal reschedule: ' . $e->getMessage());
        }
    }

    public function recomputeQueue(Request $request)
    {
        $user = Auth::user();
        if (!$user->isTeknisi() && !$user->isAdmin()) {
            abort(403);
        }

        $teknisiId = $user->isTeknisi() ? (int) $user->id : (int) $request->input('teknisi_id', 0);
        if ($teknisiId <= 0) {
            return back()->with('error', 'teknisi_id tidak valid.');
        }

        $this->dispatchQueueService->recomputeForTeknisi($teknisiId);
        return back()->with('success', 'Antrian teknisi berhasil dihitung ulang.');
    }

    private function isRescheduleSchemaReady(): bool
    {
        if (!Schema::hasTable('pengaduan_schedules')) {
            return false;
        }

        foreach ([
            'planned_start_at',
            'planned_end_at',
            'reschedule_count',
            'last_reschedule_reason',
            'last_rescheduled_by',
            'last_rescheduled_at',
            'is_overload_delayed',
            'delay_minutes',
        ] as $column) {
            if (!Schema::hasColumn('pengaduans', $column)) {
                return false;
            }
        }

        return true;
    }
}
