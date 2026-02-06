# Changelog

All notable changes to SFCS (School Facility Complaint System) will be documented in this file.

## [1.1.0] - 2026-02-06

### 🔔 Notification System Overhaul
- Fixed notification route naming (`notifications.mark-read` → `notifications.read`)
- Added browser push notification support (Web Notifications API)
- Auto-check unread notifications every 30 seconds
- Auto mark as read when notification is clicked
- Smart link routing: notification links auto-adjust based on user role
  - Siswa/Guru → `/pengaduan/{kode}`
  - Teknisi → `/teknisi/pengaduan/{kode}`
  - Admin → `/admin/pengaduan/{kode}`
- Added filter by status (read/unread) and type on notification page
- Added `/notifications/unread-count` and `/notifications/latest` API endpoints

### 🔧 Route & Model Fixes
- Added `getRouteKeyName()` to Pengaduan model → uses `kode_pengaduan` as route key
- Fixed 404 error when accessing pengaduan by kode (e.g., `/pengaduan/ADU-20260206-001`)
- Added legacy numeric ID redirect for all pengaduan routes (siswa, teknisi, admin)

### 🔐 Session & Authentication
- Increased session lifetime to 30 days (43,200 minutes)
- Remember me checkbox checked by default on login
- Updated label to "Tetap login (30 hari)"

### 👥 User Management Fix
- Fixed UserController validation: `nis_nip` → separate `nis` and `nip` fields
- Fixed `no_telp` → `no_hp` column name
- Removed non-existent `kelas` field from validation

### 📱 Mobile-First Redesign
- Redesigned pengaduan create page with step-by-step mobile UX
- Added camera capture (getUserMedia API with rear camera)
- Auto-set tanggal kejadian to current date
- Progress bar indicator for form steps
- Large touch-friendly buttons (min 48px targets)

### 🐛 Bug Fixes
- Fixed `@push/@endpush` mismatch in create.blade.php
- Deleted duplicate `2026_02_04_212951_create_logs_table.php` migration
- Fixed null check for `histories` relationship in show.blade.php
- Commented out PengaduanSeeder to prevent sample data on fresh migration

---

## [1.0.0] - 2026-02-05

### 🎉 Initial Release

#### Features Added
- **User Management**
  - Multi-role authentication (Siswa, Guru, Teknisi, Admin, Kepala Sekolah, Super Admin)
  - Role-based access control
  - User profile management

- **Pengaduan System**
  - Create pengaduan with photo upload
  - Real-time status tracking
  - Priority levels (Urgent, Tinggi, Sedang, Rendah)
  - Search and filter functionality
  - Pengaduan history tracking

- **Notification System**
  - In-app notifications
  - Mark as read functionality
  - Notification dropdown in header

- **Dashboard Analytics**
  - Role-specific dashboards
  - Performance charts
  - Export reports

- **Mobile Responsive**
  - Mobile-first design
  - Touch-friendly interface

- **Admin Features**
  - Kategori & Sub-kategori management
  - Gedung & Ruangan management
  - User management
  - System settings

- **Feedback System**
  - Rating system (1-5 stars)
  - Comment functionality

#### Security
- CSRF Protection
- XSS Prevention
- SQL Injection Prevention
- Password Hashing (Bcrypt)
- Session Security
- Activity Logging

---

© 2026 Dzaky. All Rights Reserved.
