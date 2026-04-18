<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use App\Services\DispatchQueueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class DetectTicketOverloadCommand extends Command
{
    protected $signature = 'tickets:detect-overload';
    protected $description = 'Deteksi lonjakan tiket urgent/tinggi dan kirim alert overload.';

    public function __construct(private readonly DispatchQueueService $dispatchQueueService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $summary = $this->dispatchQueueService->overloadSummary();
        if (!$summary['is_overload']) {
            $this->info('Tidak ada overload.');
            return self::SUCCESS;
        }

        $delayThreshold = (int) Setting::getValue('overload_delay_threshold_minutes', 60);
        if ((int) $summary['predicted_delay_minutes'] < $delayThreshold) {
            $this->info('Prediksi delay masih di bawah threshold.');
            return self::SUCCESS;
        }

        $cacheKey = 'tickets:overload-alert:' . now()->format('YmdHi');
        if (!Cache::add($cacheKey, true, now()->addMinutes(5))) {
            $this->info('Alert sudah terkirim untuk window ini.');
            return self::SUCCESS;
        }

        $targets = User::query()
            ->whereIn('role', ['admin', 'superadmin', 'kepsek'])
            ->where('is_active', true)
            ->pluck('id');

        foreach ($targets as $userId) {
            Notification::send(
                (int) $userId,
                Notification::JENIS_OVERLOAD_ALERT,
                'Overload Tiket Urgent',
                "Urgent/Tinggi 1 jam: {$summary['urgent_high_last_hour']}, kapasitas: {$summary['capacity_last_hour']}, estimasi delay: {$summary['predicted_delay_minutes']} menit.",
                route('admin.overload-board')
            );
        }

        $this->info('Alert overload dikirim.');
        return self::SUCCESS;
    }
}

