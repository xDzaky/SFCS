<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ubah kolom role dari ENUM ke VARCHAR(30) agar mendukung role baru:
     * - superadmin → admin (IT Admin / Guru IT)
     * - admin      → sarpras_atas (Sarpras Atas: Aset & Fasilitas)
     * - teknisi    → sarpras_atas (digabung ke sarpras_atas)
     * - NEW        → sarpras_bawah (Sarpras Bawah: ATK & Logistik)
     */
    public function up(): void
    {
        // Step 1: Ubah kolom role dari ENUM ke VARCHAR(30) (khusus MySQL/MariaDB)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(30) NOT NULL DEFAULT 'siswa'");
        }

        // Step 2: Migrasi data role yang lama ke role baru
        // superadmin → admin (IT Admin dengan full access)
        DB::table('users')->where('role', 'superadmin')->update(['role' => 'admin']);

        // admin (lama) → sarpras_atas
        DB::table('users')->where('role', 'admin')->update(['role' => 'sarpras_atas']);

        // teknisi → sarpras_atas (digabung)
        DB::table('users')->where('role', 'teknisi')->update(['role' => 'sarpras_atas']);
    }

    public function down(): void
    {
        // Kembalikan ke enum dan role lama
        DB::table('users')->where('role', 'sarpras_atas')->update(['role' => 'teknisi']);
        DB::table('users')->where('role', 'sarpras_bawah')->update(['role' => 'admin']);

        DB::table('users')->where('email', 'admin@sfcs.sch.id')->update(['role' => 'superadmin']);

        // Step 3: Kembalikan ke ENUM (khusus MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('siswa','guru','teknisi','admin','kepsek','superadmin') NOT NULL DEFAULT 'siswa'");
        }
    }
};
