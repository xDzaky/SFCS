@extends('layouts.sfcs')

@section('title', 'Buat Pengaduan')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('pengaduan.index') }}">Pengaduan</a></li>
    <li class="breadcrumb-item active">Buat Baru</li>
</ol>
@endsection

@section('content')
<!-- Mobile-First Header -->
<div class="page-header text-center text-md-start">
    <h1 class="page-title fs-4 fs-md-3">📝 Buat Pengaduan</h1>
    <p class="page-subtitle small">Laporkan masalah fasilitas sekolah dengan mudah</p>
</div>

<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-3 p-md-4">
                @if($errors->any())
                    <div class="alert alert-danger mb-4" role="alert">
                        <div class="fw-semibold mb-2">Laporan belum terkirim. Cek bagian yang masih salah:</div>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data" id="pengaduanForm">
                    @csrf

                    <!-- Step Indicator (Mobile Friendly) -->
                    <div class="mb-4 d-md-none">
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" id="formProgress" style="width: 10%"></div>
                        </div>
                        <small class="text-muted d-block mt-2 text-center">Langkah <span id="currentStep">1</span> dari 5</small>
                    </div>

                    <!-- 1. Judul Pengaduan -->
                    <div class="mb-4 form-step">
                        <label for="judul" class="form-label fw-bold fs-6">
                            <span class="badge bg-primary rounded-pill me-2">1</span>
                            Apa fasilitas/barang yang rusak? <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg @error('judul') is-invalid @enderror" 
                               id="judul" name="judul" value="{{ old('judul') }}" 
                               placeholder="Contoh: Wastafel rusak di toilet lantai 1"
                               maxlength="100"
                               required>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Tulis singkat & jelas (maks. 100 huruf)</div>
                    </div>

                    <!-- 2. Deskripsi -->
                    <div class="mb-4 form-step">
                        <label for="deskripsi" class="form-label fw-bold fs-6">
                            <span class="badge bg-primary rounded-pill me-2">2</span>
                            Jelaskan lebih detail <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control form-control-lg @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" name="deskripsi" rows="4" 
                                  placeholder="Ceritakan detailnya... misalnya: Keran air bocor terus, air tidak bisa ditutup"
                                  minlength="20"
                                  required>{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Min. 20 huruf agar teknisi bisa paham masalahnya</div>
                    </div>

                    <!-- 3. Lokasi -->
                    <div class="mb-4 form-step">
                        <label class="form-label fw-bold fs-6 mb-3">
                            <span class="badge bg-primary rounded-pill me-2">3</span>
                            Dimana lokasinya? <span class="text-danger">*</span>
                        </label>
                        <div class="alert alert-light border small py-2 mb-3">
                            Isi 3 hal saja: pilih jenis masalah, pilih gedung, lalu pilih lantai.
                        </div>
                        
                        <div class="row g-3">
                            <!-- Kategori -->
                            <div class="col-12">
                                <label for="kategori_id" class="form-label small text-muted">1) Jenis Fasilitas/Barang Bermasalah</label>
                                @php
                                    $kategoriAlias = [
                                        'Kelistrikan' => 'Listrik (Lampu, Stop Kontak, Saklar)',
                                        'Plumbing' => 'Air & Sanitasi (Keran, Wastafel, Toilet)',
                                        'Furniture' => 'Perabot Kelas (Meja, Kursi, Papan Tulis)',
                                        'AC & Pendingin' => 'Pendingin Ruangan (AC, Kipas)',
                                        'Bangunan' => 'Bangunan (Atap, Dinding, Lantai, Pintu)',
                                        'IT & Multimedia' => 'IT & Multimedia (LCD, Komputer, WiFi)',
                                        'Kebersihan' => 'Kebersihan Lingkungan',
                                        'Keamanan' => 'Keamanan Sekolah',
                                        'Lainnya' => 'Lainnya',
                                    ];
                                @endphp
                                <select class="form-select form-select-lg @error('kategori_id') is-invalid @enderror" 
                                        id="kategori_id" name="kategori_id" required>
                                    <option value="">-- Pilih jenis fasilitas/barang --</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" 
                                                data-sub-kategoris='@json($kategori->subKategoris)'
                                                {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategoriAlias[$kategori->nama] ?? $kategori->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sub Kategori (Hidden initially) -->
                            <div class="col-12" id="subKategoriWrapper" style="display: none;">
                                <label for="sub_kategori_id" class="form-label small text-muted">Detail Fasilitas/Barang <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('sub_kategori_id') is-invalid @enderror" 
                                        id="sub_kategori_id" name="sub_kategori_id" required>
                                    <option value="">-- Pilih detail fasilitas/barang --</option>
                                </select>
                                @error('sub_kategori_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Gedung -->
                            <div class="col-12 col-md-6">
                                <label for="gedung_id" class="form-label small text-muted">2) Lokasi Gedung</label>
                                <select class="form-select form-select-lg @error('gedung_id') is-invalid @enderror" 
                                        id="gedung_id" name="gedung_id" required>
                                    <option value="">-- Pilih gedung lokasi kerusakan --</option>
                                    @foreach($gedungs as $gedung)
                                        <option value="{{ $gedung->id }}" 
                                                data-lantai="{{ $gedung->jumlah_lantai }}"
                                                {{ old('gedung_id') == $gedung->id ? 'selected' : '' }}>
                                            {{ $gedung->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('gedung_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lantai -->
                            <div class="col-12 col-md-6">
                                <label for="lantai" class="form-label small text-muted">3) Lantai</label>
                                <select class="form-select form-select-lg @error('lantai') is-invalid @enderror" 
                                        id="lantai" name="lantai" required>
                                    <option value="">-- Pilih lantai --</option>
                                </select>
                                @error('lantai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lokasi Detail -->
                            <div class="col-12">
                                <label for="lokasi_detail" class="form-label small text-muted">Patokan Lokasi (Opsional)</label>
                                <input type="text" class="form-control form-control-lg @error('lokasi_detail') is-invalid @enderror" 
                                       id="lokasi_detail" name="lokasi_detail" value="{{ old('lokasi_detail') }}" 
                                       placeholder="Contoh: dekat tangga / depan kantin">
                                <div class="form-text">Isi jika ingin teknisi lebih cepat menemukan titik kerusakan</div>
                            </div>

                            <div class="col-12">
                                <div id="duplicateAlert" class="alert alert-warning d-none mb-0" role="alert"></div>
                                @error('duplicate_pengaduan')
                                    <div class="alert alert-warning mb-0">{{ $message }}</div>
                                @enderror
                            </div>

                            @include('partials.pengaduan-map-picker', [
                                'pickerId' => 'create-pengaduan-map-picker',
                                'formId' => 'pengaduanForm',
                            ])
                        </div>
                    </div>

                    <!-- 4. Tingkat Urgensi (Mobile Optimized) -->
                    <div class="mb-4 form-step">
                        <label class="form-label fw-bold fs-6 mb-3">
                            <span class="badge bg-primary rounded-pill me-2">4</span>
                            Seberapa mendesak? <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_rendah" value="rendah" 
                                       {{ old('prioritas') == 'rendah' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success w-100 py-3 px-2" for="prioritas_rendah">
                                    <div class="fs-3 mb-1">🟢</div>
                                    <div class="fw-bold small">Rendah</div>
                                    <div class="x-small">7 hari</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_sedang" value="sedang"
                                       {{ old('prioritas', 'sedang') == 'sedang' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning w-100 py-3 px-2" for="prioritas_sedang">
                                    <div class="fs-3 mb-1">🟡</div>
                                    <div class="fw-bold small">Sedang</div>
                                    <div class="x-small">3 hari</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_tinggi" value="tinggi"
                                       {{ old('prioritas') == 'tinggi' ? 'checked' : '' }}>
                                <label class="btn btn-outline-orange w-100 py-3 px-2" for="prioritas_tinggi">
                                    <div class="fs-3 mb-1">🟠</div>
                                    <div class="fw-bold small">Tinggi</div>
                                    <div class="x-small">1 hari</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_urgent" value="urgent"
                                       {{ old('prioritas') == 'urgent' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger w-100 py-3 px-2" for="prioritas_urgent">
                                    <div class="fs-3 mb-1">🔴</div>
                                    <div class="fw-bold small">Darurat</div>
                                    <div class="x-small">6 jam</div>
                                </label>
                            </div>
                        </div>
                        @error('prioritas')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        
                        <!-- Info Urgensi -->
                        <div class="alert alert-info mt-3 py-2 px-3 small" role="alert">
                            <strong>💡 Tips:</strong><br>
                            🟢 Rendah: Tidak mengganggu<br>
                            🟡 Sedang: Agak mengganggu<br>
                            🟠 Tinggi: Sangat mengganggu<br>
                            🔴 Darurat: Berbahaya!
                        </div>
                    </div>

                    <div class="mb-4 form-step">
                        <label class="form-label fw-bold fs-6 mb-2">
                            <span class="badge bg-primary rounded-pill me-2">4A</span>
                            Dampak Kerusakan (Wajib)
                        </label>
                        <div class="form-text mb-2">Dipakai sistem untuk validasi urgensi agar tiket adil.</div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="impact_safety_risk" name="impact_safety_risk" {{ old('impact_safety_risk') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="impact_safety_risk">Ada risiko keselamatan</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="impact_learning_blocked" name="impact_learning_blocked" {{ old('impact_learning_blocked') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="impact_learning_blocked">Kegiatan belajar terhambat</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="impact_exam_related" name="impact_exam_related" {{ old('impact_exam_related') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="impact_exam_related">Terkait ujian/praktik penting</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Skala Dampak Area</label>
                                <select name="impact_area_scope" class="form-select form-select-sm" required>
                                    <option value="1_kelas" {{ old('impact_area_scope', '1_kelas') === '1_kelas' ? 'selected' : '' }}>1 Kelas</option>
                                    <option value="1_lantai" {{ old('impact_area_scope') === '1_lantai' ? 'selected' : '' }}>1 Lantai</option>
                                    <option value="1_gedung" {{ old('impact_area_scope') === '1_gedung' ? 'selected' : '' }}>1 Gedung</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Utilitas Terdampak</label>
                                <select name="impact_utilities" class="form-select form-select-sm" required>
                                    <option value="none" {{ old('impact_utilities', 'none') === 'none' ? 'selected' : '' }}>Tidak ada utilitas kritikal</option>
                                    <option value="listrik" {{ old('impact_utilities') === 'listrik' ? 'selected' : '' }}>Listrik</option>
                                    <option value="air" {{ old('impact_utilities') === 'air' ? 'selected' : '' }}>Air</option>
                                    <option value="internet" {{ old('impact_utilities') === 'internet' ? 'selected' : '' }}>Internet</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Foto Bukti (Camera First) -->
                    <div class="mb-4 form-step">
                        <label class="form-label fw-bold fs-6 mb-3">
                            <span class="badge bg-primary rounded-pill me-2">5</span>
                            Foto bukti kerusakan <span class="text-danger">*</span>
                        </label>
                        
                        <!-- Camera Buttons (Mobile First) -->
                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-primary btn-lg" id="openCameraBtn">
                                <i class="fas fa-camera me-2"></i>Buka Kamera
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="uploadFileBtn">
                                <i class="fas fa-folder-open me-2"></i>Pilih dari Galeri
                            </button>
                        </div>

                        <!-- Hidden File Input -->
                        <input type="file" class="d-none" 
                               id="photos" name="photos[]" multiple accept="image/*">
                        
                        <!-- Camera Modal/Canvas -->
                        <div id="cameraContainer" class="card bg-dark text-white mb-3" style="display: none;">
                            <div class="card-body p-2">
                                <div class="position-relative">
                                    <video id="cameraStream" autoplay playsinline class="w-100 rounded"></video>
                                    <div class="position-absolute bottom-0 start-0 end-0 p-3 text-center">
                                        <button type="button" class="btn btn-light btn-lg rounded-circle" id="captureBtn" style="width: 70px; height: 70px;">
                                            <i class="fas fa-camera fs-4"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger ms-2" id="closeCameraBtn">
                                            <i class="fas fa-times me-1"></i>Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Container -->
                        <div id="photoPreview" class="d-flex flex-wrap gap-2"></div>
                        
                        @error('photos')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        @error('photos.*')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        <div id="photoValidationError" class="text-danger small mt-2 d-none"></div>
                        
                        <div class="form-text mt-2">
                            📸 Min. 1 foto, maks. 5 foto (masing-masing maks. 5MB). Format: JPG, PNG, atau WEBP.
                        </div>
                    </div>

                    <!-- Hidden Tanggal Kejadian (Auto set to today) -->
                    <input type="hidden" name="tanggal_kejadian" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="force_submit_duplicate" id="force_submit_duplicate" value="0">

                    <div id="duplicateOverrideWrapper" class="form-check mb-3 d-none">
                        <input class="form-check-input" type="checkbox" value="1" id="duplicateOverride">
                        <label class="form-check-label small" for="duplicateOverride">
                            Tetap kirim laporan ini (lokasi/titik kerusakan berbeda).
                        </label>
                    </div>

                    <!-- Submit Buttons (Sticky on Mobile) -->
                    <div class="sticky-bottom bg-white pt-3 pb-2 pb-md-0">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Pengaduan
                            </button>
                            <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Card (Desktop Only) -->
        <div class="card mt-3 d-none d-lg-block">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle me-2"></i>Panduan Singkat
            </div>
            <div class="card-body">
                <ul class="small mb-0 ps-3">
                    <li class="mb-2">Tulis judul yang jelas</li>
                    <li class="mb-2">Jelaskan masalah detail</li>
                    <li class="mb-2">Pilih lokasi yang tepat</li>
                    <li class="mb-2">Foto kerusakan dengan jelas</li>
                    <li>Pilih tingkat urgensi sesuai kondisi</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Mobile-First Responsive Styles */
    .x-small {
        font-size: 0.7rem;
    }
    
    .btn-outline-orange {
        border-color: #fd7e14;
        color: #fd7e14;
    }
    .btn-outline-orange:hover,
    .btn-check:checked + .btn-outline-orange {
        background-color: #fd7e14;
        border-color: #fd7e14;
        color: white;
    }
    
    /* Sticky button untuk mobile */
    .sticky-bottom {
        position: static;
        box-shadow: none;
    }
    
    /* Camera container */
    #cameraStream {
        max-height: 50vh;
        object-fit: cover;
    }
    
    /* Photo preview thumbs */
    .photo-thumb {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }

    /* Progress bar untuk form steps */
    .form-step {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Large touch targets untuk mobile */
    @media (max-width: 768px) {
        .form-control, .form-select {
            min-height: 48px;
            font-size: 16px; /* Prevents iOS zoom on focus */
        }
        
        .btn {
            min-height: 48px;
        }

        .sticky-bottom {
            position: sticky;
            bottom: 0;
            z-index: 10;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // ========== Dynamic Form Dependencies ==========
    const duplicateAlert = document.getElementById('duplicateAlert');
    const duplicateOverrideWrapper = document.getElementById('duplicateOverrideWrapper');
    const duplicateOverride = document.getElementById('duplicateOverride');
    const forceSubmitDuplicate = document.getElementById('force_submit_duplicate');
    const photoValidationError = document.getElementById('photoValidationError');
    let activeDuplicate = null;
    let duplicateCheckTimer = null;
    
    // Load sub kategoris when kategori changes
    document.getElementById('kategori_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const subKategoriSelect = document.getElementById('sub_kategori_id');
        const subKategoriWrapper = document.getElementById('subKategoriWrapper');
        
        subKategoriSelect.innerHTML = '<option value="">-- Pilih detail fasilitas/barang --</option>';
        
        if (this.value && selected.dataset.subKategoris) {
            try {
                const subKategoris = JSON.parse(selected.dataset.subKategoris);
                if (subKategoris.length > 0) {
                    subKategoris.forEach(item => {
                        if (item.is_active) {
                            subKategoriSelect.innerHTML += `<option value="${item.id}">${item.nama}</option>`;
                        }
                    });
                    subKategoriWrapper.style.display = 'block';
                } else {
                    subKategoriSelect.innerHTML = '<option value="">-- Tidak ada detail untuk kategori ini --</option>';
                    subKategoriWrapper.style.display = 'none';
                }
            } catch(e) {
                console.error('Error parsing sub kategoris:', e);
                subKategoriWrapper.style.display = 'none';
            }
        } else {
            subKategoriWrapper.style.display = 'none';
        }

        updateProgress();
        scheduleDuplicateCheck();
    });

    // Load lantai when gedung changes
    document.getElementById('gedung_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const lantaiSelect = document.getElementById('lantai');
        
        // Reset
        lantaiSelect.innerHTML = '<option value="">-- Pilih lantai --</option>';
        
        if (this.value) {
            // Load lantai
            const jumlahLantai = parseInt(selected.dataset.lantai) || 1;
            for (let i = 1; i <= jumlahLantai; i++) {
                lantaiSelect.innerHTML += `<option value="${i}">Lantai ${i}</option>`;
            }

            if (jumlahLantai === 1) {
                lantaiSelect.value = '1';
            }
        }
        
        updateProgress();
        scheduleDuplicateCheck();
    });

    // Trigger progress and duplicate check when lantai changes
    document.getElementById('lantai').addEventListener('change', function() {
        updateProgress();
        scheduleDuplicateCheck();
    });

    document.getElementById('sub_kategori_id').addEventListener('change', scheduleDuplicateCheck);
    document.getElementById('lokasi_detail').addEventListener('input', scheduleDuplicateCheck);

    duplicateOverride.addEventListener('change', function() {
        forceSubmitDuplicate.value = this.checked ? '1' : '0';
    });

    function canCheckDuplicate() {
        return Boolean(
            document.getElementById('kategori_id').value &&
            document.getElementById('sub_kategori_id').value &&
            document.getElementById('gedung_id').value &&
            document.getElementById('lantai').value
        );
    }

    function clearDuplicateWarning() {
        activeDuplicate = null;
        duplicateAlert.classList.add('d-none');
        duplicateAlert.innerHTML = '';
        duplicateOverrideWrapper.classList.add('d-none');
        duplicateOverride.checked = false;
        forceSubmitDuplicate.value = '0';
    }

    function scheduleDuplicateCheck() {
        clearTimeout(duplicateCheckTimer);
        duplicateCheckTimer = setTimeout(checkDuplicatePengaduan, 300);
    }

    async function checkDuplicatePengaduan() {
        if (!canCheckDuplicate()) {
            clearDuplicateWarning();
            return;
        }

        const params = new URLSearchParams({
            kategori_id: document.getElementById('kategori_id').value,
            gedung_id: document.getElementById('gedung_id').value,
            lantai: document.getElementById('lantai').value,
            lokasi_detail: document.getElementById('lokasi_detail').value || '',
        });

        const subKategoriValue = document.getElementById('sub_kategori_id').value;
        if (subKategoriValue) {
            params.set('sub_kategori_id', subKategoriValue);
        }

        try {
            const response = await fetch(`{{ route('api.pengaduan.check-duplicate') }}?${params.toString()}`);
            if (!response.ok) {
                clearDuplicateWarning();
                return;
            }

            const data = await response.json();
            if (!data.has_duplicate) {
                clearDuplicateWarning();
                return;
            }

            activeDuplicate = data.pengaduan;
            duplicateAlert.innerHTML = `
                <strong>Lokasi ini kemungkinan sudah dilaporkan.</strong><br>
                Tiket: <a href="${activeDuplicate.url}" class="alert-link">${activeDuplicate.kode_pengaduan}</a>
                (${activeDuplicate.status_display}) - ${activeDuplicate.judul}
            `;
            duplicateAlert.classList.remove('d-none');
            duplicateOverrideWrapper.classList.remove('d-none');
            forceSubmitDuplicate.value = duplicateOverride.checked ? '1' : '0';
        } catch (error) {
            console.error('Duplicate check error:', error);
            clearDuplicateWarning();
        }
    }

    // ========== Camera Capture Feature ==========
    
    let cameraStream = null;
    let capturedPhotos = [];
    const maxPhotos = 5;
    
    // Open Camera Button
    document.getElementById('openCameraBtn').addEventListener('click', async function() {
        if (capturedPhotos.length >= maxPhotos) {
            alert('Maksimal 5 foto sudah tercapai!');
            return;
        }
        
        try {
            const container = document.getElementById('cameraContainer');
            const video = document.getElementById('cameraStream');
            
            // Request camera access (rear camera preferred on mobile)
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { 
                    facingMode: 'environment', // Use rear camera on mobile
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                }
            });
            
            video.srcObject = cameraStream;
            container.style.display = 'block';
            
            // Scroll to camera
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (error) {
            console.error('Camera error:', error);
            alert('Tidak bisa mengakses kamera. Pastikan izin kamera sudah diberikan. Gunakan tombol "Pilih dari Galeri" sebagai alternatif.');
        }
    });
    
    // Capture Photo from Camera
    document.getElementById('captureBtn').addEventListener('click', function() {
        const video = document.getElementById('cameraStream');
        const canvas = document.createElement('canvas');
        
        // Set canvas size to video size
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        // Convert to blob
        canvas.toBlob(function(blob) {
            if (capturedPhotos.length >= maxPhotos) {
                alert('Maksimal 5 foto!');
                return;
            }
            
            // Create file from blob
            const fileName = `camera-${Date.now()}.jpg`;
            const file = new File([blob], fileName, { type: 'image/jpeg' });
            
            capturedPhotos.push(file);
            updatePhotoPreview();
            updateFileInput();
            clearPhotoValidationError();
            
            // Show feedback
            const btn = document.getElementById('captureBtn');
            btn.innerHTML = '<i class="fas fa-check fs-4"></i>';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-light');
            
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-camera fs-4"></i>';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-light');
            }, 500);
            
            // Auto close camera if max photos reached
            if (capturedPhotos.length >= maxPhotos) {
                setTimeout(() => {
                    closeCamera();
                    alert('5 foto sudah tercapai!');
                }, 1000);
            }
            
            updateProgress();
        }, 'image/jpeg', 0.9);
    });
    
    // Close Camera
    document.getElementById('closeCameraBtn').addEventListener('click', closeCamera);
    
    function closeCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        document.getElementById('cameraContainer').style.display = 'none';
        document.getElementById('cameraStream').srcObject = null;
    }
    
    // Upload from Gallery Button
    document.getElementById('uploadFileBtn').addEventListener('click', function() {
        document.getElementById('photos').click();
    });
    
    // Handle file input (for gallery selection)
    document.getElementById('photos').addEventListener('change', function() {
        const files = Array.from(this.files);
        
        if (capturedPhotos.length + files.length > maxPhotos) {
            alert(`Maksimal ${maxPhotos} foto! Anda sudah punya ${capturedPhotos.length} foto.`);
            this.value = '';
            return;
        }
        
        // Validate file sizes
        for (let file of files) {
            if (file.size > 5 * 1024 * 1024) {
                alert(`File ${file.name} terlalu besar! Maksimal 5MB per foto.`);
                this.value = '';
                return;
            }
        }
        
        capturedPhotos = capturedPhotos.concat(files);
        updatePhotoPreview();
        updateFileInput();
        clearPhotoValidationError();
        updateProgress();
    });
    
    // Update photo preview thumbnails
    function updatePhotoPreview() {
        const preview = document.getElementById('photoPreview');
        preview.innerHTML = '';
        
        capturedPhotos.forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'position-relative';
            
            // Create thumbnail
            const reader = new FileReader();
            reader.onload = function(e) {
                div.innerHTML = `
                    <img src="${e.target.result}" class="photo-thumb border">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-0" 
                            style="width: 24px; height: 24px; line-height: 1;" 
                            onclick="removePhoto(${index})" title="Hapus">
                        <i class="fas fa-times small"></i>
                    </button>
                `;
            };
            reader.readAsDataURL(file);
            
            preview.appendChild(div);
        });
        
        // Update counter
        const photoCount = document.createElement('div');
        photoCount.className = 'w-100 small text-muted mt-2';
        photoCount.textContent = `${capturedPhotos.length} dari ${maxPhotos} foto`;
        preview.appendChild(photoCount);
    }
    
    // Remove photo
    window.removePhoto = function(index) {
        if (confirm('Hapus foto ini?')) {
            capturedPhotos.splice(index, 1);
            updatePhotoPreview();
            updateFileInput();
            if (capturedPhotos.length > 0) {
                clearPhotoValidationError();
            }
            updateProgress();
        }
    };
    
    // Update hidden file input with captured photos
    function updateFileInput() {
        const dataTransfer = new DataTransfer();
        capturedPhotos.forEach(file => {
            dataTransfer.items.add(file);
        });
        document.getElementById('photos').files = dataTransfer.files;
    }

    function showPhotoValidationError(message) {
        photoValidationError.textContent = message;
        photoValidationError.classList.remove('d-none');
    }

    function clearPhotoValidationError() {
        photoValidationError.textContent = '';
        photoValidationError.classList.add('d-none');
    }
    
    // ========== Form Progress Tracker (Mobile) ==========
    
    function updateProgress() {
        const judul = document.getElementById('judul').value;
        const deskripsi = document.getElementById('deskripsi').value;
        const kategori = document.getElementById('kategori_id').value;
        const gedung = document.getElementById('gedung_id').value;
        const lantai = document.getElementById('lantai').value;
        const prioritas = document.querySelector('input[name="prioritas"]:checked');
        const photos = capturedPhotos.length;
        
        let completed = 0;
        const total = 5;
        
        if (judul.length >= 5) completed++;
        if (deskripsi.length >= 20) completed++;
        if (kategori && gedung && lantai) completed++;
        if (prioritas) completed++;
        if (photos >= 1) completed++;
        
        const percentage = (completed / total) * 100;
        const progressBar = document.getElementById('formProgress');
        const currentStep = document.getElementById('currentStep');
        
        if (progressBar && currentStep) {
            progressBar.style.width = percentage + '%';
            currentStep.textContent = completed;
            
            // Change color based on progress
            if (percentage === 100) {
                progressBar.className = 'progress-bar bg-success';
            } else if (percentage >= 60) {
                progressBar.className = 'progress-bar bg-warning';
            } else {
                progressBar.className = 'progress-bar bg-primary';
            }
        }
    }
    
    // Track form changes
    document.getElementById('pengaduanForm').addEventListener('input', updateProgress);
    document.getElementById('pengaduanForm').addEventListener('change', updateProgress);
    
    // ========== Form Validation Before Submit ==========
    
    document.getElementById('pengaduanForm').addEventListener('submit', function(e) {
        if (activeDuplicate && !duplicateOverride.checked) {
            e.preventDefault();
            alert(`Lokasi ini sudah punya laporan aktif (#${activeDuplicate.kode_pengaduan}). Jika titik kerusakan berbeda, centang opsi "Tetap kirim laporan ini".`);
            duplicateAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        // Check if photos are selected
        if (capturedPhotos.length === 0) {
            e.preventDefault();
            showPhotoValidationError('Minimal 1 foto bukti wajib diupload.');
            document.getElementById('openCameraBtn').scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('openCameraBtn').classList.add('btn-danger');
            setTimeout(() => {
                document.getElementById('openCameraBtn').classList.remove('btn-danger');
            }, 2000);
            return false;
        }

        clearPhotoValidationError();
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    });
    
    // ========== Initialize ==========
    
    // Trigger change events if there are old values (validation errors)
    @if(old('kategori_id'))
        document.getElementById('kategori_id').dispatchEvent(new Event('change'));
    @endif
    @if(old('gedung_id'))
        document.getElementById('gedung_id').dispatchEvent(new Event('change'));
        @if(old('lantai'))
            setTimeout(() => {
                document.getElementById('lantai').value = '{{ old('lantai') }}';
                document.getElementById('lantai').dispatchEvent(new Event('change'));
            }, 100);
        @endif
    @endif
    
    // Initial progress update
    updateProgress();
    scheduleDuplicateCheck();
    
    // Clean up camera on page unload
    window.addEventListener('beforeunload', function() {
        closeCamera();
    });
</script>
@endpush
