<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\PengaduanPhoto;
use App\Models\Kategori;
use App\Models\Gedung;
use App\Models\SubKategori;
use App\Models\Feedback;
use App\Models\Log;
use App\Models\Notification;
use App\Models\SchoolMap;
use App\Services\PengaduanDuplicateDetector;
use App\Services\PriorityScoringService;
use App\Services\SchoolMapService;
use App\Services\SlaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PengaduanController extends Controller
{
    public function __construct(
        private readonly PengaduanDuplicateDetector $duplicateDetector,
        private readonly SlaService $slaService,
        private readonly PriorityScoringService $priorityScoringService,
        private readonly SchoolMapService $schoolMapService
    )
    {
        $this->middleware('throttle:20,1')->only(['store', 'requestReopen']);
    }

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
        $gedungs = Gedung::where('is_active', true)->get();
        $mapPickerEnabled = $this->schoolMapService->hasActiveMap();

        return view('pengaduan.create', compact('kategoris', 'gedungs', 'mapPickerEnabled'));
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
            'sub_kategori_id' => 'required|exists:sub_kategoris,id',
            'gedung_id' => 'required|exists:gedungs,id',
            'lantai' => 'required|string|max:10',
            'lokasi_detail' => 'nullable|string|max:255',
            'school_map_id' => 'nullable|integer',
            'school_map_layer_id' => 'nullable|integer',
            'map_point_x' => 'nullable|numeric|between:0,1',
            'map_point_y' => 'nullable|numeric|between:0,1',
            'map_zoom' => 'nullable|numeric|min:1|max:8',
            'skip_map_point' => 'nullable|boolean',
            'tanggal_kejadian' => 'nullable|date|before_or_equal:today',
            'prioritas' => 'required|in:rendah,sedang,tinggi,urgent',
            'impact_safety_risk' => 'nullable|boolean',
            'impact_learning_blocked' => 'nullable|boolean',
            'impact_exam_related' => 'nullable|boolean',
            'impact_area_scope' => 'required|in:1_kelas,1_lantai,1_gedung',
            'impact_utilities' => 'required|in:listrik,air,internet,none',
            'photos' => 'required|array|min:1|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'force_submit_duplicate' => 'nullable|boolean',
        ], [
            'judul.required' => 'Judul pengaduan harus diisi',
            'judul.max' => 'Judul maksimal 100 karakter',
            'deskripsi.required' => 'Deskripsi harus diisi',
            'deskripsi.min' => 'Deskripsi minimal 20 karakter',
            'kategori_id.required' => 'Kategori harus dipilih',
            'sub_kategori_id.required' => 'Detail fasilitas/barang harus dipilih',
            'gedung_id.required' => 'Gedung harus dipilih',
            'lantai.required' => 'Lantai harus dipilih',
            'prioritas.required' => 'Tingkat urgensi harus dipilih',
            'photos.required' => 'Minimal upload 1 foto bukti',
            'photos.max' => 'Maksimal 5 foto',
            'photos.*.max' => 'Ukuran foto maksimal 5MB',
            'map_zoom.numeric' => 'Nilai zoom peta tidak valid.',
            'map_zoom.min' => 'Nilai zoom peta minimal 1.',
        ]);

        $duplicate = $this->duplicateDetector->findActiveDuplicateForPayload($validated);
        if ($duplicate && !$request->boolean('force_submit_duplicate')) {
            return back()
                ->withErrors([
                    'duplicate_pengaduan' => "Masalah di lokasi ini sudah dilaporkan pada tiket #{$duplicate->kode_pengaduan} ({$duplicate->status_display}). Jika titik kerusakan berbeda, centang opsi \"Tetap kirim laporan ini\".",
                ])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $impactPayload = [
                'impact_safety_risk' => $request->boolean('impact_safety_risk'),
                'impact_learning_blocked' => $request->boolean('impact_learning_blocked'),
                'impact_exam_related' => $request->boolean('impact_exam_related'),
                'impact_area_scope' => $validated['impact_area_scope'],
                'impact_utilities' => $validated['impact_utilities'],
            ];
            $priorityScore = $this->priorityScoringService->score($impactPayload);
            $priorityResolution = $this->priorityScoringService->resolvePriority($validated['prioritas'], $priorityScore);
            $mapSelection = $this->resolveMapSelection(
                $validated['gedung_id'],
                $validated['lantai'],
                $validated,
                (bool) $request->boolean('skip_map_point')
            );

            // Create pengaduan
            $pengaduan = Pengaduan::create([
                'user_id' => Auth::id(),
                'kategori_id' => $validated['kategori_id'],
                'sub_kategori_id' => $validated['sub_kategori_id'],
                'gedung_id' => $validated['gedung_id'],
                'lantai' => $validated['lantai'],
                'ruangan_id' => null,
                'school_map_id' => $mapSelection['school_map_id'],
                'school_map_layer_id' => $mapSelection['school_map_layer_id'],
                'map_point_x' => $mapSelection['map_point_x'],
                'map_point_y' => $mapSelection['map_point_y'],
                'map_zoom' => $mapSelection['map_zoom'],
                'map_source' => $mapSelection['map_source'],
                'lokasi_detail' => $validated['lokasi_detail'] ?? '',
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tanggal_kejadian' => $validated['tanggal_kejadian'] ?? null,
                'prioritas' => $priorityResolution['final'],
                'requested_prioritas' => $priorityResolution['requested'],
                'priority_score' => $priorityScore,
                'needs_priority_review' => $priorityResolution['needs_review'],
                'impact_safety_risk' => $impactPayload['impact_safety_risk'],
                'impact_learning_blocked' => $impactPayload['impact_learning_blocked'],
                'impact_exam_related' => $impactPayload['impact_exam_related'],
                'impact_area_scope' => $impactPayload['impact_area_scope'],
                'impact_utilities' => $impactPayload['impact_utilities'],
                'status' => 'pending',
                'sla_due_at' => $this->slaService->calculateDueAt($priorityResolution['final']),
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

                if ($priorityResolution['needs_review']) {
                    Notification::send(
                        $admin->id,
                        Notification::JENIS_PRIORITY_ADJUSTED,
                        'Review Urgensi Dibutuhkan',
                        "Tiket #{$pengaduan->kode_pengaduan} meminta {$priorityResolution['requested']}, sistem menetapkan {$priorityResolution['final']}.",
                        route('admin.pengaduan.show', $pengaduan->kode_pengaduan)
                    );
                }
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

        $relations = [
            'kategori',
            'subKategori',
            'gedung',
            'ruangan',
            'photos',
            'assignedTo',
            'feedbackDetail',
            'logs.user',
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

        $mapPayload = $this->schoolMapService->buildPengaduanMapPayload($pengaduan);

        return view('pengaduan.show', compact('pengaduan', 'mapPayload'));
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
        $gedungs = Gedung::where('is_active', true)->get();
        $mapPickerEnabled = $this->schoolMapService->hasActiveMap();

        return view('pengaduan.edit', compact('pengaduan', 'kategoris', 'gedungs', 'mapPickerEnabled'));
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
            'sub_kategori_id' => 'required|exists:sub_kategoris,id',
            'gedung_id' => 'required|exists:gedungs,id',
            'lantai' => 'required|string|max:10',
            'lokasi_detail' => 'nullable|string|max:255',
            'school_map_id' => 'nullable|integer',
            'school_map_layer_id' => 'nullable|integer',
            'map_point_x' => 'nullable|numeric|between:0,1',
            'map_point_y' => 'nullable|numeric|between:0,1',
            'map_zoom' => 'nullable|numeric|min:1|max:8',
            'skip_map_point' => 'nullable|boolean',
            'tanggal_kejadian' => 'nullable|date|before_or_equal:today',
            'prioritas' => 'required|in:rendah,sedang,tinggi,urgent',
            'impact_safety_risk' => 'nullable|boolean',
            'impact_learning_blocked' => 'nullable|boolean',
            'impact_exam_related' => 'nullable|boolean',
            'impact_area_scope' => 'required|in:1_kelas,1_lantai,1_gedung',
            'impact_utilities' => 'required|in:listrik,air,internet,none',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $impactPayload = [
                'impact_safety_risk' => $request->boolean('impact_safety_risk'),
                'impact_learning_blocked' => $request->boolean('impact_learning_blocked'),
                'impact_exam_related' => $request->boolean('impact_exam_related'),
                'impact_area_scope' => $validated['impact_area_scope'],
                'impact_utilities' => $validated['impact_utilities'],
            ];
            $priorityScore = $this->priorityScoringService->score($impactPayload);
            $priorityResolution = $this->priorityScoringService->resolvePriority($validated['prioritas'], $priorityScore);
            $mapSelection = $this->resolveMapSelection(
                $validated['gedung_id'],
                $validated['lantai'],
                $validated,
                (bool) $request->boolean('skip_map_point')
            );

            $pengaduan->update([
                'kategori_id' => $validated['kategori_id'],
                'sub_kategori_id' => $validated['sub_kategori_id'],
                'gedung_id' => $validated['gedung_id'],
                'lantai' => $validated['lantai'],
                'ruangan_id' => null,
                'school_map_id' => $mapSelection['school_map_id'],
                'school_map_layer_id' => $mapSelection['school_map_layer_id'],
                'map_point_x' => $mapSelection['map_point_x'],
                'map_point_y' => $mapSelection['map_point_y'],
                'map_zoom' => $mapSelection['map_zoom'],
                'map_source' => $mapSelection['map_source'],
                'lokasi_detail' => $validated['lokasi_detail'] ?? '',
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tanggal_kejadian' => $validated['tanggal_kejadian'] ?? null,
                'prioritas' => $priorityResolution['final'],
                'requested_prioritas' => $priorityResolution['requested'],
                'priority_score' => $priorityScore,
                'needs_priority_review' => $priorityResolution['needs_review'],
                'impact_safety_risk' => $impactPayload['impact_safety_risk'],
                'impact_learning_blocked' => $impactPayload['impact_learning_blocked'],
                'impact_exam_related' => $impactPayload['impact_exam_related'],
                'impact_area_scope' => $impactPayload['impact_area_scope'],
                'impact_utilities' => $impactPayload['impact_utilities'],
                'sla_due_at' => $this->slaService->calculateDueAt($priorityResolution['final'], $pengaduan->created_at),
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

        if ($pengaduan->is_auto_closed_duplicate) {
            return back()->with('error', 'Feedback hanya tersedia di tiket utama.');
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
     * Request reopen for completed ticket.
     */
    public function requestReopen(Request $request, Pengaduan $pengaduan)
    {
        if ($pengaduan->user_id != Auth::id() || $pengaduan->status !== Pengaduan::STATUS_SELESAI) {
            abort(403);
        }

        if ($pengaduan->is_auto_closed_duplicate) {
            return back()->with('error', 'Tiket auto-closed duplikat tidak dapat diajukan buka ulang.');
        }

        if ($pengaduan->has_reopen_request) {
            return back()->with('error', 'Permintaan buka ulang untuk tiket ini sudah diajukan.');
        }

        $validated = $request->validate([
            'reopen_reason' => 'required|string|min:10|max:1000',
        ]);

        $pengaduan->update([
            'reopen_requested_at' => now(),
            'reopen_requested_by' => Auth::id(),
            'reopen_reason' => $validated['reopen_reason'],
        ]);

        Log::createLog(
            $pengaduan->id,
            Auth::id(),
            'request_reopen',
            "Permintaan buka ulang diajukan: {$validated['reopen_reason']}"
        );

        $admins = \App\Models\User::query()->whereIn('role', ['admin', 'superadmin'])->get();
        foreach ($admins as $admin) {
            Notification::send(
                $admin->id,
                Notification::JENIS_STATUS_CHANGED,
                'Permintaan Buka Ulang',
                "Pengaduan #{$pengaduan->kode_pengaduan} meminta buka ulang setelah selesai.",
                route('admin.pengaduan.show', $pengaduan->kode_pengaduan)
            );
        }

        return back()->with('success', 'Permintaan buka ulang sudah dikirim ke admin.');
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
     * Check active duplicate pengaduan by category + specific location (AJAX)
     */
    public function checkDuplicate(Request $request)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'sub_kategori_id' => 'required|exists:sub_kategoris,id',
            'gedung_id' => 'required|exists:gedungs,id',
            'lantai' => 'required|string|max:10',
            'lokasi_detail' => 'nullable|string|max:255',
        ]);

        $duplicate = $this->duplicateDetector->findActiveDuplicateForPayload($validated);

        if (!$duplicate) {
            return response()->json([
                'has_duplicate' => false,
            ]);
        }

        return response()->json([
            'has_duplicate' => true,
            'pengaduan' => [
                'kode_pengaduan' => $duplicate->kode_pengaduan,
                'judul' => $duplicate->judul,
                'status' => $duplicate->status,
                'status_display' => $duplicate->status_display,
                'url' => route('pengaduan.show', $duplicate->kode_pengaduan),
            ],
        ]);
    }

    /**
     * Track pengaduan by kode (public)
     */
    public function track(Request $request)
    {
        $pengaduan = null;

        if ($request->filled('kode')) {
            $pengaduan = Pengaduan::with(['kategori', 'gedung', 'ruangan.gedung'])
                ->where('kode_pengaduan', $request->kode)
                ->first();

            if (!$pengaduan) {
                return view('pengaduan.track', ['pengaduan' => null, 'error_msg' => 'Pengaduan dengan kode tersebut tidak ditemukan.']);
            }
        }

        return view('pengaduan.track', compact('pengaduan'));
    }

    private function resolveMapSelection(int|string $gedungId, string $lantai, array $validated, bool $skipMapPoint = false): array
    {
        $activeMap = SchoolMap::query()->where('is_active', true)->first();
        if (!$activeMap) {
            return [
                'school_map_id' => null,
                'school_map_layer_id' => null,
                'map_point_x' => null,
                'map_point_y' => null,
                'map_zoom' => null,
                'map_source' => 'fallback_text',
            ];
        }

        $layer = $this->schoolMapService->resolveLayerForLocation($activeMap, (int) $gedungId, $lantai);
        if (!$layer) {
            return [
                'school_map_id' => null,
                'school_map_layer_id' => null,
                'map_point_x' => null,
                'map_point_y' => null,
                'map_zoom' => null,
                'map_source' => 'fallback_text',
            ];
        }

        if ($skipMapPoint) {
            return [
                'school_map_id' => $activeMap->id,
                'school_map_layer_id' => $layer->id,
                'map_point_x' => null,
                'map_point_y' => null,
                'map_zoom' => null,
                'map_source' => 'fallback_text',
            ];
        }

        // Gedung-lantai specific layers require a point to be picked.
        // General/overview layers allow optional point picking.
        if (!isset($validated['map_point_x'], $validated['map_point_y'])) {
            if ($layer->layer_scope === 'gedung_lantai') {
                throw ValidationException::withMessages([
                    'map_point_x' => 'Titik lokasi di denah wajib dipilih untuk lokasi ini.',
                ]);
            }
            // For general layers, point is optional — save fallback_text with layer reference.
            return [
                'school_map_id' => $activeMap->id,
                'school_map_layer_id' => $layer->id,
                'map_point_x' => null,
                'map_point_y' => null,
                'map_zoom' => null,
                'map_source' => 'fallback_text',
            ];
        }

        return [
            'school_map_id' => $activeMap->id,
            'school_map_layer_id' => $layer->id,
            'map_point_x' => (float) $validated['map_point_x'],
            'map_point_y' => (float) $validated['map_point_y'],
            'map_zoom' => isset($validated['map_zoom']) ? max(1, (int) round((float) $validated['map_zoom'])) : 2,
            'map_source' => 'manual_point',
        ];
    }

}
