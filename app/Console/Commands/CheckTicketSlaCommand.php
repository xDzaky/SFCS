<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Pengaduan;
use App\Models\User;
use App\Services\SlaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckTicketSlaCommand extends Command
{
    protected $signature = 'tickets:check-sla';
    protected $description = 'Check active tickets SLA and notify admins for warning/overdue tickets.';

    public function __construct(private readonly SlaService $slaService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = now();
        $warningUntil = $now->copy()->addMinutes($this->slaService->warningWindowMinutes());

        $candidates = Pengaduan::query()
            ->whereIn('status', [
                Pengaduan::STATUS_PENDING,
                Pengaduan::STATUS_DIVERIFIKASI,
                Pengaduan::STATUS_DIPROSES,
            ])
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<=', $warningUntil)
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('No SLA warning/overdue tickets.');
            return self::SUCCESS;
        }

        $admins = User::query()->whereIn('role', ['admin', 'superadmin'])->pluck('id');
        $warned = 0;
        $overdue = 0;

        foreach ($candidates as $ticket) {
            $isOverdue = $ticket->sla_due_at->isPast();
            $cacheKey = 'sla_notif:'.$ticket->id.':'.($isOverdue ? 'overdue' : 'warning');
            if (!Cache::add($cacheKey, true, now()->addHours(6))) {
                continue;
            }

            foreach ($admins as $adminId) {
                Notification::send(
                    $adminId,
                    Notification::JENIS_OVERDUE,
                    $isOverdue ? 'SLA Terlewat' : 'SLA Hampir Jatuh Tempo',
                    $isOverdue
                        ? "Tiket #{$ticket->kode_pengaduan} melewati SLA. Segera tindak lanjuti."
                        : "Tiket #{$ticket->kode_pengaduan} mendekati batas SLA.",
                    route('admin.pengaduan.show', $ticket->kode_pengaduan)
                );
            }

            $isOverdue ? $overdue++ : $warned++;
        }

        $this->info("SLA check complete. warning={$warned}, overdue={$overdue}");

        return self::SUCCESS;
    }
}
