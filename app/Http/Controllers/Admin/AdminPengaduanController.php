<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use App\Models\User;
use App\Models\Log;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminPengaduanController extends Controller
{
    /**
     * Display a listing of all pengaduans
     */
    public function index(Request $request)
    {
        $query = Pengaduan::with(['kategori', 'ruangan.gedung', 'user', 'teknisi']);

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

        $pengaduans = $query->paginate(15);

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
        $pengaduan->load([
            'kategori',
            'gedung',
            'user',
            'assignedTo',
            'photos',
            'feedbackDetail',
        ]);

        $teknisis = User::where('role', 'teknisi')
            ->where('is_active', true)
            ->get();

        // Get logs for this pengaduan
        $logs = Log::where('pengaduan_id', $pengaduan->id)
            ->with('user')
            ->latest()
            ->get();

        return view('admin.pengaduan.show', compact('pengaduan', 'teknisis', 'logs'));
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

        $oldStatus = $pengaduan->status;
        
        $pengaduan->update([
            'status' => $validated['status'],
            'catatan_admin' => $validated['catatan_admin'] ?? $pengaduan->catatan_admin,
        ]);

        // Log activity
        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'status_change',
            "Status diubah dari {$oldStatus} ke {$validated['status']}"
        );

        // Notify user
        Notification::send(
            $pengaduan->user_id,
            Notification::JENIS_STATUS_CHANGED,
            'Status Pengaduan Diperbarui',
            "Pengaduan #{$pengaduan->kode_pengaduan} telah diperbarui ke status: " . ucfirst($validated['status']),
            route('pengaduan.show', $pengaduan->kode_pengaduan)
        );

        return back()->with('success', 'Status pengaduan berhasil diperbarui');
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

        $pengaduan->update([
            'teknisi_id' => $validated['teknisi_id'],
            'status' => $pengaduan->status === 'pending' ? 'diverifikasi' : $pengaduan->status,
            'assigned_at' => now(),
        ]);

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

        $pengaduan->update([
            'status' => 'ditolak',
            'alasan_tolak' => $validated['alasan_tolak'],
        ]);

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

        return back()->with('success', 'Pengaduan berhasil ditolak');
    }

    /**
     * Bulk update status
     */
    public function bulkStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pengaduans,id',
            'status' => 'required|in:pending,diverifikasi,diproses,selesai,ditolak',
        ]);

        DB::beginTransaction();
        try {
            $pengaduans = Pengaduan::whereIn('id', $validated['ids'])->get();

            foreach ($pengaduans as $pengaduan) {
                $oldStatus = $pengaduan->status;
                $pengaduan->update(['status' => $validated['status']]);

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
            }

            DB::commit();

            return back()->with('success', count($validated['ids']) . ' pengaduan berhasil diperbarui');

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
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pengaduans,id',
            'teknisi_id' => 'required|exists:users,id',
        ]);

        $teknisi = User::findOrFail($validated['teknisi_id']);

        if ($teknisi->role !== 'teknisi') {
            return back()->with('error', 'User yang dipilih bukan teknisi');
        }

        DB::beginTransaction();
        try {
            $pengaduans = Pengaduan::whereIn('id', $validated['ids'])->get();

            foreach ($pengaduans as $pengaduan) {
                $pengaduan->update([
                    'teknisi_id' => $validated['teknisi_id'],
                    'status' => $pengaduan->status === 'pending' ? 'diverifikasi' : $pengaduan->status,
                    'assigned_at' => now(),
                ]);

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
                "Anda ditugaskan untuk menangani " . count($validated['ids']) . " pengaduan baru",
                route('teknisi.pengaduan.index')
            );

            DB::commit();

            return back()->with('success', count($validated['ids']) . " pengaduan berhasil ditugaskan ke {$teknisi->name}");

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
        $query = Pengaduan::with(['kategori', 'ruangan.gedung', 'user', 'teknisi']);

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
}
