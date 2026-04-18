@extends('layouts.sfcs')

@section('title', 'Kelola Gedung')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Kelola Gedung</h1>
        <p class="page-subtitle">Manajemen data gedung dan fasilitas</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGedungModal">
        <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline"> Tambah Gedung</span>
    </button>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-primary">{{ $gedungs->count() }}</div>
                <small class="text-muted">Total Gedung</small>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-info">{{ $gedungs->sum('lantai') }}</div>
                <small class="text-muted">Total Lantai</small>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-warning">{{ $gedungs->sum(fn($g) => $g->pengaduans_count ?? 0) }}</div>
                <small class="text-muted">Total Pengaduan</small>
            </div>
        </div>
    </div>
</div>

<!-- Gedung Cards -->
<div class="row g-3">
    @forelse($gedungs as $gedung)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2 text-primary"></i>
                        {{ $gedung->nama }}
                    </h5>
                    <span class="badge bg-secondary">{{ $gedung->kode }}</span>
                </div>
                <div class="card-body">
                    @if($gedung->deskripsi)
                        <p class="text-muted small mb-3">{{ $gedung->deskripsi }}</p>
                    @else
                        <p class="text-muted small mb-3 fst-italic">Tidak ada deskripsi</p>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">
                            <i class="fas fa-layer-group me-1"></i> Lantai
                        </span>
                        <span class="badge bg-info">{{ $gedung->lantai }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="fas fa-clipboard-list me-1"></i> Pengaduan
                        </span>
                        <span class="badge bg-{{ ($gedung->pengaduans_count ?? 0) > 0 ? 'warning text-dark' : 'secondary' }}">
                            {{ $gedung->pengaduans_count ?? 0 }}
                        </span>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#editGedungModal{{ $gedung->id }}">
                            <i class="fas fa-edit me-1"></i> Edit
                        </button>
                        <form action="{{ route('admin.gedungs.destroy', $gedung) }}" method="POST" class="flex-fill" onsubmit="return confirm('Yakin ingin menghapus gedung ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="fas fa-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Gedung Modal -->
            <div class="modal fade" id="editGedungModal{{ $gedung->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.gedungs.update', $gedung) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Gedung</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nama Gedung <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control" value="{{ $gedung->nama }}" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kode Gedung <span class="text-danger">*</span></label>
                                        <input type="text" name="kode" class="form-control" value="{{ $gedung->kode }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jumlah Lantai</label>
                                        <input type="number" name="lantai" class="form-control" value="{{ $gedung->lantai }}" min="0">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="2">{{ $gedung->deskripsi }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-building fa-4x text-muted mb-3"></i>
                    <h5>Belum Ada Gedung</h5>
                    <p class="text-muted">Klik tombol "Tambah Gedung" untuk membuat gedung baru.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Create Gedung Modal -->
<div class="modal fade" id="createGedungModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.gedungs.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Gedung Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Gedung <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Gedung <span class="text-danger">*</span></label>
                            <input type="text" name="kode" class="form-control" required placeholder="GD-X">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Lantai</label>
                            <input type="number" name="lantai" class="form-control" min="0" value="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
