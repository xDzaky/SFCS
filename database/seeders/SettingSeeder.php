<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Informasi Sekolah
            [
                'key' => 'school_name',
                'value' => 'SMA Negeri 1 Contoh',
                'group' => 'general',
                'type' => 'text',
                'description' => 'Nama sekolah',
            ],
            [
                'key' => 'school_address',
                'value' => 'Jl. Pendidikan No. 1, Kota Contoh, Jawa Barat 12345',
                'group' => 'general',
                'type' => 'textarea',
                'description' => 'Alamat sekolah',
            ],
            [
                'key' => 'school_phone',
                'value' => '(021) 1234567',
                'group' => 'general',
                'type' => 'text',
                'description' => 'Nomor telepon sekolah',
            ],
            [
                'key' => 'school_email',
                'value' => 'info@sma1contoh.sch.id',
                'group' => 'general',
                'type' => 'email',
                'description' => 'Email sekolah',
            ],
            [
                'key' => 'school_logo',
                'value' => null,
                'group' => 'general',
                'type' => 'image',
                'description' => 'Logo sekolah',
            ],

            // Pengaturan Notifikasi
            [
                'key' => 'notification_email_enabled',
                'value' => 'true',
                'group' => 'notification',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi email',
            ],
            [
                'key' => 'notification_admin_email',
                'value' => 'admin@sma1contoh.sch.id',
                'group' => 'notification',
                'type' => 'email',
                'description' => 'Email admin untuk notifikasi',
            ],

            // Pengaturan Pengaduan
            [
                'key' => 'pengaduan_max_photos',
                'value' => '5',
                'group' => 'pengaduan',
                'type' => 'number',
                'description' => 'Maksimal foto per pengaduan',
            ],
            [
                'key' => 'pengaduan_max_file_size',
                'value' => '5120',
                'group' => 'pengaduan',
                'type' => 'number',
                'description' => 'Maksimal ukuran file (KB)',
            ],
            [
                'key' => 'pengaduan_allowed_extensions',
                'value' => 'jpg,jpeg,png,gif,webp',
                'group' => 'pengaduan',
                'type' => 'text',
                'description' => 'Ekstensi file yang diizinkan (pisahkan dengan koma)',
            ],
            [
                'key' => 'pengaduan_auto_assign',
                'value' => 'false',
                'group' => 'pengaduan',
                'type' => 'boolean',
                'description' => 'Otomatis assign teknisi berdasarkan kategori',
            ],

            // Pengaturan Feedback
            [
                'key' => 'feedback_required',
                'value' => 'true',
                'group' => 'feedback',
                'type' => 'boolean',
                'description' => 'Wajib memberikan feedback setelah selesai',
            ],
            [
                'key' => 'feedback_deadline_days',
                'value' => '7',
                'group' => 'feedback',
                'type' => 'number',
                'description' => 'Batas waktu feedback (hari setelah selesai)',
            ],

            // Pengaturan SLA (Service Level Agreement)
            [
                'key' => 'sla_response_time',
                'value' => '24',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Target waktu respons (jam)',
            ],
            [
                'key' => 'sla_resolution_time',
                'value' => '72',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Target waktu penyelesaian (jam)',
            ],
            [
                'key' => 'sla_urgent_multiplier',
                'value' => '0.5',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Pengali untuk kasus urgent (contoh: 0.5 = setengah dari waktu normal)',
            ],
            [
                'key' => 'sla_darurat_minutes',
                'value' => '240',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Batas SLA tiket urgent/darurat (menit)',
            ],
            [
                'key' => 'sla_tinggi_hours',
                'value' => '24',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Batas SLA tiket tinggi (jam)',
            ],
            [
                'key' => 'sla_sedang_hours',
                'value' => '72',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Batas SLA tiket sedang (jam)',
            ],
            [
                'key' => 'sla_rendah_hours',
                'value' => '168',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Batas SLA tiket rendah (jam)',
            ],
            [
                'key' => 'escalation_warning_minutes_before_due',
                'value' => '60',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Jendela peringatan sebelum SLA jatuh tempo (menit)',
            ],
            [
                'key' => 'priority_threshold_urgent',
                'value' => '80',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Ambang skor untuk prioritas urgent',
            ],
            [
                'key' => 'priority_threshold_tinggi',
                'value' => '50',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Ambang skor untuk prioritas tinggi',
            ],
            [
                'key' => 'priority_threshold_sedang',
                'value' => '25',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Ambang skor untuk prioritas sedang',
            ],
            [
                'key' => 'priority_score_safety',
                'value' => '50',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Skor tambahan untuk risiko keselamatan',
            ],
            [
                'key' => 'priority_score_learning_blocked',
                'value' => '25',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Skor tambahan untuk dampak belajar terhenti',
            ],
            [
                'key' => 'priority_score_exam_related',
                'value' => '30',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Skor tambahan untuk dampak ujian',
            ],
            [
                'key' => 'priority_score_scope_lantai',
                'value' => '15',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Skor tambahan untuk dampak 1 lantai',
            ],
            [
                'key' => 'priority_score_scope_gedung',
                'value' => '30',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Skor tambahan untuk dampak 1 gedung',
            ],
            [
                'key' => 'priority_score_utilities_critical',
                'value' => '20',
                'group' => 'sla',
                'type' => 'number',
                'description' => 'Skor tambahan utilitas kritikal (listrik/air)',
            ],

            // Pengaturan Maintenance
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Mode pemeliharaan sistem',
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
                'group' => 'system',
                'type' => 'textarea',
                'description' => 'Pesan saat mode pemeliharaan aktif',
            ],

            // Pengaturan Tampilan
            [
                'key' => 'theme_primary_color',
                'value' => '#0d6efd',
                'group' => 'appearance',
                'type' => 'color',
                'description' => 'Warna utama tema',
            ],
            [
                'key' => 'theme_secondary_color',
                'value' => '#6c757d',
                'group' => 'appearance',
                'type' => 'color',
                'description' => 'Warna sekunder tema',
            ],
            [
                'key' => 'footer_text',
                'value' => '© 2024 SFCS - Sistem Pengaduan Fasilitas Sekolah. All rights reserved.',
                'group' => 'appearance',
                'type' => 'text',
                'description' => 'Teks footer',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
