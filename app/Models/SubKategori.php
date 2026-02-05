<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubKategori extends Model
{
    protected $table = 'sub_kategoris';
    
    protected $fillable = [
        'kategori_id',
        'nama',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get parent kategori
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Get all pengaduan with this sub category
     */
    public function pengaduans(): HasMany
    {
        return $this->hasMany(Pengaduan::class);
    }

    /**
     * Scope for active sub kategoris
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
