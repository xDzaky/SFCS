@extends('layouts.sfcs')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Welcome Section - Mobile Optimized -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-3 py-md-4 text-white">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-2 p-md-3 me-3">
                            <i class="fas fa-user fs-4 fs-md-3"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fs-6 fs-md-5">Halo, {{ Auth::user()->name }}!</h5>
                            <p class="mb-0 opacity-90 small">Kelola pengaduan fasilitas sekolah</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards - Mobile First Grid -->
    <div class="row g-2 g-md-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-file-alt text-primary fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold fs-5 fs-md-4">{{ $stats['total'] }}</h4>
                    <p class="mb-0 text-muted small">Total</p>
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
                    <p class="mb-0 text-muted small">Menunggu</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-cog text-info fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-info fs-5 fs-md-4">{{ $stats['proses'] }}</h4>
                    <p class="mb-0 text-muted small">Dikerjakan</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-success fs-5 fs-md-4">{{ $stats['selesai'] }}</h4>
                    <p class="mb-0 text-muted small">Selesai</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Buttons - Large Touch-Friendly -->
    <div class="row g-2 mb-3">
        <div class="col-12">
            <a href="{{ route('pengaduan.create') }}" class="btn btn-primary btn-lg w-100 py-3 shadow-sm" style="border-radius: 15px;">
                <i class="fas fa-plus-circle fs-4 me-2"></i>
                <span class="fs-5 fw-bold">BUAT PENGADUAN BARU</span>
            </a>
        </div>
        <div class="col-6">
            <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-primary w-100 py-3" style="border-radius: 12px;">
                <i class="fas fa-list d-block fs-3 mb-1"></i>
                <small class="d-block">Lihat Semua</small>
            </a>
        </div>
        <div class="col-6">
            <a href="{{ route('pengaduan.track') }}" class="btn btn-outline-secondary w-100 py-3" style="border-radius: 12px;">
                <i class="fas fa-search d-block fs-3 mb-1"></i>
                <small class="d-block">Lacak Pengaduan</small>
            </a>
        </div>
    </div>

    <!-- Recent Pengaduans - Card Style for Mobile -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-history text-primary"></i> Pengaduan Terbaru
                        </h5>
                        <a href="{{ route('pengaduan.index') }}" class="btn btn-sm btn-outline-primary">
                            Semua <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-2 p-md-3">
                    @if($pengaduans->count() > 0)
                        <!-- Mobile Card View -->
                        <div class="d-md-none">
                            @foreach($pengaduans as $pengaduan)
                                <div class="card mb-2 border shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-light text-dark font-monospace small">
                                                {{ $pengaduan->kode_pengaduan }}
                                            </span>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'diverifikasi' => 'info',
                                                    'diproses' => 'primary',
                                                    'selesai' => 'success',
                                                    'ditolak' => 'danger'
                                                ];
                                                $color = $statusColors[$pengaduan->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }} small">
                                                {{ ucfirst($pengaduan->status) }}
                                            </span>
                                        </div>
                                        
                                        <h6 class="mb-2 fw-bold">{{ Str::limit($pengaduan->judul, 50) }}</h6>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i>{{ $pengaduan->kategori->nama ?? '-' }}
                                            </small>
                                            <small class="text-muted ms-2">
                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $pengaduan->ruangan->gedung->nama ?? '-' }}
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>{{ $pengaduan->created_at->diffForHumans() }}
                                            </small>
                                            <a href="{{ route('pengaduan.show', $pengaduan->kode_pengaduan) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Desktop Table View -->
                        <div class="d-none d-md-block table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Judul</th>
                                        <th>Kategori</th>
                                        <th>Lokasi</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pengaduans as $pengaduan)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark font-monospace">
                                                    {{ $pengaduan->kode_pengaduan }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ Str::limit($pengaduan->judul, 40) }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $pengaduan->kategori->nama ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $pengaduan->ruangan->gedung->nama ?? '-' }}
                                                </small>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'diverifikasi' => 'info',
                                                        'diproses' => 'primary',
                                                        'selesai' => 'success',
                                                        'ditolak' => 'danger'
                                                    ];
                                                    $color = $statusColors[$pengaduan->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst($pengaduan->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $pengaduan->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('pengaduan.show', $pengaduan->kode_pengaduan) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3 mb-3">Belum ada pengaduan</p>
                            <a href="{{ route('pengaduan.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Buat Pengaduan Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                            <i class="fas fa-question-circle text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 small fw-bold">Butuh Bantuan?</h6>
                            <p class="mb-0 small text-muted">
                                Hubungi admin jika ada kendala
                            </p>
                        </div>
                        <a href="#" class="btn btn-sm btn-primary d-none d-md-inline-block">
                            <i class="fas fa-book"></i> Panduan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Mobile-first responsive styles */
    @media (max-width: 767.98px) {
        .container-fluid {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
    }
    
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .btn {
        transition: all 0.2s;
    }
    
    .btn:active {
        transform: scale(0.98);
    }
    
    .table tbody tr {
        transition: background-color 0.2s;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    /* Touch-friendly spacing */
    .btn-lg {
        min-height: 50px;
    }
</style>
@endpush
@endsection
