@extends('layouts.sfcs')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">Pengaturan Sistem</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pengaturan</li>
        </ol>
    </nav>
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
                                    <label class="form-label">Target Waktu Respons (Jam)</label>
                                    <input type="number" name="settings[sla_response_time]" class="form-control" value="{{ $settings['sla_response_time'] ?? 24 }}" min="1">
                                    <div class="form-text">Maksimal waktu untuk merespons pengaduan baru</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Target Waktu Penyelesaian (Jam)</label>
                                    <input type="number" name="settings[sla_resolution_time]" class="form-control" value="{{ $settings['sla_resolution_time'] ?? 72 }}" min="1">
                                    <div class="form-text">Maksimal waktu untuk menyelesaikan pengaduan</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pengali untuk Kasus Urgent</label>
                                <input type="number" name="settings[sla_urgent_multiplier]" class="form-control" value="{{ $settings['sla_urgent_multiplier'] ?? 0.5 }}" min="0.1" max="1" step="0.1">
                                <div class="form-text">Contoh: 0.5 berarti setengah dari waktu normal untuk kasus urgent</div>
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
