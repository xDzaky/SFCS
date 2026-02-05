@extends('layouts.sfcs')

@section('title', 'Laporan Pengaduan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Laporan Pengaduan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Laporan</a></li>
                <li class="breadcrumb-item active">Pengaduan</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.reports.export', ['type' => 'pengaduan'] + request()->all()) }}" class="btn btn-success">
        <i class="fas fa-download me-1"></i> Export CSV
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reports.pengaduan') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="ditugaskan" {{ request('status') == 'ditugaskan' ? 'selected' : '' }}>Ditugaskan</option>
                        <option value="dikerjakan" {{ request('status') == 'dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kategori</label>
                    <select name="kategori_id" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris ?? [] as $kategori)
                            <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Gedung</label>
                    <select name="gedung_id" class="form-select">
                        <option value="">Semua Gedung</option>
                        @foreach($gedungs ?? [] as $gedung)
                            <option value="{{ $gedung->id }}" {{ request('gedung_id') == $gedung->id ? 'selected' : '' }}>
                                {{ $gedung->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card text-center bg-primary text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $summary['total'] ?? 0 }}</h3>
                <small>Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center bg-secondary text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $summary['menunggu'] ?? 0 }}</h3>
                <small>Menunggu</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center bg-info text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $summary['ditugaskan'] ?? 0 }}</h3>
                <small>Ditugaskan</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center bg-warning text-dark">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $summary['dikerjakan'] ?? 0 }}</h3>
                <small>Dikerjakan</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center bg-success text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $summary['selesai'] ?? 0 }}</h3>
                <small>Selesai</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center bg-danger text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $summary['ditolak'] ?? 0 }}</h3>
                <small>Ditolak</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Chart by Status -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribusi Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart by Kategori -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Berdasarkan Kategori</h5>
            </div>
            <div class="card-body">
                <canvas id="kategoriChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Trend Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Trend Pengaduan</h5>
    </div>
    <div class="card-body">
        <canvas id="trendChart" height="100"></canvas>
    </div>
</div>

<!-- Detailed Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Detail Pengaduan</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Prioritas</th>
                        <th>Teknisi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengaduans ?? [] as $pengaduan)
                        <tr>
                            <td>{{ $pengaduan->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.pengaduan.show', $pengaduan) }}">
                                    {{ Str::limit($pengaduan->judul, 30) }}
                                </a>
                            </td>
                            <td>{{ $pengaduan->kategori->nama ?? '-' }}</td>
                            <td>{{ $pengaduan->gedung->kode ?? '-' }} - {{ $pengaduan->lokasi_detail ?? '-' }}</td>
                            <td>
                                @php
                                    $statusBadges = [
                                        'menunggu' => 'bg-secondary',
                                        'ditugaskan' => 'bg-info',
                                        'dikerjakan' => 'bg-warning text-dark',
                                        'selesai' => 'bg-success',
                                        'ditolak' => 'bg-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusBadges[$pengaduan->status] ?? 'bg-secondary' }}">
                                    {{ ucfirst($pengaduan->status) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $prioritasBadges = [
                                        'rendah' => 'bg-success',
                                        'sedang' => 'bg-warning text-dark',
                                        'tinggi' => 'bg-orange',
                                        'urgent' => 'bg-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $prioritasBadges[$pengaduan->prioritas] ?? 'bg-secondary' }}">
                                    {{ ucfirst($pengaduan->prioritas) }}
                                </span>
                            </td>
                            <td>{{ $pengaduan->teknisi->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Tidak ada data untuk periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($pengaduans) && $pengaduans->hasPages())
        <div class="card-footer">
            {{ $pengaduans->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Menunggu', 'Ditugaskan', 'Dikerjakan', 'Selesai', 'Ditolak'],
            datasets: [{
                data: [
                    {{ $summary['menunggu'] ?? 0 }},
                    {{ $summary['ditugaskan'] ?? 0 }},
                    {{ $summary['dikerjakan'] ?? 0 }},
                    {{ $summary['selesai'] ?? 0 }},
                    {{ $summary['ditolak'] ?? 0 }}
                ],
                backgroundColor: ['#6c757d', '#0dcaf0', '#ffc107', '#198754', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Kategori Chart
    new Chart(document.getElementById('kategoriChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(($byKategori ?? collect())->pluck('nama')) !!},
            datasets: [{
                label: 'Jumlah Pengaduan',
                data: {!! json_encode(($byKategori ?? collect())->pluck('total')) !!},
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(($trend ?? collect())->pluck('date')) !!},
            datasets: [{
                label: 'Pengaduan',
                data: {!! json_encode(($trend ?? collect())->pluck('total')) !!},
                borderColor: '#0d6efd',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
