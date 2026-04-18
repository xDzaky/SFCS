<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';
    
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    public $timestamps = true;

    /**
     * Get setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value, ?string $description = null): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );

        Cache::forget("setting.{$key}");

        return $setting;
    }

    /**
     * Get app name
     */
    public static function getAppName(): string
    {
        return self::getValue('app_name', 'SFCS - School Facility Complaint System');
    }

    /**
     * Get school name
     */
    public static function getSchoolName(): string
    {
        return self::getValue('school_name', 'SMA Negeri 1');
    }

    /**
     * Get max photos per pengaduan
     */
    public static function getMaxPhotos(): int
    {
        return (int) self::getValue('max_photos', 5);
    }

    /**
     * Get SLA days based on urgensi
     */
    public static function getSLADays(string $urgensi): int
    {
        $sla = [
            'rendah' => (int) self::getValue('sla_rendah', 7),
            'sedang' => (int) self::getValue('sla_sedang', 3),
            'tinggi' => (int) self::getValue('sla_tinggi', 1),
        ];

        return $sla[$urgensi] ?? 3;
    }

    /**
     * Get all settings as array
     */
    public static function getAllSettings(): array
    {
        return self::pluck('value', 'key')->toArray();
    }
}
