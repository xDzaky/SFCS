<?php

namespace App\Services;

use App\Models\Pengaduan;
use App\Models\Setting;
use Carbon\CarbonInterface;

class SlaService
{
    public function calculateDueAt(string $prioritas, ?CarbonInterface $baseTime = null): CarbonInterface
    {
        $base = ($baseTime ?? now())->copy();

        return match ($prioritas) {
            'urgent' => $base->addMinutes((int) Setting::getValue('sla_darurat_minutes', 240)),
            'tinggi' => $base->addHours((int) Setting::getValue('sla_tinggi_hours', 24)),
            'sedang' => $base->addHours((int) Setting::getValue('sla_sedang_hours', 72)),
            default => $base->addHours((int) Setting::getValue('sla_rendah_hours', 168)),
        };
    }

    public function warningWindowMinutes(): int
    {
        return (int) Setting::getValue('escalation_warning_minutes_before_due', 60);
    }

    public function shouldTrack(Pengaduan $pengaduan): bool
    {
        return in_array($pengaduan->status, [
            Pengaduan::STATUS_PENDING,
            Pengaduan::STATUS_DIVERIFIKASI,
            Pengaduan::STATUS_DIPROSES,
        ], true);
    }
}
