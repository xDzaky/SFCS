@extends('layouts.sfcs')

@section('title', 'Kelola Pengguna')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Kelola Pengguna</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengguna</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 w-100 w-lg-auto">
        <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center flex-fill flex-lg-grow-0" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import me-lg-2"></i> <span class="d-none d-lg-inline">Import</span>
        </button>
        <a href="{{ route('admin.users.export') }}" class="btn btn-outline-success d-flex align-items-center justify-content-center flex-fill flex-lg-grow-0">
            <i class="fas fa-file-export me-lg-2"></i> <span class="d-none d-lg-inline">Export</span>
        </a>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center flex-fill flex-lg-grow-0">
            <i class="fas fa-plus me-1"></i> Tambah
        </a>
    </div>
</div>

<!-- Filters (Collapsible on Mobile) -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom-0 p-3 pb-0 pb-lg-3 d-lg-none">
        <button class="btn btn-light w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <span class="fw-bold"><i class="fas fa-filter me-2"></i>Filter Data</span>
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>
    <div class="collapse d-lg-block" id="filterCollapse">
        <div class="card-body p-3 p-lg-4">
            <form action="{{ route('admin.users.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-12 col-lg-3">
                        <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Cari</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Nama, email..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Role</label>
                        <select name="role" class="form-select rounded-pill bg-light border-0">
                            <option value="">Semua Role</option>
                            <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="teknisi" {{ request('role') == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="kepsek" {{ request('role') == 'kepsek' ? 'selected' : '' }}>Kepala Sekolah</option>
                        </select>
                    </div>
                     <div class="col-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Status</label>
                        <select name="status" class="form-select rounded-pill bg-light border-0">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-2">
                        <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Kelas</label>
                        <input type="text" name="kelas" class="form-control rounded-pill bg-light border-0" placeholder="Contoh: X IPA 1" value="{{ request('kelas') }}">
                    </div>
                    <div class="col-12 col-lg-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill flex-grow-1 fw-bold shadow-sm">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light rounded-pill border data-bs-toggle="tooltip" title="Reset">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stats Cards (Scrollable horizontal on mobile) -->
<div class="d-flex flex-nowrap gap-3 overflow-auto pb-3 mb-2 d-lg-none" style="scrollbar-width: none; -ms-overflow-style: none;">
    @foreach([['total', 'Total', 'primary'], ['siswa', 'Siswa', 'info'], ['guru', 'Guru', 'success'], ['teknisi', 'Teknisi', 'warning'], ['admin', 'Admin', 'danger'], ['active', 'Aktif', 'secondary']] as $stat)
        <div class="card border-0 shadow-sm rounded-4 flex-shrink-0" style="min-width: 120px;">
            <div class="card-body p-3 text-center">
                <div class="h4 mb-0 fw-bold text-{{ $stat[2] }}">{{ $stats[$stat[0]] ?? 0 }}</div>
                <small class="text-muted fw-bold">{{ $stat[1] }}</small>
            </div>
        </div>
    @endforeach
</div>
<style>.d-flex::-webkit-scrollbar { display: none; }</style>

<!-- Stats Cards (Desktop Grid) -->
<div class="row g-3 mb-4 d-none d-lg-flex">
    @foreach([['total', 'Total', 'primary'], ['siswa', 'Siswa', 'info'], ['guru', 'Guru', 'success'], ['teknisi', 'Teknisi', 'warning'], ['admin', 'Admin', 'danger'], ['active', 'Aktif', 'secondary']] as $stat)
        <div class="col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                <div class="card-body py-3">
                    <div class="h4 mb-0 fw-bold text-{{ $stat[2] }}">{{ $stats[$stat[0]] ?? 0 }}</div>
                    <small class="text-muted fw-bold">{{ $stat[1] }}</small>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Mobile View: User Cards -->
<div class="d-lg-none">
    @forelse($users as $user)
        <div class="card border-0 shadow-sm rounded-4 mb-3" 
             style="cursor: pointer;" 
             onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location='{{ route('admin.users.show', $user) }}'">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar flex-shrink-0">
                         @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" class="rounded-circle shadow-sm" width="50" height="50" alt="" style="object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-muted fw-bold shadow-sm border" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-start">
                             <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $user->name }}</h6>
                             @if($user->is_active)
                                <i class="fas fa-check-circle text-success" title="Aktif"></i>
                             @else
                                <i class="fas fa-times-circle text-danger" title="Nonaktif"></i>
                            @endif
                        </div>
                        <div class="text-muted small text-truncate">{{ $user->email }}</div>
                         <div class="d-flex gap-2 mt-1">
                            @php
                                $roleBadges = [
                                    'superadmin' => 'bg-danger-subtle text-danger',
                                    'admin' => 'bg-warning-subtle text-warning-emphasis',
                                    'kepsek' => 'bg-info-subtle text-info',
                                    'teknisi' => 'bg-success-subtle text-success',
                                    'guru' => 'bg-primary-subtle text-primary',
                                    'siswa' => 'bg-secondary-subtle text-secondary',
                                ];
                            @endphp
                            <span class="badge {{ $roleBadges[$user->role] ?? 'bg-light text-dark' }} rounded-pill border border-light-subtle">
                                {{ ucfirst($user->role) }}
                            </span>
                            @if($user->kelas)
                            <span class="badge bg-light text-dark border rounded-pill">
                                {{ $user->kelas }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                 <div class="d-flex gap-2 pt-3 border-top border-light">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-light btn-sm flex-fill rounded-pill fw-bold text-muted">
                        <i class="fas fa-eye me-1"></i> Detail
                    </a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm flex-fill rounded-pill fw-bold">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                     @if($user->id !== auth()->id())
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;" onclick="if(confirm('Hapus pengguna ini?')) document.getElementById('delete-form-{{ $user->id }}').submit()">
                             <i class="fas fa-trash"></i>
                        </button>
                        <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-none">
                            @csrf @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <h5 class="text-muted">Tidak ada data pengguna</h5>
        </div>
    @endforelse
</div>

<!-- Desktop View: Users Table -->
<div class="card border-0 shadow-sm rounded-4 d-none d-lg-block overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1" width="50">#</th>
                        <th class="py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Pengguna</th>
                        <th class="py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Kontak</th>
                        <th class="py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Role / Kelas</th>
                        <th class="py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Status</th>
                         <th class="py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1">Joined</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase x-small fw-bold letter-spacing-1 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button') && !getSelection().toString()) window.location='{{ route('admin.users.show', $user) }}'">
                            <td class="ps-4 text-muted fw-bold">{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar">
                                        @if($user->avatar)
                                            <img src="{{ Storage::url($user->avatar) }}" class="rounded-circle shadow-sm" width="40" height="40" alt="" style="object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-muted fw-bold shadow-sm border" style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="text-muted small">NIS/NIP: {{ $user->nis_nip ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark small">{{ $user->email }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @php
                                    // Reusing badge logic to ensure consistency
                                    $roleBadges = [
                                        'superadmin' => 'bg-danger-subtle text-danger',
                                        'admin' => 'bg-warning-subtle text-warning-emphasis',
                                        'kepsek' => 'bg-info-subtle text-info',
                                        'teknisi' => 'bg-success-subtle text-success',
                                        'guru' => 'bg-primary-subtle text-primary',
                                        'siswa' => 'bg-secondary-subtle text-secondary',
                                    ];
                                    @endphp
                                    <span class="badge {{ $roleBadges[$user->role] ?? 'bg-secondary' }} rounded-pill border border-light-subtle align-self-start">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    @if($user->kelas)
                                        <span class="text-muted x-small ms-1">{{ $user->kelas }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success-subtle text-success rounded-pill border border-success-subtle">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill border border-danger-subtle">Inactive</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="pe-4 text-end">
                                <div class="btn-group">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-light btn-sm rounded-circle shadow-sm me-1" title="Lihat" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-light btn-sm rounded-circle shadow-sm me-1" title="Edit" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-edit text-info"></i>
                                    </a>
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-light btn-sm rounded-circle shadow-sm me-1" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }} text-{{ $user->is_active ? 'warning' : 'success' }}"></i>
                                        </button>
                                    </form>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm rounded-circle shadow-sm" title="Hapus" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-trash text-danger"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-3x mb-3 d-block opacity-25"></i>
                                    <h5 class="fw-bold">Belum ada data pengguna</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Import Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4 text-center">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-file-csv fa-3x text-success"></i>
                        </div>
                        <p class="text-muted small mb-0">Upload file CSV untuk menambahkan pengguna secara massal.</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">File CSV</label>
                        <input type="file" name="file" class="form-control" accept=".csv" required>
                    </div>
                    <div class="alert alert-light border rounded-3 small">
                        <div class="fw-bold mb-1"><i class="fas fa-info-circle me-1 text-primary"></i> Format CSV yang didukung:</div>
                        <code class="d-block bg-white p-2 border rounded text-muted">name, email, password, role, nis_nip, kelas</code>
                        <div class="mt-2 text-muted fst-italic">Contoh: John Doe, john@email.com, 123456, siswa, 101, X IPA 1</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-upload me-1"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
