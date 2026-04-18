<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\SubKategori;
use Illuminate\Database\Seeder;

class SubKategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sub-kategori per kategori sesuai dengan data di database
        $subKategoris = [
            'Kelistrikan' => [
                'Lampu Kelas', 'Stop Kontak', 'Saklar Lampu', 'MCB/Sekring',
                'Kabel Listrik', 'Instalasi Listrik'
            ],
            'Plumbing' => [
                'Keran Air', 'Wastafel', 'Kloset', 'Pipa Air',
                'Saluran Air', 'Floor Drain', 'Tandon Air'
            ],
            'Furniture' => [
                'Papan Tulis', 'Meja Siswa', 'Kursi Siswa', 'Meja Guru',
                'Lemari', 'Rak Buku'
            ],
            'AC & Pendingin' => [
                'AC Split', 'AC Central', 'Kipas Angin',
                'Exhaust Fan', 'Remote AC'
            ],
            'Bangunan' => [
                'Atap/Plafon', 'Lantai', 'Dinding', 'Pintu',
                'Jendela', 'Tangga', 'Kanopi'
            ],
            'IT & Multimedia' => [
                'LCD/Proyektor', 'Komputer', 'WiFi', 'Jaringan LAN',
                'Printer', 'Speaker Kelas', 'CCTV'
            ],
            'Kebersihan' => [
                'Toilet', 'Tempat Sampah', 'Drainase', 'Area Kotor'
            ],
            'Keamanan' => [
                'Kunci Pintu', 'Gembok', 'Pagar', 'Lampu Keamanan', 'APAR'
            ],
            'Lainnya' => [
                'Fasilitas Lainnya'
            ],
        ];

        foreach ($subKategoris as $kategoriNama => $subItems) {
            $kategori = Kategori::where('nama', $kategoriNama)->first();
            
            if ($kategori) {
                foreach ($subItems as $subNama) {
                    SubKategori::firstOrCreate([
                        'kategori_id' => $kategori->id,
                        'nama' => $subNama,
                    ], [
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
