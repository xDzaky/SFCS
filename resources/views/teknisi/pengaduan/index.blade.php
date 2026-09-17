@extends('layouts.sfcs')

@section('title', 'Pengaduan Saya')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Pengaduan Ditugaskan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengaduan Saya</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-info-subtle h-100 transition-hover">
            <div class="card-body p-3 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-white text-info rounded-circle mb-2 shadow-sm" style="width: 48px; height: 48px;">
                    <i class="fas fa-clipboard-list fa-lg"></i>
                </div>
                <h3 class="fw-bold text-info-emphasis mb-0">{{ $stats['ditugaskan'] ?? 0 }}</h3>
                <small class="text-info-emphasis fw-semibold">Ditugaskan</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-warning-subtle h-100 transition-hover">
            <div class="card-body p-3 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-white text-warning rounded-circle mb-2 shadow-sm" style="width: 48px; height: 48px;">
                    <i class="fas fa-tools fa-lg"></i>
                </div>
                <h3 class="fw-bold text-warning-emphasis mb-0">{{ $stats['dikerjakan'] ?? 0 }}</h3>
                <small class="text-warning-emphasis fw-semibold">Dikerjakan</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-success-subtle h-100 transition-hover">
            <div class="card-body p-3 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-white text-success rounded-circle mb-2 shadow-sm" style="width: 48px; height: 48px;">
                    <i class="fas fa-check-circle fa-lg"></i>
                </div>
                <h3 class="fw-bold text-success-emphasis mb-0">{{ $stats['selesai'] ?? 0 }}</h3>
                <small class="text-success-emphasis fw-semibold">Selesai</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-primary-subtle h-100 transition-hover">
            <div class="card-body p-3 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle mb-2 shadow-sm" style="width: 48px; height: 48px;">
                    <i class="fas fa-star fa-lg"></i>
                </div>
                <h3 class="fw-bold text-primary-emphasis mb-0">{{ number_format($stats['rating'] ?? 0, 1) }}</h3>
                <small class="text-primary-emphasis fw-semibold">Rata-rata Rating</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 p-md-4">
        <form action="{{ route('teknisi.pengaduan.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Pencarian</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Cari judul, lokasi..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Status</label>
                    <select name="status" class="form-select rounded-pill bg-light border-0">
                        <option value="">Semua</option>
                        <option value="ditugaskan" {{ request('status') == 'ditugaskan' ? 'selected' : '' }}>Ditugaskan</option>
                        <option value="dikerjakan" {{ request('status') == 'dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Prioritas</label>
                    <select name="prioritas" class="form-select rounded-pill bg-light border-0">
                        <option value="">Semua</option>
                        <option value="urgent" {{ request('prioritas') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="tinggi" {{ request('prioritas') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                        <option value="sedang" {{ request('prioritas') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                        <option value="rendah" {{ request('prioritas') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Urutan</label>
                    <select name="sort" class="form-select rounded-pill bg-light border-0">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        <option value="priority" {{ request('sort') == 'priority' ? 'selected' : '' }}>Prioritas</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold shadow-sm">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Mobile View: Cards (Visible only on mobile) -->
<div class="d-md-none">
    @forelse($pengaduans as $pengaduan)
        <div class="card border-0 shadow-sm rounded-4 mb-3">
             <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    @php
                        $prioritasBadges = [
                            'rendah' => 'badge-prioritas-rendah',
                            'sedang' => 'badge-prioritas-sedang',
                            'tinggi' => 'badge-prioritas-tinggi',
                            'urgent' => 'badge-prioritas-urgent',
                        ];
                    @endphp
                    <span class="badge {{ $prioritasBadges[$pengaduan->prioritas] ?? 'bg-secondary' }} rounded-pill px-3 py-1 border border-light">
                        {{ ucfirst($pengaduan->prioritas) }}
                    </span>
                    <small class="text-muted"><i class="far fa-clock me-1"></i>{{ $pengaduan->created_at->diffForHumans() }}</small>
                </div>
                
                <h5 class="card-title fw-bold mb-1 text-dark">
                    <a href="{{ route('teknisi.pengaduan.show', $pengaduan) }}" class="text-decoration-none text-dark stretched-link">
                        {{ Str::limit($pengaduan->judul, 50) }}
                    </a>
                </h5>
                <p class="text-muted small mb-3">
                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> 
                    {{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '-' }}
                    @if($pengaduan->ruangan)
                        - {{ $pengaduan->ruangan->nama }}
                    @endif
                </p>
                
                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light position-relative" style="z-index: 2;">
                     @php
                        $statusBadges = [
                            'pending'      => 'badge-status-pending',
                            'diverifikasi' => 'badge-status-diverifikasi',
                            'diproses'     => 'badge-status-diproses',
                            'selesai'      => 'badge-status-selesai',
                            'ditolak'      => 'badge-status-ditolak',
                        ];
                    @endphp
                    <span class="badge rounded-pill {{ $statusBadges[$pengaduan->status] ?? 'bg-secondary' }} px-3 py-2 border border-light">
                        {{ ucfirst($pengaduan->status) }}
                    </span>

                    <div class="d-flex gap-2">
                        @if($pengaduan->status === 'diverifikasi')
                             <form action="{{ route('teknisi.pengaduan.update-status', $pengaduan) }}" method="POST" class="d-inline">
                                @csrf @method('POST')
                                <input type="hidden" name="status" value="diproses">
                                <button type="submit" class="btn btn-warning btn-sm rounded-pill text-dark fw-bold px-3 shadow-sm" title="Mulai Kerjakan">
                                    <i class="fas fa-play me-1"></i> Mulai
                                </button>
                            </form>
                        @endif
                         @if($pengaduan->status === 'diproses')
                             <button type="button" class="btn btn-success btn-sm rounded-pill text-white fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#completeModal{{ $pengaduan->id }}" title="Selesaikan">
                                 <i class="fas fa-check me-1"></i> Selesai
                             </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-clipboard-check text-muted fa-3x"></i>
            </div>
            <h5 class="text-muted fw-bold">Tidak ada pengaduan</h5>
            <p class="text-muted small">Belum ada tugas baru.</p>
        </div>
    @endforelse
</div>

<!-- Desktop View: Table (Hidden on mobile) -->
<div class="card border-0 shadow-sm rounded-4 d-none d-md-block overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                         <th class="ps-4 py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Pengaduan</th>
                         <th class="py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Lokasi</th>
                         <th class="py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Prioritas</th>
                         <th class="py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Status</th>
                         <th class="py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Ditugaskan</th>
                         <th class="pe-4 py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengaduans as $pengaduan)
                        <tr style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location='{{ route('teknisi.pengaduan.show', $pengaduan) }}'">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ Str::limit($pengaduan->judul, 40) }}</div>
                                <div class="text-muted small"><i class="fas fa-tag me-1 text-light-emphasis"></i>{{ $pengaduan->subKategori->nama ?? '-' }}</div>
                            </td>
                             <td>
                                <div class="text-dark fw-medium">{{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '-' }}</div>
                                <div class="text-muted small">
                                    @if($pengaduan->ruangan)
                                        {{ $pengaduan->ruangan->nama }}
                                    @else
                                        Lantai {{ $pengaduan->lantai ?? '-' }}
                                    @endif
                                </div>
                            </td>
                             <td>
                                <span class="badge {{ $prioritasBadges[$pengaduan->prioritas] ?? 'bg-secondary' }} rounded-pill px-3 py-2 border border-light-subtle">
                                    {{ ucfirst($pengaduan->prioritas) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadges[$pengaduan->status] ?? 'bg-secondary' }} rounded-pill px-3 py-2 border border-light-subtle">
                                    {{ ucfirst($pengaduan->status) }}
                                </span>
                            </td>
                             <td>
                                <div class="text-dark small fw-medium">{{ $pengaduan->assigned_at ? $pengaduan->assigned_at->format('d/m/Y') : '' }}</div>
                                <div class="text-muted x-small">{{ $pengaduan->assigned_at ? $pengaduan->assigned_at->diffForHumans() : '' }}</div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group">
                                    <a href="{{ route('teknisi.pengaduan.show', $pengaduan) }}" class="btn btn-light btn-sm rounded-circle shadow-sm me-2" title="Lihat" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    @if($pengaduan->status === 'diverifikasi')
                                        <form action="{{ route('teknisi.pengaduan.update-status', $pengaduan) }}" method="POST" class="d-inline">
                                            @csrf @method('POST')
                                            <input type="hidden" name="status" value="diproses">
                                            <button type="submit" class="btn btn-warning btn-sm rounded-circle shadow-sm text-dark me-2" title="Mulai Kerjakan" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($pengaduan->status === 'diproses')
                                        <button type="button" class="btn btn-success btn-sm rounded-circle shadow-sm text-white" title="Selesaikan" data-bs-toggle="modal" data-bs-target="#completeModal{{ $pengaduan->id }}" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-clipboard-check fa-3x mb-3 d-block opacity-25"></i>
                                    <h5 class="fw-bold">Tidak ada pengaduan</h5>
                                    <p class="mb-0 small">Belum ada pengaduan yang ditugaskan kepada Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $pengaduans->links() }}
</div>

{{-- Modals Section (Placed here to be shared by both views) --}}
@foreach($pengaduans as $pengaduan)
    @if($pengaduan->status === 'diproses')
        <div class="modal fade" id="completeModal{{ $pengaduan->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <form action="{{ route('teknisi.pengaduan.complete', $pengaduan) }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="modal-header border-bottom-0 pb-0">
                            <h5 class="modal-title fw-bold">Selesaikan Pengaduan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-light border rounded-3 mb-3">
                                <small class="text-muted d-block text-uppercase x-small fw-bold">Pengaduan</small>
                                <div class="fw-bold">{{ $pengaduan->judul }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Catatan Penyelesaian <span class="text-danger">*</span></label>
                                <textarea name="catatan_teknisi" class="form-control bg-light border-0" rows="4" required placeholder="Jelaskan perbaikan yang telah dilakukan..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                <i class="fas fa-check me-1"></i> Selesai
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
