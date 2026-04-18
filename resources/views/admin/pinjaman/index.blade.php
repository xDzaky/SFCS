@extends('layouts.sfcs')

@section('title', 'Kelola Pinjaman')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item active">Kelola Pinjaman</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Kelola Pinjaman</h1>
        <p class="page-subtitle">Manajemen pinjaman barang fasilitas</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.barangs.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-box-open me-1"></i><span class="d-none d-sm-inline">Master Barang</span>
        </a>
        <a href="{{ route('admin.pinjaman.export') }}" class="btn btn-outline-success">
            <i class="fas fa-download me-1"></i><span class="d-none d-sm-inline">Export CSV</span>
        </a>
    </div>
</div>

{{-- Mobile Card List --}}
<div class="d-md-none d-flex flex-column gap-3 mb-4">
    @forelse($pinjamans as $pinjaman)
        <a href="{{ route('admin.pinjaman.show', $pinjaman) }}" class="card text-decoration-none">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="fw-bold text-primary small">{{ $pinjaman->kode_pinjaman }}</span>
                    <span class="badge bg-secondary">{{ ucfirst($pinjaman->status) }}</span>
                </div>
                <div class="fw-semibold text-dark">{{ $pinjaman->barang->nama ?? '-' }} &times; {{ $pinjaman->qty }}</div>
                <div class="small text-muted mt-1">
                    <i class="fas fa-user me-1"></i>{{ $pinjaman->user->name ?? '-' }}
                    @if($pinjaman->tgl_jatuh_tempo)
                        &nbsp;&bull;&nbsp;<i class="fas fa-calendar me-1"></i>{{ $pinjaman->tgl_jatuh_tempo->format('d/m/Y') }}
                    @endif
                </div>
            </div>
        </a>
    @empty
        <div class="card">
            <div class="card-body text-center py-4 text-muted">
                <div class="mb-2">Belum ada data pinjaman.</div>
                <a href="{{ route('admin.barangs.index') }}" class="btn btn-sm btn-outline-primary">Kelola Master Barang</a>
            </div>
        </div>
    @endforelse
</div>

{{-- Desktop Table --}}
<div class="card d-none d-md-block">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern table-hover mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Peminjam</th>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pinjamans as $pinjaman)
                        <tr onclick="window.location='{{ route('admin.pinjaman.show', $pinjaman) }}'" style="cursor:pointer;">
                            <td><span class="fw-semibold text-primary">{{ $pinjaman->kode_pinjaman }}</span></td>
                            <td>{{ $pinjaman->user->name ?? '-' }}</td>
                            <td>{{ $pinjaman->barang->nama ?? '-' }}</td>
                            <td>{{ $pinjaman->qty }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($pinjaman->status) }}</span></td>
                            <td><small class="text-muted">{{ optional($pinjaman->tgl_jatuh_tempo)->format('d/m/Y H:i') ?? '-' }}</small></td>
                            <td class="text-end">
                                <a href="{{ route('admin.pinjaman.show', $pinjaman) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada data pinjaman.
                            <div class="mt-3">
                                <a href="{{ route('admin.barangs.index') }}" class="btn btn-sm btn-outline-primary">Kelola Master Barang</a>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $pinjamans->links() }}</div>
@endsection
