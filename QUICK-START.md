# 🏫 SFCS - Panduan Singkat untuk Sekolah

## Selamat Datang!

Terima kasih telah membeli **SFCS (School Facility Complaint System)**. Sistem ini akan membantu sekolah Anda mengelola pengaduan fasilitas dengan lebih efisien dan terorganisir.

---

## 📋 Apa yang Anda Dapat?

✅ Sistem manajemen pengaduan fasilitas lengkap  
✅ Multi-role access (Siswa, Guru, Teknisi, Admin, Kepala Sekolah)  
✅ Notifikasi real-time via browser  
✅ PWA (Progressive Web App) - bisa diinstall di smartphone  
✅ Dashboard monitoring dan laporan  
✅ Mobile-first design  
✅ Source code lengkap  

---

## 🚀 Quick Start

### 1️⃣ Baca Dokumentasi Instalasi
Buka file **[INSTALLATION.md](INSTALLATION.md)** untuk panduan lengkap instalasi.

**Pilih metode instalasi:**
- **Shared Hosting (cPanel)** → Cocok untuk sekolah dengan hosting shared
- **VPS/Dedicated Server** → Untuk sekolah dengan server sendiri

### 2️⃣ Persiapkan Requirements
- **PHP 8.2+** (cek di hosting Anda)
- **MySQL Database**
- **Email SMTP** (untuk notifikasi)

### 3️⃣ Install & Setup
Ikuti step-by-step di INSTALLATION.md:
1. Upload files ke hosting
2. Setup database
3. Configure environment
4. Run migrations
5. Login dengan akun default

### 4️⃣ Customize untuk Sekolah Anda
1. **Ganti password default** (PENTING!)
2. Update nama sekolah di settings
3. Tambah data gedung & ruangan
4. Tambah kategori pengaduan
5. Buat akun untuk staff & siswa

---

## 📂 Struktur File

```
SFCS/
├── 📄 INSTALLATION.md          ← Panduan instalasi lengkap (BACA INI DULU!)
├── 📄 README.md                ← Dokumentasi sistem & fitur
├── 📄 LICENSE.md               ← Informasi lisensi
├── 📄 CHANGELOG.md             ← Riwayat update
├── 📄 tech-spec-document.md    ← Spesifikasi teknis
├── 📄 database-setup.sql       ← Backup database schema
└── sfcs-app/                   ← Source code aplikasi
    ├── app/                    ← Controller, Model, Middleware
    ├── config/                 ← File konfigurasi
    ├── database/               ← Migration & Seeder
    ├── public/                 ← Public folder (document root)
    ├── resources/              ← Views (Blade templates)
    ├── routes/                 ← Route definitions
    └── storage/                ← File upload & logs
```

---

## 👥 Role & Akses Default

Setelah instalasi, login dengan akun berikut (kemudian **GANTI PASSWORD**):

| Role | Email | Password | Fungsi Utama |
|------|-------|----------|--------------|
| 🔐 Super Admin | superadmin@sfcs.sch.id | password | Full system access, kelola user |
| 👨‍💼 Admin | admin@sfcs.sch.id | password | Verifikasi & assign pengaduan |
| 🏫 Kepala Sekolah | kepsek@sfcs.sch.id | password | Monitoring & laporan |
| 🔧 Teknisi | teknisi1@sfcs.sch.id | password | Handle perbaikan fasilitas |
| 👩‍🏫 Guru | sri.wahyuni@sfcs.sch.id | password | Buat laporan pengaduan |
| 👨‍🎓 Siswa | andi@sfcs.sch.id | password | Buat laporan pengaduan |

⚠️ **SEGERA GANTI SEMUA PASSWORD SETELAH LOGIN PERTAMA!**

---

## 🎯 Langkah-Langkah Setelah Instalasi

### 1. Update Informasi Sekolah
Login sebagai Super Admin → Settings → Update:
- Nama sekolah
- Logo sekolah
- Alamat & kontak
- Email notifikasi

### 2. Setup Master Data
**Gedung & Ruangan:**
- Menu: Gedung & Ruangan
- Tambah semua gedung di sekolah
- Tambah ruangan untuk setiap gedung

**Kategori Pengaduan:**
- Menu: Kategori
- Buat kategori: AC & Pendingin, Listrik, Furniture, dll
- Tambah sub-kategori untuk detail spesifik

### 3. Buat Akun User
**Untuk Staff (Admin, Teknisi):**
- Menu: Kelola User → Tambah User
- Pilih role sesuai tugas

**Untuk Guru & Siswa:**
- Bisa import via CSV (bulk)
- Atau tambah manual satu per satu

### 4. Test Workflow
1. Login sebagai **Siswa** → Buat pengaduan
2. Login sebagai **Admin** → Verifikasi & assign ke teknisi
3. Login sebagai **Teknisi** → Update status & selesaikan
4. Login sebagai **Siswa** → Beri feedback & rating

### 5. Training Staff
- Beri akses demo untuk staff
- Jelaskan workflow pengaduan
- Pastikan semua paham cara menggunakan sistem

---

## 📊 Fitur Utama

### Untuk Siswa & Guru
- 📝 Buat pengaduan dengan foto
- 🔍 Track status pengaduan real-time
- 📱 Notifikasi update pengaduan
- ⭐ Beri feedback & rating setelah selesai

### Untuk Admin
- ✅ Verifikasi pengaduan masuk
- 👷 Assign ke teknisi yang tepat
- ❌ Tolak pengaduan tidak valid
- 📊 Monitor semua pengaduan

### Untuk Teknisi
- 🔧 Lihat tugas yang di-assign
- 📸 Update progress dengan foto
- ✔️ Tandai selesai setelah perbaikan
- 💬 Komunikasi via catatan

### Untuk Kepala Sekolah
- 📈 Dashboard statistik lengkap
- 📄 Laporan pengaduan per periode
- 📊 Analisis performance teknisi
- 📉 Identifikasi masalah berulang

---

## 🔧 Maintenance & Backup

### Backup Database Rutin
```bash
# Via cPanel → Backup Wizard
# Atau via command line:
mysqldump -u dbuser -p database_name > backup_$(date +%Y%m%d).sql
```

### Clear Cache (Jika ada masalah)
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Update Dependencies (Periodik)
```bash
composer update
npm update && npm run build
```

---

## 🆘 Troubleshooting

### Website Error 500
1. Check log: `storage/logs/laravel.log`
2. Clear cache semua
3. Check permissions folder `storage/`

### Login Tidak Bisa
1. Pastikan email/password benar
2. Check database connection
3. Clear browser cache

### Email Tidak Terkirim
1. Check SMTP settings di `.env`
2. Test dengan akun Gmail
3. Check log untuk error message

### Upload Foto Gagal
1. Check permissions folder `storage/`
2. Pastikan max upload di PHP settings cukup
3. Check disk space hosting

**Untuk masalah teknis lebih lanjut, lihat section Troubleshooting di [INSTALLATION.md](INSTALLATION.md)**

---

## 📞 Support & Kontak

### Technical Support
- **Email**: support@xdzaky.my.id
- **Response Time**: 1-2 hari kerja
- **Support Period**: 6 bulan dari pembelian

### Documentation
- [INSTALLATION.md](INSTALLATION.md) - Panduan instalasi
- [README.md](README.md) - Dokumentasi sistem
- [tech-spec-document.md](tech-spec-document.md) - Spesifikasi teknis
- [CHANGELOG.md](CHANGELOG.md) - Update history

### License & Renewal
- Lihat [LICENSE.md](LICENSE.md) untuk info lisensi
- Contact untuk perpanjangan support/updates

---

## ⚖️ Lisensi

Software ini dilisensikan untuk **satu (1) domain sekolah**.  
Untuk sekolah dengan multiple domain, hubungi untuk lisensi tambahan.

Baca detail di **[LICENSE.md](LICENSE.md)**

---

## ✅ Checklist Setelah Instalasi

Pastikan semua sudah dilakukan:

- [ ] Instalasi selesai, website bisa diakses
- [ ] Login berhasil dengan semua role
- [ ] Semua password default sudah diganti
- [ ] Data sekolah (nama, logo) sudah diupdate
- [ ] Gedung & ruangan sudah ditambahkan
- [ ] Kategori pengaduan sudah dikonfigurasi
- [ ] Email notifikasi sudah berfungsi
- [ ] Akun staff (admin, teknisi) sudah dibuat
- [ ] Sudah test buat pengaduan end-to-end
- [ ] Backup database pertama sudah dilakukan
- [ ] Staff sudah ditraining cara menggunakan

---

## 🎉 Selamat Menggunakan SFCS!

Sistem ini akan membantu sekolah Anda:
- ✅ Meningkatkan efisiensi penanganan pengaduan
- ✅ Mempercepat response time perbaikan
- ✅ Memberikan transparansi ke siswa & guru
- ✅ Menghasilkan data untuk improvement fasilitas

**Sukses untuk digitalisasi sekolah Anda! 🚀**

---

**© 2026 SFCS - School Facility Complaint System**  
**Developed by xDzaky**
