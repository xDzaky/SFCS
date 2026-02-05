<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gedung extends Model
{
    protected $table = 'gedungs';
    
    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'jumlah_lantai',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all pengaduan in this gedung
     */
    public function pengaduans(): HasMany
    {
        return $this->hasMany(Pengaduan::class);
    }

    /**
     * Get all ruangans in this gedung
     */
    public function ruangans(): HasMany
    {
        return $this->hasMany(Ruangan::class);
    }

    /**
     * Get active ruangans
     */
    public function activeRuangans(): HasMany
    {
        return $this->hasMany(Ruangan::class)->where('is_active', true);
    }

    /**
     * Get lantai options as array
     */
    public function getLantaiOptionsAttribute(): array
    {
        $options = [];
        for ($i = 1; $i <= $this->jumlah_lantai; $i++) {
            $options[] = (string) $i;
        }
        return $options;
    }

    /**
     * Scope for active gedungs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
