@extends('layouts.sfcs')

@section('title', 'Detail Pengajuan — ' . $pinjaman->kode_pinjaman)

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.pinjaman.index') }}">Kelola Pengajuan</a></li>
    <li class="breadcrumb-item active">{{ $pinjaman->kode_pinjaman }}</li>
</ol>
@endsection

@section('content')
@php
    $isPermintaan = $pinjaman->tipe === 'minta';
    $statusColors = [
        'pending'   => 'badge-pinjaman-pending',
        'disetujui' => 'badge-pinjaman-disetujui',
        'dipinjam'  => 'badge-pinjaman-dipinjam',
        'terlambat' => 'badge-pinjaman-terlambat',
        'selesai'   => 'badge-pinjaman-selesai',
        'ditolak'   => 'badge-pinjaman-ditolak',
    ];
    $statusColor = $statusColors[$pinjaman->status] ?? 'badge-pinjaman-pending';
@endphp

<div class="row g-3">
    {{-- Main Info --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <strong>{{ $pinjaman->kode_pinjaman }}</strong>
                    @if($isPermintaan)
                        <span class="badge bg-success">Permintaan Barang</span>
                    @else
                        <span class="badge bg-primary">Peminjaman Barang</span>
                    @endif
                </div>
                <span class="badge {{ $statusColor }} fs-6">{{ ucfirst($pinjaman->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="small text-muted">Pemohon</div>
                        <div class="fw-semibold">{{ $pinjaman->user->name ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Barang</div>
                        <div class="fw-semibold">{{ $pinjaman->barang->nama ?? '-' }}</div>
                        @if($pinjaman->barang)
                            <span class="badge {{ $pinjaman->barang->unit_sarpras === 'bawah' ? 'bg-success' : 'bg-primary' }} bg-opacity-75 mt-1">
                                {{ $pinjaman->barang->label_unit_sarpras }}
                            </span>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Jumlah</div>
                        <div class="fw-semibold">{{ $pinjaman->qty }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Tanggal Pengajuan</div>
                        <div class="fw-semibold">{{ optional($pinjaman->tgl_pinjam)->format('d/m/Y H:i') }}</div>
                    </div>
                    @if(!$isPermintaan)
                    <div class="col-sm-6">
                        <div class="small text-muted">Jatuh Tempo</div>
                        <div class="fw-semibold">{{ optional($pinjaman->tgl_jatuh_tempo)->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                    @if($pinjaman->tgl_kembali)
                    <div class="col-sm-6">
                        <div class="small text-muted">{{ $isPermintaan ? 'Tanggal Diserahkan' : 'Tanggal Kembali' }}</div>
                        <div class="fw-semibold">{{ optional($pinjaman->tgl_kembali)->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="small text-muted">Alasan / Keperluan</div>
                    <div class="p-3 bg-light rounded">{{ $pinjaman->alasan }}</div>
                </div>

                @if($pinjaman->catatan_admin)
                    <div class="alert alert-info mb-3">
                        <strong><i class="fas fa-comment me-1"></i>Catatan Admin:</strong>
                        {{ $pinjaman->catatan_admin }}
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if($pinjaman->status === 'pending')
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="fas fa-check me-1"></i>{{ $isPermintaan ? 'Setujui & Serahkan' : 'Setujui' }}
                        </button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-1"></i>Tolak
                        </button>
                    @endif

                    @if(!$isPermintaan && $pinjaman->status === 'disetujui')
                        <form method="POST" action="{{ route('admin.pinjaman.checkout', $pinjaman) }}">
                            @csrf
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-box-arrow-right me-1"></i>Check-out Barang
                            </button>
                        </form>
                    @endif

                    @if(!$isPermintaan && in_array($pinjaman->status, ['dipinjam','terlambat']))
                        <form method="POST" action="{{ route('admin.pinjaman.checkin', $pinjaman) }}">
                            @csrf
                            <button class="btn btn-success btn-sm" type="submit">
                                <i class="fas fa-undo me-1"></i>Check-in / Selesai
                            </button>
                        </form>
                    @endif

                    @if(!in_array($pinjaman->status, ['selesai','ditolak']))
                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#forceCloseModal">
                            <i class="fas fa-ban me-1"></i>Force Close
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Activity Log --}}
        <div class="card mt-3 shadow-sm border-0">
            <div class="card-header fw-semibold"><i class="fas fa-history me-2"></i>Riwayat Aktivitas</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($pinjaman->logs as $log)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <span>{{ $log->description ?? $log->action }}</span>
                            <small class="text-muted text-nowrap ms-3">{{ $log->created_at?->format('d/m H:i') }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Stok Info --}}
        <div class="card shadow-sm border-0">
            <div class="card-header fw-semibold"><i class="fas fa-boxes me-2"></i>Stok Barang</div>
            <div class="card-body">
                @if($pinjaman->barang)
                    <div class="row text-center g-2 mb-3">
                        <div class="col-4">
                            <div class="border rounded-3 py-2">
                                <div class="fw-bold fs-5">{{ $pinjaman->barang->stok_total }}</div>
                                <div class="small text-muted">Total</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-3 py-2">
                                <div class="fw-bold fs-5 text-info">{{ $pinjaman->barang->stok_tersedia }}</div>
                                <div class="small text-muted">Tersedia</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-3 py-2">
                                <div class="fw-bold fs-5 text-danger">{{ $pinjaman->barang->stok_rusak }}</div>
                                <div class="small text-muted">Rusak</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.barang.adjust-stock', $pinjaman->barang) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small">Stok Total</label>
                            <input class="form-control form-control-sm" type="number" name="stok_total" value="{{ $pinjaman->barang->stok_total }}" min="0">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Stok Tersedia</label>
                            <input class="form-control form-control-sm" type="number" name="stok_tersedia" value="{{ $pinjaman->barang->stok_tersedia }}" min="0">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Stok Rusak</label>
                            <input class="form-control form-control-sm" type="number" name="stok_rusak" value="{{ $pinjaman->barang->stok_rusak }}" min="0">
                        </div>
                        <button class="btn btn-outline-primary btn-sm w-100" type="submit">Update Stok</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Tipe Info Card --}}
        <div class="card mt-3 shadow-sm border-0">
            <div class="card-body">
                @if($isPermintaan)
                    <div class="d-flex gap-2 align-items-start">
                        <div class="text-success fs-4"><i class="fas fa-info-circle"></i></div>
                        <div>
                            <div class="fw-semibold text-success">Permintaan Barang</div>
                            <p class="small text-muted mb-0">Barang habis pakai — tidak perlu dikembalikan.
                            Saat disetujui, stok langsung dikurangi dan status otomatis selesai.</p>
                        </div>
                    </div>
                @else
                    <div class="d-flex gap-2 align-items-start">
                        <div class="text-primary fs-4"><i class="fas fa-info-circle"></i></div>
                        <div>
                            <div class="fw-semibold text-primary">Peminjaman Barang</div>
                            <p class="small text-muted mb-0">Barang harus dikembalikan. Setelah disetujui,
                            lakukan <strong>Check-out</strong> saat barang diserahkan, dan <strong>Check-in</strong> saat dikembalikan.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pinjaman.approve', $pinjaman) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        {{ $isPermintaan ? 'Setujui & Serahkan Barang' : 'Setujui Pinjaman' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($isPermintaan)
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle me-1"></i>
                            Menyetujui permintaan akan <strong>langsung mengurangi stok</strong> dan menandai transaksi sebagai selesai.
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>{{ $isPermintaan ? 'Setujui & Serahkan' : 'Setujui' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pinjaman.reject', $pinjaman) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-times-circle me-1"></i>Tolak Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_admin" class="form-control" rows="3" required
                            placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Force Close Modal --}}
<div class="modal fade" id="forceCloseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pinjaman.force-close', $pinjaman) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-ban me-1"></i>Force Close</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">Aksi ini akan menutup paksa pengajuan. Tidak dapat dibatalkan.</div>
                    <div class="mb-3">
                        <label class="form-label">Alasan <span class="text-danger">*</span></label>
                        <textarea name="catatan_admin" class="form-control" rows="3" required
                            placeholder="Alasan force close..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-ban me-1"></i>Force Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
