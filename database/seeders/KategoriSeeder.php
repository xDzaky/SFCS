<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            [
                'nama' => 'Kelistrikan',
                'deskripsi' => 'Masalah fasilitas listrik sekolah seperti lampu kelas mati, stop kontak rusak, saklar rusak, kabel terkelupas, dan MCB turun',
                'icon' => 'fa-bolt',
            ],
            [
                'nama' => 'Plumbing',
                'deskripsi' => 'Masalah fasilitas air sekolah seperti keran air rusak, wastafel rusak, kloset bermasalah, pipa bocor, dan saluran mampet',
                'icon' => 'fa-faucet',
            ],
            [
                'nama' => 'Furniture',
                'deskripsi' => 'Masalah barang/perabot sekolah seperti meja rusak, kursi rusak, lemari rusak, papan tulis rusak, dan rak buku rusak',
                'icon' => 'fa-chair',
            ],
            [
                'nama' => 'AC & Pendingin',
                'deskripsi' => 'Masalah pendingin ruangan seperti AC tidak dingin, AC bocor, AC mati, kipas angin rusak, dan ventilasi bermasalah',
                'icon' => 'fa-snowflake',
            ],
            [
                'nama' => 'Bangunan',
                'deskripsi' => 'Masalah bangunan sekolah seperti atap bocor, dinding retak, lantai rusak, pintu rusak, jendela rusak, dan plafon rusak',
                'icon' => 'fa-building',
            ],
            [
                'nama' => 'IT & Multimedia',
                'deskripsi' => 'Masalah perangkat IT sekolah seperti LCD/proyektor rusak, komputer rusak, WiFi bermasalah, speaker rusak, dan printer rusak',
                'icon' => 'fa-desktop',
            ],
            [
                'nama' => 'Kebersihan',
                'deskripsi' => 'Masalah kebersihan fasilitas sekolah seperti toilet kotor, tempat sampah rusak/penuh, dan sanitasi lingkungan',
                'icon' => 'fa-broom',
            ],
            [
                'nama' => 'Keamanan',
                'deskripsi' => 'Masalah keamanan fasilitas sekolah seperti kunci rusak, CCTV bermasalah, pagar rusak, lampu penerangan mati, dan APAR',
                'icon' => 'fa-shield-alt',
            ],
            [
                'nama' => 'Lainnya',
                'deskripsi' => 'Masalah lain yang tidak termasuk kategori di atas',
                'icon' => 'fa-ellipsis-h',
            ],
        ];

        foreach ($kategoris as $kategoriData) {
            Kategori::updateOrCreate(
                ['nama' => $kategoriData['nama']],
                $kategoriData
            );
        }
    }
}
