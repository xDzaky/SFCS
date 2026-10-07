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

        // Admin IT (full system access)
        User::firstOrCreate(
            ['email' => 'admin@sfcs.sch.id'],
            [
                'name' => 'Admin IT',
                'password' => $defaultPassword,
                'role' => 'admin',
                'nip' => 'ADM001',
                'is_active' => true,
                'force_password_change' => false,
                'email_verified_at' => now(),
            ]
        );

        // Sarpras Atas (Fasilitas & Peminjaman Aset)
        User::firstOrCreate(
            ['email' => 'sarpras.atas@sfcs.sch.id'],
            [
                'name' => 'Sarpras Atas',
                'password' => $defaultPassword,
                'role' => 'sarpras_atas',
                'nip' => 'SPA001',
                'is_active' => true,
                'force_password_change' => false,
                'email_verified_at' => now(),
            ]
        );

        // Sarpras Bawah (ATK & Logistik)
        User::firstOrCreate(
            ['email' => 'sarpras.bawah@sfcs.sch.id'],
            [
                'name' => 'Sarpras Bawah',
                'password' => $defaultPassword,
                'role' => 'sarpras_bawah',
                'nip' => 'SPB001',
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
