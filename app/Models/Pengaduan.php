<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Pengaduan extends Model
{
    protected $table = 'pengaduans';
    
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'kode_pengaduan';
    }
    
    protected $fillable = [
        'kode_pengaduan',
        'user_id',
        'kategori_id',
        'sub_kategori_id',
        'gedung_id',
        'lantai',
        'ruangan_id',
        'lokasi_detail',
        'judul',
        'deskripsi',
        'tanggal_kejadian',
        'prioritas',
        'status',
        'teknisi_id',
        'catatan_admin',
        'catatan_teknisi',
        'alasan_tolak',
        'verified_at',
        'assigned_at',
        'started_at',
        'completed_at',
        'rating',
        'feedback',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'tanggal_kejadian' => 'date',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_DIVERIFIKASI = 'diverifikasi';
    const STATUS_DIPROSES = 'diproses';
    const STATUS_SELESAI = 'selesai';
    const STATUS_DITOLAK = 'ditolak';

    // Prioritas constants (same as urgensi)
    const PRIORITAS_RENDAH = 'rendah';
    const PRIORITAS_SEDANG = 'sedang';
    const PRIORITAS_TINGGI = 'tinggi';
    const PRIORITAS_URGENT = 'urgent';
    
    // Urgensi constants (aliases for prioritas)
    const URGENSI_RENDAH = 'rendah';
    const URGENSI_SEDANG = 'sedang';
    const URGENSI_TINGGI = 'tinggi';
    const URGENSI_URGENT = 'urgent';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pengaduan) {
            if (empty($pengaduan->kode_pengaduan)) {
                $pengaduan->kode_pengaduan = self::generateKode();
            }
        });
    }

    /**
     * Generate unique kode pengaduan
     */
    public static function generateKode(): string
    {
        $tanggal = now()->format('Ymd');
        $prefix = "ADU-{$tanggal}-";
        
        $lastPengaduan = self::where('kode_pengaduan', 'like', $prefix . '%')
            ->orderBy('kode_pengaduan', 'desc')
            ->first();

        if ($lastPengaduan) {
            $lastNumber = (int) substr($lastPengaduan->kode_pengaduan, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get user who created the pengaduan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get kategori
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Get sub kategori
     */
    public function subKategori(): BelongsTo
    {
        return $this->belongsTo(SubKategori::class);
    }

    /**
     * Get gedung
     */
    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class);
    }

    /**
     * Get ruangan
     */
    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    /**
     * Get assigned teknisi
     */
    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }

    /**
     * Get assigned teknisi (alias for teknisi)
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }

    /**
     * Get all photos
     */
    public function photos(): HasMany
    {
        return $this->hasMany(PengaduanPhoto::class);
    }

    /**
     * Get bukti photos
     */
    public function buktiPhotos(): HasMany
    {
        return $this->hasMany(PengaduanPhoto::class)->where('tipe', 'bukti');
    }

    /**
     * Get progress photos
     */
    public function progressPhotos(): HasMany
    {
        return $this->hasMany(PengaduanPhoto::class)->where('tipe', 'progress');
    }

    /**
     * Get hasil photos
     */
    public function hasilPhotos(): HasMany
    {
        return $this->hasMany(PengaduanPhoto::class)->where('tipe', 'hasil');
    }

    /**
     * Get feedback
     */
    /**
     * Get feedback detail
     */
    public function feedbackDetail(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    /**
     * Get activity logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get history changes
     */
    public function histories(): HasMany
    {
        return $this->hasMany(HistoryPengaduan::class)->orderBy('created_at', 'desc');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'secondary',
            self::STATUS_DIVERIFIKASI => 'info',
            self::STATUS_DIPROSES => 'warning',
            self::STATUS_SELESAI => 'success',
            self::STATUS_DITOLAK => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_DIVERIFIKASI => 'Diverifikasi',
            self::STATUS_DIPROSES => 'Sedang Diproses',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DITOLAK => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get urgensi badge color
     */
    public function getUrgensiBadgeColorAttribute(): string
    {
        return match($this->urgensi ?? $this->prioritas) {
            self::URGENSI_RENDAH => 'success',
            self::URGENSI_SEDANG => 'warning',
            self::URGENSI_TINGGI => 'orange',
            self::URGENSI_URGENT => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get urgensi display name
     */
    public function getUrgensiDisplayAttribute(): string
    {
        return match($this->urgensi ?? $this->prioritas) {
            self::URGENSI_RENDAH => '🟢 Rendah',
            self::URGENSI_SEDANG => '🟡 Sedang',
            self::URGENSI_TINGGI => '🟠 Tinggi',
            self::URGENSI_URGENT => '🔴 Urgent',
            default => ucfirst($this->urgensi ?? $this->prioritas),
        };
    }

    /**
     * Get full location
     */
    public function getFullLocationAttribute(): string
    {
        return $this->gedung->nama . ' - Lantai ' . $this->lantai . ' - ' . $this->ruangan->nama;
    }

    /**
     * Get waktu respon (time from created to first status change)
     */
    public function getWaktuResponAttribute(): ?string
    {
        $firstLog = $this->logs()->where('action', 'status_changed')->first();
        if ($firstLog) {
            return $this->created_at->diffForHumans($firstLog->created_at, true);
        }
        return null;
    }

    /**
     * Check if pengaduan can be edited by user
     */
    public function canBeEditedByUser(): bool
    {
        return $this->status === self::STATUS_BARU;
    }

    /**
     * Check if feedback can be given
     */
    public function canGiveFeedback(): bool
    {
        return $this->status === self::STATUS_SELESAI && !$this->feedback;
    }

    /**
     * Check if is overdue based on SLA
     */
    public function isOverdue(): bool
    {
        if ($this->status === self::STATUS_SELESAI || $this->status === self::STATUS_DITOLAK) {
            return false;
        }

        $sla = match($this->urgensi) {
            self::URGENSI_RENDAH => 7,
            self::URGENSI_SEDANG => 3,
            self::URGENSI_TINGGI => 1,
            self::URGENSI_DARURAT => 0.25,
            default => 7,
        };

        return $this->created_at->addDays($sla)->isPast();
    }

    // ==================== SCOPES ====================

    /**
     * Scope for user's own pengaduan
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending (not processed yet)
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for in progress (alias for scopeInProgress)
     */
    public function scopeProses($query)
    {
        return $query->where('status', self::STATUS_DIPROSES);
    }

    /**
     * Scope for in progress
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_DIPROSES);
    }

    /**
     * Scope for completed (alias for scopeCompleted)
     */
    public function scopeSelesai($query)
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    /**
     * Scope for completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    /**
     * Scope for urgent (tinggi and urgent)
     */
    public function scopeUrgent($query)
    {
        return $query->whereIn('prioritas', [self::PRIORITAS_TINGGI, self::PRIORITAS_URGENT])
                     ->orWhereIn('urgensi', [self::URGENSI_TINGGI, self::URGENSI_URGENT]);
    }

    /**
     * Scope for assigned to teknisi
     */
    public function scopeAssignedTo($query, $teknisiId)
    {
        return $query->where('teknisi_id', $teknisiId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope for this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }
}
