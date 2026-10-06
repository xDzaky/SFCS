<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <title>Dashboard — SFCS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @php
        $primaryColor  = \App\Models\Setting::getValue('theme_primary_color', '#4f46e5');
        $schoolLogo    = \App\Models\Setting::getValue('school_logo');
        $schoolName    = \App\Models\Setting::getValue('school_name', 'SFCS');
        $appName       = \App\Models\Setting::getValue('app_name', 'SFCS');
    @endphp

    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        :root {
            --primary: {{ $primaryColor }};
            --sidebar-w: 260px;
            --header-h: 60px;
        }

        body {
            background: #f3f4f6;
            margin: 0;
            overflow-x: hidden;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            z-index: 1040;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            height: var(--header-h);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            flex-shrink: 0;
        }

        .sidebar-nav { padding: 1rem 0.75rem; flex: 1; }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 2px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.12); color: white; }
        .sidebar-link.active { background: var(--primary); color: white; }
        .sidebar-link i { width: 20px; text-align: center; font-size: 1rem; }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 600;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.75rem 1rem 0.3rem;
        }

        /* Login notice at bottom of sidebar */
        .sidebar-login-notice {
            margin: 0.75rem;
            padding: 0.85rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .sidebar-login-notice p { color: rgba(255,255,255,0.75); font-size: 0.78rem; margin-bottom: 0.6rem; }
        .sidebar-login-notice .btn {
            font-size: 0.8rem;
            padding: 0.35rem 0.9rem;
            border-radius: 6px;
            font-weight: 600;
        }

        /* ── HEADER ── */
        .main-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--header-h);
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .header-right { display: flex; align-items: center; gap: 0.75rem; }
        .btn-login-header {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.45rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: opacity 0.2s;
        }
        .btn-login-header:hover { opacity: 0.88; color: white; }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: var(--sidebar-w);
            padding-top: var(--header-h);
            min-height: 100vh;
        }
        .page-content { padding: 1.5rem; }

        /* ── GUEST BANNER ── */
        .guest-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .guest-banner .text-block .title { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.2rem; }
        .guest-banner .text-block .sub   { font-size: 0.85rem; opacity: 0.85; }
        .btn-white-solid {
            background: white;
            color: #4f46e5;
            border: none;
            padding: 0.55rem 1.3rem;
            border-radius: 9px;
            font-weight: 700;
            font-size: 0.875rem;
            text-decoration: none;
            white-space: nowrap;
            transition: transform 0.15s;
        }
        .btn-white-solid:hover { transform: translateY(-1px); color: #3730a3; }

        /* ── STAT CARDS ── */
        .card { border-radius: 12px; border: none; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }

        /* ── ACTION BUTTONS ── */
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem 0.5rem;
            border-radius: 14px;
            border: none;
            font-weight: 700;
            font-size: 0.8rem;
            text-decoration: none;
            gap: 0.4rem;
            min-height: 85px;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            width: 100%;
        }
        .action-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.15); }
        .action-btn i { font-size: 1.5rem; }
        .action-btn-primary { background: #2563eb; color: white; }
        .action-btn-primary:hover { color: white; }
        .action-btn-success { background: #16a34a; color: white; }
        .action-btn-success:hover { color: white; }
        .action-btn-outline {
            background: white;
            color: #374151;
            border: 1.5px solid #e5e7eb;
            font-weight: 600;
        }

        /* ── KATEGORI CHIPS ── */
        .kategori-chip {
            display: inline-block;
            background: #ede9fe;
            color: #4f46e5;
            border-radius: 20px;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 500;
            margin: 0.2rem;
        }

        /* ── MOBILE ── */
        @media (max-width: 767.98px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .main-header { left: 0; }
            .page-content { padding: 1rem 0.85rem; }
            .guest-banner { flex-direction: column; text-align: center; }
            .guest-banner .btn-white-solid { width: 100%; text-align: center; justify-content: center; display: block; }
        }
    </style>
</head>
<body>

{{-- SIDEBAR (mode guest) --}}
<aside class="sidebar">
    <div class="sidebar-brand">
        @if($schoolLogo)
            <img src="{{ Storage::url($schoolLogo) }}" alt="Logo" style="height:34px;margin-right:8px;">
        @else
            <i class="fas fa-school me-2"></i>
        @endif
        {{ $appName }}
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('home') }}" class="sidebar-link active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-section-title">Pengaduan</div>
        {{-- Klik → akan redirect ke login dengan intended=pengaduan.create --}}
        <a href="{{ route('login') }}?intended=pengaduan" class="sidebar-link">
            <i class="fas fa-list"></i>
            <span>Pengaduan Saya</span>
        </a>
        <a href="{{ route('login') }}?intended=pengaduan" class="sidebar-link">
            <i class="fas fa-plus-circle"></i>
            <span>Buat Pengaduan</span>
        </a>

        <div class="nav-section-title">Peminjaman</div>
        <a href="{{ route('login') }}?intended=pinjaman" class="sidebar-link">
            <i class="fas fa-box-open"></i>
            <span>Pinjam Barang</span>
        </a>
    </nav>

    <div class="sidebar-login-notice">
        <p>Silakan masuk untuk mengakses fitur lengkap SFCS.</p>
        <a href="{{ route('login') }}" class="btn btn-light w-100">
            <i class="fas fa-sign-in-alt me-1"></i> Masuk / Login
        </a>
    </div>
</aside>

{{-- HEADER (mode guest) --}}
<header class="main-header">
    <div class="d-flex align-items-center gap-2">
        {{-- Mobile toggle (show sidebar on mobile tap) --}}
        <button class="btn btn-link p-0 me-1 d-md-none" onclick="document.querySelector('.sidebar').style.display='flex'" style="color:#374151">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <span style="font-weight:600;color:#1e1b4b;font-size:1rem;">{{ $schoolName }}</span>
    </div>
    <div class="header-right">
        <a href="{{ route('login') }}" class="btn-login-header">
            <i class="fas fa-sign-in-alt"></i> Login
        </a>
    </div>
</header>

{{-- MAIN CONTENT --}}
<div class="main-content">
    <div class="page-content">

        {{-- Guest Banner --}}
        <div class="guest-banner">
            <div class="text-block">
                <div class="title">👋 Selamat Datang di SFCS!</div>
                <div class="sub">Sistem Fasilitas & Pengaduan Sekolah. Login untuk membuat laporan atau meminjam barang.</div>
            </div>
            <a href="{{ route('login') }}" class="btn-white-solid">
                <i class="fas fa-sign-in-alt me-1"></i> Login Sekarang
            </a>
        </div>

        {{-- Stats (publik, hanya tampilkan selesai) --}}
        <div class="row g-2 g-md-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body p-3">
                        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex p-2 mb-2">
                            <i class="fas fa-check-circle text-success fs-5"></i>
                        </div>
                        <h4 class="mb-1 fw-bold fs-5">{{ $totalPublik }}</h4>
                        <p class="mb-0 text-muted small">Pengaduan Selesai</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body p-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex p-2 mb-2">
                            <i class="fas fa-shield-alt text-primary fs-5"></i>
                        </div>
                        <h4 class="mb-1 fw-bold fs-5">{{ $kategori->count() }}</h4>
                        <p class="mb-0 text-muted small">Kategori Pengaduan</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body p-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex p-2 mb-2">
                            <i class="fas fa-user-lock text-warning fs-5"></i>
                        </div>
                        <h4 class="mb-1 fw-bold fs-5">—</h4>
                        <p class="mb-0 text-muted small">Pengaduan Saya</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body p-3">
                        <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex p-2 mb-2">
                            <i class="fas fa-box-open text-info fs-5"></i>
                        </div>
                        <h4 class="mb-1 fw-bold fs-5">—</h4>
                        <p class="mb-0 text-muted small">Pinjaman Saya</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons (redirect ke login + intended) --}}
        <div class="row g-2 mb-3">
            <div class="col-6">
                {{-- Tombol Buat Pengaduan → simpan intended → login --}}
                <a href="{{ route('login') }}?intended=pengaduan"
                   class="action-btn action-btn-primary"
                   onclick="sessionStorage.setItem('sfcs_intended', 'pengaduan')">
                    <i class="fas fa-plus-circle"></i>
                    <span>BUAT PENGADUAN</span>
                </a>
            </div>
            <div class="col-6">
                {{-- Tombol Pinjam Barang → simpan intended → login --}}
                <a href="{{ route('login') }}?intended=pinjaman"
                   class="action-btn action-btn-success"
                   onclick="sessionStorage.setItem('sfcs_intended', 'pinjaman')">
                    <i class="fas fa-hand-holding"></i>
                    <span>PINJAM BARANG</span>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('login') }}?intended=pengaduan" class="action-btn action-btn-outline">
                    <i class="fas fa-list text-primary"></i>
                    <small>Pengaduan</small>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('login') }}?intended=pinjaman" class="action-btn action-btn-outline">
                    <i class="fas fa-box text-success"></i>
                    <small>Pinjaman</small>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('pengaduan.track') }}" class="action-btn action-btn-outline">
                    <i class="fas fa-search text-secondary"></i>
                    <small>Lacak</small>
                </a>
            </div>
        </div>

        {{-- Kategori pengaduan yang tersedia --}}
        @if($kategori->count() > 0)
        <div class="card mb-3">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-2">
                    <i class="fas fa-tags text-primary me-1"></i>
                    Kategori Pengaduan Tersedia
                </h6>
                <div>
                    @foreach($kategori as $k)
                        <span class="kategori-chip">{{ $k }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Info / CTA card --}}
        <div class="card" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3 flex-shrink-0">
                        <i class="fas fa-info-circle text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 small fw-bold">Cara Menggunakan SFCS</h6>
                        <p class="mb-0 small text-muted">
                            Login dengan akun yang diberikan oleh Admin sekolah, kemudian klik
                            <strong>Buat Pengaduan</strong> untuk melaporkan kerusakan fasilitas atau
                            <strong>Pinjam Barang</strong> untuk meminjam alat dari Sarpras.
                        </p>
                    </div>
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary ms-3 d-none d-md-inline-flex align-items-center gap-1">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
            </div>
        </div>

    </div>{{-- /page-content --}}
</div>{{-- /main-content --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
