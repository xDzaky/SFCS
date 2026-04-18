<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaduans', function (Blueprint $table): void {
            $table->enum('requested_prioritas', ['rendah', 'sedang', 'tinggi', 'urgent'])
                ->nullable()
                ->after('prioritas');
            $table->unsignedInteger('priority_score')->default(0)->after('requested_prioritas');
            $table->foreignId('prioritas_adjusted_by')
                ->nullable()
                ->after('priority_score')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('prioritas_adjust_reason')->nullable()->after('prioritas_adjusted_by');
            $table->timestamp('prioritas_adjusted_at')->nullable()->after('prioritas_adjust_reason');
            $table->boolean('needs_priority_review')->default(false)->after('prioritas_adjusted_at');

            $table->boolean('impact_safety_risk')->default(false)->after('needs_priority_review');
            $table->boolean('impact_learning_blocked')->default(false)->after('impact_safety_risk');
            $table->boolean('impact_exam_related')->default(false)->after('impact_learning_blocked');
            $table->enum('impact_area_scope', ['1_kelas', '1_lantai', '1_gedung'])->default('1_kelas')->after('impact_exam_related');
            $table->enum('impact_utilities', ['listrik', 'air', 'internet', 'none'])->default('none')->after('impact_area_scope');

            $table->index('needs_priority_review');
            $table->index('requested_prioritas');
        });
    }

    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table): void {
            $table->dropIndex(['needs_priority_review']);
            $table->dropIndex(['requested_prioritas']);
            $table->dropConstrainedForeignId('prioritas_adjusted_by');

            $table->dropColumn([
                'requested_prioritas',
                'priority_score',
                'prioritas_adjust_reason',
                'prioritas_adjusted_at',
                'needs_priority_review',
                'impact_safety_risk',
                'impact_learning_blocked',
                'impact_exam_related',
                'impact_area_scope',
                'impact_utilities',
            ]);
        });
    }
};
