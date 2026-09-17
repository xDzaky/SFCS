@extends('layouts.sfcs')

@section('title', 'Detail Pengaduan - Admin')

@push('styles')
<style>
    /* ── Layout ── */
    .adm-row { align-items: start; }
    .adm-sidebar-sticky { position: sticky; top: 72px; }

    /* ── Info table email fix ── */
    .adm-info-email { word-break: break-all; font-size: .82rem; overflow-wrap: anywhere; }

    /* ── Full-width tall map ── */
    .adm-map-fullwrapper .card { margin-bottom: 0 !important; border-radius: 1rem !important; overflow: hidden; }
    .adm-map-fullwrapper .sfcs-map-stage { min-height: 420px !important; height: 420px !important; }
    .adm-map-fullwrapper .sfcs-map-frame { border-radius: 0 !important; border-left: none !important; border-right: none !important; border-bottom: none !important; }

    /* ── Collapse chevron ── */
    .adm-collapse-header { cursor: pointer; user-select: none; }
    .adm-collapse-chevron { transition: transform .25s; }
    .adm-collapse-header[aria-expanded="true"] .adm-collapse-chevron { transform: rotate(180deg); }

    /* Status stepper */
    .status-stepper .step-circle {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .875rem; border: 2px solid #e5e7eb;
        background: #fff; color: #9ca3af;
        transition: all .3s; position: relative; z-index: 2;
    }
    .status-stepper .step-circle.active {
        background: var(--primary-color); border-color: var(--primary-color);
        color: #fff; transform: scale(1.15);
        box-shadow: 0 4px 12px rgba(79,70,229,.35);
    }
    .status-stepper .step-circle.past {
        background: var(--primary-color); border-color: var(--primary-color); color: #fff;
    }
    .status-stepper .step-label { font-size: .75rem; margin-top: .375rem; font-weight: 600; }
    .status-stepper .step-label.active { color: var(--primary-color); }
    .status-stepper .step-label.past  { color: var(--primary-color); }
    /* Rating stars */
    .rating-stars .fa-star { color: #f59e0b; font-size: 1.1rem; }
    .rating-stars .fa-star.empty { color: #d1d5db; }
    /* Sidebar action card accent */
    .sidebar-actions .card { border-top: 3px solid transparent; }
    .sidebar-actions .card.card-status  { border-top-color: #6366f1; }
    .sidebar-actions .card.card-assign  { border-top-color: #10b981; }
    .sidebar-actions .card.card-reject  { border-top-color: #ef4444; }
    .sidebar-actions .card.card-urgency { border-top-color: #f59e0b; }

    /* Map full-width style */
    .adm-map-fullcard { border-radius: 1rem; overflow: hidden; }
    .adm-map-fullcard .sfcs-map-frame { border-radius: 0; border: none; }
    .adm-map-fullcard .sfcs-map-stage { min-height: 420px !important; height: 420px; }

    /* Info table row */
    .info-row { display: flex; align-items: center; padding: .5rem .25rem; border-bottom: 1px solid #f3f4f6; gap: .75rem; }
    .info-row:last-child { border-bottom: none; }
    .info-row .info-label { font-size: .75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .04em; flex: 0 0 80px; }
    .info-row .info-val { font-size: .875rem; font-weight: 500; color: #111827; }

    /* ── Badge Prioritas & Status ─────────────────────────────────── */
    .badge-status { font-size: .72rem; font-weight: 600; letter-spacing: .02em; }
    .badge-rendah  { background-color: #16a34a !important; color: #fff !important; }
    .badge-sedang  { background-color: #d97706 !important; color: #fff !important; }
    .badge-tinggi  { background-color: #ea580c !important; color: #fff !important; }
    .badge-urgent  { background-color: #dc2626 !important; color: #fff !important; }
    .badge-pending      { background-color: #6b7280 !important; color: #fff !important; }
    .badge-diverifikasi { background-color: #0284c7 !important; color: #fff !important; }
    .badge-diproses     { background-color: #d97706 !important; color: #fff !important; }
    .badge-selesai      { background-color: #16a34a !important; color: #fff !important; }
    .badge-ditolak      { background-color: #dc2626 !important; color: #fff !important; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="collapse"].adm-collapse-header').forEach(function(header) {
            var targetId = header.getAttribute('data-bs-target');
            var target = document.querySelector(targetId);
            if (!target) return;
            target.addEventListener('show.bs.collapse', function () { header.setAttribute('aria-expanded', 'true'); });
            target.addEventListener('hide.bs.collapse', function () { header.setAttribute('aria-expanded', 'false'); });
        });
    });
</script>
@endpush

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.pengaduan.index') }}">Kelola Pengaduan</a></li>
    <li class="breadcrumb-item active">{{ $pengaduan->kode_pengaduan }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $pengaduan->kode_pengaduan }}</h1>
        <p class="page-subtitle">{{ $pengaduan->judul }}</p>
    </div>
    <a href="{{ route('admin.pengaduan.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@if($pengaduan->is_marked_duplicate && $pengaduan->duplicateOf)
    <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong>Tiket ini ditandai duplikat</strong> dari
            <a href="{{ route('admin.pengaduan.show', $pengaduan->duplicateOf) }}" class="alert-link">
                {{ $pengaduan->duplicateOf->kode_pengaduan }}
            </a>
            @if($pengaduan->duplicateMarker)
                <small class="text-muted d-block">
                    Ditandai oleh {{ $pengaduan->duplicateMarker->name }}{{ $pengaduan->duplicate_marked_at ? ' pada ' . $pengaduan->duplicate_marked_at->format('d/m/Y H:i') : '' }}
                </small>
            @endif
        </div>
        <form action="{{ route('admin.pengaduan.unmark-duplicate', $pengaduan) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan Tandai Duplikat</button>
        </form>
    </div>
@endif

@if($pengaduan->is_auto_closed_duplicate && $pengaduan->autoClosedFromMaster)
    <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <strong>Ditutup otomatis dari tiket master</strong>
            <a href="{{ route('admin.pengaduan.show', $pengaduan->autoClosedFromMaster) }}" class="alert-link">
                {{ $pengaduan->autoClosedFromMaster->kode_pengaduan }}
            </a>
            @if($pengaduan->auto_closed_at)
                <small class="text-muted d-block">Pada {{ $pengaduan->auto_closed_at->format('d/m/Y H:i') }}</small>
            @endif
        </div>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════
     FULL-WIDTH MAP (wrapper overrides height via CSS)
═══════════════════════════════════════════════════════ --}}
<div class="adm-map-fullwrapper mb-4">
    @include('partials.pengaduan-map-card', [
        'mapPayload'      => $mapPayload,
        'viewerId'        => 'admin-pengaduan-map',
        'title'           => 'Lokasi Kerusakan di Denah',
        'fullMapUrl'      => route('admin.peta-digital'),
        'showCoordinates' => false,
        'showInfoDenah'   => false,
    ])
</div>


{{-- ══════════════════════════════════════════════════════
     MAIN 2-COLUMN LAYOUT
═══════════════════════════════════════════════════════ --}}
<div class="row g-3 adm-row">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-8 order-2 order-lg-1">

        {{-- Detail Pengaduan --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-alt me-2"></i>Detail Pengaduan</span>
                <div class="d-flex gap-2">
                    <span class="badge badge-status badge-{{ $pengaduan->prioritas }}">{{ ucfirst($pengaduan->prioritas) }}</span>
                    <span class="badge badge-status badge-{{ $pengaduan->status }}">{{ ucfirst($pengaduan->status) }}</span>
                </div>
            </div>
            <div class="card-body">
                <h5 class="fw-semibold mb-3">{{ $pengaduan->judul }}</h5>
                <p style="white-space: pre-line;">{{ $pengaduan->deskripsi }}</p>
            </div>
        </div>

        {{-- Foto Bukti --}}
        @if($pengaduan->photos->count() > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-images me-2"></i>Foto Bukti ({{ $pengaduan->photos->count() }})
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($pengaduan->photos as $photo)
                            <div class="col-4 col-sm-3 col-md-2">
                                <a href="{{ $photo->url }}" data-fancybox="gallery" data-caption="Foto Bukti - {{ $pengaduan->judul }}">
                                    <img src="{{ $photo->url }}" class="img-fluid rounded"
                                         style="width:100%;height:90px;object-fit:cover;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Edit Urgensi --}}
        @if(!in_array($pengaduan->status, ['selesai', 'ditolak']))
            <div class="card mb-3 card-urgency">
                <div class="card-header bg-warning-subtle d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-sliders me-2"></i>Edit Urgensi</span>
                    @if($pengaduan->needs_priority_review)
                        <span class="badge bg-warning text-dark">Butuh Review</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 bg-light text-center">
                                <div class="small text-muted mb-1">Permintaan Pelapor</div>
                                <div class="fw-bold">{{ ucfirst($pengaduan->requested_prioritas ?? $pengaduan->prioritas) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 bg-light text-center">
                                <div class="small text-muted mb-1">Prioritas Sistem</div>
                                <div class="fw-bold">{{ ucfirst($pengaduan->prioritas) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 bg-light text-center">
                                <div class="small text-muted mb-1">Skor Dampak</div>
                                <div class="fw-bold">{{ $pengaduan->priority_score ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('admin.pengaduan.prioritas', $pengaduan) }}" method="POST">
                        @csrf
                        <div class="row g-2">
                            <div class="col-sm-4">
                                <label class="form-label small text-muted">Prioritas Final</label>
                                <select name="prioritas" class="form-select form-select-sm" required>
                                    @foreach(['rendah', 'sedang', 'tinggi', 'urgent'] as $priority)
                                        <option value="{{ $priority }}" {{ $pengaduan->prioritas === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <label class="form-label small text-muted">Alasan Penyesuaian</label>
                                <input type="text" name="prioritas_adjust_reason" class="form-control form-control-sm" required placeholder="Wajib isi alasan perubahan urgensi.">
                            </div>
                        </div>
                        <div class="mt-2 d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning btn-sm px-4">Simpan Urgensi</button>
                        </div>
                    </form>
                    @if($pengaduan->prioritas_adjusted_at)
                        <div class="small text-muted mt-2 pt-2 border-top">
                            Terakhir diubah: {{ $pengaduan->prioritas_adjusted_at->format('d/m/Y H:i') }} oleh {{ $pengaduan->priorityAdjustedBy->name ?? 'Admin' }}.
                            @if($pengaduan->prioritas_adjust_reason)
                                <span class="ms-1">— {{ $pengaduan->prioritas_adjust_reason }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Selesai / Ditolak banner --}}
        @if(in_array($pengaduan->status, ['selesai', 'ditolak']))
            <div class="card mb-3">
                <div class="card-body text-center py-4">
                    @if($pengaduan->status === 'selesai')
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="mb-0 fw-semibold">Pengaduan telah selesai</p>
                    @else
                        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                        <p class="mb-0 fw-semibold">Pengaduan ditolak</p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Activity Logs --}}
        <div class="card mb-3">
            <div class="card-header adm-collapse-header d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#activityLogsCollapse" aria-expanded="false">
                <span>
                    <i class="fas fa-history me-2"></i>Riwayat Aktivitas
                    @if($logs->count() > 0)
                        <span class="badge bg-secondary ms-1">{{ $logs->count() }}</span>
                    @endif
                </span>
                <i class="fas fa-chevron-down adm-collapse-chevron text-muted"></i>
            </div>
            <div class="collapse" id="activityLogsCollapse">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($logs as $log)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="badge bg-secondary me-2">{{ $log->action }}</span>
                                        {{ $log->description }}
                                    </div>
                                    <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                @if($log->user)
                                    <small class="text-muted">oleh: {{ $log->user->name }}</small>
                                @endif
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted">Belum ada aktivitas</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Kandidat Duplikat --}}
        <div class="card mb-4" id="duplicate-panel">
            <div class="card-header"><i class="fas fa-clone me-2"></i>Kandidat Duplikat</div>
            <div class="card-body">
                @if($duplicateCandidates->isEmpty())
                    <p class="text-muted mb-0 small">Belum ada kandidat duplikat aktif untuk tiket ini.</p>
                @else
                    <div class="list-group list-group-flush mb-3">
                        @foreach($duplicateCandidates as $candidate)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <a href="{{ route('admin.pengaduan.show', $candidate) }}" class="fw-semibold text-decoration-none">
                                            {{ $candidate->kode_pengaduan }}
                                        </a>
                                        <div class="small">{{ $candidate->judul }}</div>
                                        <div class="small text-muted">
                                            Pelapor: {{ $candidate->user->name ?? '-' }} |
                                            {{ $candidate->created_at->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ $candidate->gedung->nama ?? $candidate->ruangan->gedung->nama ?? '-' }} - {{ $candidate->lokasi_detail ?: '-' }}
                                        </div>
                                    </div>
                                    @if(!$pengaduan->is_auto_closed_duplicate && (!$pengaduan->is_marked_duplicate || $pengaduan->duplicate_of_id !== $candidate->id))
                                        <form action="{{ route('admin.pengaduan.mark-duplicate', $pengaduan) }}" method="POST" class="text-end">
                                            @csrf
                                            <input type="hidden" name="master_pengaduan_id" value="{{ $candidate->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Tandai Duplikat</button>
                                        </form>
                                    @elseif($pengaduan->is_auto_closed_duplicate)
                                        <span class="badge bg-secondary">Readonly</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($pengaduan->is_auto_closed_duplicate)
                        <div class="alert alert-light border small mb-2">
                            Tiket ini sudah auto-closed sebagai duplikat, penandaan kandidat bersifat readonly.
                        </div>
                    @endif
                    <form action="{{ route('admin.pengaduan.mark-duplicate', $pengaduan) }}" method="POST">
                        @csrf
                        <label for="master_pengaduan_id" class="form-label small text-muted">Atau pilih tiket utama secara manual</label>
                        <div class="input-group input-group-sm">
                            <select name="master_pengaduan_id" id="master_pengaduan_id" class="form-select" required>
                                <option value="">Pilih tiket utama...</option>
                                @foreach($duplicateCandidates as $candidate)
                                    <option value="{{ $candidate->id }}">{{ $candidate->kode_pengaduan }} - {{ Str::limit($candidate->judul, 40) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-warning" {{ $pengaduan->is_auto_closed_duplicate ? 'disabled' : '' }}>Tandai</button>
                        </div>
                        <textarea name="duplicate_note" class="form-control form-control-sm mt-2" rows="2" placeholder="Catatan internal (opsional)" {{ $pengaduan->is_auto_closed_duplicate ? 'disabled' : '' }}></textarea>
                    </form>
                @endif
            </div>
        </div>

    </div>{{-- end left column --}}

    {{-- ── RIGHT SIDEBAR COLUMN ── --}}
    <div class="col-lg-4 order-1 order-lg-2 sidebar-actions">
        <div class="adm-sidebar-sticky">

            {{-- Informasi --}}
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header border-0 bg-white pb-0 pt-3">
                    <span class="fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Pengaduan</span>
                </div>
                <div class="card-body pt-2">
                    <div class="info-row">
                        <div class="info-label">Pelapor</div>
                        <div class="info-val">
                            <i class="fas fa-user-circle text-muted me-1"></i>
                            {{ $pengaduan->user->name ?? '-' }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-val adm-info-email">{{ $pengaduan->user->email ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Kategori</div>
                        <div class="info-val">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $pengaduan->kategori->nama ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Gedung</div>
                        <div class="info-val">{{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Ruangan</div>
                        <div class="info-val">
                            @if($pengaduan->ruangan)
                                {{ $pengaduan->ruangan->nama }} <small class="text-muted">(Lt. {{ $pengaduan->ruangan->lantai ?? '-' }})</small>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Teknisi</div>
                        <div class="info-val">
                            @if($pengaduan->assignedTo)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="fas fa-user-cog me-1"></i>{{ $pengaduan->assignedTo->name }}
                                </span>
                            @else
                                <span class="text-muted small">Belum ditugaskan</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dibuat</div>
                        <div class="info-val">{{ $pengaduan->created_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>

            {{-- Status Laporan --}}
            @if(!in_array($pengaduan->status, ['selesai', 'ditolak']))
                <div class="card mb-3 card-status border-0 shadow-sm">
                    <div class="card-header border-0 bg-white pb-0 pt-3">
                        <span class="fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>Status Laporan</span>
                    </div>
                    <div class="card-body">
                        {{-- Status stepper --}}
                        <div class="status-stepper mb-3">
                            @php
                                $steps = [
                                    'pending'       => ['icon' => 'fa-paper-plane', 'label' => 'Terkirim'],
                                    'diverifikasi'  => ['icon' => 'fa-clipboard-check', 'label' => 'Diverifikasi'],
                                    'diproses'      => ['icon' => 'fa-tools', 'label' => 'Diproses'],
                                    'selesai'       => ['icon' => 'fa-check', 'label' => 'Selesai']
                                ];
                                $currentFound = false;
                            @endphp
                            <div class="d-flex justify-content-between position-relative">
                                <div class="position-absolute top-50 start-0 w-100 translate-middle-y bg-light" style="height:4px;z-index:1;"></div>
                                @foreach($steps as $key => $step)
                                    @php
                                        $isActive = $key === $pengaduan->status;
                                        $isPast   = !$isActive && !$currentFound;
                                        if ($isActive) $currentFound = true;
                                        $circleClass = $isActive ? 'active' : ($isPast ? 'past' : '');
                                    @endphp
                                    <div class="text-center position-relative" style="z-index:2;flex:1;">
                                        <div class="step-circle mx-auto {{ $circleClass }}"><i class="fas {{ $step['icon'] }}"></i></div>
                                        <div class="step-label {{ $circleClass ?: 'text-muted' }}">{{ $step['label'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <form action="{{ route('admin.pengaduan.status', $pengaduan) }}" method="POST">
                            @csrf
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold text-muted mb-0">Update Manual:</label>
                                <span class="badge bg-light text-muted border fw-normal" style="font-size:.65rem;">Biasanya oleh Teknisi</span>
                            </div>
                            <div class="d-grid gap-2">
                                @if($pengaduan->status == 'pending')
                                    <button type="submit" name="status" value="diverifikasi" class="btn btn-info text-white btn-sm">
                                        <i class="fas fa-clipboard-check me-2"></i>Verifikasi Pengaduan
                                    </button>
                                @elseif($pengaduan->status == 'diverifikasi')
                                    <button type="submit" name="status" value="diproses" class="btn btn-warning text-dark btn-sm">
                                        <i class="fas fa-tools me-2"></i>Mulai Proses
                                    </button>
                                @elseif($pengaduan->status == 'diproses')
                                    <button type="submit" name="status" value="selesai" class="btn btn-success btn-sm">
                                        <i class="fas fa-check-circle me-2"></i>Tandai Selesai
                                    </button>
                                @endif
                            </div>
                            <div class="mt-2">
                                <textarea name="catatan_admin" class="form-control form-control-sm" rows="2" placeholder="Catatan Status (Opsional)"></textarea>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tugaskan Teknisi --}}
                <div class="card mb-3 card-assign border-0 shadow-sm">
                    <div class="card-header border-0 bg-white pb-0 pt-3">
                        <span class="fw-bold"><i class="fas fa-user-cog me-2 text-success"></i>Tugaskan Teknisi</span>
                    </div>
                    <div class="card-body">
                        @if($pengaduan->teknisi_id)
                            <div class="d-flex align-items-center gap-2 p-2 bg-success-subtle rounded-3 mb-3">
                                <i class="fas fa-user-check text-success"></i>
                                <div>
                                    <div class="fw-semibold small">{{ $pengaduan->assignedTo->name }}</div>
                                    <div class="small text-muted">Sedang ditugaskan</div>
                                </div>
                            </div>
                        @endif
                        <form action="{{ route('admin.pengaduan.assign', $pengaduan) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <select name="teknisi_id" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Teknisi --</option>
                                    @foreach($teknisis as $teknisi)
                                        <option value="{{ $teknisi->id }}" {{ $pengaduan->teknisi_id == $teknisi->id ? 'selected' : '' }}>{{ $teknisi->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100 btn-sm">
                                <i class="fas fa-user-plus me-2"></i>{{ $pengaduan->teknisi_id ? 'Ganti' : 'Tugaskan' }} Teknisi
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Tolak Pengaduan --}}
                <div class="card mb-3 card-reject border-0 shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <span class="fw-bold"><i class="fas fa-times me-2"></i>Tolak Pengaduan</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.pengaduan.reject', $pengaduan) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <textarea name="alasan_tolak" class="form-control form-control-sm" rows="3" placeholder="Wajib isi alasan penolakan..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100 btn-sm" onclick="return confirm('Yakin ingin menolak pengaduan ini?')">
                                <i class="fas fa-times-circle me-2"></i>Tolak Pengaduan
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Reschedule history --}}
            @if($pengaduan->schedules->count() > 0)
                <div class="card mb-3 border-info border-0 shadow-sm">
                    <div class="card-header bg-info-subtle">
                        <i class="fas fa-calendar-days me-2"></i>Riwayat Reschedule
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Waktu</th><th>Dari</th><th>Ke</th><th>Oleh</th><th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pengaduan->schedules->take(5) as $schedule)
                                        <tr>
                                            <td>{{ optional($schedule->created_at)->format('d/m H:i') ?? '-' }}</td>
                                            <td>{{ optional($schedule->from_start)->format('d/m H:i') ?? '-' }}</td>
                                            <td>{{ optional($schedule->to_start)->format('d/m H:i') ?? '-' }}</td>
                                            <td>{{ $schedule->changer->name ?? 'Sistem' }}</td>
                                            <td class="small">{{ $schedule->reason }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Reopen request --}}
            @if($pengaduan->has_reopen_request)
                <div class="card mb-3 border-warning border-0 shadow-sm">
                    <div class="card-header bg-warning-subtle">
                        <i class="fas fa-redo me-2"></i>Permintaan Buka Ulang
                    </div>
                    <div class="card-body">
                        <p class="small mb-2"><strong>Alasan pelapor:</strong></p>
                        <p class="small text-muted">{{ $pengaduan->reopen_reason }}</p>
                        <p class="small mb-3">Diajukan pada {{ optional($pengaduan->reopen_requested_at)->format('d/m/Y H:i') }}</p>
                        <form action="{{ route('admin.pengaduan.approve-reopen', $pengaduan) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100">Setujui Buka Ulang</button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Feedback --}}
            @if($pengaduan->feedbackDetail)
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header"><i class="fas fa-star me-2"></i>Feedback Pelapor</div>
                    <div class="card-body">
                        <div class="rating-stars mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $pengaduan->feedbackDetail->average_rating ? '' : 'empty' }}"></i>
                            @endfor
                            <span class="ms-2 fw-semibold">{{ number_format($pengaduan->feedbackDetail->average_rating, 1) }}/5</span>
                        </div>
                        @if($pengaduan->feedbackDetail->komentar)
                            <p class="mb-0 small text-muted fst-italic">"{{ $pengaduan->feedbackDetail->komentar }}"</p>
                        @endif
                    </div>
                </div>
            @endif

        </div>{{-- end adm-sidebar-sticky --}}
    </div>

</div>

@endsection
