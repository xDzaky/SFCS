<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StoreNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $userId,
        public string $jenis,
        public string $judul,
        public string $pesan,
        public ?string $link = null,
    ) {
    }

    public function handle(): void
    {
        Notification::create([
            'user_id' => $this->userId,
            'jenis' => $this->jenis,
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'link' => $this->link,
            'is_read' => false,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('StoreNotificationJob failed', [
            'user_id' => $this->userId,
            'jenis' => $this->jenis,
            'judul' => $this->judul,
            'error' => $exception->getMessage(),
        ]);
    }
}
