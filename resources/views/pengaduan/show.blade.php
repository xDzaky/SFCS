@extends('layouts.sfcs')

@section('title', 'Detail Pengaduan')

@section('breadcrumb')
<ol class="breadcrumb mb-0 small">
    <li class="breadcrumb-item"><a href="{{ route('pengaduan.index') }}">Pengaduan</a></li>
    <li class="breadcrumb-item active">{{ $pengaduan->kode_pengaduan }}</li>
</ol>
@endsection

@section('content')
@php
    $priorityColors = [
        'rendah'  => ['bg' => 'bg-success-subtle', 'text' => 'text-success-emphasis', 'border' => 'border-success-subtle', 'dot' => '#16a34a', 'solid' => 'badge-prioritas-rendah'],
        'sedang'  => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning-emphasis', 'border' => 'border-warning-subtle', 'dot' => '#d97706', 'solid' => 'badge-prioritas-sedang'],
        'tinggi'  => ['bg' => 'bg-orange-subtle',  'text' => 'text-dark',             'border' => 'border-warning-subtle', 'dot' => '#ea580c', 'solid' => 'badge-prioritas-tinggi'],
        'urgent'  => ['bg' => 'bg-danger-subtle',  'text' => 'text-danger-emphasis',  'border' => 'border-danger-subtle',  'dot' => '#dc2626', 'solid' => 'badge-prioritas-urgent'],
    ];
    $statusColors = [
        'pending'       => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary-emphasis', 'border' => 'border-secondary-subtle'],
        'diverifikasi'  => ['bg' => 'bg-info-subtle',      'text' => 'text-info-emphasis',       'border' => 'border-info-subtle'],
        'diproses'      => ['bg' => 'bg-primary-subtle',   'text' => 'text-primary-emphasis',    'border' => 'border-primary-subtle'],
        'selesai'       => ['bg' => 'bg-success-subtle',   'text' => 'text-success-emphasis',    'border' => 'border-success-subtle'],
        'ditolak'       => ['bg' => 'bg-danger-subtle',    'text' => 'text-danger-emphasis',     'border' => 'border-danger-subtle'],
    ];
    $statusLabels = [
        'pending'      => 'Menunggu Verifikasi',
        'diverifikasi' => 'Telah Diverifikasi',
        'diproses'     => 'Sedang Diproses',
        'selesai'      => 'Selesai',
        'ditolak'      => 'Ditolak',
    ];
    $priorityLabels = [
        'rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi', 'urgent' => 'Darurat',
    ];
    $sc = $statusColors[$pengaduan->status] ?? $statusColors['pending'];
    $pc = $priorityColors[$pengaduan->prioritas] ?? $priorityColors['rendah'];

    $currStep = match($pengaduan->status) {
        'pending'      => 1,
        'diverifikasi' => 2,
        'diproses'     => 3,
        'selesai'      => 4,
        default        => 0,
    };
    $isDitolak = $pengaduan->status === 'ditolak';
@endphp

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION 1 — Header Card (full width)
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 p-md-4">

        {{-- Row 1: Code + Date + Back --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-light text-primary border border-primary-subtle fw-bold px-3 py-2 rounded-pill" style="font-size:.8rem;">
                    <i class="fas fa-hashtag me-1"></i>{{ $pengaduan->kode_pengaduan }}
                </span>
                <span class="text-muted small d-none d-sm-inline">
                    <i class="far fa-clock me-1"></i>{{ $pengaduan->created_at->format('d M Y, H:i') }}
                </span>
            </div>
            <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-none d-md-inline-flex align-items-center">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>

        {{-- Row 2: Title --}}
        <h1 class="fw-bold text-dark mb-3" style="font-size:clamp(1.25rem,3vw,1.75rem);">{{ $pengaduan->judul }}</h1>

        {{-- Row 3: Badges --}}
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge rounded-pill border px-3 py-2 {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}" style="font-size:.8rem;">
                {{ $statusLabels[$pengaduan->status] ?? ucfirst($pengaduan->status) }}
            </span>
            <span class="badge rounded-pill border px-3 py-2 bg-light text-dark border-light-subtle d-inline-flex align-items-center gap-1" style="font-size:.8rem;">
                <span class="d-inline-block rounded-circle" style="width:8px;height:8px;background:{{ $pc['dot'] }};"></span>
                Prioritas {{ $priorityLabels[$pengaduan->prioritas] ?? ucfirst($pengaduan->prioritas) }}
            </span>
        </div>

        {{-- Row 4: Action buttons --}}
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-md-none">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
            @if($pengaduan->status === 'pending' && $pengaduan->user_id === auth()->id())
                <a href="{{ route('pengaduan.edit', $pengaduan) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
                <form action="{{ route('pengaduan.destroy', $pengaduan) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengaduan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fas fa-trash me-1"></i>Hapus
                    </button>
                </form>
            @endif

        </div>

        {{-- Reopen request (only for selesai) --}}
        @if($pengaduan->status === 'selesai' && $pengaduan->user_id === auth()->id() && !$pengaduan->is_auto_closed_duplicate)
            <div class="mt-3 pt-3 border-top border-light-subtle">
                @if($pengaduan->has_reopen_request)
                    <div class="alert alert-warning mb-0 small py-2">
                        Permintaan buka ulang sudah dikirim pada {{ optional($pengaduan->reopen_requested_at)->format('d/m/Y H:i') }}.
                    </div>
                @else
                    <form action="{{ route('pengaduan.reopen-request', $pengaduan) }}" method="POST">
                        @csrf
                        <label class="form-label small text-muted mb-1">Ajukan buka ulang jika masalah muncul kembali</label>
                        <div class="d-flex gap-2">
                            <textarea name="reopen_reason" class="form-control form-control-sm flex-grow-1" rows="1" placeholder="Contoh: Kerusakan muncul lagi setelah 2 hari..." required></textarea>
                            <button type="submit" class="btn btn-outline-warning btn-sm flex-shrink-0 rounded-pill px-3">
                                <i class="fas fa-redo me-1"></i>Buka Ulang
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION 2 — Status Progress (full width, compact)
     ═══════════════════════════════════════════════════════════════════ --}}
@if(!$isDitolak)
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body px-3 px-md-4 py-3">
        <h6 class="fw-bold text-center text-muted text-uppercase mb-3" style="font-size:.7rem;letter-spacing:.08em;">Status Laporan</h6>
        <div class="sfcs-stepper d-flex justify-content-between text-center position-relative">
            @php
                $stepItems = [
                    ['step' => 1, 'icon' => 'fa-paper-plane', 'label' => 'Terkirim'],
                    ['step' => 2, 'icon' => 'fa-clipboard-check', 'label' => 'Diverifikasi'],
                    ['step' => 3, 'icon' => 'fa-tools', 'label' => 'Diproses'],
                    ['step' => 4, 'icon' => 'fa-check', 'label' => 'Selesai'],
                ];
            @endphp
            {{-- Progress bar behind --}}
            <div class="position-absolute w-100" style="top:16px;left:0;z-index:0;padding:0 12%;">
                <div class="progress" style="height:3px;">
                    <div class="progress-bar {{ $currStep >= 2 ? 'bg-primary' : 'bg-light' }}" style="width:33.33%"></div>
                    <div class="progress-bar {{ $currStep >= 3 ? 'bg-primary' : 'bg-light' }}" style="width:33.33%"></div>
                    <div class="progress-bar {{ $currStep >= 4 ? 'bg-success' : 'bg-light' }}" style="width:33.34%"></div>
                </div>
            </div>
            @foreach($stepItems as $si)
                <div class="position-relative" style="z-index:1;flex:1;">
                    <div class="d-flex justify-content-center align-items-center mx-auto mb-1 rounded-circle {{ $currStep >= $si['step'] ? ($si['step'] === 4 ? 'bg-success' : 'bg-primary') . ' text-white shadow-sm' : 'bg-light text-muted border' }}"
                         style="width:34px;height:34px;transition:all .3s;">
                        <i class="fas {{ $si['icon'] }}" style="font-size:.7rem;"></i>
                    </div>
                    <div class="fw-semibold {{ $currStep >= $si['step'] ? ($si['step'] === 4 ? 'text-success' : 'text-primary') : 'text-muted' }}" style="font-size:.65rem;">{{ $si['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@else
<div class="alert alert-danger shadow-sm rounded-4 border-0 mb-4 d-flex align-items-center gap-3 py-3">
    <i class="fas fa-times-circle fs-3"></i>
    <div>
        <div class="fw-bold mb-0">Laporan Ditolak</div>
        <p class="mb-0 small">Mohon maaf, laporan Anda tidak dapat kami proses saat ini.</p>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION 3 — Main 2-column Layout (desktop) / stacked (mobile)
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-3 g-lg-4">

    {{-- ━━━━━━━━ LEFT COLUMN ━━━━━━━━ --}}
    <div class="col-12 col-lg-8 order-2 order-lg-1">

        {{-- ── Detail Pengaduan ──────────────────────────────── --}}
        <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden">
            <div class="card-header bg-primary text-white py-2 px-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-file-alt me-2"></i>Detail Pengaduan</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="text-dark mb-3" style="white-space:pre-line;line-height:1.75;font-size:.95rem;">{{ $pengaduan->deskripsi }}</div>

                @if($pengaduan->tanggal_kejadian)
                    <div class="d-inline-flex align-items-center gap-2 text-muted small bg-light rounded-pill px-3 py-1 mb-3">
                        <i class="fas fa-calendar-day text-primary" style="font-size:.7rem;"></i>
                        <span>Tanggal Kejadian: <strong>{{ $pengaduan->tanggal_kejadian->format('d M Y') }}</strong></span>
                    </div>
                @endif

                {{-- Admin / Teknisi / Rejection notes --}}
                @if($pengaduan->catatan_admin)
                    <div class="sfcs-note sfcs-note-info mb-3">
                        <div class="d-flex gap-2">
                            <i class="fas fa-comment-dots mt-1 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold small mb-1">Catatan Admin</div>
                                <p class="mb-0 small">{{ $pengaduan->catatan_admin }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($pengaduan->catatan_teknisi)
                    <div class="sfcs-note sfcs-note-success mb-3">
                        <div class="d-flex gap-2">
                            <i class="fas fa-wrench mt-1 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold small mb-1">Catatan Teknisi</div>
                                <p class="mb-0 small">{{ $pengaduan->catatan_teknisi }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($pengaduan->status === 'ditolak' && $pengaduan->alasan_tolak)
                    <div class="sfcs-note sfcs-note-danger mb-3">
                        <div class="d-flex gap-2">
                            <i class="fas fa-times-circle mt-1 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold small mb-1">Alasan Penolakan</div>
                                <p class="mb-0 small">{{ $pengaduan->alasan_tolak }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($pengaduan->schedules->count() > 0)
                    <div class="sfcs-note sfcs-note-info mb-3">
                        <div class="d-flex gap-2">
                            <i class="fas fa-calendar-days mt-1 flex-shrink-0"></i>
                            <div class="w-100">
                                <div class="fw-semibold small mb-2">Jadwal Penanganan</div>
                                @foreach($pengaduan->schedules->take(3) as $schedule)
                                    <div class="small {{ !$loop->last ? 'mb-2 pb-2 border-bottom border-light-subtle' : '' }}">
                                        <strong>{{ optional($schedule->to_start)->format('d M Y H:i') ?? '-' }}</strong>
                                        <span class="text-muted">oleh {{ $schedule->changer->name ?? 'Sistem' }}</span>
                                        <div class="text-muted">{{ $schedule->reason }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Lokasi Kerusakan di Denah ─────────────────────── --}}
        @include('partials.pengaduan-map-card', [
            'mapPayload' => $mapPayload,
            'viewerId'   => 'student-pengaduan-map',
            'title'      => 'Lokasi Kerusakan di Denah',
            'showInfoDenah' => false,
        ])

        {{-- ── Foto Bukti ────────────────────────────────────── --}}
        @if($pengaduan->photos->count() > 0)
            <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden">
                <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:28px;height:28px;font-size:.8rem;">
                            <i class="fas fa-images"></i>
                        </span>
                        <span>Foto Bukti Kerusakan</span>
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size:.72rem;">
                        {{ $pengaduan->photos->count() }} Foto
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        @foreach($pengaduan->photos as $photo)
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card border border-light-subtle rounded-3 overflow-hidden shadow-none h-100">
                                    <a href="{{ asset('storage/' . $photo->file_path) }}" data-fancybox="gallery" data-caption="Foto Bukti - {{ $pengaduan->judul }}" class="sfcs-photo-thumb d-block position-relative">
                                        <img src="{{ asset('storage/' . $photo->file_path) }}" 
                                             alt="{{ $photo->tipe ?? 'Bukti' }}" 
                                             class="img-fluid w-100" 
                                             style="height:120px;object-fit:cover;transition:transform .3s ease;"
                                             loading="lazy">
                                        <span class="sfcs-photo-zoom">
                                            <i class="fas fa-search-plus"></i>
                                        </span>
                                    </a>
                                    <div class="p-2 text-center bg-light-subtle border-top">
                                        <span class="badge bg-secondary-subtle text-secondary small" style="font-size:.68rem;">
                                            <i class="fas fa-camera me-1"></i>{{ ucfirst($photo->tipe ?? 'Bukti') }}
                                        </span>
                                        @if($photo->file_size_human)
                                            <span class="text-muted small ms-1" style="font-size:.65rem;">({{ $photo->file_size_human }})</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Feedback Form ─────────────────────────────────── --}}
        @if($pengaduan->status === 'selesai' && !$pengaduan->feedbackDetail && $pengaduan->user_id === auth()->id() && !$pengaduan->is_auto_closed_duplicate)
            <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden border-start border-4 border-success">
                <div class="card-header bg-success text-white py-2 px-3">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-star me-2"></i>Berikan Feedback Anda</h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="rounded-3 p-3 mb-4" style="background:linear-gradient(135deg,#ecfdf5 0%,#d1fae5 100%);">
                        <p class="mb-0 small"><strong>Pengaduan Selesai!</strong> Bantu kami meningkatkan layanan dengan memberikan penilaian.</p>
                    </div>

                    <form action="{{ route('pengaduan.feedback', $pengaduan) }}" method="POST" id="feedbackForm">
                        @csrf

                        @php
                            $ratingFields = [
                                ['name' => 'rating_respon',    'label' => 'Kecepatan Respon',    'desc' => 'Seberapa cepat pengaduan ditanggapi?'],
                                ['name' => 'rating_kualitas',  'label' => 'Kualitas Perbaikan',  'desc' => 'Seberapa baik hasil perbaikan?'],
                                ['name' => 'rating_pelayanan', 'label' => 'Pelayanan Teknisi',   'desc' => 'Bagaimana sikap dan pelayanan teknisi?'],
                            ];
                        @endphp

                        @foreach($ratingFields as $idx => $rf)
                            <div class="mb-4 pb-3 {{ !$loop->last ? 'border-bottom border-light-subtle' : '' }}">
                                <label class="form-label fw-bold small mb-1">
                                    <span class="badge bg-primary rounded-pill me-1" style="font-size:.65rem;">{{ $idx + 1 }}</span>
                                    {{ $rf['label'] }} <span class="text-danger">*</span>
                                </label>
                                <p class="text-muted mb-2" style="font-size:.75rem;">{{ $rf['desc'] }}</p>
                                <div class="d-flex gap-1 flex-wrap">
                                    @for($i = 1; $i <= 5; $i++)
                                        <div>
                                            <input type="radio" class="btn-check" name="{{ $rf['name'] }}" id="{{ $rf['name'] }}_{{ $i }}" value="{{ $i }}" required>
                                            <label class="btn btn-outline-warning btn-sm" for="{{ $rf['name'] }}_{{ $i }}">
                                                <i class="fas fa-star"></i> {{ $i }}
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endforeach

                        <div class="mb-4 pb-3 border-bottom border-light-subtle">
                            <label class="form-label fw-bold small mb-2">
                                <span class="badge bg-primary rounded-pill me-1" style="font-size:.65rem;">4</span>
                                Apakah Anda puas? <span class="text-danger">*</span>
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="is_satisfied" id="satisfied_yes" value="1" required>
                                    <label class="btn btn-outline-success w-100 py-2" for="satisfied_yes">
                                        <div class="fs-5">😊</div><strong class="small">Ya, Puas</strong>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="is_satisfied" id="satisfied_no" value="0">
                                    <label class="btn btn-outline-danger w-100 py-2" for="satisfied_no">
                                        <div class="fs-5">😞</div><strong class="small">Tidak Puas</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="komentar" class="form-label fw-bold small">
                                <span class="badge bg-secondary rounded-pill me-1" style="font-size:.65rem;">5</span> Komentar (Opsional)
                            </label>
                            <textarea class="form-control form-control-sm" id="komentar" name="komentar" rows="2" placeholder="Bagikan pengalaman atau saran Anda..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 rounded-pill py-2">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Feedback
                        </button>
                    </form>
                </div>
            </div>
        @elseif($pengaduan->status === 'selesai' && $pengaduan->user_id === auth()->id() && $pengaduan->is_auto_closed_duplicate)
            <div class="alert alert-info border-0 shadow-sm rounded-3 small py-2">
                Tiket ini ditutup otomatis karena duplikat — feedback diberikan di tiket utama.
            </div>
        @endif

        {{-- ── Existing Feedback Display ─────────────────────── --}}
        @if($pengaduan->feedbackDetail)
            <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden">
                <div class="card-header bg-warning text-dark py-2 px-3">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-star me-2"></i>Feedback Pengguna</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        @php
                            $fbFields = [
                                ['label' => 'Kecepatan', 'value' => $pengaduan->feedbackDetail->rating_respon],
                                ['label' => 'Kualitas',  'value' => $pengaduan->feedbackDetail->rating_kualitas],
                                ['label' => 'Pelayanan', 'value' => $pengaduan->feedbackDetail->rating_pelayanan],
                            ];
                        @endphp
                        @foreach($fbFields as $fb)
                            <div class="col-4">
                                <div class="text-center p-2 bg-light rounded-3">
                                    <div class="text-muted mb-1" style="font-size:.65rem;">{{ $fb['label'] }}</div>
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $fb['value'] ? '' : 'text-muted opacity-25' }}" style="font-size:.7rem;"></i>
                                        @endfor
                                    </div>
                                    <div class="fw-bold small">{{ $fb['value'] }}/5</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex align-items-center gap-2 rounded-3 p-2 mb-2 {{ $pengaduan->feedbackDetail->is_satisfied ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                        <span class="fs-5">{{ $pengaduan->feedbackDetail->is_satisfied ? '😊' : '😞' }}</span>
                        <span class="small fw-semibold {{ $pengaduan->feedbackDetail->is_satisfied ? 'text-success' : 'text-danger' }}">
                            {{ $pengaduan->feedbackDetail->is_satisfied ? 'Puas dengan layanan' : 'Tidak puas dengan layanan' }}
                        </span>
                    </div>

                    @if($pengaduan->feedbackDetail->komentar)
                        <div class="bg-light p-2 rounded-3">
                            <div class="text-muted mb-1" style="font-size:.65rem;">Komentar</div>
                            <p class="mb-0 small">{{ $pengaduan->feedbackDetail->komentar }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    {{-- ━━━━━━━━ RIGHT COLUMN / Info Sidebar ━━━━━━━━
         On mobile: order-1 = shows BEFORE detail content
         On desktop: stays on the right side
         ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
    <div class="col-12 col-lg-4 order-1 order-lg-2">

        {{-- ── Informasi Ringkas ─────────────────────────────── --}}
        <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden sfcs-info-card">
            <div class="card-header bg-gradient text-white py-2 px-3" style="background:linear-gradient(135deg,#4f46e5 0%,#6366f1 100%);">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
            </div>
            <div class="card-body p-0">

                {{-- On mobile: horizontal scroll row. On desktop: vertical list --}}
                <div class="sfcs-info-list">

                    {{-- Pelapor --}}
                    <div class="sfcs-info-item">
                        <div class="sfcs-info-icon bg-primary-subtle text-primary"><i class="fas fa-user"></i></div>
                        <div>
                            <div class="sfcs-info-label">Pelapor</div>
                            <div class="sfcs-info-value">{{ $pengaduan->user->name }}</div>
                        </div>
                    </div>

                    {{-- Kategori --}}
                    <div class="sfcs-info-item">
                        <div class="sfcs-info-icon bg-info-subtle text-info"><i class="fas fa-tag"></i></div>
                        <div>
                            <div class="sfcs-info-label">Kategori</div>
                            <div class="sfcs-info-value">{{ $pengaduan->kategori->nama }}</div>
                            @if($pengaduan->subKategori)
                                <div class="text-muted" style="font-size:.7rem;">{{ $pengaduan->subKategori->nama }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Lokasi --}}
                    <div class="sfcs-info-item">
                        <div class="sfcs-info-icon bg-danger-subtle text-danger"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <div class="sfcs-info-label">Lokasi</div>
                            <div class="sfcs-info-value">{{ $pengaduan->gedung->nama }}</div>
                            <div class="text-muted" style="font-size:.7rem;">
                                Lantai {{ $pengaduan->lantai }}
                                @if($pengaduan->ruangan) &middot; {{ $pengaduan->ruangan->nama }} @endif
                            </div>
                            @if($pengaduan->lokasi_detail)
                                <div class="text-muted fst-italic mt-1" style="font-size:.65rem;">"{{ $pengaduan->lokasi_detail }}"</div>
                            @endif
                        </div>
                    </div>

                    {{-- Teknisi --}}
                    <div class="sfcs-info-item">
                        <div class="sfcs-info-icon {{ $pengaduan->teknisi ? 'bg-success-subtle text-success' : 'bg-light text-muted' }}">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div>
                            <div class="sfcs-info-label">Teknisi</div>
                            @if($pengaduan->teknisi)
                                <div class="sfcs-info-value">{{ $pengaduan->teknisi->name }}</div>
                            @else
                                <div class="sfcs-info-value text-muted fst-italic">Belum ditugaskan</div>
                            @endif
                        </div>
                    </div>

                    {{-- Waktu --}}
                    <div class="sfcs-info-item">
                        <div class="sfcs-info-icon bg-primary-subtle text-primary"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="sfcs-info-label">Dibuat</div>
                            <div class="sfcs-info-value">{{ $pengaduan->created_at->format('d M Y, H:i') }}</div>
                            <div class="text-muted" style="font-size:.65rem;">{{ $pengaduan->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    <div class="sfcs-info-item">
                        <div class="sfcs-info-icon bg-warning-subtle text-warning"><i class="fas fa-sync"></i></div>
                        <div>
                            <div class="sfcs-info-label">Update Terakhir</div>
                            <div class="sfcs-info-value">{{ $pengaduan->updated_at->format('d M Y, H:i') }}</div>
                            <div class="text-muted" style="font-size:.65rem;">{{ $pengaduan->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Timeline ──────────────────────────────────────── --}}
        @if($pengaduan->histories->count() > 0)
            <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden">
                <div class="card-header bg-dark text-white py-2 px-3">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-history me-2"></i>Timeline</h6>
                </div>
                <div class="card-body p-3">
                    @foreach($pengaduan->histories as $history)
                        <div class="d-flex gap-2 {{ !$loop->last ? 'mb-3 pb-3 border-bottom border-light-subtle' : '' }}">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;">
                                <i class="fas fa-history text-primary" style="font-size:.65rem;"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-semibold small text-dark">{{ $history->keterangan }}</div>
                                @if($history->user)
                                    <div class="text-muted" style="font-size:.65rem;">{{ $history->user->name }}</div>
                                @endif
                                <div class="text-muted" style="font-size:.6rem;">
                                    <i class="far fa-clock me-1"></i>{{ $history->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Log Sistem (non-siswa only) ───────────────────── --}}
        @if(!auth()->user()->isSiswa() && $pengaduan->logs->count() > 0)
            <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden">
                <div class="card-header bg-light py-2 px-3 collapsed" data-bs-toggle="collapse" href="#logCollapse" role="button" aria-expanded="false" style="cursor:pointer;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-muted small"><i class="fas fa-code-branch me-2"></i>Log Sistem</h6>
                        <i class="fas fa-chevron-down text-muted" style="font-size:.6rem;"></i>
                    </div>
                </div>
                <div class="collapse" id="logCollapse">
                    <div class="card-body p-3 bg-light bg-opacity-50">
                        @foreach($pengaduan->logs->take(10) as $log)
                            <div class="small {{ !$loop->last ? 'mb-2 pb-2 border-bottom border-light-subtle' : '' }}">
                                <i class="fas fa-angle-right text-muted me-1"></i>
                                <strong>{{ $log->user->name }}</strong>
                                <span class="text-muted">{{ $log->description ?? $log->action_display }}</span>
                                <div class="text-muted" style="font-size:.6rem;">{{ $log->created_at->format('d M Y, H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@push('styles')
<style>
    /* ═══ SFCS Show Detail — Mobile-first styles ═══ */

    /* Note boxes */
    .sfcs-note {
        border-radius: .75rem;
        padding: .75rem 1rem;
        border-left: 3px solid;
    }
    .sfcs-note-info    { background: #eff6ff; border-color: #3b82f6; color: #1e40af; }
    .sfcs-note-success { background: #f0fdf4; border-color: #22c55e; color: #166534; }
    .sfcs-note-danger  { background: #fef2f2; border-color: #ef4444; color: #991b1b; }

    /* Photo thumbnail */
    .sfcs-photo-thumb {
        position: relative;
        display: block;
        border-radius: .5rem;
        overflow: hidden;
        transition: transform .2s;
    }
    .sfcs-photo-thumb:hover { transform: scale(1.03); }
    .sfcs-photo-zoom {
        position: absolute;
        top: 4px; right: 4px;
        background: rgba(0,0,0,.55);
        color: #fff;
        border-radius: 50%;
        width: 22px; height: 22px;
        display: flex; align-items: center; justify-content: center;
        font-size: .55rem;
    }

    /* Info card items */
    .sfcs-info-list {
        display: flex;
        flex-direction: column;
    }
    .sfcs-info-item {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .65rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s;
    }
    .sfcs-info-item:last-child { border-bottom: none; }
    .sfcs-info-item:hover { background: #f8fafc; }
    .sfcs-info-icon {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: .7rem;
    }
    .sfcs-info-label {
        font-size: .6rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 2px;
    }
    .sfcs-info-value {
        font-size: .82rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.3;
    }

    /* Orange badge support */
    .bg-orange-subtle { background: #fff7ed !important; }
    .text-orange { color: #ea580c !important; }

    /* Card hover — softer for mobile */
    .card { transition: box-shadow .2s; }
    @media (hover: hover) {
        .card:hover { box-shadow: 0 .4rem .8rem rgba(0,0,0,.08) !important; }
    }

    /* ═══ MOBILE (< 768px) ═══ */
    @media (max-width: 767.98px) {
        /* Info card: compact horizontal grid on mobile */
        .sfcs-info-card .sfcs-info-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }
        .sfcs-info-item {
            padding: .6rem .75rem;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
        }
        .sfcs-info-item:nth-child(2n) { border-right: none; }
        /* Lokasi item spans full width */
        .sfcs-info-item:nth-child(3) {
            grid-column: 1 / -1;
            border-right: none;
        }

        /* Stepper smaller on mobile */
        .sfcs-stepper .rounded-circle {
            width: 28px !important;
            height: 28px !important;
        }
        .sfcs-stepper .rounded-circle i { font-size: .55rem !important; }

        /* Rating buttons touch-friendly */
        .btn-outline-warning.btn-sm { padding: .5rem .75rem; }
    }

    /* ═══ TABLET (768 - 991) ═══ */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .sfcs-info-card .sfcs-info-list {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0;
        }
        .sfcs-info-item {
            border-right: 1px solid #f1f5f9;
        }
        .sfcs-info-item:nth-child(3n) { border-right: none; }
        /* Lokasi item spans 2 cols on tablet */
        .sfcs-info-item:nth-child(3) {
            grid-column: span 2;
            border-right: none;
        }
    }
</style>
@endpush


