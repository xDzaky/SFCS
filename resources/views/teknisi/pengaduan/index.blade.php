@extends('layouts.sfcs')

@section('title', 'Pengaduan Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Pengaduan Ditugaskan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengaduan Saya</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $stats['ditugaskan'] ?? 0 }}</h3>
                <small>Ditugaskan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-warning text-dark">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $stats['dikerjakan'] ?? 0 }}</h3>
                <small>Dikerjakan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $stats['selesai'] ?? 0 }}</h3>
                <small>Selesai</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ number_format($stats['rating'] ?? 0, 1) }}</h3>
                <small>Rata-rata Rating</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('teknisi.pengaduan.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari judul, deskripsi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="ditugaskan" {{ request('status') == 'ditugaskan' ? 'selected' : '' }}>Ditugaskan</option>
                        <option value="dikerjakan" {{ request('status') == 'dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prioritas</label>
                    <select name="prioritas" class="form-select">
                        <option value="">Semua</option>
                        <option value="urgent" {{ request('prioritas') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="tinggi" {{ request('prioritas') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                        <option value="sedang" {{ request('prioritas') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                        <option value="rendah" {{ request('prioritas') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Urutkan</label>
                    <select name="sort" class="form-select">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        <option value="priority" {{ request('sort') == 'priority' ? 'selected' : '' }}>Prioritas</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Pengaduan List -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pengaduan</th>
                        <th>Lokasi</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Ditugaskan</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengaduans as $pengaduan)
                        <tr>
                            <td>
                                <strong>{{ Str::limit($pengaduan->judul, 40) }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-tag me-1"></i>{{ $pengaduan->subKategori->nama ?? '-' }}
                                </small>
                            </td>
                            <td>
                                <small>
                                    <i class="fas fa-building me-1"></i>{{ $pengaduan->ruangan->gedung->nama ?? '-' }}
                                    <br>
                                    <span class="text-muted">{{ $pengaduan->lokasi_detail ?? '-' }}</span>
                                </small>
                            </td>
                            <td>
                                @php
                                    $prioritasBadges = [
                                        'rendah' => 'bg-success',
                                        'sedang' => 'bg-warning text-dark',
                                        'tinggi' => 'bg-orange',
                                        'urgent' => 'bg-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $prioritasBadges[$pengaduan->prioritas] ?? 'bg-secondary' }}">
                                    {{ ucfirst($pengaduan->prioritas) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusBadges = [
                                        'ditugaskan' => 'bg-info',
                                        'dikerjakan' => 'bg-warning text-dark',
                                        'selesai' => 'bg-success',
                                    ];
                                @endphp
                                <span class="badge {{ $statusBadges[$pengaduan->status] ?? 'bg-secondary' }}">
                                    {{ ucfirst($pengaduan->status) }}
                                </span>
                            </td>
                            <td>
                                <small>
                                    {{ $pengaduan->assigned_at ? $pengaduan->assigned_at->format('d/m/Y') : $pengaduan->created_at->format('d/m/Y') }}
                                    <br>
                                    <span class="text-muted">{{ $pengaduan->assigned_at ? $pengaduan->assigned_at->diffForHumans() : $pengaduan->created_at->diffForHumans() }}</span>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('teknisi.pengaduan.show', $pengaduan) }}" class="btn btn-outline-info" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($pengaduan->status === 'ditugaskan')
                                        <form action="{{ route('teknisi.pengaduan.start', $pengaduan) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-warning" title="Mulai Kerjakan">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($pengaduan->status === 'dikerjakan')
                                        <button type="button" class="btn btn-outline-success" title="Selesaikan" data-bs-toggle="modal" data-bs-target="#completeModal{{ $pengaduan->id }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Complete Modal -->
                        @if($pengaduan->status === 'dikerjakan')
                            <div class="modal fade" id="completeModal{{ $pengaduan->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('teknisi.pengaduan.complete', $pengaduan) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Selesaikan Pengaduan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Anda akan menandai pengaduan berikut sebagai selesai:</p>
                                                <div class="alert alert-info">
                                                    <strong>{{ $pengaduan->judul }}</strong>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Catatan Penyelesaian <span class="text-danger">*</span></label>
                                                    <textarea name="catatan_penyelesaian" class="form-control" rows="3" required placeholder="Jelaskan pekerjaan yang telah dilakukan..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check me-1"></i> Tandai Selesai
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-clipboard-check fa-3x mb-3 d-block"></i>
                                    <h5>Tidak ada pengaduan</h5>
                                    <p>Belum ada pengaduan yang ditugaskan kepada Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pengaduans->hasPages())
        <div class="card-footer">
            {{ $pengaduans->links() }}
        </div>
    @endif
</div>
@endsection
