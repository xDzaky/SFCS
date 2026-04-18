<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PinjamanFeedback extends Model
{
    protected $table = 'pinjaman_feedbacks';

    protected $fillable = [
        'pinjaman_id',
        'user_id',
        'rating',
        'komentar',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
