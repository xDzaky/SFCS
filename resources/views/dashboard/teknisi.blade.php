@extends('layouts.sfcs')

@section('title', 'Dashboard Teknisi')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Welcome Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-3 py-md-4 text-white">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-2 p-md-3 me-3">
                            <i class="fas fa-tools fs-4 fs-md-3"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fs-6 fs-md-5">Halo, {{ auth()->user()->name }}!</h5>
                            <p class="mb-0 opacity-90 small">Teknisi SFCS - Kelola tugas perbaikan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards - Mobile First -->
    <div class="row g-2 g-md-3 mb-3">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-tasks text-primary fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold fs-5 fs-md-4">{{ $stats['assigned'] }}</h4>
                    <p class="mb-0 text-muted small">Tugas Aktif</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-exclamation-triangle text-danger fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-danger fs-5 fs-md-4">{{ $stats['urgent'] }}</h4>
                    <p class="mb-0 text-muted small">Urgensi Tinggi</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-success fs-5 fs-md-4">{{ $stats['selesai_bulan_ini'] }}</h4>
                    <p class="mb-0 text-muted small">Selesai Bulan Ini</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Button -->
    <div class="row g-2 mb-3">
        <div class="col-12">
            <a href="{{ route('teknisi.pengaduan.index') }}" class="btn btn-primary btn-lg w-100 py-3 shadow-sm" style="border-radius: 15px;">
                <i class="fas fa-clipboard-list fs-4 me-2"></i>
                <span class="fs-5 fw-bold">LIHAT SEMUA TUGAS</span>
            </a>
        </div>
    </div>

    <!-- Assigned Pengaduans -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fs-6 fs-md-5">
                    <i class="fas fa-clipboard-list text-primary"></i> Tugas Saya
                </h5>
                <a href="{{ route('teknisi.pengaduan.index') }}" class="btn btn-sm btn-outline-primary">
                    Semua <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="card-body p-2 p-md-3">
            @if($assignedPengaduans->count() > 0)
                <!-- Mobile Card View -->
                <div class="d-md-none">
                    @foreach($assignedPengaduans as $pengaduan)
                        <div class="card mb-2 border shadow-sm {{ $pengaduan->prioritas === 'urgent' ? 'border-danger' : '' }}" 
                             style="cursor: pointer;" 
                             onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location='{{ route('teknisi.pengaduan.show', $pengaduan) }}'">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-light text-dark font-monospace small">
                                        {{ $pengaduan->kode_pengaduan }}
                                    </span>
                                    <div>
                                        @php
                                            $prioritasColors = [
                                                'urgent' => 'danger',
                                                'tinggi' => 'warning',
                                                'sedang' => 'info',
                                                'rendah' => 'secondary'
                                            ];
                                            $pColor = $prioritasColors[$pengaduan->prioritas] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $pColor }} small me-1">
                                            {{ ucfirst($pengaduan->prioritas) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <h6 class="mb-2 fw-bold">{{ Str::limit($pengaduan->judul, 50) }}</h6>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-tag me-1"></i>{{ $pengaduan->kategori->nama ?? '-' }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '-' }}
                                    </small>
                                </div>
                                
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
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-{{ $sColor }} small">
                                        {{ ucfirst($pengaduan->status) }}
                                    </span>
                                    <a href="{{ route('teknisi.pengaduan.show', $pengaduan) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-arrow-right"></i> Kerjakan
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
                                <th>Pelapor</th>
                                <th>Urgensi</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignedPengaduans as $pengaduan)
                                <tr class="{{ $pengaduan->prioritas === 'urgent' ? 'table-danger table-danger-soft' : '' }}" 
                                    style="cursor: pointer;" 
                                    onclick="if(!event.target.closest('a') && !event.target.closest('button') && !getSelection().toString()) window.location='{{ route('teknisi.pengaduan.show', $pengaduan) }}'">
                                    <td>
                                        <span class="badge bg-light text-dark font-monospace">{{ $pengaduan->kode_pengaduan }}</span>
                                    </td>
                                    <td>{{ Str::limit($pengaduan->judul, 35) }}</td>
                                    <td>{{ $pengaduan->kategori->nama ?? '-' }}</td>
                                    <td>
                                        <small>
                                            {{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '' }}<br>
                                            {{ $pengaduan->lokasi_detail ?? '' }}
                                        </small>
                                    </td>
                                    <td>{{ $pengaduan->user->name ?? '-' }}</td>
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
                                    <td class="text-center">
                                        <a href="{{ route('teknisi.pengaduan.show', $pengaduan) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle display-1 text-success opacity-25"></i>
                    <p class="text-muted mt-3">Tidak ada tugas yang ditugaskan saat ini</p>
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
    
    .table-danger-soft {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    .btn-lg {
        min-height: 50px;
    }
</style>
@endpush
@endsection
