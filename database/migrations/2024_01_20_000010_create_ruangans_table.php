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
        Schema::create('ruangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gedung_id')->constrained('gedungs')->onDelete('cascade');
            $table->string('lantai', 10);
            $table->string('nama', 50);
            $table->string('kode', 20)->unique()->nullable();
            $table->integer('kapasitas')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('gedung_id');
            $table->index('lantai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruangans');
    }
};
