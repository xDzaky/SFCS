<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\Gedung;
use App\Models\HistoryPengaduan;
use App\Models\Kategori;
use App\Models\Log;
use App\Models\Pengaduan;
use App\Models\Ruangan;
use App\Models\Setting;
use App\Models\SubKategori;
use App\Models\User;
use App\Services\PriorityScoringService;
use App\Services\SlaService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SchoolMockDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Memulai seeding data sekolah skala besar (1.600 Siswa & Pengaduan)...');

        $defaultPassword = Hash::make('password');

        // 1. Update siswa default agar memiliki kelas
        $this->updateDefaultStudents();

        // 2. Tambahkan Guru jika belum lengkap
        $this->seedGurus($defaultPassword);

        // 3. Tambahkan 1.600 Siswa Kelas 10, 11, dan 12
        $this->seedStudents($defaultPassword);

        // 4. Tambahkan beragam Pengaduan realistis
        $this->seedPengaduans();

        $this->command->info('Seeding selesai! Total user sekarang: ' . User::count() . ' (Siswa: ' . User::where('role', 'siswa')->count() . '), Pengaduan: ' . Pengaduan::count());
    }

    private function updateDefaultStudents(): void
    {
        $classes = [
            'andi@sfcs.sch.id' => 'X RPL 1',
            'bela@sfcs.sch.id' => 'XI AK 1',
            'citra@sfcs.sch.id' => 'XII MP 1',
            'dani@sfcs.sch.id' => 'X BD 1',
            'eka@sfcs.sch.id'  => 'XI RPL 1',
        ];

        foreach ($classes as $email => $kelas) {
            User::where('email', $email)->update(['kelas' => $kelas]);
        }
    }

    private function seedGurus(string $defaultPassword): void
    {
        $existingCount = User::where('role', 'guru')->count();
        if ($existingCount >= 15) {
            return;
        }

        $teachers = [
            ['name' => 'Bambang Sudarsono, M.Kom', 'email' => 'bambang.sudarsono@sfcs.sch.id', 'nip' => '198103152006041001', 'no_hp' => '081234567801'],
            ['name' => 'Dra. Endang Purwati', 'email' => 'endang.purwati@sfcs.sch.id', 'nip' => '197508202002122002', 'no_hp' => '081234567802'],
            ['name' => 'Wahyu Hidayat, S.Pd', 'email' => 'wahyu.hidayat@sfcs.sch.id', 'nip' => '198811042014021003', 'no_hp' => '081234567803'],
            ['name' => 'Rina Kartikasari, S.E', 'email' => 'rina.kartika@sfcs.sch.id', 'nip' => '198402192009032004', 'no_hp' => '081234567804'],
            ['name' => 'Agus Santoso, S.T', 'email' => 'agus.santoso@sfcs.sch.id', 'nip' => '198305102008011005', 'no_hp' => '081234567805'],
            ['name' => 'Tri Wulandari, S.Pd', 'email' => 'tri.wulandari@sfcs.sch.id', 'nip' => '199004122019032006', 'no_hp' => '081234567806'],
            ['name' => 'Nurul Hidayati, S.Pd', 'email' => 'nurul.hidayati@sfcs.sch.id', 'nip' => '198706252010012007', 'no_hp' => '081234567807'],
            ['name' => 'Hadi Purnomo, S.Kom', 'email' => 'hadi.purnomo@sfcs.sch.id', 'nip' => '199201172020121008', 'no_hp' => '081234567808'],
            ['name' => 'Fitria Anggraini, S.Pd', 'email' => 'fitria.anggraini@sfcs.sch.id', 'nip' => '199307082022032009', 'no_hp' => '081234567809'],
            ['name' => 'Drs. Supardi, M.M', 'email' => 'supardi@sfcs.sch.id', 'nip' => '196912011995031010', 'no_hp' => '081234567810'],
            ['name' => 'Dewi Lestari, S.Pd', 'email' => 'dewi.lestari@sfcs.sch.id', 'nip' => '199109302018022011', 'no_hp' => '081234567811'],
            ['name' => 'Aris Munandar, S.T', 'email' => 'aris.munandar@sfcs.sch.id', 'nip' => '198604142011011012', 'no_hp' => '081234567812'],
            ['name' => 'Maya Indraswari, S.E', 'email' => 'maya.indraswari@sfcs.sch.id', 'nip' => '198908222015042013', 'no_hp' => '081234567813'],
        ];

        foreach ($teachers as $guru) {
            User::firstOrCreate(
                ['email' => $guru['email']],
                [
                    'name'                  => $guru['name'],
                    'password'              => $defaultPassword,
                    'role'                  => 'guru',
                    'nip'                   => $guru['nip'],
                    'no_hp'                 => $guru['no_hp'],
                    'is_active'             => true,
                    'force_password_change' => false,
                    'email_verified_at'     => now(),
                ]
            );
        }
    }

    private function seedStudents(string $defaultPassword): void
    {
        $targetTotal = 1600;
        $currentSiswaCount = User::where('role', 'siswa')->count();
        if ($currentSiswaCount >= $targetTotal) {
            $this->command->info("Siswa sudah berjumlah {$currentSiswaCount}, melewati target {$targetTotal}.");
            return;
        }

        $needed = $targetTotal - $currentSiswaCount;
        $this->command->info("Menambahkan {$needed} siswa baru dari kelas 10 sampai 12...");

        // 45 Rombel (15 kelas per tingkat: 3 kelas RPL, 3 MP, 3 AK, 3 BD, 3 LP)
        $grades = [
            10 => ['code' => 'X', 'year_prefix' => '24', 'count' => 533],
            11 => ['code' => 'XI', 'year_prefix' => '23', 'count' => 533],
            12 => ['code' => 'XII', 'year_prefix' => '22', 'count' => 534],
        ];

        $jurusans = ['RPL', 'MP', 'AK', 'BD', 'LP'];

        // Nama-nama Indonesia
        $firstNamesMale = [
            'Muhammad', 'Ahmad', 'Dimas', 'Rizky', 'Fajar', 'Bayu', 'Aditya', 'Bagas', 'Ilham', 'Farhan',
            'Kevin', 'Gilang', 'Wahyu', 'Daffa', 'Rangga', 'Hendra', 'Fikri', 'Aris', 'Yoga', 'Galih',
            'Brian', 'Hafiz', 'Alif', 'Danar', 'Fadhil', 'Rehan', 'Surya', 'Satria', 'Faisal', 'Ridho',
            'Bintang', 'Aldi', 'Rafi', 'Zidan', 'Iqbal', 'Tegar', 'Bima', 'Rian', 'Fauzan', 'Rio',
            'Taufik', 'Irfan', 'Bagus', 'Sandy', 'Wisnu', 'Lukman', 'Rizaldi', 'Ferdi', 'Riko', 'Doni'
        ];

        $firstNamesFemale = [
            'Siti', 'Nur', 'Anisa', 'Putri', 'Dwi', 'Nabila', 'Zahra', 'Aulia', 'Rina', 'Dewi',
            'Maya', 'Indah', 'Melani', 'Fitri', 'Tiara', 'Amanda', 'Intan', 'Lestari', 'Salma', 'Anggun',
            'Salsabila', 'Gita', 'Tari', 'Dina', 'Cindy', 'Nadya', 'Aurel', 'Nayla', 'Kirana', 'Febri',
            'Vina', 'Wulan', 'Safira', 'Rara', 'Clarissa', 'Amalia', 'Keysha', 'Ayu', 'Sherly', 'Tasya',
            'Bella', 'Nisa', 'Riska', 'Syifa', 'Meisya', 'Hana', 'Widya', 'Laras', 'Nadia', 'Shintia'
        ];

        $lastNames = [
            'Pratama', 'Saputra', 'Hidayat', 'Permana', 'Santoso', 'Wibowo', 'Kusuma', 'Ramadhan', 'Nugroho',
            'Setiawan', 'Kurniawan', 'Firmansyah', 'Prasetyo', 'Utomo', 'Wardana', 'Saputri', 'Anggraini',
            'Lestari', 'Maharani', 'Puspitasari', 'Wijaya', 'Oktaviani', 'Rahmawati', 'Safitri', 'Kusumawardani',
            'Wahyuni', 'Sari', 'Gunawan', 'Susanto', 'Mahendra', 'Pranata', 'Handayani', 'Pertiwi', 'Adriansyah',
            'Anwar', 'Siregar', 'Wibisono', 'Syahputra', 'Subekti', 'Kurnia', 'Damayanti', 'Triana', 'Sudarsono'
        ];

        $existingNis = User::whereNotNull('nis')->pluck('nis')->flip()->toArray();
        $existingEmails = User::pluck('email')->flip()->toArray();

        $rows = [];
        $now = now()->toDateTimeString();

        foreach ($grades as $gradeNum => $gradeInfo) {
            $yearPrefix = $gradeInfo['year_prefix'];
            $gradeLabel = $gradeInfo['code'];
            $targetForGrade = $gradeInfo['count'];

            // Bentuk daftar kelas untuk tingkat ini, misal: X RPL 1, X RPL 2, X RPL 3 ...
            $classList = [];
            foreach ($jurusans as $jur) {
                for ($r = 1; $r <= 3; $r++) {
                    $classList[] = "{$gradeLabel} {$jur} {$r}";
                }
            }
            $totalClassesInGrade = count($classList); // 15 kelas

            for ($i = 1; $i <= $targetForGrade; $i++) {
                $nisNum = str_pad($i, 4, '0', STR_PAD_LEFT);
                $nis = $yearPrefix . $nisNum;

                // Cegah duplikasi NIS jika sudah ada
                if (isset($existingNis[$nis])) {
                    $nis = $yearPrefix . '9' . str_pad($i, 3, '0', STR_PAD_LEFT);
                }
                $existingNis[$nis] = true;

                // Distribusi kelas merata
                $kelas = $classList[($i - 1) % $totalClassesInGrade];

                $isMale = ($i % 2 === 0);
                $firstName = $isMale
                    ? $firstNamesMale[array_rand($firstNamesMale)]
                    : $firstNamesFemale[array_rand($firstNamesFemale)];
                $lastName = $lastNames[array_rand($lastNames)];
                $fullName = "{$firstName} {$lastName}";

                $cleanFirst = Str::slug(strtolower($firstName), '');
                $cleanLast = Str::slug(strtolower($lastName), '');
                $email = "{$cleanFirst}.{$nis}@sfcs.sch.id";

                if (isset($existingEmails[$email])) {
                    $email = "{$cleanFirst}.{$cleanLast}.{$nis}@sfcs.sch.id";
                }
                $existingEmails[$email] = true;

                $phonePrefixes = ['0812', '0813', '0821', '0822', '0857', '0858', '0896', '0895'];
                $phone = $phonePrefixes[array_rand($phonePrefixes)] . rand(10000000, 99999999);

                $rows[] = [
                    'name'                  => $fullName,
                    'email'                 => $email,
                    'password'              => $defaultPassword,
                    'role'                  => 'siswa',
                    'nis'                   => $nis,
                    'kelas'                 => $kelas,
                    'no_hp'                 => $phone,
                    'is_active'             => true,
                    'force_password_change' => false,
                    'email_verified_at'     => $now,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];

                // Bulk insert tiap 250 record
                if (count($rows) >= 250) {
                    DB::table('users')->insert($rows);
                    $rows = [];
                }
            }
        }

        if (!empty($rows)) {
            DB::table('users')->insert($rows);
        }
    }

    private function seedPengaduans(): void
    {
        $this->command->info('Membuat data pengaduan realistis...');

        $siswas = User::where('role', 'siswa')->get();
        $gurus = User::where('role', 'guru')->get();
        $teknisis = User::where('role', 'teknisi')->get();
        $admin = User::where('role', 'admin')->first() ?? User::where('role', 'superadmin')->first();

        $kategoris = Kategori::all()->keyBy('nama');
        $gedungs = Gedung::all()->keyBy('kode');
        $ruangans = Ruangan::all();

        $scoringService = new PriorityScoringService();
        $slaService = new SlaService();

        // 35 Pengaduan Variatif
        $mockList = [
            // ── SELESAI (12 Laporan) ──
            [
                'judul'         => 'AC Ruang Teori Kelas X RPL 1 Mengeluarkan Bau Gosong',
                'deskripsi'     => 'Saat pelajaran PBO dinyalakan, AC tiba-tiba mengeluarkan asap tipis dan bau gosong terbakar. Langsung kami matikan MCB-nya demi keamanan.',
                'kategori'      => 'AC & Pendingin',
                'gedung'        => 'GD-B',
                'lantai'        => 1,
                'status'        => 'selesai',
                'prioritas_req' => 'urgent',
                'safety'        => true,
                'learning'      => true,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'listrik',
                'days_ago'      => 14,
                'teknisi_idx'   => 0,
                'rating'        => 5,
                'feedback'      => 'Penanganan cepat sekali! Teknisi langsung datang 15 menit setelah lapor dan kapasitor AC langsung diganti.',
            ],
            [
                'judul'         => 'Lampu TL Lab Komputer 2 Berkedip dan Berdengung Keras',
                'deskripsi'     => 'Lampu baris depan berkedip-kedip cepat dan mengeluarkan bunyi mendengung yang sangat mengganggu konsentrasi saat praktik coding.',
                'kategori'      => 'Kelistrikan',
                'gedung'        => 'GD-E',
                'lantai'        => 1,
                'status'        => 'selesai',
                'prioritas_req' => 'sedang',
                'safety'        => false,
                'learning'      => true,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'listrik',
                'days_ago'      => 12,
                'teknisi_idx'   => 1,
                'rating'        => 4,
                'feedback'      => 'Starter dan lampu sudah diganti baru, sekarang terang kembali.',
            ],
            [
                'judul'         => 'Keran Wastafel Depan Kelas XI AK 2 Patah',
                'deskripsi'     => 'Keran cuci tangan patah di bagian leher saat diputar siswa, air terus muncrat dan membasahi selasar depan kelas.',
                'kategori'      => 'Plumbing',
                'gedung'        => 'GD-C',
                'lantai'        => 1,
                'status'        => 'selesai',
                'prioritas_req' => 'tinggi',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'air',
                'days_ago'      => 10,
                'teknisi_idx'   => 0,
                'rating'        => 5,
                'feedback'      => 'Keran baru sudah dipasang dan stop kran langsung diperbaiki.',
            ],
            [
                'judul'         => 'Pintu Kamar Mandi Siswa Lantai 2 Gedung C Tidak Bisa Dikunci',
                'deskripsi'     => 'Grendel pintu toilet kamar 3 lepas bautnya sehingga siswa kesulitan menggunakan toilet dengan nyaman.',
                'kategori'      => 'Bangunan',
                'gedung'        => 'GD-C',
                'lantai'        => 2,
                'status'        => 'selesai',
                'prioritas_req' => 'rendah',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 9,
                'teknisi_idx'   => 2,
                'rating'        => 5,
                'feedback'      => 'Sudah diganti grendel baru yang lebih kokoh. Terima kasih Sarpras!',
            ],
            [
                'judul'         => 'Kabel HDMI Proyektor Ruang Multimedia Putus di Bagian Ujung',
                'deskripsi'     => 'Konektor HDMI bengkok dan pin di dalamnya patah, layar proyektor bergaris hijau dan tidak bisa menampilkan presentasi materi.',
                'kategori'      => 'IT & Multimedia',
                'gedung'        => 'GD-F',
                'lantai'        => 2,
                'status'        => 'selesai',
                'prioritas_req' => 'tinggi',
                'safety'        => false,
                'learning'      => true,
                'exam'          => true,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 8,
                'teknisi_idx'   => 1,
                'rating'        => 5,
                'feedback'      => 'Kabel HDMI baru diganti yang panjang 15 meter, presentasi ujian berjalan lancar.',
            ],
            [
                'judul'         => 'Kursi Guru di Ruang Teori XII RPL 3 Sandarannya Retak',
                'deskripsi'     => 'Sandaran kayu kursi guru goyang parah dan retak, khawatir patah saat diduduki guru pengajar.',
                'kategori'      => 'Furniture',
                'gedung'        => 'GD-D',
                'lantai'        => 2,
                'status'        => 'selesai',
                'prioritas_req' => 'sedang',
                'safety'        => true,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 7,
                'teknisi_idx'   => 2,
                'rating'        => 4,
                'feedback'      => 'Kursi diganti dengan kursi kantor yang nyaman.',
            ],
            [
                'judul'         => 'Plafon Selasar Depan Perpustakaan Bocor Saat Hujan Lebat',
                'deskripsi'     => 'Air hujan menetes deras dari celah plafon gypsum, membuat lantai licin dan berbahaya bagi siswa yang lewat.',
                'kategori'      => 'Bangunan',
                'gedung'        => 'GD-F',
                'lantai'        => 1,
                'status'        => 'selesai',
                'prioritas_req' => 'tinggi',
                'safety'        => true,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_lantai',
                'utilities'     => 'none',
                'days_ago'      => 6,
                'teknisi_idx'   => 0,
                'rating'        => 4,
                'feedback'      => 'Genteng yang bergeser sudah dibenahi dan plafon sudah ditambal.',
            ],
            [
                'judul'         => 'Flush Kloset Duduk Toilet Guru Macet Terus Mengalir',
                'deskripsi'     => 'Tombol flush toilet guru menancap ke dalam dan air tandon terus terbuang habis.',
                'kategori'      => 'Plumbing',
                'gedung'        => 'GD-A',
                'lantai'        => 1,
                'status'        => 'selesai',
                'prioritas_req' => 'sedang',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'air',
                'days_ago'      => 6,
                'teknisi_idx'   => 1,
                'rating'        => 5,
                'feedback'      => 'Pelampung dan karet seal flush sudah diganti baru.',
            ],
            [
                'judul'         => 'Stop Kontak Baris 3 Lab Jaringan Sering Keluar Percikan Api',
                'deskripsi'     => 'Saat siswa mencolokkan adaptor charger laptop, stop kontak mengeluarkan percikan api dan longgar.',
                'kategori'      => 'Kelistrikan',
                'gedung'        => 'GD-E',
                'lantai'        => 2,
                'status'        => 'selesai',
                'prioritas_req' => 'urgent',
                'safety'        => true,
                'learning'      => true,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'listrik',
                'days_ago'      => 5,
                'teknisi_idx'   => 0,
                'rating'        => 5,
                'feedback'      => 'Stop kontak langsung diganti tipe Outbow standar industri Panasonic.',
            ],
            [
                'judul'         => 'Papan Tulis Whiteboard Kelas XI MP 1 Goyang Mau Roboh',
                'deskripsi'     => 'Baut pengait bracket whiteboard ke tembok sudah terlepas dua buah, papan tulis miring.',
                'kategori'      => 'Furniture',
                'gedung'        => 'GD-C',
                'lantai'        => 1,
                'status'        => 'selesai',
                'prioritas_req' => 'tinggi',
                'safety'        => true,
                'learning'      => true,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 4,
                'teknisi_idx'   => 2,
                'rating'        => 4,
                'feedback'      => 'Dipasang dinabolt baru sehingga papan tulis menempel sangat kuat.',
            ],
            [
                'judul'         => 'Kipas Angin Dinding Kantin Utama Mati Total',
                'deskripsi'     => 'Kipas angin dinding dekat meja makan kantin mati total, suasana saat istirahat sangat pengap.',
                'kategori'      => 'AC & Pendingin',
                'gedung'        => 'KNT',
                'lantai'        => 1,
                'status'        => 'selesai',
                'prioritas_req' => 'rendah',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'listrik',
                'days_ago'      => 4,
                'teknisi_idx'   => 1,
                'rating'        => 5,
                'feedback'      => 'Sekring thermal kipas diganti dan sudah berputar kencang lagi.',
            ],
            [
                'judul'         => 'Kran Wudhu Masjid Al-Hikmah Bocor Halus 4 Titik',
                'deskripsi'     => 'Empat kran di area wudhu pria menetes terus meskipun sudah ditutup kencang.',
                'kategori'      => 'Plumbing',
                'gedung'        => 'MSJ',
                'lantai'        => 1,
                'status'        => 'selesai',
                'prioritas_req' => 'sedang',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'air',
                'days_ago'      => 3,
                'teknisi_idx'   => 0,
                'rating'        => 5,
                'feedback'      => 'Semua seal karet kran wudhu diganti, sekarang sudah tidak bocor sama sekali.',
            ],

            // ── SEDANG DIPROSES TEKNISI (8 Laporan) ──
            [
                'judul'         => 'MCB Utama Gedung E Sering Trip Saat Semua Komputer Nyala',
                'deskripsi'     => 'Saat Lab 1 dan Lab 2 menyalakan 70 unit PC secara bersamaan, MCB panel bawah langsung trip.',
                'kategori'      => 'Kelistrikan',
                'gedung'        => 'GD-E',
                'lantai'        => 1,
                'status'        => 'diproses',
                'prioritas_req' => 'urgent',
                'safety'        => true,
                'learning'      => true,
                'exam'          => true,
                'scope'         => '1_gedung',
                'utilities'     => 'listrik',
                'days_ago'      => 2,
                'teknisi_idx'   => 0,
            ],
            [
                'judul'         => 'AC Split Ruang Guru Gedung A Meneteskan Air Deras ke Meja',
                'deskripsi'     => 'Talang air AC bocor dan menetes ke dokumen arsip guru di bawahnya.',
                'kategori'      => 'AC & Pendingin',
                'gedung'        => 'GD-A',
                'lantai'        => 2,
                'status'        => 'diproses',
                'prioritas_req' => 'tinggi',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 2,
                'teknisi_idx'   => 1,
            ],
            [
                'judul'         => 'Saluran Pembuangan Air Halaman Basket Menggenang',
                'deskripsi'     => 'Saluran got pinggir lapangan basket tersumbat daun dan pasir sehingga air meluap ke lapangan.',
                'kategori'      => 'Kebersihan',
                'gedung'        => 'GD-G',
                'lantai'        => 1,
                'status'        => 'diproses',
                'prioritas_req' => 'sedang',
                'safety'        => true,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_lantai',
                'utilities'     => 'none',
                'days_ago'      => 2,
                'teknisi_idx'   => 2,
            ],
            [
                'judul'         => 'Proyektor Epson Lab Akuntansi Tampilan Bergaris Merah',
                'deskripsi'     => 'Optik proyektor bermasalah, muncul garis-garis merah tebal dan warna buram.',
                'kategori'      => 'IT & Multimedia',
                'gedung'        => 'GD-E',
                'lantai'        => 2,
                'status'        => 'diproses',
                'prioritas_req' => 'tinggi',
                'safety'        => false,
                'learning'      => true,
                'exam'          => true,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 1,
                'teknisi_idx'   => 1,
            ],
            [
                'judul'         => 'Engsel Pintu Ruang Praktik Otomotif Macet Berat',
                'deskripsi'     => 'Pintu rolling door geser bengkok relnya sehingga sulit dibuka tutup saat jam praktik siswa.',
                'kategori'      => 'Bangunan',
                'gedung'        => 'GD-H',
                'lantai'        => 1,
                'status'        => 'diproses',
                'prioritas_req' => 'sedang',
                'safety'        => true,
                'learning'      => true,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 1,
                'teknisi_idx'   => 2,
            ],
            [
                'judul'         => 'Kloset Jongkok Toilet Siswa Gedung D Lantai 1 Meluap',
                'deskripsi'     => 'Pipa pembuangan tersumbat sampah plastik, air tidak mau turun sama sekali.',
                'kategori'      => 'Plumbing',
                'gedung'        => 'GD-D',
                'lantai'        => 1,
                'status'        => 'diproses',
                'prioritas_req' => 'urgent',
                'safety'        => true,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'air',
                'days_ago'      => 1,
                'teknisi_idx'   => 0,
            ],
            [
                'judul'         => '5 Pasang Meja Kursi di Kelas X BD 2 Rusak Pakunya Mencuat',
                'deskripsi'     => 'Paku penyangga kayu mencuat keluar, rok siswi robek kemarin dan membahayakan tangan siswa.',
                'kategori'      => 'Furniture',
                'gedung'        => 'GD-B',
                'lantai'        => 2,
                'status'        => 'diproses',
                'prioritas_req' => 'tinggi',
                'safety'        => true,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 1,
                'teknisi_idx'   => 2,
            ],
            [
                'judul'         => 'Koneksi Kabel LAN Hub Lab Cisco Putus 6 Port',
                'deskripsi'     => 'Kabel UTP RJ45 patah penguncinya dan lepas dari switch rackmount.',
                'kategori'      => 'IT & Multimedia',
                'gedung'        => 'GD-E',
                'lantai'        => 2,
                'status'        => 'diproses',
                'prioritas_req' => 'tinggi',
                'safety'        => false,
                'learning'      => true,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 1,
                'teknisi_idx'   => 1,
            ],

            // ── DIVERIFIKASI (6 Laporan) ──
            [
                'judul'         => 'Saklar Lampu Kelas XII AK 1 Bunyi Berderik',
                'deskripsi'     => 'Saat saklar ditekan on/off terdengar suara percikan berderik dan lampu kedip sebentar.',
                'kategori'      => 'Kelistrikan',
                'gedung'        => 'GD-D',
                'lantai'        => 1,
                'status'        => 'diverifikasi',
                'prioritas_req' => 'tinggi',
                'safety'        => true,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'listrik',
                'days_ago'      => 1,
                'teknisi_idx'   => 0,
            ],
            [
                'judul'         => 'Gagang Jendela Kaca Kelas X MP 3 Lepas',
                'deskripsi'     => 'Gagang putaran jendela aluminium lepas, jendela tidak bisa dikunci dari dalam saat pulang sekolah.',
                'kategori'      => 'Keamanan',
                'gedung'        => 'GD-B',
                'lantai'        => 2,
                'status'        => 'diverifikasi',
                'prioritas_req' => 'sedang',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 1,
                'teknisi_idx'   => 2,
            ],
            [
                'judul'         => 'Wastafel Selasar Kelas XII RPL 1 Pipa Pembuangannya Bocor',
                'deskripsi'     => 'Pipa fleksibel di bawah wastafel sobek, air cuci tangan langsung menggenangi lantai koridor.',
                'kategori'      => 'Plumbing',
                'gedung'        => 'GD-D',
                'lantai'        => 2,
                'status'        => 'diverifikasi',
                'prioritas_req' => 'sedang',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'air',
                'days_ago'      => 1,
                'teknisi_idx'   => 0,
            ],
            [
                'judul'         => 'Remote AC Ruang Pertemuan Perpustakaan Hilang / Tidak Ada',
                'deskripsi'     => 'Remote AC tidak ditemukan di tempatnya sehingga AC tidak bisa dinyalakan untuk rapat MGMP besok.',
                'kategori'      => 'AC & Pendingin',
                'gedung'        => 'GD-F',
                'lantai'        => 2,
                'status'        => 'diverifikasi',
                'prioritas_req' => 'rendah',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 0,
                'teknisi_idx'   => 1,
            ],
            [
                'judul'         => 'Papan Mading Depan Gedung B Kacanya Retak',
                'deskripsi'     => 'Kaca geser mading pengumuman retak panjang akibat terkena bola sepak kemarin sore.',
                'kategori'      => 'Bangunan',
                'gedung'        => 'GD-B',
                'lantai'        => 1,
                'status'        => 'diverifikasi',
                'prioritas_req' => 'tinggi',
                'safety'        => true,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_lantai',
                'utilities'     => 'none',
                'days_ago'      => 0,
                'teknisi_idx'   => 2,
            ],
            [
                'judul'         => 'Kabel Power CPU Server Ujian CBT Longgar',
                'deskripsi'     => 'Socket power di belakang casing server agak goyang, server sempat restart sendiri saat gladi bersih ujian.',
                'kategori'      => 'IT & Multimedia',
                'gedung'        => 'GD-E',
                'lantai'        => 1,
                'status'        => 'diverifikasi',
                'prioritas_req' => 'urgent',
                'safety'        => true,
                'learning'      => true,
                'exam'          => true,
                'scope'         => '1_gedung',
                'utilities'     => 'listrik',
                'days_ago'      => 0,
                'teknisi_idx'   => 1,
            ],

            // ── PENDING / BARU MASUK (6 Laporan) ──
            [
                'judul'         => 'Lampu Kamar Mandi Belakang Kantin Putus',
                'deskripsi'     => 'Kamar mandi gelap gulita karena bohlam lampu putus, siswa tidak berani masuk.',
                'kategori'      => 'Kelistrikan',
                'gedung'        => 'KNT',
                'lantai'        => 1,
                'status'        => 'pending',
                'prioritas_req' => 'rendah',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 0,
            ],
            [
                'judul'         => 'Keran Tandon Air Wudhu Samping Pos Satpam Patah',
                'deskripsi'     => 'Keran plastik patah dan air menetes terus ke jalan masuk gerbang sekolah.',
                'kategori'      => 'Plumbing',
                'gedung'        => 'PST',
                'lantai'        => 1,
                'status'        => 'pending',
                'prioritas_req' => 'sedang',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'air',
                'days_ago'      => 0,
            ],
            [
                'judul'         => 'AC Ruang Tata Usaha Kurang Dingin Hembusan Lemah',
                'deskripsi'     => 'AC hanya mengeluarkan angin biasa seperti kipas angin, suhu ruangan panas saat melayani administrasi siswa.',
                'kategori'      => 'AC & Pendingin',
                'gedung'        => 'GD-A',
                'lantai'        => 1,
                'status'        => 'pending',
                'prioritas_req' => 'sedang',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 0,
            ],
            [
                'judul'         => 'Pintu Lemari Alat Praktik Bengkel Mesin Kuncinya Patah',
                'deskripsi'     => 'Anak kunci patah di dalam lubang silinder gembok lemari perkakas presisi.',
                'kategori'      => 'Keamanan',
                'gedung'        => 'GD-H',
                'lantai'        => 1,
                'status'        => 'pending',
                'prioritas_req' => 'tinggi',
                'safety'        => false,
                'learning'      => true,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 0,
            ],
            [
                'judul'         => 'Layar Monitor PC Instruktur Lab RPL 1 Mati Total',
                'deskripsi'     => 'Monitor tidak menyala lampu indikatornya, sudah dicoba ganti kabel power tetap mati.',
                'kategori'      => 'IT & Multimedia',
                'gedung'        => 'GD-E',
                'lantai'        => 1,
                'status'        => 'pending',
                'prioritas_req' => 'tinggi',
                'safety'        => false,
                'learning'      => true,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 0,
            ],
            [
                'judul'         => 'Keramik Lantai Tangga Gedung B Menuju Lantai 2 Terangkat',
                'deskripsi'     => 'Dua keping keramik anak tangga terangkat dan tajam ujungnya, rawan membuat siswa tersandung jatuh saat jam istirahat.',
                'kategori'      => 'Bangunan',
                'gedung'        => 'GD-B',
                'lantai'        => 1,
                'status'        => 'pending',
                'prioritas_req' => 'urgent',
                'safety'        => true,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_lantai',
                'utilities'     => 'none',
                'days_ago'      => 0,
            ],

            // ── DITOLAK (3 Laporan) ──
            [
                'judul'         => 'Minta Dipasangkan Smart TV 65 Inch di Kelas X RPL 2',
                'deskripsi'     => 'Agar pembelajaran lebih seru mohon disediakan Smart TV besar di depan kelas kami.',
                'kategori'      => 'IT & Multimedia',
                'gedung'        => 'GD-B',
                'lantai'        => 1,
                'status'        => 'ditolak',
                'prioritas_req' => 'rendah',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 11,
                'alasan_tolak'  => 'Permintaan ini termasuk kategori pengadaan barang/sarana baru, bukan pelaporan kerusakan fasilitas yang ada. Harap ajukan melalui usulan BOS/Komite sekolah.',
            ],
            [
                'judul'         => 'Minta Ganti AC Baru Ruang Kelas XI MP 2',
                'deskripsi'     => 'Model AC sekarang kurang modern, tolong ganti AC inverter baru yang warna hitam.',
                'kategori'      => 'AC & Pendingin',
                'gedung'        => 'GD-C',
                'lantai'        => 1,
                'status'        => 'ditolak',
                'prioritas_req' => 'rendah',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 8,
                'alasan_tolak'  => 'AC di kelas tersebut sudah dicek teknisi dan suhunya masih mencapai 18°C normal. Penggantian unit tidak disetujui karena unit lama masih sangat layak pakai.',
            ],
            [
                'judul'         => 'Minta Disediakan Kulkas Minuman di Kelas XII AK 3',
                'deskripsi'     => 'Siswa sering haus, butuh kulkas kecil untuk simpan air dingin bersama di kelas.',
                'kategori'      => 'Furniture',
                'gedung'        => 'GD-D',
                'lantai'        => 2,
                'status'        => 'ditolak',
                'prioritas_req' => 'rendah',
                'safety'        => false,
                'learning'      => false,
                'exam'          => false,
                'scope'         => '1_kelas',
                'utilities'     => 'none',
                'days_ago'      => 5,
                'alasan_tolak'  => 'Fasilitas kulkas pribadi di ruang kelas tidak diperkenankan sesuai tata tertib inventaris sekolah untuk menghindari beban daya berlebih.',
            ],
        ];

        $counter = 2; // Mulai dari 2 karena ADU-20260910-001 sudah ada
        foreach ($mockList as $item) {
            $kat = $kategoris->get($item['kategori']);
            $ged = $gedungs->get($item['gedung']);

            if (!$kat || !$ged) {
                continue;
            }

            // Pilih ruangan yang sesuai gedung jika ada
            $ruangan = $ruangans->where('gedung_id', $ged->id)->where('lantai', $item['lantai'])->first();

            // Pilih user pelapor (acak dari siswa atau guru)
            $user = (rand(1, 10) > 3)
                ? $siswas->random()
                : ($gurus->isNotEmpty() ? $gurus->random() : $siswas->random());

            $createdAt = Carbon::now()->subDays($item['days_ago'])->subHours(rand(1, 10))->subMinutes(rand(1, 50));
            $kode = 'ADU-' . $createdAt->format('Ymd') . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

            // Hitung skor dampak dan prioritas
            $impactPayload = [
                'impact_safety_risk'      => $item['safety'],
                'impact_learning_blocked' => $item['learning'],
                'impact_exam_related'     => $item['exam'],
                'impact_area_scope'       => $item['scope'],
                'impact_utilities'        => $item['utilities'],
            ];

            $score = $scoringService->score($impactPayload);
            $resolution = $scoringService->resolvePriority($item['prioritas_req'], $score);

            $teknisi = null;
            if (isset($item['teknisi_idx']) && $teknisis->isNotEmpty()) {
                $teknisi = $teknisis[$item['teknisi_idx'] % $teknisis->count()];
            }

            $verifiedAt = in_array($item['status'], ['diverifikasi', 'diproses', 'selesai', 'ditolak'])
                ? $createdAt->copy()->addHours(rand(1, 3))
                : null;

            $assignedAt = in_array($item['status'], ['diverifikasi', 'diproses', 'selesai']) && $teknisi
                ? $createdAt->copy()->addHours(rand(3, 6))
                : null;

            $startedAt = in_array($item['status'], ['diproses', 'selesai'])
                ? $createdAt->copy()->addHours(rand(6, 12))
                : null;

            $completedAt = ($item['status'] === 'selesai')
                ? $createdAt->copy()->addHours(rand(14, 48))
                : null;

            $pengaduan = Pengaduan::create([
                'kode_pengaduan'          => $kode,
                'user_id'                 => $user->id,
                'kategori_id'             => $kat->id,
                'gedung_id'               => $ged->id,
                'lantai'                  => $item['lantai'],
                'ruangan_id'              => $ruangan?->id,
                'lokasi_detail'           => ($ruangan ? $ruangan->nama . ' - ' : '') . $ged->nama . ' Lt. ' . $item['lantai'],
                'judul'                   => $item['judul'],
                'deskripsi'               => $item['deskripsi'],
                'prioritas'               => $resolution['final'],
                'requested_prioritas'     => $resolution['requested'],
                'priority_score'          => $score,
                'needs_priority_review'   => $resolution['needs_review'],
                'impact_safety_risk'      => $item['safety'],
                'impact_learning_blocked' => $item['learning'],
                'impact_exam_related'     => $item['exam'],
                'impact_area_scope'       => $item['scope'],
                'impact_utilities'        => $item['utilities'],
                'status'                  => $item['status'],
                'teknisi_id'              => $teknisi?->id,
                'catatan_admin'           => $item['alasan_tolak'] ?? null,
                'alasan_tolak'            => $item['alasan_tolak'] ?? null,
                'verified_at'             => $verifiedAt,
                'assigned_at'             => $assignedAt,
                'started_at'              => $startedAt,
                'completed_at'            => $completedAt,
                'sla_due_at'              => $slaService->calculateDueAt($resolution['final'], $createdAt),
                'rating'                  => $item['rating'] ?? null,
                'feedback'                => $item['feedback'] ?? null,
                'created_at'              => $createdAt,
                'updated_at'              => $completedAt ?? $startedAt ?? $verifiedAt ?? $createdAt,
            ]);

            // 1. HistoryPengaduan Timeline
            HistoryPengaduan::create([
                'pengaduan_id' => $pengaduan->id,
                'user_id'      => $user->id,
                'status_lama'  => null,
                'status_baru'  => 'pending',
                'keterangan'   => 'Laporan pengaduan dibuat oleh pelapor',
                'created_at'   => $createdAt,
                'updated_at'   => $createdAt,
            ]);

            Log::create([
                'pengaduan_id' => $pengaduan->id,
                'user_id'      => $user->id,
                'action'       => Log::ACTION_CREATED,
                'description'  => 'Membuat pengaduan: ' . $pengaduan->judul,
                'created_at'   => $createdAt,
            ]);

            if ($verifiedAt) {
                $statusVerif = ($item['status'] === 'ditolak') ? 'ditolak' : 'diverifikasi';
                HistoryPengaduan::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id'      => $admin?->id ?? $user->id,
                    'status_lama'  => 'pending',
                    'status_baru'  => $statusVerif,
                    'keterangan'   => ($item['status'] === 'ditolak')
                        ? 'Pengaduan ditolak oleh Admin: ' . ($item['alasan_tolak'] ?? '-')
                        : 'Pengaduan diverifikasi oleh Admin',
                    'created_at'   => $verifiedAt,
                    'updated_at'   => $verifiedAt,
                ]);

                Log::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id'      => $admin?->id ?? $user->id,
                    'action'       => Log::ACTION_STATUS_CHANGED,
                    'description'  => ($item['status'] === 'ditolak')
                        ? 'Menolak pengaduan dengan alasan: ' . ($item['alasan_tolak'] ?? '-')
                        : 'Memverifikasi status laporan',
                    'created_at'   => $verifiedAt,
                ]);
            }

            if ($assignedAt && $teknisi) {
                Log::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id'      => $admin?->id ?? $user->id,
                    'action'       => Log::ACTION_ASSIGNED,
                    'description'  => 'Menugaskan teknisi: ' . $teknisi->name,
                    'created_at'   => $assignedAt,
                ]);
            }

            if ($startedAt && $teknisi) {
                HistoryPengaduan::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id'      => $teknisi->id,
                    'status_lama'  => 'diverifikasi',
                    'status_baru'  => 'diproses',
                    'keterangan'   => 'Teknisi ' . $teknisi->name . ' mulai mengerjakan perbaikan',
                    'created_at'   => $startedAt,
                    'updated_at'   => $startedAt,
                ]);

                Log::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id'      => $teknisi->id,
                    'action'       => Log::ACTION_STATUS_CHANGED,
                    'description'  => 'Memulai pengerjaan perbaikan di lokasi',
                    'created_at'   => $startedAt,
                ]);
            }

            if ($completedAt && $teknisi) {
                HistoryPengaduan::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id'      => $teknisi->id,
                    'status_lama'  => 'diproses',
                    'status_baru'  => 'selesai',
                    'keterangan'   => 'Perbaikan fasilitas telah selesai dan siap digunakan kembali',
                    'created_at'   => $completedAt,
                    'updated_at'   => $completedAt,
                ]);

                Log::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id'      => $teknisi->id,
                    'action'       => Log::ACTION_STATUS_CHANGED,
                    'description'  => 'Menandai pekerjaan selesai',
                    'created_at'   => $completedAt,
                ]);

                // Detail Feedback Pelapor
                if (!empty($item['feedback'])) {
                    Feedback::create([
                        'pengaduan_id'     => $pengaduan->id,
                        'user_id'          => $user->id,
                        'rating_respon'    => $item['rating'],
                        'rating_kualitas'  => $item['rating'],
                        'rating_pelayanan' => $item['rating'],
                        'komentar'         => $item['feedback'],
                        'is_satisfied'     => true,
                        'created_at'       => $completedAt->copy()->addHours(2),
                    ]);

                    Log::create([
                        'pengaduan_id' => $pengaduan->id,
                        'user_id'      => $user->id,
                        'action'       => Log::ACTION_FEEDBACK_GIVEN,
                        'description'  => 'Memberikan rating ' . $item['rating'] . ' bintang: ' . $item['feedback'],
                        'created_at'   => $completedAt->copy()->addHours(2),
                    ]);
                }
            }

            $counter++;
        }
    }
}
