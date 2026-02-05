<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    protected $table = 'logs';
    
    public $timestamps = false;
    
    protected $fillable = [
        'pengaduan_id',
        'user_id',
        'action',
        'description',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    // Action constants
    const ACTION_CREATED = 'created';
    const ACTION_STATUS_CHANGED = 'status_changed';
    const ACTION_ASSIGNED = 'assigned';
    const ACTION_PHOTO_UPLOADED = 'photo_uploaded';
    const ACTION_FEEDBACK_GIVEN = 'feedback_given';
    const ACTION_REOPENED = 'reopened';

    /**
     * Get pengaduan
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    /**
     * Get user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action display name
     */
    public function getActionDisplayAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATED => 'Pengaduan dibuat',
            self::ACTION_STATUS_CHANGED => 'Status diubah',
            self::ACTION_ASSIGNED => 'Ditugaskan ke teknisi',
            self::ACTION_PHOTO_UPLOADED => 'Foto diupload',
            self::ACTION_FEEDBACK_GIVEN => 'Feedback diberikan',
            self::ACTION_REOPENED => 'Pengaduan dibuka kembali',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATED => 'fa-plus-circle',
            self::ACTION_STATUS_CHANGED => 'fa-exchange-alt',
            self::ACTION_ASSIGNED => 'fa-user-tag',
            self::ACTION_PHOTO_UPLOADED => 'fa-camera',
            self::ACTION_FEEDBACK_GIVEN => 'fa-star',
            self::ACTION_REOPENED => 'fa-redo',
            default => 'fa-info-circle',
        };
    }

    /**
     * Create a log entry
     */
    public static function createLog(
        int $pengaduanId,
        int $userId,
        string $action,
        ?string $description = null,
        ?array $oldValue = null,
        ?array $newValue = null
    ): self {
        return self::create([
            'pengaduan_id' => $pengaduanId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
