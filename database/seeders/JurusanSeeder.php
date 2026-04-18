<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusans = [
            ['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak'],
            ['kode' => 'MP', 'nama' => 'Manajemen Perkantoran'],
            ['kode' => 'AK', 'nama' => 'Akuntansi'],
            ['kode' => 'BD', 'nama' => 'Bisnis Digital'],
            ['kode' => 'LP', 'nama' => 'Layanan Perbankan'],
        ];

        foreach ($jurusans as $jurusan) {
            Jurusan::updateOrCreate(
                ['kode' => $jurusan['kode']],
                ['nama' => $jurusan['nama'], 'is_active' => true]
            );
        }
    }
}
