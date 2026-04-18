<?php

namespace App\Services;

use App\Models\Pengaduan;

class TriageScoringService
{
    public function score(Pengaduan $pengaduan): int
    {
        $score = match ($pengaduan->prioritas) {
            Pengaduan::PRIORITAS_URGENT => 100,
            Pengaduan::PRIORITAS_TINGGI => 70,
            Pengaduan::PRIORITAS_SEDANG => 35,
            default => 10,
        };

        if ($pengaduan->impact_safety_risk) {
            $score += 30;
        }
        if ($pengaduan->impact_exam_related) {
            $score += 15;
        }
        if ($pengaduan->impact_learning_blocked) {
            $score += 10;
        }

        $ageHours = max(1, $pengaduan->created_at?->diffInHours(now()) ?? 1);
        $score += min(24, (int) floor($ageHours / 2));

        if (in_array($pengaduan->status, [Pengaduan::STATUS_DIVERIFIKASI, Pengaduan::STATUS_DIPROSES], true)) {
            $score += 10;
        }

        return $score;
    }

    public function bucket(int $score): string
    {
        return match (true) {
            $score >= 100 => 'critical',
            $score >= 60 => 'high',
            default => 'normal',
        };
    }
}

