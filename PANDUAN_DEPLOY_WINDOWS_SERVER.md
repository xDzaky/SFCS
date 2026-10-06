# 🖥️ Panduan Deployment SFCS di Server Windows Sekolah

Dokumen ini menjelaskan cara mendeploy aplikasi **SFCS (School Facilities & Complaint System)** pada **Server Windows** milik sekolah (Windows Server 2016 / 2019 / 2022 atau Windows 10/11 Pro).

---

## ✅ Apakah SFCS Bisa Dideploy di Server Windows?

**YA, BISA 100%!**  
Aplikasi ini dibangun menggunakan framework **Laravel (PHP)** dan database **MySQL / MariaDB**, yang sepenuhnya kompatibel untuk berjalan di sistem operasi Windows.

Ada 2 metode paling umum dan direkomendasikan untuk Server Windows Sekolah:
1. **Metode 1: Menggunakan Laragon / XAMPP (Paling Mudah, Cepat & Populer di Sekolah)**
2. **Metode 2: Menggunakan IIS (Internet Information Services — Web Server Resmi Bawaan Windows Server)**

---

## 🚀 Metode 1: Menggunakan Laragon / XAMPP (Sangat Direkomendasikan)

Laragon atau XAMPP adalah opsi paling stabil, minim konfigurasi rumit, dan sangat umum dipakai di laboratorium dan server sekolah.

### Langkah-langkah:

### 1. Kebutuhan Perangkat Lunak (Software Prerequisites)
Pastikan di server Windows sudah terinstal:
- **PHP versi 8.2 atau 8.3**
- **MySQL / MariaDB**
- **Composer** (untuk dependensi PHP)
- **Git for Windows** (opsional, untuk clone repo)

### 2. Aktifkan Ekstensi PHP di `php.ini`
Buka file `php.ini` di instalasi PHP Anda, pastikan ekstensi berikut tidak ada tanda titik koma (`;`) di depannya:
```ini
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=zip
extension=intl
```

### 3. Letakkan Folder Proyek
Pindahkan folder project ke server Windows:
- Jika menggunakan Laragon: `C:\laragon\www\sfcs`
- Jika menggunakan XAMPP: `C:\xampp\htdocs\sfcs`

### 4. Konfigurasi File `.env`
Salin file `.env.example` menjadi `.env` lalu sesuaikan konfigurasi koneksi server lokal sekolah:

```env
APP_NAME=SFCS
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.1.50   <-- Ganti dengan IP lokal Server Sekolah atau Domain Sekolah

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sfcs_db
DB_USERNAME=root
DB_PASSWORD=password_db_sekolah

# Integrasi AI Developer
AI_API_KEY=sfcs-ai-secret-2026
AI_SERVICE_URL=
```

### 5. Jalankan Perintah Setup Melalui Command Prompt (CMD / PowerShell)
Buka CMD / PowerShell as Administrator, arahkan ke folder proyek (`cd C:\laragon\www\sfcs`):

```bash
# 1. Install dependensi
composer install --optimize-autoloader --no-dev

# 2. Generate Application Key jika belum ada
php artisan key:generate

# 3. Jalankan migrasi database & data awal
php artisan migrate --seed --force

# 4. Buat shortcut folder storage ke public
php artisan storage:link

# 5. Optimalkan cache production
php artisan optimize
```

### 6. Arahkan Document Root Web Server ke Folder `public`
> ⚠️ **SANGAT PENTING:**  
> Web server (Apache/Nginx) harus diarahkan ke folder **`sfcs/public`**, BUKAN langsung ke root folder `sfcs`.  
> Ini adalah standar keamanan Laravel agar file konfigurasi `.env` dan kode aplikasi tidak bisa diintip orang lain dari browser.

---

## 🏢 Metode 2: Menggunakan IIS (Internet Information Services)

Jika sekolah menggunakan IIS resmi bawaan Windows Server:

1. **Install IIS Role** di Server Manager:
   - Centang **CGI** (di bagian Application Development Features).
2. **Install URL Rewrite Module**:
   - Download dan pasang **IIS URL Rewrite Module 2.1** dari situs resmi Microsoft.
3. **Install PHP for Windows**:
   - Pasang PHP 8.2 Non-Thread Safe (NTS) di `C:\PHP`.
   - Konfigurasi FastCGI di IIS Manager mengarah ke `C:\PHP\php-cgi.exe`.
4. **Buat File `web.config` di folder `public\`**  
   Laravel sudah menyediakan file `web.config` default di dalam folder `public/`:
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <configuration>
       <system.webServer>
           <rewrite>
               <rules>
                   <rule name="Imported Rule 1" stopProcessing="true">
                       <match url="^" ignoreCase="false" />
                       <conditions logicalGrouping="MatchAll">
                           <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                           <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                       </conditions>
                       <action type="Rewrite" url="index.php" />
                   </rule>
               </rules>
           </rewrite>
       </system.webServer>
   </configuration>
   ```
5. **Atur Permission Folder di Windows**:
   - Klik kanan folder `storage` dan `bootstrap/cache` ➔ **Properties** ➔ **Security** ➔ **Edit**.
   - Berikan izin **Modify** dan **Write** kepada pengguna `IUSR` dan `IIS_IUSRS`.

---

## 🌐 Cara Mengakses Aplikasi di Jaringan Sekolah

### 1. Akses Lokal di Dalam Sekolah (LAN / WiFi Sekolah)
- Cari tahu IP server sekolah (misal buka CMD lalu ketik `ipconfig`, contoh didapat: `192.168.1.100`).
- Siswa dan guru yang terhubung ke WiFi/LAN sekolah dapat langsung mengakses melalui browser HP atau laptop di alamat:
  👉 `http://192.168.1.100`

### 2. Akses Menggunakan Domain Sekolah (Contoh: `smkn1probolinggo.sch.id`)
Jika sekolah ingin sistem ini bisa diakses dari rumah / internet publik:
1. **Subdomain Sekolah**: Buat subdomain di DNS manajemen sekolah, misalnya:
   `sfcs.smkn1probolinggo.sch.id`
2. **Port Forwarding Router**: Di modem / router utama sekolah (MikroTik / Indihome / Astinet), lakukan *NAT / Port Forwarding* port 80 (HTTP) dan port 443 (HTTPS) ke IP lokal Server Windows (`192.168.1.100`).
3. Pasang sertifikat SSL gratis (Let's Encrypt / Certbot for Windows / Cloudflare) agar koneksi berstatus aman (`https://`).

---

## 💡 Rangkuman Keuntungan Deploy di Server Sekolah

1. **Kecepatan Akses Tinggi**: Akses data lokal di WiFi sekolah sangat instan tanpa kuota internet.
2. **Kapasitas Penyimpanan Besar**: Foto bukti kerusakan fasilitas dan foto dokumen peminjaman tidak dibatasi kuota hosting gratisan.
3. **Privasi Data Penuh**: Data akun guru, siswa, dan aset sekolah tersimpan di server milik sekolah sendiri.
