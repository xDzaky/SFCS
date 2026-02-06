@extends('layouts.sfcs')

@section('title', 'Detail Pengaduan - Admin')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.pengaduan.index') }}">Kelola Pengaduan</a></li>
    <li class="breadcrumb-item active">{{ $pengaduan->kode_pengaduan }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $pengaduan->kode_pengaduan }}</h1>
        <p class="page-subtitle">{{ $pengaduan->judul }}</p>
    </div>
    <a href="{{ route('admin.pengaduan.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Info -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Detail Pengaduan</span>
                <div class="d-flex gap-2">
                    <span class="badge badge-status badge-{{ $pengaduan->prioritas }}">{{ ucfirst($pengaduan->prioritas) }}</span>
                    <span class="badge badge-status badge-{{ $pengaduan->status }}">{{ ucfirst($pengaduan->status) }}</span>
                </div>
            </div>
            <div class="card-body">
                <h5 class="fw-semibold mb-3">{{ $pengaduan->judul }}</h5>
                <p style="white-space: pre-line;">{{ $pengaduan->deskripsi }}</p>
            </div>
        </div>

        <!-- Photos -->
        @if($pengaduan->photos->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-images me-2"></i>Foto ({{ $pengaduan->photos->count() }})
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($pengaduan->photos as $photo)
                            <div class="col-4 col-md-3">
                                <a href="{{ $photo->url }}" data-fancybox="gallery" data-caption="Foto Bukti - {{ $pengaduan->judul }}">
                                    <img src="{{ $photo->url }}" class="img-fluid rounded" 
                                         style="width: 100%; height: 100px; object-fit: cover;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Activity Logs -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Riwayat Aktivitas
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($logs as $log)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="badge bg-secondary me-2">{{ $log->action }}</span>
                                    {{ $log->description }}
                                </div>
                                <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            @if($log->user)
                                <small class="text-muted">oleh: {{ $log->user->name }}</small>
                            @endif
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted">Belum ada aktivitas</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Info -->
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Informasi</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Pelapor</td><td>{{ $pengaduan->user->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $pengaduan->user->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Kategori</td><td>{{ $pengaduan->kategori->nama ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Gedung</td><td>{{ $pengaduan->ruangan->gedung->nama ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Ruangan</td><td>{{ $pengaduan->ruangan->nama ?? '-' }} (Lt. {{ $pengaduan->ruangan->lantai ?? '-' }})</td></tr>
                    <tr><td class="text-muted">Dibuat</td><td>{{ $pengaduan->created_at->format('d M Y H:i') }}</td></tr>
                </table>
            </div>
        </div>

        <!-- Actions -->
        @if(!in_array($pengaduan->status, ['selesai', 'ditolak']))
            <!-- Status Progress -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-tasks me-2"></i>Status Laporan</div>
                <div class="card-body">
                    <div class="status-stepper mb-4">
                        @php
                            $steps = [
                                'pending' => ['icon' => 'fa-paper-plane', 'label' => 'Terkirim'],
                                'diverifikasi' => ['icon' => 'fa-clipboard-check', 'label' => 'Diverifikasi'],
                                'diproses' => ['icon' => 'fa-tools', 'label' => 'Diproses'],
                                'selesai' => ['icon' => 'fa-check', 'label' => 'Selesai']
                            ];
                            $currentFound = false;
                        @endphp
                        
                        <div class="d-flex justify-content-between position-relative">
                            <div class="position-absolute top-50 start-0 w-100 translate-middle-y bg-light" style="height: 4px; z-index: 1;"></div>
                            
                            @foreach($steps as $key => $step)
                                @php
                                    $isActive = $key === $pengaduan->status;
                                    $isPast = !$isActive && !$currentFound;
                                    if ($isActive) $currentFound = true;
                                    
                                    $bgClass = $isActive ? 'bg-primary text-white border-primary' : ($isPast ? 'bg-primary text-white border-primary' : 'bg-white text-muted border-light');
                                    $scale = $isActive ? 'transform: scale(1.2); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);' : '';
                                @endphp
                                <div class="text-center position-relative" style="z-index: 2; width: 60px;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 border border-2" 
                                         style="width: 40px; height: 40px; {{ $bgClass }} {{ $scale }} transition: all 0.3s;">
                                        <i class="fas {{ $step['icon'] }}"></i>
                                    </div>
                                    <div class="small fw-bold {{ $isActive ? 'text-primary' : 'text-muted' }}">{{ $step['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <form action="{{ route('admin.pengaduan.status', $pengaduan) }}" method="POST">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold text-muted mb-0">Update Manual (Opsional):</label>
                            <span class="badge bg-light text-muted border fw-normal" style="font-size: 0.65rem;">Biasanya diubah oleh Teknisi</span>
                        </div>
                        <div class="d-grid gap-2">
                            @if($pengaduan->status == 'pending')
                                <button type="submit" name="status" value="diverifikasi" class="btn btn-info text-white">
                                    <i class="fas fa-clipboard-check me-2"></i>Verifikasi Pengaduan
                                </button>
                            @elseif($pengaduan->status == 'diverifikasi')
                                <button type="submit" name="status" value="diproses" class="btn btn-warning text-dark">
                                    <i class="fas fa-tools me-2"></i>Mulai Proses Pengerjaan
                                </button>
                            @elseif($pengaduan->status == 'diproses')
                                <button type="submit" name="status" value="selesai" class="btn btn-success">
                                    <i class="fas fa-check-circle me-2"></i>Tandai Selesai
                                </button>
                            @endif
                        </div>
                        
                        <div class="mt-3">
                            <label class="form-label small text-muted">Catatan Status (Opsional)</label>
                            <textarea name="catatan_admin" class="form-control form-control-sm" rows="2" placeholder="Tambahkan catatan untuk perubahan status ini..."></textarea>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assign Teknisi -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-user-cog me-2"></i>Tugaskan Teknisi</div>
                <div class="card-body">
                    @if($pengaduan->teknisi_id)
                        <div class="alert alert-info d-flex align-items-center mb-3">
                            <i class="fas fa-user-check fa-2x me-3"></i>
                            <div>
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Teknisi Saat Ini</small>
                                <div class="fw-bold">{{ $pengaduan->assignedTo->name }}</div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('admin.pengaduan.assign', $pengaduan) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small text-muted">Pilih Teknisi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <select name="teknisi_id" class="form-select" required>
                                    <option value="">-- Pilih Teknisi --</option>
                                    @foreach($teknisis as $teknisi)
                                        <option value="{{ $teknisi->id }}" {{ $pengaduan->teknisi_id == $teknisi->id ? 'selected' : '' }}>
                                            {{ $teknisi->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-user-plus me-2"></i>{{ $pengaduan->teknisi_id ? 'Ganti Teknisi' : 'Tugaskan Teknisi' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reject -->
            <div class="card">
                <div class="card-header bg-danger text-white"><i class="fas fa-times me-2"></i>Tolak Pengaduan</div>
                <div class="card-body">
                    <form action="{{ route('admin.pengaduan.reject', $pengaduan) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="alasan_tolak" class="form-control" rows="2" placeholder="Alasan penolakan..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Yakin ingin menolak pengaduan ini?')">
                            Tolak Pengaduan
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center">
                    @if($pengaduan->status === 'selesai')
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="mb-0">Pengaduan telah selesai</p>
                    @else
                        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                        <p class="mb-0">Pengaduan ditolak</p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Feedback -->
        @if($pengaduan->feedbackDetail)
            <div class="card mt-4">
                <div class="card-header"><i class="fas fa-star me-2"></i>Feedback</div>
                <div class="card-body">
                    <div class="rating-stars mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $pengaduan->feedbackDetail->average_rating ? '' : 'empty' }}"></i>
                        @endfor
                        <span class="ms-2">{{ number_format($pengaduan->feedbackDetail->average_rating, 1) }}/5</span>
                    </div>
                    @if($pengaduan->feedbackDetail->komentar)
                        <p class="mb-0 small text-muted">{{ $pengaduan->feedbackDetail->komentar }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
