<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->foreignId('school_map_id')->nullable()->after('ruangan_id')->constrained('school_maps')->nullOnDelete();
            $table->foreignId('school_map_layer_id')->nullable()->after('school_map_id')->constrained('school_map_layers')->nullOnDelete();
            $table->decimal('map_point_x', 8, 6)->nullable()->after('school_map_layer_id');
            $table->decimal('map_point_y', 8, 6)->nullable()->after('map_point_x');
            $table->unsignedSmallInteger('map_zoom')->nullable()->after('map_point_y');
            $table->enum('map_source', ['manual_point', 'fallback_text'])->nullable()->after('map_zoom');
        });
    }

    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_map_id');
            $table->dropConstrainedForeignId('school_map_layer_id');
            $table->dropColumn(['map_point_x', 'map_point_y', 'map_zoom', 'map_source']);
        });
    }
};
