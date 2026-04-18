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
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is teknisi
     */
    public function isTeknisi(): bool
    {
        return $this->role === 'teknisi';
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
            'superadmin' => 'danger',
            'admin' => 'primary',
            'kepsek' => 'warning',
            'teknisi' => 'info',
            'guru' => 'success',
            'siswa' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'superadmin' => 'Super Admin',
            'admin' => 'Admin Sarpras',
            'kepsek' => 'Kepala Sekolah',
            'teknisi' => 'Teknisi',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            default => ucfirst($this->role),
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
