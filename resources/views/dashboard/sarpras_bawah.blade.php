@extends('layouts.sfcs')

@section('title', 'Dashboard Sarpras Bawah')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Welcome Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body py-3 py-md-4 text-white">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-2 p-md-3 me-3">
                            <i class="fas fa-boxes fs-4 fs-md-3"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fs-6 fs-md-5">Halo, {{ auth()->user()->name }}!</h5>
                            <p class="mb-0 opacity-90 small">Sarpras Bawah — Kelola Permintaan ATK & Perlengkapan Belajar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-2 g-md-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-inbox text-primary fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold fs-5 fs-md-4">{{ $stats['total_permintaan'] }}</h4>
                    <p class="mb-0 text-muted small">Total Permintaan</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-clock text-warning fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-warning fs-5 fs-md-4">{{ $stats['pending'] }}</h4>
                    <p class="mb-0 text-muted small">Menunggu Persetujuan</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-success fs-5 fs-md-4">{{ $stats['selesai_bulan_ini'] }}</h4>
                    <p class="mb-0 text-muted small">Dipenuhi Bulan Ini</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-danger fs-5 fs-md-4">{{ $stats['stock_rendah'] }}</h4>
                    <p class="mb-0 text-muted small">Stok Hampir Habis</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-6">
            <a href="{{ route('admin.pinjaman.index') }}" class="btn btn-success btn-lg w-100 py-3 shadow-sm" style="border-radius: 15px;">
                <i class="fas fa-clipboard-check fs-4 me-2"></i>
                <span class="fs-5 fw-bold">KELOLA PERMINTAAN ATK</span>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="{{ route('admin.barangs.index') }}" class="btn btn-outline-success btn-lg w-100 py-3 shadow-sm" style="border-radius: 15px;">
                <i class="fas fa-boxes fs-4 me-2"></i>
                <span class="fs-5 fw-bold">KELOLA STOK BARANG</span>
            </a>
        </div>
    </div>

    @if($stats['stock_rendah'] > 0)
    <!-- Stock Alert -->
    <div class="alert alert-warning border-0 shadow-sm mb-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
            <div>
                <strong>Peringatan Stok!</strong>
                Ada <strong>{{ $stats['stock_rendah'] }} barang</strong> dengan stok hampir habis (kurang dari 10 unit).
                <a href="{{ route('admin.barangs.index') }}" class="alert-link">Lihat detail &rarr;</a>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Permintaan -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fs-6 fs-md-5">
                    <i class="fas fa-list-alt text-success"></i> Permintaan Terbaru
                </h5>
                <a href="{{ route('admin.pinjaman.index') }}" class="btn btn-sm btn-outline-success">
                    Lihat Semua
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($recentPermintaan->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fs-1 mb-3 d-block opacity-50"></i>
                    <p class="mb-0">Belum ada permintaan ATK</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">Pemohon</th>
                                <th class="small">Barang</th>
                                <th class="small">Jumlah</th>
                                <th class="small">Status</th>
                                <th class="small">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPermintaan as $item)
                            <tr>
                                <td class="small">
                                    <span class="fw-medium">{{ $item->user->name ?? '-' }}</span>
                                    <br><small class="text-muted">{{ $item->user->role_display ?? '' }}</small>
                                </td>
                                <td class="small">{{ $item->barang->nama ?? '-' }}</td>
                                <td class="small">{{ $item->jumlah }} {{ $item->barang->satuan ?? '' }}</td>
                                <td class="small">
                                    @php
                                        $statusClasses = [
                                            'pending'   => 'bg-warning text-dark',
                                            'disetujui' => 'bg-info text-white',
                                            'dipinjam'  => 'bg-primary text-white',
                                            'selesai'   => 'bg-success text-white',
                                            'ditolak'   => 'bg-danger text-white',
                                        ];
                                        $cls = $statusClasses[$item->status] ?? 'bg-secondary text-white';
                                    @endphp
                                    <span class="badge {{ $cls }}">{{ ucfirst($item->status) }}</span>
                                </td>
                                <td class="small text-muted">{{ $item->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
