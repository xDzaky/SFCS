<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['kode_barang' => 'BRG-001', 'nama' => 'Proyektor Epson X1', 'kategori' => 'Multimedia', 'lokasi' => 'Ruang Sarpras', 'stok_total' => 8, 'stok_tersedia' => 6, 'stok_rusak' => 2, 'is_active' => true],
            ['kode_barang' => 'BRG-002', 'nama' => 'Kabel HDMI 10m', 'kategori' => 'Multimedia', 'lokasi' => 'Ruang Sarpras', 'stok_total' => 15, 'stok_tersedia' => 15, 'stok_rusak' => 0, 'is_active' => true],
            ['kode_barang' => 'BRG-003', 'nama' => 'Bola Basket', 'kategori' => 'Olahraga', 'lokasi' => 'Gudang Olahraga', 'stok_total' => 12, 'stok_tersedia' => 10, 'stok_rusak' => 2, 'is_active' => true],
        ];

        foreach ($items as $item) {
            Barang::updateOrCreate(['kode_barang' => $item['kode_barang']], $item);
        }
    }
}
