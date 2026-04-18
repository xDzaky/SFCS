<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            JurusanSeeder::class,
            KategoriSeeder::class,
            SubKategoriSeeder::class,
            GedungSeeder::class,
            RuanganSeeder::class,
            SettingSeeder::class,
            BarangSeeder::class,
            // PengaduanSeeder::class, // Commented: Jangan create sample data
        ]);
    }
}
