@extends('layouts.sfcs')

@section('title', 'Detail Pinjaman')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('pinjaman.index') }}">Pinjam Barang</a></li>
    <li class="breadcrumb-item active">{{ $pinjaman->kode_pinjaman }}</li>
</ol>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>{{ $pinjaman->kode_pinjaman }}</span>
                <span class="badge bg-secondary">{{ ucfirst($pinjaman->status) }}</span>
            </div>
            <div class="card-body">
                <p><strong>Barang:</strong> {{ $pinjaman->barang->nama ?? '-' }}</p>
                <p><strong>Jumlah:</strong> {{ $pinjaman->qty }}</p>
                <p><strong>Tanggal Pinjam:</strong> {{ optional($pinjaman->tgl_pinjam)->format('d/m/Y H:i') }}</p>
                <p><strong>Jatuh Tempo:</strong> {{ optional($pinjaman->tgl_jatuh_tempo)->format('d/m/Y H:i') }}</p>
                <p><strong>Alasan:</strong><br>{{ $pinjaman->alasan }}</p>
                @if($pinjaman->catatan_admin)
                    <div class="alert alert-info mb-0"><strong>Catatan Admin:</strong> {{ $pinjaman->catatan_admin }}</div>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Riwayat</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($pinjaman->logs as $log)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $log->description ?? $log->action }}</span>
                            <small class="text-muted">{{ $log->created_at?->format('d/m H:i') }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Belum ada riwayat.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if($pinjaman->status === 'selesai')
            <div class="card">
                <div class="card-header">Feedback Pinjaman</div>
                <div class="card-body">
                    @if($pinjaman->feedback)
                        <p class="mb-1">Rating: {{ $pinjaman->feedback->rating }}/5</p>
                        <p class="text-muted mb-0">{{ $pinjaman->feedback->komentar ?? '-' }}</p>
                    @else
                        <form method="POST" action="{{ route('pinjaman.feedback', $pinjaman) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Rating</label>
                                <select class="form-select" name="rating" required>
                                    @for($i=5;$i>=1;$i--)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Komentar</label>
                                <textarea class="form-control" name="komentar" rows="3"></textarea>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Kirim Feedback</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
