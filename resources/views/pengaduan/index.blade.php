@extends('layouts.sfcs')

@section('title', 'Pengaduan Saya')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Pengaduan Saya</h1>
        <p class="page-subtitle">Daftar semua pengaduan yang telah Anda buat</p>
    </div>
    <a href="{{ route('pengaduan.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Buat Pengaduan
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('pengaduan.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="diverifikasi" {{ request('status') == 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Kategori</label>
                <select name="kategori_id" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris ?? [] as $kategori)
                        <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Cari</label>
                <input type="text" name="search" class="form-control" placeholder="Kode atau judul..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Pengaduans List -->
<div class="card">
    <div class="card-body p-0">
        @if($pengaduans->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Urgensi</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengaduans as $pengaduan)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-primary">{{ $pengaduan->kode_pengaduan }}</span>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ Str::limit($pengaduan->judul, 40) }}</div>
                                    @if($pengaduan->photos->count() > 0)
                                        <small class="text-muted">
                                            <i class="fas fa-image"></i> {{ $pengaduan->photos->count() }} foto
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $pengaduan->kategori->nama ?? '-' }}</td>
                                <td>
                                    <small>
                                        {{ $pengaduan->ruangan->gedung->nama ?? '' }}<br>
                                        {{ $pengaduan->lokasi_detail ?? '' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-{{ $pengaduan->prioritas }}">
                                        {{ ucfirst($pengaduan->prioritas) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-{{ $pengaduan->status }}">
                                        {{ ucfirst($pengaduan->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $pengaduan->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('pengaduan.show', $pengaduan) }}" class="btn btn-sm btn-outline-primary" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($pengaduan->status === 'pending')
                                            <a href="{{ route('pengaduan.edit', $pengaduan) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer">
                {{ $pengaduans->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-3">Belum ada pengaduan</p>
                <a href="{{ route('pengaduan.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Buat Pengaduan Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
