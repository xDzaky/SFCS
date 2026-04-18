<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    protected $table = 'ruangans';
    
    protected $fillable = [
        'gedung_id',
        'lantai',
        'nama',
        'kode',
        'source_ref',
        'kapasitas',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get gedung
     */
    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class);
    }

    /**
     * Get all pengaduan in this ruangan
     */
    public function pengaduans(): HasMany
    {
        return $this->hasMany(Pengaduan::class);
    }

    public function jurusans(): BelongsToMany
    {
        return $this->belongsToMany(Jurusan::class, 'jurusan_ruangan')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Get full location name
     */
    public function getFullLocationAttribute(): string
    {
        return $this->gedung->nama . ' - Lantai ' . $this->lantai . ' - ' . $this->nama;
    }

    /**
     * Scope for active ruangans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by gedung and lantai
     */
    public function scopeByGedungLantai($query, $gedungId, $lantai)
    {
        return $query->where('gedung_id', $gedungId)->where('lantai', $lantai);
    }
}
