<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Jurusan extends Model
{
    protected $table = 'jurusans';

    protected $fillable = [
        'kode',
        'nama',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ruangans(): BelongsToMany
    {
        return $this->belongsToMany(Ruangan::class, 'jurusan_ruangan')
            ->withPivot('is_primary')
            ->withTimestamps();
    }
}
