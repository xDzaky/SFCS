<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryPengaduan extends Model
{
    protected $table = 'history_pengaduans';
    
    protected $fillable = [
        'pengaduan_id',
        'user_id',
        'status_lama',
        'status_baru',
        'keterangan',
    ];

    /**
     * Get the pengaduan this history belongs to
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    /**
     * Get the user who made this change
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get status label with appropriate color class
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'warning',
            'diverifikasi' => 'info',
            'ditolak' => 'danger',
            'diproses' => 'primary',
            'selesai' => 'success',
        ];

        $color = $badges[$this->status_baru] ?? 'secondary';
        $label = ucfirst($this->status_baru);

        return '<span class="badge bg-' . $color . '">' . $label . '</span>';
    }
}
