@extends('layouts.sfcs')

@section('title', 'Profil Saya')

@section('content')
<div class="row justify-content-center">
    <!-- Kolom Kiri: Kartu Identitas -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm text-center py-5 h-100">
            <div class="card-body">
                <div class="position-relative d-inline-block mb-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" 
                         style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold; border: 4px solid #eef2ff;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-white p-2" 
                         title="Online" style="width: 24px; height: 24px;"></div>
                </div>
                
                <h3 class="h4 fw-bold text-dark mb-1">{{ Auth::user()->name }}</h3>
                <p class="text-muted mb-3">{{ Auth::user()->email }}</p>
                
                <div class="d-inline-flex align-items-center px-3 py-1 bg-light rounded-pill border">
                    <i class="fas fa-user-tag text-primary me-2"></i>
                    <span class="fw-bold text-uppercase small text-dark">{{ Auth::user()->role ?? 'Siswa' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Form Edit -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                <ul class="nav nav-pills card-header-pills" id="profileTab" role="tablist">
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link active rounded-pill px-4" id="biodata-tab" data-bs-toggle="tab" data-bs-target="#biodata" type="button" role="tab">
                            <i class="fas fa-id-card me-2"></i> Biodata
                        </button>
                    </li>
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link rounded-pill px-4" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                            <i class="fas fa-lock me-2"></i> Ganti Password
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 text-danger" id="danger-tab" data-bs-toggle="tab" data-bs-target="#danger" type="button" role="tab">
                            <i class="fas fa-exclamation-triangle me-2"></i> Hapus Akun
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4">
                <div class="tab-content" id="profileTabContent">
                    
                    <!-- Tab Biodata -->
                    <div class="tab-pane fade show active" id="biodata" role="tabpanel">
                        <div class="alert alert-info border-0 d-flex align-items-center mb-4" role="alert">
                            <i class="fas fa-info-circle fa-lg me-3"></i>
                            <div>
                                Pastikan nama dan email sesuai dengan data sekolah ya!
                            </div>
                        </div>

                        <form method="post" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            <div class="mb-4">
                                <label for="name" class="form-label fw-bold small text-uppercase text-muted">Nama Lengkap</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" placeholder="Contoh: Andi Pratama">
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold small text-uppercase text-muted">Alamat Email</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username" placeholder="Contoh: andi@sekolah.sch.id">
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid mt-5">
                                <button type="submit" class="btn btn-primary-gradient text-white btn-lg rounded-pill fw-bold">
                                    <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN
                                </button>
                            </div>
                            
                            @if (session('status') === 'profile-updated')
                                <div class="alert alert-success mt-3 text-center rounded-pill">
                                    <i class="fas fa-check-circle me-2"></i> Data berhasil diperbarui!
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Tab Password -->
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <div class="alert alert-warning border-0 d-flex align-items-center mb-4" role="alert">
                            <i class="fas fa-shield-alt fa-lg me-3"></i>
                            <div>
                                Gunakan password minimal 8 karakter agar akunmu tetap aman.
                            </div>
                        </div>

                        <form method="post" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-bold small text-uppercase text-muted">Password Saat Ini</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="current_password" name="current_password" autocomplete="current-password" placeholder="Masukan password lama">
                                    <span class="input-group-text bg-white border-start-0 cursor-pointer" onclick="togglePassword('current_password', this)"><i class="far fa-eye text-muted"></i></span>
                                </div>
                                @error('current_password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold small text-uppercase text-muted">Password Baru</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" autocomplete="new-password" placeholder="Masukan password baru">
                                    <span class="input-group-text bg-white border-start-0 cursor-pointer" onclick="togglePassword('password', this)"><i class="far fa-eye text-muted"></i></span>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-bold small text-uppercase text-muted">Ulangi Password Baru</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-check-double text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password_confirmation" name="password_confirmation" autocomplete="new-password" placeholder="Ketik ulang password baru">
                                </div>
                                @error('password_confirmation')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid mt-5">
                                <button type="submit" class="btn btn-warning text-dark btn-lg rounded-pill fw-bold">
                                    <i class="fas fa-sync-alt me-2"></i> GANTI PASSWORD
                                </button>
                            </div>

                            @if (session('status') === 'password-updated')
                                <div class="alert alert-success mt-3 text-center rounded-pill">
                                    <i class="fas fa-check-circle me-2"></i> Password berhasil diganti!
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Tab Hapus Akun -->
                    <div class="tab-pane fade" id="danger" role="tabpanel">
                        <div class="text-center py-4">
                            <i class="fas fa-user-slash fa-4x text-danger mb-3 opacity-50"></i>
                            <h4 class="text-danger fw-bold">Hapus Akun Permanen</h4>
                            <p class="text-muted">
                                Setelah akun dihapus, semua data laporan, feedback, dan riwayat tidak akan bisa dikembalikan lagi. 
                                <br>Pastikan kamu benar-benar yakin ya!
                            </p>
                            
                            <button class="btn btn-outline-danger btn-lg rounded-pill px-5 mt-3" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
                                Saya Mengerti, Hapus Akun Saya
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="post" action="{{ route('profile.destroy') }}" class="p-4">
                @csrf
                @method('delete')
                
                <div class="text-center mb-4">
                    <div class="mx-auto bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <h4 class="fw-bold">Yakin mau hapus akun?</h4>
                    <p class="text-muted small">Tolong masukkan password kamu untuk konfirmasi terakhir.</p>
                </div>

                <div class="mb-4">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light"><i class="fas fa-key text-muted"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Password kamu" required>
                    </div>
                    @error('password', 'userDeletion')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill">
                        Ya, Hapus Sekarang
                    </button>
                    <button type="button" class="btn btn-light btn-lg rounded-pill" data-bs-dismiss="modal">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .card {
        border-radius: 1rem; /* Sudut lebih bulat */
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); /* Bayangan lembut */
        transition: transform 0.2s;
    }
    
    .avatar-circle {
        background: linear-gradient(135deg, var(--primary-color) 0%, #818cf8 100%);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
    }

    .nav-pills .nav-link {
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        color: #6b7280;
    }
    
    .nav-pills .nav-link.active {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        transform: translateY(-2px);
    }

    .nav-pills .nav-link:hover:not(.active) {
        background-color: #f3f4f6;
        color: var(--primary-color);
    }

    /* Input Styling */
    .input-group-text {
        border-color: #e5e7eb;
    }
    
    .form-control {
        border-color: #e5e7eb;
        font-size: 1rem;
        padding: 0.8rem 1rem;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    /* Button Gradient */
    .btn-primary-gradient {
        background: linear-gradient(135deg, var(--primary-color) 0%, #4338ca 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        transition: all 0.3s ease;
    }

    .btn-primary-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.5);
    }
    
    /* Animation */
    .tab-pane {
        animation: fadeIn 0.4s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    function togglePassword(fieldId, iconElement) {
        const input = document.getElementById(fieldId);
        const icon = iconElement.querySelector('i');
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endpush
