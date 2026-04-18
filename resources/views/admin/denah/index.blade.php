@extends('layouts.sfcs')

@section('title', 'Kelola Denah')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Kelola Denah</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Kelola Denah Sekolah</h1>
        <p class="page-subtitle">Upload PDF/JPG/PNG, aktifkan denah, lalu mapping area ke gedung atau ruangan.</p>
    </div>
    <a href="{{ route('admin.peta-digital') }}" class="btn btn-outline-primary">
        <i class="fas fa-map-location-dot me-2"></i>Buka Peta Digital
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-2">Upload denah gagal.</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@unless($phpUploadReady)
    <div class="alert alert-warning" role="alert">
        <div class="fw-semibold mb-2">Server upload belum siap untuk denah PDF sekolah.</div>
        <div class="small mb-2">
            Batas PHP saat ini `upload_max_filesize={{ $phpUploadMax }}` dan `post_max_size={{ $phpPostMax }}`.
            Untuk denah sekolah, set keduanya minimal `{{ $recommendedUploadLimit }}`.
        </div>
        <div class="small mb-0">
            Upload bertahap untuk file besar sudah aktif di halaman ini, jadi denah sampai 20 MB tetap bisa diunggah.
            Meski begitu, server produksi tetap sebaiknya memakai limit minimal `{{ $recommendedUploadLimit }}` agar upload biasa juga aman.
        </div>
    </div>
@endunless

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card shadow-sm">
            <div class="card-header">Upload Denah Baru</div>
            <div class="card-body">
                <form id="school-map-upload-form" action="{{ route('admin.denah.store') }}" method="POST" enctype="multipart/form-data" data-chunk-url="{{ route('admin.denah.store-chunked') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Denah</label>
                        <input type="text" name="nama" id="map-nama-input" class="form-control @error('nama') is-invalid @enderror" required placeholder="Contoh: Denah PKL 2025/2026" value="{{ old('nama') }}">
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Denah</label>
                        <input type="file" name="file" id="map-file-input" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" required>
                        <div class="form-text">Bisa unggah PDF seperti `DENAH_SEKOLAH_PKL.pdf` atau file gambar denah.</div>
                        @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div id="map-upload-progress-wrapper" class="d-none mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="map-upload-progress-label">Menyiapkan upload...</span>
                            <span id="map-upload-progress-value">0%</span>
                        </div>
                        <div class="progress" role="progressbar" aria-label="Progress upload denah" aria-valuemin="0" aria-valuemax="100">
                            <div id="map-upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>
                    </div>
                    <div id="map-upload-runtime-hint" class="small text-muted mb-3">
                        File di atas 2 MB akan diupload bertahap otomatis agar tetap bisa dipakai di server sekolah yang limit PHP-nya kecil.
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="map-upload-submit-btn">Upload Denah</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card shadow-sm">
            <div class="card-header">Daftar Denah</div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($maps as $map)
                        <div class="col-12">
                            <div class="border rounded-4 p-3">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                            <div class="fw-semibold">{{ $map->nama }}</div>
                                            <span class="badge bg-light text-dark text-uppercase">{{ $map->file_type }}</span>
                                            @if($map->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">{{ $map->slug }}</div>
                                        <div class="small text-muted mt-1">{{ $map->layers_count }} layer tersedia untuk mapping gedung/lantai.</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('admin.denah.edit', $map) }}" class="btn btn-sm btn-outline-primary">Edit Layer & Area</a>
                                        @if(!$map->is_active)
                                            <form action="{{ route('admin.denah.activate', $map) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">Aktifkan</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.denah.destroy', $map) }}" method="POST" onsubmit="return confirm('Hapus denah ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </div>
                                </div>

                                <form action="{{ route('admin.denah.update', $map) }}" method="POST" class="row g-2 mt-3 align-items-end">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-8">
                                        <label class="form-label small text-muted mb-1">Nama Denah</label>
                                        <input type="text" name="nama" class="form-control form-control-sm" value="{{ $map->nama }}">
                                    </div>
                                    <div class="col-md-4 d-grid">
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Simpan Nama</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5 text-muted border rounded-4">Belum ada denah sekolah.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('school-map-upload-form');
            if (!form) {
                return;
            }

            const fileInput = document.getElementById('map-file-input');
            const namaInput = document.getElementById('map-nama-input');
            const submitButton = document.getElementById('map-upload-submit-btn');
            const progressWrapper = document.getElementById('map-upload-progress-wrapper');
            const progressBar = document.getElementById('map-upload-progress-bar');
            const progressLabel = document.getElementById('map-upload-progress-label');
            const progressValue = document.getElementById('map-upload-progress-value');
            const chunkUrl = form.dataset.chunkUrl;
            const csrfToken = form.querySelector('input[name="_token"]').value;
            const chunkSize = 1024 * 1024;

            form.addEventListener('submit', async (event) => {
                const file = fileInput.files?.[0];

                if (!file) {
                    return;
                }

                event.preventDefault();

                const nama = namaInput.value.trim();
                if (!nama) {
                    namaInput.focus();
                    return;
                }

                submitButton.disabled = true;
                progressWrapper.classList.remove('d-none');
                setProgress(0, 'Menyiapkan upload...');

                try {
                    const totalChunks = Math.ceil(file.size / chunkSize);
                    const uploadId = `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;

                    for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                        const start = chunkIndex * chunkSize;
                        const end = Math.min(start + chunkSize, file.size);
                        const chunk = file.slice(start, end);
                        const payload = new FormData();

                        payload.append('_token', csrfToken);
                        payload.append('nama', nama);
                        payload.append('upload_id', uploadId);
                        payload.append('original_name', file.name);
                        payload.append('chunk_index', String(chunkIndex));
                        payload.append('total_chunks', String(totalChunks));
                        payload.append('total_size', String(file.size));
                        payload.append('file_chunk', chunk, `${file.name}.part${chunkIndex}`);

                        setProgress(Math.round((chunkIndex / totalChunks) * 100), `Mengirim bagian ${chunkIndex + 1} dari ${totalChunks}...`);

                        const response = await fetch(chunkUrl, {
                            method: 'POST',
                            body: payload,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                        });

                        const result = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(extractErrorMessage(result));
                        }

                        const completedProgress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
                        setProgress(completedProgress, chunkIndex + 1 === totalChunks ? 'Menyusun file denah di server...' : `Bagian ${chunkIndex + 1}/${totalChunks} berhasil`);

                        if (result.completed) {
                            setProgress(100, 'Upload selesai. Mengalihkan...');
                            window.location.href = result.redirect_url || form.action;
                            return;
                        }
                    }

                    throw new Error('Upload selesai tetapi server tidak mengembalikan status akhir.');
                } catch (error) {
                    submitButton.disabled = false;
                    progressBar.classList.remove('progress-bar-animated');
                    progressBar.classList.add('bg-danger');
                    progressLabel.textContent = error.message || 'Upload denah gagal.';
                    progressValue.textContent = 'Gagal';
                }
            });

            function setProgress(percent, label) {
                progressBar.classList.remove('bg-danger');
                progressBar.classList.add('progress-bar-animated');
                progressBar.style.width = `${percent}%`;
                progressBar.setAttribute('aria-valuenow', String(percent));
                progressLabel.textContent = label;
                progressValue.textContent = `${percent}%`;
            }

            function extractErrorMessage(result) {
                if (result?.message) {
                    return result.message;
                }

                if (result?.errors) {
                    const firstKey = Object.keys(result.errors)[0];
                    if (firstKey && Array.isArray(result.errors[firstKey]) && result.errors[firstKey][0]) {
                        return result.errors[firstKey][0];
                    }
                }

                return 'Upload denah gagal.';
            }
        })();
    </script>
@endpush
@endsection
