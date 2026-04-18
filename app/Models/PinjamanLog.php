<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PinjamanLog extends Model
{
    protected $table = 'pinjaman_logs';

    public $timestamps = false;

    protected $fillable = [
        'pinjaman_id',
        'user_id',
        'action',
        'description',
        'old_value',
        'new_value',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
    ];

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createLog(
        int $pinjamanId,
        ?int $userId,
        string $action,
        ?string $description = null,
        ?array $oldValue = null,
        ?array $newValue = null
    ): self {
        return self::create([
            'pinjaman_id' => $pinjamanId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }
}
