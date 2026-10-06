# 📊 Panduan Import Data Pengguna & Master Data (Excel / CSV) — SFCS

Dokumen ini menjelaskan tata cara mengimport data pengguna (khususnya data siswa) dan master data fasilitas dari file Excel / Spreadsheet ke dalam sistem **SFCS (School Facilities & Complaint System)**.

---

## 📍 1. Lokasi Halaman Import

Import data pengguna dilakukan melalui halaman:
👉 **URL:** `http://127.0.0.1:8000/admin/users`  
*(Atau melalui menu sidebar: **Manajemen** ➔ **Kelola Pengguna**)*

> ⚠️ **Catatan Hak Akses:**  
> Tombol **"Import"** hanya dapat dilihat dan digunakan oleh akun dengan role **Superadmin**. Pastikan Anda login menggunakan akun Superadmin terlebih dahulu.

---

## 📁 2. Format File yang Didukung

- **Format File:** File berformat **`.csv` (Comma-Separated Values)** atau `.txt`.
- **Maksimal Ukuran File:** 5 MB.
- **Jika Data Masih di Microsoft Excel / Google Sheets:**  
  Anda dapat membuat dan merapikan data terlebih dahulu di Excel, lalu saat menyimpan pilih **Save As** ➔ tipe **CSV (Comma delimited) (*.csv)**.

---

## 📑 3. Struktur Kolom (Header) Import Pengguna

Baris pertama pada file CSV harus berisi nama-nama kolom berikut (huruf kecil):

```csv
nama,email,nis,kelas,no_hp,role,password
```

*(Sistem juga mendukung variasi nama header: `name` sebagai alias `nama`, dan `phone` sebagai alias `no_hp`).*

### 🔍 Penjelasan Setiap Kolom:

| Nama Kolom | Wajib / Opsional | Keterangan & Aturan Validasi | Contoh Isi |
| :--- | :---: | :--- | :--- |
| **`nama`** | **Wajib** | Nama lengkap pengguna / siswa. | `Andi Pratama` |
| **`nis`** | **Wajib** | Nomor Induk Siswa. Harus unik di sistem dan di dalam file. NIS ini digunakan oleh siswa untuk login. | `2401001` |
| **`email`** | *Opsional* | Alamat email aktif. Jika dikosongkan, sistem akan **otomatis membuatkan email unik**: `<nis>@school.local`. | `andi@example.com` *(atau kosongkan)* |
| **`kelas`** | *Opsional* | Nama kelas siswa. Digunakan untuk filter dan fitur kenaikan kelas (*Promote Kelas*). | `X IPA 1`, `XI TKJ 2` |
| **`no_hp`** | *Opsional* | Nomor handphone / WhatsApp siswa (format angka, +, - dengan panjang 8–20 digit). | `081234567890` |
| **`role`** | *Opsional* | Peran pengguna. Isi dengan `siswa` atau biarkan kosong (sistem default ke `siswa`). | `siswa` *(atau kosongkan)* |
| **`password`** | *Opsional* | Kata sandi awal. Jika dikosongkan, sistem otomatis memberikan password default: **`password`**. | `rahasia123` *(atau kosongkan)* |

---

## 📝 4. Contoh Isi File CSV Siap Pakai

Berikut contoh isi file jika dibuka di Text Editor / Notepad:

```csv
nama,email,nis,kelas,no_hp,role,password
Andi Pratama,andi@example.com,2401001,X IPA 1,081234567890,siswa,password123
Bela Sari,,2401002,X IPA 1,081222334455,siswa,
Chandra Wijaya,,2401003,X IPA 2,,siswa,
Dewi Lestari,dewi@gmail.com,2401004,X IPS 1,085712345678,siswa,
```

> 💡 **Unduh Template Resmi:**  
> Anda juga dapat langsung mengunduh template resmi sistem melalui tombol **"Download Template CSV"** yang terdapat di dalam modal Import pada halaman `admin/users`.

---

## ⚙️ 5. Pilihan Mode Import

Saat melakukan upload di modal import, terdapat 2 pilihan mode:

1. **Replace Siswa (Default)**:
   - Data siswa di file yang NIS-nya sudah ada akan diperbarui (*update*).
   - Data siswa di file yang NIS-nya baru akan didaftarkan (*create*).
   - **Siswa aktif lama yang NIS-nya TIDAK ADA di file import ini akan otomatis dinonaktifkan (`is_active = false`)**.  
   *Sangat cocok digunakan saat tahun ajaran baru di mana hanya siswa terdaftar yang aktif.*
2. **Upsert Only**:
   - Hanya menambah siswa baru dan memperbarui siswa yang ada.
   - Tidak akan menonaktifkan siswa lama yang tidak tercantum di file.  
   *Cocok digunakan jika hanya ingin menambah rombel baru atau data susulan.*

---

## 💡 6. Tips Jika Menggunakan Microsoft Excel

Di Indonesia, pengaturan regional Windows sering kali membuat Microsoft Excel menyimpan CSV menggunakan pemisah **titik koma (`;`)** bukan koma (`,`).

Jika mengalami error format saat upload:
1. Buka file CSV hasil simpan Excel menggunakan **Notepad**.
2. Periksa apakah pemisahnya menggunakan koma `,` atau titik koma `;`.
3. Jika menggunakan titik koma `;`, tekan `Ctrl + H` di Notepad:
   - *Find what:* `;`
   - *Replace with:* `,`
   - Klik **Replace All**, lalu simpan kembali (`Ctrl + S`).

---

## 🏢 7. Import Master Data Fasilitas (Gedung, Ruangan, Kategori)

Selain data pengguna, sistem SFCS juga memiliki menu khusus untuk import master data sekolah secara massal:
👉 **URL:** `http://127.0.0.1:8000/admin/master-data`  
*(Menu: **Sistem** ➔ **Master Data**)*

Fitur ini mendukung file **`.xlsx`**, **`.csv`**, maupun paket **`.zip`** dengan alur **Preview & Commit** yang aman sebelum perubahan diterapkan ke database.
