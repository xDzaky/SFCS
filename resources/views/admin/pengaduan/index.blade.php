@extends('layouts.sfcs')

@section('title', 'Kelola Pengaduan')

@push('styles')
<style>
    /* Mobile cards for pengaduan list */
    .pengaduan-card-mobile {
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1rem;
        background: #fff;
        transition: box-shadow .15s ease, transform .1s ease;
    }
    .pengaduan-card-mobile:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
        transform: translateY(-1px);
    }
    .pengaduan-card-mobile .kode {
        font-size: .75rem;
        font-weight: 600;
        color: var(--primary-color);
    }
    .pengaduan-card-mobile .judul {
        font-weight: 600;
        font-size: .9375rem;
        color: #111827;
    }
    .pengaduan-card-mobile .meta {
        font-size: .8125rem;
        color: #6b7280;
    }
    .filter-toggle-btn {
        font-size: .8125rem;
    }
    /* Active filter indicator */
    .has-filter { border-color: var(--primary-color) !important; }

    /* ── Badge Prioritas & Status ─────────────────────────────────── */
    .badge-status { font-size: .72rem; font-weight: 600; letter-spacing: .02em; }
    /* Prioritas */
    .badge-rendah  { background-color: #16a34a !important; color: #fff !important; }
    .badge-sedang  { background-color: #d97706 !important; color: #fff !important; }
    .badge-tinggi  { background-color: #ea580c !important; color: #fff !important; }
    .badge-urgent  { background-color: #dc2626 !important; color: #fff !important; }
    /* Status */
    .badge-pending      { background-color: #6b7280 !important; color: #fff !important; }
    .badge-diverifikasi { background-color: #0284c7 !important; color: #fff !important; }
    .badge-diproses     { background-color: #d97706 !important; color: #fff !important; }
    .badge-selesai      { background-color: #16a34a !important; color: #fff !important; }
    .badge-ditolak      { background-color: #dc2626 !important; color: #fff !important; }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
        <h1 class="page-title">Kelola Pengaduan</h1>
        <p class="page-subtitle">Manajemen semua pengaduan fasilitas</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.pengaduan.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="fas fa-download me-1"></i><span class="d-none d-sm-inline">Export CSV</span>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4 {{ request()->hasAny(['status','prioritas','kategori_id','teknisi_id','duplicate_state','search']) ? 'has-filter' : '' }}">
    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
        <span class="fw-semibold small"><i class="fas fa-filter me-2 text-muted"></i>Filter Pengaduan</span>
        <button class="btn btn-sm btn-outline-secondary filter-toggle-btn d-md-none" 
                type="button" data-bs-toggle="collapse" data-bs-target="#filterBody"
                aria-expanded="{{ request()->hasAny(['status','prioritas','kategori_id','teknisi_id','duplicate_state','search']) ? 'true' : 'false' }}">
            <i class="fas fa-chevron-down me-1"></i>Tampilkan
        </button>
    </div>
    <div class="collapse{{ request()->hasAny(['status','prioritas','kategori_id','teknisi_id','duplicate_state','search']) ? ' show' : '' }} d-md-block" id="filterBody">
        <div class="card-body pt-2">
            <form action="{{ route('admin.pengaduan.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label small mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="diverifikasi" {{ request('status') == 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label small mb-1">Urgensi</label>
                        <select name="prioritas" class="form-select form-select-sm">
                            <option value="">Semua Urgensi</option>
                            <option value="rendah" {{ request('prioritas') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                            <option value="sedang" {{ request('prioritas') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="tinggi" {{ request('prioritas') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                            <option value="urgent" {{ request('prioritas') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label small mb-1">Kategori</label>
                        <select name="kategori_id" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label small mb-1">Teknisi</label>
                        <select name="teknisi_id" class="form-select form-select-sm">
                            <option value="">Semua Teknisi</option>
                            @foreach($teknisis as $teknisi)
                                <option value="{{ $teknisi->id }}" {{ request('teknisi_id') == $teknisi->id ? 'selected' : '' }}>
                                    {{ $teknisi->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label small mb-1">Duplikasi</label>
                        <select name="duplicate_state" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="potential" {{ request('duplicate_state') == 'potential' ? 'selected' : '' }}>Kemungkinan Duplikat</option>
                            <option value="marked" {{ request('duplicate_state') == 'marked' ? 'selected' : '' }}>Sudah Ditandai</option>
                            <option value="normal" {{ request('duplicate_state') == 'normal' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-8 col-md-4 col-lg-3">
                        <label class="form-label small mb-1">Cari</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Kode, judul, pelapor..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('admin.pengaduan.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pengaduans Table -->
<div class="card">
    <div class="card-body p-0">
        @if($pengaduans->count() > 0)

            {{-- ===== MOBILE CARD LIST (hidden on md+) ===== --}}
            <div class="d-md-none p-3 d-flex flex-column gap-3">
                @foreach($pengaduans as $pengaduan)
                    <a href="{{ route('admin.pengaduan.show', $pengaduan) }}"
                       class="pengaduan-card-mobile text-decoration-none {{ $pengaduan->prioritas === 'urgent' && !in_array($pengaduan->status, ['selesai','ditolak']) ? 'border-danger' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="kode">{{ $pengaduan->kode_pengaduan }}</span>
                            <div class="d-flex gap-1">
                                <span class="badge badge-status badge-{{ $pengaduan->prioritas }}">{{ ucfirst($pengaduan->prioritas) }}</span>
                                <span class="badge badge-status badge-{{ $pengaduan->status }}">{{ ucfirst($pengaduan->status) }}</span>
                            </div>
                        </div>
                        <div class="judul mb-1">{{ Str::limit($pengaduan->judul, 50) }}</div>
                        <div class="d-flex flex-wrap gap-3 meta">
                            <span><i class="fas fa-user me-1"></i>{{ $pengaduan->user->name ?? '-' }}</span>
                            <span><i class="fas fa-tag me-1"></i>{{ $pengaduan->kategori->nama ?? '-' }}</span>
                            @if($pengaduan->assignedTo)
                                <span><i class="fas fa-hard-hat me-1"></i>{{ $pengaduan->assignedTo->name }}</span>
                            @endif
                            <span><i class="fas fa-calendar me-1"></i>{{ $pengaduan->created_at->format('d/m/Y') }}</span>
                        </div>
                        @if($pengaduan->is_marked_duplicate && $pengaduan->duplicateOf)
                            <div class="mt-2">
                                <span class="badge badge-ditolak border">Duplikat dari {{ $pengaduan->duplicateOf->kode_pengaduan }}</span>
                                @if($pengaduan->is_auto_closed_duplicate)
                                    <span class="badge badge-diverifikasi border">Auto-closed</span>
                                @endif
                            </div>
                        @elseif($pengaduan->has_potential_duplicate)
                            <div class="mt-2">
                                <span class="badge badge-sedang border">Kemungkinan Duplikat</span>
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- ===== DESKTOP TABLE (hidden below md) ===== --}}
            <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Kode</th>
                            <th>Judul</th>
                            <th>Pelapor</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Urgensi</th>
                            <th>Status</th>
                            <th>Teknisi</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengaduans as $pengaduan)
                            <tr class="{{ $pengaduan->prioritas === 'urgent' && !in_array($pengaduan->status, ['selesai', 'ditolak']) ? 'table-danger' : '' }}" 
                                style="cursor: pointer;" 
                                onclick="if(!event.target.closest('input') && !event.target.closest('a') && !event.target.closest('button')) window.location='{{ route('admin.pengaduan.show', $pengaduan) }}'">
                                <td>
                                    <input type="checkbox" class="form-check-input pengaduan-check" value="{{ $pengaduan->id }}">
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary">{{ $pengaduan->kode_pengaduan }}</span>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ Str::limit($pengaduan->judul, 30) }}</div>
                                    @if($pengaduan->photos->count() > 0)
                                        <small class="text-muted"><i class="fas fa-image"></i> {{ $pengaduan->photos->count() }}</small>
                                    @endif
                                    @if($pengaduan->is_marked_duplicate && $pengaduan->duplicateOf)
                                        <div class="mt-1">
                                            <a href="{{ route('admin.pengaduan.show', $pengaduan) }}#duplicate-panel" class="badge badge-ditolak text-decoration-none">
                                                Duplikat dari {{ $pengaduan->duplicateOf->kode_pengaduan }}
                                            </a>
                                            @if($pengaduan->is_auto_closed_duplicate)
                                                <span class="badge badge-diverifikasi">Auto-closed (Duplikat)</span>
                                            @endif
                                        </div>
                                    @elseif($pengaduan->has_potential_duplicate)
                                        <div class="mt-1 d-flex gap-1 flex-wrap">
                                            <a href="{{ route('admin.pengaduan.show', $pengaduan) }}#duplicate-panel" class="badge badge-sedang text-decoration-none">
                                                Kemungkinan Duplikat
                                            </a>
                                            <a href="{{ route('admin.pengaduan.show', $pengaduan) }}#duplicate-panel" class="badge badge-pending text-decoration-none">
                                                Lihat kandidat
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $pengaduan->user->name ?? '-' }}</small>
                                </td>
                                <td><small>{{ $pengaduan->kategori->nama ?? '-' }}</small></td>
                                <td>
                                    <small>
                                        {{ $pengaduan->gedung->nama ?? $pengaduan->ruangan->gedung->nama ?? '' }}<br>
                                        {{ $pengaduan->lokasi_detail ?? '' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-{{ $pengaduan->prioritas }}">
                                        {{ ucfirst($pengaduan->prioritas) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-{{ $pengaduan->status }}">
                                        {{ ucfirst($pengaduan->status) }}
                                    </span>
                                </td>
                                <td><small>{{ $pengaduan->assignedTo->name ?? '-' }}</small></td>
                                <td>
                                    <small class="text-muted">{{ $pengaduan->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.pengaduan.show', $pengaduan) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>{{-- /d-none d-md-block --}}

            <!-- Bulk Actions -->
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <select id="bulkAction" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Aksi Massal...</option>
                        <option value="status">Ubah Status</option>
                        <option value="assign">Tugaskan Teknisi</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="executeBulkAction()">
                        Terapkan
                    </button>
                </div>
                {{ $pengaduans->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada pengaduan yang ditemukan</p>
            </div>
        @endif
    </div>
</div>

<!-- Bulk Status Modal -->
<div class="modal fade" id="bulkStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pengaduan.bulk-status') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Status Massal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ids" id="bulkStatusIds">
                    <div class="alert alert-warning small">
                        <strong>Konfirmasi:</strong> aksi ini akan mengubah <span id="bulkStatusCount">0</span> tiket.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Baru</label>
                        <select name="status" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="diverifikasi">Diverifikasi</option>
                            <option value="diproses">Diproses</option>
                            <option value="selesai">Selesai</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Ketik <code>TERAPKAN</code> untuk konfirmasi</label>
                        <input type="text" name="confirmation_text" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pengaduan.bulk-assign') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tugaskan Teknisi Massal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ids" id="bulkAssignIds">
                    <div class="alert alert-warning small">
                        <strong>Konfirmasi:</strong> aksi ini akan menugaskan <span id="bulkAssignCount">0</span> tiket.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teknisi</label>
                        <select name="teknisi_id" class="form-select" required>
                            <option value="">Pilih Teknisi</option>
                            @foreach($teknisis as $teknisi)
                                <option value="{{ $teknisi->id }}">{{ $teknisi->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Ketik <code>TERAPKAN</code> untuk konfirmasi</label>
                        <input type="text" name="confirmation_text" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.pengaduan-check').forEach(cb => cb.checked = this.checked);
    });

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.pengaduan-check:checked')).map(cb => cb.value);
    }

    function executeBulkAction() {
        const action = document.getElementById('bulkAction').value;
        const ids = getSelectedIds();

        if (ids.length === 0) {
            alert('Pilih minimal satu pengaduan');
            return;
        }

        if (action === 'status') {
            document.getElementById('bulkStatusIds').value = JSON.stringify(ids);
            document.getElementById('bulkStatusCount').textContent = ids.length;
            new bootstrap.Modal(document.getElementById('bulkStatusModal')).show();
        } else if (action === 'assign') {
            document.getElementById('bulkAssignIds').value = JSON.stringify(ids);
            document.getElementById('bulkAssignCount').textContent = ids.length;
            new bootstrap.Modal(document.getElementById('bulkAssignModal')).show();
        }
    }
</script>
@endpush
