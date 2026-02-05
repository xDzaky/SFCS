<?php

namespace Database\Seeders;

use App\Models\Gedung;
use Illuminate\Database\Seeder;

class GedungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gedungs = [
            [
                'nama' => 'Gedung A - Administrasi',
                'kode' => 'GD-A',
                'deskripsi' => 'Gedung utama untuk kantor administrasi dan kepala sekolah. Terdiri dari Ruang Kepala Sekolah, Ruang Wakil Kepsek, Ruang TU, Ruang Guru, Ruang BK, dan Ruang Rapat.',
                'jumlah_lantai' => 2,
            ],
            [
                'nama' => 'Gedung B - Kelas X',
                'kode' => 'GD-B',
                'deskripsi' => 'Gedung kelas untuk siswa tingkat X (IPA, IPS, dan Bahasa) dengan toilet di setiap lantai.',
                'jumlah_lantai' => 3,
            ],
            [
                'nama' => 'Gedung C - Kelas XI',
                'kode' => 'GD-C',
                'deskripsi' => 'Gedung kelas untuk siswa tingkat XI (IPA, IPS, dan Bahasa) dengan toilet di setiap lantai.',
                'jumlah_lantai' => 3,
            ],
            [
                'nama' => 'Gedung D - Kelas XII',
                'kode' => 'GD-D',
                'deskripsi' => 'Gedung kelas untuk siswa tingkat XII (IPA, IPS, dan Bahasa) dengan toilet di setiap lantai.',
                'jumlah_lantai' => 3,
            ],
            [
                'nama' => 'Gedung E - Laboratorium',
                'kode' => 'GD-E',
                'deskripsi' => 'Gedung laboratorium IPA (Fisika, Kimia, Biologi), Lab Komputer, Lab Bahasa, dan ruang persiapan.',
                'jumlah_lantai' => 2,
            ],
            [
                'nama' => 'Gedung F - Perpustakaan & Aula',
                'kode' => 'GD-F',
                'deskripsi' => 'Gedung perpustakaan, aula serbaguna, ruang multimedia, dan ruang pertemuan.',
                'jumlah_lantai' => 2,
            ],
            [
                'nama' => 'Gedung G - Olahraga',
                'kode' => 'GD-G',
                'deskripsi' => 'Gedung olahraga indoor, lapangan basket, ruang fitness, dan ruang ganti.',
                'jumlah_lantai' => 1,
            ],
            [
                'nama' => 'Gedung H - Workshop',
                'kode' => 'GD-H',
                'deskripsi' => 'Gedung workshop dan praktikum, bengkel, dan ruang keterampilan.',
                'jumlah_lantai' => 1,
            ],
            [
                'nama' => 'Masjid Al-Hikmah',
                'kode' => 'MSJ',
                'deskripsi' => 'Masjid sekolah untuk kegiatan ibadah dan keagamaan.',
                'jumlah_lantai' => 2,
            ],
            [
                'nama' => 'Kantin & Koperasi',
                'kode' => 'KNT',
                'deskripsi' => 'Area kantin sekolah dan koperasi siswa.',
                'jumlah_lantai' => 1,
            ],
            [
                'nama' => 'Pos Satpam',
                'kode' => 'PST',
                'deskripsi' => 'Pos keamanan dan area parkir.',
                'jumlah_lantai' => 1,
            ],
        ];

        foreach ($gedungs as $gedungData) {
            Gedung::create($gedungData);
        }
    }
}
