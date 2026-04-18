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
            if (!Schema::hasColumn('pengaduans', 'duplicate_of_id')) {
                $table->foreignId('duplicate_of_id')
                    ->nullable()
                    ->after('completed_at')
                    ->constrained('pengaduans')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('pengaduans', 'duplicate_marked_by')) {
                $table->foreignId('duplicate_marked_by')
                    ->nullable()
                    ->after('duplicate_of_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('pengaduans', 'duplicate_marked_at')) {
                $table->timestamp('duplicate_marked_at')->nullable()->after('duplicate_marked_by');
            }

            if (!Schema::hasColumn('pengaduans', 'duplicate_note')) {
                $table->text('duplicate_note')->nullable()->after('duplicate_marked_at');
            }

            $table->index('duplicate_of_id');
            $table->index(['status', 'duplicate_of_id', 'created_at'], 'pengaduans_status_duplicate_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->dropIndex('pengaduans_status_duplicate_created_idx');
            $table->dropIndex(['duplicate_of_id']);
            $table->dropConstrainedForeignId('duplicate_marked_by');
            $table->dropConstrainedForeignId('duplicate_of_id');
            $table->dropColumn(['duplicate_marked_at', 'duplicate_note']);
        });
    }
};
