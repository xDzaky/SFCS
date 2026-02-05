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
                'deskripsi' => 'Masalah terkait instalasi listrik, lampu, stop kontak, saklar, kabel terkelupas, dan korsleting',
                'icon' => 'fa-bolt',
            ],
            [
                'nama' => 'Plumbing',
                'deskripsi' => 'Masalah terkait saluran air, keran bocor, toilet mampet, pipa bocor, dan saluran tersumbat',
                'icon' => 'fa-faucet',
            ],
            [
                'nama' => 'Furniture',
                'deskripsi' => 'Masalah terkait meja rusak, kursi rusak, lemari rusak, papan tulis, dan rak buku',
                'icon' => 'fa-chair',
            ],
            [
                'nama' => 'AC & Pendingin',
                'deskripsi' => 'Masalah terkait AC tidak dingin, AC bocor, AC mati, kipas angin rusak, dan ventilasi',
                'icon' => 'fa-snowflake',
            ],
            [
                'nama' => 'Bangunan',
                'deskripsi' => 'Masalah struktur bangunan seperti atap bocor, dinding retak, lantai rusak, pintu, jendela, dan plafon',
                'icon' => 'fa-building',
            ],
            [
                'nama' => 'IT & Multimedia',
                'deskripsi' => 'Masalah terkait komputer, proyektor, WiFi, speaker, printer, dan perangkat multimedia',
                'icon' => 'fa-desktop',
            ],
            [
                'nama' => 'Kebersihan',
                'deskripsi' => 'Masalah terkait kebersihan ruangan, toilet, tempat sampah, dan sanitasi lingkungan',
                'icon' => 'fa-broom',
            ],
            [
                'nama' => 'Keamanan',
                'deskripsi' => 'Masalah terkait kunci rusak, CCTV, pagar, lampu penerangan, dan alat pemadam kebakaran',
                'icon' => 'fa-shield-alt',
            ],
            [
                'nama' => 'Lainnya',
                'deskripsi' => 'Masalah lain yang tidak termasuk kategori di atas',
                'icon' => 'fa-ellipsis-h',
            ],
        ];

        foreach ($kategoris as $kategoriData) {
            Kategori::create($kategoriData);
        }
    }
}
