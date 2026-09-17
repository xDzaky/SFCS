@extends('layouts.sfcs')

@section('title', 'Detail ' . ($pinjaman->tipe === 'minta' ? 'Permintaan' : 'Pinjaman'))

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('pinjaman.index') }}">Pinjam & Minta Barang</a></li>
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
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold">{{ $pinjaman->kode_pinjaman }}</span>
                    @if($isPermintaan)
                        <span class="badge bg-success">Permintaan Barang</span>
                    @else
                        <span class="badge bg-primary">Peminjaman Barang</span>
                    @endif
                </div>
                <span class="badge {{ $statusColor }}">{{ ucfirst($pinjaman->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
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
                </div>

                <hr>

                <div class="mb-3">
                    <div class="small text-muted mb-1">Alasan / Keperluan</div>
                    <div class="p-3 bg-light rounded">{{ $pinjaman->alasan }}</div>
                </div>

                @if($pinjaman->catatan_admin)
                    <div class="alert alert-info mb-0">
                        <strong><i class="fas fa-comment me-1"></i>Catatan Admin:</strong>
                        {{ $pinjaman->catatan_admin }}
                    </div>
                @endif

                {{-- Cancel button --}}
                @if($pinjaman->status === 'pending')
                    <form method="POST" action="{{ route('pinjaman.cancel', $pinjaman) }}" class="mt-3"
                        onsubmit="return confirm('Batalkan pengajuan ini?')">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-times me-1"></i>Batalkan Pengajuan
                        </button>
                    </form>
                @endif

                {{-- Permintaan info --}}
                @if($isPermintaan && $pinjaman->status === 'selesai')
                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-check-circle me-1"></i>
                        Permintaan barang telah disetujui dan barang sudah diserahkan.
                        Terima kasih!
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-3 shadow-sm border-0">
            <div class="card-header fw-semibold"><i class="fas fa-history me-2"></i>Riwayat</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($pinjaman->logs as $log)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <span>{{ $log->description ?? $log->action }}</span>
                            <small class="text-muted text-nowrap ms-3">{{ $log->created_at?->format('d/m H:i') }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Belum ada riwayat.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Status Timeline --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header fw-semibold"><i class="fas fa-stream me-2"></i>Alur Status</div>
            <div class="card-body py-3">
                @php
                    $steps = $isPermintaan
                        ? [['pending','Menunggu','Pengajuan dikirim'],['selesai','Disetujui & Selesai','Barang diserahkan'],['ditolak','Ditolak','']]
                        : [['pending','Menunggu','Pengajuan dikirim'],['disetujui','Disetujui','Siap diambil'],['dipinjam','Dipinjam','Barang diserahkan'],['selesai','Selesai','Barang dikembalikan']];
                    $current = $pinjaman->status;
                @endphp
                @foreach($steps as $step)
                    @if($step[0] === 'ditolak') @continue @endif
                    <div class="d-flex align-items-start gap-2 mb-2">
                        @if($current === $step[0])
                            <div class="text-primary mt-1"><i class="fas fa-circle-dot fs-6"></i></div>
                        @elseif(in_array($step[0], ['selesai']) && $current === 'selesai')
                            <div class="text-success mt-1"><i class="fas fa-check-circle fs-6"></i></div>
                        @else
                            <div class="text-muted mt-1"><i class="fas fa-circle fs-6" style="font-size:.6rem!important;margin-top:4px"></i></div>
                        @endif
                        <div>
                            <div class="small fw-semibold {{ $current === $step[0] ? 'text-primary' : '' }}">{{ $step[1] }}</div>
                            @if($step[2])<div class="small text-muted">{{ $step[2] }}</div>@endif
                        </div>
                    </div>
                @endforeach
                @if($current === 'ditolak')
                    <div class="d-flex align-items-start gap-2">
                        <div class="text-danger mt-1"><i class="fas fa-times-circle fs-6"></i></div>
                        <div>
                            <div class="small fw-semibold text-danger">Ditolak</div>
                            <div class="small text-muted">{{ $pinjaman->catatan_admin }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Feedback --}}
        @if($pinjaman->status === 'selesai')
            <div class="card shadow-sm border-0">
                <div class="card-header fw-semibold"><i class="fas fa-star me-2"></i>Feedback</div>
                <div class="card-body">
                    @if($pinjaman->feedback)
                        <div class="text-warning mb-1">
                            @for($i=1;$i<=5;$i++)
                                <i class="fas fa-star{{ $i > $pinjaman->feedback->rating ? '-empty' : '' }}"></i>
                            @endfor
                            <span class="text-dark ms-1">{{ $pinjaman->feedback->rating }}/5</span>
                        </div>
                        <p class="text-muted small mb-0">{{ $pinjaman->feedback->komentar ?? 'Tidak ada komentar.' }}</p>
                    @else
                        <form method="POST" action="{{ route('pinjaman.feedback', $pinjaman) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small">Rating</label>
                                <select class="form-select form-select-sm" name="rating" required>
                                    @for($i=5;$i>=1;$i--)
                                        <option value="{{ $i }}">{{ $i }} bintang</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Komentar</label>
                                <textarea class="form-control form-control-sm" name="komentar" rows="3"
                                    placeholder="Kesan Anda..."></textarea>
                            </div>
                            <button class="btn btn-primary btn-sm w-100" type="submit">Kirim Feedback</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
