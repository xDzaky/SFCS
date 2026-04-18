<?php

namespace App\Console\Commands;

use App\Services\StudentClassPromotionService;
use Illuminate\Console\Command;
use RuntimeException;

class PromoteStudentsCommand extends Command
{
    protected $signature = 'students:promote
                            {--preview : Tampilkan preview tanpa apply}
                            {--apply : Terapkan hasil preview}
                            {--token= : Preview token untuk apply}
                            {--target-grade=all : all|x|xi|xii}
                            {--actor= : User ID superadmin untuk metadata}';

    protected $description = 'Preview/apply promote kelas siswa aktif (X->XI, XI->XII, XII nonaktif)';

    public function __construct(private readonly StudentClassPromotionService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $isPreview = (bool) $this->option('preview');
        $isApply = (bool) $this->option('apply');

        if ($isPreview === $isApply) {
            $this->error('Gunakan salah satu: --preview atau --apply.');
            return self::FAILURE;
        }

        if ($isPreview) {
            $actorId = (int) ($this->option('actor') ?: 0);
            $result = $this->service->preview([
                'target_grade' => (string) $this->option('target-grade'),
                'actor_id' => $actorId,
            ]);

            $summary = $result['summary'] ?? [];
            $this->info('Preview promote berhasil.');
            $this->line('Token: '.($result['preview_token'] ?? '-'));
            $this->line('TTL: '.($result['ttl_minutes'] ?? 15).' menit');
            $this->table(['Metric', 'Value'], [
                ['Total aktif', (string) ($summary['total_active_students'] ?? 0)],
                ['Targeted', (string) ($summary['targeted_students'] ?? 0)],
                ['X -> XI', (string) ($summary['x_to_xi'] ?? 0)],
                ['XI -> XII', (string) ($summary['xi_to_xii'] ?? 0)],
                ['XII -> Nonaktif', (string) ($summary['xii_to_nonactive'] ?? 0)],
                ['Skipped', (string) ($summary['skipped'] ?? 0)],
            ]);

            return self::SUCCESS;
        }

        $token = (string) $this->option('token');
        if ($token === '') {
            $this->error('Untuk --apply wajib isi --token=');
            return self::FAILURE;
        }

        $actorId = (int) ($this->option('actor') ?: 0);
        try {
            $result = $this->service->apply($token, $actorId);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $applied = $result['applied'] ?? [];
        $this->info('Promote berhasil diterapkan.');
        $this->table(['Metric', 'Value'], [
            ['Applied total', (string) ($applied['applied_total'] ?? 0)],
            ['Updated kelas', (string) ($applied['updated_class'] ?? 0)],
            ['Nonaktifkan XII', (string) ($applied['deactivated'] ?? 0)],
            ['Skipped', (string) ($applied['skipped'] ?? 0)],
        ]);

        return self::SUCCESS;
    }
}

