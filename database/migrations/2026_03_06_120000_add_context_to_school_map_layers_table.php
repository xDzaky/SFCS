<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_map_layers', function (Blueprint $table) {
            $table->foreignId('gedung_id')->nullable()->after('school_map_id')->constrained('gedungs')->nullOnDelete();
            $table->string('lantai_label', 50)->nullable()->after('label');
            $table->enum('layer_scope', ['general', 'gedung_lantai'])->default('general')->after('lantai_label');
        });
    }

    public function down(): void
    {
        Schema::table('school_map_layers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gedung_id');
            $table->dropColumn(['lantai_label', 'layer_scope']);
        });
    }
};
