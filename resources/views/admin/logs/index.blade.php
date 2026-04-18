@extends('layouts.sfcs')

@section('title', 'Log Aktivitas')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Log Aktivitas</h1>
        <p class="page-subtitle">Rekaman aktivitas dan audit trail sistem</p>
    </div>
    <a href="{{ route('superadmin.logs.export', request()->query()) }}" class="btn btn-outline-success">
        <i class="fas fa-download me-1"></i><span class="d-none d-sm-inline">Export CSV</span>
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card border-0 h-100" style="background: #fee2e2;">
            <div class="card-body py-3">
                <small class="text-muted d-block">Failed Jobs (24 jam)</small>
                <div class="h4 mb-0 text-danger">{{ $errorStats['failed_jobs_24h'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 h-100" style="background: #fef9c3;">
            <div class="card-body py-3">
                <small class="text-muted d-block">Failed Jobs (7 hari)</small>
                <div class="h4 mb-0 text-warning">{{ $errorStats['failed_jobs_7d'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 h-100" style="background: #e0f2fe;">
            <div class="card-body py-3">
                <small class="text-muted d-block">Aktivitas (24 jam)</small>
                <div class="h4 mb-0 text-info">{{ $errorStats['activity_24h'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4 {{ request()->hasAny(['search','action','user_id','date_from','date_to']) ? 'border-primary' : '' }}">
    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
        <span class="fw-semibold small"><i class="fas fa-filter me-2 text-muted"></i>Filter Log</span>
        <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#logFilterBody">
            <i class="fas fa-chevron-down me-1"></i>Tampilkan
        </button>
    </div>
    <div class="collapse{{ request()->hasAny(['search','action','user_id','date_from','date_to']) ? ' show' : '' }} d-md-block" id="logFilterBody">
        <div class="card-body pt-2">
            <form action="{{ route('superadmin.logs.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-12 col-md-3">
                        <label class="form-label small mb-1">Cari</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari aktivitas..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">Aksi</label>
                        <select name="action" class="form-select form-select-sm">
                            <option value="">Semua Aksi</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">User ID</label>
                        <input type="text" name="user_id" class="form-control form-control-sm" placeholder="User ID" value="{{ request('user_id') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">Dari</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">Sampai</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12 col-md-1 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="fas fa-search"></i></button>
                        <a href="{{ route('superadmin.logs.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="180">Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Pengaduan</th>
                        <th>Deskripsi</th>
                        <th width="100">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <small>
                                    {{ $log->created_at->format('d/m/Y') }}<br>
                                    <span class="text-muted">{{ $log->created_at->format('H:i:s') }}</span>
                                </small>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-2" style="width: 30px; height: 30px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                            {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <small class="d-block">{{ $log->user->name }}</small>
                                            <small class="text-muted">{{ $log->user->role }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $actionBadges = [
                                        'created' => 'bg-success',
                                        'status_changed' => 'bg-warning text-dark',
                                        'assigned' => 'bg-info',
                                        'photo_uploaded' => 'bg-primary',
                                        'feedback_given' => 'bg-success',
                                        'reopened' => 'bg-danger',
                                        'duplicate_auto_closed' => 'bg-info text-dark',
                                    ];
                                @endphp
                                <span class="badge {{ $actionBadges[$log->action] ?? 'bg-secondary' }}">
                                    {{ $log->action_display ?? ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td>
                                @if($log->pengaduan)
                                    <a href="{{ route('admin.pengaduan.show', $log->pengaduan) }}" class="text-decoration-none">
                                        {{ $log->pengaduan->kode_pengaduan }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($log->description || $log->old_value || $log->new_value)
                                    <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#logDetailModal{{ $log->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Detail Modal -->
                        @if($log->old_value || $log->new_value)
                            <div class="modal fade" id="logDetailModal{{ $log->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detail Log</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <strong>Waktu:</strong><br>
                                                    {{ $log->created_at->format('d F Y H:i:s') }}
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong>User:</strong><br>
                                                    {{ $log->user->name ?? '-' }}
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong>Aksi:</strong><br>
                                                    {{ $log->action_display ?? ucfirst(str_replace('_', ' ', $log->action)) }}
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong>Pengaduan:</strong><br>
                                                    #{{ $log->pengaduan_id ?? '-' }}
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Deskripsi:</strong><br>
                                                {{ $log->description }}
                                            </div>
                                            
                                            @if($log->ip_address)
                                                <div class="mb-3">
                                                    <strong>IP Address:</strong><br>
                                                    <code>{{ $log->ip_address }}</code>
                                                </div>
                                            @endif
                                            
                                            @if($log->old_value)
                                                <div class="mb-3">
                                                    <strong>Nilai Lama:</strong>
                                                    <pre class="bg-light p-3 rounded mt-2"><code>{{ json_encode($log->old_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                </div>
                                            @endif
                                            
                                            @if($log->new_value)
                                                <div class="mb-3">
                                                    <strong>Nilai Baru:</strong>
                                                    <pre class="bg-light p-3 rounded mt-2"><code>{{ json_encode($log->new_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-history fa-3x mb-3 d-block"></i>
                                    Belum ada log aktivitas
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
