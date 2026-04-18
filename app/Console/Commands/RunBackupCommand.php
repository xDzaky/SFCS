<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class RunBackupCommand extends Command
{
    protected $signature = 'backup:run {--retention-days= : Override retention days for old backups}';
    protected $description = 'Create database backup and prune old backups.';

    public function handle(): int
    {
        $driver = config('database.default');
        $path = storage_path('app/backups');
        File::ensureDirectoryExists($path);

        $timestamp = now()->format('Ymd_His');
        $backupPath = "{$path}/sfcs_{$timestamp}.sql";

        if ($driver === 'sqlite') {
            $source = config('database.connections.sqlite.database');
            if (!$source || !File::exists($source)) {
                $this->error('SQLite database file not found.');
                return self::FAILURE;
            }
            File::copy($source, "{$path}/sfcs_{$timestamp}.sqlite");
            $this->info('SQLite backup created.');
        } else {
            $dumpBinary = $this->resolveDumpBinary();
            if (!$dumpBinary) {
                $this->error('Backup failed: tidak menemukan binary dump (mariadb-dump/mysqldump).');
                return self::FAILURE;
            }

            $host = (string) config("database.connections.{$driver}.host");
            $port = (string) config("database.connections.{$driver}.port");
            $database = (string) config("database.connections.{$driver}.database");
            $username = (string) config("database.connections.{$driver}.username");
            $password = (string) config("database.connections.{$driver}.password");
            $ignoreArgs = [];

            try {
                // Skip views by default to prevent dump failure on invalid/stale definers.
                $views = DB::table('information_schema.tables')
                    ->where('table_schema', $database)
                    ->where('table_type', 'VIEW')
                    ->pluck('table_name')
                    ->all();

                foreach ($views as $viewName) {
                    $ignoreArgs[] = "--ignore-table={$database}.{$viewName}";
                }

                if (count($views) > 0) {
                    $this->warn('Views akan di-skip dari backup: '.implode(', ', $views));
                }
            } catch (\Throwable $e) {
                $this->warn('Gagal membaca daftar view, lanjut backup tanpa skip view: '.$e->getMessage());
            }

            $process = new Process([
                $dumpBinary,
                "--host={$host}",
                "--port={$port}",
                "--user={$username}",
                "--password={$password}",
                '--single-transaction',
                '--quick',
                '--force',
                $database,
                ...$ignoreArgs,
            ]);
            $process->setTimeout(120);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->error('Backup failed: '.$process->getErrorOutput());
                return self::FAILURE;
            }

            File::put($backupPath, $process->getOutput());
            $this->info("MySQL backup created: {$backupPath}");
        }

        $retentionDays = (int) ($this->option('retention-days') ?: env('BACKUP_RETENTION_DAYS', 14));
        $deleted = 0;

        foreach (File::files($path) as $file) {
            if ($file->getMTime() < now()->subDays($retentionDays)->getTimestamp()) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        $this->info("Pruned old backups: {$deleted}");

        return self::SUCCESS;
    }

    private function resolveDumpBinary(): ?string
    {
        foreach (['mariadb-dump', 'mysqldump'] as $binary) {
            $probe = new Process(['which', $binary]);
            $probe->run();
            if ($probe->isSuccessful()) {
                return trim($probe->getOutput()) ?: $binary;
            }
        }

        return null;
    }
}
