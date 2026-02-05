@extends('layouts.sfcs')

@section('title', 'Laporan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Laporan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Laporan</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Report Type Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Laporan Pengaduan</h5>
                <p class="card-text text-muted">Lihat laporan pengaduan berdasarkan periode, kategori, dan status.</p>
                <a href="{{ route('admin.reports.pengaduan') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-1"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-success">
            <div class="card-body text-center">
                <i class="fas fa-chart-bar fa-3x text-success mb-3"></i>
                <h5 class="card-title">Laporan Performa</h5>
                <p class="card-text text-muted">Analisis performa teknisi, waktu penyelesaian, dan rating.</p>
                <a href="{{ route('admin.reports.performance') }}" class="btn btn-success">
                    <i class="fas fa-arrow-right me-1"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-info">
            <div class="card-body text-center">
                <i class="fas fa-download fa-3x text-info mb-3"></i>
                <h5 class="card-title">Export Data</h5>
                <p class="card-text text-muted">Download data pengaduan dalam format CSV untuk analisis lanjutan.</p>
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
        <h5 class="card-title mb-0">Statistik Bulan Ini</h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <h2 class="text-primary mb-0">{{ $stats['total_bulan_ini'] ?? 0 }}</h2>
                    <small class="text-muted">Pengaduan Masuk</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <h2 class="text-success mb-0">{{ $stats['selesai_bulan_ini'] ?? 0 }}</h2>
                    <small class="text-muted">Selesai</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <h2 class="text-warning mb-0">{{ $stats['proses_bulan_ini'] ?? 0 }}</h2>
                    <small class="text-muted">Dalam Proses</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <h2 class="text-info mb-0">{{ number_format($stats['rata_rating'] ?? 0, 1) }}</h2>
                    <small class="text-muted">Rata-rata Rating</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 5 Kategori (Bulan Ini)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%">
                                                {{ $percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        Belum ada data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 5 Gedung (Bulan Ini)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%">
                                                {{ $percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        Belum ada data
                                    </td>
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
