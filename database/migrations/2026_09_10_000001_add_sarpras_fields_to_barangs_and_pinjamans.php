<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table): void {
            $table->enum('unit_sarpras', ['atas', 'bawah'])->default('atas')->after('lokasi');
            $table->enum('tipe_transaksi', ['pinjam', 'minta'])->default('pinjam')->after('unit_sarpras');
        });

        Schema::table('pinjamans', function (Blueprint $table): void {
            $table->enum('tipe', ['pinjam', 'minta'])->default('pinjam')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table): void {
            $table->dropColumn(['unit_sarpras', 'tipe_transaksi']);
        });

        Schema::table('pinjamans', function (Blueprint $table): void {
            $table->dropColumn('tipe');
        });
    }
};
