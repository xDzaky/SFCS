<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — SFCS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: #f0f4ff;
            display: flex;
            align-items: stretch;
            margin: 0;
        }

        /* ── LEFT PANEL ── */
        .login-left {
            background: linear-gradient(145deg, #1e1b4b 0%, #312e81 40%, #4338ca 80%, #4f46e5 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            top: -80px; right: -80px;
        }
        .login-left::after {
            content: '';
            position: absolute;
            width: 250px; height: 250px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            bottom: -60px; left: -60px;
        }

        .sfcs-logo-box {
            width: 64px; height: 64px;
            background: rgba(255,255,255,.15);
            border: 2px solid rgba(255,255,255,.25);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: #fff;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(6px);
        }

        .sfcs-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: .5rem;
        }
        .sfcs-subtitle {
            color: rgba(255,255,255,.7);
            font-size: .9rem;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        /* Role cards */
        .role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
        .role-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 14px;
            padding: .9rem;
            backdrop-filter: blur(6px);
            transition: background .2s;
        }
        .role-card:hover { background: rgba(255,255,255,.14); }
        .role-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem;
            margin-bottom: .5rem;
        }
        .role-name  { font-size: .75rem; font-weight: 700; color: #fff; }
        .role-desc  { font-size: .65rem; color: rgba(255,255,255,.55); margin-top: 1px; }

        .left-footer {
            margin-top: 2.5rem;
            font-size: .7rem;
            color: rgba(255,255,255,.35);
        }

        /* ── RIGHT PANEL ── */
        .login-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            background: #fff;
        }

        .login-form-wrap { width: 100%; max-width: 400px; }

        .form-welcome {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e1b4b;
            margin-bottom: .25rem;
        }
        .form-welcome-sub {
            font-size: .875rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        .form-label {
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .4rem;
        }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: .65rem 1rem;
            font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,.12);
        }
        .input-group-text {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px 0 0 10px;
            color: #94a3b8;
        }
        .input-group .form-control { border-radius: 0 10px 10px 0; border-left: none; }
        .input-group:focus-within .input-group-text {
            border-color: #4f46e5;
            color: #4f46e5;
        }
        .input-group:focus-within .form-control { border-color: #4f46e5; }

        .btn-login {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
            padding: .75rem;
            width: 100%;
            transition: opacity .2s, transform .15s;
        }
        .btn-login:hover { opacity: .92; transform: translateY(-1px); color: #fff; }
        .btn-login:active { transform: translateY(0); }

        .toggle-password {
            cursor: pointer;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-left: none;
            border-radius: 0 10px 10px 0;
            color: #94a3b8;
            padding: 0 .75rem;
            font-size: .8rem;
        }
        .toggle-password:hover { color: #4f46e5; }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .form-check-label { font-size: .82rem; color: #64748b; cursor: pointer; }
        .form-check-input:checked { background-color: #4f46e5; border-color: #4f46e5; }

        .forgot-link {
            font-size: .82rem;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; color: #4338ca; }

        .divider {
            display: flex; align-items: center; gap: .75rem;
            margin: 1.5rem 0;
            color: #cbd5e1; font-size: .75rem;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }

        .role-hint {
            display: flex; align-items: center; gap: .6rem;
            background: #f8f9ff;
            border: 1px solid #e0e2ff;
            border-radius: 10px;
            padding: .7rem .9rem;
            font-size: .78rem;
            color: #4f46e5;
        }

        /* Error messages */
        .field-error { font-size: .78rem; color: #ef4444; margin-top: .3rem; }

        /* ── RESPONSIVE ── */
        @media (max-width: 767.98px) {
            .login-left { padding: 2.5rem 1.5rem; min-height: auto; }
            .role-grid { grid-template-columns: 1fr 1fr; gap: .5rem; }
            .sfcs-title { font-size: 1.5rem; }
            .left-footer { display: none; }
        }
    </style>
</head>
<body>
<div class="container-fluid p-0 min-vh-100 d-flex">
<div class="row g-0 w-100">

    {{-- ══ LEFT — Branding ══════════════════════════════════════════════════ --}}
    <div class="col-12 col-md-5 col-lg-4 login-left">

        <div class="sfcs-logo-box">
            <i class="fas fa-building-shield"></i>
        </div>

        <div class="sfcs-title">SFCS</div>
        <p class="sfcs-subtitle">
            School Facility & Complaint System<br>
            Platform pelaporan dan pemantauan<br>kerusakan fasilitas sekolah.
        </p>

        {{-- Role cards --}}
        <div class="role-grid">
            <div class="role-card">
                <div class="role-icon" style="background:rgba(99,102,241,.25);">
                    <i class="fas fa-user-graduate text-white"></i>
                </div>
                <div class="role-name">Siswa</div>
                <div class="role-desc">Login dengan NIS</div>
            </div>
            <div class="role-card">
                <div class="role-icon" style="background:rgba(34,197,94,.2);">
                    <i class="fas fa-wrench text-white"></i>
                </div>
                <div class="role-name">Teknisi</div>
                <div class="role-desc">Login dengan Email</div>
            </div>
            <div class="role-card">
                <div class="role-icon" style="background:rgba(245,158,11,.2);">
                    <i class="fas fa-user-tie text-white"></i>
                </div>
                <div class="role-name">Admin</div>
                <div class="role-desc">Login dengan Email</div>
            </div>
            <div class="role-card">
                <div class="role-icon" style="background:rgba(239,68,68,.2);">
                    <i class="fas fa-shield-halved text-white"></i>
                </div>
                <div class="role-name">Super Admin</div>
                <div class="role-desc">Login dengan Email</div>
            </div>
        </div>

        <p class="left-footer">
            &copy; {{ date('Y') }} SFCS — Sistem Pengaduan Fasilitas Sekolah
        </p>
    </div>

    {{-- ══ RIGHT — Login Form ════════════════════════════════════════════════ --}}
    <div class="col-12 col-md-7 col-lg-8 login-right">
        <div class="login-form-wrap">

            <p class="form-welcome">Selamat Datang 👋</p>
            <p class="form-welcome-sub">Masuk ke akun Anda untuk melanjutkan</p>

            {{-- Session status --}}
            @if(session('status'))
                <div class="alert alert-success py-2 small mb-3 rounded-3">
                    <i class="fas fa-check-circle me-1"></i>{{ session('status') }}
                </div>
            @endif

            {{-- Error alerts --}}
            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3 rounded-3">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- NIS / Email --}}
                <div class="mb-3">
                    <label for="identifier" class="form-label">NIS atau Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user" style="font-size:.8rem;"></i></span>
                        <input type="text" id="identifier" name="identifier"
                               value="{{ old('identifier') }}"
                               class="form-control @error('identifier') is-invalid @enderror"
                               placeholder="Masukkan NIS atau email..." required autofocus>
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock" style="font-size:.8rem;"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Masukkan password..." required>
                        <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember + Forgot --}}
                <div class="remember-row">
                    <div class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                        <label class="form-check-label" for="remember">Tetap login (30 hari)</label>
                    </div>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-login">
                    <i class="fas fa-right-to-bracket me-2"></i>Masuk ke SFCS
                </button>
            </form>

            <div class="divider">atau</div>

            <div class="role-hint">
                <i class="fas fa-circle-info flex-shrink-0"></i>
                <span>Siswa gunakan <strong>NIS</strong> sebagai username. Teknisi, Admin & Super Admin gunakan <strong>Email</strong>.</span>
            </div>
        </div>
    </div>

</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
</body>
</html>
