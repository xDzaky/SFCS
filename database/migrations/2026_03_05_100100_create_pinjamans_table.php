<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pinjamans', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_pinjaman')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barangs')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->dateTime('tgl_pinjam');
            $table->dateTime('tgl_jatuh_tempo');
            $table->dateTime('tgl_kembali')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'dipinjam', 'terlambat', 'selesai', 'ditolak'])->default('pending');
            $table->text('alasan')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamp('marked_late_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'tgl_jatuh_tempo']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjamans');
    }
};
