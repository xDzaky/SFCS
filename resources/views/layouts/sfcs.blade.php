<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SFCS') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --sidebar-width: 260px;
            --sidebar-collapsed: 70px;
            --header-height: 60px;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f3f4f6;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            color: white;
            font-weight: 600;
            font-size: 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand img {
            height: 36px;
            margin-right: 10px;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section-title {
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1.25rem 0.5rem;
            margin-top: 0.5rem;
        }

        .sidebar.collapsed .nav-section-title {
            display: none;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #818cf8;
        }

        .sidebar-link i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .sidebar.collapsed .sidebar-link span {
            display: none;
        }

        .sidebar.collapsed .sidebar-link i {
            margin-right: 0;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .sidebar.collapsed + .main-content {
            margin-left: var(--sidebar-collapsed);
        }

        /* Header */
        .main-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #6b7280;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .sidebar-toggle:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Notification Badge */
        .notification-badge {
            position: relative;
        }

        .notification-badge .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.65rem;
            padding: 0.25em 0.5em;
        }

        /* Page Content */
        .page-content {
            padding: 1.5rem;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 0.875rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        /* Stats Cards */
        .stat-card {
            border-radius: 0.75rem;
            padding: 1.5rem;
            color: white;
        }

        .stat-card.primary { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .stat-card.success { background: linear-gradient(135deg, #059669 0%, #10b981 100%); }
        .stat-card.warning { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); }
        .stat-card.danger { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); }
        .stat-card.info { background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        /* Status Badges */
        .badge-status {
            padding: 0.35em 0.75em;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-diverifikasi { background: #dbeafe; color: #1e40af; }
        .badge-diproses { background: #e0e7ff; color: #3730a3; }
        .badge-selesai { background: #d1fae5; color: #065f46; }
        .badge-ditolak { background: #fee2e2; color: #991b1b; }

        .badge-rendah { background: #d1fae5; color: #065f46; }
        .badge-sedang { background: #fef3c7; color: #92400e; }
        .badge-tinggi { background: #fee2e2; color: #991b1b; }

        /* Tables */
        .table-modern {
            margin-bottom: 0;
        }

        .table-modern th {
            background: #f9fafb;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }

        .table-modern td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        /* Forms */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        /* User Avatar */
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Rating Stars */
        .rating-stars {
            color: #fbbf24;
        }

        .rating-stars .empty {
            color: #d1d5db;
        }

        /* Alert animation */
        .alert {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-school me-2"></i>
            <span>SFCS</span>
        </div>

        <nav class="sidebar-nav">
            <!-- Main Navigation -->
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            @if(auth()->user()->isSiswa() || auth()->user()->isGuru())
                <div class="nav-section-title">Pengaduan</div>
                <a href="{{ route('pengaduan.index') }}" class="sidebar-link {{ request()->routeIs('pengaduan.index') ? 'active' : '' }}">
                    <i class="fas fa-list"></i>
                    <span>Pengaduan Saya</span>
                </a>
                <a href="{{ route('pengaduan.create') }}" class="sidebar-link {{ request()->routeIs('pengaduan.create') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle"></i>
                    <span>Buat Pengaduan</span>
                </a>
            @endif

            @if(auth()->user()->isTeknisi())
                <div class="nav-section-title">Tugas</div>
                <a href="{{ route('teknisi.pengaduan.index') }}" class="sidebar-link {{ request()->routeIs('teknisi.pengaduan.*') ? 'active' : '' }}">
                    <i class="fas fa-tasks"></i>
                    <span>Pengaduan Ditugaskan</span>
                </a>
            @endif

            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <div class="nav-section-title">Manajemen</div>
                <a href="{{ route('admin.pengaduan.index') }}" class="sidebar-link {{ request()->routeIs('admin.pengaduan.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Kelola Pengaduan</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Kelola Pengguna</span>
                </a>
                <a href="{{ route('admin.kategoris.index') }}" class="sidebar-link {{ request()->routeIs('admin.kategoris.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i>
                    <span>Kategori</span>
                </a>
                <a href="{{ route('admin.gedungs.index') }}" class="sidebar-link {{ request()->routeIs('admin.gedungs.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span>Gedung & Ruangan</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
            @endif

            @if(auth()->user()->isKepsek())
                <div class="nav-section-title">Manajemen Evaluasi</div>
                <a href="{{ route('kepsek.reports') }}" class="sidebar-link {{ request()->routeIs('kepsek.reports') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan</span>
                </a>
            @endif

            @if(auth()->user()->isSuperAdmin())
                <div class="nav-section-title">Sistem</div>
                <a href="{{ route('superadmin.settings.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
                <a href="{{ route('superadmin.logs.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.logs.*') ? 'active' : '' }}">
                    <i class="fas fa-history"></i>
                    <span>Log Aktivitas</span>
                </a>
            @endif
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <nav aria-label="breadcrumb">
                    @yield('breadcrumb')
                </nav>
            </div>

            <div class="header-right">
                <!-- Notifications -->
                <div class="dropdown notification-badge">
                    <button class="btn btn-link text-secondary position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell fa-lg"></i>
                        @php
                            $unreadCount = auth()->user()->notifications()->where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="badge bg-danger rounded-pill">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" style="width: 320px; max-height: 400px; overflow-y: auto;">
                        <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                            Notifikasi
                            @if($unreadCount > 0)
                                <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm p-0">Tandai semua dibaca</button>
                                </form>
                            @endif
                        </h6>
                        @php
                            $notifications = auth()->user()->notifications()->latest()->take(5)->get();
                        @endphp
                        @forelse($notifications as $notification)
                            <a href="{{ route('notifications.index') }}" class="dropdown-item py-2 {{ $notification->is_read ? '' : 'bg-light' }}">
                                <div class="d-flex">
                                    <div class="me-2">
                                        <i class="fas {{ $notification->jenis_icon }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small fw-semibold">{{ $notification->judul }}</div>
                                        <div class="small text-muted text-truncate" style="max-width: 240px;">{{ $notification->pesan }}</div>
                                        <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="dropdown-item text-center text-muted py-3">
                                Tidak ada notifikasi
                            </div>
                        @endforelse
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('notifications.index') }}" class="dropdown-item text-center small">
                            Lihat Semua Notifikasi
                        </a>
                    </div>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="d-none d-md-block text-start">
                            <div class="small fw-semibold text-dark">{{ auth()->user()->name }}</div>
                            <div class="small text-muted">{{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                        <i class="fas fa-chevron-down text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user me-2"></i> Profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>
