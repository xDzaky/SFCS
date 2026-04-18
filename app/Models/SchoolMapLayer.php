<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolMapLayer extends Model
{
    protected $fillable = [
        'school_map_id',
        'gedung_id',
        'page_number',
        'label',
        'lantai_label',
        'layer_scope',
        'width',
        'height',
        'sort_order',
    ];

    protected $casts = [
        'gedung_id' => 'integer',
        'page_number' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
    ];

    public function schoolMap(): BelongsTo
    {
        return $this->belongsTo(SchoolMap::class);
    }

    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(SchoolMapArea::class)->orderBy('label');
    }
}
