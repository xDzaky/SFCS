@extends('layouts.sfcs')

@section('title', 'Kelola Pengajuan Pinjaman & Permintaan')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item active">Kelola Pengajuan</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Kelola Pengajuan</h1>
        <p class="page-subtitle">Peminjaman (Sarpras Atas) & Permintaan Barang (Sarpras Bawah)</p>
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

{{-- Filter Bar --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.pinjaman.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Kode, nama peminjam, atau barang..." value="{{ request('search') }}">
                </div>
                <div class="col-lg-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua status</option>
                        @foreach(['pending','disetujui','dipinjam','terlambat','selesai','ditolak'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label small fw-semibold">Tipe</label>
                    <select name="tipe" class="form-select form-select-sm">
                        <option value="">Semua tipe</option>
                        <option value="pinjam" {{ request('tipe') === 'pinjam' ? 'selected' : '' }}>Peminjaman</option>
                        <option value="minta" {{ request('tipe') === 'minta' ? 'selected' : '' }}>Permintaan</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label small fw-semibold">Unit Sarpras</label>
                    <select name="unit_sarpras" class="form-select form-select-sm">
                        <option value="">Semua unit</option>
                        <option value="atas" {{ request('unit_sarpras') === 'atas' ? 'selected' : '' }}>Sarpras Atas</option>
                        <option value="bawah" {{ request('unit_sarpras') === 'bawah' ? 'selected' : '' }}>Sarpras Bawah</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
                    <a href="{{ route('admin.pinjaman.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Mobile Card List --}}
<div class="d-md-none d-flex flex-column gap-3 mb-4">
    @forelse($pinjamans as $pinjaman)
        @php
            $isPermintaan = $pinjaman->tipe === 'minta';
            $statusColors = ['pending'=>'warning','disetujui'=>'info','dipinjam'=>'primary','terlambat'=>'danger','selesai'=>'success','ditolak'=>'secondary'];
            $sc = $statusColors[$pinjaman->status] ?? 'secondary';
        @endphp
        <a href="{{ route('admin.pinjaman.show', $pinjaman) }}" class="card text-decoration-none shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div>
                        <span class="fw-bold text-primary small">{{ $pinjaman->kode_pinjaman }}</span>
                        <span class="badge ms-1 {{ $isPermintaan ? 'bg-success' : 'bg-primary' }} bg-opacity-75" style="font-size:.65rem">
                            {{ $isPermintaan ? 'Permintaan' : 'Pinjaman' }}
                        </span>
                    </div>
                    <span class="badge bg-{{ $sc }}">{{ ucfirst($pinjaman->status) }}</span>
                </div>
                <div class="fw-semibold text-dark">{{ $pinjaman->barang->nama ?? '-' }} &times; {{ $pinjaman->qty }}</div>
                <div class="small text-muted mt-1">
                    <i class="fas fa-user me-1"></i>{{ $pinjaman->user->name ?? '-' }}
                    @if($pinjaman->barang)
                        &nbsp;&bull;&nbsp;{{ $pinjaman->barang->unit_sarpras === 'bawah' ? 'Sarpras Bawah' : 'Sarpras Atas' }}
                    @endif
                </div>
            </div>
        </a>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada data pengajuan.
            </div>
        </div>
    @endforelse
</div>

{{-- Desktop Table --}}
<div class="card d-none d-md-block border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Tipe</th>
                        <th>Pemohon</th>
                        <th>Barang</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pinjamans as $pinjaman)
                        @php
                            $isPermintaan = $pinjaman->tipe === 'minta';
                            $statusColors = ['pending'=>'warning','disetujui'=>'info','dipinjam'=>'primary','terlambat'=>'danger','selesai'=>'success','ditolak'=>'secondary'];
                            $sc = $statusColors[$pinjaman->status] ?? 'secondary';
                        @endphp
                        <tr onclick="window.location='{{ route('admin.pinjaman.show', $pinjaman) }}'" style="cursor:pointer">
                            <td class="ps-4">
                                <span class="fw-semibold text-primary">{{ $pinjaman->kode_pinjaman }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $isPermintaan ? 'bg-success' : 'bg-primary' }}">
                                    {{ $isPermintaan ? 'Permintaan' : 'Pinjaman' }}
                                </span>
                            </td>
                            <td>{{ $pinjaman->user->name ?? '-' }}</td>
                            <td>{{ $pinjaman->barang->nama ?? '-' }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ $pinjaman->barang ? ($pinjaman->barang->unit_sarpras === 'bawah' ? 'Sarpras Bawah' : 'Sarpras Atas') : '-' }}
                                </small>
                            </td>
                            <td>{{ $pinjaman->qty }}</td>
                            <td><span class="badge bg-{{ $sc }}">{{ ucfirst($pinjaman->status) }}</span></td>
                            <td>
                                <small class="text-muted">
                                    {{ $isPermintaan ? '-' : optional($pinjaman->tgl_jatuh_tempo)->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.pinjaman.show', $pinjaman) }}"
                                    class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation()">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-inbox fa-2x text-muted mb-3 d-block"></i>
                                <div class="fw-semibold">Belum ada pengajuan</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $pinjamans->links() }}</div>
@endsection
