<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaduanSchedule extends Model
{
    protected $table = 'pengaduan_schedules';

    public $timestamps = false;

    protected $fillable = [
        'pengaduan_id',
        'from_start',
        'to_start',
        'from_end',
        'to_end',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'from_start' => 'datetime',
        'to_start' => 'datetime',
        'from_end' => 'datetime',
        'to_end' => 'datetime',
        'created_at' => 'datetime',
    ];

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

