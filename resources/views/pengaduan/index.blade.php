@extends('layouts.sfcs')

@section('title', 'Pengaduan Saya')

@section('content')
<div class="container-fluid px-0 px-md-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 pt-3 px-3 px-md-0">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Pengaduan Saya</h1>
            <p class="text-muted small mb-0">Pantau status laporan kerusakan fasilitas sekolah disini.</p>
        </div>
        {{-- Desktop Button --}}
        <a href="{{ route('pengaduan.create') }}" class="btn btn-primary d-none d-md-inline-flex align-items-center shadow-sm px-4 rounded-pill">
            <i class="fas fa-plus me-2"></i> Buat Pengaduan Baru
        </a>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4 mx-3 mx-md-0 rounded-4">
        <div class="card-body p-3">
            <form action="{{ route('pengaduan.index') }}" method="GET">
                <div class="row g-2 align-items-center">
                    {{-- Search --}}
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light rounded-start-pill ps-3"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-0 bg-light rounded-end-pill py-2" placeholder="Cari judul atau kode laporan..." value="{{ request('search') }}">
                        </div>
                    </div>
                    {{-- Filters --}}
                    <div class="col-6 col-md-3">
                        <select name="status" class="form-select border-0 bg-light rounded-pill py-2 text-muted" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Menunggu</option>
                            <option value="diverifikasi" {{ request('status') == 'diverifikasi' ? 'selected' : '' }}>✅ Diverifikasi</option>
                            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>🛠 Sedang Diproses</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>🎉 Selesai</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>❌ Ditolak</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                         <select name="kategori_id" class="form-select border-0 bg-light rounded-pill py-2 text-muted" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris ?? [] as $kategori)
                                <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Reset Button --}}
                    <div class="col-12 col-md-1 text-center">
                        <a href="{{ route('pengaduan.index') }}" class="btn btn-link text-muted text-decoration-none small">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Mobile View: Stacked Cards --}}
    <div class="d-md-none pb-5 text-decoration-none">
        @forelse($pengaduans as $pengaduan)
            <a href="{{ route('pengaduan.show', $pengaduan) }}" class="text-decoration-none text-dark">
                <div class="card border-0 shadow-sm mb-3 mx-3 rounded-4 overflow-hidden position-relative btn-reveal-trigger">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge rounded-pill 
                                {{ $pengaduan->status == 'pending' ? 'bg-warning text-dark' : '' }}
                                {{ $pengaduan->status == 'diverifikasi' ? 'bg-info text-white' : '' }}
                                {{ $pengaduan->status == 'diproses' ? 'bg-primary' : '' }}
                                {{ $pengaduan->status == 'selesai' ? 'bg-success' : '' }}
                                {{ $pengaduan->status == 'ditolak' ? 'bg-danger' : '' }}
                            fw-normal px-3 py-1">
                                @php
                                    $mobileStatusLabel = [
                                        'pending' => '⏳ Menunggu',
                                        'diverifikasi' => '✅ Diverifikasi',
                                        'diproses' => '🛠 Diproses',
                                        'selesai' => '🎉 Selesai',
                                        'ditolak' => '❌ Ditolak'
                                    ][$pengaduan->status] ?? ucfirst($pengaduan->status);
                                @endphp
                                {{ $mobileStatusLabel }}
                            </span>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ $pengaduan->created_at->diffForHumans() }}</small>
                        </div>
                        
                        <h5 class="card-title fw-bold mb-1 text-truncate">{{ $pengaduan->judul }}</h5>
                        <p class="text-muted small mb-3 text-truncate">{{ Str::limit($pengaduan->deskripsi, 60) }}</p>
                        
                        <div class="d-flex align-items-center justify-content-between bg-light rounded-3 p-2">
                            <div class="d-flex align-items-center text-muted small">
                                 <i class="fas fa-map-marker-alt me-2 text-danger"></i> 
                                 <span class="text-truncate" style="max-width: 120px;">
                                    {{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '-' }}
                                    @if($pengaduan->ruangan)
                                        - {{ $pengaduan->ruangan->nama }}
                                    @endif
                                 </span>
                            </div>
                            @if($pengaduan->photos->count() > 0)
                                <div class="d-flex align-items-center small text-primary fw-bold">
                                    <i class="fas fa-camera me-1"></i> {{ $pengaduan->photos->count() }}
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Status Indicator Line --}}
                    <div class="position-absolute start-0 top-0 bottom-0" style="width: 4px; background: 
                        {{ $pengaduan->status == 'pending' ? '#ffc107' : '' }}
                        {{ $pengaduan->status == 'diverifikasi' ? '#0dcaf0' : '' }}
                        {{ $pengaduan->status == 'diproses' ? '#0d6efd' : '' }}
                        {{ $pengaduan->status == 'selesai' ? '#198754' : '' }}
                        {{ $pengaduan->status == 'ditolak' ? '#dc3545' : '' }}">
                    </div>
                </div>
            </a>
        @empty
            <div class="text-center py-5 mx-3">
                <div class="mb-3">
                    <i class="fas fa-clipboard-list fa-3x text-light bg-secondary p-4 rounded-circle bg-opacity-10" style="color: #cbd5e1 !important;"></i>
                </div>
                <h5 class="fw-bold text-secondary">Belum ada laporan</h5>
                <p class="text-muted small">Kamu belum membuat pengaduan apapun. Jika melihat fasilitas rusak, lapor segera ya!</p>
                <a href="{{ route('pengaduan.create') }}" class="btn btn-primary rounded-pill px-4 mt-2">Buat Laporan Sekarang</a>
            </div>
        @endforelse
    </div>

    {{-- Desktop View: Table --}}
    <div class="d-none d-md-block card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-body p-0">
            @if($pengaduans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th class="border-0 py-3 ps-4" width="15%">Kode</th>
                                <th class="border-0 py-3" width="25%">Judul / Deskripsi</th>
                                <th class="border-0 py-3">Lokasi</th>
                                <th class="border-0 py-3">Kategori</th>
                                <th class="border-0 py-3">Status</th>
                                <th class="border-0 py-3 text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengaduans as $pengaduan)
                                <tr style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button') && !getSelection().toString()) window.location='{{ route('pengaduan.show', $pengaduan) }}'">
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark">#{{ $pengaduan->kode_pengaduan }}</span><br>
                                        <small class="text-muted">{{ $pengaduan->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark mb-1">{{ Str::limit($pengaduan->judul, 30) }}</div>
                                        <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ Str::limit($pengaduan->deskripsi, 50) }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-square bg-light text-primary rounded-2 me-2 p-1">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>
                                            <div>
                                                <div class="small fw-bold">{{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '-' }}</div>
                                                <small class="text-muted">
                                                    @if($pengaduan->ruangan)
                                                        {{ $pengaduan->ruangan->nama }}
                                                    @else
                                                        Lantai {{ $pengaduan->lantai ?? '-' }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-normal">
                                            {{ $pengaduan->kategori->nama ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusConfig = [
                                                'pending' => ['label' => '⏳ Menunggu', 'class' => 'bg-warning text-dark'],
                                                'diverifikasi' => ['label' => '✅ Diverifikasi', 'class' => 'bg-info text-white'],
                                                'diproses' => ['label' => '🛠 Diproses', 'class' => 'bg-primary'],
                                                'selesai' => ['label' => '🎉 Selesai', 'class' => 'bg-success'],
                                                'ditolak' => ['label' => '❌ Ditolak', 'class' => 'bg-danger'],
                                            ];
                                            $config = $statusConfig[$pengaduan->status] ?? ['label' => ucfirst($pengaduan->status), 'class' => 'bg-secondary'];
                                        @endphp
                                        <span class="badge {{ $config['class'] }} rounded-pill px-3">
                                            {{ $config['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('pengaduan.show', $pengaduan) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                <div class="card-footer bg-white border-0 py-3">
                    {{ $pengaduans->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
                    <p class="text-muted mb-3">Belum ada data pengaduan yang ditemukan.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Mobile Floating Action Button --}}
    <a href="{{ route('pengaduan.create') }}" class="btn btn-primary d-md-none rounded-circle shadow-lg position-fixed d-flex justify-content-center align-items-center text-white" 
       style="bottom: 25px; right: 25px; width: 56px; height: 56px; z-index: 1030; font-size: 24px;">
        <i class="fas fa-plus"></i>
    </a>
</div>
@endsection
