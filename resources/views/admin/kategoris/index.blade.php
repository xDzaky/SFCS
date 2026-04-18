@extends('layouts.sfcs')

@section('title', 'Kelola Kategori')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Kelola Kategori</h1>
        <p class="page-subtitle">Manajemen kategori pengaduan fasilitas</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createKategoriModal">
        <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline"> Tambah Kategori</span>
    </button>
</div>

<div class="row">
    @forelse($kategoris as $kategori)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas {{ $kategori->icon ?? 'fa-folder' }} me-2 text-primary"></i>
                        {{ $kategori->nama }}
                    </h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editKategoriModal{{ $kategori->id }}">
                                    <i class="fas fa-edit me-2"></i> Edit
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('admin.kategoris.destroy', $kategori) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash me-2"></i> Hapus
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    @if($kategori->deskripsi)
                        <p class="text-muted small mb-3">{{ $kategori->deskripsi }}</p>
                    @else
                        <p class="text-muted small mb-3 fst-italic">Tidak ada deskripsi</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <small class="text-muted">
                        <i class="fas fa-clipboard-list me-1"></i>
                        {{ $kategori->pengaduans_count ?? 0 }} pengaduan
                    </small>
                </div>
            </div>

            <!-- Edit Kategori Modal -->
            <div class="modal fade" id="editKategoriModal{{ $kategori->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.kategoris.update', $kategori) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Kategori</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control" value="{{ $kategori->nama }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="2">{{ $kategori->deskripsi }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icon (Font Awesome class)</label>
                                    <input type="text" name="icon" class="form-control" value="{{ $kategori->icon }}" placeholder="fa-folder">
                                    <div class="form-text">Contoh: fa-bolt, fa-faucet, fa-chair</div>
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
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <h5>Belum Ada Kategori</h5>
                    <p class="text-muted">Klik tombol "Tambah Kategori" untuk membuat kategori baru.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Create Kategori Modal -->
<div class="modal fade" id="createKategoriModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.kategoris.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Font Awesome class)</label>
                        <input type="text" name="icon" class="form-control" placeholder="fa-folder">
                        <div class="form-text">Contoh: fa-bolt, fa-faucet, fa-chair, fa-snowflake</div>
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
