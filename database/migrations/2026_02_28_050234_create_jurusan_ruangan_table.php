<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jurusan_ruangan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('jurusan_id')->constrained('jurusans')->cascadeOnDelete();
            $table->foreignId('ruangan_id')->constrained('ruangans')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['jurusan_id', 'ruangan_id']);
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurusan_ruangan');
    }
};
