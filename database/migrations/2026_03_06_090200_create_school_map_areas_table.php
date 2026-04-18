<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_map_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_map_layer_id')->constrained('school_map_layers')->cascadeOnDelete();
            $table->foreignId('gedung_id')->nullable()->constrained('gedungs')->nullOnDelete();
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();
            $table->string('label');
            $table->enum('shape_type', ['marker', 'polygon', 'rect']);
            $table->json('geometry_json');
            $table->string('color', 20)->default('#2563eb');
            $table->string('icon', 50)->nullable();
            $table->boolean('is_clickable')->default(true);
            $table->timestamps();

            $table->index(['school_map_layer_id', 'ruangan_id']);
            $table->index(['school_map_layer_id', 'gedung_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_map_areas');
    }
};
