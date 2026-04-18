<?php

namespace App\Console\Commands;

use App\Services\DispatchQueueService;
use Illuminate\Console\Command;

class RecomputeTicketQueueCommand extends Command
{
    protected $signature = 'tickets:recompute-queue';
    protected $description = 'Hitung ulang triage score dan urutan antrean tiket aktif per teknisi.';

    public function __construct(private readonly DispatchQueueService $dispatchQueueService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->dispatchQueueService->recomputeAll();
        $this->info('Queue ticket berhasil dihitung ulang.');

        return self::SUCCESS;
    }
}

