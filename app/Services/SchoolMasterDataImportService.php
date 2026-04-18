<?php

namespace App\Services;

use App\Models\Gedung;
use App\Models\Jurusan;
use App\Models\Kategori;
use App\Models\Log;
use App\Models\MasterDataImportBatch;
use App\Models\Ruangan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class SchoolMasterDataImportService
{
    private const MODES = ['replace_safe', 'upsert_only'];
    private const SCOPES = ['full', 'gedung_only', 'ruangan_only', 'mapping_only', 'kategori_only'];

    /**
     * @return array<int, string>
     */
    public function modes(): array
    {
        return self::MODES;
    }

    /**
     * @return array<int, string>
     */
    public function scopes(): array
    {
        return self::SCOPES;
    }

    public function preview(
        string $filePath,
        string $mode = 'replace_safe',
        string $scope = 'full',
        ?int $actorId = null,
        ?string $originalFilename = null,
        ?string $schoolKey = null,
        ?string $storedPath = null
    ): MasterDataImportBatch {
        $this->assertMode($mode);
        $this->assertScope($scope);

        if (!is_file($filePath)) {
            throw new RuntimeException('File import tidak ditemukan.');
        }

        $scope = $this->resolveScopeForFile($filePath, $scope, $originalFilename);

        $checksum = hash_file('sha256', $filePath) ?: null;
        $filename = $originalFilename ?: basename($filePath);

        $batch = MasterDataImportBatch::query()->create([
            'school_key' => $schoolKey,
            'mode' => $mode,
            'scope' => $scope,
            'status' => 'uploaded',
            'filename' => $filename,
            'storage_path' => $storedPath ?: $filePath,
            'checksum' => $checksum,
            'actor_id' => $actorId,
        ]);

        try {
            $dataset = $this->readDataset($filePath, $scope, $originalFilename);
            $validation = $this->validateDataset($dataset, $scope);
            $summary = $this->estimateSummary($dataset, $mode, $scope);

            $batch->summary_json = $summary;
            $batch->warnings_json = $validation['warnings'];
            $batch->errors_json = $validation['errors'];
            $batch->status = empty($validation['errors']) ? 'validated' : 'failed';

            if (!empty($validation['errors'])) {
                $batch->error_report_path = $this->writeErrorReport($batch->id, $validation['errors']);
            }

            $batch->save();
        } catch (\Throwable $exception) {
            $batch->status = 'failed';
            $batch->errors_json = [[
                'sheet' => '-',
                'row' => '-',
                'column' => '-',
                'error_code' => 'exception',
                'error_message' => $exception->getMessage(),
                'raw_value' => '-',
            ]];
            $batch->error_report_path = $this->writeErrorReport($batch->id, $batch->errors_json);
            $batch->save();

            throw $exception;
        }

        return $batch->fresh();
    }

    public function commitBatch(MasterDataImportBatch $batch, ?int $actorId = null): MasterDataImportBatch
    {
        if ($batch->status !== 'validated') {
            throw new RuntimeException('Batch belum valid untuk diterapkan. Jalankan preview/validasi dulu.');
        }

        if (!empty($batch->errors_json)) {
            throw new RuntimeException('Batch masih memiliki error validasi.');
        }

        $absolutePath = $this->resolveBatchFilePath($batch);
        $lock = Cache::lock('master_data_import_lock', 300);

        if (!$lock->get()) {
            throw new RuntimeException('Import sedang berjalan oleh proses lain. Coba lagi sebentar.');
        }

        try {
            $dataset = $this->readDataset($absolutePath, $batch->scope, $batch->filename);
            $result = DB::transaction(function () use ($dataset, $batch): array {
                return $this->applyDataset($dataset, $batch->mode, $batch->scope);
            });

            $final = $this->buildResult($batch->mode, $batch->checksum, $result);

            $batch->summary_json = $final;
            $batch->warnings_json = $final['warnings'] ?? [];
            $batch->errors_json = [];
            $batch->status = 'applied';
            $batch->actor_id = $actorId ?? $batch->actor_id;
            $batch->save();

            $this->writeLog($batch->actor_id, $final, $batch->scope);
        } finally {
            optional($lock)->release();
        }

        return $batch->fresh();
    }

    /**
     * Backward-compatible one-step import.
     *
     * @return array<string, mixed>
     */
    public function import(
        string $filePath,
        string $mode = 'replace_safe',
        ?int $actorId = null,
        ?string $originalFilename = null,
        string $scope = 'full'
    ): array {
        $batch = $this->preview($filePath, $mode, $scope, $actorId, $originalFilename, null, $filePath);

        if (!empty($batch->errors_json)) {
            return $this->buildResult($mode, $batch->checksum, [
                'errors' => $batch->errors_json,
                'warnings' => $batch->warnings_json ?? [],
            ]);
        }

        $applied = $this->commitBatch($batch, $actorId);
        return is_array($applied->summary_json) ? $applied->summary_json : [];
    }

    /**
     * @return array<int, string>
     */
    public function templateRows(string $sheet): array
    {
        return match ($sheet) {
            'kategori' => [
                'nama,deskripsi,icon,is_active',
                'Kelistrikan,Masalah instalasi listrik,fa-bolt,1',
            ],
            'gedung' => [
                'kode_gedung,nama_gedung,jumlah_lantai,deskripsi,is_active',
                'E,Gedung E,2,Gedung kelas dan laboratorium,1',
            ],
            'ruangan' => [
                'kode_ruang,nama_ruang,kode_gedung,lantai,kapasitas,is_active',
                'E203,XII MP 1,E,2,36,1',
            ],
            'mapping_jurusan_ruang' => [
                'kode_ruang,kode_jurusan,is_primary',
                'E203,MP,1',
            ],
            default => [],
        };
    }

    private function resolveBatchFilePath(MasterDataImportBatch $batch): string
    {
        $storagePath = (string) $batch->storage_path;

        if (is_file($storagePath)) {
            return $storagePath;
        }

        $absolute = Storage::disk('local')->path($storagePath);
        if (!is_file($absolute)) {
            throw new RuntimeException('File batch tidak ditemukan di storage.');
        }

        return $absolute;
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    private function readDataset(string $filePath, string $scope, ?string $originalFilename = null): array
    {
        $extension = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === '' && $originalFilename !== null) {
            $extension = strtolower((string) pathinfo($originalFilename, PATHINFO_EXTENSION));
        }

        if ($extension === 'zip') {
            return $this->readFromZip($filePath, $scope);
        }

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            return $this->readFromSpreadsheet($filePath, $scope);
        }

        if ($extension === 'csv') {
            return $this->readFromSingleCsv($filePath, $scope, $originalFilename);
        }

        throw new RuntimeException('Format file tidak didukung. Gunakan ZIP (4 CSV), XLSX (4 sheet), atau CSV tunggal sesuai scope.');
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    private function readFromSingleCsv(string $filePath, string $scope, ?string $originalFilename = null): array
    {
        if ($scope === 'full') {
            $scope = $this->detectSingleCsvScope($filePath, $originalFilename);
        }

        $sheet = match ($scope) {
            'gedung_only' => 'gedung',
            'ruangan_only' => 'ruangan',
            'mapping_only' => 'mapping_jurusan_ruang',
            'kategori_only' => 'kategori',
            default => null,
        };

        if ($sheet === null) {
            throw new RuntimeException('Scope CSV tidak valid.');
        }

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            throw new RuntimeException('Gagal membaca file CSV.');
        }

        $dataset = $this->emptyDataset();
        $dataset[$sheet] = $this->parseCsvRows($contents, $sheet);

        return $dataset;
    }

    private function resolveScopeForFile(string $filePath, string $scope, ?string $originalFilename = null): string
    {
        $extension = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === '' && $originalFilename !== null) {
            $extension = strtolower((string) pathinfo($originalFilename, PATHINFO_EXTENSION));
        }

        if ($extension !== 'csv' || $scope !== 'full') {
            return $scope;
        }

        return $this->detectSingleCsvScope($filePath, $originalFilename);
    }

    private function detectSingleCsvScope(string $filePath, ?string $originalFilename = null): string
    {
        $contents = file_get_contents($filePath);
        if ($contents === false) {
            throw new RuntimeException('Gagal membaca file CSV.');
        }

        $lines = preg_split('/\r\n|\n|\r/', trim($contents));
        if (!$lines || empty($lines[0])) {
            throw new RuntimeException('CSV kosong. Isi minimal header sesuai template.');
        }

        $headers = str_getcsv((string) $lines[0]);
        $normalizedHeaders = array_map(
            fn ($value) => $this->normalizeHeader((string) $value),
            $headers
        );

        $sheet = $this->detectSheetByHeaders($normalizedHeaders, $originalFilename);
        if ($sheet === null) {
            throw new RuntimeException('CSV tunggal tidak dikenali. Pilih scope yang sesuai atau gunakan template.');
        }

        return match ($sheet) {
            'gedung' => 'gedung_only',
            'ruangan' => 'ruangan_only',
            'mapping_jurusan_ruang' => 'mapping_only',
            'kategori' => 'kategori_only',
            default => throw new RuntimeException('Scope CSV tidak valid.'),
        };
    }

    /**
     * @param array<int, string> $normalizedHeaders
     */
    private function detectSheetByHeaders(array $normalizedHeaders, ?string $originalFilename = null): ?string
    {
        $required = [
            'gedung' => ['kode_gedung', 'nama_gedung'],
            'ruangan' => ['kode_ruang', 'kode_gedung'],
            'mapping_jurusan_ruang' => ['kode_ruang', 'kode_jurusan'],
            'kategori' => ['nama'],
        ];

        $matches = [];
        foreach ($required as $sheet => $requiredHeaders) {
            $mappedHeaders = array_map(
                fn ($header) => $this->mapHeaderAlias($sheet, $header),
                $normalizedHeaders
            );
            $mappedLookup = array_flip($mappedHeaders);

            $ok = true;
            foreach ($requiredHeaders as $requiredHeader) {
                if (!isset($mappedLookup[$requiredHeader])) {
                    $ok = false;
                    break;
                }
            }

            if ($ok) {
                $matches[] = $sheet;
            }
        }

        if (count($matches) === 1) {
            return $matches[0];
        }

        if ($originalFilename !== null) {
            $name = strtolower($originalFilename);
            foreach ($matches as $candidate) {
                if ($candidate === 'mapping_jurusan_ruang' && str_contains($name, 'mapping')) {
                    return $candidate;
                }
                if ($candidate === 'ruangan' && str_contains($name, 'ruangan')) {
                    return $candidate;
                }
                if ($candidate === 'gedung' && str_contains($name, 'gedung')) {
                    return $candidate;
                }
                if ($candidate === 'kategori' && str_contains($name, 'kategori')) {
                    return $candidate;
                }
            }
        }

        return $matches[0] ?? null;
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    private function readFromZip(string $filePath, string $scope): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException('Gagal membuka file ZIP.');
        }

        $dataset = $this->emptyDataset();
        $required = $this->requiredSheets($scope);

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);
            if (!$name) {
                continue;
            }

            $base = strtolower(pathinfo($name, PATHINFO_FILENAME));
            if (!array_key_exists($base, $dataset)) {
                continue;
            }

            $contents = (string) $zip->getFromIndex($index);
            $dataset[$base] = $this->parseCsvRows($contents, $base);
        }

        $zip->close();

        foreach ($required as $sheet) {
            if (empty($dataset[$sheet])) {
                throw new RuntimeException("Sheet {$sheet} wajib ada sesuai scope {$scope}.");
            }
        }

        return $dataset;
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    private function readFromSpreadsheet(string $filePath, string $scope): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new RuntimeException('Import XLSX membutuhkan package phpoffice/phpspreadsheet.');
        }

        /** @var \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet */
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

        $dataset = $this->emptyDataset();
        foreach (array_keys($dataset) as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) {
                continue;
            }

            $rows = $sheet->toArray(null, true, true, true);
            if (empty($rows)) {
                continue;
            }

            $headers = array_map(
                fn ($value) => $this->mapHeaderAlias($sheetName, $this->normalizeHeader((string) $value)),
                array_values((array) array_shift($rows))
            );

            $parsed = [];
            foreach ($rows as $row) {
                $values = array_values((array) $row);
                if ($this->isEmptyRow($values)) {
                    continue;
                }

                $item = [];
                foreach ($headers as $idx => $header) {
                    if ($header === '') {
                        continue;
                    }
                    $item[$header] = trim((string) ($values[$idx] ?? ''));
                }
                $parsed[] = $item;
            }

            $dataset[$sheetName] = $parsed;
        }

        foreach ($this->requiredSheets($scope) as $sheet) {
            if (empty($dataset[$sheet])) {
                throw new RuntimeException("Sheet {$sheet} wajib ada sesuai scope {$scope}.");
            }
        }

        return $dataset;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseCsvRows(string $contents, ?string $sheet = null): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($contents));
        if (!$lines || count($lines) < 1) {
            return [];
        }

        $headerLine = array_shift($lines);
        $headers = str_getcsv((string) $headerLine);
        $headers = array_map(function ($value) use ($sheet) {
            $normalized = $this->normalizeHeader((string) $value);
            return $sheet ? $this->mapHeaderAlias($sheet, $normalized) : $normalized;
        }, $headers);

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = str_getcsv($line);
            $item = [];
            foreach ($headers as $idx => $header) {
                if ($header === '') {
                    continue;
                }
                $item[$header] = trim((string) ($values[$idx] ?? ''));
            }
            $rows[] = $item;
        }

        return $rows;
    }

    private function mapHeaderAlias(string $sheet, string $header): string
    {
        $alias = [
            'gedung' => [
                'kode_gedung' => 'kode_gedung',
                'kode gedung' => 'kode_gedung',
                'kd_gedung' => 'kode_gedung',
                'nama_gedung' => 'nama_gedung',
                'nama gedung' => 'nama_gedung',
                'jml_lantai' => 'jumlah_lantai',
                'jumlah_lantai' => 'jumlah_lantai',
                'isactive' => 'is_active',
            ],
            'ruangan' => [
                'kode_ruang' => 'kode_ruang',
                'kode ruang' => 'kode_ruang',
                'kd_ruang' => 'kode_ruang',
                'nama_ruang' => 'nama_ruang',
                'nama ruang' => 'nama_ruang',
                'kode_gedung' => 'kode_gedung',
                'kd_gedung' => 'kode_gedung',
                'isactive' => 'is_active',
            ],
            'mapping_jurusan_ruang' => [
                'kode_ruang' => 'kode_ruang',
                'kd_ruang' => 'kode_ruang',
                'kode_jurusan' => 'kode_jurusan',
                'kd_jurusan' => 'kode_jurusan',
                'utama' => 'is_primary',
            ],
            'kategori' => [
                'isactive' => 'is_active',
            ],
        ];

        return $alias[$sheet][$header] ?? $header;
    }

    /**
     * @param array<string, array<int, array<string, string>>> $dataset
     * @return array{errors:array<int, array<string, string>>,warnings:array<int, string>}
     */
    private function validateDataset(array $dataset, string $scope): array
    {
        $errors = [];
        $warnings = [];

        $gedungCodes = [];
        if ($this->scopeHas($scope, 'gedung')) {
            foreach ($dataset['gedung'] as $index => $row) {
                $code = strtoupper(trim((string) ($row['kode_gedung'] ?? '')));
                if ($code === '') {
                    $errors[] = $this->errorItem('gedung', $index + 2, 'kode_gedung', 'required', 'kode_gedung wajib diisi.', '');
                    continue;
                }
                if (isset($gedungCodes[$code])) {
                    $errors[] = $this->errorItem('gedung', $index + 2, 'kode_gedung', 'duplicate', "kode_gedung {$code} duplikat.", $code);
                    continue;
                }
                $gedungCodes[$code] = true;
            }
        }

        $ruangCodes = [];
        if ($this->scopeHas($scope, 'ruangan')) {
            foreach ($dataset['ruangan'] as $index => $row) {
                $room = strtoupper(trim((string) ($row['kode_ruang'] ?? '')));
                $gedung = strtoupper(trim((string) ($row['kode_gedung'] ?? '')));

                if ($room === '') {
                    $errors[] = $this->errorItem('ruangan', $index + 2, 'kode_ruang', 'required', 'kode_ruang wajib diisi.', '');
                } elseif (isset($ruangCodes[$room])) {
                    $errors[] = $this->errorItem('ruangan', $index + 2, 'kode_ruang', 'duplicate', "kode_ruang {$room} duplikat.", $room);
                }
                $ruangCodes[$room] = true;

                if ($gedung === '') {
                    $errors[] = $this->errorItem('ruangan', $index + 2, 'kode_gedung', 'required', 'kode_gedung wajib diisi.', '');
                    continue;
                }

                if ($this->scopeHas($scope, 'gedung')) {
                    if (!isset($gedungCodes[$gedung])) {
                        $errors[] = $this->errorItem('ruangan', $index + 2, 'kode_gedung', 'not_found', "kode_gedung {$gedung} tidak ditemukan di file gedung.", $gedung);
                    }
                } else {
                    $exists = Gedung::query()->where('kode', $gedung)->where('is_active', true)->exists();
                    if (!$exists) {
                        $errors[] = $this->errorItem('ruangan', $index + 2, 'kode_gedung', 'not_found', "kode_gedung {$gedung} tidak ditemukan di master gedung aktif.", $gedung);
                    }
                }
            }
        }

        if ($this->scopeHas($scope, 'mapping')) {
            $jurusanCodes = array_flip(array_keys($this->defaultJurusanMap()));
            foreach ($dataset['mapping_jurusan_ruang'] as $index => $row) {
                $room = strtoupper(trim((string) ($row['kode_ruang'] ?? '')));
                $jurusan = strtoupper(trim((string) ($row['kode_jurusan'] ?? '')));

                $roomValid = false;
                if ($room !== '') {
                    if ($this->scopeHas($scope, 'ruangan')) {
                        $roomValid = isset($ruangCodes[$room]);
                    } else {
                        $roomValid = Ruangan::query()->where('kode', $room)->where('is_active', true)->exists();
                    }
                }

                if (!$roomValid) {
                    $errors[] = $this->errorItem('mapping_jurusan_ruang', $index + 2, 'kode_ruang', 'not_found', "kode_ruang {$room} tidak valid.", $room);
                }

                if ($jurusan === '' || !isset($jurusanCodes[$jurusan])) {
                    $errors[] = $this->errorItem('mapping_jurusan_ruang', $index + 2, 'kode_jurusan', 'invalid', "kode_jurusan {$jurusan} tidak valid.", $jurusan);
                }
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param array<string, array<int, array<string, string>>> $dataset
     * @return array<string, mixed>
     */
    private function estimateSummary(array $dataset, string $mode, string $scope): array
    {
        $summary = [
            'mode' => $mode,
            'scope' => $scope,
            'kategori' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'gedung' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'ruangan' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'jurusan' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'mapping' => ['created' => 0, 'updated' => 0, 'deleted' => 0],
        ];

        if ($this->scopeHas($scope, 'kategori')) {
            foreach ($dataset['kategori'] as $row) {
                $name = trim((string) ($row['nama'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $exists = Kategori::query()->where('nama', $name)->exists();
                $summary['kategori'][$exists ? 'updated' : 'created']++;
            }
        }

        if ($this->scopeHas($scope, 'gedung')) {
            $codes = [];
            foreach ($dataset['gedung'] as $row) {
                $code = strtoupper(trim((string) ($row['kode_gedung'] ?? '')));
                if ($code === '') {
                    continue;
                }
                $codes[] = $code;
                $exists = Gedung::query()->where('kode', $code)->exists();
                $summary['gedung'][$exists ? 'updated' : 'created']++;
            }
            if ($mode === 'replace_safe' && !empty($codes)) {
                $summary['gedung']['deactivated'] = Gedung::query()->whereNotIn('kode', $codes)->where('is_active', true)->count();
            }
        }

        if ($this->scopeHas($scope, 'ruangan')) {
            $codes = [];
            foreach ($dataset['ruangan'] as $row) {
                $code = strtoupper(trim((string) ($row['kode_ruang'] ?? '')));
                if ($code === '') {
                    continue;
                }
                $codes[] = $code;
                $exists = Ruangan::query()->where('kode', $code)->exists();
                $summary['ruangan'][$exists ? 'updated' : 'created']++;
            }
            if ($mode === 'replace_safe' && !empty($codes)) {
                $summary['ruangan']['deactivated'] = Ruangan::query()->whereNotIn('kode', $codes)->where('is_active', true)->count();
            }
        }

        if ($this->scopeHas($scope, 'mapping')) {
            foreach ($dataset['mapping_jurusan_ruang'] as $row) {
                $room = strtoupper(trim((string) ($row['kode_ruang'] ?? '')));
                $jurusan = strtoupper(trim((string) ($row['kode_jurusan'] ?? '')));
                if ($room === '' || $jurusan === '') {
                    continue;
                }

                $ruangan = Ruangan::query()->where('kode', $room)->first();
                $jurusanModel = Jurusan::query()->where('kode', $jurusan)->first();
                if (!$ruangan || !$jurusanModel) {
                    continue;
                }

                $exists = DB::table('jurusan_ruangan')
                    ->where('jurusan_id', $jurusanModel->id)
                    ->where('ruangan_id', $ruangan->id)
                    ->exists();

                $summary['mapping'][$exists ? 'updated' : 'created']++;
            }
        }

        return $summary;
    }

    /**
     * @param array<string, array<int, array<string, string>>> $dataset
     * @return array<string, mixed>
     */
    private function applyDataset(array $dataset, string $mode, string $scope): array
    {
        $this->ensureDefaultJurusans();

        $stats = [
            'kategori' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'gedung' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'ruangan' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'jurusan' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'mapping' => ['created' => 0, 'updated' => 0, 'deleted' => 0],
            'warnings' => [],
            'errors' => [],
        ];

        if ($this->scopeHas($scope, 'kategori')) {
            $stats['kategori'] = $this->syncKategoris($dataset['kategori'], $mode);
        }

        if ($this->scopeHas($scope, 'gedung')) {
            $stats['gedung'] = $this->syncGedungs($dataset['gedung'], $mode);
        }

        if ($this->scopeHas($scope, 'ruangan')) {
            $stats['ruangan'] = $this->syncRuangans($dataset['ruangan'], $mode, $stats['warnings']);
        }

        if ($this->scopeHas($scope, 'mapping')) {
            $stats['jurusan'] = $this->syncJurusans('upsert_only');
            $stats['mapping'] = $this->syncJurusanRuangans($dataset['mapping_jurusan_ruang'], $mode, $stats['warnings']);
        }

        return $stats;
    }

    /**
     * @param array<int, array<string, string>> $errors
     */
    private function writeErrorReport(int $batchId, array $errors): string
    {
        $path = "master-data/errors/{$batchId}-errors.csv";
        $lines = ['sheet,row,column,error_code,error_message,raw_value'];

        foreach ($errors as $error) {
            $lines[] = implode(',', [
                $this->csvSafe($error['sheet'] ?? '-'),
                $this->csvSafe((string) ($error['row'] ?? '-')),
                $this->csvSafe($error['column'] ?? '-'),
                $this->csvSafe($error['error_code'] ?? '-'),
                $this->csvSafe($error['error_message'] ?? '-'),
                $this->csvSafe($error['raw_value'] ?? '-'),
            ]);
        }

        Storage::disk('local')->put($path, implode(PHP_EOL, $lines).PHP_EOL);
        return $path;
    }

    private function csvSafe(string $value): string
    {
        $escaped = str_replace('"', '""', $value);
        return '"'.$escaped.'"';
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    private function emptyDataset(): array
    {
        return [
            'kategori' => [],
            'gedung' => [],
            'ruangan' => [],
            'mapping_jurusan_ruang' => [],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function requiredSheets(string $scope): array
    {
        return match ($scope) {
            'full' => ['kategori', 'gedung', 'ruangan', 'mapping_jurusan_ruang'],
            'gedung_only' => ['gedung'],
            'ruangan_only' => ['ruangan'],
            'mapping_only' => ['mapping_jurusan_ruang'],
            'kategori_only' => ['kategori'],
            default => [],
        };
    }

    private function scopeHas(string $scope, string $area): bool
    {
        if ($scope === 'full') {
            return true;
        }

        return match ($area) {
            'kategori' => $scope === 'kategori_only',
            'gedung' => $scope === 'gedung_only',
            'ruangan' => $scope === 'ruangan_only',
            'mapping' => $scope === 'mapping_only',
            default => false,
        };
    }

    private function assertMode(string $mode): void
    {
        if (!in_array($mode, self::MODES, true)) {
            throw new RuntimeException('Mode import tidak valid.');
        }
    }

    private function assertScope(string $scope): void
    {
        if (!in_array($scope, self::SCOPES, true)) {
            throw new RuntimeException('Scope import tidak valid.');
        }
    }

    /**
     * @return array<string, string>
     */
    private function errorItem(string $sheet, int|string $row, string $column, string $code, string $message, string $raw): array
    {
        return [
            'sheet' => $sheet,
            'row' => (string) $row,
            'column' => $column,
            'error_code' => $code,
            'error_message' => $message,
            'raw_value' => $raw,
        ];
    }

    /**
     * @param array<int, array<string, string>> $rows
     * @return array<string, int>
     */
    private function syncKategoris(array $rows, string $mode): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'deactivated' => 0];
        $importedNames = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['nama'] ?? ''));
            if ($name === '') {
                continue;
            }

            $importedNames[] = $name;
            $payload = [
                'deskripsi' => $row['deskripsi'] ?? null,
                'icon' => $row['icon'] ?? null,
                'is_active' => $this->toBool($row['is_active'] ?? '1'),
            ];

            $existing = Kategori::query()->where('nama', $name)->first();
            if ($existing) {
                $existing->fill($payload)->save();
                $stats['updated']++;
            } else {
                Kategori::query()->create(array_merge(['nama' => $name], $payload));
                $stats['created']++;
            }
        }

        if ($mode === 'replace_safe' && !empty($importedNames)) {
            $stats['deactivated'] = Kategori::query()
                ->whereNotIn('nama', $importedNames)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        return $stats;
    }

    /**
     * @param array<int, array<string, string>> $rows
     * @return array<string, int>
     */
    private function syncGedungs(array $rows, string $mode): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'deactivated' => 0];
        $importedCodes = [];

        foreach ($rows as $row) {
            $kode = strtoupper(trim((string) ($row['kode_gedung'] ?? '')));
            if ($kode === '') {
                continue;
            }

            $importedCodes[] = $kode;
            $payload = [
                'nama' => trim((string) ($row['nama_gedung'] ?? $kode)),
                'jumlah_lantai' => max(1, (int) ($row['jumlah_lantai'] ?? 1)),
                'deskripsi' => trim((string) ($row['deskripsi'] ?? '')) ?: null,
                'is_active' => $this->toBool($row['is_active'] ?? '1'),
                'source_ref' => strtoupper($kode),
            ];

            $existing = Gedung::query()->where('kode', $kode)->first();
            if ($existing) {
                $existing->fill($payload)->save();
                $stats['updated']++;
            } else {
                Gedung::query()->create(array_merge(['kode' => $kode], $payload));
                $stats['created']++;
            }
        }

        if ($mode === 'replace_safe' && !empty($importedCodes)) {
            $stats['deactivated'] = Gedung::query()
                ->whereNotIn('kode', $importedCodes)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        return $stats;
    }

    /**
     * @param array<int, array<string, string>> $rows
     * @param array<int, string> $warnings
     * @return array<string, int>
     */
    private function syncRuangans(array $rows, string $mode, array &$warnings): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'deactivated' => 0];
        $importedCodes = [];

        foreach ($rows as $row) {
            $kodeRuang = strtoupper(trim((string) ($row['kode_ruang'] ?? '')));
            $kodeGedung = strtoupper(trim((string) ($row['kode_gedung'] ?? '')));

            if ($kodeRuang === '' || $kodeGedung === '') {
                continue;
            }

            $gedung = Gedung::query()->where('kode', $kodeGedung)->first();
            if (!$gedung) {
                $warnings[] = "Ruangan {$kodeRuang} dilewati karena gedung {$kodeGedung} tidak ditemukan.";
                continue;
            }

            $importedCodes[] = $kodeRuang;
            $payload = [
                'gedung_id' => $gedung->id,
                'nama' => trim((string) ($row['nama_ruang'] ?? $kodeRuang)),
                'lantai' => (string) max(1, (int) ($row['lantai'] ?? 1)),
                'kapasitas' => isset($row['kapasitas']) && $row['kapasitas'] !== '' ? (int) $row['kapasitas'] : null,
                'is_active' => $this->toBool($row['is_active'] ?? '1'),
                'source_ref' => $kodeRuang,
            ];

            $existing = Ruangan::query()->where('kode', $kodeRuang)->first();
            if ($existing) {
                $existing->fill($payload)->save();
                $stats['updated']++;
            } else {
                Ruangan::query()->create(array_merge(['kode' => $kodeRuang], $payload));
                $stats['created']++;
            }
        }

        if ($mode === 'replace_safe' && !empty($importedCodes)) {
            $stats['deactivated'] = Ruangan::query()
                ->whereNotIn('kode', $importedCodes)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        return $stats;
    }

    /**
     * @return array<string, int>
     */
    private function syncJurusans(string $mode): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'deactivated' => 0];
        $codes = [];

        foreach ($this->defaultJurusanMap() as $kode => $nama) {
            $codes[] = $kode;
            $existing = Jurusan::query()->where('kode', $kode)->first();

            if ($existing) {
                $existing->fill(['nama' => $nama, 'is_active' => true])->save();
                $stats['updated']++;
            } else {
                Jurusan::query()->create([
                    'kode' => $kode,
                    'nama' => $nama,
                    'is_active' => true,
                ]);
                $stats['created']++;
            }
        }

        if ($mode === 'replace_safe') {
            $stats['deactivated'] = Jurusan::query()
                ->whereNotIn('kode', $codes)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        return $stats;
    }

    /**
     * @param array<int, array<string, string>> $rows
     * @param array<int, string> $warnings
     * @return array<string, int>
     */
    private function syncJurusanRuangans(array $rows, string $mode, array &$warnings): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'deleted' => 0];
        $desiredKeys = [];

        foreach ($rows as $row) {
            $kodeRuang = strtoupper(trim((string) ($row['kode_ruang'] ?? '')));
            $kodeJurusan = strtoupper(trim((string) ($row['kode_jurusan'] ?? '')));

            $ruangan = Ruangan::query()->where('kode', $kodeRuang)->first();
            $jurusan = Jurusan::query()->where('kode', $kodeJurusan)->first();

            if (!$ruangan || !$jurusan) {
                $warnings[] = "Mapping {$kodeRuang}/{$kodeJurusan} dilewati karena data tidak lengkap.";
                continue;
            }

            $key = "{$jurusan->id}:{$ruangan->id}";
            $desiredKeys[$key] = true;
            $isPrimary = $this->toBool($row['is_primary'] ?? '0');

            $existing = DB::table('jurusan_ruangan')
                ->where('jurusan_id', $jurusan->id)
                ->where('ruangan_id', $ruangan->id)
                ->first();

            if ($existing) {
                DB::table('jurusan_ruangan')
                    ->where('jurusan_id', $jurusan->id)
                    ->where('ruangan_id', $ruangan->id)
                    ->update([
                        'is_primary' => $isPrimary,
                        'updated_at' => now(),
                    ]);
                $stats['updated']++;
            } else {
                DB::table('jurusan_ruangan')->insert([
                    'jurusan_id' => $jurusan->id,
                    'ruangan_id' => $ruangan->id,
                    'is_primary' => $isPrimary,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $stats['created']++;
            }
        }

        if ($mode === 'replace_safe') {
            $existingRows = DB::table('jurusan_ruangan')->get(['jurusan_id', 'ruangan_id']);
            foreach ($existingRows as $existingRow) {
                $key = "{$existingRow->jurusan_id}:{$existingRow->ruangan_id}";
                if (!isset($desiredKeys[$key])) {
                    DB::table('jurusan_ruangan')
                        ->where('jurusan_id', $existingRow->jurusan_id)
                        ->where('ruangan_id', $existingRow->ruangan_id)
                        ->delete();
                    $stats['deleted']++;
                }
            }
        }

        return $stats;
    }

    private function ensureDefaultJurusans(): void
    {
        foreach ($this->defaultJurusanMap() as $kode => $nama) {
            Jurusan::query()->updateOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'is_active' => true]
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function defaultJurusanMap(): array
    {
        return [
            'RPL' => 'Rekayasa Perangkat Lunak',
            'MP' => 'Manajemen Perkantoran',
            'AK' => 'Akuntansi',
            'BD' => 'Bisnis Digital',
            'LP' => 'Layanan Perbankan',
        ];
    }

    private function toBool(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'y', 'ya'], true);
    }

    private function normalizeHeader(string $value): string
    {
        return Str::of($value)
            ->replace("\xEF\xBB\xBF", '')
            ->lower()
            ->trim()
            ->replace(' ', '_')
            ->replace('-', '_')
            ->value();
    }

    /**
     * @param array<int, mixed> $values
     */
    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $stats
     * @return array<string, mixed>
     */
    private function buildResult(string $mode, ?string $checksum, array $stats): array
    {
        return array_merge([
            'mode' => $mode,
            'checksum' => $checksum,
            'scope' => 'full',
            'kategori' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'gedung' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'ruangan' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'jurusan' => ['created' => 0, 'updated' => 0, 'deactivated' => 0],
            'mapping' => ['created' => 0, 'updated' => 0, 'deleted' => 0],
            'warnings' => [],
            'errors' => [],
        ], $stats);
    }

    /**
     * @param array<string, mixed> $result
     */
    private function writeLog(?int $actorId, array $result, string $scope): void
    {
        if ($actorId === null) {
            return;
        }

        $result['scope'] = $scope;

        Log::query()->create([
            'pengaduan_id' => null,
            'user_id' => $actorId,
            'action' => Log::ACTION_MASTER_DATA_IMPORT,
            'description' => 'Import master data sekolah dijalankan.',
            'new_value' => $result,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
