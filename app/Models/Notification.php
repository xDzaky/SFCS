<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifications';
    
    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'jenis',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Jenis constants
    const JENIS_PENGADUAN_CREATED = 'pengaduan_created';
    const JENIS_STATUS_CHANGED = 'status_changed';
    const JENIS_ASSIGNED = 'assigned';
    const JENIS_FEEDBACK_REMINDER = 'feedback_reminder';
    const JENIS_OVERDUE = 'overdue';

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->is_read || $this->read_at !== null;
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    /**
     * Get jenis icon
     */
    public function getJenisIconAttribute(): string
    {
        return match($this->jenis) {
            self::JENIS_PENGADUAN_CREATED => 'fa-plus-circle text-primary',
            self::JENIS_STATUS_CHANGED => 'fa-exchange-alt text-info',
            self::JENIS_ASSIGNED => 'fa-user-tag text-warning',
            self::JENIS_FEEDBACK_REMINDER => 'fa-star text-success',
            self::JENIS_OVERDUE => 'fa-exclamation-triangle text-danger',
            default => 'fa-bell text-secondary',
        };
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false)->orWhereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Create notification
     */
    public static function send(
        int $userId,
        string $jenis,
        string $judul,
        string $pesan,
        ?string $link = null
    ): self {
        // Auto-adjust link based on user role if it's a pengaduan link
        if ($link && str_contains($link, 'pengaduan')) {
            $user = User::find($userId);
            if ($user) {
                // If user is siswa/guru, use siswa route
                if (in_array($user->role, ['siswa', 'guru'])) {
                    $link = str_replace('/admin/pengaduan', '/pengaduan', $link);
                }
                // If user is teknisi, use teknisi route
                elseif ($user->role === 'teknisi') {
                    if (str_contains($link, '/admin/pengaduan')) {
                        $link = str_replace('/admin/pengaduan', '/teknisi/pengaduan', $link);
                    } elseif (!str_contains($link, '/teknisi/pengaduan') && str_contains($link, '/pengaduan')) {
                        $link = str_replace('/pengaduan', '/teknisi/pengaduan', $link);
                    }
                }
                // Admin/superadmin keep admin route
            }
        }
        
        return self::create([
            'user_id' => $userId,
            'jenis' => $jenis,
            'judul' => $judul,
            'pesan' => $pesan,
            'link' => $link,
            'is_read' => false,
        ]);
    }
}
