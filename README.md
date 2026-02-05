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

- ✅ Pengaduan digital dengan upload foto dokumentasi
- ✅ Real-time tracking status pengaduan
- ✅ Sistem prioritas otomatis (urgent, tinggi, sedang, rendah)
- ✅ Dashboard analytics untuk semua level user
- ✅ Notifikasi real-time untuk setiap update
- ✅ Rating & feedback system untuk evaluasi kualitas

---

## ✨ Fitur Utama

### 👨‍🎓 Untuk Siswa & Guru
- 📝 Buat pengaduan dengan form yang mudah
- 📸 Upload foto dokumentasi kerusakan
- 🔍 Lacak status pengaduan real-time
- ⭐ Berikan rating & feedback setelah selesai
- 🔔 Notifikasi update status

### 👨‍🔧 Untuk Teknisi
- 📋 Lihat daftar tugas yang ditugaskan
- 🚨 Prioritas tugas berdasarkan urgensi
- 📊 Update progress pengerjaan
- ✅ Tandai tugas selesai dengan dokumentasi

### 👨‍💼 Untuk Admin
- ✔️ Verifikasi pengaduan masuk
- 👷 Assign teknisi untuk setiap pengaduan
- 📈 Dashboard statistik pengaduan
- 🏢 Kelola data gedung, ruangan, kategori
- 👥 Kelola user dan hak akses

### 👨‍🏫 Untuk Kepala Sekolah
- 📊 Dashboard analytics komprehensif
- 📈 Laporan performa per kategori
- ⭐ Monitoring rating teknisi
- 🚨 Alert isu prioritas tinggi
- 📄 Export laporan berkala

### 👑 Untuk Super Admin
- ⚙️ Pengaturan sistem lengkap
- 📝 Activity logging
- 🔐 Manajemen role & permission
- 💾 Backup & restore data

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

---

## 📱 Responsive Design

Aplikasi ini didesain dengan pendekatan **mobile-first** sehingga dapat diakses dengan nyaman melalui:

- 📱 Smartphone (Android/iOS)
- 📟 Tablet
- 💻 Desktop/Laptop

---

## 📸 Screenshots

<details>
<summary>🖼️ Klik untuk melihat screenshots</summary>

### Dashboard Siswa
![Dashboard Siswa](screenshots/dashboard-siswa.png)

### Dashboard Admin
![Dashboard Admin](screenshots/dashboard-admin.png)

### Form Pengaduan
![Form Pengaduan](screenshots/form-pengaduan.png)

### Detail Pengaduan
![Detail Pengaduan](screenshots/detail-pengaduan.png)

</details>

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────────┐
│                        SFCS Architecture                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────────┐ │
│  │  Siswa   │  │   Guru   │  │ Teknisi  │  │ Admin/Kepsek/SA  │ │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────────┬─────────┘ │
│       │             │             │                  │          │
│       └─────────────┴──────┬──────┴──────────────────┘          │
│                            │                                     │
│                    ┌───────▼───────┐                            │
│                    │   Frontend    │                            │
│                    │  (Bootstrap)  │                            │
│                    └───────┬───────┘                            │
│                            │                                     │
│                    ┌───────▼───────┐                            │
│                    │    Laravel    │                            │
│                    │   (Backend)   │                            │
│                    └───────┬───────┘                            │
│                            │                                     │
│          ┌─────────────────┼─────────────────┐                  │
│          │                 │                 │                  │
│    ┌─────▼─────┐    ┌──────▼──────┐   ┌─────▼─────┐            │
│    │  MySQL    │    │   Storage   │   │  Mailer   │            │
│    │ Database  │    │   (Files)   │   │  (SMTP)   │            │
│    └───────────┘    └─────────────┘   └───────────┘            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
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

## 🔒 Security Features

- ✅ Role-based Access Control (RBAC)
- ✅ CSRF Protection
- ✅ XSS Prevention
- ✅ SQL Injection Prevention (Eloquent ORM)
- ✅ Password Hashing (Bcrypt)
- ✅ Session Security
- ✅ Activity Logging
- ✅ Input Validation & Sanitization

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

For licensing inquiries, please contact the author.
```

**⚠️ PENTING**: Project ini dilindungi hak cipta. Penggunaan, penyalinan, atau modifikasi tanpa izin tertulis dari penulis adalah **DILARANG**.

---

## 📞 Contact

Untuk pertanyaan, kolaborasi, atau perizinan penggunaan:

- 📧 Email: [Your Email]
- 💼 LinkedIn: [Your LinkedIn]
- 🌐 Portfolio: [Your Portfolio URL]

---

<p align="center">
  <strong>© 2026 Dzaky. All Rights Reserved.</strong>
  <br/>
  <em>SFCS - School Facility Complaint System</em>
</p>
