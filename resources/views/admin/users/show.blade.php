@extends('layouts.sfcs')

@section('title', 'Detail Pengguna')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">Detail Pengguna</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Pengguna</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card mb-4">
            <div class="card-body text-center">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" class="rounded-circle img-thumbnail mb-3" width="150" height="150" alt="{{ $user->name }}">
                @else
                    <div class="avatar mx-auto mb-3" style="width: 150px; height: 150px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 48px;">
                        <span class="text-muted">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif

                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>

                @php
                    $roleBadges = [
                        'superadmin' => 'bg-danger',
                        'admin' => 'bg-warning text-dark',
                        'kepsek' => 'bg-info',
                        'teknisi' => 'bg-success',
                        'guru' => 'bg-primary',
                        'siswa' => 'bg-secondary',
                    ];
                @endphp
                <span class="badge {{ $roleBadges[$user->role] ?? 'bg-secondary' }} mb-3">
                    {{ ucfirst($user->role) }}
                </span>

                @if($user->is_active)
                    <span class="badge bg-success mb-3">Aktif</span>
                @else
                    <span class="badge bg-danger mb-3">Nonaktif</span>
                @endif

                <hr>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $user->is_active ? 'warning' : 'success' }}">
                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }} me-1"></i>
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Kontak</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td width="40"><i class="fas fa-envelope text-muted"></i></td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    @if($user->phone)
                        <tr>
                            <td><i class="fas fa-phone text-muted"></i></td>
                            <td>{{ $user->phone }}</td>
                        </tr>
                    @endif
                    @if($user->address)
                        <tr>
                            <td><i class="fas fa-map-marker-alt text-muted"></i></td>
                            <td>{{ $user->address }}</td>
                        </tr>
                    @endif
                    @if($user->nis_nip)
                        <tr>
                            <td><i class="fas fa-id-card text-muted"></i></td>
                            <td>{{ $user->nis_nip }}</td>
                        </tr>
                    @endif
                    @if($user->kelas)
                        <tr>
                            <td><i class="fas fa-school text-muted"></i></td>
                            <td>{{ $user->kelas }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Account Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Akun</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Terdaftar Pada</label>
                        <p class="mb-0">{{ $user->created_at->format('d F Y H:i') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Terakhir Diperbarui</label>
                        <p class="mb-0">{{ $user->updated_at->format('d F Y H:i') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status Email</label>
                        <p class="mb-0">
                            @if($user->email_verified_at)
                                <span class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Terverifikasi pada {{ $user->email_verified_at->format('d F Y') }}
                                </span>
                            @else
                                <span class="text-danger">
                                    <i class="fas fa-times-circle me-1"></i>
                                    Belum Terverifikasi
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Status Akun</label>
                        <p class="mb-0">
                            @if($user->is_active)
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i> Aktif</span>
                            @else
                                <span class="text-danger"><i class="fas fa-times-circle me-1"></i> Nonaktif</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistik Aktivitas</h5>
            </div>
            <div class="card-body">
                @if($user->role === 'teknisi')
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-primary mb-0">{{ $user->assignedPengaduans->count() }}</h3>
                                <small class="text-muted">Total Ditugaskan</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-warning mb-0">{{ $user->assignedPengaduans->where('status', 'dikerjakan')->count() }}</h3>
                                <small class="text-muted">Sedang Dikerjakan</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-success mb-0">{{ $user->assignedPengaduans->where('status', 'selesai')->count() }}</h3>
                                <small class="text-muted">Selesai</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                @php
                                    $feedbacks = $user->assignedPengaduans
                                        ->filter(function($pengaduan) { return $pengaduan->feedback !== null; })
                                        ->map(function($pengaduan) { return $pengaduan->feedback->average_rating; });
                                    $avgRating = $feedbacks->count() > 0 ? $feedbacks->avg() : 0;
                                @endphp
                                <h3 class="text-info mb-0">{{ number_format($avgRating, 1) }}</h3>
                                <small class="text-muted">Rata-rata Rating</small>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-primary mb-0">{{ $user->pengaduans->count() }}</h3>
                                <small class="text-muted">Total Pengaduan</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-warning mb-0">{{ $user->pengaduans->whereIn('status', ['menunggu', 'ditugaskan', 'dikerjakan'])->count() }}</h3>
                                <small class="text-muted">Dalam Proses</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-success mb-0">{{ $user->pengaduans->where('status', 'selesai')->count() }}</h3>
                                <small class="text-muted">Selesai</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-danger mb-0">{{ $user->pengaduans->where('status', 'ditolak')->count() }}</h3>
                                <small class="text-muted">Ditolak</small>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Pengaduan -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    {{ $user->role === 'teknisi' ? 'Pengaduan Ditugaskan' : 'Pengaduan Terbaru' }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $pengaduans = $user->role === 'teknisi' 
                                    ? $user->assignedPengaduans()->latest()->limit(5)->get()
                                    : $user->pengaduans()->latest()->limit(5)->get();
                            @endphp
                            @forelse($pengaduans as $pengaduan)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($pengaduan->judul, 40) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $pengaduan->subKategori->nama ?? '-' }}</small>
                                    </td>
                                    <td>{{ $pengaduan->created_at->format('d/m/Y') }}</td>
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
                                        <a href="{{ route('admin.pengaduan.show', $pengaduan) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">
                                        Belum ada pengaduan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Pengguna
    </a>
</div>
@endsection
