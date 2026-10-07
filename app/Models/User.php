<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nip',
        'nis',
        'kelas',
        'no_hp',
        'avatar',
        'is_active',
        'force_password_change',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'force_password_change' => 'boolean',
        ];
    }

    /**
     * Check whether user must change password before using the system.
     */
    public function mustChangePassword(): bool
    {
        return $this->force_password_change === true;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user is admin (IT Admin — full system access)
     */
    public function isAdmin(): bool
    {
        // 'admin' is the new IT Admin role (was 'superadmin')
        // Keep 'superadmin' as backward-compat alias
        return in_array($this->role, ['admin', 'superadmin']);
    }

    /**
     * Check if user is super admin (backward compat alias for admin)
     */
    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }

    /**
     * Check if user is sarpras_atas (handles facility complaints + returnable assets)
     * Merged the old teknisi role into this role.
     */
    public function isSarprasAtas(): bool
    {
        // Also keep 'teknisi' and 'admin' (old admin) as backward-compat aliases
        return in_array($this->role, ['sarpras_atas', 'teknisi']);
    }

    /**
     * Check if user is sarpras_bawah (handles consumable ATK/supplies only)
     */
    public function isSarprasBawah(): bool
    {
        return $this->role === 'sarpras_bawah';
    }

    /**
     * Check if user is any sarpras role (atas or bawah)
     */
    public function isSarpras(): bool
    {
        return in_array($this->role, ['sarpras_atas', 'sarpras_bawah', 'teknisi']);
    }

    /**
     * Check if user is teknisi (backward-compat alias → sarpras_atas)
     */
    public function isTeknisi(): bool
    {
        return in_array($this->role, ['sarpras_atas', 'teknisi']);
    }

    /**
     * Check if user is kepala sekolah
     */
    public function isKepsek(): bool
    {
        return $this->role === 'kepsek';
    }

    /**
     * Check if user is siswa
     */
    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    /**
     * Check whether a login identifier should be treated as siswa identifier.
     */
    public static function isSiswaLoginIdentifier(string $identifier): bool
    {
        $identifier = trim($identifier);

        return $identifier !== '' && filter_var($identifier, FILTER_VALIDATE_EMAIL) === false;
    }

    /**
     * Check if user is guru
     */
    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=2563EB&color=fff';
    }

    /**
     * Get role badge color
     */
    public function getRoleBadgeColorAttribute(): string
    {
        return match($this->role) {
            'admin'         => 'danger',
            'superadmin'    => 'danger',   // backward compat
            'sarpras_atas'  => 'primary',
            'sarpras_bawah' => 'info',
            'kepsek'        => 'warning',
            'teknisi'       => 'primary',  // backward compat
            'guru'          => 'success',
            'siswa'         => 'secondary',
            default         => 'secondary',
        };
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'admin'         => 'Admin IT',
            'superadmin'    => 'Admin IT',           // backward compat
            'sarpras_atas'  => 'Sarpras Atas',
            'sarpras_bawah' => 'Sarpras Bawah',
            'kepsek'        => 'Kepala Sekolah',
            'teknisi'       => 'Sarpras Atas',       // backward compat
            'guru'          => 'Guru',
            'siswa'         => 'Siswa',
            default         => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get all pengaduan created by user
     */
    public function pengaduans(): HasMany
    {
        return $this->hasMany(Pengaduan::class, 'user_id');
    }

    /**
     * Get all pengaduan assigned to teknisi
     */
    public function assignedPengaduans(): HasMany
    {
        return $this->hasMany(Pengaduan::class, 'teknisi_id');
    }

    /**
     * Get all feedbacks given by user
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get all activity logs by user
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    /**
     * Get all notifications for user
     */
    public function userNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get all notifications (alias for userNotifications)
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function pinjamans(): HasMany
    {
        return $this->hasMany(Pinjaman::class);
    }

    public function pinjamanFeedbacks(): HasMany
    {
        return $this->hasMany(PinjamanFeedback::class);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }
}
