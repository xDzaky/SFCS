<?php

namespace App\Console\Commands;

use App\Services\SchoolMasterDataImportService;
use Illuminate\Console\Command;
use RuntimeException;

class ImportSchoolMasterDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'master-data:import-school-layout
                            {file : Path file ZIP/XLSX master data sekolah}
                            {--mode=replace_safe : replace_safe|upsert_only}
                            {--scope=full : full|gedung_only|ruangan_only|mapping_only|kategori_only}
                            {--actor= : User ID untuk audit log}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import master data kategori, gedung, ruangan, dan mapping jurusan';

    public function __construct(private readonly SchoolMasterDataImportService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = (string) $this->argument('file');
        $mode = (string) $this->option('mode');
        $scope = (string) $this->option('scope');
        $actorId = $this->option('actor');
        $actorId = $actorId !== null && $actorId !== '' ? (int) $actorId : null;

        try {
            $result = $this->service->import($file, $mode, $actorId, basename($file), $scope);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        } catch (\Throwable $exception) {
            $this->error('Import gagal: '.$exception->getMessage());
            return self::FAILURE;
        }

        if (!empty($result['errors'])) {
            $this->error('Import batal karena validasi gagal:');
            foreach ($result['errors'] as $error) {
                if (is_array($error)) {
                    $sheet = $error['sheet'] ?? '-';
                    $row = $error['row'] ?? '-';
                    $message = $error['error_message'] ?? 'Error validasi.';
                    $this->line("- [{$sheet} row {$row}] {$message}");
                } else {
                    $this->line('- '.$error);
                }
            }
            return self::FAILURE;
        }

        $this->info('Import master data selesai.');
        $this->table(
            ['Entity', 'Created', 'Updated', 'Deactivated/Deleted'],
            [
                ['Kategori', $result['kategori']['created'], $result['kategori']['updated'], $result['kategori']['deactivated']],
                ['Gedung', $result['gedung']['created'], $result['gedung']['updated'], $result['gedung']['deactivated']],
                ['Ruangan', $result['ruangan']['created'], $result['ruangan']['updated'], $result['ruangan']['deactivated']],
                ['Jurusan', $result['jurusan']['created'], $result['jurusan']['updated'], $result['jurusan']['deactivated']],
                ['Mapping', $result['mapping']['created'], $result['mapping']['updated'], $result['mapping']['deleted']],
            ]
        );
        $this->line('Scope: '.($result['scope'] ?? $scope));

        if (!empty($result['warnings'])) {
            $this->warn('Warnings:');
            foreach ($result['warnings'] as $warning) {
                $this->line('- '.$warning);
            }
        }

        return self::SUCCESS;
    }
}
