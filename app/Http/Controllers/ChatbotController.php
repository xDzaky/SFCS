<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Gedung;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\Pinjaman;
use App\Models\Setting;
use App\Models\User;
use App\Services\SlangNormalizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    private SlangNormalizerService $normalizer;

    public function __construct()
    {
        $this->normalizer = new SlangNormalizerService();
    }

    // ── Kata kunci TERLARANG — langsung ditolak tanpa proses lebih lanjut ──
    private const FORBIDDEN_KEYWORDS = [
        // Data sensitif user lain
        'password', 'kata sandi', 'sandi', 'hash', 'bcrypt',
        'email semua', 'semua email', 'daftar email', 'semua user',
        'semua pengguna', 'seluruh pengguna', 'data pengguna',
        'data user', 'list user', 'daftar user', 'semua akun',
        'username', 'nomor hp semua', 'kontak semua',
        // Aksi berbahaya
        'hapus data', 'drop table', 'delete from', 'truncate',
        'sql', 'inject', 'exploit', 'hack', 'bypass',
        // Data keuangan / pribadi orang lain
        'gaji', 'salary', 'rekening', 'transfer',
        // Token / key sistem
        'app_key', 'secret', 'token semua', 'api key',
        // Informasi role-restricted yang dicoba oleh role rendah
        'laporan teknisi semua', 'data admin',
    ];

    // ── Role hierarchy: index lebih tinggi = lebih banyak akses ──
    private const ROLE_LEVEL = [
        'siswa'      => 1,
        'guru'       => 2,
        'teknisi'    => 3,
        'kepsek'     => 4,
        'admin'      => 5,
        'superadmin' => 6,
    ];

    /**
     * Main entry point: receive a message, return a JSON reply.
     */
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $raw     = trim($request->input('message'));
        $user    = Auth::user();

        // ── 1. Detect tone BEFORE normalization (original text) ───────
        $isInformal = $this->normalizer->isInformal($raw);

        // ── 2. Expand abbreviations (clean up repeated chars) ─────────
        $expanded = $this->normalizer->expandAbbreviations($raw);

        // ── 3. Normalize slang → formal words ─────────────────────────
        $normalized = $this->normalizer->normalize($expanded);

        $lc = mb_strtolower($normalized);

        // ── 4. Security filter: blokir keyword berbahaya ──────────────
        // Check on BOTH original and normalized text
        if ($this->isForbidden(mb_strtolower($raw)) || $this->isForbidden($lc)) {
            return response()->json([
                'reply'   => "⛔ Permintaan ini tidak dapat diproses.\n\n"
                    . "Untuk keamanan, data sensitif pengguna tidak dapat diakses melalui chatbot.\n\n"
                    . "Jika ada kebutuhan khusus, hubungi Administrator secara langsung.",
                'type'    => 'blocked',
                'actions' => [],
            ]);
        }

        // ── 5. Dispatch ke handler berdasarkan intent & role ──────────
        $response = $this->dispatch($lc, $user, $raw, $isInformal);

        return response()->json([
            'reply'   => $response['reply'],
            'type'    => $response['type']    ?? 'text',
            'actions' => $response['actions'] ?? [],
        ]);
    }

    /**
     * Cek apakah pesan mengandung kata kunci terlarang.
     */
    private function isForbidden(string $lc): bool
    {
        foreach (self::FORBIDDEN_KEYWORDS as $kw) {
            if (str_contains($lc, $kw)) return true;
        }
        return false;
    }

    /**
     * Cek apakah user memiliki level role minimal yang dibutuhkan.
     */
    private function hasRole($user, string $minRole): bool
    {
        if (!$user) return false;
        $userLevel = self::ROLE_LEVEL[$user->role] ?? 0;
        $minLevel  = self::ROLE_LEVEL[$minRole]    ?? 99;
        return $userLevel >= $minLevel;
    }

    /**
     * Route pesan ke handler yang tepat.
     */
    private function dispatch(string $lc, $user, string $original, bool $isInformal = false): array
    {
        // — cara tolak pengaduan —
        if ($this->matches($lc, ['tolak pengaduan', 'cara tolak', 'menolak pengaduan', 'menolak laporan', 'alasan tolak', 'tolak'])) {
            return $this->handleTolakPengaduanInfo($user);
        }

        // — cara tugaskan teknisi (Admin) —
        if ($this->matches($lc, ['tugaskan teknisi', 'cara tugaskan', 'assign teknisi', 'bagi tugas', 'pilih teknisi', 'penugasan'])) {
            return $this->handleTugaskanTeknisiInfo($user);
        }

        // — urgensi / prioritas pengaduan —
        if ($this->matches($lc, ['urgensi', 'urgensinya', 'prioritas', 'prioritasnya', 'tingkat urgensi', 'skor dampak',
            'rendah', 'sedang', 'tinggi', 'urgent', 'darurat', 'tingkatan', 'level kerusakan',
            'cara ubah prioritas', 'edit urgensi', 'ubah urgensi', 'ganti prioritas'])) {
            return $this->handleUrgensiInfo($user);
        }

        // — laporan/ekspor (harus sebelum pengaduan umum agar keyword 'laporan' tidak salah tangkap) —
        if ($this->matches($lc, ['laporan sistem', 'laporan bulanan', 'ekspor', 'export', 'cetak laporan', 'unduh laporan', 'download laporan'])) {
            return $this->handleLaporanInfo($user);
        }

        // — jumlah / statistik pengaduan (semua role bisa tanya) —
        // Harus SEBELUM handler pengaduanInfo agar "ada berapa pengaduan" tidak salah tangkap
        if ($this->matches($lc, ['ada berapa pengaduan', 'berapa pengaduan', 'berapa banyak pengaduan',
            'jumlah pengaduan', 'total pengaduan', 'ringkasan pengaduan', 'rekap pengaduan',
            'statistik pengaduan', 'dashboard admin', 'berapa laporan'])) {
            return $this->handleStatistikPengaduan($user);
        }

        // — cara pengaduan / laporan fasilitas —
        if ($this->matches($lc, ['lapor', 'pengaduan', 'aduan', 'kerusakan', 'tidak berfungsi', 'laporan fasilitas', 'rusak', 'melaporkan', 'cara lapor', 'submit laporan', 'buat laporan', 'langkah', 'tutorial', 'bagaimana caranya', 'step', 'tata cara'])) {
            return $this->handlePengaduanInfo($user, $isInformal);
        }

        // — status pengaduan milik user sendiri —
        if ($this->matches($lc, ['status pengaduan', 'pengaduan saya', 'laporan saya', 'perkembangan', 'update pengaduan', 'progres', 'diproses belum', 'udah diproses', 'sudah diproses', 'belum selesai', 'kapan selesai'])) {
            return $this->handleMyPengaduan($user, $isInformal);
        }

        // — tracking via kode —
        if ($this->matches($lc, ['lacak', 'adu-', 'kode pengaduan', 'tracking', 'nomor laporan'])) {
            return $this->handleTrackByKode($lc, $user);
        }

        // — cara edit / ubah pengaduan —
        if ($this->matches($lc, ['edit pengaduan', 'ubah pengaduan', 'cara edit', 'ubah laporan', 'revisi pengaduan'])) {
            return $this->handleEditPengaduanInfo($user);
        }

        // — cara buka ulang pengaduan —
        if ($this->matches($lc, ['buka ulang', 'reopen', 'buka kembali', 'aktifkan kembali', 'pengaduan selesai tapi belum beres'])) {
            return $this->handleReopenInfo($user);
        }

        // — feedback / penilaian —
        if ($this->matches($lc, ['feedback', 'rating', 'nilai', 'beri penilaian', 'kasih bintang', 'ulasan', 'review pengaduan'])) {
            return $this->handleFeedbackInfo($user);
        }

        // — cara pinjam / permintaan barang —
        if ($this->matches($lc, ['cara pinjam', 'pinjam barang', 'peminjaman', 'langkah pinjam', 'tutorial pinjam',
            'prosedur pinjam', 'bagaimana pinjam', 'mau pinjam', 'ingin pinjam',
            'cara minta barang', 'minta barang', 'permintaan barang', 'cara ajukan', 'prosedur sarpras',
            'sarpras atas', 'sarpras bawah', 'sarpras', 'cara ke sarpras'])) {
            return $this->handlePinjamanInfo($user, $isInformal);
        }

        // — stok / daftar barang —
        if ($this->matches($lc, ['stok', 'stock', 'cek barang', 'barang tersedia', 'inventaris',
            'ketersediaan', 'daftar barang', 'ada barang', 'available', 'barang apa saja',
            'barang apa aja', 'barang yang bisa dipinjam', 'apa saja yang bisa dipinjam'])) {
            return $this->handleBarangInfo($user);
        }

        // — pinjaman milik user sendiri —
        if ($this->matches($lc, ['pinjaman saya', 'status pinjaman', 'kembalikan', 'pengembalian', 'batas waktu'])) {
            return $this->handleMyPinjaman($user, $isInformal);
        }

        // — kategori kerusakan —
        if ($this->matches($lc, ['kategori', 'jenis kerusakan', 'jenis laporan', 'jenis fasilitas', 'macam kerusakan'])) {
            return $this->handleKategoriInfo();
        }

        // — gedung / ruangan —
        if ($this->matches($lc, ['gedung', 'ruangan', 'lokasi gedung', 'denah', 'peta', 'laboratorium', 'perpustakaan', 'kantin'])) {
            return $this->handleGedungInfo();
        }

        // ── FITUR KHUSUS TEKNISI ─────────────────────────────────────
        if ($this->matches($lc, ['tugas saya', 'pengaduan ditugaskan', 'antrian teknisi', 'queue teknisi', 'beban tugas'])) {
            return $this->handleTeknisiTugas($user);
        }

        // ── FITUR KHUSUS ADMIN & SUPERADMIN ─────────────────────────
        if ($this->matches($lc, ['ringkasan pengaduan', 'rekap pengaduan', 'statistik pengaduan', 'dashboard admin', 'total pengaduan'])) {
            return $this->handleStatistikPengaduan($user); // sudah handle semua role
        }

        if ($this->matches($lc, ['kelola pengguna', 'daftar pengguna', 'kelola user', 'tambah user', 'hapus user', 'manajemen user', 'akun pengguna'])) {
            return $this->handleUserManagementInfo($user);
        }

        if ($this->matches($lc, ['overload', 'penumpukan', 'antrean teknisi', 'teknisi kewalahan'])) {
            return $this->handleOverloadInfo($user);
        }

        if ($this->matches($lc, ['rekap pinjaman', 'statistik pinjaman', 'laporan pinjaman', 'data pinjaman'])) {
            return $this->handleAdminPinjaman($user);
        }

        // ── FITUR KHUSUS KEPSEK ──────────────────────────────────────
        if ($this->matches($lc, ['laporan performa', 'performa teknisi', 'sla', 'kinerja'])) {
            return $this->handleKepsekPerforma($user);
        }

        // — notifikasi —
        if ($this->matches($lc, ['notifikasi', 'notification', 'pemberitahuan'])) {
            return $this->handleNotifikasi();
        }

        // — kontak / profil sekolah —
        if ($this->matches($lc, ['kontak', 'hubungi', 'admin kontak', 'sekolah'])) {
            return $this->handleKontak();
        }

        // — bantuan / help / salam —
        if ($this->matches($lc, ['bantuan', 'help', 'menu', 'fitur', 'bisa apa', 'halo', 'selamat', 'tanya', 'informasi', 'perlu diketahui'])) {
            return $this->handleHelp($user, $isInformal);
        }

        // — fallback dengan tone-aware —
        return $this->handleFallback($user, $isInformal);
    }

    // ═══════════════════════════════════════════════════════════════════
    // HANDLERS — SEMUA ROLE
    // ═══════════════════════════════════════════════════════════════════

    private function handlePengaduanInfo($user, bool $isInformal = false): array
    {
        $kategoris = Kategori::where('is_active', true)->take(6)->get();
        $kategoriList = $kategoris->isEmpty()
            ? '(belum ada kategori aktif)'
            : $kategoris->map(fn($k) => "• {$k->nama}")->join("\n");

        if ($this->hasRole($user, 'siswa') && !$this->hasRole($user, 'teknisi')) {
            $intro = $isInformal
                ? "Nih cara lapor fasilitas sekolah yang rusak, gampang banget! 🙌\n\n"
                : "📋 **Cara Membuat Pengaduan Fasilitas:**\n\n";

            $reply = $intro
                . "1️⃣ Klik **\"Buat Pengaduan\"** di menu sidebar kiri\n"
                . "2️⃣ Pilih **Kategori** jenis kerusakan\n"
                . "3️⃣ Pilih **Gedung & Ruangan** lokasi kerusakan\n"
                . "4️⃣ Isi **judul** dan **deskripsi** kerusakan secara jelas\n"
                . "5️⃣ Upload **foto bukti** kerusakan (sangat disarankan)\n"
                . "6️⃣ Klik **Kirim Pengaduan**\n\n"
                . "📂 **Kategori tersedia:**\n{$kategoriList}\n\n"
                . ($isInformal
                    ? "⚠️ BTW, kalau udah pernah ada laporan di tempat yang sama, sistem bakal otomatis deteksi duplikat ya!"
                    : "⚠️ Sistem akan otomatis mendeteksi laporan duplikat di lokasi yang sama.");

            return [
                'reply'   => $reply,
                'actions' => [
                    ['label' => '📝 Buat Pengaduan Sekarang', 'url' => route('pengaduan.create')],
                    ['label' => '📋 Lihat Pengaduan Saya',    'url' => route('pengaduan.index')],
                ],
            ];
        }

        if ($this->hasRole($user, 'teknisi') && !$this->hasRole($user, 'admin')) {
            return [
                'reply' => "🔧 Sebagai teknisi, kamu menerima tugas dari Admin.\n\n"
                    . "**Alur kerjamu:**\n"
                    . "1️⃣ Terima notifikasi tugas pengaduan baru\n"
                    . "2️⃣ Buka detail pengaduan → ubah status ke **Diproses**\n"
                    . "3️⃣ Kalau butuh reschedule, isi alasan & jadwal baru\n"
                    . "4️⃣ Setelah selesai → klik **Tandai Selesai**\n\n"
                    . "📂 **Kategori kerusakan:**\n{$kategoriList}",
                'actions' => [
                    ['label' => '📋 Lihat Tugas Saya', 'url' => route('teknisi.pengaduan.index')],
                ],
            ];
        }

        $pending = Pengaduan::where('status', 'pending')->count();
        $aktif   = Pengaduan::whereIn('status', ['diverifikasi', 'diproses'])->count();
        $selesai = Pengaduan::where('status', 'selesai')->count();
        $total   = Pengaduan::count();

        return [
            'reply'   => "📋 **Status Pengaduan Keseluruhan (Admin View):**\n\n"
                . "⏳ Menunggu Verifikasi: **{$pending}** tiket\n"
                . "🔧 Sedang Ditangani Teknisi: **{$aktif}** tiket\n"
                . "✔️ Selesai: **{$selesai}** tiket\n"
                . "📊 Total Laporan Masuk: **{$total}** tiket\n\n"
                . "📂 **Kategori Aktif:**\n{$kategoriList}\n\n"
                . "💡 *Tips: Buka menu **Kelola Pengaduan** untuk memverifikasi, menugaskan teknisi, atau menolak laporan.*",
            'actions' => [
                ['label' => '📋 Kelola Pengaduan', 'url' => route('admin.pengaduan.index')],
            ],
        ];
    }

    private function handleMyPengaduan($user, bool $isInformal = false): array
    {
        if ($this->hasRole($user, 'teknisi') && !$this->hasRole($user, 'admin')) {
            return $this->handleTeknisiTugas($user);
        }

        if ($this->hasRole($user, 'admin')) {
            return $this->handleAdminRekap($user);
        }

        if (!$user || !in_array($user->role, ['siswa', 'guru'])) {
            return ['reply' => 'Fitur ini hanya tersedia untuk siswa dan guru.'];
        }

        $pengaduans = Pengaduan::where('user_id', $user->id)->latest()->take(5)->get();

        if ($pengaduans->isEmpty()) {
            $msg = $isInformal
                ? "📋 Belum ada pengaduan nih dari kamu.\nGas bikin sekarang kalau ada fasilitas yang rusak! 💪"
                : "📋 Kamu belum memiliki pengaduan.\n\nYuk buat pengaduan jika ada fasilitas yang perlu diperbaiki!";
            return [
                'reply'   => $msg,
                'actions' => [['label' => '📝 Buat Pengaduan', 'url' => route('pengaduan.create')]],
            ];
        }

        $emoji = ['pending' => '⏳', 'diverifikasi' => '✅', 'diproses' => '🔧', 'selesai' => '✔️', 'ditolak' => '❌'];
        $list  = $pengaduans->map(function ($p) use ($emoji) {
            $e   = $emoji[$p->status] ?? '•';
            $tgl = $p->created_at->format('d M');
            return "{$e} [{$p->kode_pengaduan}] {$p->judul} — *" . ucfirst($p->status) . "* ({$tgl})";
        })->join("\n");

        $header = $isInformal
            ? "Ini pengaduan terbaru kamu ({$pengaduans->count()}) nih 👇\n\n"
            : "📋 **Pengaduan terbaru kamu ({$pengaduans->count()}):**\n\n";

        return [
            'reply'   => $header . $list,
            'actions' => [['label' => '📋 Lihat Semua', 'url' => route('pengaduan.index')]],
        ];
    }

    private function handleTrackByKode(string $lc, $user): array
    {
        preg_match('/adu-\d{8}-\d{3}/i', $lc, $m);

        if (!empty($m)) {
            $kode = strtoupper($m[0]);
            $p    = Pengaduan::where('kode_pengaduan', $kode)->with('teknisi')->first();

            if (!$p) {
                return ['reply' => "❌ Kode **{$kode}** tidak ditemukan. Pastikan kode benar (format: ADU-YYYYMMDD-XXX)."];
            }

            // Siswa/Guru hanya bisa track milik sendiri
            if (in_array($user->role ?? '', ['siswa', 'guru']) && $p->user_id !== $user->id) {
                return ['reply' => "⛔ Kamu hanya dapat melihat pengaduan milikmu sendiri."];
            }

            $teknisi = $p->teknisi ? $p->teknisi->name : 'Belum ditugaskan';
            $status  = ucfirst($p->status);

            // Admin & teknisi bisa lihat lebih detail
            $extraInfo = '';
            if ($this->hasRole($user, 'teknisi')) {
                $extraInfo = "\n📅 SLA Due: " . (optional($p->sla_due_at)->format('d M Y H:i') ?? '-');
            }

            $reply = "🔍 **Tracking {$kode}:**\n\n"
                . "📌 {$p->judul}\n"
                . "📊 Status: **{$status}**\n"
                . "👷 Teknisi: {$teknisi}\n"
                . "📅 Dibuat: {$p->created_at->format('d M Y, H:i')}"
                . $extraInfo;

            $url = in_array($user->role ?? '', ['siswa', 'guru'])
                ? route('pengaduan.show', $kode)
                : route('admin.pengaduan.show', $kode);

            return [
                'reply'   => $reply,
                'actions' => [['label' => '🔍 Buka Detail', 'url' => $url]],
            ];
        }

        return [
            'reply'   => "🔍 Masukkan kode pengaduan untuk melacak, contoh:\n**ADU-20260910-001**\n\nAtau gunakan halaman tracking publik:",
            'actions' => [['label' => '🔍 Halaman Tracking', 'url' => route('pengaduan.track')]],
        ];
    }

    private function handlePinjamanInfo($user, bool $isInformal = false): array
    {
        if ($this->hasRole($user, 'admin')) {
            $pending  = Pinjaman::where('status', 'pending')->count();
            $aktif    = Pinjaman::where('status', 'disetujui')->count();
            $dipinjam = Pinjaman::where('status', 'dipinjam')->count();
            return [
                'reply'   => "📦 **Status Pengajuan (Admin View):**\n\n"
                    . "⏳ Menunggu persetujuan: **{$pending}**\n"
                    . "✅ Disetujui (belum diambil): **{$aktif}**\n"
                    . "🔄 Sedang dipinjam: **{$dipinjam}**",
                'actions' => [['label' => '📦 Kelola Pengajuan', 'url' => route('admin.pinjaman.index')]],
            ];
        }

        $intro = $isInformal
            ? "Di SFCS ada 2 cara ngajuin barang ke Sarpras 👇\n\n"
            : "📦 **Cara Mengajukan ke Sarpras:**\n\n";

        $reply = $intro
            . "**🔵 Sarpras Atas — Peminjaman** *(Proyektor, Kabel, Mic, Speaker, dll)*\n"
            . "1️⃣ Buka menu **\"Pinjam & Minta Barang\"** → Pilih barang Sarpras Atas\n"
            . "2️⃣ Isi jumlah & durasi pinjam\n"
            . "3️⃣ Tunggu persetujuan Admin\n"
            . "4️⃣ Ambil barang di Sarpras Atas *(Check-out)*\n"
            . "5️⃣ Kembalikan tepat waktu *(Check-in)*\n\n"
            . "**🟢 Sarpras Bawah — Permintaan** *(Kertas HVS, Spidol, ATK, dll)*\n"
            . "1️⃣ Buka menu **\"Pinjam & Minta Barang\"** → Pilih barang Sarpras Bawah\n"
            . "2️⃣ Isi jumlah yang dibutuhkan & keperluan\n"
            . "3️⃣ Tunggu persetujuan Admin\n"
            . "4️⃣ Ambil barang di Sarpras Bawah *(tidak perlu dikembalikan)*\n\n"
            . ($isInformal
                ? "⚠️ Inget, jam pulang **15.20** — jangan telat balikin barang pinjaman ya!"
                : "⚠️ Jam pulang sekolah **15.20** — jadikan patokan waktu pengembalian barang pinjaman.");

        return [
            'reply'   => $reply,
            'actions' => [
                ['label' => '📦 Ajukan Pinjaman/Permintaan', 'url' => route('pinjaman.create')],
                ['label' => '📋 Pengajuan Saya',             'url' => route('pinjaman.index')],
            ],
        ];
    }

    private function handleBarangInfo($user): array
    {
        $query = Barang::query();
        if (!$this->hasRole($user, 'admin')) {
            $query->where('is_active', true)->where('stok_tersedia', '>', 0);
        }
        $barangs = $query->orderBy('unit_sarpras')->orderBy('nama')->take(12)->get();

        if ($barangs->isEmpty()) {
            return [
                'reply'   => "📦 Belum ada barang yang tersedia saat ini. Hubungi Admin Sarana.",
                'actions' => $this->hasRole($user, 'admin')
                    ? [['label' => '📦 Kelola Master Barang', 'url' => route('admin.barangs.index')]]
                    : [],
            ];
        }

        $atas  = $barangs->where('unit_sarpras', 'atas');
        $bawah = $barangs->where('unit_sarpras', 'bawah');

        $formatItem = fn($b) => "• **{$b->nama}** — Tersedia: **{$b->stok_tersedia}**"
            . ($b->kategori ? " *({$b->kategori})*" : '');

        $listAtas  = $atas->map($formatItem)->join("\n") ?: '*(kosong)*';
        $listBawah = $bawah->map($formatItem)->join("\n") ?: '*(kosong)*';

        $reply = "📦 **Daftar Barang Inventaris:**\n\n"
            . "**🔵 Sarpras Atas** *(Peminjaman — dikembalikan)*\n{$listAtas}\n\n"
            . "**🟢 Sarpras Bawah** *(Permintaan — tidak dikembalikan)*\n{$listBawah}\n\n"
            . ($this->hasRole($user, 'admin')
                ? "Kelola stok dan barang melalui menu Master Barang."
                : "Pilih barang sesuai kebutuhan dan ajukan melalui menu Pinjam & Minta Barang.");

        return [
            'reply'   => $reply,
            'actions' => $this->hasRole($user, 'admin')
                ? [['label' => '📦 Kelola Master Barang', 'url' => route('admin.barangs.index')]]
                : [['label' => '📦 Ajukan Sekarang', 'url' => route('pinjaman.create')]],
        ];
    }

    private function handleMyPinjaman($user, bool $isInformal = false): array
    {
        if (!$user || !in_array($user->role, ['siswa', 'guru'])) {
            if ($this->hasRole($user, 'admin')) {
                return $this->handleAdminPinjaman($user);
            }
            return ['reply' => 'Fitur ini hanya tersedia untuk siswa dan guru.'];
        }

        $pinjamans = Pinjaman::where('user_id', $user->id)
            ->with('barang')
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->latest()->take(4)->get();

        if ($pinjamans->isEmpty()) {
            $msg = $isInformal
                ? "📦 Belum ada pinjaman aktif nih.\nMau pinjem sekarang?"
                : "📦 Tidak ada pinjaman aktif saat ini.";
            return [
                'reply'   => $msg,
                'actions' => [['label' => '📦 Pinjam Barang', 'url' => route('pinjaman.create')]],
            ];
        }

        $list = $pinjamans->map(function ($p) {
            $tgl = optional($p->tanggal_kembali)->format('d M Y H:i') ?? '-';
            return "• **{$p->barang->nama}** — *" . ucfirst($p->status) . "* (Kembali: {$tgl})";
        })->join("\n");

        $header = $isInformal
            ? "Ini pinjaman aktif kamu nih 👇\n\n"
            : "📦 **Pinjaman aktif kamu:**\n\n";

        return [
            'reply'   => $header . $list,
            'actions' => [['label' => '📋 Detail Pinjaman', 'url' => route('pinjaman.index')]],
        ];
    }

    private function handleKategoriInfo(): array
    {
        $kategoris = Kategori::where('is_active', true)->with('subKategoris')->get();

        if ($kategoris->isEmpty()) {
            return ['reply' => "Belum ada kategori aktif. Hubungi Admin."];
        }

        $list = $kategoris->map(function ($k) {
            $subs = $k->subKategoris->isNotEmpty()
                ? ' (' . $k->subKategoris->take(3)->pluck('nama')->join(', ') . ')'
                : '';
            return "• **{$k->nama}**{$subs}";
        })->join("\n");

        return [
            'reply'   => "📂 **Kategori Kerusakan yang Tersedia:**\n\n{$list}\n\nPilih kategori yang sesuai saat membuat pengaduan.",
            'actions' => [['label' => '📝 Buat Pengaduan', 'url' => route('pengaduan.create')]],
        ];
    }

    private function handleGedungInfo(): array
    {
        $gedungs = Gedung::with('ruangans')->take(8)->get();

        if ($gedungs->isEmpty()) {
            return ['reply' => "Data gedung belum tersedia. Hubungi Admin."];
        }

        $list = $gedungs->map(fn($g) => "• **{$g->nama}** ({$g->ruangans->count()} ruangan)")->join("\n");

        return ['reply' => "🏫 **Daftar Gedung:**\n\n{$list}\n\nSaat membuat pengaduan, pilih gedung dan ruangan lokasi kerusakan."];
    }

    // ═══════════════════════════════════════════════════════════════════
    // HANDLERS — TEKNISI ONLY
    // ═══════════════════════════════════════════════════════════════════

    private function handleTeknisiTugas($user): array
    {
        if (!$this->hasRole($user, 'teknisi')) {
            return ['reply' => "⛔ Fitur ini hanya tersedia untuk Teknisi."];
        }

        if ($this->hasRole($user, 'admin')) {
            return $this->handleAdminRekap($user);
        }

        $aktif   = Pengaduan::where('teknisi_id', $user->id)->whereIn('status', ['diverifikasi', 'diproses'])->count();
        $selesai = Pengaduan::where('teknisi_id', $user->id)->where('status', 'selesai')
            ->whereDate('completed_at', today())->count();
        $overload = Pengaduan::where('teknisi_id', $user->id)->where('is_overload_delayed', true)->count();

        return [
            'reply'   => "🔧 **Ringkasan Tugas Kamu (Teknisi):**\n\n"
                . "📋 Aktif/diproses: **{$aktif}** tiket\n"
                . "✔️ Selesai hari ini: **{$selesai}** tiket\n"
                . "⚠️ Indikasi overload: **{$overload}** tiket",
            'actions' => [['label' => '📋 Buka Daftar Tugas', 'url' => route('teknisi.pengaduan.index')]],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    // HANDLERS — ADMIN & SUPERADMIN ONLY
    // ═══════════════════════════════════════════════════════════════════

    private function handleTolakPengaduanInfo($user): array
    {
        if (!$this->hasRole($user, 'admin')) {
            return [
                'reply' => "ℹ️ Penolakan laporan pengaduan hanya dapat dilakukan oleh **Admin Sarana**.\n\n"
                    . "Pengaduan yang diajukan oleh siswa/guru akan diverifikasi terlebih dahulu oleh pihak Sarpras. Jika tidak memenuhi kriteria, pengaduan dapat ditolak dengan alasan yang tercantum pada detail laporan.",
            ];
        }

        return [
            'reply' => "❌ **Panduan Menolak Pengaduan (Admin Sarana):**\n\n"
                . "1️⃣ Buka menu **Kelola Pengaduan** di sidebar kiri\n"
                . "2️⃣ Klik tiket pengaduan yang ingin ditolak\n"
                . "3️⃣ Scroll ke bagian bawah halaman detail, temukan kartu merah **\"Tolak Pengaduan\"**\n"
                . "4️⃣ Masukkan **alasan penolakan** secara jelas pada kolom teks (wajib diisi)\n"
                . "5️⃣ Klik tombol merah **\"Tolak Pengaduan\"**\n\n"
                . "🔔 *Sistem otomatis mengirimkan notifikasi penolakan beserta alasannya kepada pelapor (siswa/guru).*",
            'actions' => [
                ['label' => '📋 Buka Kelola Pengaduan', 'url' => route('admin.pengaduan.index')],
            ],
        ];
    }

    private function handleTugaskanTeknisiInfo($user): array
    {
        if (!$this->hasRole($user, 'admin')) {
            return [
                'reply' => "ℹ️ Penugasan teknisi dilakukan oleh **Admin Sarana**.\n\n"
                    . "Setelah pengaduan diverifikasi, Admin akan menunjuk teknisi yang berwenang untuk menangani fasilitas yang rusak.",
            ];
        }

        return [
            'reply' => "👷 **Panduan Menugaskan Teknisi (Admin Sarana):**\n\n"
                . "1️⃣ Buka menu **Kelola Pengaduan** di sidebar kiri\n"
                . "2️⃣ Klik tiket pengaduan yang berstatus **\"Menunggu Verifikasi\"**\n"
                . "3️⃣ Pada halaman detail, temukan kartu **\"Tugaskan Teknisi\"**\n"
                . "4️⃣ Pilih nama teknisi yang bertugas (Ahmad Teknisi / Budi / Cahyo) pada dropdown\n"
                . "5️⃣ Klik tombol hijau **\"Tugaskan Teknisi\"**\n\n"
                . "🔔 *Status tiket otomatis menjadi **Diverifikasi**, dan laporan akan langsung muncul di **Dashboard & Tugas Saya** teknisi tersebut.*",
            'actions' => [
                ['label' => '📋 Buka Kelola Pengaduan', 'url' => route('admin.pengaduan.index')],
            ],
        ];
    }

    private function handleAdminRekap($user): array
    {
        if (!$this->hasRole($user, 'admin')) {
            return ['reply' => "⛔ Fitur ringkasan admin hanya tersedia untuk Admin dan Super Admin."];
        }

        $pending      = Pengaduan::where('status', 'pending')->count();
        $diverifikasi = Pengaduan::where('status', 'diverifikasi')->count();
        $diproses     = Pengaduan::where('status', 'diproses')->count();
        $selesaiHari  = Pengaduan::where('status', 'selesai')->whereDate('completed_at', today())->count();
        $totalUser    = User::where('is_active', true)->count();

        return [
            'reply'   => "📊 **Ringkasan Sistem (Admin View):**\n\n"
                . "⏳ Menunggu verifikasi: **{$pending}**\n"
                . "✅ Terverifikasi: **{$diverifikasi}**\n"
                . "🔧 Sedang diproses: **{$diproses}**\n"
                . "✔️ Selesai hari ini: **{$selesaiHari}**\n"
                . "👥 Pengguna aktif: **{$totalUser}**",
            'actions' => [
                ['label' => '📋 Kelola Pengaduan', 'url' => route('admin.pengaduan.index')],
                ['label' => '📊 Lihat Laporan',    'url' => route('admin.reports.index')],
            ],
        ];
    }

    private function handleOverloadInfo($user): array
    {
        if (!$this->hasRole($user, 'admin')) {
            return ['reply' => "⛔ Informasi overload hanya tersedia untuk Admin."];
        }

        $overload = Pengaduan::where('is_overload_delayed', true)
            ->whereIn('status', ['diverifikasi', 'diproses'])->count();

        return [
            'reply'   => "⚠️ **Status Overload Teknisi:**\n\nTiket terindikasi overload: **{$overload}**\n\n"
                . "Gunakan Overload Board untuk memantau dan mendistribusikan ulang beban kerja teknisi.",
            'actions' => [['label' => '⚠️ Buka Overload Board', 'url' => route('admin.overload-board')]],
        ];
    }

    private function handleAdminPinjaman($user): array
    {
        if (!$this->hasRole($user, 'admin')) {
            return ['reply' => "⛔ Data pinjaman keseluruhan hanya tersedia untuk Admin."];
        }

        $menunggu  = Pinjaman::where('status', 'menunggu')->count();
        $aktif     = Pinjaman::where('status', 'disetujui')->count();
        $terlambat = Pinjaman::where('status', 'terlambat')->count();

        return [
            'reply'   => "📦 **Rekap Pinjaman (Admin View):**\n\n"
                . "⏳ Menunggu persetujuan: **{$menunggu}**\n"
                . "✅ Sedang dipinjam: **{$aktif}**\n"
                . "🚨 Terlambat dikembalikan: **{$terlambat}**",
            'actions' => [['label' => '📦 Kelola Pinjaman', 'url' => route('admin.pinjaman.index')]],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    // HANDLERS — KEPSEK ONLY
    // ═══════════════════════════════════════════════════════════════════

    private function handleKepsekPerforma($user): array
    {
        if (!$this->hasRole($user, 'kepsek')) {
            return ['reply' => "⛔ Data performa teknisi hanya tersedia untuk Kepala Sekolah, Admin, dan Super Admin."];
        }

        $total      = Pengaduan::whereMonth('created_at', now()->month)->count();
        $selesai    = Pengaduan::where('status', 'selesai')->whereMonth('completed_at', now()->month)->count();
        $overdueSla = Pengaduan::whereNotNull('sla_due_at')->where('sla_due_at', '<', now())
            ->whereNotIn('status', ['selesai', 'ditolak'])->count();

        return [
            'reply'   => "📊 **Performa Bulan Ini:**\n\n"
                . "📋 Total pengaduan masuk: **{$total}**\n"
                . "✔️ Sudah selesai: **{$selesai}**\n"
                . "⚠️ Melewati SLA: **{$overdueSla}** tiket\n\n"
                . "Gunakan halaman Laporan untuk analisis lebih mendalam.",
            'actions' => [['label' => '📊 Buka Laporan', 'url' => route('kepsek.reports')]],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    // HANDLERS — UMUM
    // ═══════════════════════════════════════════════════════════════════

    private function handleNotifikasi(): array
    {
        return [
            'reply'   => "🔔 **Sistem Notifikasi SFCS:**\n\n"
                . "Kamu akan mendapat notifikasi saat:\n"
                . "• ✅ Pengaduanmu **diverifikasi** Admin\n"
                . "• 👷 Pengaduanmu **ditugaskan** ke teknisi\n"
                . "• 🔧 Teknisi mulai **memproses** pengaduanmu\n"
                . "• ✔️ Pengaduanmu dinyatakan **selesai**\n"
                . "• 📅 Jadwal perbaikan **dijadwalkan ulang**\n"
                . "• 📦 Pinjaman **disetujui / ditolak**\n\n"
                . "Cek ikon 🔔 di kanan atas halaman.",
            'actions' => [['label' => '🔔 Lihat Notifikasi', 'url' => route('notifications.index')]],
        ];
    }

    private function handleKontak(): array
    {
        $schoolName = Setting::getValue('school_name', 'SFCS');
        return [
            'reply' => "🏫 **{$schoolName}**\n\n"
                . "Untuk pertanyaan lebih lanjut, hubungi:\n"
                . "• Admin — melalui halaman Admin\n"
                . "• Kepala Sekolah — untuk laporan darurat\n\n"
                . "Jam operasional: **Senin–Sabtu, 07.00–16.00**",
        ];
    }

    private function handleHelp($user, bool $isInformal = false): array
    {
        $name = $user ? $user->name : 'User';
        $role = $user ? ucfirst($user->role) : '';

        $greeting = $isInformal
            ? "Heyy **{$name}**! 👋 Aku asisten virtual SFCS.\nMau tanya apaan? Nih yang bisa aku bantu:\n\n"
            : "👋 Halo, **{$name}** *(Role: {$role})*!\n\nSaya asisten virtual SFCS. Yang bisa saya bantu:\n\n";

        $menu = "📋 **Pengaduan:** \"cara lapor\", \"status pengaduan saya\"\n"
            . "📦 **Pinjaman:** \"cara pinjam\", \"cek stok barang\"\n"
            . "📂 **Info:** \"kategori kerusakan\", \"daftar gedung\"\n"
            . "🔍 **Tracking:** ketik kode (contoh: ADU-20260910-001)\n";

        if ($this->hasRole($user, 'teknisi') && !$this->hasRole($user, 'admin')) {
            $menu .= "🔧 **Teknisi:** \"tugas saya\", \"antrian teknisi\"\n";
        }
        if ($this->hasRole($user, 'admin')) {
            $menu .= "📊 **Admin:** \"ringkasan pengaduan\", \"rekap pinjaman\", \"overload\"\n";
        }
        if ($this->hasRole($user, 'kepsek')) {
            $menu .= "📈 **Kepsek:** \"laporan performa\", \"kinerja teknisi\"\n";
        }

        $outro = $isInformal ? "\nGas tanya aja langsung! 😄" : "\nKetik pertanyaanmu! 😊";

        return ['reply' => $greeting . $menu . $outro];
    }

    private function handleFallback($user, bool $isInformal = false): array
    {
        if ($isInformal) {
            return [
                'reply' => "Hmm, aku kurang nangkep nih maksudnya 😅\n\n"
                    . "Coba tanya pake kata kunci ini ya:\n"
                    . "• \"cara lapor\" kalau mau laporin kerusakan\n"
                    . "• \"status pengaduan saya\" buat ngecek laporan\n"
                    . "• \"urgensi\" buat tau soal prioritas pengaduan\n"
                    . "• \"cara pinjem\" kalau mau pinjem barang\n"
                    . "• \"stok barang\" buat ngecek barang tersedia\n\n"
                    . "Atau ketik **\"bantuan\"** buat liat semua fitur! 💪",
            ];
        }

        return [
            'reply' => "Maaf, saya belum memahami pertanyaan tersebut. 😅\n\n"
                . "Coba tanyakan:\n"
                . "• \"Cara membuat pengaduan\"\n"
                . "• \"Status pengaduan saya\"\n"
                . "• \"Apa itu urgensi / prioritas pengaduan\"\n"
                . "• \"Cara pinjam barang\"\n"
                . "• \"Cek stok barang\"\n\n"
                . "Atau ketik **\"bantuan\"** untuk melihat semua topik.",
        ];
    }

    // ─── New Handlers ─────────────────────────────────────────────────

    /**
     * Statistik pengaduan — accessible untuk semua role, konten disesuaikan per role.
     */
    private function handleStatistikPengaduan($user): array
    {
        $total   = Pengaduan::count();
        $pending = Pengaduan::where('status', 'pending')->count();
        $proses  = Pengaduan::whereIn('status', ['diverifikasi', 'diproses'])->count();
        $selesai = Pengaduan::where('status', 'selesai')->count();
        $ditolak = Pengaduan::where('status', 'ditolak')->count();

        // ── Teknisi ONLY (level 3, bukan kepsek/admin): tampilkan statistik + tugas miliknya ──
        if ($this->hasRole($user, 'teknisi') && !$this->hasRole($user, 'kepsek')) {
            $myTotal   = Pengaduan::where('teknisi_id', $user->id)->count();
            $myAktif   = Pengaduan::where('teknisi_id', $user->id)->whereIn('status', ['diverifikasi', 'diproses'])->count();
            $mySelesai = Pengaduan::where('teknisi_id', $user->id)->where('status', 'selesai')->count();

            return [
                'reply' => "📊 **Statistik Pengaduan di Sistem:**\n\n"
                    . "📋 Total pengaduan masuk: **{$total}**\n"
                    . "⏳ Menunggu verifikasi: **{$pending}**\n"
                    . "🔧 Sedang diproses: **{$proses}**\n"
                    . "✅ Selesai: **{$selesai}**\n"
                    . "❌ Ditolak: **{$ditolak}**\n\n"
                    . "🔧 **Tugas yang ditugaskan ke kamu:**\n"
                    . "• Total tugas: **{$myTotal}**\n"
                    . "• Sedang aktif: **{$myAktif}**\n"
                    . "• Sudah selesai: **{$mySelesai}**",
                'actions' => [
                    ['label' => '📋 Lihat Tugas Saya', 'url' => route('teknisi.pengaduan.index')],
                ],
            ];
        }

        // ── Siswa / Guru (level 1-2): tampilkan statistik umum + laporan miliknya ─
        if (!$this->hasRole($user, 'kepsek')) {
            $myTotal   = Pengaduan::where('user_id', $user->id)->count();
            $myAktif   = Pengaduan::where('user_id', $user->id)->whereIn('status', ['pending', 'diverifikasi', 'diproses'])->count();
            $mySelesai = Pengaduan::where('user_id', $user->id)->where('status', 'selesai')->count();

            return [
                'reply' => "📊 **Statistik Pengaduan:**\n\n"
                    . "📋 Total pengaduan di sistem: **{$total}**\n"
                    . "⏳ Menunggu verifikasi: **{$pending}**\n"
                    . "🔧 Sedang diproses: **{$proses}**\n"
                    . "✅ Selesai: **{$selesai}**\n\n"
                    . "📁 **Laporan kamu:**\n"
                    . "• Total laporan kamu: **{$myTotal}**\n"
                    . "• Masih diproses: **{$myAktif}**\n"
                    . "• Sudah selesai: **{$mySelesai}**",
                'actions' => [
                    ['label' => '📋 Lihat Laporan Saya', 'url' => route('pengaduan.index')],
                ],
            ];
        }

        // ── Admin / Superadmin / Kepsek: tampilkan data lengkap ────────
        $diverifikasi = Pengaduan::where('status', 'diverifikasi')->count();
        $diprosesSaja = Pengaduan::where('status', 'diproses')->count();
        $selesaiHari  = Pengaduan::where('status', 'selesai')->whereDate('completed_at', today())->count();
        $totalUser    = User::where('is_active', true)->count();

        $actions = [
            ['label' => '📋 Kelola Pengaduan', 'url' => route('admin.pengaduan.index')],
        ];
        if ($this->hasRole($user, 'admin')) {
            $actions[] = ['label' => '📊 Lihat Laporan', 'url' => route('admin.reports.index')];
        }

        return [
            'reply' => "📊 **Statistik Pengaduan Sistem:**\n\n"
                . "📋 Total pengaduan masuk: **{$total}**\n"
                . "⏳ Menunggu verifikasi: **{$pending}**\n"
                . "✅ Terverifikasi: **{$diverifikasi}**\n"
                . "🔧 Sedang diproses: **{$diprosesSaja}**\n"
                . "✔️ Selesai: **{$selesai}**\n"
                . "❌ Ditolak: **{$ditolak}**\n"
                . "🏁 Selesai hari ini: **{$selesaiHari}**\n\n"
                . "👥 Pengguna aktif: **{$totalUser}**",
            'actions' => $actions,
        ];
    }

    private function handleUrgensiInfo($user): array
    {
        $isAdmin = $this->hasRole($user, 'admin');

        $base = "🎯 **Tingkat Urgensi (Prioritas) Pengaduan:**\n\n"
            . "Setiap pengaduan memiliki tingkat urgensi yang menentukan seberapa cepat harus ditangani:\n\n"
            . "🔴 **Urgent** — Sangat mendesak, mengancam keselamatan atau kegiatan belajar\n"
            . "   *(contoh: atap bocor parah, kebakaran, kabel listrik putus)*\n\n"
            . "🟠 **Tinggi** — Perlu segera ditangani, mengganggu banyak orang\n"
            . "   *(contoh: AC rusak di ruang ujian, proyektor tidak bisa menyala)*\n\n"
            . "🟡 **Sedang** — Perlu ditangani tapi tidak mendesak\n"
            . "   *(contoh: lampu berkedip, pintu macet)*\n\n"
            . "🟢 **Rendah** — Bisa dijadwalkan, tidak mengganggu kegiatan\n"
            . "   *(contoh: cat tembok terkelupas, wastafel kurang lancar)*\n\n"
            . "📊 *Sistem otomatis menentukan prioritas berdasarkan kategori, lokasi, dan skor dampak. Admin dapat menyesuaikannya jika diperlukan.*";

        if ($isAdmin) {
            $base .= "\n\n**Admin:** Untuk mengubah urgensi, buka detail pengaduan → temukan kartu **Edit Urgensi** → pilih prioritas baru → isi alasan → **Simpan Urgensi**.";
            return [
                'reply'   => $base,
                'actions' => [['label' => '📋 Kelola Pengaduan', 'url' => route('admin.pengaduan.index')]],
            ];
        }

        return [
            'reply'   => $base,
            'actions' => [['label' => '📝 Buat Pengaduan', 'url' => route('pengaduan.create')]],
        ];
    }

    private function handleEditPengaduanInfo($user): array
    {
        if ($this->hasRole($user, 'admin')) {
            return [
                'reply' => "✏️ **Edit Pengaduan (Admin):**\n\n"
                    . "Admin dapat mengubah urgensi/prioritas melalui halaman detail pengaduan.\n\n"
                    . "Untuk edit data lainnya (judul, deskripsi, foto), gunakan tombol Edit di halaman detail pengaduan.\n\n"
                    . "⚠️ *Pengaduan yang sudah Selesai atau Ditolak tidak dapat diedit.*",
                'actions' => [['label' => '📋 Kelola Pengaduan', 'url' => route('admin.pengaduan.index')]],
            ];
        }

        return [
            'reply' => "✏️ **Cara Edit Pengaduan:**\n\n"
                . "1️⃣ Buka menu **Pengaduan Saya**\n"
                . "2️⃣ Klik pengaduan yang ingin diubah\n"
                . "3️⃣ Klik tombol **Edit** di halaman detail\n"
                . "4️⃣ Ubah informasi yang diperlukan (judul, deskripsi, foto)\n"
                . "5️⃣ Klik **Simpan Perubahan**\n\n"
                . "⚠️ *Pengaduan hanya bisa diedit selama masih berstatus **Pending** (belum diverifikasi Admin).*",
            'actions' => [['label' => '📋 Pengaduan Saya', 'url' => route('pengaduan.index')]],
        ];
    }

    private function handleReopenInfo($user): array
    {
        if ($this->hasRole($user, 'admin')) {
            return [
                'reply' => "🔄 **Buka Ulang Pengaduan (Admin):**\n\n"
                    . "Jika pelapor mengajukan permintaan buka ulang, Admin akan menerima notifikasi.\n\n"
                    . "1️⃣ Buka detail pengaduan yang ada permintaan buka ulang\n"
                    . "2️⃣ Di kolom kanan, temukan kartu **Permintaan Buka Ulang**\n"
                    . "3️⃣ Baca alasan pelapor\n"
                    . "4️⃣ Klik **Setujui Buka Ulang** jika memang perlu ditindaklanjuti",
                'actions' => [['label' => '📋 Kelola Pengaduan', 'url' => route('admin.pengaduan.index')]],
            ];
        }

        return [
            'reply' => "🔄 **Cara Minta Buka Ulang Pengaduan:**\n\n"
                . "Jika pengaduan sudah ditandai **Selesai** tapi masalah belum benar-benar teratasi:\n\n"
                . "1️⃣ Buka detail pengaduan yang bersangkutan\n"
                . "2️⃣ Scroll ke bawah, temukan tombol **Minta Buka Ulang**\n"
                . "3️⃣ Isi alasan mengapa perlu dibuka ulang\n"
                . "4️⃣ Kirim permintaan → Admin akan meninjau dan memutuskan\n\n"
                . "🔔 *Kamu akan mendapat notifikasi setelah Admin memproses permintaanmu.*",
            'actions' => [['label' => '📋 Pengaduan Saya', 'url' => route('pengaduan.index')]],
        ];
    }

    private function handleFeedbackInfo($user): array
    {
        return [
            'reply' => "⭐ **Feedback & Penilaian Pengaduan:**\n\n"
                . "Setelah pengaduanmu dinyatakan **Selesai**, kamu bisa memberikan feedback:\n\n"
                . "1️⃣ Buka detail pengaduan yang sudah selesai\n"
                . "2️⃣ Scroll ke bawah, temukan form **Berikan Feedback**\n"
                . "3️⃣ Pilih rating bintang (1–5)\n"
                . "4️⃣ Tulis komentar (opsional)\n"
                . "5️⃣ Klik **Kirim Feedback**\n\n"
                . "💡 *Feedbackmu membantu Admin mengevaluasi kinerja teknisi dan kualitas penanganan.*",
            'actions' => [['label' => '📋 Pengaduan Saya', 'url' => route('pengaduan.index')]],
        ];
    }

    private function handleUserManagementInfo($user): array
    {
        if (!$this->hasRole($user, 'admin')) {
            return [
                'reply' => "ℹ️ Manajemen pengguna hanya dapat dilakukan oleh **Admin** dan **Super Admin**.\n\n"
                    . "Jika ada masalah dengan akun kamu, hubungi Admin Sarana atau Tim IT sekolah.",
            ];
        }

        $totalUser     = User::count();
        $totalSiswa    = User::where('role', 'siswa')->count();
        $totalGuru     = User::where('role', 'guru')->count();
        $totalTeknisi  = User::where('role', 'teknisi')->count();
        $totalAdmin    = User::whereIn('role', ['admin', 'superadmin'])->count();

        return [
            'reply'   => "👥 **Manajemen Pengguna (Admin View):**\n\n"
                . "📊 **Total Pengguna:** {$totalUser}\n"
                . "• Siswa: **{$totalSiswa}**\n"
                . "• Guru: **{$totalGuru}**\n"
                . "• Teknisi: **{$totalTeknisi}**\n"
                . "• Admin/Super Admin: **{$totalAdmin}**\n\n"
                . "Gunakan menu **Kelola Pengguna** untuk menambah, mengedit, atau menonaktifkan akun.",
            'actions' => [['label' => '👥 Kelola Pengguna', 'url' => route('admin.users.index')]],
        ];
    }

    private function handleLaporanInfo($user): array
    {
        if (!$this->hasRole($user, 'admin')) {
            return [
                'reply' => "📊 Laporan dan ekspor data hanya tersedia untuk **Admin**, **Kepala Sekolah**, dan **Super Admin**.",
            ];
        }

        return [
            'reply'   => "📊 **Laporan & Ekspor Data:**\n\n"
                . "**Laporan Pengaduan:**\n"
                . "• Lihat statistik per periode, per kategori, per gedung\n"
                . "• Ekspor data ke format CSV\n\n"
                . "**Laporan Pinjaman:**\n"
                . "• Rekap pinjaman dan permintaan barang\n"
                . "• Ekspor riwayat transaksi\n\n"
                . "📥 Klik tombol **Export CSV** di halaman Kelola Pengaduan atau Kelola Pinjaman untuk mengunduh data.",
            'actions' => [
                ['label' => '📊 Laporan Pengaduan', 'url' => route('admin.reports.index')],
                ['label' => '📦 Ekspor Pinjaman',   'url' => route('admin.pinjaman.export')],
            ],
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    private function matches(string $text, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) return true;
        }
        return false;
    }
}

