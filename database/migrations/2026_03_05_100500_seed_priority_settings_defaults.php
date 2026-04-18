<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['key' => 'priority_threshold_urgent', 'value' => '80', 'group' => 'sla', 'type' => 'number', 'description' => 'Ambang skor untuk prioritas urgent'],
            ['key' => 'priority_threshold_tinggi', 'value' => '50', 'group' => 'sla', 'type' => 'number', 'description' => 'Ambang skor untuk prioritas tinggi'],
            ['key' => 'priority_threshold_sedang', 'value' => '25', 'group' => 'sla', 'type' => 'number', 'description' => 'Ambang skor untuk prioritas sedang'],
            ['key' => 'priority_score_safety', 'value' => '50', 'group' => 'sla', 'type' => 'number', 'description' => 'Skor tambahan untuk risiko keselamatan'],
            ['key' => 'priority_score_learning_blocked', 'value' => '25', 'group' => 'sla', 'type' => 'number', 'description' => 'Skor tambahan untuk belajar terhambat'],
            ['key' => 'priority_score_exam_related', 'value' => '30', 'group' => 'sla', 'type' => 'number', 'description' => 'Skor tambahan untuk dampak ujian'],
            ['key' => 'priority_score_scope_lantai', 'value' => '15', 'group' => 'sla', 'type' => 'number', 'description' => 'Skor tambahan skala dampak 1 lantai'],
            ['key' => 'priority_score_scope_gedung', 'value' => '30', 'group' => 'sla', 'type' => 'number', 'description' => 'Skor tambahan skala dampak 1 gedung'],
            ['key' => 'priority_score_utilities_critical', 'value' => '20', 'group' => 'sla', 'type' => 'number', 'description' => 'Skor tambahan utilitas kritikal'],
        ];

        foreach ($defaults as $row) {
            DB::table('settings')->updateOrInsert(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'group' => $row['group'],
                    'type' => $row['type'],
                    'description' => $row['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'priority_threshold_urgent',
            'priority_threshold_tinggi',
            'priority_threshold_sedang',
            'priority_score_safety',
            'priority_score_learning_blocked',
            'priority_score_exam_related',
            'priority_score_scope_lantai',
            'priority_score_scope_gedung',
            'priority_score_utilities_critical',
        ])->delete();
    }
};
