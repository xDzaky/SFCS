@extends('layouts.sfcs')

@section('title', 'Lacak Pengaduan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Search Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="mx-auto bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-search fa-3x text-primary"></i>
                    </div>
                    <h3 class="fw-bold">Lacak Status Pengaduan</h3>
                    <p class="text-muted mb-1">Cek progress pengaduan yang sudah kamu laporkan</p>
                    <div class="alert alert-info border-0 d-inline-flex align-items-center mt-2 py-2 px-3">
                        <i class="fas fa-lightbulb me-2"></i>
                        <small><strong>Sudah login?</strong> Langsung aja ke menu <a href="{{ route('pengaduan.index') }}" class="alert-link">Pengaduan Saya</a></small>
                    </div>
                </div>

                <form action="{{ route('pengaduan.track') }}" method="GET">
                    <div class="input-group input-group-lg mb-3">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-hashtag text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0 ps-0" 
                               name="kode" 
                               value="{{ request('kode') }}" 
                               placeholder="Contoh: ADU-20260207-001" 
                               required
                               autocomplete="off">
                        <button class="btn btn-primary px-4" type="submit">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                    <div class="form-text text-center">
                        <i class="fas fa-info-circle me-1"></i>
                        Kode pengaduan bisa kamu lihat di email konfirmasi atau halaman "Pengaduan Saya"
                    </div>
                </form>
            </div>
        </div>

        @if(isset($error_msg))
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <div class="mb-3">
                        <div class="mx-auto bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-search-minus fa-3x text-danger"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-danger mb-2">Oops! Tidak Ditemukan</h4>
                    <p class="text-muted mb-2 px-4">{{ $error_msg }}</p>
                    <p class="small text-muted">Pastikan kode yang kamu masukkan sudah benar (contoh: ADU-2023...). Coba periksa lagi ya!</p>
                </div>
            </div>
        @endif

        <!-- Result Card -->
        @if($pengaduan)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-clipboard-list me-2 text-primary"></i>
                            Detail Pengaduan
                        </h5>
                        <span class="badge bg-{{ $pengaduan->status == 'selesai' ? 'success' : ($pengaduan->status == 'ditolak' ? 'danger' : 'warning') }} rounded-pill px-3 py-2">
                            {{ strtoupper($pengaduan->status) }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <!-- Kode & Judul -->
                    <div class="mb-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                                <i class="fas fa-barcode fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1">Kode Pengaduan</small>
                                <h4 class="mb-0 fw-bold text-primary">{{ $pengaduan->kode_pengaduan }}</h4>
                            </div>
                        </div>
                        
                        <h5 class="fw-bold mb-2">{{ $pengaduan->judul }}</h5>
                        <p class="text-muted mb-0">{{ Str::limit($pengaduan->deskripsi, 200) }}</p>
                    </div>

                    <hr>

                    <!-- Info Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-tag fa-lg text-primary me-3"></i>
                                <div>
                                    <small class="text-muted d-block">Kategori</small>
                                    <strong>{{ $pengaduan->kategori->nama ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-building fa-lg text-primary me-3"></i>
                                <div>
                                    <small class="text-muted d-block">Lokasi</small>
                                    <strong>{{ $pengaduan->ruangan->gedung->nama ?? '-' }} - {{ $pengaduan->ruangan->nama ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-exclamation-circle fa-lg text-warning me-3"></i>
                                <div>
                                    <small class="text-muted d-block">Prioritas</small>
                                    <strong class="text-capitalize">{{ $pengaduan->prioritas }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-calendar fa-lg text-primary me-3"></i>
                                <div>
                                    <small class="text-muted d-block">Tanggal Dibuat</small>
                                    <strong>{{ $pengaduan->created_at->format('d M Y, H:i') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-history me-2"></i>Timeline Status
                        </h6>
                        
                        <div class="position-relative">
                            @php
                                $statuses = [
                                    'pending' => ['icon' => 'fa-paper-plane', 'label' => 'Terkirim', 'color' => 'secondary'],
                                    'diverifikasi' => ['icon' => 'fa-clipboard-check', 'label' => 'Diverifikasi', 'color' => 'info'],
                                    'diproses' => ['icon' => 'fa-tools', 'label' => 'Sedang Dikerjakan', 'color' => 'warning'],
                                    'selesai' => ['icon' => 'fa-check-circle', 'label' => 'Selesai', 'color' => 'success']
                                ];
                                
                                $currentIndex = array_search($pengaduan->status, array_keys($statuses));
                            @endphp
                            
                            @foreach($statuses as $key => $status)
                                @php
                                    $index = array_search($key, array_keys($statuses));
                                    $isActive = $index <= $currentIndex;
                                    $isCurrent = $key === $pengaduan->status;
                                @endphp
                                
                                <div class="d-flex align-items-start mb-3 position-relative">
                                    <div class="position-relative" style="z-index: 2;">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ $isActive ? 'bg-' . $status['color'] : 'bg-light' }} {{ $isActive ? 'text-white' : 'text-muted' }}" 
                                             style="width: 50px; height: 50px; {{ $isCurrent ? 'box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);' : '' }}">
                                            <i class="fas {{ $status['icon'] }} fa-lg"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="ms-3 flex-grow-1">
                                        <h6 class="mb-1 fw-bold {{ $isActive ? 'text-dark' : 'text-muted' }}">
                                            {{ $status['label'] }}
                                            @if($isCurrent)
                                                <span class="badge bg-primary ms-2">Saat Ini</span>
                                            @endif
                                        </h6>
                                        @if($isActive)
                                            <small class="text-muted">
                                                <i class="fas fa-check-circle me-1"></i>Sudah dilakukan
                                            </small>
                                        @else
                                            <small class="text-muted">Belum dilakukan</small>
                                        @endif
                                    </div>
                                </div>
                                
                                @if(!$loop->last)
                                    <div class="position-absolute" style="left: 24px; width: 2px; height: 30px; background: {{ $isActive ? '#dee2e6' : '#f3f4f6' }};"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-4">
                        @auth
                            @if(Auth::id() === $pengaduan->user_id)
                                <a href="{{ route('pengaduan.show', $pengaduan->kode_pengaduan) }}" class="btn btn-primary btn-lg rounded-pill">
                                    <i class="fas fa-eye me-2"></i>Lihat Detail Lengkap
                                </a>
                            @endif
                        @endauth
                        
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-lg rounded-pill">
                            <i class="fas fa-print me-2"></i>Cetak Bukti
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    @media print {
        .sidebar, .header, .btn, nav, .breadcrumb { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    }
</style>
@endpush

@endsection
