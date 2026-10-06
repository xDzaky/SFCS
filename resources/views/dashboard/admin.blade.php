@extends('layouts.sfcs')

@section('title', 'Dashboard Admin')

@section('content')
@php
    $statusColors = [
        'pending'      => ['class' => 'badge-status-pending',      'label' => 'Pending'],
        'diverifikasi' => ['class' => 'badge-status-diverifikasi',  'label' => 'Diverifikasi'],
        'diproses'     => ['class' => 'badge-status-diproses',      'label' => 'Diproses'],
        'selesai'      => ['class' => 'badge-status-selesai',       'label' => 'Selesai'],
        'ditolak'      => ['class' => 'badge-status-ditolak',       'label' => 'Ditolak'],
    ];
    $prioritasColors = [
        'urgent' => ['class' => 'badge-prioritas-urgent', 'label' => 'Darurat'],
        'tinggi' => ['class' => 'badge-prioritas-tinggi', 'label' => 'Tinggi'],
        'sedang' => ['class' => 'badge-prioritas-sedang', 'label' => 'Sedang'],
        'rendah' => ['class' => 'badge-prioritas-rendah', 'label' => 'Rendah'],
    ];
@endphp

{{-- ══ OVERLOAD ALERT ════════════════════════════════════════════════════════ --}}
@if(($stats['overload_active'] ?? false) === true)
<div class="alert alert-danger alert-dismissible d-flex justify-content-between align-items-center py-2 mb-3 rounded-3 shadow-sm" role="alert">
    <div class="small">
        <i class="fas fa-triangle-exclamation me-1"></i>
        <strong>Overload aktif:</strong>
        Backlog {{ $stats['urgent_backlog_1h'] ?? 0 }} tiket, kapasitas {{ $stats['capacity_1h'] ?? 0 }}, estimasi delay <strong>{{ $stats['predicted_delay_minutes'] ?? 0 }} menit</strong>.
    </div>
    <a href="{{ route('admin.overload-board') }}" class="btn btn-sm btn-danger">Overload Board</a>
</div>
@endif

{{-- ══ HEADER ════════════════════════════════════════════════════════════════ --}}
<div class="dash-header rounded-4 mb-4 p-3 p-lg-4 text-white d-flex justify-content-between align-items-center flex-wrap gap-3"
     style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 60%,#9333ea 100%);">
    <div class="d-flex align-items-center gap-3">
        <div class="dash-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-0">Dashboard Admin</h5>
            <p class="mb-0 opacity-75 small">Selamat datang, {{ auth()->user()->name }} &mdash; Sarana Prasarana</p>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.pengaduan.index') }}" class="btn btn-light btn-sm rounded-pill shadow-sm px-3 fw-semibold">
            <i class="fas fa-list me-1"></i>Kelola Pengaduan
        </a>
        <a href="{{ route('admin.overload-board') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
            <i class="fas fa-gauge-high me-1"></i>Overload Board
        </a>
    </div>
</div>

{{-- ══ STATS SUMMARY (Clean, Compact, Minimalist Slate Theme) ═══════════════ --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
    <div class="card-body p-2 p-md-3">
        <div class="row g-2">
            {{-- Total Laporan --}}
            <div class="col-6 col-md-3 col-xl">
                <div class="stat-item px-3 py-2 rounded-3 h-100">
                    <div class="stat-title text-muted mb-1">Total Laporan</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-subtle text-primary"><i class="fas fa-clipboard-list"></i></span>
                        <div class="stat-number text-dark">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Pending --}}
            <div class="col-6 col-md-3 col-xl">
                <div class="stat-item px-3 py-2 rounded-3 h-100">
                    <div class="stat-title text-muted mb-1">Pending</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-subtle text-secondary"><i class="fas fa-hourglass-half"></i></span>
                        <div class="stat-number text-dark">{{ $stats['pending'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Sedang Diproses --}}
            <div class="col-6 col-md-3 col-xl">
                <div class="stat-item px-3 py-2 rounded-3 h-100">
                    <div class="stat-title text-muted mb-1">Diproses</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-subtle text-secondary"><i class="fas fa-screwdriver-wrench"></i></span>
                        <div class="stat-number text-dark">{{ $stats['proses'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Overdue --}}
            <div class="col-6 col-md-3 col-xl">
                <div class="stat-item px-3 py-2 rounded-3 h-100">
                    <div class="stat-title text-muted mb-1">Overdue</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-subtle {{ ($stats['overdue'] ?? 0) > 0 ? 'text-danger' : 'text-secondary' }}"><i class="fas fa-fire"></i></span>
                        <div class="stat-number {{ ($stats['overdue'] ?? 0) > 0 ? 'text-danger' : 'text-dark' }}">{{ $stats['overdue'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            {{-- Pinjaman Aktif --}}
            <div class="col-6 col-md-3 col-xl">
                <div class="stat-item px-3 py-2 rounded-3 h-100">
                    <div class="stat-title text-muted mb-1">Pinjaman Aktif</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-subtle text-secondary"><i class="fas fa-box-open"></i></span>
                        <div class="stat-number text-dark">{{ $stats['pinjaman_aktif'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            {{-- Pinjaman Terlambat --}}
            <div class="col-6 col-md-3 col-xl">
                <div class="stat-item px-3 py-2 rounded-3 h-100">
                    <div class="stat-title text-muted mb-1">Pinjaman Terlambat</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-subtle {{ ($stats['pinjaman_terlambat'] ?? 0) > 0 ? 'text-danger' : 'text-secondary' }}"><i class="fas fa-clock-rotate-left"></i></span>
                        <div class="stat-number {{ ($stats['pinjaman_terlambat'] ?? 0) > 0 ? 'text-danger' : 'text-dark' }}">{{ $stats['pinjaman_terlambat'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            {{-- Review Urgensi --}}
            <div class="col-6 col-md-3 col-xl">
                <div class="stat-item px-3 py-2 rounded-3 h-100">
                    <div class="stat-title text-muted mb-1">Review Urgensi</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-subtle text-secondary"><i class="fas fa-triangle-exclamation"></i></span>
                        <div class="stat-number text-dark">{{ $stats['priority_review'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            {{-- Prediksi Delay --}}
            <div class="col-6 col-md-3 col-xl">
                <div class="stat-item px-3 py-2 rounded-3 h-100">
                    <div class="stat-title text-muted mb-1">Prediksi Delay</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-subtle text-secondary"><i class="fas fa-stopwatch"></i></span>
                        <div class="stat-number text-dark">{{ $stats['predicted_delay_minutes'] ?? 0 }}<span class="stat-unit">mnt</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ MAIN CONTENT (Wide Table First, Followed by Category/Gedung & Teknisi) ══════════════ --}}
<div class="row g-3">

    {{-- 1. Pengaduan Terbaru (Full Width untuk kelegaan tabel) ────────────────── --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom border-light-subtle px-3 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="dash-badge-icon bg-light text-dark border">
                        <i class="fas fa-list-check"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-semibold text-dark">Pengaduan Terbaru</h6>
                        @if($totalPengaduans > 8)
                            <small class="text-muted" style="font-size:.75rem;">Menampilkan 8 dari {{ $totalPengaduans }} laporan</small>
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.pengaduan.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:.78rem;">
                    @if($totalPengaduans > 8)
                        Lihat Semua ({{ $totalPengaduans }}) <i class="fas fa-arrow-right ms-1"></i>
                    @else
                        Semua <i class="fas fa-arrow-right ms-1"></i>
                    @endif
                </a>
            </div>

            {{-- Desktop / Tablet table --}}
            <div class="d-none d-sm-block table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.84rem;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="ps-3 text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">KODE</th>
                            <th class="text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">JUDUL</th>
                            <th class="text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">PELAPOR</th>
                            <th class="text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">STATUS</th>
                            <th class="text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">URGENSI</th>
                            <th class="text-center text-muted fw-semibold pe-3" style="font-size:.7rem;letter-spacing:.05em;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPengaduans as $p)
                        <tr class="dash-table-row"
                            onclick="if(!event.target.closest('a')&&!getSelection().toString()) window.location='{{ route('admin.pengaduan.show', $p) }}'">
                            <td class="ps-3">
                                <span class="badge bg-light text-dark border font-monospace" style="font-size:.72rem;">{{ $p->kode_pengaduan }}</span>
                            </td>
                            <td class="fw-medium text-dark">{{ Str::limit($p->judul, 45) }}</td>
                            <td class="text-muted">{{ $p->user->name ?? '-' }}</td>
                            <td>
                                @php $sc = $statusColors[$p->status] ?? ['class'=>'badge-status-pending','label'=>ucfirst($p->status)]; @endphp
                                <span class="badge {{ $sc['class'] }} rounded-pill" style="font-size:.7rem;">{{ $sc['label'] }}</span>
                            </td>
                            <td>
                                @php $pc = $prioritasColors[$p->prioritas] ?? ['class'=>'badge-prioritas-unknown','label'=>ucfirst($p->prioritas)]; @endphp
                                <span class="badge {{ $pc['class'] }} rounded-pill" style="font-size:.7rem;">{{ $pc['label'] }}</span>
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('admin.pengaduan.show', $p) }}" class="btn btn-sm btn-outline-secondary rounded-circle p-1" style="width:28px;height:28px;line-height:1;">
                                    <i class="fas fa-eye" style="font-size:.65rem;"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">Belum ada pengaduan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile card list --}}
            <div class="d-block d-sm-none p-2">
                @forelse($recentPengaduans as $p)
                @php $sc = $statusColors[$p->status] ?? ['badge'=>'secondary','label'=>ucfirst($p->status)]; @endphp
                <a href="{{ route('admin.pengaduan.show', $p) }}" class="text-decoration-none">
                    <div class="d-flex align-items-start gap-2 p-2 mb-1 rounded-3 hover-bg">
                        <div class="rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center bg-light text-secondary border" style="width:36px;height:36px;font-size:.75rem;">
                            <i class="fas fa-file-lines"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start gap-1">
                                <span class="fw-semibold text-dark small" style="font-size:.83rem;">{{ Str::limit($p->judul, 38) }}</span>
                                <span class="badge {{ $sc['class'] }}" style="font-size:.65rem;white-space:nowrap;">{{ $sc['label'] }}</span>
                            </div>
                            <div class="text-muted" style="font-size:.7rem;">
                                <span class="font-monospace">{{ $p->kode_pengaduan }}</span>
                                &middot; {{ $p->user->name ?? '-' }}
                            </div>
                        </div>
                    </div>
                </a>
                @empty
                <p class="text-muted text-center small py-3 mb-0">Belum ada pengaduan</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 2. Per Kategori (Di Bawah Pengaduan Terbaru) ────────────────────────── --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-bottom border-light-subtle px-3 py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="dash-badge-icon bg-light text-dark border">
                        <i class="fas fa-chart-pie"></i>
                    </span>
                    <h6 class="mb-0 fw-semibold text-dark">Per Kategori</h6>
                </div>
            </div>
            <div class="card-body p-3">
                @forelse($byKategori as $kategori)
                    @php $pct = $stats['total'] > 0 ? round(($kategori->pengaduans_count / $stats['total']) * 100) : 0; @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-medium text-dark">{{ $kategori->nama }}</span>
                            <span class="badge rounded-pill bg-light text-dark border" style="font-size:.7rem;">{{ $kategori->pengaduans_count }}</span>
                        </div>
                        <div class="progress rounded-pill bg-light" style="height:6px;">
                            <div class="progress-bar bg-dark-subtle rounded-pill" style="width:{{ $pct }}%; background-color:#6366f1 !important;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center small py-2 mb-0">Belum ada data kategori</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 3. Per Gedung (Di Bawah Pengaduan Terbaru) ─────────────────────────── --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-bottom border-light-subtle px-3 py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="dash-badge-icon bg-light text-dark border">
                        <i class="fas fa-building"></i>
                    </span>
                    <h6 class="mb-0 fw-semibold text-dark">Per Gedung</h6>
                </div>
            </div>
            <div class="card-body p-3">
                @forelse($byGedung as $gedung)
                    @php $pct = $stats['total'] > 0 ? round(($gedung['total'] / $stats['total']) * 100) : 0; @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-medium text-dark text-truncate me-2" style="max-width:180px;">{{ $gedung['nama'] }}</span>
                            <span class="badge rounded-pill bg-light text-dark border" style="font-size:.7rem;">{{ $gedung['total'] }}</span>
                        </div>
                        <div class="progress rounded-pill bg-light" style="height:6px;">
                            <div class="progress-bar rounded-pill" style="width:{{ $pct }}%; background-color:#475569 !important;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center small py-2 mb-0">Belum ada data gedung</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 4. Teknisi Utilization (Di Samping Kategori & Gedung) ───────────────── --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-bottom border-light-subtle px-3 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="dash-badge-icon bg-light text-dark border">
                        <i class="fas fa-users-cog"></i>
                    </span>
                    <h6 class="mb-0 fw-semibold text-dark">Teknisi Utilization</h6>
                </div>
                <a href="{{ route('admin.overload-board') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:.75rem;">
                    <i class="fas fa-gauge-high me-1"></i>Board
                </a>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-column gap-2">
                    @forelse($teknisis as $teknisi)
                    @php
                        $pct      = $teknisi->utilization_percent ?? 0;
                        $barColor = $pct >= 80 ? 'danger' : ($pct >= 50 ? 'warning' : 'success');
                        $initial  = strtoupper(substr($teknisi->name, 0, 1));
                    @endphp
                    <div class="dash-teknisi-card">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="dash-teknisi-avatar bg-light text-dark border">{{ $initial }}</div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold small text-dark">{{ Str::limit($teknisi->name, 22) }}</div>
                                <div class="text-muted" style="font-size:.69rem;">
                                    {{ $teknisi->active_ticket_count ?? 0 }} tiket aktif
                                    @if(($teknisi->urgent_ticket_count ?? 0) > 0)
                                        &middot; <span class="text-danger fw-semibold">{{ $teknisi->urgent_ticket_count }} urgent</span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border rounded-pill flex-shrink-0" style="font-size:.7rem;">{{ $pct }}%</span>
                        </div>
                        <div class="progress rounded-pill bg-light" style="height:5px;">
                            <div class="progress-bar bg-{{ $barColor }} rounded-pill" style="width:{{ min($pct,100) }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center small py-2 mb-0">Belum ada teknisi aktif</p>
                    @endforelse
                </div>
                @if($teknisis->isNotEmpty())
                    <div class="border-top pt-2 mt-2">
                        <small class="text-muted" style="font-size:.69rem;">
                            <i class="fas fa-circle-info me-1"></i>
                            Kapasitas default {{ $teknisis->first()->capacity_per_hour ?? 2 }} tiket/jam/teknisi
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>{{-- /main row --}}


@push('styles')
<style>
    /* ══ Dashboard Admin Styles ══════════════════════════════════════════════ */

    /* Header */
    .dash-avatar {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.2);
        border: 2px solid rgba(255,255,255,.3);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    /* Clean Minimalist Stat items */
    .stat-item {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        transition: background .2s, border-color .2s;
    }
    .stat-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .stat-icon-subtle {
        font-size: .85rem;
        opacity: .85;
    }
    .stat-title {
        font-size: .73rem;
        font-weight: 500;
        letter-spacing: .01em;
        white-space: nowrap;
    }
    .stat-number {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -.02em;
    }
    .stat-unit {
        font-size: .8rem;
        font-weight: 500;
        margin-left: 2px;
        opacity: .7;
    }

    /* Badge icon in card headers */
    .dash-badge-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem;
        flex-shrink: 0;
    }

    /* Table rows */
    .dash-table-row {
        cursor: pointer;
        transition: background .15s;
    }
    .dash-table-row:hover { background: #f8fafc; }

    /* Mobile hover bg */
    .hover-bg { transition: background .15s; }
    .hover-bg:hover { background: #f8fafc; }

    /* Teknisi card */
    .dash-teknisi-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: .75rem;
        border: 1px solid #f1f5f9;
    }
    .dash-teknisi-avatar {
        width: 34px; height: 34px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* Card consistent style */
    .card { transition: box-shadow .2s; }

    /* ═══ DESKTOP (lg+) tweaks ═════════════════════════════════════════════ */
    @media (min-width: 992px) {
        .stat-number { font-size: 1.55rem; }
        .dash-avatar { width: 60px; height: 60px; font-size: 1.6rem; }
    }

    /* ═══ TABLET (md) ══════════════════════════════════════════════════════ */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .stat-number { font-size: 1.4rem; }
    }

    /* ═══ MOBILE (< 576px) ════════════════════════════════════════════════ */
    @media (max-width: 575.98px) {
        .stat-number { font-size: 1.25rem; }
        .stat-item { padding: .55rem .75rem !important; border-radius: 10px; }
        .dash-header { border-radius: 14px !important; }
        .dash-avatar { width: 44px; height: 44px; font-size: 1.1rem; border-radius: 11px; }
    }
</style>
@endpush

@endsection
