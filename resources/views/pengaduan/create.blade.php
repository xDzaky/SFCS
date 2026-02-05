@extends('layouts.sfcs')

@section('title', 'Buat Pengaduan')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('pengaduan.index') }}">Pengaduan</a></li>
    <li class="breadcrumb-item active">Buat Baru</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Buat Pengaduan Baru</h1>
    <p class="page-subtitle">Laporkan masalah fasilitas sekolah</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Judul -->
                    <div class="mb-4">
                        <label for="judul" class="form-label">Judul Pengaduan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                               id="judul" name="judul" value="{{ old('judul') }}" 
                               placeholder="Contoh: Lampu Mati di Ruang Kelas X IPA 1"
                               maxlength="100"
                               required>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maksimal 100 karakter</div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-4">
                        <label for="deskripsi" class="form-label">Deskripsi Kerusakan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" name="deskripsi" rows="5" 
                                  placeholder="Jelaskan masalah secara detail..."
                                  minlength="20"
                                  required>{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Minimal 20 karakter</div>
                    </div>

                    <div class="row mb-4">
                        <!-- Kategori -->
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="kategori_id" class="form-label">Kategori Fasilitas <span class="text-danger">*</span></label>
                            <select class="form-select @error('kategori_id') is-invalid @enderror" 
                                    id="kategori_id" name="kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}" 
                                            data-sub-kategoris='@json($kategori->subKategoris)'
                                            {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sub Kategori -->
                        <div class="col-md-6">
                            <label for="sub_kategori_id" class="form-label">Sub Kategori</label>
                            <select class="form-select @error('sub_kategori_id') is-invalid @enderror" 
                                    id="sub_kategori_id" name="sub_kategori_id">
                                <option value="">Pilih Sub Kategori (Opsional)</option>
                            </select>
                            @error('sub_kategori_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Gedung -->
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="gedung_id" class="form-label">Gedung/Bangunan <span class="text-danger">*</span></label>
                            <select class="form-select @error('gedung_id') is-invalid @enderror" 
                                    id="gedung_id" name="gedung_id" required>
                                <option value="">Pilih Gedung</option>
                                @foreach($gedungs as $gedung)
                                    <option value="{{ $gedung->id }}" 
                                            data-lantai="{{ $gedung->jumlah_lantai }}"
                                            data-ruangans='@json($gedung->ruangans)'
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
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="lantai" class="form-label">Lantai <span class="text-danger">*</span></label>
                            <select class="form-select @error('lantai') is-invalid @enderror" 
                                    id="lantai" name="lantai" required>
                                <option value="">Pilih Lantai</option>
                            </select>
                            @error('lantai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ruangan -->
                        <div class="col-md-4">
                            <label for="ruangan_id" class="form-label">Ruangan</label>
                            <select class="form-select @error('ruangan_id') is-invalid @enderror" 
                                    id="ruangan_id" name="ruangan_id">
                                <option value="">Pilih Ruangan (Opsional)</option>
                            </select>
                            @error('ruangan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Lokasi Detail (Optional) -->
                    <div class="mb-4">
                        <label for="lokasi_detail" class="form-label">Detail Lokasi</label>
                        <input type="text" class="form-control @error('lokasi_detail') is-invalid @enderror" 
                               id="lokasi_detail" name="lokasi_detail" value="{{ old('lokasi_detail') }}" 
                               placeholder="Contoh: Di samping jendela / Dekat pintu masuk">
                        @error('lokasi_detail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Opsional - Tambahkan keterangan lokasi spesifik jika diperlukan</div>
                    </div>

                    <!-- Tanggal Kejadian -->
                    <div class="mb-4">
                        <label for="tanggal_kejadian" class="form-label">Tanggal Kejadian</label>
                        <input type="date" class="form-control @error('tanggal_kejadian') is-invalid @enderror" 
                               id="tanggal_kejadian" name="tanggal_kejadian" 
                               value="{{ old('tanggal_kejadian') }}"
                               max="{{ date('Y-m-d') }}">
                        @error('tanggal_kejadian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Opsional - Kapan kerusakan pertama kali terjadi/ditemukan</div>
                    </div>

                    <!-- Urgensi / Prioritas -->
                    <div class="mb-4">
                        <label class="form-label">Tingkat Urgensi <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_rendah" value="rendah" 
                                       {{ old('prioritas') == 'rendah' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="prioritas_rendah">
                                    <span class="mb-1">🟢</span>
                                    <strong>Rendah</strong>
                                    <div class="small">SLA: 7 hari</div>
                                </label>
                            </div>
                            <div class="col-md-3 col-6">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_sedang" value="sedang"
                                       {{ old('prioritas', 'sedang') == 'sedang' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="prioritas_sedang">
                                    <span class="mb-1">🟡</span>
                                    <strong>Sedang</strong>
                                    <div class="small">SLA: 3 hari</div>
                                </label>
                            </div>
                            <div class="col-md-3 col-6">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_tinggi" value="tinggi"
                                       {{ old('prioritas') == 'tinggi' ? 'checked' : '' }}>
                                <label class="btn btn-outline-orange w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="prioritas_tinggi">
                                    <span class="mb-1">🟠</span>
                                    <strong>Tinggi</strong>
                                    <div class="small">SLA: 1 hari</div>
                                </label>
                            </div>
                            <div class="col-md-3 col-6">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_urgent" value="urgent"
                                       {{ old('prioritas') == 'urgent' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="prioritas_urgent">
                                    <span class="mb-1">🔴</span>
                                    <strong>Darurat</strong>
                                    <div class="small">SLA: 6 jam</div>
                                </label>
                            </div>
                        </div>
                        @error('prioritas')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Photos -->
                    <div class="mb-4">
                        <label for="photos" class="form-label">Bukti Foto <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror" 
                               id="photos" name="photos[]" multiple accept="image/jpeg,image/png,image/jpg" required>
                        @error('photos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('photos.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Wajib - Minimal 1 foto, maksimal 5 foto, masing-masing maksimal 5MB (JPG, PNG)</div>
                        
                        <!-- Preview -->
                        <div id="photoPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Pengaduan
                        </button>
                        <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Panduan
            </div>
            <div class="card-body">
                <h6 class="fw-semibold">Tips Membuat Pengaduan:</h6>
                <ul class="small text-muted mb-4">
                    <li>Gunakan judul yang jelas dan spesifik</li>
                    <li>Jelaskan masalah secara detail (minimal 20 karakter)</li>
                    <li>Pilih kategori yang sesuai</li>
                    <li>Tentukan lokasi dengan benar</li>
                    <li>Sertakan foto bukti kerusakan (wajib)</li>
                    <li>Pilih tingkat urgensi yang tepat</li>
                </ul>

                <h6 class="fw-semibold">Tingkat Urgensi:</h6>
                <ul class="small mb-0">
                    <li class="mb-2"><span class="text-success">🟢 <strong>Rendah:</strong></span> Tidak mengganggu aktivitas, SLA 7 hari</li>
                    <li class="mb-2"><span class="text-warning">🟡 <strong>Sedang:</strong></span> Sedikit mengganggu aktivitas, SLA 3 hari</li>
                    <li class="mb-2"><span style="color: #fd7e14;">🟠 <strong>Tinggi:</strong></span> Sangat mengganggu aktivitas, SLA 1 hari</li>
                    <li><span class="text-danger">🔴 <strong>Darurat:</strong></span> Berbahaya/tidak bisa digunakan, SLA 6 jam</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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
</style>
@endpush

@push('scripts')
<script>
    // Load sub kategoris when kategori changes
    document.getElementById('kategori_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const subKategoriSelect = document.getElementById('sub_kategori_id');
        
        subKategoriSelect.innerHTML = '<option value="">Pilih Sub Kategori (Opsional)</option>';
        
        if (this.value && selected.dataset.subKategoris) {
            try {
                const subKategoris = JSON.parse(selected.dataset.subKategoris);
                subKategoris.forEach(item => {
                    if (item.is_active) {
                        subKategoriSelect.innerHTML += `<option value="${item.id}">${item.nama}</option>`;
                    }
                });
            } catch(e) {
                console.error('Error parsing sub kategoris:', e);
            }
        }
    });

    // Load lantai and ruangans when gedung changes
    document.getElementById('gedung_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const lantaiSelect = document.getElementById('lantai');
        const ruanganSelect = document.getElementById('ruangan_id');
        
        // Reset
        lantaiSelect.innerHTML = '<option value="">Pilih Lantai</option>';
        ruanganSelect.innerHTML = '<option value="">Pilih Ruangan (Opsional)</option>';
        
        if (this.value) {
            // Load lantai
            const jumlahLantai = parseInt(selected.dataset.lantai) || 1;
            for (let i = 1; i <= jumlahLantai; i++) {
                lantaiSelect.innerHTML += `<option value="${i}">Lantai ${i}</option>`;
            }
            
            // Store ruangans data
            lantaiSelect.dataset.ruangans = selected.dataset.ruangans || '[]';
        }
    });

    // Load ruangans when lantai changes
    document.getElementById('lantai').addEventListener('change', function() {
        const ruanganSelect = document.getElementById('ruangan_id');
        const lantai = this.value;
        
        ruanganSelect.innerHTML = '<option value="">Pilih Ruangan (Opsional)</option>';
        
        if (lantai && this.dataset.ruangans) {
            try {
                const ruangans = JSON.parse(this.dataset.ruangans);
                ruangans.filter(r => r.lantai == lantai && r.is_active).forEach(item => {
                    ruanganSelect.innerHTML += `<option value="${item.id}">${item.nama}</option>`;
                });
            } catch(e) {
                console.error('Error parsing ruangans:', e);
            }
        }
    });

    // Photo preview
    document.getElementById('photos').addEventListener('change', function() {
        const preview = document.getElementById('photoPreview');
        preview.innerHTML = '';
        
        if (this.files.length > 5) {
            alert('Maksimal 5 foto!');
            this.value = '';
            return;
        }
        
        Array.from(this.files).forEach((file, index) => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`File ${file.name} terlalu besar! Maksimal 5MB.`);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'position-relative';
                div.innerHTML = `
                    <img src="${e.target.result}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // Trigger change events if there are old values
    @if(old('kategori_id'))
        document.getElementById('kategori_id').dispatchEvent(new Event('change'));
    @endif
    @if(old('gedung_id'))
        document.getElementById('gedung_id').dispatchEvent(new Event('change'));
    @endif
</script>
@endpush
