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
            $table->timestamp('sla_due_at')->nullable()->after('completed_at');
            $table->timestamp('first_response_at')->nullable()->after('sla_due_at');
            $table->unsignedInteger('reopen_count')->default(0)->after('first_response_at');
            $table->timestamp('reopen_requested_at')->nullable()->after('reopen_count');
            $table->foreignId('reopen_requested_by')->nullable()->after('reopen_requested_at')
                ->constrained('users')->nullOnDelete();
            $table->text('reopen_reason')->nullable()->after('reopen_requested_by');

            $table->index('sla_due_at');
            $table->index('reopen_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->dropIndex(['sla_due_at']);
            $table->dropIndex(['reopen_requested_at']);
            $table->dropConstrainedForeignId('reopen_requested_by');
            $table->dropColumn([
                'sla_due_at',
                'first_response_at',
                'reopen_count',
                'reopen_requested_at',
                'reopen_reason',
            ]);
        });
    }
};
