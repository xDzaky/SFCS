<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppDoctorCommand extends Command
{
    protected $signature = 'app:doctor';
    protected $description = 'Cek kesiapan environment, database, dan schema penting SFCS.';

    public function handle(): int
    {
        $hasError = false;

        $this->components->info('Pemeriksaan environment SFCS');

        $this->line('APP_ENV: '.config('app.env'));
        $this->line('DB_CONNECTION: '.config('database.default'));
        $this->line('DB_HOST: '.(config('database.connections.'.config('database.default').'.host') ?? '-'));
        $this->line('DB_DATABASE: '.(config('database.connections.'.config('database.default').'.database') ?? '-'));
        $this->line('SESSION_DRIVER: '.config('session.driver'));
        $this->line('CACHE_STORE: '.config('cache.default'));
        $this->line('QUEUE_CONNECTION: '.config('queue.default'));
        $this->newLine();

        try {
            DB::connection()->getPdo();
            $this->components->twoColumnDetail('Koneksi database', 'OK');
        } catch (\Throwable $e) {
            $this->components->twoColumnDetail('Koneksi database', 'GAGAL');
            $this->error('Database tidak bisa diakses: '.$e->getMessage());
            $this->warn('Periksa .env dan pastikan gunakan user database aplikasi, bukan root Linux/auth_socket.');
            return self::FAILURE;
        }

        $requiredTables = [
            'users',
            'sessions',
            'cache',
            'jobs',
            'pengaduans',
            'notifications',
            'barangs',
            'pinjamans',
            'settings',
            'school_maps',
            'school_map_layers',
            'school_map_areas',
            'pengaduan_schedules',
        ];

        foreach ($requiredTables as $table) {
            $exists = Schema::hasTable($table);
            $this->components->twoColumnDetail("Tabel {$table}", $exists ? 'OK' : 'BELUM ADA');
            $hasError = $hasError || !$exists;
        }

        if (Schema::hasTable('pengaduans')) {
            foreach ([
                'requested_prioritas',
                'priority_score',
                'needs_priority_review',
                'triage_score',
                'triage_bucket',
                'queue_rank',
                'planned_start_at',
                'planned_end_at',
                'delay_minutes',
                'is_overload_delayed',
                'load_snapshot',
                'school_map_id',
                'school_map_layer_id',
                'map_point_x',
                'map_point_y',
            ] as $column) {
                $exists = Schema::hasColumn('pengaduans', $column);
                $this->components->twoColumnDetail("Kolom pengaduans.{$column}", $exists ? 'OK' : 'BELUM ADA');
                $hasError = $hasError || !$exists;
            }
        }

        if (Schema::hasTable('users')) {
            $defaultAccounts = [
                'superadmin@sfcs.sch.id',
                'admin@sfcs.sch.id',
                'kepsek@sfcs.sch.id',
            ];

            foreach ($defaultAccounts as $email) {
                $exists = DB::table('users')->where('email', $email)->exists();
                $this->components->twoColumnDetail("Akun {$email}", $exists ? 'ADA' : 'TIDAK ADA');
                $hasError = $hasError || !$exists;
            }
        }

        $this->newLine();

        if ($hasError) {
            $this->warn('Masih ada item setup yang belum siap. Jalankan migrate/seed atau perbaiki kredensial database.');
            return self::FAILURE;
        }

        $this->components->info('Semua pemeriksaan inti lolos. SFCS siap diuji lebih lanjut.');
        return self::SUCCESS;
    }
}
