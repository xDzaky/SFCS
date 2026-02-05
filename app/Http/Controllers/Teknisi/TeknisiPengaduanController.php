<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use App\Models\Log;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeknisiPengaduanController extends Controller
{
    /**
     * Display list of assigned pengaduans
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Pengaduan::with(['kategori', 'ruangan.gedung', 'user', 'photos'])
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
        if ($pengaduan->teknisi_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Pengaduan ini tidak ditugaskan kepada Anda');
        }

        $pengaduan->load([
            'kategori',
            'subKategori',
            'ruangan.gedung',
            'user',
            'photos',
            'feedback',
        ]);

        // Get logs for this pengaduan
        $logs = Log::where('pengaduan_id', $pengaduan->id)
            ->with('user')
            ->latest()
            ->get();

        return view('teknisi.pengaduan.show', compact('pengaduan', 'logs'));
    }

    /**
     * Update status to "diproses"
     */
    public function updateStatus(Request $request, Pengaduan $pengaduan)
    {
        $user = Auth::user();

        // Check if assigned to this teknisi
        if ($pengaduan->teknisi_id !== $user->id && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:diproses',
            'catatan_teknisi' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $pengaduan->status;

        $pengaduan->update([
            'status' => $validated['status'],
            'catatan_teknisi' => $validated['catatan_teknisi'] ?? $pengaduan->catatan_teknisi,
            'started_at' => $pengaduan->started_at ?? now(),
        ]);

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

        return back()->with('success', 'Status berhasil diperbarui');
    }

    /**
     * Mark pengaduan as complete
     */
    public function complete(Request $request, Pengaduan $pengaduan)
    {
        $user = Auth::user();

        // Check if assigned to this teknisi
        if ($pengaduan->teknisi_id !== $user->id && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'catatan_teknisi' => 'required|string|max:1000',
        ], [
            'catatan_teknisi.required' => 'Catatan penyelesaian harus diisi',
        ]);

        $pengaduan->update([
            'status' => 'selesai',
            'catatan_teknisi' => $validated['catatan_teknisi'],
            'completed_at' => now(),
        ]);

        // Log activity
        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'teknisi_complete',
            "Pengaduan ditandai selesai oleh teknisi. Catatan: {$validated['catatan_teknisi']}"
        );

        // Notify user
        Notification::send(
            $pengaduan->user_id,
            Notification::JENIS_STATUS_CHANGED,
            'Pengaduan Selesai',
            "Pengaduan #{$pengaduan->kode_pengaduan} telah selesai dikerjakan. Silakan berikan feedback Anda.",
            route('pengaduan.show', $pengaduan->kode_pengaduan)
        );

        // Send feedback reminder after 24 hours (could be a job)
        Notification::send(
            $pengaduan->user_id,
            Notification::JENIS_FEEDBACK_REMINDER,
            'Berikan Feedback',
            "Jangan lupa berikan feedback untuk pengaduan #{$pengaduan->kode_pengaduan} yang sudah selesai.",
            route('pengaduan.show', $pengaduan->kode_pengaduan)
        );

        return redirect()->route('teknisi.pengaduan.index')
            ->with('success', 'Pengaduan berhasil ditandai selesai');
    }
}
