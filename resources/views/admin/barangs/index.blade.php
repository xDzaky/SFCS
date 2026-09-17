@extends('layouts.sfcs')

@section('title', 'Master Barang')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Master Barang</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Master Barang</h1>
        <p class="page-subtitle">Kelola semua jenis barang dan stok yang bisa dipinjam siswa.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBarangModal">
        <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline"> Tambah Barang</span>
    </button>
</div>

<div class="alert alert-light border shadow-sm">
    <div class="fw-semibold mb-1">Aturan stok</div>
    <div class="small text-muted">
        `stok_total = stok_tersedia + stok_rusak + stok_yang_sedang_dipinjam`.
        Saat siswa check-out pinjaman, sistem otomatis mengurangi `stok_tersedia`. Saat check-in, stok tersedia akan bertambah lagi.
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg">
        <div class="card text-center h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-primary">{{ $stats['total_jenis'] }}</div>
                <small class="text-muted">Jenis Barang</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card text-center h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-success">{{ $stats['aktif'] }}</div>
                <small class="text-muted">Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card text-center h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-dark">{{ $stats['stok_total'] }}</div>
                <small class="text-muted">Stok Total</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card text-center h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-info">{{ $stats['stok_tersedia'] }}</div>
                <small class="text-muted">Tersedia</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg">
        <div class="card text-center h-100 shadow-sm">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-danger">{{ $stats['stok_rusak'] }}</div>
                <small class="text-muted">Rusak</small>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.barangs.index') }}">
            <div class="row g-3">
                <div class="col-lg-5">
                    <label class="form-label small fw-semibold">Cari Barang</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama, kode, kategori, lokasi" value="{{ request('search') }}">
                </div>
                <div class="col-lg-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label small fw-semibold">Unit Sarpras</label>
                    <select name="unit_sarpras" class="form-select">
                        <option value="">Semua unit</option>
                        <option value="atas"  {{ request('unit_sarpras') === 'atas'  ? 'selected' : '' }}>Sarpras Atas</option>
                        <option value="bawah" {{ request('unit_sarpras') === 'bawah' ? 'selected' : '' }}>Sarpras Bawah</option>
                    </select>
                </div>
                <div class="col-lg-1">
                    <label class="form-label small fw-semibold">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Semua</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('kategori') === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                    <a href="{{ route('admin.barangs.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="d-lg-none d-flex flex-column gap-3">
    @forelse($barangs as $barang)
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-semibold">{{ $barang->nama }}</div>
                        <div class="small text-muted">{{ $barang->kode_barang }} • {{ $barang->kategori ?: 'Tanpa kategori' }}</div>
                    </div>
                    <span class="badge {{ $barang->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $barang->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="small text-muted mt-2">{{ $barang->lokasi ?: 'Lokasi belum diisi' }}</div>
                <div class="row text-center mt-3 g-2">
                    <div class="col-4">
                        <div class="border rounded-3 py-2">
                            <div class="fw-semibold">{{ $barang->stok_total }}</div>
                            <div class="small text-muted">Total</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded-3 py-2">
                            <div class="fw-semibold text-info">{{ $barang->stok_tersedia }}</div>
                            <div class="small text-muted">Tersedia</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded-3 py-2">
                            <div class="fw-semibold text-danger">{{ $barang->stok_rusak }}</div>
                            <div class="small text-muted">Rusak</div>
                        </div>
                    </div>
                </div>
                <div class="small text-muted mt-3">
                    Dipakai di {{ $barang->pinjamans_count }} transaksi pinjaman
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#editBarangModal{{ $barang->id }}">
                        Edit
                    </button>
                    <form method="POST" action="{{ route('admin.barangs.toggle-status', $barang) }}" class="flex-fill">
                        @csrf
                        <button type="submit" class="btn btn-outline-dark btn-sm w-100">
                            {{ $barang->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </div>
                <form method="POST" action="{{ route('admin.barangs.destroy', $barang) }}" class="mt-2" onsubmit="return confirm('Hapus barang ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Hapus Barang</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5>Belum ada barang</h5>
                <p class="text-muted mb-3">Tambahkan barang pertama supaya siswa bisa mulai mengajukan pinjaman.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBarangModal">
                    Tambah Barang Pertama
                </button>
            </div>
        </div>
    @endforelse
</div>

<div class="card border-0 shadow-sm d-none d-lg-block">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Barang</th>
                        <th>Unit Sarpras</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Tersedia</th>
                        <th class="text-center">Rusak</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Riwayat</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangs as $barang)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $barang->nama }}</div>
                                <div class="small text-muted">{{ $barang->kode_barang }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $barang->unit_sarpras === 'bawah' ? 'bg-success' : 'bg-primary' }} bg-opacity-75">
                                    {{ $barang->unit_sarpras === 'bawah' ? 'Sarpras Bawah' : 'Sarpras Atas' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $barang->tipe_transaksi === 'minta' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                    {{ $barang->tipe_transaksi === 'minta' ? 'Permintaan' : 'Pinjaman' }}
                                </span>
                            </td>
                            <td>{{ $barang->kategori ?: '-' }}</td>
                            <td><small class="text-muted">{{ $barang->lokasi ?: '-' }}</small></td>
                            <td class="text-center fw-semibold">{{ $barang->stok_total }}</td>
                            <td class="text-center text-info fw-semibold">{{ $barang->stok_tersedia }}</td>
                            <td class="text-center text-danger fw-semibold">{{ $barang->stok_rusak }}</td>
                            <td class="text-center">
                                <span class="badge {{ $barang->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $barang->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center small text-muted">{{ $barang->pinjamans_count }}x</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editBarangModal{{ $barang->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.barangs.toggle-status', $barang) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-dark btn-sm">
                                            <i class="fas {{ $barang->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.barangs.destroy', $barang) }}"
                                        onsubmit="return confirm('Hapus barang ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                <div class="fw-semibold">Belum ada barang</div>
                                <div class="text-muted mb-3">Tambahkan barang pertama supaya siswa bisa mulai meminjam fasilitas sekolah.</div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBarangModal">
                                    Tambah Barang Pertama
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $barangs->links() }}
</div>

<div class="modal fade" id="createBarangModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.barangs.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.barangs.partials.form-fields')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modals — HARUS di luar tabel agar DOM-nya valid --}}
@foreach($barangs as $barang)
    @include('admin.barangs.partials.edit-modal', ['barang' => $barang])
@endforeach

@endsection
