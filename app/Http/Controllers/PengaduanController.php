<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\PengaduanPhoto;
use App\Models\Kategori;
use App\Models\Gedung;
use App\Models\Ruangan;
use App\Models\SubKategori;
use App\Models\Feedback;
use App\Models\Log;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PengaduanController extends Controller
{
    /**
     * Display a listing of the user's pengaduans
     */
    public function index(Request $request)
    {
        $query = Pengaduan::with(['kategori', 'gedung', 'ruangan', 'photos'])
            ->where('user_id', Auth::id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_pengaduan', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $pengaduans = $query->latest()->paginate(10);
        $kategoris = Kategori::where('is_active', true)->get();

        return view('pengaduan.index', compact('pengaduans', 'kategoris'));
    }

    /**
     * Show the form for creating a new pengaduan
     */
    public function create()
    {
        $kategoris = Kategori::with('subKategoris')->where('is_active', true)->get();
        $gedungs = Gedung::with('ruangans')->where('is_active', true)->get();

        return view('pengaduan.create', compact('kategoris', 'gedungs'));
    }

    /**
     * Store a newly created pengaduan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:100',
            'deskripsi' => 'required|string|min:20|max:2000',
            'kategori_id' => 'required|exists:kategoris,id',
            'sub_kategori_id' => 'nullable|exists:sub_kategoris,id',
            'gedung_id' => 'required|exists:gedungs,id',
            'lantai' => 'required|string|max:10',
            'ruangan_id' => 'nullable|exists:ruangans,id',
            'lokasi_detail' => 'nullable|string|max:255',
            'tanggal_kejadian' => 'nullable|date|before_or_equal:today',
            'prioritas' => 'required|in:rendah,sedang,tinggi,urgent',
            'photos' => 'required|array|min:1|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'judul.required' => 'Judul pengaduan harus diisi',
            'judul.max' => 'Judul maksimal 100 karakter',
            'deskripsi.required' => 'Deskripsi harus diisi',
            'deskripsi.min' => 'Deskripsi minimal 20 karakter',
            'kategori_id.required' => 'Kategori harus dipilih',
            'gedung_id.required' => 'Gedung harus dipilih',
            'lantai.required' => 'Lantai harus dipilih',
            'prioritas.required' => 'Tingkat urgensi harus dipilih',
            'photos.required' => 'Minimal upload 1 foto bukti',
            'photos.max' => 'Maksimal 5 foto',
            'photos.*.max' => 'Ukuran foto maksimal 5MB',
        ]);

        DB::beginTransaction();
        try {
            // Create pengaduan
            $pengaduan = Pengaduan::create([
                'user_id' => Auth::id(),
                'kategori_id' => $validated['kategori_id'],
                'sub_kategori_id' => $validated['sub_kategori_id'] ?? null,
                'gedung_id' => $validated['gedung_id'],
                'lantai' => $validated['lantai'],
                'ruangan_id' => $validated['ruangan_id'] ?? null,
                'lokasi_detail' => $validated['lokasi_detail'] ?? '',
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tanggal_kejadian' => $validated['tanggal_kejadian'] ?? null,
                'prioritas' => $validated['prioritas'],
                'status' => 'pending',
            ]);

            // Upload photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('pengaduan/' . $pengaduan->id, 'public');
                    
                    PengaduanPhoto::create([
                        'pengaduan_id' => $pengaduan->id,
                        'file_path' => $path,
                        'file_name' => $photo->getClientOriginalName(),
                        'file_size' => $photo->getSize(),
                        'tipe' => 'bukti',
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            // Create log
            Log::createLog(
                $pengaduan->id,
                Auth::id(),
                'create',
                "Pengaduan baru #{$pengaduan->kode_pengaduan}"
            );

            // Notify admins
            $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])->get();
            foreach ($admins as $admin) {
                Notification::send(
                    $admin->id,
                    Notification::JENIS_PENGADUAN_CREATED,
                    'Pengaduan Baru',
                    "Pengaduan baru #{$pengaduan->kode_pengaduan}: {$pengaduan->judul}",
                    route('admin.pengaduan.show', $pengaduan->kode_pengaduan)
                );
            }

            DB::commit();

            return redirect()->route('pengaduan.show', $pengaduan->kode_pengaduan)
                ->with('success', 'Pengaduan berhasil dibuat dengan kode: ' . $pengaduan->kode_pengaduan);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified pengaduan
     */
    public function show(Pengaduan $pengaduan)
    {
        // Check if user owns this pengaduan or is admin
        // Use == instead of !== to handle potential type mismatch between string and int
        if ($pengaduan->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $pengaduan->load(['kategori', 'subKategori', 'gedung', 'ruangan', 'photos', 'assignedTo', 'feedbackDetail', 'logs']);

        return view('pengaduan.show', compact('pengaduan'));
    }

    /**
     * Show the form for editing the pengaduan
     */
    public function edit(Pengaduan $pengaduan)
    {
        // Only allow edit if status is pending and user owns it
        // Use != instead of !== to handle potential type mismatch
        if ($pengaduan->user_id != Auth::id() || $pengaduan->status !== 'pending') {
            abort(403);
        }

        $kategoris = Kategori::with('subKategoris')->where('is_active', true)->get();
        $gedungs = Gedung::with('ruangans')->where('is_active', true)->get();

        return view('pengaduan.edit', compact('pengaduan', 'kategoris', 'gedungs'));
    }

    /**
     * Update the specified pengaduan
     */
    public function update(Request $request, Pengaduan $pengaduan)
    {
        // Only allow update if status is pending and user owns it
        if ($pengaduan->user_id != Auth::id() || $pengaduan->status !== 'pending') {
            abort(403);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:100',
            'deskripsi' => 'required|string|min:20|max:2000',
            'kategori_id' => 'required|exists:kategoris,id',
            'sub_kategori_id' => 'nullable|exists:sub_kategoris,id',
            'gedung_id' => 'required|exists:gedungs,id',
            'lantai' => 'required|string|max:10',
            'ruangan_id' => 'nullable|exists:ruangans,id',
            'lokasi_detail' => 'nullable|string|max:255',
            'tanggal_kejadian' => 'nullable|date|before_or_equal:today',
            'prioritas' => 'required|in:rendah,sedang,tinggi,urgent',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $pengaduan->update([
                'kategori_id' => $validated['kategori_id'],
                'sub_kategori_id' => $validated['sub_kategori_id'] ?? null,
                'gedung_id' => $validated['gedung_id'],
                'lantai' => $validated['lantai'],
                'ruangan_id' => $validated['ruangan_id'] ?? null,
                'lokasi_detail' => $validated['lokasi_detail'] ?? '',
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tanggal_kejadian' => $validated['tanggal_kejadian'] ?? null,
                'prioritas' => $validated['prioritas'],
            ]);

            // Upload new photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('pengaduan/' . $pengaduan->id, 'public');
                    
                    PengaduanPhoto::create([
                        'pengaduan_id' => $pengaduan->id,
                        'file_path' => $path,
                        'file_name' => $photo->getClientOriginalName(),
                        'file_size' => $photo->getSize(),
                        'tipe' => 'bukti',
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            // Log activity
            Log::createLog(
                $pengaduan->id,
                Auth::id(),
                'update',
                "Pengaduan #{$pengaduan->kode_pengaduan} diperbarui"
            );

            DB::commit();

            return redirect()->route('pengaduan.show', $pengaduan->kode_pengaduan)
                ->with('success', 'Pengaduan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified pengaduan
     */
    public function destroy(Pengaduan $pengaduan)
    {
        // Only allow delete if status is pending and user owns it
        if ($pengaduan->user_id != Auth::id() || $pengaduan->status !== 'pending') {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Delete photos from storage
            foreach ($pengaduan->photos as $photo) {
                Storage::disk('public')->delete($photo->file_path);
            }

            // Delete directory
            Storage::disk('public')->deleteDirectory('pengaduan/' . $pengaduan->id);

            // Log activity
            Log::createLog(
                $pengaduan->id,
                Auth::id(),
                'delete',
                "Pengaduan #{$pengaduan->kode_pengaduan} dihapus"
            );

            $pengaduan->delete();

            DB::commit();

            return redirect()->route('pengaduan.index')
                ->with('success', 'Pengaduan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete a photo from pengaduan
     */
    public function deletePhoto(PengaduanPhoto $photo)
    {
        $pengaduan = $photo->pengaduan;

        // Check permission
        if ($pengaduan->user_id != Auth::id() || $pengaduan->status !== 'pending') {
            abort(403);
        }

        // Delete from storage
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return back()->with('success', 'Foto berhasil dihapus');
    }

    /**
     * Submit feedback for completed pengaduan
     */
    public function submitFeedback(Request $request, Pengaduan $pengaduan)
    {
        // Check if user owns this and status is selesai
        if ($pengaduan->user_id != Auth::id() || $pengaduan->status !== 'selesai') {
            abort(403);
        }

        // Check if already has feedback
        if ($pengaduan->feedbackDetail) {
            return back()->with('error', 'Anda sudah memberikan feedback untuk pengaduan ini');
        }

        $validated = $request->validate([
            'rating_respon' => 'required|integer|min:1|max:5',
            'rating_kualitas' => 'required|integer|min:1|max:5',
            'rating_pelayanan' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
            'is_satisfied' => 'required|boolean',
        ]);

        Feedback::create([
            'pengaduan_id' => $pengaduan->id,
            'user_id' => Auth::id(),
            'rating_respon' => $validated['rating_respon'],
            'rating_kualitas' => $validated['rating_kualitas'],
            'rating_pelayanan' => $validated['rating_pelayanan'],
            'komentar' => $validated['komentar'],
            'is_satisfied' => $validated['is_satisfied'],
        ]);

        // Log activity
        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'feedback',
            "Feedback untuk pengaduan #{$pengaduan->kode_pengaduan}"
        );

        return back()->with('success', 'Terima kasih atas feedback Anda!');
    }

    /**
     * Get sub kategoris by kategori (AJAX)
     */
    public function getSubKategoris(Kategori $kategori)
    {
        $subKategoris = $kategori->subKategoris()->where('is_active', true)->get();
        
        return response()->json($subKategoris);
    }

    /**
     * Get ruangans by gedung (AJAX)
     */
    public function getRuangans(Gedung $gedung)
    {
        $ruangans = $gedung->ruangans()->where('is_active', true)->get();
        
        return response()->json($ruangans);
    }

    /**
     * Track pengaduan by kode (public)
     */
    public function track(Request $request)
    {
        $pengaduan = null;

        if ($request->filled('kode')) {
            $pengaduan = Pengaduan::with(['kategori', 'ruangan.gedung'])
                ->where('kode_pengaduan', $request->kode)
                ->first();

            if (!$pengaduan) {
                return view('pengaduan.track', ['pengaduan' => null, 'error_msg' => 'Pengaduan dengan kode tersebut tidak ditemukan.']);
            }
        }

        return view('pengaduan.track', compact('pengaduan'));
    }
}
