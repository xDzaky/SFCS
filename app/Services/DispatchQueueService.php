<?php

namespace App\Services;

use App\Models\Pengaduan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DispatchQueueService
{
    private ?bool $queueSchemaReady = null;

    public function __construct(private readonly TriageScoringService $triageScoringService)
    {
    }

    public function recomputeAll(): void
    {
        if (!$this->isQueueSchemaReady()) {
            return;
        }

        $teknisiIds = User::query()
            ->where('role', 'teknisi')
            ->where('is_active', true)
            ->pluck('id');

        foreach ($teknisiIds as $teknisiId) {
            $this->recomputeForTeknisi((int) $teknisiId);
        }
    }

    public function recomputeForTeknisi(int $teknisiId): void
    {
        if (!$this->isQueueSchemaReady()) {
            return;
        }

        $tickets = Pengaduan::query()
            ->where('teknisi_id', $teknisiId)
            ->whereIn('status', [Pengaduan::STATUS_DIVERIFIKASI, Pengaduan::STATUS_DIPROSES])
            ->get();

        $sorted = $this->sortTickets($tickets);
        $capacityPerHour = max(1, (int) Setting::getValue('teknisi_capacity_per_hour', 2));
        $delayThresholdMinutes = max(1, (int) Setting::getValue('overload_delay_threshold_minutes', 60));

        foreach ($sorted as $index => $ticket) {
            $rank = $index + 1;
            $score = $this->triageScoringService->score($ticket);
            $bucket = $this->triageScoringService->bucket($score);

            $delayMinutes = (int) floor((max(0, $rank - 1) * 60) / $capacityPerHour);
            $plannedStart = now()->copy()->addMinutes($delayMinutes);
            $plannedEnd = $plannedStart->copy()->addMinutes(60);

            $ticket->update([
                'triage_score' => $score,
                'triage_bucket' => $bucket,
                'queue_rank' => $rank,
                'planned_start_at' => $ticket->planned_start_at ?? $plannedStart,
                'planned_end_at' => $ticket->planned_end_at ?? $plannedEnd,
                'delay_minutes' => $delayMinutes,
                'is_overload_delayed' => $delayMinutes > $delayThresholdMinutes,
                'load_snapshot' => [
                    'capacity_per_hour' => $capacityPerHour,
                    'computed_at' => now()->toIso8601String(),
                    'active_tickets' => $tickets->count(),
                ],
            ]);
        }
    }

    public function overloadSummary(): array
    {
        $teknisiCount = max(1, User::query()->where('role', 'teknisi')->where('is_active', true)->count());
        $capacityPerHour = max(1, (int) Setting::getValue('teknisi_capacity_per_hour', 2));
        $urgentHighCount = Pengaduan::query()
            ->whereIn('status', [Pengaduan::STATUS_DIVERIFIKASI, Pengaduan::STATUS_DIPROSES])
            ->whereIn('prioritas', [Pengaduan::PRIORITAS_URGENT, Pengaduan::PRIORITAS_TINGGI])
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $capacityPerWindow = $teknisiCount * $capacityPerHour;
        $isOverload = $urgentHighCount > $capacityPerWindow;

        return [
            'is_overload' => $isOverload,
            'urgent_high_last_hour' => $urgentHighCount,
            'capacity_last_hour' => $capacityPerWindow,
            'predicted_delay_minutes' => $isOverload
                ? (int) ceil((($urgentHighCount - $capacityPerWindow) / $capacityPerHour) * 60)
                : 0,
        ];
    }

    /**
     * @param Collection<int, Pengaduan> $tickets
     * @return Collection<int, Pengaduan>
     */
    private function sortTickets(Collection $tickets): Collection
    {
        return $tickets
            ->sortByDesc(function (Pengaduan $ticket) {
                return $this->triageScoringService->score($ticket);
            })
            ->values();
    }

    private function isQueueSchemaReady(): bool
    {
        if ($this->queueSchemaReady !== null) {
            return $this->queueSchemaReady;
        }

        if (!Schema::hasTable('pengaduans')) {
            $this->queueSchemaReady = false;
            return false;
        }

        foreach ([
            'triage_score',
            'triage_bucket',
            'queue_rank',
            'planned_start_at',
            'planned_end_at',
            'delay_minutes',
            'is_overload_delayed',
            'load_snapshot',
        ] as $column) {
            if (!Schema::hasColumn('pengaduans', $column)) {
                $this->queueSchemaReady = false;
                return false;
            }
        }

        $this->queueSchemaReady = true;
        return true;
    }
}
