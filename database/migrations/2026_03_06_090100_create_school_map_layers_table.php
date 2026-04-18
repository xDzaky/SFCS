<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_map_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_map_id')->constrained('school_maps')->cascadeOnDelete();
            $table->unsignedInteger('page_number')->default(1);
            $table->string('label');
            $table->unsignedInteger('width')->default(842);
            $table->unsignedInteger('height')->default(595);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_map_layers');
    }
};
