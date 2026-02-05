@extends('layouts.sfcs')

@section('title', 'Notifikasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Notifikasi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Notifikasi</li>
            </ol>
        </nav>
    </div>
    @if($notifications->where('read_at', null)->count() > 0)
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
            </button>
        </form>
    @endif
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-primary">{{ $notifications->total() }}</div>
                <small class="text-muted">Total Notifikasi</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-warning">{{ $unreadCount ?? 0 }}</div>
                <small class="text-muted">Belum Dibaca</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-success">{{ ($notifications->total()) - ($unreadCount ?? 0) }}</div>
                <small class="text-muted">Sudah Dibaca</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('notifications.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Belum Dibaca</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Sudah Dibaca</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipe</label>
                    <select name="type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="pengaduan" {{ request('type') == 'pengaduan' ? 'selected' : '' }}>Pengaduan</option>
                        <option value="status_update" {{ request('type') == 'status_update' ? 'selected' : '' }}>Update Status</option>
                        <option value="assignment" {{ request('type') == 'assignment' ? 'selected' : '' }}>Penugasan</option>
                        <option value="feedback" {{ request('type') == 'feedback' ? 'selected' : '' }}>Feedback</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Notifications List -->
<div class="card">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <div class="list-group-item {{ !$notification->read_at ? 'bg-light' : '' }}">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            @php
                                $iconClass = 'fa-bell';
                                $iconBg = 'bg-primary';
                                
                                if(isset($notification->data['type'])) {
                                    switch($notification->data['type']) {
                                        case 'pengaduan_baru':
                                            $iconClass = 'fa-plus-circle';
                                            $iconBg = 'bg-success';
                                            break;
                                        case 'status_update':
                                            $iconClass = 'fa-sync';
                                            $iconBg = 'bg-info';
                                            break;
                                        case 'assignment':
                                            $iconClass = 'fa-user-check';
                                            $iconBg = 'bg-warning';
                                            break;
                                        case 'feedback':
                                            $iconClass = 'fa-star';
                                            $iconBg = 'bg-purple';
                                            break;
                                        case 'rejected':
                                            $iconClass = 'fa-times-circle';
                                            $iconBg = 'bg-danger';
                                            break;
                                        case 'completed':
                                            $iconClass = 'fa-check-circle';
                                            $iconBg = 'bg-success';
                                            break;
                                    }
                                }
                            @endphp
                            <div class="rounded-circle {{ $iconBg }} text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas {{ $iconClass }}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 {{ !$notification->read_at ? 'fw-bold' : '' }}">
                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                    </h6>
                                    <p class="mb-1 text-muted">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="d-flex align-items-center">
                                    @if(!$notification->read_at)
                                        <span class="badge bg-warning me-2">Baru</span>
                                    @endif
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if(!$notification->read_at)
                                                <li>
                                                    <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-check me-2"></i> Tandai Dibaca
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if(isset($notification->data['pengaduan_id']))
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('pengaduan.show', $notification->data['pengaduan_id']) }}">
                                                        <i class="fas fa-eye me-2"></i> Lihat Pengaduan
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-bell-slash fa-3x mb-3 d-block"></i>
                        <h5>Tidak Ada Notifikasi</h5>
                        <p class="mb-0">Anda belum memiliki notifikasi.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
    @if($notifications->hasPages())
        <div class="card-footer">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
</style>
@endpush
