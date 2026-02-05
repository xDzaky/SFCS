<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    protected $table = 'kategoris';
    
    protected $fillable = [
        'nama',
        'deskripsi',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all pengaduan in this category
     */
    public function pengaduans(): HasMany
    {
        return $this->hasMany(Pengaduan::class);
    }

    /**
     * Get all sub kategoris
     */
    public function subKategoris(): HasMany
    {
        return $this->hasMany(SubKategori::class);
    }

    /**
     * Get active sub kategoris
     */
    public function activeSubKategoris(): HasMany
    {
        return $this->hasMany(SubKategori::class)->where('is_active', true);
    }

    /**
     * Scope for active kategoris
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
