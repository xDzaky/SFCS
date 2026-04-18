<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_barang')->unique();
            $table->string('nama');
            $table->string('kategori')->nullable();
            $table->string('lokasi')->nullable();
            $table->unsignedInteger('stok_total')->default(0);
            $table->unsignedInteger('stok_tersedia')->default(0);
            $table->unsignedInteger('stok_rusak')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
