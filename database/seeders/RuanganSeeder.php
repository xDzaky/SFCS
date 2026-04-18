<?php

namespace Database\Seeders;

use App\Models\Gedung;
use App\Models\Ruangan;
use Illuminate\Database\Seeder;

class RuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all gedungs from database
        $gedungs = Gedung::all();

        foreach ($gedungs as $gedung) {
            // Use first letter of building name after "Gedung " as prefix
            $prefix = strtoupper(substr($gedung->nama, 7, 1));
            
            // Generate rooms for each floor of the building
            for ($lantai = 1; $lantai <= $gedung->jumlah_lantai; $lantai++) {
                // Keep seeding deterministic and idempotent.
                $roomCount = 4;
                
                for ($i = 1; $i <= $roomCount; $i++) {
                    // Use gedung_id to ensure uniqueness
                    $kode = $prefix . $gedung->id . '-' . $lantai . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);

                    $roomTypes = ['Ruang Kelas', 'Ruang Rapat', 'Ruang Praktik', 'Ruang Lab', 'Ruang Kantor'];
                    $roomName = $roomTypes[($i - 1) % count($roomTypes)] . ' ' . $lantai . str_pad($i, 2, '0', STR_PAD_LEFT);
                    
                    Ruangan::updateOrCreate(
                        ['kode' => $kode],
                        [
                            'gedung_id' => $gedung->id,
                            'lantai' => (string) $lantai,
                            'nama' => $roomName,
                            'kapasitas' => 30 + ($i * 5),
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
