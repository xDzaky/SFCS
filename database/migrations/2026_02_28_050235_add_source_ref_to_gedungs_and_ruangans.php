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
        Schema::table('gedungs', function (Blueprint $table): void {
            $table->string('source_ref', 50)->nullable()->after('kode');
            $table->index('source_ref');
        });

        Schema::table('ruangans', function (Blueprint $table): void {
            $table->string('source_ref', 50)->nullable()->after('kode');
            $table->index('source_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruangans', function (Blueprint $table): void {
            $table->dropIndex(['source_ref']);
            $table->dropColumn('source_ref');
        });

        Schema::table('gedungs', function (Blueprint $table): void {
            $table->dropIndex(['source_ref']);
            $table->dropColumn('source_ref');
        });
    }
};
