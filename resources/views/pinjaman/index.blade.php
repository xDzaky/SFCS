@extends('layouts.sfcs')

@section('title', 'Pinjam & Minta Barang')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item active">Pinjam & Minta Barang</li>
</ol>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Pinjaman & Permintaan Saya</h1>
        <p class="text-muted mb-0">Pantau status pinjaman dan permintaan barang ke Sarpras.</p>
    </div>
    <a href="{{ route('pinjaman.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i><span class="d-none d-sm-inline">Ajukan Baru</span>
    </a>
</div>

{{-- Info panel --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm" style="border-left:4px solid #0d6efd!important">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0">
                    <i class="fas fa-exchange-alt text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold small">Peminjaman (Sarpras Atas)</div>
                    <div class="text-muted" style="font-size:.8rem">Proyektor, kabel, mic, dll. <strong>Dikembalikan</strong> setelah pakai.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm" style="border-left:4px solid #198754!important">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0">
                    <i class="fas fa-hand-holding text-success"></i>
                </div>
                <div>
                    <div class="fw-bold small">Permintaan (Sarpras Bawah)</div>
                    <div class="text-muted" style="font-size:.8rem">Kertas, spidol, ATK, dll. <strong>Tidak dikembalikan</strong>.</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Mobile card list --}}
<div class="d-md-none d-flex flex-column gap-3 mb-4">
    @forelse($pinjamans as $pinjaman)
        @php
            $isPermintaan = $pinjaman->tipe === 'minta';
            $statusColors = ['pending'=>'warning','disetujui'=>'info','dipinjam'=>'primary','terlambat'=>'danger','selesai'=>'success','ditolak'=>'secondary'];
            $sc = $statusColors[$pinjaman->status] ?? 'secondary';
        @endphp
        <a href="{{ route('pinjaman.show', $pinjaman) }}" class="card text-decoration-none shadow-sm border-0">
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
                @if(!$isPermintaan && $pinjaman->tgl_jatuh_tempo)
                    <div class="small text-muted mt-1"><i class="fas fa-calendar me-1"></i>Jatuh tempo: {{ $pinjaman->tgl_jatuh_tempo->format('d/m/Y') }}</div>
                @endif
            </div>
        </a>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h6>Belum ada pengajuan</h6>
                <p class="text-muted small mb-3">Ajukan pinjaman atau permintaan barang ke Sarpras.</p>
                <a href="{{ route('pinjaman.create') }}" class="btn btn-primary btn-sm">Ajukan Sekarang</a>
            </div>
        </div>
    @endforelse
</div>

{{-- Desktop table --}}
<div class="card d-none d-md-block border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Tipe</th>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Sarpras</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
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
                        <tr>
                            <td class="ps-4">
                                <span class="fw-semibold text-primary">{{ $pinjaman->kode_pinjaman }}</span>
                            </td>
                            <td>
                                @if($isPermintaan)
                                    <span class="badge bg-success">Permintaan</span>
                                @else
                                    <span class="badge bg-primary">Pinjaman</span>
                                @endif
                            </td>
                            <td>{{ $pinjaman->barang->nama ?? '-' }}</td>
                            <td>{{ $pinjaman->qty }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ $pinjaman->barang ? ($pinjaman->barang->unit_sarpras === 'bawah' ? 'Sarpras Bawah' : 'Sarpras Atas') : '-' }}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $isPermintaan ? '-' : optional($pinjaman->tgl_jatuh_tempo)->format('d/m/Y') }}
                                </small>
                            </td>
                            <td><span class="badge bg-{{ $sc }}">{{ ucfirst($pinjaman->status) }}</span></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('pinjaman.show', $pinjaman) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($pinjaman->status === 'pending')
                                        <form action="{{ route('pinjaman.cancel', $pinjaman) }}" method="POST"
                                            onsubmit="return confirm('Batalkan pengajuan ini?')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox fa-2x text-muted mb-3 d-block"></i>
                                <div class="fw-semibold">Belum ada pengajuan</div>
                                <p class="text-muted small mb-3">Ajukan pinjaman atau permintaan barang ke Sarpras.</p>
                                <a href="{{ route('pinjaman.create') }}" class="btn btn-primary btn-sm">Ajukan Sekarang</a>
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
