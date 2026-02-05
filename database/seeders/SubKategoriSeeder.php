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
                'Stop Kontak', 'Saklar Lampu', 'Lampu', 'MCB/Sekring', 
                'Kabel', 'Instalasi Listrik'
            ],
            'Plumbing' => [
                'Keran Air', 'Kloset', 'Wastafel', 'Pipa', 
                'Floor Drain', 'Shower', 'Tandon Air'
            ],
            'Furniture' => [
                'Meja', 'Kursi', 'Lemari', 'Papan Tulis', 
                'Rak', 'Pintu', 'Jendela'
            ],
            'AC & Pendingin' => [
                'AC Split', 'AC Central', 'Kipas Angin', 
                'Exhaust Fan', 'Remote AC'
            ],
            'Bangunan' => [
                'Atap/Plafon', 'Lantai', 'Dinding', 'Cat', 
                'Pagar', 'Tangga', 'Kanopi'
            ],
            'IT & Multimedia' => [
                'Komputer', 'Proyektor', 'WiFi', 'CCTV', 
                'Sound System', 'Printer', 'Jaringan LAN'
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
