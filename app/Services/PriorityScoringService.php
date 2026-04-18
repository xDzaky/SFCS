<?php

namespace App\Services;

use App\Models\Pengaduan;
use App\Models\Setting;

class PriorityScoringService
{
    public function score(array $payload): int
    {
        $score = 0;

        if (!empty($payload['impact_safety_risk'])) {
            $score += (int) Setting::getValue('priority_score_safety', 50);
        }

        if (!empty($payload['impact_learning_blocked'])) {
            $score += (int) Setting::getValue('priority_score_learning_blocked', 25);
        }

        if (!empty($payload['impact_exam_related'])) {
            $score += (int) Setting::getValue('priority_score_exam_related', 30);
        }

        $score += match ($payload['impact_area_scope'] ?? '1_kelas') {
            '1_lantai' => (int) Setting::getValue('priority_score_scope_lantai', 15),
            '1_gedung' => (int) Setting::getValue('priority_score_scope_gedung', 30),
            default => 0,
        };

        $score += match ($payload['impact_utilities'] ?? 'none') {
            'listrik', 'air' => (int) Setting::getValue('priority_score_utilities_critical', 20),
            default => 0,
        };

        return $score;
    }

    public function mapScoreToPriority(int $score): string
    {
        $urgentMin = (int) Setting::getValue('priority_threshold_urgent', 80);
        $tinggiMin = (int) Setting::getValue('priority_threshold_tinggi', 50);
        $sedangMin = (int) Setting::getValue('priority_threshold_sedang', 25);

        if ($score >= $urgentMin) {
            return Pengaduan::PRIORITAS_URGENT;
        }

        if ($score >= $tinggiMin) {
            return Pengaduan::PRIORITAS_TINGGI;
        }

        if ($score >= $sedangMin) {
            return Pengaduan::PRIORITAS_SEDANG;
        }

        return Pengaduan::PRIORITAS_RENDAH;
    }

    public function shouldFlagForReview(string $requestedPriority, string $finalPriority): bool
    {
        return $this->priorityRank($requestedPriority) >= $this->priorityRank(Pengaduan::PRIORITAS_TINGGI)
            && $this->priorityRank($requestedPriority) > $this->priorityRank($finalPriority);
    }

    public function resolvePriority(string $requestedPriority, int $score): array
    {
        $computed = $this->mapScoreToPriority($score);
        $final = $this->priorityRank($requestedPriority) > $this->priorityRank($computed) ? $computed : $requestedPriority;

        return [
            'requested' => $requestedPriority,
            'computed' => $computed,
            'final' => $final,
            'needs_review' => $this->shouldFlagForReview($requestedPriority, $final),
        ];
    }

    private function priorityRank(string $priority): int
    {
        return match ($priority) {
            Pengaduan::PRIORITAS_URGENT => 4,
            Pengaduan::PRIORITAS_TINGGI => 3,
            Pengaduan::PRIORITAS_SEDANG => 2,
            default => 1,
        };
    }
}
