@extends('layouts.sfcs')

@section('title', 'Notifikasi')

@push('styles')
<style>
/* ── Notification Index Page ── */
.bg-purple { background-color: #6f42c1 !important; }

.notif-list-item {
    display: flex;
    align-items: flex-start;
    padding: .9rem 1.1rem;
    border-bottom: 1px solid #f3f4f6;
    border-left: 4px solid transparent;
    text-decoration: none;
    color: inherit;
    transition: background .12s, transform .12s;
    position: relative;
    cursor: pointer;
}
.notif-list-item:last-child { border-bottom: none; }
.notif-list-item.is-unread {
    background: #eef2ff;
    border-left-color: var(--primary-color, #4f46e5);
}
.notif-list-item.is-read { background: #fff; }
.notif-list-item:hover { background: #f5f6ff; transform: translateX(3px); }
.notif-list-item.is-unread:hover { background: #e0e7ff; }

.notif-list-icon {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
    margin-right: .9rem;
}

.notif-list-title {
    font-size: .88rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}
.notif-list-item.is-read .notif-list-title {
    font-weight: 400;
    color: #4b5563;
}
.notif-list-msg { font-size: .8rem; color: #6b7280; line-height: 1.45; }
.notif-list-time { font-size: .73rem; color: #9ca3af; margin-top: 4px; }
.notif-unread-pill {
    background: #4f46e5;
    color: #fff;
    font-size: .67rem;
    padding: .2em .6em;
    border-radius: 9999px;
    font-weight: 600;
    white-space: nowrap;
}
</style>
@endpush

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
                @php
                    $isUnread  = !$notification->isRead();
                    $targetLink = $notification->link;

                    // Icon map
                    $iconMap = [
                        'pengaduan_created'  => ['fa-plus-circle',           'bg-success bg-opacity-10 text-success'],
                        'status_changed'     => ['fa-exchange-alt',           'bg-info bg-opacity-10 text-info'],
                        'assigned'           => ['fa-user-tag',               'bg-warning bg-opacity-10 text-warning'],
                        'feedback_reminder'  => ['fa-star',                   'bg-purple text-white'],
                        'overdue'            => ['fa-exclamation-triangle',   'bg-danger bg-opacity-10 text-danger'],
                        'pinjaman_created'   => ['fa-box-open',               'bg-primary bg-opacity-10 text-primary'],
                        'pinjaman_status'    => ['fa-arrow-right-arrow-left', 'bg-info bg-opacity-10 text-info'],
                        'priority_adjusted'  => ['fa-sliders',               'bg-warning bg-opacity-10 text-warning'],
                        'rescheduled'        => ['fa-calendar-days',          'bg-warning bg-opacity-10 text-warning'],
                        'overload_alert'     => ['fa-gauge-high',             'bg-danger bg-opacity-10 text-danger'],
                    ];
                    $iconInfo = $iconMap[$notification->jenis] ?? ['fa-bell', 'bg-secondary bg-opacity-10 text-secondary'];
                @endphp
                <div class="notif-list-item {{ $isUnread ? 'is-unread' : 'is-read' }}"
                     data-notification-id="{{ $notification->id }}"
                     data-link="{{ $targetLink ?? '' }}">
                    {{-- Invisible stretched link for native navigation --}}
                    @if($targetLink)
                        <a href="{{ $targetLink }}" class="stretched-link" style="text-decoration:none;"></a>
                    @endif

                    <div class="notif-list-icon {{ $iconInfo[1] }}">
                        <i class="fas {{ $iconInfo[0] }}"></i>
                    </div>

                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="notif-list-title">{{ $notification->judul }}</div>
                                <div class="notif-list-msg">{{ $notification->pesan }}</div>
                                <div class="notif-list-time">
                                    <i class="fas fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0" style="position:relative;z-index:2;">
                                @if($isUnread)
                                    <span class="notif-unread-pill">Baru</span>
                                @endif
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if($isUnread)
                                            <li>
                                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-check me-2"></i>Tandai Dibaca
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        @if($notification->link)
                                            <li>
                                                <a class="dropdown-item" href="{{ $notification->link }}">
                                                    <i class="fas fa-eye me-2"></i>Lihat Detail
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.notif-list-item[data-notification-id]').forEach(item => {
        const id   = item.dataset.notificationId;
        const link = item.dataset.link;

        item.addEventListener('click', function(e) {
            // Don't interfere with dropdown buttons
            if (e.target.closest('.dropdown')) return;

            // Mark as read via AJAX if unread
            if (item.classList.contains('is-unread')) {
                item.classList.remove('is-unread');
                item.classList.add('is-read');
                const pill = item.querySelector('.notif-unread-pill');
                if (pill) pill.remove();

                fetch(`/notifications/${id}/read`, {
                    method : 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                }).catch(() => {});
            }

            // Navigate
            if (link) window.location.href = link;
        });
    });
});
</script>
@endpush
