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
        Schema::table('pengaduans', function (Blueprint $table) {
            if (!Schema::hasColumn('pengaduans', 'auto_closed_by_duplicate')) {
                $table->boolean('auto_closed_by_duplicate')
                    ->default(false)
                    ->after('duplicate_note');
            }

            if (!Schema::hasColumn('pengaduans', 'auto_closed_from_master_id')) {
                $table->foreignId('auto_closed_from_master_id')
                    ->nullable()
                    ->after('auto_closed_by_duplicate')
                    ->constrained('pengaduans')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('pengaduans', 'auto_closed_at')) {
                $table->timestamp('auto_closed_at')->nullable()->after('auto_closed_from_master_id');
            }

            $table->index('auto_closed_by_duplicate');
            $table->index(['duplicate_of_id', 'status'], 'pengaduans_duplicate_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->dropIndex(['auto_closed_by_duplicate']);
            $table->dropIndex('pengaduans_duplicate_status_idx');
            $table->dropConstrainedForeignId('auto_closed_from_master_id');
            $table->dropColumn(['auto_closed_by_duplicate', 'auto_closed_at']);
        });
    }
};
