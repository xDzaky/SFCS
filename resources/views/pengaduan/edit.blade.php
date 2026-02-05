@extends('layouts.sfcs')

@section('title', 'Edit Pengaduan')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('pengaduan.index') }}">Pengaduan</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pengaduan.show', $pengaduan) }}">{{ $pengaduan->kode_pengaduan }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Pengaduan</h1>
    <p class="page-subtitle">{{ $pengaduan->kode_pengaduan }}</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('pengaduan.update', $pengaduan) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Judul -->
                    <div class="mb-4">
                        <label for="judul" class="form-label">Judul Pengaduan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                               id="judul" name="judul" value="{{ old('judul', $pengaduan->judul) }}" 
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
                                  minlength="20"
                                  required>{{ old('deskripsi', $pengaduan->deskripsi) }}</textarea>
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
                                            {{ old('kategori_id', $pengaduan->kategori_id) == $kategori->id ? 'selected' : '' }}>
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
                                            {{ old('gedung_id', $pengaduan->gedung_id) == $gedung->id ? 'selected' : '' }}>
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
                               id="lokasi_detail" name="lokasi_detail" value="{{ old('lokasi_detail', $pengaduan->lokasi_detail) }}" 
                               placeholder="Contoh: Di samping jendela / Dekat pintu masuk">
                        @error('lokasi_detail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Kejadian -->
                    <div class="mb-4">
                        <label for="tanggal_kejadian" class="form-label">Tanggal Kejadian</label>
                        <input type="date" class="form-control @error('tanggal_kejadian') is-invalid @enderror" 
                               id="tanggal_kejadian" name="tanggal_kejadian" 
                               value="{{ old('tanggal_kejadian', $pengaduan->tanggal_kejadian ? $pengaduan->tanggal_kejadian->format('Y-m-d') : '') }}"
                               max="{{ date('Y-m-d') }}">
                        @error('tanggal_kejadian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Urgensi / Prioritas -->
                    <div class="mb-4">
                        <label class="form-label">Tingkat Urgensi <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_rendah" value="rendah" 
                                       {{ old('prioritas', $pengaduan->prioritas) == 'rendah' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="prioritas_rendah">
                                    <span class="mb-1">🟢</span>
                                    <strong>Rendah</strong>
                                </label>
                            </div>
                            <div class="col-md-3 col-6">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_sedang" value="sedang"
                                       {{ old('prioritas', $pengaduan->prioritas) == 'sedang' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="prioritas_sedang">
                                    <span class="mb-1">🟡</span>
                                    <strong>Sedang</strong>
                                </label>
                            </div>
                            <div class="col-md-3 col-6">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_tinggi" value="tinggi"
                                       {{ old('prioritas', $pengaduan->prioritas) == 'tinggi' ? 'checked' : '' }}>
                                <label class="btn btn-outline-orange w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="prioritas_tinggi">
                                    <span class="mb-1">🟠</span>
                                    <strong>Tinggi</strong>
                                </label>
                            </div>
                            <div class="col-md-3 col-6">
                                <input type="radio" class="btn-check" name="prioritas" id="prioritas_urgent" value="urgent"
                                       {{ old('prioritas', $pengaduan->prioritas) == 'urgent' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="prioritas_urgent">
                                    <span class="mb-1">🔴</span>
                                    <strong>Darurat</strong>
                                </label>
                            </div>
                        </div>
                        @error('prioritas')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Current Photos -->
                    @if($pengaduan->photos && $pengaduan->photos->count() > 0)
                        <div class="mb-4">
                            <label class="form-label">Foto Saat Ini ({{ $pengaduan->photos->count() }})</label>
                            <div class="row g-2">
                                @foreach($pengaduan->photos as $photo)
                                    <div class="col-6 col-md-3">
                                        <div class="position-relative">
                                            <img src="{{ asset('storage/' . $photo->file_path) }}" class="img-fluid rounded" 
                                                 style="width: 100%; height: 120px; object-fit: cover;">
                                            <form action="{{ route('pengaduan.photo.delete', $photo) }}" method="POST" class="position-absolute" style="top: 5px; right: 5px;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus foto ini?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- New Photos -->
                    <div class="mb-4">
                        <label for="photos" class="form-label">Tambah Foto Baru</label>
                        <input type="file" class="form-control @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror" 
                               id="photos" name="photos[]" multiple accept="image/jpeg,image/png,image/jpg">
                        @error('photos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('photos.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Opsional - Maksimal 5 foto total, masing-masing maksimal 5MB (JPG, PNG)</div>
                        
                        <!-- Preview -->
                        <div id="photoPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('pengaduan.show', $pengaduan) }}" class="btn btn-outline-secondary">
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
                <i class="fas fa-info-circle me-2"></i>Informasi
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <strong>Catatan:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Pengaduan hanya dapat diedit jika statusnya masih "Menunggu"</li>
                        <li>Klik tombol hapus pada foto untuk menghapusnya</li>
                        <li>Pastikan deskripsi jelas dan lengkap (minimal 20 karakter)</li>
                    </ul>
                </div>
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
    const oldSubKategoriId = {{ old('sub_kategori_id', $pengaduan->sub_kategori_id ?? 'null') }};
    const oldLantai = '{{ old('lantai', $pengaduan->lantai ?? '') }}';
    const oldRuanganId = {{ old('ruangan_id', $pengaduan->ruangan_id ?? 'null') }};

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
                        const isSelected = item.id == oldSubKategoriId ? 'selected' : '';
                        subKategoriSelect.innerHTML += `<option value="${item.id}" ${isSelected}>${item.nama}</option>`;
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
                const isSelected = i.toString() == oldLantai ? 'selected' : '';
                lantaiSelect.innerHTML += `<option value="${i}" ${isSelected}>Lantai ${i}</option>`;
            }
            
            // Store ruangans data
            lantaiSelect.dataset.ruangans = selected.dataset.ruangans || '[]';
            
            // Trigger lantai change if has old value
            if (oldLantai) {
                lantaiSelect.dispatchEvent(new Event('change'));
            }
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
                    const isSelected = item.id == oldRuanganId ? 'selected' : '';
                    ruanganSelect.innerHTML += `<option value="${item.id}" ${isSelected}>${item.nama}</option>`;
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
        
        const currentCount = {{ $pengaduan->photos->count() }};
        if (this.files.length + currentCount > 5) {
            alert('Maksimal 5 foto total!');
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

    // Initialize dropdowns on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Trigger kategori change to load sub kategoris
        document.getElementById('kategori_id').dispatchEvent(new Event('change'));
        
        // Trigger gedung change to load lantai and ruangans
        document.getElementById('gedung_id').dispatchEvent(new Event('change'));
    });
</script>
@endpush
