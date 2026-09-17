<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pinjaman extends Model
{
    protected $table = 'pinjamans';

    protected $fillable = [
        'kode_pinjaman',
        'user_id',
        'barang_id',
        'qty',
        'tipe',
        'tgl_pinjam',
        'tgl_jatuh_tempo',
        'tgl_kembali',
        'status',
        'alasan',
        'catatan_admin',
        'approved_at',
        'checked_out_at',
        'marked_late_at',
    ];

    protected $casts = [
        'qty'           => 'integer',
        'tgl_pinjam'    => 'datetime',
        'tgl_jatuh_tempo' => 'datetime',
        'tgl_kembali'   => 'datetime',
        'approved_at'   => 'datetime',
        'checked_out_at' => 'datetime',
        'marked_late_at' => 'datetime',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DIPINJAM  = 'dipinjam';
    public const STATUS_TERLAMBAT = 'terlambat';
    public const STATUS_SELESAI   = 'selesai';
    public const STATUS_DITOLAK   = 'ditolak';

    public const TIPE_PINJAM = 'pinjam';
    public const TIPE_MINTA  = 'minta';

    /** Apakah ini transaksi permintaan (tidak dikembalikan)? */
    public function isPermintaan(): bool
    {
        return $this->tipe === self::TIPE_MINTA;
    }

    /** Label tipe yang ramah tampilan */
    public function getLabelTipeAttribute(): string
    {
        return $this->tipe === self::TIPE_MINTA ? 'Permintaan Barang' : 'Peminjaman Barang';
    }

    public function getRouteKeyName(): string
    {
        return 'kode_pinjaman';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Pinjaman $pinjaman): void {
            if (empty($pinjaman->kode_pinjaman)) {
                $pinjaman->kode_pinjaman = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        $tanggal = now()->format('Ymd');
        $prefix  = "PJM-{$tanggal}-";

        $last = self::query()
            ->where('kode_pinjaman', 'like', $prefix.'%')
            ->orderByDesc('kode_pinjaman')
            ->first();

        $newNumber = $last ? ((int) substr($last->kode_pinjaman, -3) + 1) : 1;

        return $prefix.str_pad((string) $newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PinjamanLog::class)->latest('created_at');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(PinjamanFeedback::class);
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', [self::STATUS_DISETUJUI, self::STATUS_DIPINJAM, self::STATUS_TERLAMBAT]);
    }
}
