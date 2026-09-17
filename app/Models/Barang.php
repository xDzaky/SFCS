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
        'unit_sarpras',
        'tipe_transaksi',
        'stok_total',
        'stok_tersedia',
        'stok_rusak',
        'is_active',
    ];

    protected $casts = [
        'stok_total'     => 'integer',
        'stok_tersedia'  => 'integer',
        'stok_rusak'     => 'integer',
        'is_active'      => 'boolean',
    ];

    /** Barang Sarpras Atas: proyektor, kabel, mic, dll (dikembalikan) */
    public function isAtas(): bool
    {
        return $this->unit_sarpras === 'atas';
    }

    /** Barang Sarpras Bawah: ATK, kertas, spidol, dll (tidak dikembalikan) */
    public function isBawah(): bool
    {
        return $this->unit_sarpras === 'bawah';
    }

    /** Apakah transaksi ini adalah permintaan (tidak dikembalikan)? */
    public function isPermintaan(): bool
    {
        return $this->tipe_transaksi === 'minta';
    }

    /** Label unit sarpras yang ramah tampilan */
    public function getLabelUnitSarprasAttribute(): string
    {
        return $this->unit_sarpras === 'bawah' ? 'Sarpras Bawah' : 'Sarpras Atas';
    }

    /** Label tipe transaksi */
    public function getLabelTipeTransaksiAttribute(): string
    {
        return $this->tipe_transaksi === 'minta' ? 'Permintaan' : 'Peminjaman';
    }

    public function pinjamans(): HasMany
    {
        return $this->hasMany(Pinjaman::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAtas($query)
    {
        return $query->where('unit_sarpras', 'atas');
    }

    public function scopeBawah($query)
    {
        return $query->where('unit_sarpras', 'bawah');
    }
}
