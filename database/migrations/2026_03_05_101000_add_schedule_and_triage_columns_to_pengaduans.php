<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaduans', function (Blueprint $table): void {
            $table->timestamp('planned_start_at')->nullable()->after('assigned_at');
            $table->timestamp('planned_end_at')->nullable()->after('planned_start_at');
            $table->unsignedSmallInteger('reschedule_count')->default(0)->after('planned_end_at');
            $table->text('last_reschedule_reason')->nullable()->after('reschedule_count');
            $table->foreignId('last_rescheduled_by')->nullable()->after('last_reschedule_reason')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('last_rescheduled_at')->nullable()->after('last_rescheduled_by');
            $table->boolean('is_overload_delayed')->default(false)->after('last_rescheduled_at');
            $table->unsignedInteger('delay_minutes')->default(0)->after('is_overload_delayed');
            $table->unsignedInteger('triage_score')->default(0)->after('delay_minutes');
            $table->unsignedInteger('queue_rank')->nullable()->after('triage_score');
            $table->enum('triage_bucket', ['critical', 'high', 'normal'])->default('normal')->after('queue_rank');
            $table->json('load_snapshot')->nullable()->after('triage_bucket');

            $table->index(['teknisi_id', 'queue_rank']);
            $table->index(['is_overload_delayed', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table): void {
            $table->dropIndex(['teknisi_id', 'queue_rank']);
            $table->dropIndex(['is_overload_delayed', 'status']);
            $table->dropConstrainedForeignId('last_rescheduled_by');
            $table->dropColumn([
                'planned_start_at',
                'planned_end_at',
                'reschedule_count',
                'last_reschedule_reason',
                'last_rescheduled_at',
                'is_overload_delayed',
                'delay_minutes',
                'triage_score',
                'queue_rank',
                'triage_bucket',
                'load_snapshot',
            ]);
        });
    }
};

