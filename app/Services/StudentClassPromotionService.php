<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class StudentClassPromotionService
{
    private const PREVIEW_TTL_MINUTES = 15;
    private const CACHE_KEY_PREFIX = 'students:promotion:preview:';
    private const ALLOWED_JURUSAN = ['RPL', 'MP', 'AK', 'BD', 'LP'];

    /**
     * @param array{target_grade?:string,actor_id?:int} $options
     * @return array<string, mixed>
     */
    public function preview(array $options): array
    {
        $targetGrade = $this->normalizeTargetGrade((string) ($options['target_grade'] ?? 'all'));
        $actorId = (int) ($options['actor_id'] ?? 0);

        $users = User::query()
            ->where('role', 'siswa')
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'nis', 'kelas', 'is_active']);

        $summary = [
            'total_active_students' => $users->count(),
            'targeted_students' => 0,
            'x_to_xi' => 0,
            'xi_to_xii' => 0,
            'xii_to_nonactive' => 0,
            'skipped' => 0,
        ];

        $candidates = [];
        $skipped = [];

        foreach ($users as $user) {
            $kelas = trim((string) ($user->kelas ?? ''));
            $parsed = $this->parseClass($kelas);

            if ($parsed === null) {
                $skipped[] = $this->buildSkippedItem($user, $kelas, 'Format kelas tidak sesuai pola SMK.');
                continue;
            }

            if ($targetGrade !== 'all' && $parsed['grade'] !== $targetGrade) {
                continue;
            }

            $normalizedClass = $this->buildClass($parsed['grade'], $parsed['jurusan'], $parsed['rombel']);
            $candidate = [
                'user_id' => $user->id,
                'name' => $user->name,
                'nis' => $user->nis,
                'from_class' => $normalizedClass,
                'action' => null,
                'to_class' => null,
            ];

            if ($parsed['grade'] === 'X') {
                $candidate['action'] = 'promote_x_to_xi';
                $candidate['to_class'] = $this->buildClass('XI', $parsed['jurusan'], $parsed['rombel']);
                $summary['x_to_xi']++;
            } elseif ($parsed['grade'] === 'XI') {
                $candidate['action'] = 'promote_xi_to_xii';
                $candidate['to_class'] = $this->buildClass('XII', $parsed['jurusan'], $parsed['rombel']);
                $summary['xi_to_xii']++;
            } else {
                $candidate['action'] = 'deactivate_xii';
                $candidate['to_class'] = $normalizedClass;
                $summary['xii_to_nonactive']++;
            }

            $summary['targeted_students']++;
            $candidates[] = $candidate;
        }

        $summary['skipped'] = count($skipped);
        $token = Str::random(48);
        $cacheKey = $this->cacheKey($token);
        $generatedAt = now()->toIso8601String();

        $payload = [
            'token' => $token,
            'actor_id' => $actorId,
            'options' => ['target_grade' => $targetGrade],
            'summary' => $summary,
            'candidates' => $candidates,
            'skipped' => $skipped,
            'generated_at' => $generatedAt,
        ];

        Cache::put($cacheKey, $payload, now()->addMinutes(self::PREVIEW_TTL_MINUTES));

        return [
            'preview_token' => $token,
            'ttl_minutes' => self::PREVIEW_TTL_MINUTES,
            'generated_at' => $generatedAt,
            'options' => $payload['options'],
            'summary' => $summary,
            'skipped_sample' => array_slice($skipped, 0, 50),
            'skipped_remaining' => max(0, count($skipped) - 50),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function apply(string $previewToken, int $actorId): array
    {
        $cacheKey = $this->cacheKey($previewToken);
        $payload = Cache::get($cacheKey);

        if (!is_array($payload)) {
            throw new RuntimeException('Preview token tidak valid atau sudah kadaluarsa. Silakan preview ulang.');
        }

        if ((int) ($payload['actor_id'] ?? 0) !== $actorId) {
            throw new RuntimeException('Token preview tidak cocok dengan pengguna yang login.');
        }

        /** @var array<int, array<string, mixed>> $candidates */
        $candidates = $payload['candidates'] ?? [];

        $result = [
            'applied_total' => 0,
            'updated_class' => 0,
            'deactivated' => 0,
            'skipped' => 0,
            'skip_reasons' => [],
        ];

        DB::transaction(function () use ($candidates, &$result): void {
            foreach ($candidates as $candidate) {
                $user = User::query()
                    ->where('id', (int) ($candidate['user_id'] ?? 0))
                    ->where('role', 'siswa')
                    ->first();

                if (!$user || !$user->is_active) {
                    $this->appendSkip($result, 'Siswa tidak ditemukan atau sudah nonaktif.');
                    continue;
                }

                $currentParsed = $this->parseClass((string) ($user->kelas ?? ''));
                if ($currentParsed === null) {
                    $this->appendSkip($result, 'Format kelas siswa berubah/tidak valid saat apply.');
                    continue;
                }

                $currentClass = $this->buildClass($currentParsed['grade'], $currentParsed['jurusan'], $currentParsed['rombel']);
                if ($currentClass !== (string) ($candidate['from_class'] ?? '')) {
                    $this->appendSkip($result, 'Kelas siswa berubah setelah preview.');
                    continue;
                }

                $action = (string) ($candidate['action'] ?? '');
                if (in_array($action, ['promote_x_to_xi', 'promote_xi_to_xii'], true)) {
                    $user->kelas = (string) ($candidate['to_class'] ?? $user->kelas);
                    $user->save();
                    $result['updated_class']++;
                    $result['applied_total']++;
                    continue;
                }

                if ($action === 'deactivate_xii') {
                    $user->is_active = false;
                    $user->save();
                    $result['deactivated']++;
                    $result['applied_total']++;
                    continue;
                }

                $this->appendSkip($result, 'Aksi promote tidak dikenali.');
            }
        });

        Cache::forget($cacheKey);

        return [
            'preview_token' => $previewToken,
            'summary_before' => $payload['summary'] ?? [],
            'options' => $payload['options'] ?? [],
            'applied' => $result,
            'applied_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array{grade:string,jurusan:string,rombel:int}|null
     */
    public function parseClass(string $kelas): ?array
    {
        $normalized = preg_replace('/\s+/', ' ', strtoupper(trim($kelas)));
        if (!$normalized) {
            return null;
        }

        if (!preg_match('/^(X|XI|XII)\s+([A-Z]+)\s+([1-9][0-9]*)$/', $normalized, $matches)) {
            return null;
        }

        $jurusan = $matches[2];
        if (!in_array($jurusan, self::ALLOWED_JURUSAN, true)) {
            return null;
        }

        return [
            'grade' => $matches[1],
            'jurusan' => $jurusan,
            'rombel' => (int) $matches[3],
        ];
    }

    private function normalizeTargetGrade(string $grade): string
    {
        $normalized = strtoupper(trim($grade));
        return match ($normalized) {
            'X', 'XI', 'XII' => $normalized,
            default => 'all',
        };
    }

    private function cacheKey(string $token): string
    {
        return self::CACHE_KEY_PREFIX.$token;
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function buildSkippedItem(User $user, string $kelas, string $reason): array
    {
        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'nis' => $user->nis,
            'kelas' => $kelas,
            'reason' => $reason,
        ];
    }

    private function buildClass(string $grade, string $jurusan, int $rombel): string
    {
        return "{$grade} {$jurusan} {$rombel}";
    }

    /**
     * @param array<string, mixed> $result
     */
    private function appendSkip(array &$result, string $reason): void
    {
        $result['skipped']++;
        if (count($result['skip_reasons']) < 50) {
            $result['skip_reasons'][] = $reason;
        }
    }
}

