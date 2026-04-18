<?php

namespace App\Services;

use App\Models\Pengaduan;
use Illuminate\Validation\ValidationException;

class TicketStatusTransitionService
{
    /**
     * Allowed transitions by current status.
     *
     * @var array<string, array<int, string>>
     */
    private const DEFAULT_TRANSITIONS = [
        Pengaduan::STATUS_PENDING => [
            Pengaduan::STATUS_DIVERIFIKASI,
            Pengaduan::STATUS_DITOLAK,
        ],
        Pengaduan::STATUS_DIVERIFIKASI => [
            Pengaduan::STATUS_DIPROSES,
            Pengaduan::STATUS_DITOLAK,
            Pengaduan::STATUS_PENDING,
        ],
        Pengaduan::STATUS_DIPROSES => [
            Pengaduan::STATUS_SELESAI,
            Pengaduan::STATUS_DITOLAK,
            Pengaduan::STATUS_DIVERIFIKASI,
        ],
        Pengaduan::STATUS_SELESAI => [],
        Pengaduan::STATUS_DITOLAK => [],
    ];

    public function assertCanTransition(Pengaduan $pengaduan, string $toStatus, string $context = 'default'): void
    {
        $fromStatus = $pengaduan->status;

        if ($fromStatus === $toStatus) {
            return;
        }

        if ($context === 'reopen_approved' && $fromStatus === Pengaduan::STATUS_SELESAI && $toStatus === Pengaduan::STATUS_DIVERIFIKASI) {
            return;
        }

        $allowed = self::DEFAULT_TRANSITIONS[$fromStatus] ?? [];
        if (!in_array($toStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Transisi status tidak valid: {$fromStatus} -> {$toStatus}.",
            ]);
        }
    }

    /**
     * Apply transition and lifecycle timestamps.
     *
     * @return array<string, mixed>
     */
    public function buildUpdatePayload(Pengaduan $pengaduan, string $toStatus, array $extra = []): array
    {
        $this->assertCanTransition($pengaduan, $toStatus, (string) ($extra['transition_context'] ?? 'default'));

        $payload = ['status' => $toStatus];

        if ($pengaduan->first_response_at === null && $toStatus !== Pengaduan::STATUS_PENDING) {
            $payload['first_response_at'] = now();
        }

        if ($toStatus === Pengaduan::STATUS_DIVERIFIKASI && $pengaduan->verified_at === null) {
            $payload['verified_at'] = now();
        }

        if ($toStatus === Pengaduan::STATUS_DIPROSES && $pengaduan->started_at === null) {
            $payload['started_at'] = now();
        }

        if ($toStatus === Pengaduan::STATUS_SELESAI) {
            $payload['completed_at'] = now();
        } elseif ($pengaduan->status === Pengaduan::STATUS_SELESAI && $toStatus !== Pengaduan::STATUS_SELESAI) {
            // Reopen path.
            $payload['completed_at'] = null;
        }

        return array_merge($payload, $extra);
    }
}
