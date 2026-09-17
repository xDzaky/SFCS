<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SFCS">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

    <title>{{ config('app.name', 'SFCS') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Fancybox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"/>

    <!-- Custom CSS -->
    @php
        $primaryColor = \App\Models\Setting::getValue('theme_primary_color', '#4f46e5');
        $secondaryColor = \App\Models\Setting::getValue('theme_secondary_color', '#6c757d');
        $schoolLogo = \App\Models\Setting::getValue('school_logo');
        $schoolName = \App\Models\Setting::getValue('school_name', 'SFCS');
        $appName = \App\Models\Setting::getValue('app_name', 'SFCS');
    @endphp
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --primary-hover: {{ $primaryColor }}dd;
            --secondary-color: {{ $secondaryColor }};
            --sidebar-width: 260px;
            --sidebar-collapsed: 70px;
            --header-height: 60px;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f3f4f6;
            overflow-x: hidden;
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
            z-index: 1040;
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
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .sidebar.collapsed + .main-content {
            margin-left: var(--sidebar-collapsed);
            width: calc(100% - var(--sidebar-collapsed));
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
            z-index: 1020;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
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

        /* ── Tables ───────────────────────────────────────────── */
        .table-modern {
            margin-bottom: 0;
            font-size: .875rem;
        }
        .table-modern th {
            background: #f9fafb;
            font-weight: 700;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
            padding: .65rem .75rem;
        }
        .table-modern td {
            vertical-align: middle;
            padding: .75rem .75rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .table-modern tbody tr { transition: background .12s; }
        .table-modern tbody tr:hover { background: #f8fafc; }
        .table-modern tbody tr:last-child td { border-bottom: none; }

        /* Scrollable table wrapper always */
        .table-responsive { border-radius: 0 0 .75rem .75rem; }

        /* ── Page content responsive padding ─────────────────── */
        .page-content {
            padding: 1.25rem 1.5rem;
        }
        @media (min-width: 1200px) {
            .page-content { padding: 1.5rem 2rem; }
        }
        @media (max-width: 767.98px) {
            .page-content { padding: .875rem 1rem; }
            .page-title { font-size: 1.2rem; }
        }
        @media (max-width: 575.98px) {
            .page-content { padding: .75rem .75rem; }
        }

        /* ── Cards ────────────────────────────────────────────── */
        .card {
            border: none;
            border-radius: .875rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.07), 0 2px 8px rgba(0,0,0,.04);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #f3f4f6;
            padding: .875rem 1.25rem;
            font-weight: 600;
            border-radius: .875rem .875rem 0 0 !important;
        }
        .card-footer {
            background: #fafafa;
            border-top: 1px solid #f3f4f6;
            border-radius: 0 0 .875rem .875rem !important;
        }

        /* ── Buttons ──────────────────────────────────────────── */
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        .btn { transition: all .15s; }
        .btn:active { transform: scale(.97); }
        /* Touch-friendly min height on mobile */
        @media (max-width: 767.98px) {
            .btn { min-height: 38px; }
            .btn-sm { min-height: 32px; }
        }

        /* ── Forms ────────────────────────────────────────────── */
        .form-control, .form-select {
            border-color: #e5e7eb;
            border-radius: .5rem;
            font-size: .9rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79,70,229,.12);
        }
        .form-label {
            font-weight: 600;
            font-size: .82rem;
            margin-bottom: .35rem;
            color: #374151;
        }
        .input-group-text { border-color: #e5e7eb; }
        @media (max-width: 767.98px) {
            .form-control, .form-select { font-size: .95rem; min-height: 42px; }
        }

        /* ── Page Header ──────────────────────────────────────── */
        .page-header {
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #f3f4f6;
        }
        @media (max-width: 575.98px) {
            .page-header { margin-bottom: .875rem; padding-bottom: .875rem; }
        }

        /* ── Badge helpers ────────────────────────────────────── */
        .badge { font-weight: 600; }

        /* ── Pagination ───────────────────────────────────────── */
        .pagination .page-link {
            border-radius: .5rem;
            margin: 0 2px;
            border-color: #e5e7eb;
            color: #374151;
            font-size: .83rem;
        }
        .pagination .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* ── Modal ────────────────────────────────────────────── */
        .modal-content { border: none; border-radius: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
        .modal-header { border-bottom: 1px solid #f3f4f6; padding: 1.1rem 1.5rem; }
        .modal-footer { border-top: 1px solid #f3f4f6; padding: .875rem 1.5rem; }
        @media (max-width: 575.98px) {
            .modal-dialog { margin: .5rem; }
            .modal-content { border-radius: .875rem; }
        }

        /* ── Alert ────────────────────────────────────────────── */
        .alert { border-radius: .75rem; border: none; }

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
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1035;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Map and widgets isolation to prevent overlapping sticky headers */
        .sfcs-map-frame,
        .leaflet-container {
            isolation: isolate;
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

    <style>
        /* ── Notification System ──────────────────────────────── */
        .notif-bell-btn {
            position: relative;
            color: #6b7280 !important;
            padding: .45rem .55rem;
            border-radius: .5rem;
            transition: background .15s, color .15s;
        }
        .notif-bell-btn:hover { background: #f3f4f6; color: #374151 !important; }
        .notif-bell-btn.has-unread { color: var(--primary-color) !important; }

        /* Badge always in DOM, toggled via JS */
        #notifBadge {
            position: absolute;
            top: -4px; right: -4px;
            font-size: .6rem;
            padding: .22em .42em;
            min-width: 18px;
            line-height: 1.1;
            pointer-events: none;
        }

        /* Dropdown menu */
        .notif-menu {
            padding: 0 !important;
            border: 1px solid #e5e7eb;
            border-radius: .875rem;
            box-shadow: 0 10px 40px rgba(0,0,0,.12);
            overflow: hidden;
        }
        .notif-menu-header {
            background: #fff;
            padding: .75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Individual notification row */
        .notif-item {
            display: flex;
            align-items: flex-start;
            padding: .75rem 1rem;
            cursor: pointer;
            transition: background .12s;
            border-bottom: 1px solid #f3f4f6;
            text-decoration: none;
            color: inherit;
            border-left: 3px solid transparent;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item.unread {
            background: #eef2ff;
            border-left-color: var(--primary-color);
        }
        .notif-item.read { background: #fff; }
        .notif-item:hover { background: #f5f6ff; }
        .notif-item.unread:hover { background: #e0e7ff; }

        .notif-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #e0e7ff;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
            margin-right: .75rem;
        }
        .notif-icon.icon-green  { background: #dcfce7; color: #16a34a; }
        .notif-icon.icon-blue   { background: #dbeafe; color: #2563eb; }
        .notif-icon.icon-yellow { background: #fef9c3; color: #ca8a04; }
        .notif-icon.icon-red    { background: #fee2e2; color: #dc2626; }
        .notif-icon.icon-purple { background: #f3e8ff; color: #9333ea; }
        .notif-icon.icon-gray   { background: #f3f4f6; color: #6b7280; }

        .notif-title { font-size: .83rem; font-weight: 600; color: #111827; line-height: 1.35; }
        .notif-item.read .notif-title { font-weight: 400; color: #4b5563; }
        .notif-msg { font-size: .77rem; color: #6b7280; margin-top: 1px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .notif-time { font-size: .72rem; color: #9ca3af; margin-top: 3px; }

        .notif-dot {
            width: 7px; height: 7px; border-radius: 50%; background: var(--primary-color);
            flex-shrink: 0; margin-top: 5px; margin-left: 4px;
        }

        .notif-empty { text-align: center; padding: 2rem 1rem; color: #9ca3af; }

        /* ── In-page Toast ────────────────────────────────────── */
        #notif-toast-container {
            position: fixed;
            bottom: 80px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            max-width: 340px;
            pointer-events: none;
        }
        .notif-toast {
            background: #fff;
            border-left: 4px solid var(--primary-color);
            border-radius: .75rem;
            box-shadow: 0 8px 30px rgba(0,0,0,.15);
            padding: .85rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            pointer-events: auto;
            cursor: pointer;
            animation: toastSlideIn .3s ease;
            will-change: transform, opacity;
        }
        .notif-toast.out { animation: toastSlideOut .3s ease forwards; }
        @keyframes toastSlideIn {
            from { transform: translateX(120%); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastSlideOut {
            from { transform: translateX(0); opacity: 1; }
            to   { transform: translateX(120%); opacity: 0; }
        }
        .notif-toast-icon { width: 34px; height: 34px; border-radius: 50%; background: #eef2ff; color: var(--primary-color); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .notif-toast-title { font-size: .82rem; font-weight: 600; color: #111827; }
        .notif-toast-msg { font-size: .76rem; color: #6b7280; margin-top: 1px; line-height: 1.4; }
        .notif-toast-close { margin-left: auto; color: #9ca3af; background: none; border: none; padding: 0; cursor: pointer; font-size: .9rem; }

    </style>

    {{-- ── Global Badge: Prioritas & Status — high-contrast, visible di semua halaman ── --}}
    <style>
        /* ── Prioritas Badges ─────────────────────────────────────── */
        .badge-prioritas-rendah  { background-color: #16a34a !important; color: #ffffff !important; }
        .badge-prioritas-sedang  { background-color: #d97706 !important; color: #ffffff !important; }
        .badge-prioritas-tinggi  { background-color: #ea580c !important; color: #ffffff !important; }
        .badge-prioritas-urgent  { background-color: #dc2626 !important; color: #ffffff !important; }

        /* ── Status Badges ────────────────────────────────────────── */
        .badge-status-pending      { background-color: #6b7280 !important; color: #ffffff !important; }
        .badge-status-diverifikasi { background-color: #0284c7 !important; color: #ffffff !important; }
        .badge-status-diproses     { background-color: #d97706 !important; color: #ffffff !important; }
        .badge-status-selesai      { background-color: #16a34a !important; color: #ffffff !important; }
        .badge-status-ditolak      { background-color: #dc2626 !important; color: #ffffff !important; }

        /* Null/empty/unknown prioritas */
        .badge-prioritas-unknown { background-color: #9ca3af !important; color: #ffffff !important; }

        /* ── Status Pinjaman Badges ────────────────────────────────── */
        .badge-pinjaman-pending   { background-color: #d97706 !important; color: #ffffff !important; }
        .badge-pinjaman-disetujui { background-color: #0284c7 !important; color: #ffffff !important; }
        .badge-pinjaman-dipinjam  { background-color: #7c3aed !important; color: #ffffff !important; }
        .badge-pinjaman-terlambat { background-color: #dc2626 !important; color: #ffffff !important; }
        .badge-pinjaman-selesai   { background-color: #16a34a !important; color: #ffffff !important; }
        .badge-pinjaman-ditolak   { background-color: #6b7280 !important; color: #ffffff !important; }
    </style>

    @stack('styles')</head>
<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            @if($schoolLogo)
                <img src="{{ Storage::url($schoolLogo) }}" alt="Logo" style="height: 36px; margin-right: 10px;">
            @else
                <i class="fas fa-school me-2"></i>
            @endif
            <span>{{ $appName }}</span>
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
                <div class="nav-section-title">Peminjaman</div>
                <a href="{{ route('pinjaman.index') }}" class="sidebar-link {{ request()->routeIs('pinjaman.*') ? 'active' : '' }}">
                    <i class="fas fa-box-open"></i>
                    <span>Pinjam Barang</span>
                </a>
            @endif

            @if(auth()->user()->isTeknisi())
                <div class="nav-section-title">Tugas</div>
                <a href="{{ route('teknisi.pengaduan.index') }}" class="sidebar-link {{ request()->routeIs('teknisi.pengaduan.*') ? 'active' : '' }}">
                    <i class="fas fa-tasks"></i>
                    <span>Pengaduan Ditugaskan</span>
                </a>
                <a href="{{ route('teknisi.peta-digital.index') }}" class="sidebar-link {{ request()->routeIs('teknisi.peta-digital.*') ? 'active' : '' }}">
                    <i class="fas fa-map"></i>
                    <span>Peta Digital</span>
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
                <a href="{{ route('admin.overload-board') }}" class="sidebar-link {{ request()->routeIs('admin.overload-board') ? 'active' : '' }}">
                    <i class="fas fa-gauge-high"></i>
                    <span>Overload Board</span>
                </a>
                <a href="{{ route('admin.pinjaman.index') }}" class="sidebar-link {{ request()->routeIs('admin.pinjaman.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes-stacked"></i>
                    <span>Kelola Pinjaman</span>
                </a>
                <a href="{{ route('admin.barangs.index') }}" class="sidebar-link {{ request()->routeIs('admin.barangs.*') ? 'active' : '' }}">
                    <i class="fas fa-box-open"></i>
                    <span>Master Barang</span>
                </a>
                <a href="{{ route('admin.peta-digital') }}" class="sidebar-link {{ request()->routeIs('admin.peta-digital') ? 'active' : '' }}">
                    <i class="fas fa-map-location-dot"></i>
                    <span>Peta Digital</span>
                </a>
                <a href="{{ route('admin.denah.index') }}" class="sidebar-link {{ request()->routeIs('admin.denah.*') ? 'active' : '' }}">
                    <i class="fas fa-draw-polygon"></i>
                    <span>Kelola Denah</span>
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
                <a href="{{ route('admin.master-data.index') }}" class="sidebar-link {{ request()->routeIs('admin.master-data.*') ? 'active' : '' }}">
                    <i class="fas fa-database"></i>
                    <span>Master Data</span>
                </a>
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
                <div class="dropdown" id="notifDropdown">
                    <button class="btn btn-link notif-bell-btn" type="button" id="notifBellBtn"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-lg"></i>
                        <span class="badge bg-danger rounded-pill d-none" id="notifBadge">0</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end notif-menu"
                         id="notifMenu" style="width: 340px; max-height: 480px; overflow-y: auto;">
                        <!-- Header -->
                        <div class="notif-menu-header d-flex justify-content-between align-items-center">
                            <span class="fw-semibold" style="font-size:.9rem;">Notifikasi</span>
                            <button class="btn btn-link btn-sm p-0 text-primary"
                                    id="notifMarkAllBtn" style="display:none; font-size:.8rem;">
                                Tandai semua dibaca
                            </button>
                        </div>
                        <!-- Items rendered by JS -->
                        <div id="notifList">
                            <div class="notif-empty">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <!-- Footer -->
                        <div class="border-top text-center py-2">
                            <a href="{{ route('notifications.index') }}"
                               class="small text-primary text-decoration-none">
                                Lihat Semua Notifikasi
                            </a>
                        </div>
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

    <!-- AI Chat Bubble -->
    @include('components.ai-chat-bubble')

    <!-- In-page Toast Container -->
    <div id="notif-toast-container"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Fancybox JS -->
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
        Fancybox.bind("[data-fancybox]", {
            // Your custom options
        });
    </script>

    <!-- Custom JS -->
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mobileBreakpoint = 991.98;

        const isMobileViewport = () => window.innerWidth <= mobileBreakpoint;

        const syncSidebarLayout = () => {
            if (isMobileViewport()) {
                // On mobile/tablet, sidebar should behave as off-canvas.
                sidebar.classList.remove('collapsed');
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            } else {
                // On desktop, ensure overlay states are cleared.
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        };

        sidebarToggle.addEventListener('click', () => {
            if (isMobileViewport()) {
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

        window.addEventListener('resize', syncSidebarLayout);
        syncSidebarLayout();

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // ═══════════════════════════════════════════════════════
        //  NOTIFICATION SYSTEM
        // ═══════════════════════════════════════════════════════

        const CSRF      = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const bellBtn   = document.getElementById('notifBellBtn');
        const badge     = document.getElementById('notifBadge');
        const notifList = document.getElementById('notifList');
        const markAllBtn = document.getElementById('notifMarkAllBtn');

        let lastUnreadCount = 0;
        let dropdownLoaded  = false;
        let cachedData      = null;   // stores last successful dropdown payload

        // ── Icon map ──────────────────────────────────────────
        const iconMap = {
            pengaduan_created : { cls: 'fa-plus-circle',          bg: 'icon-green'  },
            status_changed    : { cls: 'fa-exchange-alt',          bg: 'icon-blue'   },
            assigned          : { cls: 'fa-user-tag',              bg: 'icon-yellow' },
            feedback_reminder : { cls: 'fa-star',                  bg: 'icon-purple' },
            overdue           : { cls: 'fa-exclamation-triangle',  bg: 'icon-red'    },
            pinjaman_created  : { cls: 'fa-box-open',              bg: 'icon-blue'   },
            pinjaman_status   : { cls: 'fa-arrow-right-arrow-left',bg: 'icon-blue'   },
            priority_adjusted : { cls: 'fa-sliders',               bg: 'icon-yellow' },
            rescheduled       : { cls: 'fa-calendar-days',         bg: 'icon-yellow' },
            overload_alert    : { cls: 'fa-gauge-high',            bg: 'icon-red'    },
        };
        function getIcon(jenis) {
            return iconMap[jenis] ?? { cls: 'fa-bell', bg: 'icon-gray' };
        }

        // ── Update badge ──────────────────────────────────────
        function updateBadge(count) {
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.classList.remove('d-none');
                bellBtn.classList.add('has-unread');
            } else {
                badge.classList.add('d-none');
                bellBtn.classList.remove('has-unread');
            }
        }

        // ── Render one notification row ───────────────────────
        function renderNotifItem(n) {
            const icon  = getIcon(n.jenis);
            const link  = n.link || null;
            const el    = document.createElement('a');
            el.href     = 'javascript:void(0)';
            el.className = `notif-item ${n.is_read ? 'read' : 'unread'}`;
            el.dataset.id   = n.id;
            el.dataset.link = link ?? '';

            el.innerHTML = `
                <div class="notif-icon ${icon.bg}">
                    <i class="fas ${icon.cls}"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div class="notif-title">${escHtml(n.judul)}</div>
                    <div class="notif-msg">${escHtml(n.pesan)}</div>
                    <div class="notif-time">${escHtml(n.time)}</div>
                </div>
                ${!n.is_read ? '<span class="notif-dot"></span>' : ''}
            `;

            el.addEventListener('click', () => handleNotifClick(n.id, link, el));
            return el;
        }

        // ── Render full dropdown contents ─────────────────────
        function renderNotifList(data) {
            notifList.innerHTML = '';
            if (!data.notifications || data.notifications.length === 0) {
                notifList.innerHTML = `
                    <div class="notif-empty">
                        <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                        Tidak ada notifikasi
                    </div>`;
                markAllBtn.style.display = 'none';
                return;
            }
            data.notifications.forEach(n => notifList.appendChild(renderNotifItem(n)));
            markAllBtn.style.display = data.unread_count > 0 ? 'inline-block' : 'none';
        }

        // ── Fetch dropdown data ───────────────────────────────
        function fetchDropdown(showSpinner = false) {
            // Show cached data immediately to avoid empty flash
            if (cachedData && !showSpinner) {
                renderNotifList(cachedData);
            } else if (showSpinner) {
                notifList.innerHTML = '<div class="notif-empty"><i class="fas fa-spinner fa-spin"></i></div>';
            }

            fetch('/notifications/dropdown', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    cachedData       = data;
                    updateBadge(data.unread_count);
                    renderNotifList(data);
                    dropdownLoaded   = true;
                    lastUnreadCount  = data.unread_count;
                })
                .catch(() => {
                    if (!cachedData) {
                        notifList.innerHTML = '<div class="notif-empty text-danger">Gagal memuat notifikasi.</div>';
                    }
                });
        }

        // ── Fetch count only (background polling) ────────────
        function fetchCountAndNotify() {
            fetch('/notifications/unread-count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    const count = data.count ?? 0;

                    // New notification arrived while user is on page
                    if (count > lastUnreadCount) {
                        // Refresh dropdown cache (always, no spinner)
                        fetchDropdown(false);

                        // Fetch latest for toast/browser notif
                        fetch('/notifications/latest', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(notif => {
                                if (notif && notif.judul) {
                                    // Strip absolute URL to avoid cross-host 403
                                    if (notif.link) {
                                        notif.link = notif.link.replace(/^https?:\/\/[^/]+/, '');
                                    }
                                    showToast(notif);
                                    showBrowserNotif(notif);
                                }
                            });
                    } else {
                        updateBadge(count);
                    }

                    lastUnreadCount = count;
                })
                .catch(() => {});
        }

        // ── Click: mark as read, then navigate ────────────────
        function handleNotifClick(id, link, el) {
            // Optimistic UI
            el.classList.remove('unread');
            el.classList.add('read');
            const dot = el.querySelector('.notif-dot');
            if (dot) dot.remove();

            // Update cache
            if (cachedData) {
                const n = cachedData.notifications.find(x => x.id == id);
                if (n) n.is_read = true;
                cachedData.unread_count = Math.max(0, (cachedData.unread_count || 1) - 1);
                updateBadge(cachedData.unread_count);
            }

            // Tell server (fire-and-forget), then navigate
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            }).finally(() => {
                // link is already relative path (stripped by dropdown API)
                if (link) window.location.href = link;
            });
        }

        // ── Mark all as read (AJAX) ────────────────────────────
        markAllBtn.addEventListener('click', () => {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(r => r.json())
            .then(() => {
                document.querySelectorAll('.notif-item.unread').forEach(el => {
                    el.classList.remove('unread');
                    el.classList.add('read');
                    const dot = el.querySelector('.notif-dot');
                    if (dot) dot.remove();
                });
                // Update cache
                if (cachedData) {
                    cachedData.unread_count = 0;
                    cachedData.notifications.forEach(n => n.is_read = true);
                }
                updateBadge(0);
                markAllBtn.style.display = 'none';
                lastUnreadCount = 0;
            });
        });

        // ── Load dropdown when bell is opened ─────────────────
        const bsDropdown = document.getElementById('notifDropdown');
        bsDropdown.addEventListener('show.bs.dropdown', () => {
            // If we have cached data, show immediately (no spinner)
            // then silently refresh in background
            fetchDropdown(cachedData === null);
        });

        // ── In-page Toast ──────────────────────────────────────
        const toastContainer = document.getElementById('notif-toast-container');

        function showToast(notif) {
            const icon = getIcon(notif.jenis);
            const toast = document.createElement('div');
            toast.className = 'notif-toast';
            toast.innerHTML = `
                <div class="notif-toast-icon ${icon.bg}">
                    <i class="fas ${icon.cls}"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div class="notif-toast-title">${escHtml(notif.judul)}</div>
                    <div class="notif-toast-msg">${escHtml(notif.pesan ?? '')}</div>
                </div>
                <button class="notif-toast-close" title="Tutup">&times;</button>
            `;

            const link = notif.link;
            toast.addEventListener('click', e => {
                if (!e.target.classList.contains('notif-toast-close') && link) {
                    window.location.href = link;
                }
            });
            toast.querySelector('.notif-toast-close').addEventListener('click', () => dismissToast(toast));

            toastContainer.appendChild(toast);
            setTimeout(() => dismissToast(toast), 6000);
        }

        function dismissToast(toast) {
            toast.classList.add('out');
            setTimeout(() => toast.remove(), 300);
        }

        // ── Browser Push Notification ──────────────────────────
        function showBrowserNotif(notif) {
            if (Notification.permission === 'granted') {
                const n = new Notification('SFCS — ' + notif.judul, {
                    body   : notif.pesan ?? '',
                    icon   : '/images/icons/icon-192x192.png',
                    tag    : 'sfcs-notif-' + notif.id,
                    requireInteraction: false,
                });
                n.onclick = () => {
                    window.focus();
                    if (notif.link) window.location.href = notif.link;
                    n.close();
                };
            }
        }

        // ── Request browser notification permission ────────────
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // ── Bootstrap: load data immediately on page load ─────
        fetchDropdown(false);                               // pre-warm cache silently
        setInterval(fetchCountAndNotify, 15000);

        // ── Utility ───────────────────────────────────────────
        function escHtml(str) {
            return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    </script>

    <!-- PWA Service Worker Registration & Install Prompt -->
    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('ServiceWorker registered:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }

        // PWA Install Prompt
        let deferredPrompt;
        const installBanner = document.createElement('div');
        installBanner.id = 'pwa-install-banner';
        installBanner.innerHTML = `
            <div style="position: fixed; bottom: 20px; left: 20px; right: 20px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; padding: 16px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 9999; display: none; align-items: center; justify-content: space-between; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-mobile-alt" style="font-size: 24px;"></i>
                    <div>
                        <div style="font-weight: 600;">Install SFCS</div>
                        <div style="font-size: 12px; opacity: 0.9;">Akses lebih cepat dari home screen</div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button id="pwa-install-btn" style="background: white; color: #4f46e5; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer;">Install</button>
                    <button id="pwa-dismiss-btn" style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.3); padding: 8px 12px; border-radius: 8px; cursor: pointer;">Nanti</button>
                </div>
            </div>
        `;
        document.body.appendChild(installBanner);

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Check if user dismissed before
            if (!localStorage.getItem('pwa-install-dismissed')) {
                const banner = document.querySelector('#pwa-install-banner > div');
                if (banner) banner.style.display = 'flex';
            }
        });

        document.getElementById('pwa-install-btn')?.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log('PWA install outcome:', outcome);
                deferredPrompt = null;
            }
            document.querySelector('#pwa-install-banner > div').style.display = 'none';
        });

        document.getElementById('pwa-dismiss-btn')?.addEventListener('click', () => {
            document.querySelector('#pwa-install-banner > div').style.display = 'none';
            localStorage.setItem('pwa-install-dismissed', 'true');
        });

        // Hide banner if already installed
        window.addEventListener('appinstalled', () => {
            console.log('PWA installed');
            document.querySelector('#pwa-install-banner > div').style.display = 'none';
        });
    </script>

    @stack('scripts')

</body>
</html>
