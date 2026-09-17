@extends('layouts.sfcs')

@section('title', 'Detail Pengaduan')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teknisi.pengaduan.index') }}">Pengaduan Saya</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($pengaduan->judul, 30) }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Content -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-tools me-2"></i>{{ $pengaduan->judul }}</h5>
                @php
                    $statusBadges = [
                        'pending' => 'bg-secondary',
                        'diverifikasi' => 'bg-info',
                        'diproses' => 'bg-warning text-dark',
                        'selesai' => 'bg-success',
                        'ditolak' => 'bg-danger',
                    ];
                    $statusLabels = [
                        'pending' => 'Menunggu',
                        'diverifikasi' => 'Ditugaskan',
                        'diproses' => 'Dikerjakan',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                    ];
                @endphp
                <span class="badge {{ $statusBadges[$pengaduan->status] ?? 'bg-secondary' }} fs-6">
                    <i class="fas fa-flag me-1"></i>{{ $statusLabels[$pengaduan->status] ?? ucfirst($pengaduan->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Kode Pengaduan:</strong> {{ $pengaduan->kode_pengaduan }}
                </div>

                <div class="mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="fas fa-clipboard-list me-2"></i>Deskripsi Masalah
                    </h6>
                    <p class="mb-0" style="line-height: 1.8;">{{ $pengaduan->deskripsi }}</p>
                </div>

                @if($pengaduan->photos && $pengaduan->photos->count() > 0)
                    <div class="mb-4">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-camera me-2"></i>Foto Dokumentasi ({{ $pengaduan->photos->count() }} foto)
                        </h6>
                        <div class="row g-3">
                            @foreach($pengaduan->photos as $photo)
                                <div class="col-md-4 col-6">
                                    <div class="card">
                                        <a href="{{ asset('storage/' . $photo->file_path) }}" data-fancybox="gallery" data-caption="Foto {{ $loop->iteration }} - {{ $pengaduan->judul }}">
                                            <img src="{{ asset('storage/' . $photo->file_path) }}" class="card-img-top" alt="Foto {{ $loop->iteration }}" style="height: 200px; object-fit: cover;">
                                        </a>
                                        <div class="card-body text-center py-2">
                                            <small class="text-muted">Foto {{ $loop->iteration }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($pengaduan->catatan_penyelesaian)
                    <div class="mb-4">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-check-circle me-2"></i>Catatan Penyelesaian
                        </h6>
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ $pengaduan->catatan_penyelesaian }}
                        </div>
                    </div>
                @endif

                @include('partials.pengaduan-map-card', [
                    'mapPayload' => $mapPayload,
                    'viewerId' => 'teknisi-pengaduan-map',
                    'title' => 'Lokasi Kerusakan di Denah',
                    'fullMapUrl' => route('teknisi.peta-digital.index'),
                    'showInfoDenah' => false,
                ])

                @if($pengaduan->feedback)
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Feedback dari Pelapor</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $pengaduan->feedbackDetail->average_rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-muted"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="badge bg-primary">{{ number_format($pengaduan->feedbackDetail->average_rating, 1) }}/5</span>
                                </div>
                                @if($pengaduan->feedbackDetail->komentar)
                                    <p class="mb-0">{{ $pengaduan->feedbackDetail->komentar }}</p>
                                @endif
                                <small class="text-muted">
                                    Diberikan pada {{ $pengaduan->feedbackDetail->created_at->format('d F Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        @if($pengaduan->status === 'diverifikasi')
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-play-circle me-2"></i>Mulai Pengerjaan</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Klik tombol di bawah untuk memulai mengerjakan pengaduan ini. Status akan berubah menjadi <strong>"Dikerjakan"</strong>.
                    </p>
                    <form action="{{ route('teknisi.pengaduan.update-status', $pengaduan) }}" method="POST">
                        @csrf
                        @method('POST')
                        <input type="hidden" name="status" value="diproses">
                        <button type="submit" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-play me-2"></i> Mulai Kerjakan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if(in_array($pengaduan->status, ['diverifikasi', 'diproses']))
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-calendar-days me-2"></i>Jadwal Penanganan</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Queue Rank</small>
                            <strong>{{ $pengaduan->queue_rank ?? '-' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Triage Score</small>
                            <strong>{{ $pengaduan->triage_score ?? 0 }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Prediksi Delay</small>
                            <strong class="{{ $pengaduan->is_overload_delayed ? 'text-danger' : '' }}">{{ $pengaduan->delay_minutes ?? 0 }} menit</strong>
                        </div>
                    </div>
                    <form action="{{ route('teknisi.pengaduan.reschedule', $pengaduan) }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Mulai Baru</label>
                            <input type="datetime-local" name="planned_start_at" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Selesai Baru</label>
                            <input type="datetime-local" name="planned_end_at" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alasan Reschedule</label>
                            <textarea name="reason" class="form-control" rows="2" minlength="10" required placeholder="Contoh: Menunggu suku cadang, dijadwalkan besok pagi."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-info text-white">
                                <i class="fas fa-arrows-rotate me-2"></i>Reschedule
                            </button>
                        </div>
                    </form>
                    <form action="{{ route('teknisi.pengaduan.recompute-queue') }}" method="POST" class="mt-2">
                        @csrf
                        <input type="hidden" name="teknisi_id" value="{{ auth()->id() }}">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-calculator me-2"></i>Hitung Ulang Antrean
                        </button>
                    </form>

                    @if($pengaduan->schedules->count() > 0)
                        <hr>
                        <h6 class="mb-2">Riwayat Reschedule</h6>
                        @foreach($pengaduan->schedules->take(3) as $schedule)
                            <div class="small {{ !$loop->last ? 'mb-2 pb-2 border-bottom' : '' }}">
                                <div>
                                    <strong>{{ optional($schedule->from_start)->format('d/m H:i') ?? '-' }}</strong>
                                    <i class="fas fa-arrow-right mx-1"></i>
                                    <strong>{{ optional($schedule->to_start)->format('d/m H:i') ?? '-' }}</strong>
                                </div>
                                <div class="text-muted">{{ $schedule->reason }}</div>
                                <div class="text-muted">oleh {{ $schedule->changer->name ?? 'Sistem' }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endif

        @if($pengaduan->status === 'diproses')
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-check-circle me-2"></i>Selesaikan Pengaduan</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Tips:</strong> Jelaskan pekerjaan yang telah dilakukan, material yang digunakan, dan hasil perbaikan.
                    </div>
                    <form action="{{ route('teknisi.pengaduan.complete', $pengaduan) }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-clipboard-check me-1"></i>
                                Catatan Penyelesaian <span class="text-muted fw-normal">(opsional)</span>
                            </label>
                            <textarea name="catatan_teknisi" class="form-control @error('catatan_teknisi') is-invalid @enderror" rows="5" placeholder="Contoh: AC sudah diperbaiki, freon ditambah 1kg, filter dibersihkan. AC sudah berfungsi normal kembali."></textarea>
                            @error('catatan_teknisi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Boleh dikosongkan — diisi jika ada catatan tambahan.</small>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-check me-2"></i> Tandai Selesai
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Info Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Detail</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <small class="text-muted d-block mb-1"><i class="fas fa-user me-1"></i>Pelapor</small>
                    <strong class="d-block">{{ $pengaduan->user->name }}</strong>
                    <span class="badge bg-secondary mt-1">{{ ucfirst($pengaduan->user->role) }}</span>
                </div>

                <div class="mb-3 pb-3 border-bottom">
                    <small class="text-muted d-block mb-1"><i class="fas fa-tags me-1"></i>Kategori</small>
                    <strong class="d-block">{{ $pengaduan->subKategori->kategori->nama ?? '-' }}</strong>
                    <small class="text-muted">{{ $pengaduan->subKategori->nama ?? '-' }}</small>
                </div>

                <div class="mb-3 pb-3 border-bottom">
                    <small class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt me-1"></i>Lokasi</small>
                    <strong class="d-block">
                        <i class="fas fa-building me-1 text-primary"></i>{{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '-' }}
                    </strong>
                    @if($pengaduan->ruangan)
                        <small class="text-muted d-block"><i class="fas fa-door-open me-1"></i>{{ $pengaduan->ruangan->nama ?? '-' }}</small>
                    @endif
                    @if($pengaduan->lokasi_detail)
                        <small class="text-muted d-block mt-1">{{ $pengaduan->lokasi_detail }}</small>
                    @endif
                </div>

                <div class="mb-3 pb-3 border-bottom">
                    <small class="text-muted d-block mb-1"><i class="fas fa-exclamation-triangle me-1"></i>Prioritas</small>
                    @php
                        $prioritasBadges = [
                            'rendah' => 'badge-prioritas-rendah',
                            'sedang' => 'badge-prioritas-sedang',
                            'tinggi' => 'badge-prioritas-tinggi',
                            'urgent' => 'badge-prioritas-urgent',
                        ];
                    @endphp
                    <span class="badge {{ $prioritasBadges[$pengaduan->prioritas] ?? 'bg-secondary' }} fs-6">
                        {{ strtoupper($pengaduan->prioritas) }}
                    </span>
                </div>

                <div class="mb-3 pb-3 border-bottom">
                    <small class="text-muted d-block mb-1"><i class="fas fa-calendar-alt me-1"></i>Tanggal Kejadian</small>
                    <strong>{{ $pengaduan->tanggal_kejadian ? $pengaduan->tanggal_kejadian->format('d F Y') : '-' }}</strong>
                </div>

                <div class="mb-3 pb-3 border-bottom">
                    <small class="text-muted d-block mb-1"><i class="fas fa-clock me-1"></i>Dilaporkan</small>
                    <strong class="d-block">{{ $pengaduan->created_at->format('d F Y H:i') }}</strong>
                    <small class="text-muted">{{ $pengaduan->created_at->diffForHumans() }}</small>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1"><i class="fas fa-user-check me-1"></i>Ditugaskan</small>
                    @if($pengaduan->assigned_at)
                        <strong class="d-block">{{ $pengaduan->assigned_at->format('d F Y H:i') }}</strong>
                        <small class="text-muted">{{ $pengaduan->assigned_at->diffForHumans() }}</small>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </div>

                @if($pengaduan->completed_at)
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block mb-1"><i class="fas fa-check-circle me-1"></i>Selesai</small>
                        <strong class="d-block">{{ $pengaduan->completed_at->format('d F Y H:i') }}</strong>
                        <small class="text-muted">{{ $pengaduan->completed_at->diffForHumans() }}</small>
                    </div>
                @endif
                </table>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Timeline Pengerjaan</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary"></div>
                        <div class="timeline-content">
                            <strong><i class="fas fa-file-alt me-1"></i>Dilaporkan</strong>
                            <p class="text-muted small mb-0">
                                <i class="far fa-clock me-1"></i>{{ $pengaduan->created_at->format('d F Y, H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    @if($pengaduan->assigned_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <strong><i class="fas fa-user-tag me-1"></i>Ditugaskan kepada Anda</strong>
                                <p class="text-muted small mb-0">
                                    <i class="far fa-clock me-1"></i>{{ $pengaduan->assigned_at->format('d F Y, H:i') }} WIB
                                </p>
                            </div>
                        </div>
                    @endif

                    @if(in_array($pengaduan->status, ['diproses', 'selesai']))
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <strong><i class="fas fa-tools me-1"></i>Mulai Dikerjakan</strong>
                                <p class="text-muted small mb-0">
                                    <i class="far fa-clock me-1"></i>{{ $pengaduan->started_at ? $pengaduan->started_at->format('d F Y, H:i') . ' WIB' : '' }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($pengaduan->status === 'selesai')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <strong><i class="fas fa-check-circle me-1"></i>Selesai Dikerjakan</strong>
                                <p class="text-muted small mb-0">
                                    <i class="far fa-clock me-1"></i>{{ $pengaduan->completed_at ? $pengaduan->completed_at->format('d F Y, H:i') . ' WIB' : '-' }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('teknisi.pengaduan.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
    border-left: 2px solid #e9ecef;
    padding-left: 20px;
    margin-left: 6px;
}
.timeline-item:last-child {
    border-left: 2px solid transparent;
    padding-bottom: 0;
}
.timeline-marker {
    position: absolute;
    left: -8px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
}
</style>
@endsection
