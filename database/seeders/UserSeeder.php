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
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@sfcs.sch.id',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'nip' => 'SA001',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Admin
        User::create([
            'name' => 'Admin Sarana',
            'email' => 'admin@sfcs.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'nip' => 'ADM001',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Kepala Sekolah
        User::create([
            'name' => 'Dr. Budi Santoso, M.Pd',
            'email' => 'kepsek@sfcs.sch.id',
            'password' => Hash::make('password'),
            'role' => 'kepsek',
            'nip' => 'KS001',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Teknisi
        $teknisis = [
            ['name' => 'Ahmad Teknisi', 'email' => 'teknisi1@sfcs.sch.id', 'nip' => 'TK001'],
            ['name' => 'Budi Teknisi', 'email' => 'teknisi2@sfcs.sch.id', 'nip' => 'TK002'],
            ['name' => 'Cahyo Teknisi', 'email' => 'teknisi3@sfcs.sch.id', 'nip' => 'TK003'],
        ];

        foreach ($teknisis as $teknisi) {
            User::create([
                'name' => $teknisi['name'],
                'email' => $teknisi['email'],
                'password' => Hash::make('password'),
                'role' => 'teknisi',
                'nip' => $teknisi['nip'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        // Guru
        $gurus = [
            ['name' => 'Ibu Sri Wahyuni, S.Pd', 'email' => 'sri.wahyuni@sfcs.sch.id', 'nip' => 'GR001'],
            ['name' => 'Bapak Joko Susilo, M.Pd', 'email' => 'joko.susilo@sfcs.sch.id', 'nip' => 'GR002'],
        ];

        foreach ($gurus as $guru) {
            User::create([
                'name' => $guru['name'],
                'email' => $guru['email'],
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nip' => $guru['nip'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
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
            User::create([
                'name' => $siswa['name'],
                'email' => $siswa['email'],
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nis' => $siswa['nis'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
