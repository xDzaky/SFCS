@extends('layouts.sfcs')

@section('title', 'Detail Pinjaman Admin')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.pinjaman.index') }}">Kelola Pinjaman</a></li>
    <li class="breadcrumb-item active">{{ $pinjaman->kode_pinjaman }}</li>
</ol>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>{{ $pinjaman->kode_pinjaman }}</strong>
                <span class="badge bg-secondary">{{ ucfirst($pinjaman->status) }}</span>
            </div>
            <div class="card-body">
                <p><strong>Peminjam:</strong> {{ $pinjaman->user->name ?? '-' }}</p>
                <p><strong>Barang:</strong> {{ $pinjaman->barang->nama ?? '-' }}</p>
                <p><strong>Qty:</strong> {{ $pinjaman->qty }}</p>
                <p><strong>Jatuh Tempo:</strong> {{ optional($pinjaman->tgl_jatuh_tempo)->format('d/m/Y H:i') }}</p>
                <p><strong>Alasan:</strong><br>{{ $pinjaman->alasan }}</p>
                @if($pinjaman->catatan_admin)
                    <div class="alert alert-info"><strong>Catatan Admin:</strong> {{ $pinjaman->catatan_admin }}</div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if($pinjaman->status === 'pending')
                        <form method="POST" action="{{ route('admin.pinjaman.approve', $pinjaman) }}">
                            @csrf
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.pinjaman.reject', $pinjaman) }}">
                            @csrf
                            <input type="hidden" name="catatan_admin" value="Pengajuan tidak memenuhi syarat.">
                            <button class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    @endif

                    @if($pinjaman->status === 'disetujui')
                        <form method="POST" action="{{ route('admin.pinjaman.checkout', $pinjaman) }}">
                            @csrf
                            <button class="btn btn-primary btn-sm">Check-out Barang</button>
                        </form>
                    @endif

                    @if(in_array($pinjaman->status, ['dipinjam','terlambat']))
                        <form method="POST" action="{{ route('admin.pinjaman.checkin', $pinjaman) }}">
                            @csrf
                            <button class="btn btn-success btn-sm">Check-in / Selesai</button>
                        </form>
                    @endif

                    @if(!in_array($pinjaman->status, ['selesai','ditolak']))
                        <form method="POST" action="{{ route('admin.pinjaman.force-close', $pinjaman) }}">
                            @csrf
                            <input type="hidden" name="catatan_admin" value="Ditutup paksa oleh admin.">
                            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Tutup paksa pinjaman ini?')">Force Close</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Riwayat Aktivitas</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($pinjaman->logs as $log)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $log->description ?? $log->action }}</span>
                            <small class="text-muted">{{ $log->created_at?->format('d/m H:i') }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Stok Barang</div>
            <div class="card-body">
                @if($pinjaman->barang)
                    <p class="mb-1"><strong>Total:</strong> {{ $pinjaman->barang->stok_total }}</p>
                    <p class="mb-1"><strong>Tersedia:</strong> {{ $pinjaman->barang->stok_tersedia }}</p>
                    <p class="mb-3"><strong>Rusak:</strong> {{ $pinjaman->barang->stok_rusak }}</p>

                    <form method="POST" action="{{ route('admin.barang.adjust-stock', $pinjaman->barang) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Stok Total</label>
                            <input class="form-control form-control-sm" type="number" name="stok_total" value="{{ $pinjaman->barang->stok_total }}" min="0">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Stok Tersedia</label>
                            <input class="form-control form-control-sm" type="number" name="stok_tersedia" value="{{ $pinjaman->barang->stok_tersedia }}" min="0">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Stok Rusak</label>
                            <input class="form-control form-control-sm" type="number" name="stok_rusak" value="{{ $pinjaman->barang->stok_rusak }}" min="0">
                        </div>
                        <button class="btn btn-outline-primary btn-sm w-100" type="submit">Update Stok</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
