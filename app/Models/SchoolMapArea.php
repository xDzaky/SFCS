<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolMapArea extends Model
{
    protected $fillable = [
        'school_map_layer_id',
        'gedung_id',
        'ruangan_id',
        'label',
        'shape_type',
        'geometry_json',
        'color',
        'icon',
        'is_clickable',
    ];

    protected $casts = [
        'geometry_json' => 'array',
        'is_clickable' => 'boolean',
    ];

    public function layer(): BelongsTo
    {
        return $this->belongsTo(SchoolMapLayer::class, 'school_map_layer_id');
    }

    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class);
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }
}
