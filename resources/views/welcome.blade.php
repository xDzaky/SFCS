<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Pengaduan Fasilitas Sekolah - Laporkan kerusakan fasilitas sekolah dengan mudah dan cepat">

    <title>{{ config('app.name', 'SFCS') }} - Sistem Pengaduan Fasilitas Sekolah</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --gradient-start: #0d6efd;
            --gradient-end: #0dcaf0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }

        .navbar-brand i {
            color: var(--gradient-end);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }

        .hero-buttons .btn {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            margin-right: 1rem;
            margin-bottom: 1rem;
        }

        .btn-light-custom {
            background: #fff;
            color: var(--primary-color);
            border: none;
        }

        .btn-light-custom:hover {
            background: #f8f9fa;
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-outline-light-custom {
            border: 2px solid #fff;
            color: #fff;
            background: transparent;
        }

        .btn-outline-light-custom:hover {
            background: #fff;
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .hero-image {
            position: relative;
            z-index: 1;
        }

        .hero-image img {
            max-width: 100%;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            margin-top: 3rem;
        }

        .stat-item {
            text-align: center;
            color: #fff;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Features Section */
        .features-section {
            padding: 6rem 0;
            background: #f8f9fa;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #333;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--secondary-color);
            margin-bottom: 3rem;
        }

        .feature-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }

        .feature-icon.blue {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: #fff;
        }

        .feature-icon.green {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: #fff;
        }

        .feature-icon.orange {
            background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
            color: #fff;
        }

        .feature-icon.purple {
            background: linear-gradient(135deg, #6f42c1 0%, #d63384 100%);
            color: #fff;
        }

        .feature-icon.red {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: #fff;
        }

        .feature-icon.teal {
            background: linear-gradient(135deg, #20c997 0%, #0dcaf0 100%);
            color: #fff;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #333;
        }

        .feature-desc {
            color: var(--secondary-color);
            font-size: 0.95rem;
        }

        /* How It Works Section */
        .how-it-works-section {
            padding: 6rem 0;
            background: #fff;
        }

        .step-card {
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .step-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #333;
        }

        .step-desc {
            color: var(--secondary-color);
        }

        .step-arrow {
            position: absolute;
            top: 50%;
            right: -15%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: var(--primary-color);
            opacity: 0.3;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            position: relative;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .cta-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }

        /* Footer */
        .footer {
            background: #1a1a2e;
            color: #fff;
            padding: 4rem 0 2rem;
        }

        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
        }

        .footer-desc {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1.5rem;
        }

        .footer-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #fff;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: #fff;
        }

        .footer-contact li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-contact i {
            margin-right: 1rem;
            color: var(--gradient-end);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            margin-top: 3rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            margin-right: 0.5rem;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-stats {
                gap: 2rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .step-arrow {
                display: none;
            }
        }

        @media (max-width: 767px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .hero-stats {
                flex-wrap: wrap;
                gap: 1.5rem;
            }

            .stat-item {
                flex: 0 0 45%;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        /* Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeIn 0.8s ease forwards;
        }

        .fade-in-delay-1 { animation-delay: 0.2s; }
        .fade-in-delay-2 { animation-delay: 0.4s; }
        .fade-in-delay-3 { animation-delay: 0.6s; }
        .fade-in-delay-4 { animation-delay: 0.8s; }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-school me-2"></i>SFCS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">Cara Kerja</a>
                    </li>
                    @if (Route::has('login'))
                        @auth
                            <li class="nav-item ms-lg-3">
                                <a class="btn btn-primary rounded-pill px-4" href="{{ url('/dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                        @else
                            <li class="nav-item ms-lg-2">
                                <a class="btn btn-outline-primary rounded-pill px-4" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                                </a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item ms-2">
                                    <a class="btn btn-primary rounded-pill px-4" href="{{ route('register') }}">
                                        <i class="fas fa-user-plus me-2"></i>Daftar
                                    </a>
                                </li>
                            @endif
                        @endauth
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title fade-in">
                        Sistem Pengaduan Fasilitas Sekolah
                    </h1>
                    <p class="hero-subtitle fade-in fade-in-delay-1">
                        Laporkan kerusakan fasilitas sekolah dengan mudah, cepat, dan transparan. 
                        Bersama kita wujudkan lingkungan belajar yang nyaman dan kondusif.
                    </p>
                    <div class="hero-buttons fade-in fade-in-delay-2">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn btn-light-custom btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>Ke Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-light-custom btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Mulai Sekarang
                                </a>
                                <a href="#how-it-works" class="btn btn-outline-light-custom btn-lg">
                                    <i class="fas fa-info-circle me-2"></i>Pelajari Lebih
                                </a>
                            @endauth
                        @endif
                    </div>
                    <div class="hero-stats fade-in fade-in-delay-3">
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Laporan Selesai</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">98%</div>
                            <div class="stat-label">Tingkat Kepuasan</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">&lt;24jam</div>
                            <div class="stat-label">Rata-rata Respon</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-image fade-in fade-in-delay-2">
                        <div class="text-center p-5">
                            <i class="fas fa-school fa-10x text-white opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Fitur Unggulan</h2>
                <p class="section-subtitle">Solusi lengkap untuk pengelolaan pengaduan fasilitas sekolah</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon blue">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h3 class="feature-title">Lapor Mudah</h3>
                        <p class="feature-desc">
                            Buat laporan pengaduan dengan mudah melalui form yang simpel. 
                            Lengkapi dengan foto dan deskripsi masalah.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon green">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3 class="feature-title">Tracking Real-time</h3>
                        <p class="feature-desc">
                            Pantau status pengaduan Anda secara real-time. 
                            Dapatkan notifikasi setiap ada update dari teknisi.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon orange">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3 class="feature-title">Tim Teknisi Handal</h3>
                        <p class="feature-desc">
                            Pengaduan akan ditangani oleh tim teknisi profesional 
                            yang siap menyelesaikan masalah dengan cepat.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon purple">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3 class="feature-title">Dashboard Analitik</h3>
                        <p class="feature-desc">
                            Lihat statistik dan laporan lengkap tentang pengaduan 
                            untuk membantu pengambilan keputusan.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon red">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="feature-title">Sistem Rating</h3>
                        <p class="feature-desc">
                            Berikan feedback dan rating setelah pengaduan selesai 
                            untuk meningkatkan kualitas layanan.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon teal">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h3 class="feature-title">Notifikasi Instan</h3>
                        <p class="feature-desc">
                            Dapatkan notifikasi instan untuk setiap update status 
                            pengaduan Anda melalui sistem.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section" id="how-it-works">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Cara Kerja</h2>
                <p class="section-subtitle">Proses pengaduan yang mudah dan transparan</p>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h3 class="step-title">Buat Laporan</h3>
                        <p class="step-desc">
                            Isi form pengaduan dengan detail masalah, lokasi, dan foto pendukung.
                        </p>
                        <i class="fas fa-arrow-right step-arrow d-none d-lg-block"></i>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h3 class="step-title">Verifikasi Admin</h3>
                        <p class="step-desc">
                            Admin memverifikasi laporan dan menugaskan teknisi yang sesuai.
                        </p>
                        <i class="fas fa-arrow-right step-arrow d-none d-lg-block"></i>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h3 class="step-title">Pengerjaan</h3>
                        <p class="step-desc">
                            Teknisi mengerjakan perbaikan dan mengupdate status secara berkala.
                        </p>
                        <i class="fas fa-arrow-right step-arrow d-none d-lg-block"></i>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h3 class="step-title">Selesai</h3>
                        <p class="step-desc">
                            Masalah teratasi! Berikan feedback untuk meningkatkan layanan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Siap Melaporkan Masalah?</h2>
                <p class="cta-subtitle">
                    Bergabunglah dengan ribuan pengguna yang telah merasakan kemudahan melaporkan kerusakan fasilitas sekolah.
                </p>
                @if (Route::has('login'))
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-light-custom btn-lg">
                            <i class="fas fa-rocket me-2"></i>Daftar Sekarang - Gratis!
                        </a>
                    @else
                        <a href="{{ route('pengaduan.create') }}" class="btn btn-light-custom btn-lg">
                            <i class="fas fa-plus me-2"></i>Buat Pengaduan Baru
                        </a>
                    @endguest
                @endif
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h3 class="footer-brand">
                        <i class="fas fa-school me-2"></i>SFCS
                    </h3>
                    <p class="footer-desc">
                        Sistem Pengaduan Fasilitas Sekolah - Solusi digital untuk pengelolaan 
                        dan pemeliharaan fasilitas sekolah yang lebih baik.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h4 class="footer-title">Link Cepat</h4>
                    <ul class="footer-links">
                        <li><a href="#features">Fitur</a></li>
                        <li><a href="#how-it-works">Cara Kerja</a></li>
                        @if (Route::has('login'))
                            <li><a href="{{ route('login') }}">Masuk</a></li>
                            @if (Route::has('register'))
                                <li><a href="{{ route('register') }}">Daftar</a></li>
                            @endif
                        @endif
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h4 class="footer-title">Informasi</h4>
                    <ul class="footer-links">
                        <li><a href="#">Tentang Kami</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h4 class="footer-title">Kontak</h4>
                    <ul class="footer-links footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt mt-1"></i>
                            <span>Jl. Pendidikan No. 123, Kota Contoh, Indonesia</span>
                        </li>
                        <li>
                            <i class="fas fa-phone-alt"></i>
                            <span>(021) 1234-5678</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>info@sfcs-sekolah.id</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} SFCS - Sistem Pengaduan Fasilitas Sekolah. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Smooth Scroll -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 30px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            }
        });
    </script>
</body>
</html>
