@extends('layouts.sfcs')

@section('title', 'Kelola Pengaduan')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Kelola Pengaduan</h1>
        <p class="page-subtitle">Manajemen semua pengaduan fasilitas</p>
    </div>
    <a href="{{ route('admin.pengaduan.export', request()->query()) }}" class="btn btn-outline-success">
        <i class="fas fa-download me-2"></i>Export CSV
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.pengaduan.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="diverifikasi" {{ request('status') == 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Urgensi</label>
                    <select name="urgensi" class="form-select form-select-sm">
                        <option value="">Semua Urgensi</option>
                        <option value="rendah" {{ request('urgensi') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                        <option value="sedang" {{ request('urgensi') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                        <option value="tinggi" {{ request('urgensi') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Kategori</label>
                    <select name="kategori_id" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Teknisi</label>
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">Semua Teknisi</option>
                        @foreach($teknisis as $teknisi)
                            <option value="{{ $teknisi->id }}" {{ request('assigned_to') == $teknisi->id ? 'selected' : '' }}>
                                {{ $teknisi->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Kode, judul, pelapor..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.pengaduan.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Pengaduans Table -->
<div class="card">
    <div class="card-body p-0">
        @if($pengaduans->count() > 0)
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
                                </td>
                                <td>
                                    <small>{{ $pengaduan->user->name ?? '-' }}</small>
                                </td>
                                <td><small>{{ $pengaduan->kategori->nama ?? '-' }}</small></td>
                                <td>
                                    <small>
                                        {{ $pengaduan->ruangan->gedung->nama ?? '' }}<br>
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
                    <div class="mb-3">
                        <label class="form-label">Teknisi</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Pilih Teknisi</option>
                            @foreach($teknisis as $teknisi)
                                <option value="{{ $teknisi->id }}">{{ $teknisi->name }}</option>
                            @endforeach
                        </select>
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
            new bootstrap.Modal(document.getElementById('bulkStatusModal')).show();
        } else if (action === 'assign') {
            document.getElementById('bulkAssignIds').value = JSON.stringify(ids);
            new bootstrap.Modal(document.getElementById('bulkAssignModal')).show();
        }
    }
</script>
@endpush
