@extends('layouts.sfcs')

@section('title', 'Detail Pengaduan')

@section('breadcrumb')
<ol class="breadcrumb mb-0 small">
    <li class="breadcrumb-item"><a href="{{ route('pengaduan.index') }}">Pengaduan</a></li>
    <li class="breadcrumb-item active">{{ $pengaduan->kode_pengaduan }}</li>
</ol>
@endsection

@section('content')
{{-- Mobile-First Design: Detail Pengaduan --}}

{{-- Header Section (Sticky on Mobile) --}}
{{-- Header Section (Card Style) --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        {{-- Top Row: ID, Date, and Desktop Back Button --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
            <div class="d-flex align-items-center gap-2 mb-2 mb-md-0">
                <span class="badge bg-light text-primary border border-primary-subtle fw-bold px-3 py-2 rounded-pill">
                    <i class="fas fa-hashtag me-1"></i>{{ $pengaduan->kode_pengaduan }}
                </span>
                <span class="text-muted small">
                    <i class="far fa-clock me-1"></i>{{ $pengaduan->created_at->format('d M Y, H:i') }}
                </span>
            </div>
            
            {{-- Desktop Back Button --}}
            <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary rounded-pill px-4 d-none d-md-inline-flex align-items-center transition-all hover-shadow">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        {{-- Title --}}
        <h1 class="display-6 fw-bold text-dark mb-4">{{ $pengaduan->judul }}</h1>

        {{-- Bottom Row: Badges and Action Buttons --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-3">
            
            {{-- Badges Group --}}
            <div class="d-flex flex-wrap gap-2">
                @php
                    $priorityIcons = [
                        'rendah' => '🟢',
                        'sedang' => '🟡',
                        'tinggi' => '🟠',
                        'urgent' => '🔴'
                    ];
                    $statusIcons = [
                        'pending' => '⏳',
                        'diverifikasi' => '✅',
                        'diproses' => '🛠',
                        'selesai' => '🎉',
                        'ditolak' => '❌'
                    ];
                    $priorityLabels = [
                        'rendah' => 'Rendah',
                        'sedang' => 'Sedang',
                        'tinggi' => 'Tinggi',
                        'urgent' => 'Darurat'
                    ];
                    $statusLabels = [
                        'pending' => 'Menunggu Verifikasi',
                        'diverifikasi' => 'Telah Diverifikasi',
                        'diproses' => 'Sedang Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak'
                    ];
                @endphp
                
                {{-- Status Badge --}}
                <div class="badge rounded-pill border px-3 py-2 d-flex align-items-center gap-2 {{ $pengaduan->status === 'selesai' ? 'bg-success-subtle text-success border-success-subtle' : ($pengaduan->status === 'ditolak' ? 'bg-danger-subtle text-danger border-danger-subtle' : ($pengaduan->status === 'pending' ? 'bg-warning-subtle text-warning-emphasis border-warning-subtle' : 'bg-primary-subtle text-primary border-primary-subtle')) }}">
                    <span class="fs-6">{{ $statusIcons[$pengaduan->status] ?? '❓' }}</span> 
                    <span class="fw-semibold">{{ $statusLabels[$pengaduan->status] ?? ucfirst($pengaduan->status) }}</span>
                </div>

                {{-- Priority Badge --}}
                <div class="badge rounded-pill border px-3 py-2 d-flex align-items-center gap-2 bg-light text-dark border-light-subtle">
                    <span class="fs-6">{{ $priorityIcons[$pengaduan->prioritas] ?? '⚪' }}</span>
                    <span class="fw-semibold">Prioritas {{ $priorityLabels[$pengaduan->prioritas] ?? ucfirst($pengaduan->prioritas) }}</span>
                </div>
            </div>

            {{-- Action Buttons Group --}}
            <div class="d-flex gap-2 w-100 w-md-auto mt-3 mt-md-0 pt-3 pt-md-0 border-top border-md-0 border-light-subtle">
                {{-- Mobile Back Button (Only visible on mobile) --}}
                <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary rounded-pill flex-fill d-md-none d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                
                @if($pengaduan->status === 'pending' && $pengaduan->user_id === auth()->id())
                    <a href="{{ route('pengaduan.edit', $pengaduan) }}" class="btn btn-primary rounded-pill px-4 flex-fill flex-md-grow-0 d-flex align-items-center justify-content-center shadow-sm">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <form action="{{ route('pengaduan.destroy', $pengaduan) }}" method="POST" class="flex-fill flex-md-grow-0" onsubmit="return confirm('Yakin ingin menghapus pengaduan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 w-100 d-flex align-items-center justify-content-center shadow-sm">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </button>
                    </form>
                @endif
                
                {{-- Share Link Button --}}
                <button type="button" class="btn btn-outline-primary rounded-pill px-4 flex-fill flex-md-grow-0 d-flex align-items-center justify-content-center" onclick="shareTrackingLink()">
                    <i class="fas fa-share-alt me-2"></i>Bagikan Link
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Main Content --}}
    <div class="col-12 col-lg-8">
        
        {{-- 🚀 Status Progress Tracker (Visual) --}}
        @php
            $steps = [
                'pending' => ['step' => 1, 'label' => 'Terkirim', 'icon' => 'fa-paper-plane'],
                'diverifikasi' => ['step' => 2, 'label' => 'Diverifikasi', 'icon' => 'fa-clipboard-check'],
                'diproses' => ['step' => 3, 'label' => 'Diproses', 'icon' => 'fa-tools'],
                'selesai' => ['step' => 4, 'label' => 'Selesai', 'icon' => 'fa-check-circle']
            ];
            
            $currStep = 0;
            if ($pengaduan->status == 'pending') $currStep = 1;
            elseif ($pengaduan->status == 'diverifikasi') $currStep = 2;
            elseif ($pengaduan->status == 'diproses') $currStep = 3;
            elseif ($pengaduan->status == 'selesai') $currStep = 4;
            
            // Special case for Ditolak
            $isDitolak = $pengaduan->status == 'ditolak';
        @endphp

        @if(!$isDitolak)
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4 text-center text-muted text-uppercase small letter-spacing-1">Status Laporan</h6>
                {{-- Numbered stepper removed as per request --}}
                
                {{-- Simplified 4-Step Visual Stepper --}}
                <div class="d-flex justify-content-between text-center position-relative mt-2">
                    {{-- 1. Terkirim --}}
                    <div class="position-relative z-1">
                        <div class="d-flex justify-content-center align-items-center mx-auto mb-2 rounded-circle {{ $currStep >= 1 ? 'bg-primary text-white shadow' : 'bg-light text-muted border' }}" 
                             style="width: 40px; height: 40px; transition: all 0.3s;">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="small fw-bold {{ $currStep >= 1 ? 'text-primary' : 'text-muted' }}">Terkirim</div>
                    </div>
                    
                    {{-- Connector 1 --}}
                    <div class="position-absolute top-0 start-0 w-100 mt-3" style="z-index: 0;">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar {{ $currStep >= 2 ? 'bg-primary' : 'bg-light' }}" style="width: 33%"></div>
                            <div class="progress-bar {{ $currStep >= 3 ? 'bg-primary' : 'bg-light' }}" style="width: 33%"></div>
                            <div class="progress-bar {{ $currStep >= 4 ? 'bg-success' : 'bg-light' }}" style="width: 34%"></div>
                        </div>
                    </div>

                    {{-- 2. Diverifikasi --}}
                    <div class="position-relative z-1">
                        <div class="d-flex justify-content-center align-items-center mx-auto mb-2 rounded-circle {{ $currStep >= 2 ? 'bg-primary text-white shadow' : 'bg-light text-muted border' }}" 
                             style="width: 40px; height: 40px; transition: all 0.3s;">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="small fw-bold {{ $currStep >= 2 ? 'text-primary' : 'text-muted' }}">Diverifikasi</div>
                    </div>

                    {{-- 3. Diproses --}}
                    <div class="position-relative z-1">
                        <div class="d-flex justify-content-center align-items-center mx-auto mb-2 rounded-circle {{ $currStep >= 3 ? 'bg-primary text-white shadow' : 'bg-light text-muted border' }}" 
                             style="width: 40px; height: 40px; transition: all 0.3s;">
                             <i class="fas fa-tools"></i>
                        </div>
                        <div class="small fw-bold {{ $currStep >= 3 ? 'text-primary' : 'text-muted' }}">Diproses</div>
                    </div>

                    {{-- 4. Selesai --}}
                    <div class="position-relative z-1">
                        <div class="d-flex justify-content-center align-items-center mx-auto mb-2 rounded-circle {{ $currStep >= 4 ? 'bg-success text-white shadow' : 'bg-light text-muted border' }}" 
                             style="width: 40px; height: 40px; transition: all 0.3s;">
                             <i class="fas fa-check"></i>
                        </div>
                        <div class="small fw-bold {{ $currStep >= 4 ? 'text-success' : 'text-muted' }}">Selesai</div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-danger shadow-sm rounded-4 border-0 mb-4 d-flex align-items-center gap-3">
             <div class="fs-1"><i class="fas fa-times-circle"></i></div>
             <div>
                 <h5 class="alert-heading fw-bold mb-1">Laporan Ditolak</h5>
                 <p class="mb-0">Mohon maaf, laporan Anda tidak dapat kami proses saat ini.</p>
             </div>
        </div>
        @endif

        {{-- 📝 Detail Pengaduan Card --}}
        <div class="card shadow-sm border-0 mb-3 rounded-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Detail Pengaduan</h5>
            </div>
            <div class="card-body">
                <div class="text-muted mb-3 fs-5" style="white-space: pre-line; line-height: 1.8;">
                    {{ $pengaduan->deskripsi }}
                </div>
                
                @if($pengaduan->tanggal_kejadian)
                    <div class="d-flex align-items-center gap-2 text-muted small mb-3 p-2 bg-light rounded">
                        <i class="fas fa-calendar text-primary"></i>
                        <span><strong>Tanggal Kejadian:</strong> {{ $pengaduan->tanggal_kejadian->format('d M Y') }}</span>
                    </div>
                @endif

                {{-- Admin Note --}}
                @if($pengaduan->catatan_admin)
                    <div class="alert alert-info border-0 shadow-sm">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-comment-dots text-info mt-1"></i>
                            <div>
                                <strong class="d-block mb-1">💬 Catatan Admin</strong>
                                <p class="mb-0 small">{{ $pengaduan->catatan_admin }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Teknisi Note --}}
                @if($pengaduan->catatan_teknisi)
                    <div class="alert alert-success border-0 shadow-sm">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-tools text-success mt-1"></i>
                            <div>
                                <strong class="d-block mb-1">🔧 Catatan Teknisi</strong>
                                <p class="mb-0 small">{{ $pengaduan->catatan_teknisi }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Rejection Reason --}}
                @if($pengaduan->status === 'ditolak' && $pengaduan->alasan_tolak)
                    <div class="alert alert-danger border-0 shadow-sm">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-times-circle text-danger mt-1"></i>
                            <div>
                                <strong class="d-block mb-1">❌ Alasan Penolakan</strong>
                                <p class="mb-0 small">{{ $pengaduan->alasan_tolak }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 📸 Foto Bukti Card --}}
        @if($pengaduan->photos->count() > 0)
            <div class="card shadow-sm border-0 mb-3 rounded-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-images me-2"></i>Foto Bukti ({{ $pengaduan->photos->count() }})</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        @foreach($pengaduan->photos as $photo)
                            <div class="col-6 col-md-4">
                                <a href="{{ asset('storage/' . $photo->file_path) }}" 
                                   data-fancybox="gallery" class="d-block position-relative">
                                    <img src="{{ asset('storage/' . $photo->file_path) }}" 
                                         class="img-fluid rounded shadow-sm hover-zoom" 
                                         style="width: 100%; height: 120px; object-fit: cover;">
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-dark bg-opacity-75">
                                            <i class="fas fa-search-plus"></i>
                                        </span>
                                    </div>
                                </a>
                                <small class="text-muted d-block mt-1 text-center">
                                    {{ ucfirst($photo->tipe ?? 'Bukti') }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- ⭐ Feedback Form (if completed and no feedback yet) --}}
        @if($pengaduan->status === 'selesai' && !$pengaduan->feedbackDetail && $pengaduan->user_id === auth()->id())
            <div class="card shadow-sm border-0 border-start border-5 border-success mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Berikan Feedback Anda</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success bg-success bg-opacity-10 border-0 mb-4">
                        <p class="mb-0"><strong>🎉 Pengaduan Selesai!</strong><br>
                        Bantu kami meningkatkan layanan dengan memberikan penilaian.</p>
                    </div>
                    
                    <form action="{{ route('pengaduan.feedback', $pengaduan) }}" method="POST" id="feedbackForm">
                        @csrf
                        
                        {{-- Rating Respon --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <label class="form-label fw-bold mb-2">
                                <span class="badge bg-primary rounded-pill me-2">1</span>
                                Kecepatan Respon <span class="text-danger">*</span>
                            </label>
                            <p class="text-muted small mb-3">Seberapa cepat pengaduan Anda ditanggapi?</p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                @for($i = 1; $i <= 5; $i++)
                                    <div>
                                        <input type="radio" class="btn-check" name="rating_respon" 
                                               id="rating_respon_{{ $i }}" value="{{ $i }}" required>
                                        <label class="btn btn-outline-warning btn-lg" for="rating_respon_{{ $i }}">
                                            <i class="fas fa-star"></i> {{ $i }}
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- Rating Kualitas --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <label class="form-label fw-bold mb-2">
                                <span class="badge bg-primary rounded-pill me-2">2</span>
                                Kualitas Perbaikan <span class="text-danger">*</span>
                            </label>
                            <p class="text-muted small mb-3">Seberapa baik hasil perbaikan yang dilakukan?</p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                @for($i = 1; $i <= 5; $i++)
                                    <div>
                                        <input type="radio" class="btn-check" name="rating_kualitas" 
                                               id="rating_kualitas_{{ $i }}" value="{{ $i }}" required>
                                        <label class="btn btn-outline-warning btn-lg" for="rating_kualitas_{{ $i }}">
                                            <i class="fas fa-star"></i> {{ $i }}
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- Rating Pelayanan --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <label class="form-label fw-bold mb-2">
                                <span class="badge bg-primary rounded-pill me-2">3</span>
                                Pelayanan Teknisi <span class="text-danger">*</span>
                            </label>
                            <p class="text-muted small mb-3">Bagaimana sikap dan pelayanan teknisi?</p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                @for($i = 1; $i <= 5; $i++)
                                    <div>
                                        <input type="radio" class="btn-check" name="rating_pelayanan" 
                                               id="rating_pelayanan_{{ $i }}" value="{{ $i }}" required>
                                        <label class="btn btn-outline-warning btn-lg" for="rating_pelayanan_{{ $i }}">
                                            <i class="fas fa-star"></i> {{ $i }}
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- Satisfaction --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <label class="form-label fw-bold mb-2">
                                <span class="badge bg-primary rounded-pill me-2">4</span>
                                Apakah Anda puas? <span class="text-danger">*</span>
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="is_satisfied" 
                                           id="satisfied_yes" value="1" required>
                                    <label class="btn btn-outline-success w-100 py-3" for="satisfied_yes">
                                        <div class="fs-3">😊</div>
                                        <strong>Ya, Puas</strong>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="is_satisfied" 
                                           id="satisfied_no" value="0">
                                    <label class="btn btn-outline-danger w-100 py-3" for="satisfied_no">
                                        <div class="fs-3">😞</div>
                                        <strong>Tidak Puas</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Komentar --}}
                        <div class="mb-4">
                            <label for="komentar" class="form-label fw-bold">
                                <span class="badge bg-secondary rounded-pill me-2">5</span>
                                Komentar (Opsional)
                            </label>
                            <textarea class="form-control form-control-lg" id="komentar" name="komentar" rows="3" 
                                      placeholder="Bagikan pengalaman atau saran Anda..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ⭐ Existing Feedback Display --}}
        @if($pengaduan->feedbackDetail)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Feedback Pengguna</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="text-center p-3 bg-light rounded shadow-sm">
                                <div class="small text-muted mb-2">Kecepatan Respon</div>
                                <div class="h4 mb-0 text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $pengaduan->feedbackDetail->rating_respon ? '' : 'text-muted opacity-25' }}"></i>
                                    @endfor
                                </div>
                                <div class="small mt-1"><strong>{{ $pengaduan->feedbackDetail->rating_respon }}/5</strong></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="text-center p-3 bg-light rounded shadow-sm">
                                <div class="small text-muted mb-2">Kualitas Perbaikan</div>
                                <div class="h4 mb-0 text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $pengaduan->feedbackDetail->rating_kualitas ? '' : 'text-muted opacity-25' }}"></i>
                                    @endfor
                                </div>
                                <div class="small mt-1"><strong>{{ $pengaduan->feedbackDetail->rating_kualitas }}/5</strong></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="text-center p-3 bg-light rounded shadow-sm">
                                <div class="small text-muted mb-2">Pelayanan Teknisi</div>
                                <div class="h4 mb-0 text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $pengaduan->feedbackDetail->rating_pelayanan ? '' : 'text-muted opacity-25' }}"></i>
                                    @endfor
                                </div>
                                <div class="small mt-1"><strong>{{ $pengaduan->feedbackDetail->rating_pelayanan }}/5</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="alert {{ $pengaduan->feedbackDetail->is_satisfied ? 'alert-success' : 'alert-danger' }} border-0 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fs-1">{{ $pengaduan->feedbackDetail->is_satisfied ? '😊' : '😞' }}</div>
                            <div>
                                <strong>Status Kepuasan:</strong><br>
                                {{ $pengaduan->feedbackDetail->is_satisfied ? 'Puas dengan layanan' : 'Tidak puas dengan layanan' }}
                            </div>
                        </div>
                    </div>

                    @if($pengaduan->feedbackDetail->komentar)
                        <div class="bg-light p-3 rounded">
                            <strong class="d-block mb-2">💬 Komentar:</strong>
                            <p class="mb-0 text-muted">{{ $pengaduan->feedbackDetail->komentar }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    {{-- Sidebar Info --}}
    <div class="col-12 col-lg-4">
        
        {{-- 📊 Informasi Pengaduan --}}
        <div class="card shadow-sm border-0 mb-3 rounded-4">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    {{-- 👤 Pelapor --}}
                    <div class="list-group-item">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light p-2 rounded-circle text-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user text-primary mt-1"></i>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Pelapor</small>
                                <div class="fw-bold text-dark">{{ $pengaduan->user->name }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- 🏷️ Kategori --}}
                    <div class="list-group-item">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light p-2 rounded-circle text-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-tag text-info mt-1"></i>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Kategori</small>
                                <div class="fw-bold text-dark">{{ $pengaduan->kategori->nama }}</div>
                                @if($pengaduan->subKategori)
                                    <div class="text-muted small">{{ $pengaduan->subKategori->nama }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 📍 Location Group --}}
                    <div class="list-group-item bg-light-subtle">
                        <div class="d-flex gap-3">
                            <div class="mt-1">
                                <div class="bg-white p-2 rounded-circle shadow-sm text-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-map-marker-alt text-danger fs-5"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Lokasi</small>
                                <div class="fw-bold text-dark fs-6">{{ $pengaduan->gedung->nama }}</div>
                                <div class="text-muted small">
                                    Lantai {{ $pengaduan->lantai }} 
                                    @if($pengaduan->ruangan)
                                        <span class="mx-1">•</span> {{ $pengaduan->ruangan->nama }}
                                    @endif
                                </div>
                                @if($pengaduan->lokasi_detail)
                                    <div class="mt-2 text-muted x-small fst-italic bg-white p-2 rounded border border-light">
                                        "{{ $pengaduan->lokasi_detail }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 👤 Teknisi --}}
                    <div class="list-group-item">
                        <div class="d-flex align-items-center gap-3">
                             <div class="bg-light p-2 rounded-circle text-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user-cog {{ $pengaduan->teknisi ? 'text-success' : 'text-muted' }} mt-1"></i>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Teknisi</small>
                                @if($pengaduan->teknisi)
                                    <div class="fw-bold text-dark">{{ $pengaduan->teknisi->name }}</div>
                                @else
                                    <div class="text-muted fst-italic">Belum ditugaskan</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-clock text-info"></i>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Dibuat</small>
                                <strong>{{ $pengaduan->created_at->format('d M Y, H:i') }}</strong>
                                <div class="small text-muted">{{ $pengaduan->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-sync text-warning"></i>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Update Terakhir</small>
                                <strong>{{ $pengaduan->updated_at->format('d M Y, H:i') }}</strong>
                                <div class="small text-muted">{{ $pengaduan->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🕐 Timeline --}}
        @if($pengaduan->histories->count() > 0)
            <div class="card shadow-sm border-0 mb-3 rounded-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Timeline</h6>
                </div>
                <div class="card-body p-3">
                    <div class="timeline">
                        @foreach($pengaduan->histories as $history)
                            <div class="timeline-item mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="d-flex gap-2">
                                    <div class="timeline-icon">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas fa-history text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <div class="fw-bold text-dark">{{ $history->keterangan }}</div>
                                        @if($history->user)
                                            <div class="text-muted small">
                                                By: {{ $history->user->name }}
                                            </div>
                                        @endif
                                        <div class="text-muted mt-1 small bg-light d-inline-block px-2 rounded">
                                            <i class="far fa-clock me-1"></i> {{ $history->created_at->format('d M Y, H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- 📝 Log Aktivitas (Hidden for Students, visible/collapsible for others) --}}
        @if(!auth()->user()->isSiswa() && $pengaduan->logs->count() > 0)
            <div class="card shadow-sm border-0 mb-3 rounded-4">
                <div class="card-header bg-light collapsed" data-bs-toggle="collapse" href="#logCollapse" role="button" aria-expanded="false" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-muted"><i class="fas fa-code-branch me-2"></i>Log Sistem (Teknis)</h6>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                </div>
                <div class="collapse" id="logCollapse">
                    <div class="card-body p-3 bg-light bg-opacity-50">
                        <div class="log-list">
                            @foreach($pengaduan->logs->take(10) as $log)
                                <div class="log-item mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="small">
                                        <i class="fas fa-angle-right text-muted"></i>
                                        <strong>{{ $log->user->name }}</strong>
                                        <span class="text-muted">{{ $log->description ?? $log->action_display }}</span>
                                    </div>
                                    <div class="text-muted x-small">
                                        {{ $log->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@push('styles')
<style>
    /* Mobile-First Custom Styles */
    .x-small {
        font-size: 0.7rem;
    }
    
    .hover-zoom {
        transition: transform 0.3s ease;
    }
    
    .hover-zoom:hover {
        transform: scale(1.05);
    }
    
    .timeline-icon {
        padding-top: 2px;
    }
    
    .badge {
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    /* Sticky header on mobile */
    @media (max-width: 768px) {
        .page-header {
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    }
    
    /* Better card shadows */
    .card {
        transition: box-shadow 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
    
    /* Rating stars bigger touch targets on mobile */
    @media (max-width: 576px) {
        .btn-outline-warning {
            padding: 0.75rem 1rem;
            font-size: 1.2rem;
        }
    }
    
    /* Better list group item spacing */
    .list-group-item {
        padding: 0.75rem 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    function shareTrackingLink() {
        const trackingUrl = "{{ route('pengaduan.track', ['kode' => $pengaduan->kode_pengaduan]) }}";
        const shareData = {
            title: 'Lacak Status Pengaduan SFCS',
            text: 'Cek status pengaduan "{{ $pengaduan->judul }}" di sini:',
            url: trackingUrl
        };

        if (navigator.share) {
            navigator.share(shareData)
                .then(() => console.log('Link shared successfully'))
                .catch((error) => console.log('Error sharing:', error));
        } else {
            // Fallback for browsers that don't support Web Share API
            navigator.clipboard.writeText(trackingUrl).then(() => {
                alert('Link tracking berhasil disalin ke clipboard!');
            });
        }
    }
</script>
@endpush
