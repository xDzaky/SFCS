@extends('layouts.sfcs')

@section('title', 'Detail Pengaduan')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('pengaduan.index') }}">Pengaduan</a></li>
    <li class="breadcrumb-item active">{{ $pengaduan->kode_pengaduan }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $pengaduan->kode_pengaduan }}</h1>
        <p class="page-subtitle">{{ $pengaduan->judul }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($pengaduan->status === 'pending' && $pengaduan->user_id === auth()->id())
            <a href="{{ route('pengaduan.edit', $pengaduan) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <form action="{{ route('pengaduan.destroy', $pengaduan) }}" method="POST" 
                  onsubmit="return confirm('Yakin ingin menghapus pengaduan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Hapus
                </button>
            </form>
        @endif
        <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Info -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Detail Pengaduan</span>
                <div class="d-flex gap-2">
                    <span class="badge badge-status badge-{{ $pengaduan->prioritas }}">
                        {{ ucfirst($pengaduan->prioritas) }}
                    </span>
                    <span class="badge badge-status badge-{{ $pengaduan->status }}">
                        {{ ucfirst($pengaduan->status) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <h5 class="fw-semibold mb-3">{{ $pengaduan->judul }}</h5>
                <p class="text-muted" style="white-space: pre-line;">{{ $pengaduan->deskripsi }}</p>
                
                @if($pengaduan->tanggal_kejadian)
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>Tanggal Kejadian: {{ $pengaduan->tanggal_kejadian->format('d M Y') }}
                        </small>
                    </div>
                @endif

                @if($pengaduan->catatan_admin)
                    <div class="alert alert-info mt-3">
                        <strong><i class="fas fa-comment me-2"></i>Catatan Admin:</strong><br>
                        {{ $pengaduan->catatan_admin }}
                    </div>
                @endif

                @if($pengaduan->catatan_teknisi)
                    <div class="alert alert-success mt-3">
                        <strong><i class="fas fa-tools me-2"></i>Catatan Teknisi:</strong><br>
                        {{ $pengaduan->catatan_teknisi }}
                    </div>
                @endif

                @if($pengaduan->status === 'ditolak' && $pengaduan->alasan_tolak)
                    <div class="alert alert-danger mt-3">
                        <strong><i class="fas fa-times-circle me-2"></i>Alasan Penolakan:</strong><br>
                        {{ $pengaduan->alasan_tolak }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Photos -->
        @if($pengaduan->photos->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-images me-2"></i>Foto Bukti ({{ $pengaduan->photos->count() }})
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($pengaduan->photos as $photo)
                            <div class="col-6 col-md-4">
                                <a href="{{ asset('storage/' . $photo->file_path) }}" target="_blank" data-fancybox="gallery">
                                    <img src="{{ asset('storage/' . $photo->file_path) }}" class="img-fluid rounded" 
                                         style="width: 100%; height: 150px; object-fit: cover;">
                                </a>
                                <small class="text-muted d-block mt-1">{{ ucfirst($photo->tipe ?? 'bukti') }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Feedback Form (if completed and no feedback yet) -->
        @if($pengaduan->status === 'selesai' && !$pengaduan->feedback && $pengaduan->user_id === auth()->id())
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-star me-2"></i>Berikan Feedback
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Pengaduan Anda telah selesai! Bantu kami meningkatkan layanan dengan memberikan feedback.</p>
                    <form action="{{ route('pengaduan.feedback', $pengaduan) }}" method="POST">
                        @csrf
                        
                        <!-- Rating Respon -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Kecepatan Respon <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Seberapa cepat pengaduan Anda ditanggapi?</p>
                            <div class="rating-input d-flex gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <div>
                                        <input type="radio" class="btn-check" name="rating_respon" id="rating_respon_{{ $i }}" value="{{ $i }}" {{ old('rating_respon') == $i ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-warning" for="rating_respon_{{ $i }}">
                                            {{ $i }} ⭐
                                        </label>
                                    </div>
                                @endfor
                            </div>
                            @error('rating_respon')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rating Kualitas -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Kualitas Perbaikan <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Seberapa baik hasil perbaikan yang dilakukan?</p>
                            <div class="rating-input d-flex gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <div>
                                        <input type="radio" class="btn-check" name="rating_kualitas" id="rating_kualitas_{{ $i }}" value="{{ $i }}" {{ old('rating_kualitas') == $i ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-warning" for="rating_kualitas_{{ $i }}">
                                            {{ $i }} ⭐
                                        </label>
                                    </div>
                                @endfor
                            </div>
                            @error('rating_kualitas')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rating Pelayanan -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Pelayanan Teknisi <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Bagaimana sikap dan pelayanan teknisi?</p>
                            <div class="rating-input d-flex gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <div>
                                        <input type="radio" class="btn-check" name="rating_pelayanan" id="rating_pelayanan_{{ $i }}" value="{{ $i }}" {{ old('rating_pelayanan') == $i ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-warning" for="rating_pelayanan_{{ $i }}">
                                            {{ $i }} ⭐
                                        </label>
                                    </div>
                                @endfor
                            </div>
                            @error('rating_pelayanan')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Is Satisfied -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Apakah Anda puas dengan penyelesaian ini? <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div>
                                    <input type="radio" class="btn-check" name="is_satisfied" id="satisfied_yes" value="1" {{ old('is_satisfied') == '1' ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-success" for="satisfied_yes">
                                        😊 Ya, Puas
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" class="btn-check" name="is_satisfied" id="satisfied_no" value="0" {{ old('is_satisfied') == '0' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger" for="satisfied_no">
                                        😞 Tidak Puas
                                    </label>
                                </div>
                            </div>
                            @error('is_satisfied')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Komentar -->
                        <div class="mb-4">
                            <label for="komentar" class="form-label fw-semibold">Komentar (Opsional)</label>
                            <textarea class="form-control" id="komentar" name="komentar" rows="3" 
                                      placeholder="Bagikan pengalaman atau saran Anda...">{{ old('komentar') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Feedback
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Existing Feedback -->
        @if($pengaduan->feedback)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-star me-2"></i>Feedback Pengguna
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="small text-muted mb-1">Kecepatan Respon</div>
                                <div class="h5 mb-0 text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $pengaduan->feedback->rating_respon ? '' : 'text-muted opacity-25' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="small text-muted mb-1">Kualitas Perbaikan</div>
                                <div class="h5 mb-0 text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $pengaduan->feedback->rating_kualitas ? '' : 'text-muted opacity-25' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="small text-muted mb-1">Pelayanan Teknisi</div>
                                <div class="h5 mb-0 text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $pengaduan->feedback->rating_pelayanan ? '' : 'text-muted opacity-25' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="h4 mb-0">{{ number_format($pengaduan->feedback->average_rating, 1) }}/5</div>
                        <span class="badge {{ $pengaduan->feedback->is_satisfied ? 'bg-success' : 'bg-danger' }} fs-6">
                            {{ $pengaduan->feedback->is_satisfied ? '😊 Puas' : '😞 Tidak Puas' }}
                        </span>
                    </div>

                    @if($pengaduan->feedback->komentar)
                        <div class="bg-light p-3 rounded mb-3">
                            <i class="fas fa-quote-left text-muted me-2"></i>
                            {{ $pengaduan->feedback->komentar }}
                        </div>
                    @endif
                    <small class="text-muted">Diberikan pada {{ $pengaduan->feedback->created_at->format('d M Y H:i') }}</small>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Informasi
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="40%">Pelapor</td>
                        <td class="fw-medium">{{ $pengaduan->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kategori</td>
                        <td>{{ $pengaduan->kategori->nama ?? '-' }}</td>
                    </tr>
                    @if($pengaduan->subKategori)
                    <tr>
                        <td class="text-muted">Sub Kategori</td>
                        <td>{{ $pengaduan->subKategori->nama }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Gedung</td>
                        <td>{{ $pengaduan->ruangan->gedung->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lantai</td>
                        <td>{{ $pengaduan->lantai ?? '-' }}</td>
                    </tr>
                    @if($pengaduan->ruangan)
                    <tr>
                        <td class="text-muted">Ruangan</td>
                        <td>{{ $pengaduan->ruangan->nama }}</td>
                    </tr>
                    @endif
                    @if($pengaduan->lokasi_detail)
                    <tr>
                        <td class="text-muted">Detail Lokasi</td>
                        <td>{{ $pengaduan->lokasi_detail }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Teknisi</td>
                        <td>{{ $pengaduan->assignedTo->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat</td>
                        <td>{{ $pengaduan->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Update Terakhir</td>
                        <td>{{ $pengaduan->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Timeline
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item {{ $pengaduan->status === 'pending' ? 'active' : 'completed' }}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="fw-semibold">Pengaduan Dibuat</div>
                            <small class="text-muted">{{ $pengaduan->created_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                    @if(in_array($pengaduan->status, ['diverifikasi', 'diproses', 'selesai']))
                        <div class="timeline-item {{ $pengaduan->status === 'diverifikasi' ? 'active' : 'completed' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="fw-semibold">Diverifikasi</div>
                                @if($pengaduan->verified_at)
                                    <small class="text-muted">{{ $pengaduan->verified_at->format('d M Y H:i') }}</small>
                                @else
                                    <small class="text-muted">Admin telah memverifikasi</small>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if(in_array($pengaduan->status, ['diproses', 'selesai']))
                        <div class="timeline-item {{ $pengaduan->status === 'diproses' ? 'active' : 'completed' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="fw-semibold">Sedang Diproses</div>
                                @if($pengaduan->started_at)
                                    <small class="text-muted">{{ $pengaduan->started_at->format('d M Y H:i') }}</small>
                                @else
                                    <small class="text-muted">Teknisi sedang menangani</small>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if($pengaduan->status === 'selesai')
                        <div class="timeline-item completed">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="fw-semibold">Selesai</div>
                                @if($pengaduan->completed_at)
                                    <small class="text-muted">{{ $pengaduan->completed_at->format('d M Y H:i') }}</small>
                                @else
                                    <small class="text-muted">{{ $pengaduan->updated_at->format('d M Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if($pengaduan->status === 'ditolak')
                        <div class="timeline-item rejected">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <div class="fw-semibold text-danger">Ditolak</div>
                                <small class="text-muted">{{ $pengaduan->updated_at->format('d M Y H:i') }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        @if($pengaduan->logs && $pengaduan->logs->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-list me-2"></i>Log Aktivitas
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                @foreach($pengaduan->logs as $log)
                    <div class="d-flex gap-2 mb-3 pb-3 border-bottom">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-sm bg-light">
                                <i class="fas fa-history text-muted"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small">{{ $log->deskripsi }}</div>
                            <small class="text-muted">
                                {{ $log->user->name ?? 'System' }} • {{ $log->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-marker {
        position: absolute;
        left: -26px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #d1d5db;
        border: 2px solid white;
    }
    .timeline-item.completed .timeline-marker {
        background: #10b981;
    }
    .timeline-item.active .timeline-marker {
        background: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
    }
    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-sm {
        width: 28px;
        height: 28px;
    }
</style>
@endpush
