<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $table = 'barangs';

    protected $fillable = [
        'kode_barang',
        'nama',
        'kategori',
        'lokasi',
        'stok_total',
        'stok_tersedia',
        'stok_rusak',
        'is_active',
    ];

    protected $casts = [
        'stok_total' => 'integer',
        'stok_tersedia' => 'integer',
        'stok_rusak' => 'integer',
        'is_active' => 'boolean',
    ];

    public function pinjamans(): HasMany
    {
        return $this->hasMany(Pinjaman::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
