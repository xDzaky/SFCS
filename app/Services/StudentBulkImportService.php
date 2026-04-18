<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentBulkImportService
{
    /**
     * @return array{
     *   created:int,
     *   updated:int,
     *   deactivated:int,
     *   failed:int,
     *   errors:array<int, string>,
     *   total_rows:int
     * }
     */
    public function import(string $csvPath, string $mode = 'replace_siswa'): array
    {
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return $this->emptyResult(['Gagal membaca file CSV.']);
        }

        $headerRow = fgetcsv($handle);
        if (!$headerRow || count($headerRow) === 0) {
            fclose($handle);
            return $this->emptyResult(['Header CSV kosong atau tidak valid.']);
        }

        $headerMap = $this->buildHeaderMap($headerRow);
        if (!isset($headerMap['nama']) || !isset($headerMap['nis'])) {
            fclose($handle);
            return $this->emptyResult(['Header wajib minimal: nama, nis.']);
        }

        $result = [
            'created' => 0,
            'updated' => 0,
            'deactivated' => 0,
            'failed' => 0,
            'errors' => [],
            'total_rows' => 0,
        ];

        $seenNis = [];
        $importedNis = [];
        $rowNumber = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $result['total_rows']++;
                if ($result['total_rows'] > 5000) {
                    $result['failed']++;
                    $result['errors'][] = 'Maksimal 5000 baris per upload.';
                    break;
                }
                $parsed = $this->parseRow($row, $headerMap);

                $validationError = $this->validateRow($parsed, $seenNis);
                if ($validationError !== null) {
                    $result['failed']++;
                    $result['errors'][] = "Baris {$rowNumber}: {$validationError}";
                    continue;
                }

                $seenNis[$parsed['nis']] = true;
                $importedNis[] = $parsed['nis'];

                $existing = User::query()->where('nis', $parsed['nis'])->first();
                if ($existing && $existing->role !== 'siswa') {
                    $result['failed']++;
                    $result['errors'][] = "Baris {$rowNumber}: NIS {$parsed['nis']} sudah dipakai role {$existing->role}.";
                    continue;
                }

                $email = $this->resolveEmail($parsed['email'], $parsed['nis'], $existing?->id);
                if ($email === null) {
                    $result['failed']++;
                    $result['errors'][] = "Baris {$rowNumber}: email bentrok dan tidak bisa di-generate unik.";
                    continue;
                }

                $passwordPlain = $parsed['password'] !== '' ? $parsed['password'] : 'password';
                $payload = [
                    'name' => $parsed['nama'],
                    'email' => $email,
                    'nis' => $parsed['nis'],
                    'kelas' => $parsed['kelas'] !== '' ? $parsed['kelas'] : null,
                    'no_hp' => $parsed['no_hp'] !== '' ? $parsed['no_hp'] : null,
                    'role' => 'siswa',
                    'is_active' => true,
                    'force_password_change' => false,
                ];

                if ($existing) {
                    $existing->fill($payload);
                    if ($parsed['password'] !== '') {
                        $existing->password = Hash::make($passwordPlain);
                    }
                    $existing->save();
                    $result['updated']++;
                    continue;
                }

                User::create(array_merge($payload, [
                    'password' => Hash::make($passwordPlain),
                    'email_verified_at' => now(),
                ]));
                $result['created']++;
            }

            if ($mode === 'replace_siswa' && count($importedNis) > 0) {
                $query = User::query()->where('role', 'siswa');
                $query->whereNotIn('nis', $importedNis);

                $result['deactivated'] = $query
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            } elseif ($mode === 'replace_siswa' && count($importedNis) === 0) {
                $result['errors'][] = 'Tidak ada baris valid, mode replace tidak menonaktifkan siswa lama.';
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            $result['errors'][] = 'Import gagal: '.$exception->getMessage();
            $result['failed'] = $result['failed'] + 1;
        } finally {
            fclose($handle);
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    public function templateRows(): array
    {
        return [
            'nama,email,nis,kelas,no_hp,role,password',
            'Andi Pratama,andi@example.com,2401001,X IPA 1,081234567890,siswa,password',
            'Bela Sari,,2401002,X IPA 2,081222334455,siswa,',
        ];
    }

    /**
     * @param array<int, string> $headerRow
     * @return array<string, int>
     */
    private function buildHeaderMap(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $index => $value) {
            $normalized = strtolower(trim((string) $value));
            if ($normalized !== '') {
                $map[$normalized] = $index;
            }
        }

        if (isset($map['name']) && !isset($map['nama'])) {
            $map['nama'] = $map['name'];
        }

        if (isset($map['phone']) && !isset($map['no_hp'])) {
            $map['no_hp'] = $map['phone'];
        }

        return $map;
    }

    /**
     * @param array<int, string|null> $row
     * @param array<string, int> $headerMap
     * @return array{nama:string,email:string,nis:string,kelas:string,no_hp:string,role:string,password:string}
     */
    private function parseRow(array $row, array $headerMap): array
    {
        return [
            'nama' => $this->cell($row, $headerMap, 'nama'),
            'email' => $this->cell($row, $headerMap, 'email'),
            'nis' => $this->cell($row, $headerMap, 'nis'),
            'kelas' => $this->cell($row, $headerMap, 'kelas'),
            'no_hp' => $this->cell($row, $headerMap, 'no_hp'),
            'role' => strtolower($this->cell($row, $headerMap, 'role')),
            'password' => $this->cell($row, $headerMap, 'password'),
        ];
    }

    /**
     * @param array<string, string> $parsed
     * @param array<string, bool> $seenNis
     */
    private function validateRow(array $parsed, array $seenNis): ?string
    {
        if ($parsed['nama'] === '') {
            return 'nama wajib diisi.';
        }

        if ($parsed['nis'] === '') {
            return 'nis wajib diisi.';
        }

        if (isset($seenNis[$parsed['nis']])) {
            return "nis {$parsed['nis']} duplikat di file.";
        }

        if ($parsed['email'] !== '' && !filter_var($parsed['email'], FILTER_VALIDATE_EMAIL)) {
            return 'format email tidak valid.';
        }

        if ($parsed['role'] !== '' && $parsed['role'] !== 'siswa') {
            return 'role harus kosong atau siswa.';
        }

        if ($parsed['no_hp'] !== '' && !preg_match('/^[0-9+\\- ]{8,20}$/', $parsed['no_hp'])) {
            return 'format no_hp tidak valid.';
        }

        return null;
    }

    /**
     * @param array<int, string|null> $row
     * @param array<string, int> $headerMap
     */
    private function cell(array $row, array $headerMap, string $key): string
    {
        if (!isset($headerMap[$key])) {
            return '';
        }

        $index = $headerMap[$key];

        return trim((string) ($row[$index] ?? ''));
    }

    /**
     * @param array<int, string|null> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function resolveEmail(string $rawEmail, string $nis, ?int $existingId = null): ?string
    {
        $email = $rawEmail !== '' ? strtolower($rawEmail) : strtolower($nis).'@school.local';
        if ($this->isEmailAvailable($email, $existingId)) {
            return $email;
        }

        for ($suffix = 1; $suffix <= 100; $suffix++) {
            $candidate = strtolower($nis)."+{$suffix}@school.local";
            if ($this->isEmailAvailable($candidate, $existingId)) {
                return $candidate;
            }
        }

        return null;
    }

    private function isEmailAvailable(string $email, ?int $existingId = null): bool
    {
        $query = User::query()->where('email', $email);
        if ($existingId !== null) {
            $query->where('id', '!=', $existingId);
        }

        return !$query->exists();
    }

    /**
     * @param array<int, string> $errors
     * @return array{created:int,updated:int,deactivated:int,failed:int,errors:array<int,string>,total_rows:int}
     */
    private function emptyResult(array $errors): array
    {
        return [
            'created' => 0,
            'updated' => 0,
            'deactivated' => 0,
            'failed' => count($errors),
            'errors' => $errors,
            'total_rows' => 0,
        ];
    }
}
