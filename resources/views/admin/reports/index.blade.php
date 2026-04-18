@extends('layouts.sfcs')

@section('title', 'Laporan')

@section('content')
<div class="page-header">
    <h1 class="page-title">Laporan</h1>
    <p class="page-subtitle">Analisis dan laporan data fasilitas sekolah</p>
</div>

<!-- Report Type Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100" style="border-top: 3px solid #4f46e5;">
            <div class="card-body text-center py-4">
                <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Laporan Pengaduan</h5>
                <p class="card-text text-muted small">Lihat laporan pengaduan berdasarkan periode, kategori, dan status.</p>
                <a href="{{ route('admin.reports.pengaduan') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-1"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100" style="border-top: 3px solid #10b981;">
            <div class="card-body text-center py-4">
                <i class="fas fa-chart-bar fa-3x text-success mb-3"></i>
                <h5 class="card-title">Laporan Performa</h5>
                <p class="card-text text-muted small">Analisis performa teknisi, waktu penyelesaian, dan rating.</p>
                <a href="{{ route('admin.reports.performance') }}" class="btn btn-success">
                    <i class="fas fa-arrow-right me-1"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100" style="border-top: 3px solid #06b6d4;">
            <div class="card-body text-center py-4">
                <i class="fas fa-download fa-3x text-info mb-3"></i>
                <h5 class="card-title">Export Data</h5>
                <p class="card-text text-muted small">Download data pengaduan dalam format CSV untuk analisis lanjutan.</p>
                <a href="{{ route('admin.pengaduan.export') }}" class="btn btn-info text-white">
                    <i class="fas fa-download me-1"></i> Export CSV
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik Bulan Ini</h5>
    </div>
    <div class="card-body">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="border rounded-3 p-3">
                    <h2 class="text-primary mb-0">{{ $stats['total_bulan_ini'] ?? 0 }}</h2>
                    <small class="text-muted">Pengaduan Masuk</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded-3 p-3">
                    <h2 class="text-success mb-0">{{ $stats['selesai_bulan_ini'] ?? 0 }}</h2>
                    <small class="text-muted">Selesai</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded-3 p-3">
                    <h2 class="text-warning mb-0">{{ $stats['proses_bulan_ini'] ?? 0 }}</h2>
                    <small class="text-muted">Dalam Proses</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded-3 p-3">
                    <h2 class="text-info mb-0">{{ number_format($stats['rata_rating'] ?? 0, 1) }}</h2>
                    <small class="text-muted">Rata-rata Rating</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="row g-3">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 5 Kategori (Bulan Ini)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topKategoris ?? [] as $kategori)
                                <tr>
                                    <td>{{ $kategori->nama }}</td>
                                    <td class="text-center">{{ $kategori->total }}</td>
                                    <td class="text-center">
                                        @php
                                            $percentage = ($stats['total_bulan_ini'] ?? 0) > 0 
                                                ? round($kategori->total / $stats['total_bulan_ini'] * 100, 1) 
                                                : 0;
                                        @endphp
                                        <div class="progress" style="height: 18px; border-radius: .5rem;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%">
                                                {{ $percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 5 Gedung (Bulan Ini)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Gedung</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topGedungs ?? [] as $gedung)
                                <tr>
                                    <td>{{ $gedung->nama }}</td>
                                    <td class="text-center">{{ $gedung->total }}</td>
                                    <td class="text-center">
                                        @php
                                            $percentage = ($stats['total_bulan_ini'] ?? 0) > 0 
                                                ? round($gedung->total / $stats['total_bulan_ini'] * 100, 1) 
                                                : 0;
                                        @endphp
                                        <div class="progress" style="height: 18px; border-radius: .5rem;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%">
                                                {{ $percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
