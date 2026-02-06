@extends('layouts.sfcs')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Welcome Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-3 py-md-4 text-white">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-2 p-md-3 me-3">
                            <i class="fas fa-user-shield fs-4 fs-md-3"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fs-6 fs-md-5">Dashboard Admin</h5>
                            <p class="mb-0 opacity-90 small">Kelola sistem pengaduan fasilitas</p>
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
                    <p class="mb-0 text-muted small">Pending</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-spinner text-info fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-info fs-5 fs-md-4">{{ $stats['proses'] }}</h4>
                    <p class="mb-0 text-muted small">Proses</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-exclamation-triangle text-danger fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-danger fs-5 fs-md-4">{{ $stats['overdue'] ?? 0 }}</h4>
                    <p class="mb-0 text-muted small">Overdue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Button -->
    <div class="row g-2 mb-3">
        <div class="col-12">
            <a href="{{ route('admin.pengaduan.index') }}" class="btn btn-primary btn-lg w-100 py-3 shadow-sm" style="border-radius: 15px;">
                <i class="fas fa-list fs-4 me-2"></i>
                <span class="fs-5 fw-bold">KELOLA PENGADUAN</span>
            </a>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-2 g-md-3 mb-3">
        <!-- By Kategori Chart -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fs-6">
                        <i class="fas fa-chart-pie text-primary"></i> Per Kategori
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if($byKategori->count() > 0)
                        @foreach($byKategori as $kategori)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold">{{ $kategori->nama }}</span>
                                    <span class="badge bg-primary">{{ $kategori->pengaduans_count }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    @php
                                        $percentage = $stats['total'] > 0 ? ($kategori->pengaduans_count / $stats['total']) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center small">Belum ada data</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- By Gedung -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fs-6">
                        <i class="fas fa-building text-success"></i> Per Gedung
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if($byGedung->count() > 0)
                        @foreach($byGedung as $gedung)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold">{{ $gedung['nama'] }}</span>
                                    <span class="badge bg-success">{{ $gedung['total'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    @php
                                        $percentage = $stats['total'] > 0 ? ($gedung['total'] / $stats['total']) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center small">Belum ada data</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 g-md-3">
        <!-- Recent Pengaduans -->
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fs-6">
                            <i class="fas fa-list text-primary"></i> Pengaduan Terbaru
                        </h6>
                        <a href="{{ route('admin.pengaduan.index') }}" class="btn btn-sm btn-outline-primary">
                            Semua <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-2 p-md-3">
                    @forelse($recentPengaduans as $pengaduan)
                        <!-- Mobile Card View -->
                        <div class="d-md-none card mb-2 border hover-clickable" 
                             style="cursor: pointer;" 
                             onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location='{{ route('admin.pengaduan.show', $pengaduan) }}'">
                            <div class="card-body p-2">
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
                                <h6 class="mb-1 small fw-bold">{{ Str::limit($pengaduan->judul, 40) }}</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $pengaduan->user->name ?? '-' }}</small>
                                    <a href="{{ route('admin.pengaduan.show', $pengaduan) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-muted small mb-0">Belum ada pengaduan</p>
                        </div>
                    @endforelse

                    <!-- Desktop Table View -->
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Judul</th>
                                    <th>Pelapor</th>
                                    <th>Status</th>
                                    <th>Urgensi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPengaduans as $pengaduan)
                                <tr style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button') && !getSelection().toString()) window.location='{{ route('admin.pengaduan.show', $pengaduan) }}'">
                                        <td><span class="badge bg-light text-dark font-monospace">{{ $pengaduan->kode_pengaduan }}</span></td>
                                        <td>{{ Str::limit($pengaduan->judul, 30) }}</td>
                                        <td>{{ $pengaduan->user->name ?? '-' }}</td>
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
                                        <td>
                                            @php
                                                $prioritasColors = [
                                                    'urgent' => 'danger',
                                                    'tinggi' => 'warning',
                                                    'sedang' => 'info',
                                                    'rendah' => 'secondary'
                                                ];
                                                $pColor = $prioritasColors[$pengaduan->prioritas] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $pColor }}">
                                                {{ ucfirst($pengaduan->prioritas) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.pengaduan.show', $pengaduan) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada pengaduan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teknisi Status -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fs-6">
                        <i class="fas fa-users-cog text-info"></i> Status Teknisi
                    </h6>
                </div>
                <div class="card-body p-3">
                    @forelse($teknisis as $teknisi)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                     style="width: 32px; height: 32px; font-size: 0.75rem;">
                                    {{ strtoupper(substr($teknisi->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-medium small">{{ $teknisi->name }}</div>
                                </div>
                            </div>
                            <span class="badge {{ $teknisi->assigned_pengaduans_count > 5 ? 'bg-danger' : ($teknisi->assigned_pengaduans_count > 2 ? 'bg-warning' : 'bg-success') }}">
                                {{ $teknisi->assigned_pengaduans_count }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted text-center small">Belum ada teknisi</p>
                    @endforelse
                </div>
            </div>
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
