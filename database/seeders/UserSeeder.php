<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@sfcs.sch.id'],
            [
                'name' => 'Super Admin',
                'password' => $defaultPassword,
                'role' => 'superadmin',
                'nip' => 'SA001',
                'is_active' => true,
                'force_password_change' => false,
                'email_verified_at' => now(),
            ]
        );

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@sfcs.sch.id'],
            [
                'name' => 'Admin Sarana',
                'password' => $defaultPassword,
                'role' => 'admin',
                'nip' => 'ADM001',
                'is_active' => true,
                'force_password_change' => false,
                'email_verified_at' => now(),
            ]
        );

        // Kepala Sekolah
        User::firstOrCreate(
            ['email' => 'kepsek@sfcs.sch.id'],
            [
                'name' => 'Dr. Budi Santoso, M.Pd',
                'password' => $defaultPassword,
                'role' => 'kepsek',
                'nip' => 'KS001',
                'is_active' => true,
                'force_password_change' => false,
                'email_verified_at' => now(),
            ]
        );

        // Teknisi
        $teknisis = [
            ['name' => 'Ahmad Teknisi', 'email' => 'teknisi1@sfcs.sch.id', 'nip' => 'TK001'],
            ['name' => 'Budi Teknisi', 'email' => 'teknisi2@sfcs.sch.id', 'nip' => 'TK002'],
            ['name' => 'Cahyo Teknisi', 'email' => 'teknisi3@sfcs.sch.id', 'nip' => 'TK003'],
        ];

        foreach ($teknisis as $teknisi) {
            User::firstOrCreate(
                ['email' => $teknisi['email']],
                [
                    'name' => $teknisi['name'],
                    'password' => $defaultPassword,
                    'role' => 'teknisi',
                    'nip' => $teknisi['nip'],
                    'is_active' => true,
                    'force_password_change' => false,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Guru
        $gurus = [
            ['name' => 'Ibu Sri Wahyuni, S.Pd', 'email' => 'sri.wahyuni@sfcs.sch.id', 'nip' => 'GR001'],
            ['name' => 'Bapak Joko Susilo, M.Pd', 'email' => 'joko.susilo@sfcs.sch.id', 'nip' => 'GR002'],
        ];

        foreach ($gurus as $guru) {
            User::firstOrCreate(
                ['email' => $guru['email']],
                [
                    'name' => $guru['name'],
                    'password' => $defaultPassword,
                    'role' => 'guru',
                    'nip' => $guru['nip'],
                    'is_active' => true,
                    'force_password_change' => false,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Siswa
        $siswas = [
            ['name' => 'Andi Pratama', 'email' => 'andi@sfcs.sch.id', 'nis' => 'SW001'],
            ['name' => 'Bela Safitri', 'email' => 'bela@sfcs.sch.id', 'nis' => 'SW002'],
            ['name' => 'Citra Dewi', 'email' => 'citra@sfcs.sch.id', 'nis' => 'SW003'],
            ['name' => 'Dani Setiawan', 'email' => 'dani@sfcs.sch.id', 'nis' => 'SW004'],
            ['name' => 'Eka Putri', 'email' => 'eka@sfcs.sch.id', 'nis' => 'SW005'],
        ];

        foreach ($siswas as $siswa) {
            User::firstOrCreate(
                ['email' => $siswa['email']],
                [
                    'name' => $siswa['name'],
                    'password' => $defaultPassword,
                    'role' => 'siswa',
                    'nis' => $siswa['nis'],
                    'is_active' => true,
                    'force_password_change' => false,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
