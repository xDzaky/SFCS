@extends('layouts.sfcs')

@section('title', 'Laporan Performa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Laporan Performa</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Laporan</a></li>
                <li class="breadcrumb-item active">Performa</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.reports.export', ['type' => 'performance'] + request()->all()) }}" class="btn btn-success">
        <i class="fas fa-download me-1"></i> Export CSV
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reports.performance') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Teknisi</label>
                    <select name="teknisi_id" class="form-select">
                        <option value="">Semua Teknisi</option>
                        @foreach($teknisis ?? [] as $teknisi)
                            <option value="{{ $teknisi->id }}" {{ request('teknisi_id') == $teknisi->id ? 'selected' : '' }}>
                                {{ $teknisi->name }}
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
    <div class="col-md-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $summary['total_selesai'] ?? 0 }}</h3>
                <small>Total Selesai</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $summary['avg_resolution_time'] ?? '-' }}</h3>
                <small>Rata-rata Waktu (Jam)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-warning text-dark">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ number_format($summary['avg_rating'] ?? 0, 1) }}</h3>
                <small>Rata-rata Rating</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body py-3">
                <h3 class="mb-0">{{ $summary['sla_compliance'] ?? 0 }}%</h3>
                <small>SLA Compliance</small>
            </div>
        </div>
    </div>
</div>

<!-- Teknisi Performance Table -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Performa Teknisi</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Teknisi</th>
                        <th class="text-center">Ditugaskan</th>
                        <th class="text-center">Dikerjakan</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Rata-rata Waktu</th>
                        <th class="text-center">Rating</th>
                        <th class="text-center">SLA %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teknisiPerformance ?? [] as $perf)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2" style="width: 35px; height: 35px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        {{ strtoupper(substr($perf['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $perf['name'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $perf['email'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $perf['ditugaskan'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $perf['dikerjakan'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $perf['selesai'] }}</span>
                            </td>
                            <td class="text-center">
                                {{ $perf['avg_time'] ?? '-' }} jam
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($perf['rating'] ?? 0))
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            <i class="far fa-star text-muted"></i>
                                        @endif
                                    @endfor
                                    <span class="ms-1">({{ number_format($perf['rating'] ?? 0, 1) }})</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $sla = $perf['sla_percentage'] ?? 0;
                                    $slaClass = $sla >= 90 ? 'success' : ($sla >= 70 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $slaClass }}">{{ $sla }}%</span>
                            </td>
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
</div>

<div class="row">
    <!-- Performance by Category -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Waktu Penyelesaian per Kategori</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryTimeChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Rating Distribution -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribusi Rating</h5>
            </div>
            <div class="card-body">
                <canvas id="ratingChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- SLA Trend -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Trend SLA Compliance</h5>
    </div>
    <div class="card-body">
        <canvas id="slaTrendChart" height="100"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category Time Chart
    new Chart(document.getElementById('categoryTimeChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(($categoryTime ?? collect())->pluck('nama')) !!},
            datasets: [{
                label: 'Rata-rata Jam',
                data: {!! json_encode(($categoryTime ?? collect())->pluck('avg_hours')) !!},
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
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jam'
                    }
                }
            }
        }
    });

    // Rating Distribution Chart
    new Chart(document.getElementById('ratingChart'), {
        type: 'doughnut',
        data: {
            labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
            datasets: [{
                data: {!! json_encode(array_values($ratingDistribution ?? [0,0,0,0,0])) !!},
                backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754']
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

    // SLA Trend Chart
    new Chart(document.getElementById('slaTrendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(($slaTrend ?? collect())->pluck('date')) !!},
            datasets: [{
                label: 'SLA %',
                data: {!! json_encode(($slaTrend ?? collect())->pluck('percentage')) !!},
                borderColor: '#198754',
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
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Persentase'
                    }
                }
            }
        }
    });
});
</script>
@endpush
