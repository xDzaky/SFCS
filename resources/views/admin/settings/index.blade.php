@extends('layouts.sfcs')

@section('title', 'Pengaturan Sistem')

@push('styles')
<style>
    /* Mobile horizontal nav-pills scroll */
    @media (max-width: 991.98px) {
        #settings-tab {
            flex-direction: row !important;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: .375rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
            gap: .25rem;
        }
        #settings-tab::-webkit-scrollbar { display: none; }
        #settings-tab .nav-link {
            white-space: nowrap;
            padding: .5rem .875rem;
            font-size: .8125rem;
            border-radius: 2rem !important;
        }
    }
    @media (min-width: 992px) {
        #settings-tab .nav-link {
            border-radius: .5rem;
            margin-bottom: .25rem;
        }
        #settings-tab .nav-link.active {
            background: var(--primary-color);
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Pengaturan Sistem</h1>
    <p class="page-subtitle">Konfigurasi umum aplikasi SFCS</p>
</div>

<form action="{{ route('superadmin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row">
        <div class="col-lg-3 mb-4">
            <!-- Navigation Tabs -->
            <div class="nav flex-column nav-pills" id="settings-tab" role="tablist">
                <button class="nav-link active text-start" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button">
                    <i class="fas fa-school me-2"></i> Informasi Sekolah
                </button>
                <button class="nav-link text-start" id="notification-tab" data-bs-toggle="pill" data-bs-target="#notification" type="button">
                    <i class="fas fa-bell me-2"></i> Notifikasi
                </button>
                <button class="nav-link text-start" id="pengaduan-tab" data-bs-toggle="pill" data-bs-target="#pengaduan" type="button">
                    <i class="fas fa-clipboard-list me-2"></i> Pengaduan
                </button>
                <button class="nav-link text-start" id="feedback-tab" data-bs-toggle="pill" data-bs-target="#feedback" type="button">
                    <i class="fas fa-star me-2"></i> Feedback
                </button>
                <button class="nav-link text-start" id="sla-tab" data-bs-toggle="pill" data-bs-target="#sla" type="button">
                    <i class="fas fa-clock me-2"></i> SLA
                </button>
                <button class="nav-link text-start" id="appearance-tab" data-bs-toggle="pill" data-bs-target="#appearance" type="button">
                    <i class="fas fa-palette me-2"></i> Tampilan
                </button>
                <button class="nav-link text-start" id="system-tab" data-bs-toggle="pill" data-bs-target="#system" type="button">
                    <i class="fas fa-cog me-2"></i> Sistem
                </button>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="tab-content" id="settings-tabContent">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informasi Sekolah</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Sekolah</label>
                                <input type="text" name="settings[school_name]" class="form-control" value="{{ $settings['school_name'] ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="settings[school_address]" class="form-control" rows="2">{{ $settings['school_address'] ?? '' }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="settings[school_phone]" class="form-control" value="{{ $settings['school_phone'] ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="settings[school_email]" class="form-control" value="{{ $settings['school_email'] ?? '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Logo Sekolah</label>
                                @if(!empty($settings['school_logo']))
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($settings['school_logo']) }}" alt="Logo" height="60">
                                    </div>
                                @endif
                                <input type="file" name="school_logo" class="form-control" accept="image/*">
                                <div class="form-text">Format: PNG, JPG. Maks 2MB</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="tab-pane fade" id="notification" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pengaturan Notifikasi</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="settings[notification_email_enabled]" id="notificationEmail" value="true" {{ ($settings['notification_email_enabled'] ?? 'false') == 'true' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notificationEmail">Aktifkan Notifikasi Email</label>
                                </div>
                                <div class="form-text">Kirim email notifikasi saat ada update pengaduan</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Admin untuk Notifikasi</label>
                                <input type="email" name="settings[notification_admin_email]" class="form-control" value="{{ $settings['notification_admin_email'] ?? '' }}">
                                <div class="form-text">Email yang menerima notifikasi pengaduan baru</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengaduan Settings -->
                <div class="tab-pane fade" id="pengaduan" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pengaturan Pengaduan</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maksimal Foto per Pengaduan</label>
                                    <input type="number" name="settings[pengaduan_max_photos]" class="form-control" value="{{ $settings['pengaduan_max_photos'] ?? 5 }}" min="1" max="10">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maksimal Ukuran File (KB)</label>
                                    <input type="number" name="settings[pengaduan_max_file_size]" class="form-control" value="{{ $settings['pengaduan_max_file_size'] ?? 5120 }}" min="512">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ekstensi File yang Diizinkan</label>
                                <input type="text" name="settings[pengaduan_allowed_extensions]" class="form-control" value="{{ $settings['pengaduan_allowed_extensions'] ?? 'jpg,jpeg,png,gif,webp' }}">
                                <div class="form-text">Pisahkan dengan koma, tanpa spasi</div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="settings[pengaduan_auto_assign]" id="autoAssign" value="true" {{ ($settings['pengaduan_auto_assign'] ?? 'false') == 'true' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="autoAssign">Auto-Assign Teknisi</label>
                                </div>
                                <div class="form-text">Otomatis menugaskan teknisi berdasarkan kategori dan ketersediaan</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Settings -->
                <div class="tab-pane fade" id="feedback" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pengaturan Feedback</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="settings[feedback_required]" id="feedbackRequired" value="true" {{ ($settings['feedback_required'] ?? 'false') == 'true' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feedbackRequired">Wajib Memberikan Feedback</label>
                                </div>
                                <div class="form-text">Pelapor wajib memberikan feedback setelah pengaduan selesai</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Batas Waktu Feedback (Hari)</label>
                                <input type="number" name="settings[feedback_deadline_days]" class="form-control" value="{{ $settings['feedback_deadline_days'] ?? 7 }}" min="1" max="30">
                                <div class="form-text">Berapa hari setelah selesai untuk memberikan feedback</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLA Settings -->
                <div class="tab-pane fade" id="sla" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Service Level Agreement (SLA)</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SLA Darurat (Menit)</label>
                                    <input type="number" name="settings[sla_darurat_minutes]" class="form-control" value="{{ $settings['sla_darurat_minutes'] ?? 240 }}" min="30">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SLA Tinggi (Jam)</label>
                                    <input type="number" name="settings[sla_tinggi_hours]" class="form-control" value="{{ $settings['sla_tinggi_hours'] ?? 24 }}" min="1">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SLA Sedang (Jam)</label>
                                    <input type="number" name="settings[sla_sedang_hours]" class="form-control" value="{{ $settings['sla_sedang_hours'] ?? 72 }}" min="1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SLA Rendah (Jam)</label>
                                    <input type="number" name="settings[sla_rendah_hours]" class="form-control" value="{{ $settings['sla_rendah_hours'] ?? 168 }}" min="1">
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Peringatan Eskalasi Sebelum Jatuh Tempo (Menit)</label>
                                <input type="number" name="settings[escalation_warning_minutes_before_due]" class="form-control" value="{{ $settings['escalation_warning_minutes_before_due'] ?? 60 }}" min="5">
                            </div>
                            <hr>
                            <h6 class="mt-3">Konfigurasi Scoring Urgensi</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Threshold Urgent</label>
                                    <input type="number" name="settings[priority_threshold_urgent]" class="form-control" value="{{ $settings['priority_threshold_urgent'] ?? 80 }}" min="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Threshold Tinggi</label>
                                    <input type="number" name="settings[priority_threshold_tinggi]" class="form-control" value="{{ $settings['priority_threshold_tinggi'] ?? 50 }}" min="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Threshold Sedang</label>
                                    <input type="number" name="settings[priority_threshold_sedang]" class="form-control" value="{{ $settings['priority_threshold_sedang'] ?? 25 }}" min="1">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Skor Risiko Keselamatan</label>
                                    <input type="number" name="settings[priority_score_safety]" class="form-control" value="{{ $settings['priority_score_safety'] ?? 50 }}" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Skor Belajar Terhambat</label>
                                    <input type="number" name="settings[priority_score_learning_blocked]" class="form-control" value="{{ $settings['priority_score_learning_blocked'] ?? 25 }}" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Skor Terkait Ujian</label>
                                    <input type="number" name="settings[priority_score_exam_related]" class="form-control" value="{{ $settings['priority_score_exam_related'] ?? 30 }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appearance Settings -->
                <div class="tab-pane fade" id="appearance" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pengaturan Tampilan</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Warna Utama</label>
                                    <input type="color" name="settings[theme_primary_color]" class="form-control form-control-color w-100" value="{{ $settings['theme_primary_color'] ?? '#0d6efd' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Warna Sekunder</label>
                                    <input type="color" name="settings[theme_secondary_color]" class="form-control form-control-color w-100" value="{{ $settings['theme_secondary_color'] ?? '#6c757d' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teks Footer</label>
                                <input type="text" name="settings[footer_text]" class="form-control" value="{{ $settings['footer_text'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="tab-pane fade" id="system" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pengaturan Sistem</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="settings[maintenance_mode]" id="maintenanceMode" value="true" {{ ($settings['maintenance_mode'] ?? 'false') == 'true' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="maintenanceMode">Mode Pemeliharaan</label>
                                </div>
                                <div class="form-text text-danger">Aktifkan mode ini untuk sementara menutup akses ke sistem</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pesan Mode Pemeliharaan</label>
                                <textarea name="settings[maintenance_message]" class="form-control" rows="2">{{ $settings['maintenance_message'] ?? 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Cache Management -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Manajemen Cache</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Hapus cache untuk memperbarui pengaturan yang di-cache.</p>
                            <button type="button" class="btn btn-warning" onclick="if(confirm('Yakin ingin menghapus cache?')) { fetch('/superadmin/settings/clear-cache', {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => location.reload()); }">
                                <i class="fas fa-sync-alt me-1"></i> Clear Cache
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-1"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
