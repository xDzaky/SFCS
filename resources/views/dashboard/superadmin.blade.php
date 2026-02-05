@extends('layouts.sfcs')

@section('title', 'Dashboard Super Admin')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Welcome Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-3 py-md-4 text-white">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-2 p-md-3 me-3">
                            <i class="fas fa-crown fs-4 fs-md-3"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fs-6 fs-md-5">Dashboard Super Admin</h5>
                            <p class="mb-0 opacity-90 small">Kontrol penuh sistem SFCS</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards - Mobile First Grid -->
    <div class="row g-2 g-md-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-users text-primary fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold fs-5 fs-md-4">{{ $stats['total_users'] }}</h4>
                    <p class="mb-0 text-muted small">User</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-user-check text-success fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-success fs-5 fs-md-4">{{ $stats['active_users'] }}</h4>
                    <p class="mb-0 text-muted small">Aktif</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-clipboard-list text-info fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-info fs-5 fs-md-4">{{ $stats['total_pengaduan'] }}</h4>
                    <p class="mb-0 text-muted small">Pengaduan</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
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

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-building text-danger fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-danger fs-5 fs-md-4">{{ $stats['total_gedung'] }}</h4>
                    <p class="mb-0 text-muted small">Gedung</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-2 p-md-3">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex p-2 mb-2">
                        <i class="fas fa-tags text-secondary fs-5"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-secondary fs-5 fs-md-4">{{ $stats['total_kategori'] }}</h4>
                    <p class="mb-0 text-muted small">Kategori</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 g-md-3">
        <!-- Users by Role -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fs-6">
                        <i class="fas fa-users text-primary"></i> User per Role
                    </h6>
                </div>
                <div class="card-body p-3">
                    @foreach($usersByRole as $role)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <span class="text-capitalize fw-semibold small">{{ $role->role }}</span>
                            <span class="badge bg-primary">{{ $role->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fs-6">
                        <i class="fas fa-bolt text-warning"></i> Aksi Cepat
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Tambah User
                        </a>
                        <a href="{{ route('admin.kategoris.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-tags me-2"></i>Kelola Kategori
                        </a>
                        <a href="{{ route('admin.gedungs.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-building me-2"></i>Kelola Gedung
                        </a>
                        <a href="{{ route('superadmin.settings.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-cog me-2"></i>Pengaturan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fs-6">
                            <i class="fas fa-history text-info"></i> Aktivitas
                        </h6>
                        <a href="{{ route('superadmin.logs.index') }}" class="btn btn-sm btn-outline-primary">
                            Semua <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentLogs as $log)
                            <div class="list-group-item py-2 px-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="badge bg-primary small">{{ $log->action }}</span>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </div>
                                <small class="text-muted">{{ Str::limit($log->description ?? 'Activity logged', 40) }}</small>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="fas fa-inbox fs-4 d-block mb-2 opacity-25"></i>
                                <small>Belum ada aktivitas</small>
                            </div>
                        @endforelse
                    </div>
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
    
    .list-group-item {
        transition: background-color 0.2s;
    }
    
    .list-group-item:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
</style>
@endpush
@endsection
