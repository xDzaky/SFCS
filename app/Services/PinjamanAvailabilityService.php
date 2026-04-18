<?php

namespace App\Services;

use App\Models\Barang;
use DomainException;

class PinjamanAvailabilityService
{
    public function ensureStockAvailable(Barang $barang, int $qty): void
    {
        if ($qty <= 0) {
            throw new DomainException('Jumlah pinjam harus lebih dari 0.');
        }

        if ($barang->stok_tersedia < $qty) {
            throw new DomainException('Stok tersedia tidak mencukupi.');
        }
    }

    public function reserveStock(Barang $barang, int $qty): void
    {
        $this->ensureStockAvailable($barang, $qty);
        $barang->decrement('stok_tersedia', $qty);
    }

    public function releaseStock(Barang $barang, int $qty): void
    {
        $barang->increment('stok_tersedia', $qty);
    }
}
