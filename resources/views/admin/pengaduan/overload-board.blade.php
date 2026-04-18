@extends('layouts.sfcs')

@section('title', 'Overload Board')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Overload Board</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Overload Board</h1>
        <p class="page-subtitle">Pantau antrean tiket urgent/tinggi dan estimasi delay teknisi.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card"><div class="card-body text-center"><small class="text-muted d-block">Status</small><strong class="{{ $summary['is_overload'] ? 'text-danger' : 'text-success' }}">{{ $summary['is_overload'] ? 'OVERLOAD' : 'NORMAL' }}</strong></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body text-center"><small class="text-muted d-block">Urgent/Tinggi 1 Jam</small><strong>{{ $summary['urgent_high_last_hour'] }}</strong></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body text-center"><small class="text-muted d-block">Kapasitas 1 Jam</small><strong>{{ $summary['capacity_last_hour'] }}</strong></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body text-center"><small class="text-muted d-block">Prediksi Delay</small><strong>{{ $summary['predicted_delay_minutes'] }} menit</strong></div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Antrian Tiket Aktif</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Judul</th>
                        <th>Prioritas</th>
                        <th>Triage</th>
                        <th>Rank</th>
                        <th>Delay</th>
                        <th>Teknisi</th>
                        <th>Jadwal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td><span class="badge bg-light text-dark">{{ $ticket->kode_pengaduan }}</span></td>
                            <td>{{ Str::limit($ticket->judul, 40) }}</td>
                            <td>{{ strtoupper($ticket->prioritas) }}</td>
                            <td>{{ $ticket->triage_score }}</td>
                            <td>{{ $ticket->queue_rank ?? '-' }}</td>
                            <td class="{{ $ticket->is_overload_delayed ? 'text-danger fw-semibold' : '' }}">{{ $ticket->delay_minutes ?? 0 }} m</td>
                            <td>{{ $ticket->assignedTo->name ?? '-' }}</td>
                            <td>{{ optional($ticket->planned_start_at)->format('d/m H:i') ?? '-' }}</td>
                            <td><a href="{{ route('admin.pengaduan.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada tiket aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

