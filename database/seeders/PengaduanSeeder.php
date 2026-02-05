<?php

namespace Database\Seeders;

use App\Models\Pengaduan;
use App\Models\HistoryPengaduan;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Gedung;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PengaduanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users for seeding
        $siswas = User::where('role', 'siswa')->get();
        $gurus = User::where('role', 'guru')->get();
        $teknisis = User::where('role', 'teknisi')->get();
        $admin = User::where('role', 'admin')->first();
        
        $kategoris = Kategori::all();
        $gedungs = Gedung::all();

        if ($siswas->isEmpty() || $kategoris->isEmpty() || $gedungs->isEmpty()) {
            return;
        }

        $pengaduanData = [
            // Pengaduan selesai dengan feedback
            [
                'judul' => 'AC Kelas tidak dingin',
                'deskripsi' => 'AC di kelas X IPA 1 tidak dingin sama sekali meski sudah dinyalakan sejak pagi. Sudah coba matikan dan nyalakan lagi tapi tetap tidak dingin. Filter AC terlihat kotor.',
                'lokasi_detail' => 'Kelas X IPA 1, Lantai 1',
                'status' => 'selesai',
                'prioritas' => 'tinggi',
                'kategori' => 'AC & Pendingin',
                'gedung' => 'GD-B',
                'days_ago' => 7,
                'rating' => 5,
                'feedback' => 'Perbaikan sangat cepat dan AC sekarang sudah dingin. Terima kasih!',
            ],
            [
                'judul' => 'Lampu kelas mati',
                'deskripsi' => 'Dua lampu di bagian depan kelas XI IPS 1 mati. Sangat mengganggu saat pelajaran karena papan tulis menjadi gelap.',
                'lokasi_detail' => 'Kelas XI IPS 1, Lantai 2, Bagian Depan',
                'status' => 'selesai',
                'prioritas' => 'sedang',
                'kategori' => 'Kelistrikan',
                'gedung' => 'GD-C',
                'days_ago' => 10,
                'rating' => 4,
                'feedback' => 'Lampu sudah diganti, terima kasih teknisi.',
            ],
            
            // Pengaduan dalam proses
            [
                'judul' => 'Toilet mampet di lantai 2',
                'deskripsi' => 'Toilet di gedung B lantai 2 mampet dan air tidak bisa mengalir. Sudah beberapa hari tidak bisa digunakan.',
                'lokasi_detail' => 'Toilet Lantai 2, Kamar ke-3',
                'status' => 'diproses',
                'prioritas' => 'urgent',
                'kategori' => 'Plumbing',
                'gedung' => 'GD-B',
                'days_ago' => 2,
            ],
            [
                'judul' => 'Proyektor tidak menyala',
                'deskripsi' => 'Proyektor di Lab Komputer 1 tidak mau menyala. Sudah dicek kabelnya tapi tetap mati.',
                'lokasi_detail' => 'Lab Komputer 1, Lantai 1',
                'status' => 'diproses',
                'prioritas' => 'tinggi',
                'kategori' => 'IT & Multimedia',
                'gedung' => 'GD-E',
                'days_ago' => 3,
            ],
            
            // Pengaduan diverifikasi (ditugaskan ke teknisi)
            [
                'judul' => 'Kursi patah di kelas XII IPA 2',
                'deskripsi' => 'Ada 3 kursi yang kakinya patah dan tidak bisa dipakai. Mohon segera diperbaiki.',
                'lokasi_detail' => 'Kelas XII IPA 2, Lantai 1, Baris ke-3',
                'status' => 'diverifikasi',
                'prioritas' => 'sedang',
                'kategori' => 'Furniture',
                'gedung' => 'GD-D',
                'days_ago' => 1,
            ],
            [
                'judul' => 'Keran air bocor di toilet',
                'deskripsi' => 'Keran air di toilet gedung C lantai 1 bocor dan air terus mengalir. Boros air.',
                'lokasi_detail' => 'Toilet Lantai 1, Wastafel kedua dari kiri',
                'status' => 'diverifikasi',
                'prioritas' => 'tinggi',
                'kategori' => 'Plumbing',
                'gedung' => 'GD-C',
                'days_ago' => 1,
            ],
            
            // Pengaduan baru (pending)
            [
                'judul' => 'Jendela tidak bisa ditutup',
                'deskripsi' => 'Jendela di kelas X IPS 2 tidak bisa ditutup rapat. Saat hujan, air masuk ke dalam kelas.',
                'lokasi_detail' => 'Kelas X IPS 2, Lantai 2, Jendela sisi kiri',
                'status' => 'pending',
                'prioritas' => 'sedang',
                'kategori' => 'Bangunan',
                'gedung' => 'GD-B',
                'days_ago' => 0,
            ],
            [
                'judul' => 'Kipas angin berisik',
                'deskripsi' => 'Kipas angin di perpustakaan ruang baca mengeluarkan suara berisik yang mengganggu.',
                'lokasi_detail' => 'Ruang Baca Perpustakaan, Lantai 1',
                'status' => 'pending',
                'prioritas' => 'rendah',
                'kategori' => 'AC & Pendingin',
                'gedung' => 'GD-F',
                'days_ago' => 0,
            ],
            [
                'judul' => 'Stop kontak rusak di Lab Fisika',
                'deskripsi' => 'Stop kontak di Lab Fisika tidak berfungsi. Sudah dicoba beberapa alat tapi tidak ada yang nyala.',
                'lokasi_detail' => 'Lab Fisika, Lantai 2, Meja Praktikum nomor 4',
                'status' => 'pending',
                'prioritas' => 'tinggi',
                'kategori' => 'Kelistrikan',
                'gedung' => 'GD-E',
                'days_ago' => 0,
            ],
            
            // Pengaduan ditolak
            [
                'judul' => 'Minta AC baru untuk kelas',
                'deskripsi' => 'AC di kelas kami sudah tua, mohon diganti dengan AC baru yang lebih dingin.',
                'lokasi_detail' => 'Kelas XII IPS 3, Lantai 3',
                'status' => 'ditolak',
                'prioritas' => 'rendah',
                'kategori' => 'AC & Pendingin',
                'gedung' => 'GD-D',
                'days_ago' => 5,
                'catatan_admin' => 'Ditolak karena AC masih berfungsi dengan baik. Pengaduan ini bukan kerusakan melainkan permintaan pengadaan baru.',
            ],
        ];

        $counter = 1;
        foreach ($pengaduanData as $data) {
            $kategori = $kategoris->where('nama', $data['kategori'])->first();
            $gedung = $gedungs->where('kode', $data['gedung'])->first();
            
            if (!$kategori || !$gedung) {
                continue;
            }

            // Randomly pick user (siswa or guru)
            $user = rand(0, 1) ? $siswas->random() : ($gurus->isNotEmpty() ? $gurus->random() : $siswas->random());
            
            $createdAt = Carbon::now()->subDays($data['days_ago']);
            
            $pengaduan = Pengaduan::create([
                'kode_pengaduan' => 'PGD-' . date('Ymd', strtotime($createdAt)) . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'kategori_id' => $kategori->id,
                'gedung_id' => $gedung->id,
                'lokasi_detail' => $data['lokasi_detail'],
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'],
                'prioritas' => $data['prioritas'],
                'status' => $data['status'],
                'teknisi_id' => in_array($data['status'], ['diproses', 'selesai']) ? $teknisis->random()->id : null,
                'catatan_admin' => $data['catatan_admin'] ?? null,
                'verified_at' => in_array($data['status'], ['diverifikasi', 'diproses', 'selesai', 'ditolak']) ? $createdAt->copy()->addHours(rand(1, 6)) : null,
                'assigned_at' => in_array($data['status'], ['diproses', 'selesai']) ? $createdAt->copy()->addHours(rand(6, 12)) : null,
                'started_at' => in_array($data['status'], ['diproses', 'selesai']) ? $createdAt->copy()->addHours(rand(12, 24)) : null,
                'completed_at' => $data['status'] === 'selesai' ? $createdAt->copy()->addDays(rand(1, 3)) : null,
                'rating' => $data['rating'] ?? null,
                'feedback' => $data['feedback'] ?? null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Create history
            HistoryPengaduan::create([
                'pengaduan_id' => $pengaduan->id,
                'user_id' => $user->id,
                'status_lama' => null,
                'status_baru' => 'pending',
                'keterangan' => 'Pengaduan baru dibuat',
                'created_at' => $createdAt,
            ]);

            if (in_array($data['status'], ['diverifikasi', 'diproses', 'selesai', 'ditolak'])) {
                HistoryPengaduan::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id' => $admin ? $admin->id : $user->id,
                    'status_lama' => 'pending',
                    'status_baru' => $data['status'] === 'ditolak' ? 'ditolak' : 'diverifikasi',
                    'keterangan' => $data['status'] === 'ditolak' ? 'Pengaduan ditolak oleh admin' : 'Pengaduan diverifikasi oleh admin',
                    'created_at' => $createdAt->copy()->addHours(rand(1, 6)),
                ]);
            }

            if (in_array($data['status'], ['diproses', 'selesai'])) {
                HistoryPengaduan::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id' => $teknisis->random()->id,
                    'status_lama' => 'diverifikasi',
                    'status_baru' => 'diproses',
                    'keterangan' => 'Pengaduan sedang dikerjakan oleh teknisi',
                    'created_at' => $createdAt->copy()->addHours(rand(12, 24)),
                ]);
            }

            if ($data['status'] === 'selesai') {
                HistoryPengaduan::create([
                    'pengaduan_id' => $pengaduan->id,
                    'user_id' => $teknisis->random()->id,
                    'status_lama' => 'diproses',
                    'status_baru' => 'selesai',
                    'keterangan' => 'Pengaduan telah selesai dikerjakan',
                    'created_at' => $createdAt->copy()->addDays(rand(1, 3)),
                ]);
            }

            $counter++;
        }
    }
}
