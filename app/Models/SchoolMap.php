<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SchoolMap extends Model
{
    protected $fillable = [
        'nama',
        'slug',
        'file_path',
        'file_type',
        'page_count',
        'is_active',
    ];

    protected $casts = [
        'page_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function layers(): HasMany
    {
        return $this->hasMany(SchoolMapLayer::class)->orderBy('sort_order');
    }

    public function areas(): HasManyThrough
    {
        return $this->hasManyThrough(SchoolMapArea::class, SchoolMapLayer::class);
    }
}
