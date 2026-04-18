<?php

namespace App\Services;

use App\Models\Pengaduan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PengaduanDuplicateDetector
{
    private const ACTIVE_DUPLICATE_STATUSES = [
        Pengaduan::STATUS_PENDING,
        Pengaduan::STATUS_DIVERIFIKASI,
        Pengaduan::STATUS_DIPROSES,
    ];

    /**
     * Per-request memoization to avoid repeated duplicate checks for the same signature.
     *
     * @var array<string, \Illuminate\Support\Collection<int, Pengaduan>>
     */
    private array $candidatesCache = [];

    public function findActiveDuplicateForPayload(array $payload): ?Pengaduan
    {
        $candidates = $this->findActiveCandidatesForPayload($payload, 10);

        if ($candidates->isEmpty()) {
            return null;
        }

        $normalizedInputDetail = $this->normalizeLocationDetail($payload['lokasi_detail'] ?? null);
        $ruanganId = $payload['ruangan_id'] ?? null;

        // Prevent false positives if location is still too generic.
        if (empty($ruanganId) && $normalizedInputDetail === '') {
            return null;
        }

        if ($normalizedInputDetail === '') {
            return $candidates->first();
        }

        foreach ($candidates as $candidate) {
            $normalizedCandidateDetail = $this->normalizeLocationDetail($candidate->lokasi_detail);
            if ($normalizedCandidateDetail === '' || $normalizedCandidateDetail === $normalizedInputDetail) {
                return $candidate;
            }
        }

        return null;
    }

    public function findActiveCandidatesForPengaduan(Pengaduan $pengaduan, int $limit = 5): Collection
    {
        $payload = [
            'kategori_id' => $pengaduan->kategori_id,
            'sub_kategori_id' => $pengaduan->sub_kategori_id,
            'gedung_id' => $pengaduan->gedung_id,
            'lantai' => $pengaduan->lantai,
            'ruangan_id' => $pengaduan->ruangan_id,
            'lokasi_detail' => $pengaduan->lokasi_detail,
        ];

        return $this->findActiveCandidatesForPayload($payload, $limit, $pengaduan->id);
    }

    public function hasPotentialDuplicate(Pengaduan $pengaduan): bool
    {
        if (!$pengaduan->is_active_ticket) {
            return false;
        }

        return $this->findActiveCandidatesForPengaduan($pengaduan, 1)->isNotEmpty();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Pengaduan>
     */
    private function findActiveCandidatesForPayload(array $payload, int $limit = 10, ?int $excludePengaduanId = null): Collection
    {
        $signature = $this->buildSignature($payload, $excludePengaduanId, $limit);

        if (isset($this->candidatesCache[$signature])) {
            return $this->candidatesCache[$signature];
        }

        $query = $this->buildBaseActiveQuery($payload);

        if ($excludePengaduanId !== null) {
            $query->where('id', '!=', $excludePengaduanId);
        }

        $candidates = $query->latest()->limit($limit)->get();
        $normalizedInputDetail = $this->normalizeLocationDetail($payload['lokasi_detail'] ?? null);

        if ($normalizedInputDetail !== '') {
            $candidates = $candidates->filter(function (Pengaduan $candidate) use ($normalizedInputDetail) {
                $normalizedCandidateDetail = $this->normalizeLocationDetail($candidate->lokasi_detail);
                return $normalizedCandidateDetail === '' || $normalizedCandidateDetail === $normalizedInputDetail;
            })->values();
        }

        $this->candidatesCache[$signature] = $candidates;

        return $candidates;
    }

    private function buildBaseActiveQuery(array $payload): Builder
    {
        $query = Pengaduan::query()
            ->whereIn('status', self::ACTIVE_DUPLICATE_STATUSES)
            ->where('kategori_id', $payload['kategori_id'])
            ->where('gedung_id', $payload['gedung_id'])
            ->where('lantai', $payload['lantai']);

        if (array_key_exists('sub_kategori_id', $payload) && !empty($payload['sub_kategori_id'])) {
            $query->where(function (Builder $q) use ($payload) {
                $q->where('sub_kategori_id', $payload['sub_kategori_id'])
                    ->orWhereNull('sub_kategori_id');
            });
        }

        // If ruangan_id is not provided by the form, do not constrain by ruangan.
        if (array_key_exists('ruangan_id', $payload)) {
            $ruanganId = $payload['ruangan_id'] ?? null;
            if (!empty($ruanganId)) {
                $query->where('ruangan_id', $ruanganId);
            } else {
                $query->whereNull('ruangan_id');
            }
        }

        return $query;
    }

    private function normalizeLocationDetail(?string $locationDetail): string
    {
        return Str::of((string) $locationDetail)
            ->lower()
            ->squish()
            ->toString();
    }

    private function buildSignature(array $payload, ?int $excludeId, int $limit): string
    {
        return implode('|', [
            $payload['kategori_id'] ?? '',
            $payload['sub_kategori_id'] ?? '',
            $payload['gedung_id'] ?? '',
            $payload['lantai'] ?? '',
            $payload['ruangan_id'] ?? '',
            $this->normalizeLocationDetail($payload['lokasi_detail'] ?? null),
            $excludeId ?? '',
            $limit,
        ]);
    }
}
