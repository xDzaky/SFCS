@extends('layouts.sfcs')

@section('title', 'Master Data Sekolah')

@section('content')
@php
    $result = session('master_import_result');
    $importErrors = session('master_import_errors', []);
    $warnings = session('master_import_warnings', []);
    $batchId = session('master_import_batch_id');
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Master Data Sekolah</h1>
        <p class="text-muted mb-0">Wizard import: upload, preview validasi, lalu terapkan perubahan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.master-data.template', ['scope' => request('scope', 'full')]) }}" class="btn btn-outline-secondary">
            <i class="fas fa-download me-1"></i> Download Template
        </a>
        <a href="{{ route('admin.master-data.validation') }}" class="btn btn-outline-info" target="_blank">
            <i class="fas fa-shield-alt me-1"></i> Cek Validasi JSON
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.master-data.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">File Master Data</label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".zip,.xlsx,.xls,.csv,.txt" required>
                    <small class="text-muted">ZIP/XLSX untuk full import; CSV tunggal boleh (scope akan terdeteksi otomatis).</small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Mode</label>
                    <select name="mode" class="form-select @error('mode') is-invalid @enderror">
                        <option value="replace_safe" {{ old('mode') === 'replace_safe' ? 'selected' : '' }}>replace_safe</option>
                        <option value="upsert_only" {{ old('mode') === 'upsert_only' ? 'selected' : '' }}>upsert_only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Scope</label>
                    <select name="scope" class="form-select @error('scope') is-invalid @enderror">
                        @foreach(($scopes ?? ['full']) as $scope)
                            <option value="{{ $scope }}" {{ old('scope', 'full') === $scope ? 'selected' : '' }}>{{ $scope }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Preview
                    </button>
                </div>
            </div>
        </form>

        @if($batchId && empty($importErrors))
            <hr>
            <form action="{{ route('admin.master-data.commit', $batchId) }}" method="POST" class="d-flex justify-content-end">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> Terapkan Perubahan
                </button>
            </form>
        @endif
    </div>
</div>

@if($result)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="mb-0">Hasil Preview / Import</h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-3">
                    <thead>
                    <tr>
                        <th>Entity</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Deactivated/Deleted</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>Kategori</td><td>{{ $result['kategori']['created'] ?? 0 }}</td><td>{{ $result['kategori']['updated'] ?? 0 }}</td><td>{{ $result['kategori']['deactivated'] ?? 0 }}</td></tr>
                    <tr><td>Gedung</td><td>{{ $result['gedung']['created'] ?? 0 }}</td><td>{{ $result['gedung']['updated'] ?? 0 }}</td><td>{{ $result['gedung']['deactivated'] ?? 0 }}</td></tr>
                    <tr><td>Ruangan</td><td>{{ $result['ruangan']['created'] ?? 0 }}</td><td>{{ $result['ruangan']['updated'] ?? 0 }}</td><td>{{ $result['ruangan']['deactivated'] ?? 0 }}</td></tr>
                    <tr><td>Jurusan</td><td>{{ $result['jurusan']['created'] ?? 0 }}</td><td>{{ $result['jurusan']['updated'] ?? 0 }}</td><td>{{ $result['jurusan']['deactivated'] ?? 0 }}</td></tr>
                    <tr><td>Mapping</td><td>{{ $result['mapping']['created'] ?? 0 }}</td><td>{{ $result['mapping']['updated'] ?? 0 }}</td><td>{{ $result['mapping']['deleted'] ?? 0 }}</td></tr>
                    </tbody>
                </table>
            </div>

            @if(!empty($warnings))
                <div class="alert alert-warning mb-3">
                    <strong>Warnings:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($warnings as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($importErrors))
                <div class="alert alert-danger mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Validasi gagal.</strong> Perbaiki data berdasarkan daftar error berikut.
                    </div>
                    @if($batchId)
                        <a href="{{ route('admin.master-data.error-report', $batchId) }}" class="btn btn-sm btn-outline-danger">Download Error Report</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                        <tr>
                            <th>Sheet</th>
                            <th>Row</th>
                            <th>Column</th>
                            <th>Code</th>
                            <th>Message</th>
                            <th>Raw Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($importErrors as $error)
                            <tr>
                                <td>{{ $error['sheet'] ?? '-' }}</td>
                                <td>{{ $error['row'] ?? '-' }}</td>
                                <td>{{ $error['column'] ?? '-' }}</td>
                                <td>{{ $error['error_code'] ?? '-' }}</td>
                                <td>{{ $error['error_message'] ?? '-' }}</td>
                                <td>{{ $error['raw_value'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h5 class="mb-0">Riwayat Import Terakhir</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                <tr>
                    <th class="ps-3">Waktu</th>
                    <th>File</th>
                    <th>Scope</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th>Operator</th>
                </tr>
                </thead>
                <tbody>
                @forelse(($batches ?? []) as $batch)
                    <tr>
                        <td class="ps-3">{{ $batch->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $batch->filename }}</td>
                        <td><span class="badge bg-light text-dark">{{ $batch->scope }}</span></td>
                        <td>{{ $batch->mode }}</td>
                        <td>
                            @php
                                $badge = match($batch->status) {
                                    'applied' => 'success',
                                    'validated' => 'info',
                                    'failed' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $batch->status }}</span>
                        </td>
                        <td>{{ $batch->actor->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-3 text-muted">Belum ada riwayat import.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
