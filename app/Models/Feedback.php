<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    
    public $timestamps = false;
    
    protected $fillable = [
        'pengaduan_id',
        'user_id',
        'rating_respon',
        'rating_kualitas',
        'rating_pelayanan',
        'komentar',
        'is_satisfied',
    ];

    protected $casts = [
        'is_satisfied' => 'boolean',
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Get pengaduan
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get average rating
     */
    public function getAverageRatingAttribute(): float
    {
        return round(($this->rating_respon + $this->rating_kualitas + $this->rating_pelayanan) / 3, 1);
    }

    /**
     * Get stars display
     */
    public function getStarsAttribute(): string
    {
        $avg = $this->average_rating;
        $fullStars = floor($avg);
        $hasHalf = ($avg - $fullStars) >= 0.5;
        
        $stars = str_repeat('★', $fullStars);
        if ($hasHalf) {
            $stars .= '½';
        }
        $stars .= str_repeat('☆', 5 - $fullStars - ($hasHalf ? 1 : 0));
        
        return $stars;
    }

    /**
     * Get satisfaction display
     */
    public function getSatisfactionDisplayAttribute(): string
    {
        return $this->is_satisfied ? '😊 Puas' : '😞 Belum Puas';
    }
}
