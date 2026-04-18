<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class InternalHealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $result = [
            'db' => $this->checkDb(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
            'app_version' => config('app.version', 'dev'),
            'timestamp' => now()->toIso8601String(),
        ];

        $isHealthy = $result['db']['ok'] && $result['queue']['ok'] && $result['storage']['ok'];

        return response()->json(
            array_merge(['status' => $isHealthy ? 'ok' : 'degraded'], $result),
            $isHealthy ? 200 : 503
        );
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    private function checkDb(): array
    {
        try {
            DB::select('select 1');
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * @return array{ok: bool, pending: int, failed_last_24h: int}
     */
    private function checkQueue(): array
    {
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subDay())
            ->count();

        return [
            'ok' => Queue::getDefaultDriver() !== '',
            'pending' => $pending,
            'failed_last_24h' => $failed,
        ];
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    private function checkStorage(): array
    {
        try {
            $disk = Storage::disk('public');
            $probe = 'healthcheck/probe.txt';
            $disk->put($probe, 'ok');
            $disk->delete($probe);
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
