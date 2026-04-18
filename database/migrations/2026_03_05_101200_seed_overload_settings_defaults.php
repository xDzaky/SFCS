<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['key' => 'teknisi_capacity_per_hour', 'value' => '2', 'group' => 'sla', 'type' => 'number', 'description' => 'Kapasitas penanganan tiket per teknisi per jam'],
            ['key' => 'overload_delay_threshold_minutes', 'value' => '60', 'group' => 'sla', 'type' => 'number', 'description' => 'Ambang delay menit untuk menandai overload'],
            ['key' => 'urgent_max_reschedule_hours', 'value' => '24', 'group' => 'sla', 'type' => 'number', 'description' => 'Maksimal mundur jadwal untuk tiket urgent (jam)'],
            ['key' => 'high_max_reschedule_hours', 'value' => '48', 'group' => 'sla', 'type' => 'number', 'description' => 'Maksimal mundur jadwal untuk tiket tinggi (jam)'],
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
            'teknisi_capacity_per_hour',
            'overload_delay_threshold_minutes',
            'urgent_max_reschedule_hours',
            'high_max_reschedule_hours',
        ])->delete();
    }
};

