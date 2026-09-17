# SFCS Developer Handoff

Dokumen ini ditulis untuk developer yang akan melanjutkan maintenance atau pengembangan `sfcs-app`.
Fokusnya bukan onboarding user akhir, tetapi current state implementasi, boundary antar modul, workflow bisnis lintas role, dan titik rawan yang perlu dipahami sebelum mengubah code.

Project ini saat ini adalah aplikasi Laravel multi-role untuk operasional sarpras sekolah, dengan 2 domain utama:

- `pengaduan fasilitas`: pelaporan kerusakan/masalah fasilitas sekolah dari guru/siswa ke admin dan teknisi
- `pinjaman barang`: workflow peminjaman inventaris sekolah berbasis approval admin dan stok aktual

Di atas 2 domain utama itu, ada modul pendukung yang cukup penting:

- digital school map / denah interaktif
- master data gedung, ruangan, kategori, subkategori, jurusan
- user management + import siswa + promote kelas
- reporting, activity log, health check, scheduler/commands

## 1. Ringkasan Stack

- Backend: Laravel 12
- PHP: 8.2+
- Database utama: MySQL / MariaDB
- Frontend asset build: Vite
- UI layer: Blade + Tailwind + Alpine + vanilla JS
- Auth: Laravel Breeze-style auth
- Role model: custom middleware `role`
- Queue: database queue supported, tetapi local setup default aman memakai `sync`
- Notification: in-app notification via tabel `notifications`
- PWA: basic manifest + service worker tersedia

## 2. Current State Fitur

### 2.1 Implemented dan aktif dipakai codebase

- Multi-role auth untuk `superadmin`, `admin`, `kepsek`, `teknisi`, `guru`, `siswa`
- Login siswa via `NIS`, selain siswa via email
- Dashboard per role dengan statistik berbeda
- CRUD pengaduan untuk siswa/guru/admin/superadmin
- Upload multi-foto bukti pengaduan
- Public tracking pengaduan lewat `/track`
- Duplicate detection saat submit pengaduan
- Priority scoring dan auto-downgrade priority claim yang tidak match impact
- Manual priority review flag untuk admin
- SLA due date per prioritas
- Assignment pengaduan ke teknisi
- Status flow pengaduan `pending -> diverifikasi -> diproses -> selesai/ditolak`
- Reschedule penanganan oleh teknisi dengan history schedule
- Queue recompute / triage ranking per teknisi
- Overload detection + overload board
- Duplicate lifecycle management, termasuk auto-close tiket duplikat saat master selesai
- Request reopen dari pelapor dan approval reopen oleh admin
- Feedback pengaduan setelah selesai
- Modul pinjaman barang end-to-end: request, approve/reject, check-out, check-in, force close
- Stock reservation / release berbasis transaksi database
- Reminder pinjaman mendekati jatuh tempo dan auto-mark `terlambat`
- Master barang, kategori, gedung, ruangan, user
- Master data import sekolah dengan preview lalu commit
- Import siswa massal CSV
- Promote kelas siswa aktif berbasis preview token lalu apply
- Digital map: upload denah PDF/image, layer management, area mapping gedung/ruangan, active map viewer
- Map picker pada pengaduan jika denah aktif tersedia
- In-app notification dropdown, unread count, mark read
- Activity log untuk event penting
- Internal health endpoint admin
- App doctor command untuk validasi setup
- Backup command terjadwal

### 2.2 Implemented parsial / ada caveat operasional

- PWA sudah ada, tetapi belum bisa dianggap mobile app yang fully polished
- Queue async supported, tetapi banyak local/dev flow masih diasumsikan jalan aman dengan `QUEUE_CONNECTION=sync`
- Force password change middleware masih terpasang di route group, tetapi seed/default flow saat ini tidak memaksa reset password pertama kali
- Digital map sudah terhubung ke pengaduan, tetapi fallback lokasi teks tetap bagian penting dari flow
- Notification system hanya in-app/database; belum ada WA, email transactional, atau push notification production-grade
- Reporting sudah cukup untuk operasional, tetapi belum masuk level BI / audit analytics yang mendalam

### 2.3 Belum implemented / masih roadmap

- WhatsApp notification / WhatsApp reminder
- Penggunaan nomor HP sebagai workflow wajib first-login
- Search/filter katalog barang yang kaya untuk peminjam
- CTA cepat seperti "pinjam barang ini sekarang" dari katalog siswa
- Template pengaduan dinamis per kategori
- Mobile UX audit menyeluruh lintas semua halaman

## 3. Struktur Arsitektur

Secara implementasi, codebase dibagi cukup rapi:

- `app/Http/Controllers`
  controller untuk HTTP entry point dan orchestration request
- `app/Services`
  business rules yang tidak sebaiknya ditaruh di controller
- `app/Models`
  Eloquent model + relationship + beberapa helper domain
- `resources/views`
  Blade view per role/modul
- `routes/web.php`
  seluruh route web utama per role
- `routes/console.php`
  scheduler command
- `app/Console/Commands`
  task operasional dan background maintenance
- `database/migrations`
  schema inti sistem
- `database/seeders`
  seed data awal, akun role, dan sample data

Pattern yang dipakai sekarang:

- controller menangani validasi request, authorization, dan orchestration
- service menangani rule seperti priority scoring, duplicate detection, triage queue, SLA, import, promotion
- model menyimpan constants status/prioritas, cast, route key, relationship
- notification dibuat lewat `Notification::send(...)`
- audit event penting direkam ke `Log`

## 4. Teknologi dan Dependency Penting

`composer.json` dan `package.json` saat ini menunjukkan dependency inti berikut:

- `laravel/framework:^12.0`
- `laravel/breeze:^2.3`
- `phpunit:^11.5`
- `laravel/pint`
- `laravel/pail`
- `vite:^7`
- `tailwindcss`
- `alpinejs`
- `axios`
- `concurrently`

Tidak ada SPA framework seperti React/Vue. Flow frontend tetap server-rendered Blade.

## 5. Setup Local yang Disarankan

### 5.1 Requirement minimum

- PHP 8.2+
- Composer
- Node.js 18+ dan npm
- MySQL/MariaDB
- PHP extensions: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `json`, `tokenizer`, `xml`, `curl`, `zip`, `bcmath`, `gd`, `intl`, `pdo_sqlite`, `sqlite3`

`pdo_sqlite` dan `sqlite3` tetap berguna karena test/CLI tertentu bisa bergantung ke driver itu.

### 5.2 Bootstrapping

```bash
git clone https://github.com/xDzaky/SFCS.git
cd SFCS/sfcs-app
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan app:doctor
```

Untuk local yang paling aman:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sfcs_db
DB_USERNAME=sfcs_user
DB_PASSWORD=strong_password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
MAIL_MAILER=log
```

### 5.3 Menjalankan app

Cara aman:

```bash
php artisan serve
npm run dev
```

Atau:

```bash
composer run dev
```

`composer run dev` akan menyalakan:

- web server
- queue listener
- log watcher
- vite dev server

## 6. Akun Default Seeder

Password default semua akun seed: `password`

Role utama yang tersedia dari seeder:

- `superadmin@sfcs.sch.id`
- `admin@sfcs.sch.id`
- `kepsek@sfcs.sch.id`
- beberapa akun `teknisi`
- beberapa akun `guru`
- beberapa akun `siswa`

`app:doctor` saat ini minimal mengecek:

- `superadmin@sfcs.sch.id`
- `admin@sfcs.sch.id`
- `kepsek@sfcs.sch.id`

## 7. Model Domain Inti

### 7.1 `User`

Field penting:

- `role`
- `nis`
- `nip`
- `kelas`
- `no_hp`
- `is_active`
- `force_password_change`

Catatan:

- siswa bisa login pakai `nis`
- user nonaktif akan langsung di-logout oleh middleware `active`
- role helper tersedia di model, mis. `isAdmin()`, `isTeknisi()`, `isSiswa()`

### 7.2 `Pengaduan`

Route key memakai `kode_pengaduan`, bukan ID numerik.

Field domain penting:

- lokasi: `gedung_id`, `lantai`, `ruangan_id`, `lokasi_detail`
- map: `school_map_id`, `school_map_layer_id`, `map_point_x`, `map_point_y`, `map_zoom`, `map_source`
- priority: `prioritas`, `requested_prioritas`, `priority_score`, `needs_priority_review`
- lifecycle: `verified_at`, `assigned_at`, `started_at`, `completed_at`
- triage/load: `triage_score`, `queue_rank`, `triage_bucket`, `planned_start_at`, `planned_end_at`, `delay_minutes`, `is_overload_delayed`, `load_snapshot`
- duplicate: `duplicate_of_id`, `duplicate_marked_by`, `auto_closed_by_duplicate`, `auto_closed_from_master_id`
- reopen: `reopen_requested_at`, `reopen_requested_by`, `reopen_reason`, `reopen_count`
- SLA: `sla_due_at`, `first_response_at`

Status constant:

- `pending`
- `diverifikasi`
- `diproses`
- `selesai`
- `ditolak`

Prioritas constant:

- `rendah`
- `sedang`
- `tinggi`
- `urgent`

### 7.3 `Pinjaman`

Route key memakai `kode_pinjaman`.

Status utama:

- `pending`
- `disetujui`
- `dipinjam`
- `terlambat`
- `selesai`
- `ditolak`

Field penting:

- `qty`
- `tgl_pinjam`
- `tgl_jatuh_tempo`
- `tgl_kembali`
- `approved_at`
- `checked_out_at`
- `marked_late_at`

### 7.4 `Notification`

Semua notifikasi aplikasi masuk ke tabel `notifications`.

Jenis penting yang saat ini dipakai:

- `pengaduan_created`
- `status_changed`
- `assigned`
- `feedback_reminder`
- `overdue`
- `pinjaman_created`
- `pinjaman_status`
- `priority_adjusted`
- `rescheduled`
- `overload_alert`

`Notification::send()` juga mengoreksi link notifikasi sesuai role target supaya link admin/teknisi/siswa tidak saling tertukar.

## 8. Service Layer yang Paling Menentukan Behaviour

### 8.1 `PriorityScoringService`

Dipakai saat submit pengaduan.

Input utama:

- safety risk
- pembelajaran terhambat
- exam related
- scope area
- utilitas terdampak

Output:

- score numerik
- prioritas computed
- prioritas final
- flag `needs_priority_review`

Rule penting:

- user boleh meminta prioritas tinggi/urgent
- sistem bisa menurunkan prioritas berdasarkan score impact aktual
- jika request awal lebih tinggi dari hasil sistem, tiket bisa di-flag untuk review admin

### 8.2 `PengaduanDuplicateDetector`

Mendeteksi kandidat tiket aktif yang kemungkinan duplikat berdasarkan:

- kategori
- subkategori
- gedung
- lantai
- ruangan
- `lokasi_detail` yang sudah dinormalisasi

Deteksi ini dipakai di:

- submit pengaduan baru
- panel admin untuk kandidat duplicate

### 8.3 `DuplicateLifecycleService`

Jika tiket master selesai, service ini bisa auto-close child tickets yang ditandai sebagai duplicate.

Efek samping:

- status duplicate child menjadi `selesai`
- teknisi dilepas
- admin note ditambah alasan auto-close
- log dan notification dibuat

### 8.4 `TicketStatusTransitionService`

Semua transisi status pengaduan sebaiknya mengikuti service ini, supaya timestamp lifecycle konsisten.

Rule transisi default:

- `pending -> diverifikasi | ditolak`
- `diverifikasi -> diproses | ditolak | pending`
- `diproses -> selesai | ditolak | diverifikasi`

### 8.5 `SlaService`

Menghitung `sla_due_at` berdasarkan prioritas:

- `urgent` dalam menit
- `tinggi`, `sedang`, `rendah` dalam jam

Threshold dibaca dari tabel `settings`.

### 8.6 `TriageScoringService` dan `DispatchQueueService`

Dipakai untuk workload teknisi.

`TriageScoringService` menghitung score dari:

- prioritas
- impact flags
- umur tiket
- status aktif

`DispatchQueueService` lalu:

- sort tiket aktif milik teknisi
- assign `queue_rank`
- hitung `planned_start_at` / `planned_end_at`
- hitung `delay_minutes`
- tandai `is_overload_delayed`
- simpan `load_snapshot`

Ini inti logic overload board dan estimasi delay.

### 8.7 `SchoolMapService`

Service ini menjadi jembatan antara denah sekolah dan domain pengaduan.

Dipakai untuk:

- cek apakah ada denah aktif
- generate layer blueprint dari PDF/image
- resolve layer berdasarkan `gedung + lantai`
- build payload viewer peta
- build payload map untuk detail pengaduan
- build active map API payload untuk map picker

### 8.8 `PinjamanAvailabilityService`

Dipakai admin pinjaman untuk reserve/release stock saat check-out dan check-in.
Ini bagian penting agar perubahan stok tetap transactional.

### 8.9 `StudentBulkImportService`

Import siswa CSV dengan mode:

- `replace_siswa`
- `upsert_only`

Behaviour penting:

- maksimal 5000 baris per upload
- role import dibatasi ke `siswa`
- jika email kosong, akan digenerate dari NIS
- default password fallback: `password`
- pada mode `replace_siswa`, siswa lama yang tidak ikut file dapat dinonaktifkan

### 8.10 `StudentClassPromotionService`

Mekanisme promote kelas 2 tahap:

1. preview menghasilkan token sementara
2. apply memakai token itu

Rule kelas yang saat ini diasumsikan valid:

- `X RPL 1`
- `XI MP 2`
- `XII AK 3`

Jurusan yang di-allow hardcoded:

- `RPL`
- `MP`
- `AK`
- `BD`
- `LP`

Jika format kelas tidak sesuai pola ini, siswa akan masuk list `skipped`.

## 9. Route dan Boundary per Role

Seluruh route utama ada di `routes/web.php`.

### 9.1 Public

- `/` redirect ke login
- `/track` untuk tracking pengaduan tanpa login

### 9.2 Authenticated shared routes

- `/dashboard`
- `/profile`
- `/notifications/*`
- `/api/school-map/active`

Middleware umum:

- `auth`
- `active`
- `force.password.change`

### 9.3 Siswa / Guru / Admin / Superadmin

Route pengaduan user-facing dan pinjaman berada di group role:

- `siswa`
- `guru`
- `admin`
- `superadmin`

Artinya admin/superadmin juga bisa membuka flow user-facing jika perlu.

### 9.4 Teknisi

Prefix `teknisi`:

- daftar tiket assigned
- detail tiket
- update status ke `diproses`
- complete tiket
- reschedule tiket
- recompute queue
- peta digital teknisi

### 9.5 Admin

Prefix `admin`:

- pengelolaan pengaduan
- pengelolaan pinjaman dan stok barang
- user management
- kategori, gedung, ruangan
- denah sekolah
- reports
- master data import
- internal health

### 9.6 Kepsek

Prefix `kepsek`:

- dashboard redirect ke dashboard role
- halaman reports untuk monitoring

### 9.7 Superadmin

Prefix `superadmin`:

- settings
- log aktivitas

Secara permission nyata, ada juga beberapa aksi yang di-controller dibatasi lebih keras untuk `superadmin`, misalnya:

- import siswa massal
- promote kelas
- master data import

## 10. Workflow End-to-End

### 10.1 Pengaduan: siswa/guru -> admin -> teknisi -> selesai

1. Pelapor membuat pengaduan.
2. Sistem validasi field lokasi, impact, foto, dan optional map point.
3. Sistem cek duplicate aktif.
4. Sistem hitung `priority_score`, prioritas final, dan `sla_due_at`.
5. Tiket dibuat dengan status `pending`.
6. Admin melihat tiket masuk, bisa:
   - assign teknisi
   - ubah status
   - reject
   - mark duplicate
   - ubah prioritas
7. Saat assign teknisi dari tiket `pending`, status bisa otomatis bergerak ke `diverifikasi`.
8. Teknisi membuka tiket assigned, lalu:
   - set `diproses`
   - reschedule bila perlu
   - mark `selesai`
9. Saat tiket selesai:
   - pelapor mendapat notification
   - reminder feedback dikirim
   - duplicate child dapat auto-close jika tiket ini adalah master
10. Pelapor bisa kirim feedback.
11. Jika perlu, pelapor bisa request reopen dan admin bisa approve reopen.

### 10.2 Duplicate handling

Ada 2 lapisan duplicate handling:

- pre-submit duplicate detection saat user membuat tiket
- manual duplicate marking oleh admin terhadap tiket existing

Jika tiket master selesai, duplicate child aktif bisa ikut ditutup otomatis.

### 10.3 Pinjaman barang

1. Siswa/guru membuka form pinjaman.
2. Barang yang tampil hanya barang aktif dengan `stok_tersedia > 0`.
3. User submit request `pending`.
4. Admin memutuskan:
   - approve
   - reject
5. Saat approve, status menjadi `disetujui`.
6. Saat barang fisik diserahkan, admin melakukan check-out:
   - stok di-reserve / dikurangi via service
   - status menjadi `dipinjam`
7. Saat barang dikembalikan, admin check-in:
   - stok dilepas / dikembalikan
   - status menjadi `selesai`
8. Jika melewati due date, scheduler menandai pinjaman sebagai `terlambat`.
9. User dapat memberi feedback setelah pinjaman selesai.

### 10.4 Denah sekolah / digital map

1. Admin upload file denah PDF atau image.
2. Sistem membuat `school_map`.
3. Sistem membentuk layer blueprint:
   - PDF: per halaman
   - image: single layer
4. Admin mengedit layer metadata:
   - `general`
   - `gedung_lantai`
5. Admin menggambar area yang menghubungkan denah dengan gedung/ruangan.
6. Salah satu denah ditandai aktif.
7. Saat user membuka form pengaduan:
   - jika ada denah aktif, frontend bisa load active map API
   - layer dicoba di-resolve dari `gedung + lantai`
   - user bisa menaruh titik manual
8. Detail tiket admin/teknisi/pelapor dapat memakai payload map yang sama untuk visualisasi konteks lokasi.

### 10.5 Master data sekolah

1. Superadmin upload ZIP/XLSX/CSV master data.
2. Sistem membuat preview batch.
3. Error/warning dikumpulkan.
4. Jika preview valid, superadmin commit batch.
5. Data kategori, gedung, ruangan, jurusan, dan mapping di-apply ke sistem.

Ini penting karena modul pengaduan dan denah sangat bergantung ke kualitas master data.

## 11. Fitur per Role

### 11.1 Siswa

- login via NIS
- dashboard personal
- buat, edit, lihat, track pengaduan milik sendiri
- upload foto bukti
- optional pilih titik di denah jika tersedia
- kirim feedback pengaduan
- request reopen
- ajukan pinjaman barang
- batalkan pinjaman yang masih pending
- beri feedback pinjaman selesai
- lihat notifikasi dan profil

### 11.2 Guru

Flow hampir sama dengan siswa, tetapi login via email dan tidak punya `nis`.

### 11.3 Teknisi

- dashboard tiket assigned
- list tiket assigned dengan sorting urgensi
- detail tiket + map context
- update status ke `diproses`
- complete tiket
- reschedule penanganan
- recompute queue
- akses peta digital untuk melihat context ruang/gedung

### 11.4 Admin

- dashboard operasional
- kelola seluruh pengaduan
- assign teknisi
- ubah status, reject, set prioritas, mark duplicate
- approve reopen
- export pengaduan
- overload board
- CRUD master barang
- manage approval pinjaman
- adjust stok
- manage user
- manage kategori dan subkategori
- manage gedung dan ruangan
- upload dan edit denah
- report pengaduan dan performance
- internal health endpoint

### 11.5 Kepsek

- dashboard monitoring
- melihat agregasi performa
- overload alert notification
- halaman report ringkas

Kepsek tidak menjadi operator workflow harian, lebih ke monitoring dan oversight.

### 11.6 Superadmin

- seluruh kemampuan admin
- settings
- log aktivitas
- import siswa
- promote kelas
- master data import

## 12. Dashboard Behaviour

`DashboardController` melakukan dispatch berdasarkan role:

- `superadmin`
- `admin`
- `kepsek`
- `teknisi`
- default: `siswa/guru`

Beberapa dashboard yang sudah memiliki logic berbeda:

- siswa/guru: statistik personal dan tiket terakhir
- teknisi: assigned ticket, urgent count, selesai bulan ini
- admin: operasional harian, backlog, pinjaman, overload, teknisi utilization
- kepsek: KPI high-level, performance teknisi, rating, tren
- superadmin: overview sistem dan aktivitas

## 13. AJAX / API Touchpoints Penting

Endpoint ini perlu diingat karena sering jadi coupling antara Blade dan backend:

- `/api/kategori/{kategori}/sub-kategoris`
  dependent dropdown kategori -> subkategori
- `/api/gedung/{gedung}/ruangans`
  dependent dropdown gedung -> ruangan
- `/api/pengaduan/check-duplicate`
  duplicate pre-check saat create pengaduan
- `/api/school-map/active`
  payload active map dan layer yang cocok untuk map picker
- `/notifications/unread-count`
- `/notifications/latest`
- `/notifications/dropdown`

## 14. Scheduler dan Command Operasional

Scheduler aktif di `routes/console.php`:

- `tickets:check-sla` tiap 15 menit
- `tickets:recompute-queue` tiap 5 menit
- `tickets:detect-overload` tiap 5 menit
- `backup:run` harian
- `pinjaman:mark-overdue` harian

Command penting:

- `php artisan app:doctor`
  health check setup aplikasi
- `php artisan tickets:check-sla`
  kirim warning/overdue notification
- `php artisan tickets:recompute-queue`
  hitung ulang antrean teknisi
- `php artisan tickets:detect-overload`
  kirim alert overload
- `php artisan pinjaman:mark-overdue`
  reminder dan auto-mark terlambat
- `php artisan students:promote --preview`
- `php artisan students:promote --apply --token=...`
- `php artisan master-data:import-school-layout ...`
- `php artisan backup:run`

## 15. File dan Modul yang Paling Sering Perlu Dibuka

### Pengaduan

- `app/Http/Controllers/PengaduanController.php`
- `app/Http/Controllers/Admin/AdminPengaduanController.php`
- `app/Http/Controllers/Teknisi/TeknisiPengaduanController.php`
- `app/Services/PriorityScoringService.php`
- `app/Services/PengaduanDuplicateDetector.php`
- `app/Services/DuplicateLifecycleService.php`
- `app/Services/TicketStatusTransitionService.php`
- `app/Services/SlaService.php`
- `app/Services/DispatchQueueService.php`
- `app/Services/SchoolMapService.php`

### Pinjaman

- `app/Http/Controllers/PinjamanController.php`
- `app/Http/Controllers/Admin/AdminPinjamanController.php`
- `app/Http/Controllers/Admin/BarangController.php`
- `app/Services/PinjamanAvailabilityService.php`
- `app/Console/Commands/MarkOverduePinjamanCommand.php`

### User / master data

- `app/Http/Controllers/Admin/UserController.php`
- `app/Services/StudentBulkImportService.php`
- `app/Services/StudentClassPromotionService.php`
- `app/Http/Controllers/Admin/MasterDataController.php`
- `app/Services/SchoolMasterDataImportService.php`

### Denah

- `app/Http/Controllers/Admin/SchoolMapController.php`
- `app/Http/Controllers/Teknisi/TeknisiSchoolMapController.php`
- `app/Services/SchoolMapService.php`
- `resources/views/partials/pengaduan-map-picker.blade.php`
- `resources/views/partials/school-map-viewer.blade.php`

## 16. Known Constraints dan Hal yang Mudah Menjebak

- Sistem masih sangat bergantung pada kualitas master data gedung/ruangan. Jika data ini berantakan, duplicate detection, report, dan map layer resolution ikut menurun kualitasnya.
- `ruangan_id` di flow pengaduan belum selalu menjadi input utama; beberapa logic duplicate masih fallback ke `lokasi_detail`.
- Map picker bukan source of truth tunggal. Pengaduan tetap harus aman walau tanpa titik denah.
- Banyak route menyediakan redirect compatibility untuk legacy notification link yang masih berbasis numeric ID.
- Notification link sudah banyak di-hardening per role, tetapi jika generate link baru secara custom tetap cek hasil akhirnya.
- Queue logic dan schedule logic mengandalkan kolom schema tertentu. `app:doctor` harus lolos dulu sebelum debug behaviour bisnis.
- `StudentClassPromotionService` mengasumsikan format kelas SMK yang cukup ketat. Data kelas yang tidak standar akan di-skip.
- `InternalHealthController` menghitung queue health dengan membaca tabel `jobs` dan `failed_jobs`; jangan asumsikan sehat jika migration queue belum lengkap.
- `RunBackupCommand` memakai `mariadb-dump` atau `mysqldump`; environment tanpa binary itu akan gagal backup.

## 17. Testing dan Verifikasi Minimal Setelah Mengubah Fitur

Sebelum merge perubahan yang menyentuh domain inti, minimal verifikasi:

- login tiap role yang terdampak
- create pengaduan baru
- assign teknisi dan ubah status sampai selesai
- feedback pengaduan
- create pinjaman, approve, check-out, check-in
- notification dropdown masih berjalan
- denah aktif masih bisa dibuka jika Anda menyentuh map-related code
- `php artisan app:doctor`

Jika menyentuh scheduler/service domain:

- `php artisan test`
- `php artisan tickets:recompute-queue`
- `php artisan tickets:check-sla`
- `php artisan pinjaman:mark-overdue`

Saat ini test otomatis yang terlihat di repo masih sangat sedikit, jadi regresi fungsional tetap perlu dicek manual.

## 18. Rekomendasi Cara Kerja untuk Maintainer Berikutnya

- Jangan treat project ini sebagai CRUD Laravel biasa. Behaviour utamanya ada di service layer.
- Kalau mengubah status flow pengaduan, selalu cek efek ke:
  - SLA
  - queue rank
  - duplicate auto-close
  - notifikasi
  - report/dashboard
- Kalau mengubah pinjaman, cek efek ke stok dan scheduler overdue.
- Kalau mengubah denah, cek juga form pengaduan, viewer admin, viewer teknisi, dan detail tiket.
- Kalau mengubah import/promotion siswa, cek konsekuensinya ke auth login via NIS dan status aktif user.

## 19. Ringkasan untuk Developer Baru

Kalau hanya punya waktu singkat untuk orientasi, pahami 5 hal ini dulu:

1. `Pengaduan` adalah domain paling kompleks karena terhubung ke duplicate detection, priority scoring, SLA, assignment teknisi, queue ranking, overload, feedback, reopen, dan denah.
2. `Pinjaman` punya lifecycle terpisah yang lebih sederhana, tetapi stok harus dijaga transactional.
3. `SchoolMapService` adalah integrasi lokasi visual, bukan pengganti total lokasi tekstual.
4. `superadmin` bukan sekadar admin tambahan; ada capability khusus untuk import siswa, promote kelas, master data, dan setting sistem.
5. Bila behaviour terasa aneh, cek dulu `settings`, scheduler command, dan schema readiness sebelum menyimpulkan bug ada di view/controller.
