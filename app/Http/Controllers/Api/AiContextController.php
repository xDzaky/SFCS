<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Gedung;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\Pinjaman;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiContextController extends Controller
{
    /**
     * Role level constants.
     */
    private const ALLOWED_ROLES = [
        'siswa',
        'guru',
        'teknisi',
        'kepsek',
        'admin',
        'superadmin',
    ];

    /**
     * Verifikasi API Key atau Web Session.
     */
    private function verifyAccess(Request $request): ?string
    {
        // 1. Cek jika user login melalui Web Session
        if (Auth::check()) {
            return null; // Lolos verifikasi
        }

        // 2. Cek API Key dari Header atau Bearer Token
        $configuredKey = config('app.ai_api_key', env('AI_API_KEY', 'sfcs-ai-secret-2026'));
        $apiKey = $request->header('X-API-KEY') 
            ?? $request->header('X-AI-KEY') 
            ?? $request->bearerToken() 
            ?? $request->input('api_key');

        if (!$apiKey || $apiKey !== $configuredKey) {
            return 'Unauthorized: API Key tidak valid atau tidak disertakan.';
        }

        return null;
    }

    /**
     * Health check endpoint untuk service AI.
     */
    public function health(Request $request)
    {
        $authError = $this->verifyAccess($request);
        if ($authError) {
            return response()->json([
                'status'  => 'error',
                'message' => $authError,
            ], 401);
        }

        $schoolName = Setting::getValue('school_name', 'SFCS School');

        return response()->json([
            'status'      => 'ok',
            'app'         => config('app.name', 'SFCS'),
            'school_name' => $schoolName,
            'timestamp'   => now()->toIso8601String(),
            'message'     => 'API Context SFCS untuk AI Developer siap digunakan.',
        ]);
    }

    /**
     * Metadata role & batasan hak akses data untuk panduan AI.
     */
    public function roles(Request $request)
    {
        $authError = $this->verifyAccess($request);
        if ($authError) {
            return response()->json(['status' => 'error', 'message' => $authError], 401);
        }

        return response()->json([
            'status' => 'ok',
            'roles'  => [
                'siswa' => [
                    'label'       => 'Siswa',
                    'description' => 'Siswa hanya boleh melihat laporan & pinjaman milik dirinya sendiri, katalog barang publik, fasilitas umum, dan kategori laporan.',
                    'allowed'     => ['my_pengaduan', 'my_pinjaman', 'katalog_barang', 'fasilitas_sekolah', 'kategori_pengaduan', 'info_sekolah'],
                    'prohibited'  => ['data_guru', 'data_siswa_lain', 'data_teknisi', 'semua_pengaduan', 'laporan_kinerja', 'log_keamanan'],
                ],
                'guru' => [
                    'label'       => 'Guru',
                    'description' => 'Guru hanya boleh melihat laporan & pinjaman miliknya sendiri, katalog barang sarpras, fasilitas sekolah, dan kategori laporan.',
                    'allowed'     => ['my_pengaduan', 'my_pinjaman', 'katalog_barang', 'fasilitas_sekolah', 'kategori_pengaduan', 'info_sekolah'],
                    'prohibited'  => ['data_guru_lain', 'data_siswa_lain', 'data_teknisi', 'semua_pengaduan', 'laporan_kinerja'],
                ],
                'teknisi' => [
                    'label'       => 'Teknisi',
                    'description' => 'Teknisi hanya dapat melihat tiket yang ditugaskan ke dirinya, detail teknis perbaikan, stok barang sarpras, dan fasilitas.',
                    'allowed'     => ['assigned_pengaduan', 'tugas_selesai_hari_ini', 'katalog_barang', 'fasilitas_sekolah', 'kategori_pengaduan'],
                    'prohibited'  => ['data_pengguna_lain', 'gaji', 'laporan_manajerial_admin'],
                ],
                'kepsek' => [
                    'label'       => 'Kepala Sekolah',
                    'description' => 'Kepala sekolah dapat melihat ringkasan statistik performa, SLA overdue, tingkat kepuasan (rating), dan rekap sarpras tanpa kredensial.',
                    'allowed'     => ['statistik_pengaduan', 'metrik_performa_sla', 'rekap_rating_feedback', 'rekap_pinjaman', 'fasilitas_sekolah'],
                    'prohibited'  => ['password', 'token_sistem', 'data_sensitif_pribadi'],
                ],
                'admin' => [
                    'label'       => 'Admin Sarana / Super Admin',
                    'description' => 'Admin memiliki akses ke seluruh ringkasan pengaduan, penugasan teknisi, stok penuh inventaris, rekap pinjaman, dan daftar akun.',
                    'allowed'     => ['semua_pengaduan', 'statistik_penuh', 'beban_kerja_teknisi', 'kelola_barang', 'rekap_pinjaman', 'daftar_pengguna_sanitasi'],
                    'prohibited'  => ['password_hash', 'app_secret', 'database_credentials'],
                ],
            ],
        ]);
    }

    /**
     * Endpoint Utama: Mendapatkan Context Database tersaring sesuai Role & User ID.
     * GET /api/v1/ai/context?role=siswa&user_id=1
     */
    public function getContext(Request $request)
    {
        $authError = $this->verifyAccess($request);
        if ($authError) {
            return response()->json(['status' => 'error', 'message' => $authError], 401);
        }

        // Tentukan User & Role
        $user = Auth::user();
        $targetUserId = $request->query('user_id');
        $targetRole   = $request->query('role');

        if ($user) {
            // Jika login via web, prioritaskan user login
            $activeRole = $user->role;
            $activeUserId = $user->id;
            $activeUserName = $user->name;
        } else {
            // Jika dipanggil via API eksternal oleh Developer AI
            if ($targetUserId) {
                $targetUser = User::find($targetUserId);
                if ($targetUser) {
                    $activeRole     = $targetUser->role;
                    $activeUserId   = $targetUser->id;
                    $activeUserName = $targetUser->name;
                } else {
                    $activeRole     = in_array($targetRole, self::ALLOWED_ROLES) ? $targetRole : 'siswa';
                    $activeUserId   = null;
                    $activeUserName = 'Guest (' . ucfirst($activeRole) . ')';
                }
            } else {
                $activeRole     = in_array($targetRole, self::ALLOWED_ROLES) ? $targetRole : 'siswa';
                $activeUserId   = null;
                $activeUserName = 'Role ' . ucfirst($activeRole);
            }
        }

        $scope = $request->query('scope', 'all');

        $contextData = $this->buildContextByRole($activeRole, $activeUserId, $activeUserName, $scope);

        return response()->json([
            'status'        => 'ok',
            'authenticated' => [
                'role'      => $activeRole,
                'user_id'   => $activeUserId,
                'user_name' => $activeUserName,
            ],
            'context'       => $contextData,
        ]);
    }

    /**
     * Endpoint Query Terfokus: Teman AI bisa menanyakan data spesifik sesuai role.
     * POST /api/v1/ai/query
     */
    public function query(Request $request)
    {
        $authError = $this->verifyAccess($request);
        if ($authError) {
            return response()->json(['status' => 'error', 'message' => $authError], 401);
        }

        $validated = $request->validate([
            'role'    => 'required|string|in:siswa,guru,teknisi,kepsek,admin,superadmin',
            'user_id' => 'nullable|integer',
            'action'  => 'required|string|in:check_barang,check_pengaduan,check_pinjaman,check_fasilitas,summary',
            'keyword' => 'nullable|string|max:100',
        ]);

        $role    = $validated['role'];
        $userId  = $validated['user_id'] ?? null;
        $action  = $validated['action'];
        $keyword = trim($validated['keyword'] ?? '');

        $result = [];

        switch ($action) {
            case 'check_barang':
                $q = Barang::query();
                // Siswa, guru, teknisi hanya bisa melihat barang aktif
                if (!in_array($role, ['admin', 'superadmin'])) {
                    $q->where('is_active', true)->where('stok_tersedia', '>', 0);
                }
                if ($keyword !== '') {
                    $q->where('nama', 'like', "%{$keyword}%");
                }
                $result = $q->take(15)->get()->map(function ($b) use ($role) {
                    $item = [
                        'id'            => $b->id,
                        'nama'          => $b->nama,
                        'unit_sarpras'  => $b->unit_sarpras === 'atas' ? 'Sarpras Atas (Peminjaman)' : 'Sarpras Bawah (Permintaan Habis Pakai)',
                        'kategori'      => $b->kategori,
                        'stok_tersedia' => $b->stok_tersedia,
                    ];
                    if (in_array($role, ['admin', 'superadmin'])) {
                        $item['stok_total'] = $b->stok_total;
                        $item['kondisi']    = $b->kondisi;
                        $item['is_active']  = $b->is_active;
                    }
                    return $item;
                });
                break;

            case 'check_pengaduan':
                $q = Pengaduan::with(['kategori:id,nama', 'gedung:id,nama', 'ruangan:id,nama']);
                
                // BATASAN HAK AKSES PER ROLE
                if (in_array($role, ['siswa', 'guru'])) {
                    if (!$userId) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'Role siswa/guru memerlukan user_id untuk melihat data pengaduannya sendiri.',
                        ], 403);
                    }
                    // Siswa/guru HANYA pengaduannya sendiri
                    $q->where('user_id', $userId);
                } elseif ($role === 'teknisi') {
                    if (!$userId) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'Role teknisi memerlukan user_id untuk melihat tugasnya.',
                        ], 403);
                    }
                    $q->where('teknisi_id', $userId);
                }

                if ($keyword !== '') {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('kode_pengaduan', 'like', "%{$keyword}%")
                            ->orWhere('judul', 'like', "%{$keyword}%");
                    });
                }

                $result = $q->latest()->take(10)->get()->map(function ($p) use ($role) {
                    $item = [
                        'kode'       => $p->kode_pengaduan,
                        'judul'      => $p->judul,
                        'kategori'   => $p->kategori->nama ?? '-',
                        'lokasi'     => ($p->gedung->nama ?? '') . ' - ' . ($p->ruangan->nama ?? ''),
                        'prioritas'  => $p->prioritas ?? $p->urgensi,
                        'status'     => $p->status,
                        'created_at' => $p->created_at->format('d M Y H:i'),
                    ];
                    if (in_array($role, ['teknisi', 'admin', 'superadmin'])) {
                        $item['sla_due_at']          = optional($p->sla_due_at)->format('d M Y H:i');
                        $item['is_overload_delayed'] = (bool) $p->is_overload_delayed;
                        $item['catatan_teknisi']     = $p->catatan_teknisi;
                    }
                    return $item;
                });
                break;

            case 'check_pinjaman':
                $q = Pinjaman::with('barang:id,nama,unit_sarpras');

                if (in_array($role, ['siswa', 'guru'])) {
                    if (!$userId) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'Role siswa/guru memerlukan user_id untuk melihat data pinjamannya.',
                        ], 403);
                    }
                    $q->where('user_id', $userId);
                }

                $result = $q->latest()->take(10)->get()->map(function ($pinjam) {
                    return [
                        'id'             => $pinjam->id,
                        'barang'         => $pinjam->barang->nama ?? '-',
                        'unit'           => $pinjam->barang->unit_sarpras ?? '-',
                        'jumlah'         => $pinjam->jumlah,
                        'status'         => $pinjam->status,
                        'tanggal_pinjam' => optional($pinjam->tanggal_pinjam)->format('d M Y H:i'),
                        'tanggal_kembali'=> optional($pinjam->tanggal_kembali)->format('d M Y H:i'),
                    ];
                });
                break;

            case 'check_fasilitas':
                $result = Gedung::with(['ruangans:id,gedung_id,nama'])->get()->map(function ($g) {
                    return [
                        'gedung'   => $g->nama,
                        'ruangans' => $g->ruangans->pluck('nama')->toArray(),
                    ];
                });
                break;

            case 'summary':
                $result = $this->buildSummaryStats($role, $userId);
                break;
        }

        return response()->json([
            'status'  => 'ok',
            'action'  => $action,
            'role'    => $role,
            'count'   => is_countable($result) ? count($result) : 1,
            'data'    => $result,
        ]);
    }

    /**
     * Helper pembuat context data terstruktur per role.
     */
    public function buildContextByRole(string $role, ?int $userId, string $userName, string $scope = 'all'): array
    {
        $context = [
            'school_profile' => [
                'nama'             => Setting::getValue('school_name', 'SFCS School'),
                'jam_operasional'  => 'Senin - Sabtu: 07.00 - 16.00 WIB',
                'jam_pulang_sarpras'=> '15.20 WIB (Batas pengembalian barang pinjaman Sarpras Atas)',
                'aturan_sarpras_atas' => 'Peminjaman alat (proyektor, kabel, mic, dll) harus dikembalikan pada hari yang sama paling lambat jam 15.20 WIB.',
                'aturan_sarpras_bawah'=> 'Permintaan barang habis pakai (kertas HVS, spidol, ATK) tidak perlu dikembalikan setelah disetujui.',
            ],
            'kategori_pengaduan' => Kategori::where('is_active', true)->pluck('nama')->toArray(),
            'daftar_gedung'      => Gedung::pluck('nama')->toArray(),
        ];

        // 1. ROLE SISWA / GURU (DATA PRIBADI SAJA, TANPA DATA ORANG LAIN)
        if (in_array($role, ['siswa', 'guru'])) {
            $context['hak_akses'] = 'User Biasa (' . ucfirst($role) . '). Data dibatasi hanya untuk laporan dan pinjaman milik sendiri.';

            if ($userId) {
                // Pengaduan milik user sendiri
                $context['my_pengaduan_terbaru'] = Pengaduan::where('user_id', $userId)
                    ->with(['kategori:id,nama', 'gedung:id,nama', 'ruangan:id,nama'])
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn($p) => [
                        'kode'       => $p->kode_pengaduan,
                        'judul'      => $p->judul,
                        'lokasi'     => ($p->gedung->nama ?? '') . ' - ' . ($p->ruangan->nama ?? ''),
                        'prioritas'  => $p->prioritas ?? $p->urgensi,
                        'status'     => $p->status,
                        'created_at' => $p->created_at->format('d M Y'),
                    ]);

                // Pinjaman aktif milik user sendiri
                $context['my_pinjaman_aktif'] = Pinjaman::where('user_id', $userId)
                    ->whereNotIn('status', ['selesai', 'dibatalkan'])
                    ->with('barang:id,nama,unit_sarpras')
                    ->latest()
                    ->take(4)
                    ->get()
                    ->map(fn($pinjam) => [
                        'barang'         => $pinjam->barang->nama ?? '-',
                        'unit'           => $pinjam->barang->unit_sarpras ?? '-',
                        'jumlah'         => $pinjam->jumlah,
                        'status'         => $pinjam->status,
                        'tanggal_kembali'=> optional($pinjam->tanggal_kembali)->format('d M Y H:i'),
                    ]);
            } else {
                $context['my_pengaduan_terbaru'] = [];
                $context['my_pinjaman_aktif']    = [];
            }

            // Barang yang tersedia untuk dipinjam/diminta
            $context['katalog_barang_tersedia'] = Barang::where('is_active', true)
                ->where('stok_tersedia', '>', 0)
                ->orderBy('unit_sarpras')
                ->take(15)
                ->get()
                ->map(fn($b) => [
                    'nama'          => $b->nama,
                    'unit'          => $b->unit_sarpras === 'atas' ? 'Sarpras Atas (Peminjaman)' : 'Sarpras Bawah (Permintaan)',
                    'stok_tersedia' => $b->stok_tersedia,
                ]);

            return $context;
        }

        // 2. ROLE TEKNISI (HANYA TIKET TUGAS TEKNISI BERSANGKUTAN)
        if ($role === 'teknisi') {
            $context['hak_akses'] = 'Teknisi Sekolah. Hanya dapat melihat tiket yang ditugaskan ke dirinya dan ketersediaan barang inventaris.';

            if ($userId) {
                $context['tugas_aktif_saya'] = Pengaduan::where('teknisi_id', $userId)
                    ->whereIn('status', ['diverifikasi', 'diproses'])
                    ->with(['kategori:id,nama', 'gedung:id,nama', 'ruangan:id,nama', 'user:id,name'])
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(fn($p) => [
                        'kode'            => $p->kode_pengaduan,
                        'judul'           => $p->judul,
                        'deskripsi'       => $p->deskripsi,
                        'pelapor'         => $p->user->name ?? 'Anonim',
                        'lokasi'          => ($p->gedung->nama ?? '') . ' - ' . ($p->ruangan->nama ?? ''),
                        'prioritas'       => $p->prioritas ?? $p->urgensi,
                        'status'          => $p->status,
                        'sla_due_at'      => optional($p->sla_due_at)->format('d M Y H:i'),
                        'overload_delay'  => (bool) $p->is_overload_delayed,
                    ]);

                $context['statistik_tugas'] = [
                    'aktif'            => Pengaduan::where('teknisi_id', $userId)->whereIn('status', ['diverifikasi', 'diproses'])->count(),
                    'selesai_hari_ini' => Pengaduan::where('teknisi_id', $userId)->where('status', 'selesai')->whereDate('completed_at', today())->count(),
                ];
            }

            // Teknisi bisa melihat semua barang aktif untuk kebutuhan spare part
            $context['katalog_sarpras'] = Barang::where('is_active', true)
                ->select(['id', 'nama', 'unit_sarpras', 'stok_tersedia'])
                ->take(20)
                ->get();

            return $context;
        }

        // 3. ROLE KEPALA SEKOLAH (STATISTIK AGREGAT & KINERJA)
        if ($role === 'kepsek') {
            $context['hak_akses'] = 'Kepala Sekolah. Akses data analitik, ringkasan kinerja perbaikan fasilitas, dan SLA sekolah.';

            $context['statistik_pengaduan'] = [
                'total_semua'   => Pengaduan::count(),
                'menunggu'      => Pengaduan::where('status', 'pending')->count(),
                'sedang_proses' => Pengaduan::whereIn('status', ['diverifikasi', 'diproses'])->count(),
                'selesai_total' => Pengaduan::where('status', 'selesai')->count(),
                'selesai_bulan_ini' => Pengaduan::where('status', 'selesai')->whereMonth('completed_at', now()->month)->count(),
                'lewat_sla'     => Pengaduan::whereNotNull('sla_due_at')->where('sla_due_at', '<', now())->whereNotIn('status', ['selesai', 'ditolak'])->count(),
            ];

            $context['statistik_pinjaman'] = [
                'menunggu_persetujuan' => Pinjaman::where('status', 'menunggu')->count(),
                'aktif_dipinjam'       => Pinjaman::where('status', 'disetujui')->orWhere('status', 'dipinjam')->count(),
                'terlambat'            => Pinjaman::where('status', 'terlambat')->count(),
            ];

            return $context;
        }

        // 4. ROLE ADMIN / SUPERADMIN (AKSES PENUH TERSANITASI)
        if (in_array($role, ['admin', 'superadmin'])) {
            $context['hak_akses'] = 'Administrator Sistem SFCS. Akses penuh untuk manajemen dan pemantauan sistem.';

            $context['rekap_pengaduan_global'] = [
                'pending'      => Pengaduan::where('status', 'pending')->count(),
                'diverifikasi' => Pengaduan::where('status', 'diverifikasi')->count(),
                'diproses'     => Pengaduan::where('status', 'diproses')->count(),
                'selesai'      => Pengaduan::where('status', 'selesai')->count(),
                'ditolak'      => Pengaduan::where('status', 'ditolak')->count(),
                'overload_delay'=> Pengaduan::where('is_overload_delayed', true)->whereIn('status', ['diverifikasi', 'diproses'])->count(),
            ];

            $context['daftar_teknisi_beban'] = User::where('role', 'teknisi')
                ->where('is_active', true)
                ->withCount(['assignedPengaduans as tiket_aktif' => function ($q) {
                    $q->whereIn('status', ['diverifikasi', 'diproses']);
                }])
                ->get()
                ->map(fn($t) => [
                    'id'          => $t->id,
                    'nama'        => $t->name,
                    'tiket_aktif' => $t->tiket_aktif,
                ]);

            $context['rekap_pinjaman'] = [
                'pending'   => Pinjaman::where('status', 'pending')->count(),
                'disetujui' => Pinjaman::where('status', 'disetujui')->count(),
                'dipinjam'  => Pinjaman::where('status', 'dipinjam')->count(),
                'terlambat' => Pinjaman::where('status', 'terlambat')->count(),
            ];

            // 10 Pengaduan aktif terbaru untuk quick monitoring
            $context['tiket_aktif_terbaru'] = Pengaduan::whereIn('status', ['pending', 'diverifikasi', 'diproses'])
                ->with(['kategori:id,nama', 'gedung:id,nama', 'teknisi:id,name', 'user:id,name'])
                ->latest()
                ->take(10)
                ->get()
                ->map(fn($p) => [
                    'kode'      => $p->kode_pengaduan,
                    'judul'     => $p->judul,
                    'pelapor'   => $p->user->name ?? '-',
                    'teknisi'   => $p->teknisi->name ?? 'Belum Ditugaskan',
                    'prioritas' => $p->prioritas ?? $p->urgensi,
                    'status'    => $p->status,
                ]);

            return $context;
        }

        return $context;
    }

    /**
     * Helper ringkasan statistik.
     */
    private function buildSummaryStats(string $role, ?int $userId): array
    {
        $base = [
            'total_pengaduan' => Pengaduan::count(),
            'selesai'         => Pengaduan::where('status', 'selesai')->count(),
            'sedang_proses'   => Pengaduan::whereIn('status', ['diverifikasi', 'diproses'])->count(),
        ];

        if (in_array($role, ['siswa', 'guru']) && $userId) {
            $base['pengaduan_saya'] = Pengaduan::where('user_id', $userId)->count();
            $base['pinjaman_saya']  = Pinjaman::where('user_id', $userId)->count();
        }

        if ($role === 'teknisi' && $userId) {
            $base['tugas_aktif_saya'] = Pengaduan::where('teknisi_id', $userId)->whereIn('status', ['diverifikasi', 'diproses'])->count();
        }

        return $base;
    }
}
