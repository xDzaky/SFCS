@extends('layouts.sfcs')

@section('title', 'Pinjam Barang')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item active">Pinjam Barang</li>
</ol>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">Pinjaman Saya</h1>
        <p class="text-muted mb-0">Pantau status pinjaman barang fasilitas.</p>
    </div>
    <a href="{{ route('pinjaman.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Ajukan Pinjaman
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pinjamans as $pinjaman)
                        <tr>
                            <td><span class="badge bg-light text-dark">{{ $pinjaman->kode_pinjaman }}</span></td>
                            <td>{{ $pinjaman->barang->nama ?? '-' }}</td>
                            <td>{{ $pinjaman->qty }}</td>
                            <td>{{ optional($pinjaman->tgl_jatuh_tempo)->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($pinjaman->status) }}</span></td>
                            <td class="text-end d-flex justify-content-end gap-2">
                                <a href="{{ route('pinjaman.show', $pinjaman) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                @if($pinjaman->status === 'pending')
                                    <form action="{{ route('pinjaman.cancel', $pinjaman) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan pengajuan ini?')">Batalkan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data pinjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $pinjamans->links() }}</div>
@endsection
