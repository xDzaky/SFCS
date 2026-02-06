<p align="center">
  <img src="https://img.shields.io/badge/SFCS-School%20Facility%20Complaint%20System-667eea?style=for-the-badge&logo=laravel&logoColor=white" alt="SFCS Banner"/>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel"/>
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP"/>
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL"/>
  <img src="https://img.shields.io/badge/License-Proprietary-red?style=flat-square" alt="License"/>
</p>

<p align="center">
  <strong>🏫 Sistem Manajemen Pengaduan Fasilitas Sekolah</strong>
  <br/>
  <em>Built with ❤️ by Dzaky</em>
</p>

---

## 📋 Deskripsi

**SFCS (School Facility Complaint System)** adalah aplikasi web modern untuk mengelola pengaduan dan perbaikan fasilitas di lingkungan sekolah. Sistem ini dirancang untuk mempermudah proses pelaporan kerusakan fasilitas oleh siswa/guru, penugasan teknisi oleh admin, hingga monitoring dan evaluasi oleh kepala sekolah.

### 🎯 Masalah yang Diselesaikan

- ❌ Proses pengaduan manual yang lambat dan tidak terstruktur
- ❌ Tidak ada tracking real-time status perbaikan
- ❌ Kesulitan dalam mengelola prioritas pengaduan
- ❌ Tidak ada data analytics untuk evaluasi performa

### ✅ Solusi yang Ditawarkan

- ✅ Pengaduan digital dengan upload foto & kamera langsung
- ✅ Real-time tracking status pengaduan
- ✅ Sistem prioritas otomatis (urgent, tinggi, sedang, rendah)
- ✅ Dashboard analytics untuk semua level user
- ✅ Notifikasi real-time + browser push notification
- ✅ Rating & feedback system untuk evaluasi kualitas
- ✅ Remember me login (30 hari tetap login)

---

## 🔄 Alur Sistem & Peran Setiap Role

### 📌 Alur Pengaduan (End-to-End)

```
Siswa/Guru          Admin               Teknisi           Kepsek/SuperAdmin
    │                  │                    │                     │
    ▼                  │                    │                     │
 1. Buat Pengaduan     │                    │                     │
    (foto + deskripsi) │                    │                     │
    │                  │                    │                     │
    │──── notifikasi ──▶                    │                     │
    │                  ▼                    │                     │
    │            2. Verifikasi              │                     │
    │               Pengaduan              │                     │
    │               (approve/reject)       │                     │
    │                  │                    │                     │
    │                  ▼                    │                     │
    │            3. Assign Teknisi ─────────▶                     │
    │                  │               4. Terima Tugas            │
    │                  │                    │                     │
    │◀─── notifikasi ──│                    ▼                     │
    │                  │              5. Kerjakan &               │
    │                  │                 Update Progress          │
    │                  │                    │                     │
    │                  │                    ▼                     │
    │                  │              6. Tandai Selesai           │
    │                  │                    │                     │
    │◀─── notifikasi ──│◀───────────────────│                     │
    │                  │                    │                     │
    ▼                  │                    │                     │
 7. Beri Rating        │                    │                     │
    & Feedback         │                    │                     │
    │                  │                    │          8. Lihat Laporan
    │                  │                    │             & Analytics
    ▼                  ▼                    ▼                     ▼
                    [ SELESAI ]
```

### 👨‍🎓 Siswa (siswa)

| Fitur | Deskripsi |
|-------|-----------|
| 📝 Buat Pengaduan | Buat laporan kerusakan fasilitas dengan form step-by-step |
| 📸 Ambil Foto | Langsung dari kamera HP/laptop atau upload dari galeri |
| 📋 Pengaduan Saya | Lihat daftar semua pengaduan yang pernah dibuat |
| 🔍 Lacak Pengaduan | Tracking real-time status pengaduan |
| ⭐ Beri Feedback | Rating & komentar setelah pengaduan selesai |
| 🔔 Notifikasi | Update status pengaduan via web & browser push notification |

**Akses:** `/dashboard` → `/pengaduan` → `/pengaduan/create`

### 👩‍🏫 Guru (guru)

| Fitur | Deskripsi |
|-------|-----------|
| 📝 Buat Pengaduan | Sama seperti siswa, bisa buat laporan kerusakan |
| 📋 Pengaduan Saya | Lihat & lacak semua pengaduan sendiri |
| ⭐ Beri Feedback | Rating setelah pengaduan selesai |
| 🔔 Notifikasi | Update status via notifikasi |

**Akses:** Sama dengan siswa

### 👨‍🔧 Teknisi (teknisi)

| Fitur | Deskripsi |
|-------|-----------|
| 📋 Daftar Tugas | Lihat pengaduan yang ditugaskan oleh admin |
| 🚨 Prioritas Tugas | Tugas diurutkan berdasarkan urgensi |
| 🔄 Update Status | Update progress pengerjaan (diproses → selesai) |
| ✅ Selesaikan Tugas | Tandai pengaduan selesai dengan catatan |
| 🔔 Notifikasi | Dapat notifikasi saat ditugaskan tugas baru |

**Akses:** `/dashboard` → `/teknisi/pengaduan`

### 👨‍💼 Admin (admin)

| Fitur | Deskripsi |
|-------|-----------|
| ✔️ Verifikasi Pengaduan | Approve atau reject pengaduan masuk |
| 👷 Assign Teknisi | Tugaskan teknisi untuk setiap pengaduan |
| 🔄 Update Status | Ubah status pengaduan secara manual |
| 📈 Dashboard Statistik | Statistik pengaduan, performa, tren |
| 👥 Kelola Pengguna | Tambah, edit, aktifkan/nonaktifkan user |
| 📁 Kelola Kategori | Atur kategori & sub-kategori pengaduan |
| 🏢 Kelola Gedung | Atur gedung & ruangan |
| 📊 Laporan | Export laporan pengaduan & performa |
| 🔔 Notifikasi | Dapat notifikasi saat ada pengaduan baru |

**Akses:** `/dashboard` → `/admin/pengaduan` → `/admin/users` → `/admin/kategoris` → `/admin/gedungs` → `/admin/reports`

### 👨‍🏫 Kepala Sekolah (kepsek)

| Fitur | Deskripsi |
|-------|-----------|
| 📊 Dashboard Analytics | Statistik komprehensif seluruh pengaduan |
| 📈 Performa Kategori | Lihat kategori mana yang paling banyak masalah |
| ⭐ Rating Teknisi | Monitoring performa teknisi berdasarkan rating |
| 🚨 Prioritas Tinggi | Alert untuk pengaduan urgent |
| 📈 Tren Bulanan | Grafik tren pengaduan per bulan |
| 📄 Laporan | Akses laporan untuk evaluasi |

**Akses:** `/dashboard` → `/kepsek/reports`

### 👑 Super Admin (superadmin)

| Fitur | Deskripsi |
|-------|-----------|
| ⚙️ Semua Fitur Admin | Akses penuh ke semua fitur admin |
| 🔧 Pengaturan Sistem | Konfigurasi nama sekolah, logo, maintenance mode |
| 📝 Activity Log | Lihat seluruh log aktivitas user |
| 🔐 Manajemen Role | Akses penuh ke manajemen pengguna |

**Akses:** `/dashboard` → semua route admin + `/superadmin/settings` → `/superadmin/logs`

---

## ✨ Fitur Utama

### 🔔 Sistem Notifikasi
- Notifikasi real-time di dalam aplikasi
- **Browser Push Notification** (muncul di layar laptop/PC/HP)
- Auto-check setiap 30 detik untuk notifikasi baru
- Auto mark as read saat diklik
- Filter berdasarkan status & tipe
- Link otomatis disesuaikan berdasarkan role user

### 📸 Kamera & Upload Foto
- Ambil foto langsung dari kamera belakang (mobile)
- Upload dari galeri
- Preview foto sebelum submit
- Multiple foto per pengaduan

### 🔐 Keamanan & Session
- Remember me login (30 hari session)
- Role-based access control (6 role)
- Auto-redirect berdasarkan role
- CSRF & XSS protection

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel 12.x (PHP 8.2+) |
| **Frontend** | Blade Templates + Bootstrap 5.3 |
| **Database** | MySQL 8.0 / MariaDB |
| **Authentication** | Laravel Breeze |
| **Icons** | Font Awesome 6 |
| **Build Tool** | Vite |
| **Camera API** | MediaDevices.getUserMedia() |
| **Push Notification** | Web Notifications API |

---

## 📱 Responsive Design

Aplikasi ini didesain dengan pendekatan **mobile-first** sehingga dapat diakses dengan nyaman melalui:

- 📱 Smartphone (Android/iOS)
- 📟 Tablet
- 💻 Desktop/Laptop

---

## 🏗️ Arsitektur Sistem

```
┌──────────────────────────────────────────────────────────────────┐
│                        SFCS Architecture                         │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────────┐   │
│  │  Siswa   │  │   Guru   │  │ Teknisi  │  │ Admin/Kepsek   │   │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └───────┬────────┘   │
│       │              │             │                │            │
│       └──────────────┴──────┬──────┴────────────────┘            │
│                             │                                    │
│                     ┌───────▼───────┐                            │
│                     │   Frontend    │                            │
│                     │  (Bootstrap)  │                            │
│                     └───────┬───────┘                            │
│                             │                                    │
│                     ┌───────▼───────┐                            │
│                     │    Laravel    │                            │
│                     │   (Backend)   │                            │
│                     └───────┬───────┘                            │
│                             │                                    │
│           ┌─────────────────┼─────────────────┐                  │
│           │                 │                 │                  │
│     ┌─────▼─────┐   ┌──────▼──────┐   ┌──────▼──────┐           │
│     │  MySQL    │   │   Storage   │   │   Push      │           │
│     │ Database  │   │   (Files)   │   │ Notification│           │
│     └───────────┘   └─────────────┘   └─────────────┘           │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 📊 Database Schema

```
Users ─────────────┬─── Pengaduans ─────┬─── HistoryPengaduans
                   │                     │
                   │                     ├─── PengaduanPhotos
                   │                     │
                   │                     └─── Feedbacks
                   │
                   ├─── Notifications
                   │
                   └─── Logs

Kategoris ─────────┬─── SubKategoris
                   │
                   └─── Pengaduans

Gedungs ───────────┬─── Ruangans ───────── Pengaduans

Settings (System Configuration)
```

---

## 🚀 Deployment

### Production Server
- **Hosting:** NusantaraHost (Shared Hosting)
- **URL:** [sfcs.xdzaky.my.id](https://sfcs.xdzaky.my.id)
- **PHP:** 8.2+
- **Database:** MySQL

### Deploy ke Production

```bash
# Di server cPanel terminal
cd ~/sfcs-app
git pull origin main
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔒 Security Features

- ✅ Role-based Access Control (RBAC) - 6 role
- ✅ CSRF Protection
- ✅ XSS Prevention (Blade escaping)
- ✅ SQL Injection Prevention (Eloquent ORM)
- ✅ Password Hashing (Bcrypt)
- ✅ Session Security (30 hari remember me)
- ✅ Activity Logging
- ✅ Input Validation & Sanitization
- ✅ Route-level middleware protection
- ✅ Owner-only access untuk pengaduan

---

## 👨‍💻 Developer

<table>
  <tr>
    <td align="center">
      <strong>Dzaky</strong>
      <br/>
      <em>Full-Stack Developer</em>
      <br/>
      <sub>SMK Negeri 1 Probolinggo</sub>
    </td>
  </tr>
</table>

---

## 📜 License

```
Copyright (c) 2026 Dzaky. All Rights Reserved.

This project is proprietary software. Unauthorized copying, modification,
distribution, or use of this software, via any medium, is strictly prohibited
without explicit written permission from the author.
```

---

<p align="center">
  <strong>© 2026 Dzaky. All Rights Reserved.</strong>
  <br/>
  <em>SFCS v1.1.0 - School Facility Complaint System</em>
</p>
