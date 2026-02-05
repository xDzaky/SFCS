@extends('layouts.sfcs')

@section('title', 'Laporan - Kepala Sekolah')

@section('content')
<div class="page-header">
    <h1 class="page-title">Laporan Pengaduan</h1>
    <p class="page-subtitle">Analisis dan statistik pengaduan fasilitas sekolah</p>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('kepsek.reports') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $periodStats['total'] }}</div>
                    <div class="stat-label">Total Pengaduan</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $periodStats['selesai'] }}</div>
                    <div class="stat-label">Selesai</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $periodStats['ditolak'] }}</div>
                    <div class="stat-label">Ditolak</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- By Gedung -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-building me-2"></i>Pengaduan per Gedung
            </div>
            <div class="card-body">
                @if($byGedung->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Gedung</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byGedung as $gedung)
                                    <tr>
                                        <td>{{ $gedung['nama'] }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">{{ $gedung['total'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">Tidak ada data</p>
                @endif
            </div>
        </div>
    </div>

    <!-- By Kategori -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-tags me-2"></i>Pengaduan per Kategori
            </div>
            <div class="card-body">
                @if($byKategori->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byKategori as $kategori)
                                    <tr>
                                        <td>{{ $kategori->nama }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">{{ $kategori->pengaduans_count }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">Tidak ada data</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Average Resolution Time -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i>Waktu Penyelesaian Rata-rata
            </div>
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-primary mb-2">
                    {{ $avgResolutionTime ? round($avgResolutionTime, 1) : 0 }}
                </div>
                <p class="text-muted mb-0">Hari</p>
            </div>
        </div>
    </div>
</div>

@endsection
