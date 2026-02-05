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
                // Create 3-5 rooms per floor
                $roomCount = rand(3, 5);
                
                for ($i = 1; $i <= $roomCount; $i++) {
                    // Use gedung_id to ensure uniqueness
                    $kode = $prefix . $gedung->id . '-' . $lantai . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                    
                    // Check if already exists
                    if (Ruangan::where('kode', $kode)->exists()) {
                        continue;
                    }
                    
                    $roomTypes = ['Ruang Kelas', 'Ruang Rapat', 'Ruang Praktik', 'Ruang Lab', 'Ruang Kantor'];
                    $roomName = $roomTypes[array_rand($roomTypes)] . ' ' . $lantai . str_pad($i, 2, '0', STR_PAD_LEFT);
                    
                    Ruangan::create([
                        'gedung_id' => $gedung->id,
                        'kode' => $kode,
                        'lantai' => (string) $lantai,
                        'nama' => $roomName,
                        'kapasitas' => rand(20, 50),
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
