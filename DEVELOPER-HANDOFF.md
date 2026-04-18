# SFCS Developer Handoff

Fokus dokumen ini:
- cara install project di Windows
- dependency yang wajib ada
- setup `.env`
- command yang sering dipakai
- struktur code yang penting
- hal-hal yang perlu diketahui sebelum modifikasi fitur

Project utama untuk dokumentasi ini adalah root repo `sfcs-app`.

## 1. Stack Project

- Backend: Laravel 12
- PHP: 8.2+
- Database: MySQL / MariaDB
- Frontend build: Vite
- CSS/JS: Tailwind, Alpine, vanilla JS
- Auth: Laravel Breeze-style auth + middleware role custom

## 2. Rekomendasi Environment Windows

Yang paling aman untuk dev di Windows:

1. `Git`
2. `PHP 8.2+`
3. `Composer`
4. `Node.js 18+`
5. `MySQL` atau `MariaDB`
6. `npm`

Saran praktis:
- Paling nyaman pakai `Laragon`, karena PHP, MySQL, dan virtual host biasanya lebih gampang.
- Alternatif: `XAMPP` + install Composer + install Node.js manual.
- Terminal yang nyaman: `PowerShell`, `Windows Terminal`, atau `Git Bash`.

## 3. Software yang Harus Di-install di Windows

### Wajib

- Git: https://git-scm.com/download/win
- Composer: https://getcomposer.org/download/
- Node.js LTS: https://nodejs.org/
- Laragon: https://laragon.org/ atau XAMPP: https://www.apachefriends.org/

### PHP Extensions yang harus aktif

Kalau pakai Laragon/XAMPP, pastikan extension ini aktif:

- `pdo_mysql`
- `mbstring`
- `openssl`
- `fileinfo`
- `ctype`
- `json`
- `tokenizer`
- `xml`
- `curl`
- `zip`
- `bcmath`
- `gd`
- `intl`

Kalau ada error aneh saat `composer install` atau saat image/file upload, biasanya masalahnya ada di extension PHP yang belum aktif.

## 4. Clone dan Install Project

Masuk ke folder kerja, lalu:

```bash
git clone https://github.com/xDzaky/SFCS.git
cd SFCS/sfcs-app
composer install
npm install
```

Kalau dependency frontend/backend sudah selesai:

```bash
copy .env.example .env
php artisan key:generate
```

Kalau pakai Git Bash:

```bash
cp .env.example .env
php artisan key:generate
```

## 5. Setup Database

Buat database dulu di MySQL/MariaDB.

Contoh:
- database: `sfcs_db`
- user: `sfcs_user`
- password: `strong_password`

Lalu isi `.env`.

## 6. Isi `.env` Minimal untuk Local Development

Contoh setup yang aman untuk local Windows:

```env
APP_NAME="SFCS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sfcs_db
DB_USERNAME=sfcs_user
DB_PASSWORD=strong_password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@sfcs.local"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

Catatan penting:
- `APP_URL` sebaiknya konsisten. Kalau local dibuka lewat `http://127.0.0.1:8000`, isi itu juga di `.env`.
- Jangan campur `localhost` dan `127.0.0.1` sembarangan kalau ada fitur file/viewer yang sensitif ke origin.
- Untuk setup awal, `QUEUE_CONNECTION=sync` paling aman. Kalau nanti mau queue async, baru pindah ke worker.
- `MAIL_MAILER=log` cukup untuk local supaya email tidak benar-benar dikirim.

## 7. Migrasi dan Seeder

Setelah `.env` siap:

```bash
php artisan migrate
php artisan db:seed
php artisan app:doctor
```

`app:doctor` penting karena command ini dipakai untuk cek:
- koneksi database
- tabel inti sudah ada atau belum
- beberapa kolom penting
- akun default hasil seed

Kalau command ini gagal, jangan lanjut debug fitur dulu. Bereskan environment terlebih dahulu.

## 8. Jalankan Project

### Opsi paling gampang

Jalankan backend:

```bash
php artisan serve
```

Lalu di terminal lain jalankan Vite:

```bash
npm run dev
```

### Opsi praktis dari Composer

Project ini sudah punya script:

```bash
composer run dev
```

Script ini menjalankan:
- Laravel server
- queue listener
- log watcher
- Vite

Kalau di Windows ada issue dengan `concurrently` atau multi-process, balik saja ke cara manual 2 terminal seperti di atas.

## 9. Login Default Setelah Seeder

Seeder user default ada di [`sfcs-app/database/seeders/UserSeeder.php`](./database/seeders/UserSeeder.php).

Semua akun bawaan seeder menggunakan password awal:

```text
password
```

Catatan penting:
- login pertama tidak lagi dipaksa mengganti password default
- jika ingin mengganti password, lakukan manual dari menu profil / setting akun
- kalau login gagal, cek ulang seeder atau reset password via database

### Daftar akun default per role

| Role | Email | Catatan |
|------|-------|---------|
| Superadmin | `superadmin@sfcs.sch.id` | akses penuh sistem |
| Admin | `admin@sfcs.sch.id` | admin sarpras / operasional |
| Kepsek | `kepsek@sfcs.sch.id` | monitoring dan laporan |
| Teknisi | `teknisi1@sfcs.sch.id` | akun teknisi contoh 1 |
| Teknisi | `teknisi2@sfcs.sch.id` | akun teknisi contoh 2 |
| Teknisi | `teknisi3@sfcs.sch.id` | akun teknisi contoh 3 |
| Guru | `sri.wahyuni@sfcs.sch.id` | akun guru contoh 1 |
| Guru | `joko.susilo@sfcs.sch.id` | akun guru contoh 2 |
| Siswa | `andi@sfcs.sch.id` | akun siswa contoh 1 |
| Siswa | `bela@sfcs.sch.id` | akun siswa contoh 2 |
| Siswa | `citra@sfcs.sch.id` | akun siswa contoh 3 |
| Siswa | `dani@sfcs.sch.id` | akun siswa contoh 4 |
| Siswa | `eka@sfcs.sch.id` | akun siswa contoh 5 |

### Akun yang dicek oleh `app:doctor`

Command `php artisan app:doctor` saat ini mengecek minimal akun berikut:

- `superadmin@sfcs.sch.id`
- `admin@sfcs.sch.id`
- `kepsek@sfcs.sch.id`

Kalau butuh memastikan teknisi, guru, dan siswa juga berhasil terseed:
- cek tabel `users`
- atau login manual memakai email di atas

## 10. Struktur Folder yang Perlu Dipahami

### Folder inti

- [`sfcs-app/app/Http/Controllers`](./app/Http/Controllers): controller Laravel
- [`sfcs-app/app/Models`](./app/Models): model Eloquent
- [`sfcs-app/app/Services`](./app/Services): business logic yang dipisah dari controller
- [`sfcs-app/resources/views`](./resources/views): Blade templates
- [`sfcs-app/routes/web.php`](./routes/web.php): route web utama
- [`sfcs-app/database/migrations`](./database/migrations): schema database
- [`sfcs-app/database/seeders`](./database/seeders): seed data awal

### Mapping cepat fitur

- Pengaduan:
  - `PengaduanController`
  - `Admin/AdminPengaduanController`
  - `Teknisi/TeknisiPengaduanController`

- Pinjaman:
  - `PinjamanController`
  - `Admin/AdminPinjamanController`
  - `Admin/BarangController`
  - `PinjamanAvailabilityService`

- Peta digital / denah:
  - `Admin/SchoolMapController`
  - `Teknisi/TeknisiSchoolMapController`
  - `SchoolMapService`

- User / role:
  - `Admin/UserController`
  - middleware role di `app/Http/Middleware`

## 11. Route dan Role

Role utama:
- `siswa`
- `guru`
- `teknisi`
- `admin`
- `kepsek`
- `superadmin`

Route dibagi per role di [`sfcs-app/routes/web.php`](./routes/web.php):
- route umum user login
- prefix `teknisi`
- prefix `admin`
- route khusus kepala sekolah / superadmin

Kalau nambah fitur baru, pastikan:
- route masuk group role yang tepat
- sidebar di [`resources/views/layouts/sfcs.blade.php`](./resources/views/layouts/sfcs.blade.php) ikut disesuaikan

### Menu utama per role

Ringkasan ini mengikuti route aktif dan sidebar yang sekarang dipakai aplikasi.

| Role | Menu / Fitur utama |
|------|---------------------|
| Siswa | Dashboard, Pengaduan Saya, Buat Pengaduan, Pinjam Barang, Profil, Notifikasi |
| Guru | Dashboard, Pengaduan Saya, Buat Pengaduan, Pinjam Barang, Profil, Notifikasi |
| Teknisi | Dashboard, Pengaduan Ditugaskan, Peta Digital, Profil, Notifikasi |
| Admin | Dashboard, Kelola Pengaduan, Kelola Pengguna, Kategori, Gedung & Ruangan, Laporan, Overload Board, Kelola Pinjaman, Master Barang, Peta Digital, Kelola Denah, Profil, Notifikasi |
| Kepsek | Dashboard, Laporan, Profil, Notifikasi |
| Superadmin | Semua akses admin + Master Data + Pengaturan + Log Aktivitas |

### Dashboard per role

Dashboard tidak sepenuhnya sama untuk semua role. `DashboardController` akan mengarahkan user ke tampilan yang sesuai dengan role masing-masing.

View dashboard utama yang sekarang ada:
- `resources/views/dashboard/siswa.blade.php`
- `resources/views/dashboard/admin.blade.php`
- `resources/views/dashboard/superadmin.blade.php`
- `resources/views/dashboard/teknisi.blade.php`
- `resources/views/dashboard/kepsek.blade.php`
- `resources/views/dashboard/kepsek-reports.blade.php`

Kalau ada perubahan KPI/card/statistik, biasanya titik awalnya ada di controller dashboard + blade dashboard role terkait.

## 12. Modul Fitur yang Wajib Dipahami

Bagian ini lebih penting daripada sekadar nama file. Tujuannya supaya developer baru cepat paham sistem dari sudut pandang fitur.

### 12.1 Autentikasi, Profil, dan Notifikasi

Modul ini mengurus:
- login / logout / forgot password Laravel auth
- edit profil user
- ganti password manual
- notifikasi in-app
- unread counter dan dropdown notifikasi

File penting:
- `routes/auth.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/NotificationController.php`
- `app/Models/Notification.php`
- `app/Jobs/StoreNotificationJob.php`

Catatan:
- notifikasi dipakai di banyak modul lain
- jangan ubah format notifikasi sembarangan kalau tidak mengecek pemanggilnya

### 12.2 Pengaduan

Ini modul inti project.

Fitur yang ada:
- buat pengaduan
- track pengaduan publik tanpa login
- upload foto pengaduan
- feedback setelah selesai
- request reopen
- cek duplicate via endpoint AJAX
- pemilihan gedung/ruangan

File penting:
- `app/Http/Controllers/PengaduanController.php`
- `app/Models/Pengaduan.php`
- `app/Models/PengaduanPhoto.php`
- `app/Models/HistoryPengaduan.php`
- `resources/views/pengaduan/*`

Behavior penting:
- kode pengaduan dibentuk otomatis
- status pengaduan dipakai lintas role
- banyak dashboard dan laporan bergantung pada data modul ini

### 12.3 Pengaduan Admin

Admin punya modul lanjutan untuk operasi pengaduan:
- verifikasi / reject
- assign teknisi
- bulk status / bulk assign
- mark duplicate
- approve reopen
- update prioritas
- force priority
- export data
- overload board

File penting:
- `app/Http/Controllers/Admin/AdminPengaduanController.php`
- `app/Services/TicketStatusTransitionService.php`
- `app/Services/PriorityScoringService.php`
- `app/Services/TriageScoringService.php`
- `app/Services/SlaService.php`
- `app/Services/DispatchQueueService.php`
- `resources/views/admin/pengaduan/*`

Kalau ada bug status atau prioritas, cek service dulu sebelum mengubah controller.

### 12.4 Pengaduan Teknisi

Teknisi tidak melihat semua pengaduan, hanya yang relevan dengan alur teknisi:
- daftar pengaduan yang ditugaskan
- update status
- reschedule
- complete
- recompute queue

File penting:
- `app/Http/Controllers/Teknisi/TeknisiPengaduanController.php`
- `resources/views/teknisi/pengaduan/*`

### 12.5 Pinjaman Barang

Modul ini dipakai siswa/guru untuk mengajukan pinjaman barang.

Fitur:
- lihat daftar pinjaman saya
- ajukan pinjaman
- batal pinjaman pending
- feedback setelah selesai

File penting:
- `app/Http/Controllers/PinjamanController.php`
- `app/Models/Pinjaman.php`
- `app/Models/PinjamanFeedback.php`
- `app/Models/PinjamanLog.php`
- `resources/views/pinjaman/*`

Behavior penting:
- user hanya bisa pilih barang aktif
- hanya barang dengan `stok_tersedia > 0` yang muncul

### 12.6 Manajemen Pinjaman Admin

Ini area operasional admin untuk modul pinjaman:
- approve / reject
- check-out
- check-in
- force close
- export pinjaman
- adjust stock barang terkait transaksi

File penting:
- `app/Http/Controllers/Admin/AdminPinjamanController.php`
- `resources/views/admin/pinjaman/*`

Behavior penting:
- stok tersedia berkurang saat `check-out`
- stok tersedia bertambah lagi saat `check-in`
- jangan ubah alur ini tanpa cek service stok

### 12.7 Master Barang

Ini modul inventaris barang untuk admin/superadmin.

Fitur:
- lihat daftar semua barang
- tambah barang
- edit data barang
- ubah status aktif/nonaktif
- hapus barang jika belum punya histori pinjaman

File penting:
- `app/Http/Controllers/Admin/BarangController.php`
- `app/Models/Barang.php`
- `resources/views/admin/barangs/*`

Modul ini sekarang adalah sumber data utama untuk fitur pinjaman.

### 12.8 Kategori

Modul ini mengelola kategori dan subkategori pengaduan.

Fitur:
- CRUD kategori
- CRUD subkategori per kategori
- endpoint dependent dropdown kategori -> subkategori

File penting:
- `app/Http/Controllers/Admin/KategoriController.php`
- `app/Models/Kategori.php`
- `app/Models/SubKategori.php`
- `resources/views/admin/kategoris/*`

### 12.9 Gedung & Ruangan

Modul ini mengelola lokasi fisik sekolah yang dipakai oleh pengaduan dan denah.

Fitur:
- CRUD gedung
- CRUD ruangan per gedung
- endpoint dependent dropdown gedung -> ruangan

File penting:
- `app/Http/Controllers/Admin/GedungController.php`
- `app/Models/Gedung.php`
- `app/Models/Ruangan.php`
- `resources/views/admin/gedungs/*`

Kalau data ruangan salah, biasanya efeknya terasa ke:
- form pengaduan
- laporan
- peta digital / denah

### 12.10 Laporan

Modul ini dipakai admin dan kepsek untuk rekap data.

Fitur:
- halaman laporan index
- laporan pengaduan
- laporan performa
- export report

File penting:
- `app/Http/Controllers/Admin/ReportController.php`
- `app/Http/Controllers/Kepsek/KepsekDashboardController.php`
- `resources/views/admin/reports/*`
- `resources/views/dashboard/kepsek-reports.blade.php`

### 12.11 Overload Board

Modul ini adalah papan monitoring pengaduan overload untuk admin.

File penting:
- `app/Http/Controllers/Admin/AdminPengaduanController.php`
- `resources/views/admin/pengaduan/overload-board.blade.php`
- command terkait overload dan SLA di `app/Console/Commands`

### 12.12 Peta Digital dan Denah

Ada dua hal yang perlu dibedakan:

- `Peta Digital`
  - viewer denah aktif untuk admin/teknisi
  - dipakai untuk melihat layer dan area yang sudah dipetakan

- `Kelola Denah`
  - CRUD denah
  - upload file PDF/gambar
  - edit layer
  - gambar area
  - aktivasi denah

File penting:
- `app/Http/Controllers/Admin/SchoolMapController.php`
- `app/Http/Controllers/Teknisi/TeknisiSchoolMapController.php`
- `app/Services/SchoolMapService.php`
- `resources/views/admin/denah/*`
- `resources/views/teknisi/peta-digital/index.blade.php`
- `resources/views/partials/school-map-viewer.blade.php`

Catatan:
- modul ini sudah punya logic file streaming sendiri
- jangan asumsi file denah cukup dibuka lewat `/storage/...`

### 12.13 Master Data

Modul ini khusus superadmin untuk import data sekolah.

Fitur:
- preview import
- commit import
- download template
- error report
- validasi master data

File penting:
- `app/Http/Controllers/Admin/MasterDataController.php`
- `app/Services/SchoolMasterDataImportService.php`
- `app/Services/StudentBulkImportService.php`
- `resources/views/admin/master-data/*`

### 12.14 Pengaturan dan Log Aktivitas

Khusus superadmin.

Fitur:
- ubah setting aplikasi
- upload logo
- lihat log aktivitas
- export log

File penting:
- `app/Http/Controllers/Admin/SettingController.php`
- `app/Http/Controllers/Admin/LogController.php`
- `app/Models/Setting.php`
- `app/Models/Log.php`
- `resources/views/admin/settings/*`
- `resources/views/admin/logs/*`

## 13. File yang Paling Sering Disentuh Saat Nambah Fitur

Kalau develop fitur baru, biasanya file yang disentuh kombinasi dari:

- `routes/web.php`
- `app/Http/Controllers/...`
- `app/Services/...`
- `app/Models/...`
- `resources/views/...`
- migration baru di `database/migrations`

Rule of thumb project ini:
- validasi request umumnya di controller
- logic yang lumayan kompleks dipindah ke `Services`
- tampilan masih dominan Blade, bukan SPA

### Pola kerja codebase yang perlu diingat

- route hampir semuanya ada di `routes/web.php`
- middleware role adalah gate utama untuk hak akses
- kalau fitur menyentuh status workflow, biasanya ada service yang sebaiknya dipakai daripada menulis update mentah di controller
- banyak halaman memakai AJAX endpoint kecil untuk dependent dropdown atau widget UI
- notifikasi dan dashboard sering mengambil data dari banyak modul, jadi perubahan schema/model bisa berdampak ke area lain

## 14. Command yang Sering Dipakai

### Clear cache Laravel

```bash
php artisan optimize:clear
```

### Cek environment

```bash
php artisan app:doctor
```

### Jalankan test

```bash
composer test
```

### Rebuild frontend production

```bash
npm run build
```

### Lihat route

```bash
php artisan route:list
```

### Queue dan scheduler

Untuk dev lokal biasanya aman pakai `QUEUE_CONNECTION=sync`, tapi project ini punya command/scheduler yang penting.

Command yang relevan:
- `php artisan queue:work`
- `php artisan schedule:run`
- `php artisan backup:run`
- `php artisan tickets:check-sla`
- `php artisan tickets:detect-overload`
- `php artisan pengaduan:recompute-queue`
- `php artisan master-data:import-school-layout ...`

Kalau ada fitur yang terasa “tidak jalan otomatis”, cek apakah command/scheduler terkait memang sedang berjalan.

## 15. Fitur Pinjaman: Hal yang Wajib Dipahami

Karena fitur ini gampang rusak kalau tidak paham alurnya:

- Master barang dikelola admin lewat `BarangController`
- Siswa hanya bisa meminjam barang yang:
  - `is_active = true`
  - `stok_tersedia > 0`
- Stok `tidak` berkurang saat pengajuan dibuat
- Stok `baru berkurang` saat admin melakukan `check-out`
- Stok `bertambah lagi` saat `check-in`

Logic stok ada di:
- [`PinjamanAvailabilityService.php`](./app/Services/PinjamanAvailabilityService.php)

Jadi kalau ada bug stok, cek service ini lebih dulu.

## 16. Fitur Denah / Peta Digital: Hal yang Wajib Dipahami

Fitur ini menyimpan file denah dan area mapping untuk gedung/ruangan.

Yang perlu diingat:
- denah dikelola oleh `SchoolMapController`
- viewer aktif dihasilkan oleh `SchoolMapService`
- file denah sekarang diakses lewat route aplikasi, bukan bergantung penuh ke `public/storage`

Kalau ada masalah file denah tidak muncul:
1. cek data `school_maps`
2. cek route file denah
3. cek file benar-benar ada di storage

## 17. Error yang Paling Sering Terjadi Saat Setup

### `Access denied for user ...`

Biasanya:
- kredensial DB di `.env` salah
- user database belum dibuat
- masih pakai root dengan auth_socket

Solusi:
- buat user DB khusus aplikasi
- update `.env`
- jalankan `php artisan optimize:clear`

### `Base table or view not found`

Artinya migrasi belum jalan.

Solusi:

```bash
php artisan migrate
php artisan db:seed
php artisan app:doctor
```

### Asset CSS/JS tidak muncul

Biasanya `npm install` atau `npm run dev` / `npm run build` belum dijalankan.

### Vite tidak jalan di Windows

Solusi paling aman:
- pakai Node.js LTS
- hapus `node_modules` lalu install ulang
- jalankan `npm install`
- jalankan `npm run dev`

## 18. Checklist Sebelum Commit Perubahan

Minimal lakukan ini:

```bash
php artisan optimize:clear
php artisan app:doctor
composer test
```

Kalau ada perubahan frontend:

```bash
npm run build
```

Kalau tidak sempat test semua, tulis jelas di commit atau handoff apa yang belum diverifikasi.

## 19. Saran Workflow Buat Penerus Project

- jangan edit langsung di `main`
- selalu buat branch fitur/fix
- baca route + controller + view terkait dulu sebelum ubah logic
- kalau ubah DB, selalu pakai migration baru
- jangan mengubah data seed default tanpa alasan yang jelas
- kalau ubah `.env.example`, pastikan perubahan itu memang layak untuk semua developer berikutnya

## 20. Catatan Handoff Terakhir

Beberapa hal yang sudah pernah jadi sumber kebingungan di project ini:

- local setup lebih stabil dengan `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`
- fitur chatbot lama sudah dihapus dari project, jadi kalau mau ditambahkan lagi sebaiknya dibuat sebagai modul/endpoint terpisah dengan scope yang jelas
- master barang sekarang punya halaman admin sendiri, jadi tidak perlu input barang manual ke database untuk kebutuhan normal

## 21. Rancangan Endpoint Sumber Informasi untuk Chatbot

Bagian ini sengaja disiapkan untuk programmer yang akan mengerjakan chatbot agar tidak perlu menebak data apa saja yang boleh diambil dari sistem utama.

Prinsipnya:
- chatbot `jangan` query database sembarangan dari semua tabel
- chatbot sebaiknya membaca data dari endpoint yang memang disiapkan sebagai `context source`
- setiap endpoint harus sudah difilter sesuai role user yang login
- chatbot hanya mengonsumsi data, bukan menjadi tempat business logic utama

### Tujuan endpoint ini

Endpoint sumber informasi dipakai untuk:
- menampilkan barang apa saja yang tersedia dan stoknya berapa
- memberi tahu status pinjaman user
- memberi tahu status pengaduan user
- menjelaskan cara memakai fitur sistem
- memberi konteks sesuai role: siswa, guru, admin, teknisi, kepsek

### Namespace yang disarankan

Supaya rapi, gunakan prefix:

```text
/api/chatbot/context/*
```

Kalau nanti anggota tim membuat service chatbot terpisah, service itu tinggal consume endpoint di bawah ini.

### Endpoint yang disarankan

#### 1. Profil user login

```http
GET /api/chatbot/context/me
```

Fungsi:
- memberi tahu chatbot siapa user yang sedang login
- menentukan role dan hak akses jawaban

Contoh response:

```json
{
  "user": {
    "id": 12,
    "name": "Andi Pratama",
    "email": "andi@sfcs.sch.id",
    "role": "siswa",
    "kelas": "XI RPL 1"
  }
}
```

#### 2. FAQ / bantuan sistem

```http
GET /api/chatbot/context/help
```

Fungsi:
- sumber jawaban statis seperti cara pakai sistem
- cocok untuk pertanyaan seperti:
  - "cara lapor kerusakan gimana?"
  - "cara pinjam barang gimana?"
  - "admin ngapain saja?"

Contoh response:

```json
{
  "pinjam_barang": [
    "Buka menu Pinjam Barang",
    "Pilih barang yang tersedia",
    "Isi jumlah dan alasan pinjam",
    "Kirim pengajuan dan tunggu approval admin"
  ],
  "buat_pengaduan": [
    "Buka menu Pengaduan",
    "Isi judul dan deskripsi kerusakan",
    "Pilih kategori dan lokasi",
    "Upload foto bila ada lalu kirim"
  ],
  "role_summary": {
    "siswa": "Bisa membuat pengaduan dan pinjaman",
    "guru": "Bisa membuat pengaduan dan pinjaman",
    "admin": "Bisa memverifikasi pengaduan dan mengelola pinjaman",
    "teknisi": "Bisa menangani pengaduan yang di-assign"
  }
}
```

#### 3. Daftar barang aktif dan stok

```http
GET /api/chatbot/context/barangs
```

Fungsi:
- menjawab pertanyaan seperti:
  - "barang apa yang tersedia?"
  - "stok kertas folio berapa?"
  - "barang ATK apa saja yang bisa dipinjam?"

Contoh response:

```json
{
  "items": [
    {
      "id": 4,
      "kode_barang": "BRG-ATK-001",
      "nama": "Kertas Folio",
      "kategori": "ATK",
      "lokasi": "Gudang ATK",
      "stok_total": 100,
      "stok_tersedia": 100,
      "stok_rusak": 0,
      "is_active": true
    }
  ]
}
```

Catatan:
- untuk chatbot siswa/guru, biasanya cukup tampilkan barang `aktif`
- kalau ingin lebih ringan, endpoint ini bisa hanya mengirim barang dengan `stok_tersedia > 0`

#### 4. Detail satu barang

```http
GET /api/chatbot/context/barangs/{id}
```

Fungsi:
- menjawab pertanyaan spesifik tentang satu barang
- berguna kalau chatbot sudah melakukan matching item tertentu

Contoh response:

```json
{
  "item": {
    "id": 4,
    "kode_barang": "BRG-ATK-001",
    "nama": "Kertas Folio",
    "kategori": "ATK",
    "lokasi": "Gudang ATK",
    "stok_total": 100,
    "stok_tersedia": 100,
    "stok_rusak": 0,
    "is_active": true
  }
}
```

#### 5. Pinjaman milik user login

```http
GET /api/chatbot/context/my-pinjamans
```

Fungsi:
- untuk siswa/guru
- menjawab:
  - "pinjaman saya apa saja?"
  - "status pinjaman saya sekarang apa?"

Contoh response:

```json
{
  "items": [
    {
      "id": 33,
      "kode_pinjaman": "PJM-20260418-001",
      "barang": "Kertas Folio",
      "qty": 2,
      "status": "disetujui",
      "tgl_pinjam": "2026-04-18 09:30:00",
      "tgl_jatuh_tempo": "2026-04-20 09:30:00",
      "tgl_kembali": null
    }
  ]
}
```

#### 6. Pengaduan milik user login

```http
GET /api/chatbot/context/my-pengaduans
```

Fungsi:
- untuk siswa/guru
- menjawab:
  - "pengaduan saya statusnya apa?"
  - "laporan saya yang masih aktif apa saja?"

Contoh response:

```json
{
  "items": [
    {
      "id": 15,
      "kode_pengaduan": "ADU-20260418-004",
      "judul": "Lampu kelas mati",
      "status": "diproses",
      "prioritas": "sedang",
      "gedung": "Gedung A",
      "ruangan": "Ruang XI RPL 1",
      "created_at": "2026-04-18 08:10:00"
    }
  ]
}
```

#### 7. Ringkasan pengaduan global

```http
GET /api/chatbot/context/pengaduan-summary
```

Fungsi:
- hanya untuk role tertentu seperti admin, teknisi, kepsek
- menjawab:
  - "berapa pengaduan aktif?"
  - "berapa yang pending?"
  - "berapa yang urgent?"

Contoh response:

```json
{
  "total_aktif": 12,
  "pending": 4,
  "diverifikasi": 3,
  "diproses": 5,
  "urgent": 2
}
```

#### 8. Ringkasan pinjaman global

```http
GET /api/chatbot/context/pinjaman-summary
```

Fungsi:
- untuk admin/superadmin
- menjawab:
  - "ada berapa pinjaman aktif?"
  - "pinjaman yang terlambat ada berapa?"

Contoh response:

```json
{
  "pending": 2,
  "disetujui": 3,
  "dipinjam": 6,
  "terlambat": 1,
  "selesai": 18
}
```

#### 9. Daftar menu/fitur per role

```http
GET /api/chatbot/context/feature-map
```

Fungsi:
- chatbot bisa menjelaskan menu yang tersedia untuk role tertentu
- cocok untuk pertanyaan seperti:
  - "saya bisa akses apa saja?"
  - "menu admin apa saja?"

Contoh response:

```json
{
  "siswa": [
    "Dashboard",
    "Pengaduan Saya",
    "Buat Pengaduan",
    "Pinjam Barang"
  ],
  "admin": [
    "Kelola Pengaduan",
    "Kelola Pengguna",
    "Kelola Pinjaman",
    "Master Barang",
    "Peta Digital"
  ]
}
```

### Endpoint minimal yang paling penting

Kalau mau mulai dari versi paling kecil dulu, minimal siapkan ini:

```text
GET /api/chatbot/context/me
GET /api/chatbot/context/help
GET /api/chatbot/context/barangs
GET /api/chatbot/context/my-pinjamans
GET /api/chatbot/context/my-pengaduans
```

Dengan 5 endpoint itu saja chatbot sudah bisa menjawab cukup banyak pertanyaan umum.

### Aturan access control

Ini penting. Jangan sampai chatbot bocor data.

Saran pembatasan:
- `siswa`
  - hanya boleh akses data dirinya sendiri
  - boleh akses FAQ umum dan daftar barang aktif
- `guru`
  - sama seperti siswa, kecuali memang ada policy tambahan
- `teknisi`
  - boleh akses ringkasan pengaduan yang relevan dengan pekerjaannya
- `admin`
  - boleh akses ringkasan global pengaduan dan pinjaman
- `kepsek`
  - boleh akses summary/dashboard level
- `superadmin`
  - boleh akses semua endpoint context yang bersifat administratif

### Bentuk implementasi yang disarankan

Kalau nanti endpoint ini benar-benar dibuat, saran struktur code:

- controller:
  - `App\Http\Controllers\Api\ChatbotContextController`
- route:
  - dikelompokkan di `routes/web.php` atau dipisah ke `routes/api.php`
- service:
  - `App\Services\ChatbotContextService`

Kalau mau cepat dan tetap rapi:
- controller memanggil service
- service membentuk payload JSON sesuai role user

### Catatan penting untuk programmer chatbot

- chatbot sebaiknya tidak langsung membaca semua model
- lebih aman consume endpoint context yang sudah stabil
- jangan taruh prompt besar atau logic AI di controller utama aplikasi
- pisahkan jelas:
  - aplikasi inti SFCS
  - endpoint sumber data
  - engine chatbot

### Kontrak kerja yang direkomendasikan antar anggota tim

Supaya kolaborasi enak:
- maintainer SFCS menyediakan endpoint context
- developer chatbot consume endpoint tersebut
- format JSON harus dijaga stabil
- kalau ada field baru, tambahkan tanpa merusak struktur lama

Dengan model ini, backend utama dan chatbot bisa berkembang masing-masing tanpa saling mengacaukan.

## 22. Quick Start Singkat

Kalau kamu cuma butuh project jalan cepat di mesin baru:

```bash
cd sfcs-app
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan app:doctor
php artisan serve
```

Lalu di terminal lain:

```bash
npm run dev
```

Selesai. Kalau ada error, debug dari `.env`, koneksi database, lalu hasil `app:doctor`.
