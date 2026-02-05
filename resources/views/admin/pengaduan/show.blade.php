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
                                <a href="{{ $photo->url }}" target="_blank">
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
            <!-- Update Status -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-edit me-2"></i>Update Status</div>
                <div class="card-body">
                    <form action="{{ route('admin.pengaduan.status', $pengaduan) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ $pengaduan->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="diverifikasi" {{ $pengaduan->status == 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                                <option value="diproses" {{ $pengaduan->status == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                <option value="selesai" {{ $pengaduan->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <textarea name="catatan_admin" class="form-control" rows="2" placeholder="Catatan (opsional)">{{ $pengaduan->catatan_admin }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Assign Teknisi -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-user-cog me-2"></i>Tugaskan Teknisi</div>
                <div class="card-body">
                    <form action="{{ route('admin.pengaduan.assign', $pengaduan) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select name="teknisi_id" class="form-select" required>
                                <option value="">Pilih Teknisi</option>
                                @foreach($teknisis as $teknisi)
                                    <option value="{{ $teknisi->id }}" {{ $pengaduan->teknisi_id == $teknisi->id ? 'selected' : '' }}>
                                        {{ $teknisi->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Tugaskan</button>
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
        @if($pengaduan->feedback)
            <div class="card mt-4">
                <div class="card-header"><i class="fas fa-star me-2"></i>Feedback</div>
                <div class="card-body">
                    <div class="rating-stars mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $pengaduan->feedback->average_rating ? '' : 'empty' }}"></i>
                        @endfor
                        <span class="ms-2">{{ number_format($pengaduan->feedback->average_rating, 1) }}/5</span>
                    </div>
                    @if($pengaduan->feedback->komentar)
                        <p class="mb-0 small text-muted">{{ $pengaduan->feedback->komentar }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
