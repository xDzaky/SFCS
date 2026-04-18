@extends('layouts.sfcs')

@section('title', 'Dashboard Kepala Sekolah')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Welcome Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-3 py-md-4 text-white">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-2 p-md-3 me-3">
                            <i class="fas fa-user-tie fs-4 fs-md-3"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fs-6 fs-md-5">Dashboard Kepala Sekolah</h5>
                            <p class="mb-0 opacity-90 small">Ringkasan performa sistem</p>
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
                        <i class="fas fa-clipboard-list text-primary fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold fs-5 fs-md-4">{{ $stats['total_bulan_ini'] }}</h4>
                    <p class="mb-0 text-muted small">Bulan Ini</p>
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
                    <p class="mb-0 text-muted small">Selesai</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-star text-warning fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-warning fs-5 fs-md-4">{{ number_format($stats['rata_rating'], 1) }}</h4>
                    <p class="mb-0 text-muted small">Rating</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-clock text-danger fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-danger fs-5 fs-md-4">{{ $stats['pending'] }}</h4>
                    <p class="mb-0 text-muted small">Menunggu</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Button -->
    <div class="row g-2 mb-3">
        <div class="col-12">
            <a href="{{ route('kepsek.reports') }}" class="btn btn-primary btn-lg w-100 py-3 shadow-sm" style="border-radius: 15px;">
                <i class="fas fa-chart-line fs-4 me-2"></i>
                <span class="fs-5 fw-bold">LIHAT LAPORAN LENGKAP</span>
            </a>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row g-2 g-md-3 mb-3">
        <!-- Performance by Kategori -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fs-6">
                        <i class="fas fa-chart-bar text-success"></i> Performa per Kategori
                    </h6>
                </div>
                <div class="card-body p-3">
                    @foreach($kategoriPerformance as $kategori)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold">{{ $kategori['nama'] }}</span>
                                <span class="badge bg-success small">{{ $kategori['persentase'] }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $kategori['persentase'] }}%"></div>
                            </div>
                            <small class="text-muted">{{ $kategori['selesai'] }}/{{ $kategori['total'] }} selesai</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Teknisi Performance -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fs-6">
                        <i class="fas fa-users text-info"></i> Performa Teknisi
                    </h6>
                </div>
                <div class="card-body p-3">
                    @foreach($teknisiPerformance as $teknisi)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <div class="fw-semibold small">{{ $teknisi['nama'] }}</div>
                                    <small class="text-muted">{{ $teknisi['selesai'] }}/{{ $teknisi['total'] }} tugas</small>
                                </div>
                                <div class="text-end">
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= round($teknisi['rating']) ? '' : 'opacity-25' }}"></i>
                                        @endfor
                                    </div>
                                    <small class="text-muted">{{ number_format($teknisi['rating'], 1) }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- High Priority Issues -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fs-6">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Isu Prioritas Tinggi
                </h6>
                <a href="{{ route('kepsek.reports') }}" class="btn btn-sm btn-outline-primary">
                    Laporan <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="card-body p-2 p-md-3">
            @if($highPriorityPengaduans->count() > 0)
                <!-- Mobile Card View -->
                <div class="d-md-none">
                    @foreach($highPriorityPengaduans as $pengaduan)
                        <div class="card mb-2 border border-danger shadow-sm">
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
                                        $sColor = $statusColors[$pengaduan->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $sColor }} small">
                                        {{ ucfirst($pengaduan->status) }}
                                    </span>
                                </div>
                                
                                <h6 class="mb-2 fw-bold">{{ Str::limit($pengaduan->judul, 50) }}</h6>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-tag me-1"></i>{{ $pengaduan->kategori->nama ?? '-' }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>{{ $pengaduan->assignedTo->name ?? 'Belum ditugaskan' }}
                                    </small>
                                </div>
                                
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $pengaduan->created_at->diffForHumans() }}
                                </small>
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
                                <th>Teknisi</th>
                                <th>Sejak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($highPriorityPengaduans as $pengaduan)
                                <tr>
                                    <td><span class="badge bg-light text-dark font-monospace">{{ $pengaduan->kode_pengaduan }}</span></td>
                                    <td>{{ Str::limit($pengaduan->judul, 35) }}</td>
                                    <td>{{ $pengaduan->kategori->nama ?? '-' }}</td>
                                    <td>
                                        <small>{{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '' }} - {{ $pengaduan->lokasi_detail ?? '' }}</small>
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
                                            $sColor = $statusColors[$pengaduan->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $sColor }}">
                                            {{ ucfirst($pengaduan->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $pengaduan->assignedTo->name ?? '-' }}</td>
                                    <td>
                                        <small class="text-muted">{{ $pengaduan->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle display-1 text-success opacity-25"></i>
                    <p class="text-muted mt-3">Tidak ada isu prioritas tinggi</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
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

    .btn-lg {
        min-height: 50px;
    }
</style>
@endpush
@endsection
