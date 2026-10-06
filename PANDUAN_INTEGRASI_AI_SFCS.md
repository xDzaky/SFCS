# 🤖 Dokumentasi Integrasi AI SFCS (Role-Based Context & Database API)

Dokumen ini diperuntukkan bagi **Developer AI** yang bertugas mengembangkan kecerdasan buatan (*chatbot / LLM / RAG / AI Agent*) untuk sistem **SFCS (School Facilities & Complaint System)**.

---

## 📌 Ringkasan Pembaruan
1. **Data Chatbot Lama Dinonaktifkan**: Seluruh respon teks hardcoded / rule-based lama di SFCS Assistant telah dibersihkan agar jawaban AI tidak lagi melenceng atau kaku.
2. **Endpoint Context Database Aktif**: Disediakan REST API yang mengekstrak data database SFCS dengan proteksi **Role-Based Access Control (RBAC)** yang ketat.
3. **Widget Chat Web Siap Terhubung**: Chatbot di frontend SFCS sudah siap mem-forward pesan user ke server AI Anda melalui konfigurasi `AI_SERVICE_URL`.

---

## 🌐 Alamat Base URL
Endpoint dapat diakses pada:
- **Pengembangan Lokal (Localhost):**  
  `http://127.0.0.1:8000/api/v1/ai`
- **Hosting InfinityFree (Sementara):**  
  `https://sfcs.freedev.app/api/v1/ai`
- **Server Sekolah (Produksi Nanti):**  
  `http://<domain-atau-ip-sekolah>/api/v1/ai`

---

## 🔐 Autentikasi API
Untuk memanggil endpoint API dari backend/service AI Anda, sertakan API Key pada HTTP Header:
```http
X-API-KEY: sfcs-ai-secret-2026
```
*(Atau menggunakan `Authorization: Bearer sfcs-ai-secret-2026`). Nilai API key ini dapat disesuaikan pada file `.env` di variabel `AI_API_KEY`.*

---

## 🛡️ Aturan Hak Akses Data Per Role (RBAC)

Setiap role memiliki batasan privasi data yang berbeda. API akan otomatis memfilter data sesuai role yang diminta:

| Role | Data yang Boleh Diakses (Allowed) | Data yang Dilarang (Prohibited / Blocked) |
| :--- | :--- | :--- |
| **`siswa`** | • Laporan pengaduan miliknya sendiri<br>• Pinjaman barang miliknya sendiri<br>• Katalog barang Sarpras yang tersedia (stok > 0)<br>• Daftar gedung & ruangan umum<br>• Kategori pengaduan & aturan sekolah | ❌ **DILARANG**: Data semua guru, nomor HP/kontak guru, data siswa lain, daftar akun/user, laporan pengaduan orang lain, log sistem. |
| **`guru`** | • Laporan pengaduan miliknya sendiri<br>• Pinjaman barang miliknya sendiri<br>• Katalog barang Sarpras & fasilitas sekolah | ❌ **DILARANG**: Data pribadi guru/siswa lain, data teknisi, tiket orang lain, data kredensial. |
| **`teknisi`** | • Tiket tugas pengaduan yang ditugaskan ke teknisi bersangkutan<br>• Statistik penyelesaian tugas teknisi<br>• Stok barang & suku cadang Sarpras<br>• Denah fasilitas & kategori kerusakan | ❌ **DILARANG**: Data gaji, data user yang tidak relevan dengan tugas perbaikan. |
| **`kepsek`** | • Statistik ringkasan seluruh pengaduan sekolah (agregat)<br>• Metrik performa penanganan & SLA overdue<br>• Rekap rating & feedback kepuasan<br>• Ringkasan sarpras | ❌ **DILARANG**: Password hash, token sistem, data pribadi yang tidak berhubungan dengan pelaporan manajerial. |
| **`admin`** / **`superadmin`** | • Rekapitulasi pengaduan lengkap & monitoring tiket aktif<br>• Status beban kerja & penumpukan (overload) teknisi<br>• Master inventaris Sarpras (Atas & Bawah)<br>• Daftar pengguna aktif (tersanitasi tanpa password) | ❌ **DILARANG**: Password hash, key enkripsi internal. |

---

## 📡 Daftar Endpoint API

### 1. Health Check
Memeriksa status kesiapan API.
- **Method:** `GET`
- **Path:** `/api/v1/ai/health`
- **Headers:** `X-API-KEY: sfcs-ai-secret-2026`
- **Response Contoh:**
```json
{
  "status": "ok",
  "app": "SFCS",
  "school_name": "SMA Negeri 1 Contoh",
  "timestamp": "2026-10-06T06:18:45+07:00",
  "message": "API Context SFCS untuk AI Developer siap digunakan."
}
```

---

### 2. Informasi Hak Akses Role
Melihat daftar role beserta daftar data yang diizinkan dan dilarang untuk dimasukkan ke system prompt model AI.
- **Method:** `GET`
- **Path:** `/api/v1/ai/roles`
- **Headers:** `X-API-KEY: sfcs-ai-secret-2026`

---

### 3. Ambil Full Context Database Sesuai Role
Endpoint utama untuk menginjeksi konteks database ke System Prompt LLM (RAG / Few-shot context).
- **Method:** `GET`
- **Path:** `/api/v1/ai/context?role={role}&user_id={id}`
- **Headers:** `X-API-KEY: sfcs-ai-secret-2026`
- **Query Parameters:**
  - `role`: `siswa` | `guru` | `teknisi` | `kepsek` | `admin` (wajib jika tanpa user_id)
  - `user_id`: ID user bersangkutan (opsional, jika ingin context pengaduan/pinjaman spesifik user tersebut)
- **Response Contoh (Role: `siswa`, user_id: 1):**
```json
{
  "status": "ok",
  "authenticated": {
    "role": "siswa",
    "user_id": 1,
    "user_name": "Andi Pratama"
  },
  "context": {
    "school_profile": {
      "nama": "SMA Negeri 1 Contoh",
      "jam_operasional": "Senin - Sabtu: 07.00 - 16.00 WIB",
      "jam_pulang_sarpras": "15.20 WIB (Batas pengembalian barang pinjaman Sarpras Atas)",
      "aturan_sarpras_atas": "Peminjaman alat (proyektor, kabel, mic, dll) harus dikembalikan pada hari yang sama paling lambat jam 15.20 WIB.",
      "aturan_sarpras_bawah": "Permintaan barang habis pakai (kertas HVS, spidol, ATK) tidak perlu dikembalikan setelah disetujui."
    },
    "kategori_pengaduan": [
      "Kelistrikan", "Plumbing", "Furniture", "AC & Pendingin", "Bangunan", "IT & Multimedia"
    ],
    "daftar_gedung": [
      "Gedung A - Administrasi", "Gedung B - Kelas X", "Gedung C - Kelas XI", "Gedung E - Laboratorium"
    ],
    "hak_akses": "User Biasa (Siswa). Data dibatasi hanya untuk laporan dan pinjaman milik sendiri.",
    "my_pengaduan_terbaru": [
      {
        "kode": "ADU-20260414-001",
        "judul": "AC Mati di Lab Komputer",
        "lokasi": "Gedung E - Lab Komputer 2",
        "prioritas": "tinggi",
        "status": "diproses",
        "created_at": "14 Apr 2026"
      }
    ],
    "my_pinjaman_aktif": [],
    "katalog_barang_tersedia": [
      {
        "nama": "Proyektor Epson X1",
        "unit": "Sarpras Atas (Peminjaman)",
        "stok_tersedia": 6
      },
      {
        "nama": "Kabel HDMI 10m",
        "unit": "Sarpras Atas (Peminjaman)",
        "stok_tersedia": 15
      },
      {
        "nama": "Spidol Whiteboard (pak)",
        "unit": "Sarpras Bawah (Permintaan)",
        "stok_tersedia": 30
      }
    ]
  }
}
```

---

### 4. Query Database Terarah (Focused Query)
Endpoint untuk mencari data tertentu secara spesifik (misal pengecekan barang atau pengaduan tertentu).
- **Method:** `POST`
- **Path:** `/api/v1/ai/query`
- **Headers:**
  - `X-API-KEY: sfcs-ai-secret-2026`
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Request Body JSON:**
```json
{
  "role": "siswa",
  "user_id": 1,
  "action": "check_barang",
  "keyword": "proyektor"
}
```
*Pilihan `action`:*
- `check_barang`: Mencari daftar barang Sarpras yang tersedia.
- `check_pengaduan`: Melacak tiket pengaduan (hanya tiket milik user jika role siswa/guru).
- `check_pinjaman`: Melihat riwayat atau status pinjaman barang.
- `check_fasilitas`: Menampilkan daftar gedung dan ruangan sekolah.
- `summary`: Menampilkan rekap ringkas angka statistik sesuai role.

- **Response Contoh:**
```json
{
  "status": "ok",
  "action": "check_barang",
  "role": "siswa",
  "count": 2,
  "data": [
    {
      "id": 1,
      "nama": "Proyektor Epson X1",
      "unit_sarpras": "Sarpras Atas (Peminjaman)",
      "kategori": "Multimedia",
      "stok_tersedia": 6
    },
    {
      "id": 4,
      "nama": "Proyektor Epson EB-X51",
      "unit_sarpras": "Sarpras Atas (Peminjaman)",
      "kategori": "Elektronik",
      "stok_tersedia": 5
    }
  ]
}
```

---

## 🔄 Cara Menghubungkan Backend AI Teman ke Web SFCS

Ketika user mengetik pesan di bubble chat website SFCS, sistem SFCS dapat langsung meneruskan pesan ke server AI Anda.

### Langkah-langkah:
1. Jalankan server AI Anda (misalnya di `http://127.0.0.1:5000/chat` atau URL publik VPS/Cloud Anda).
2. Di file `.env` proyek SFCS, atur variabel `AI_SERVICE_URL`:
   ```dotenv
   AI_SERVICE_URL=http://127.0.0.1:5000/chat
   ```
3. Saat user mengirim chat di web, controller SFCS akan mengirim HTTP `POST` ke `AI_SERVICE_URL` Anda dengan format:
   ```json
   {
     "message": "Halo, saya mau tanya apakah ada proyektor yang bisa dipinjam hari ini?",
     "user": {
       "id": 1,
       "name": "Andi Pratama",
       "role": "siswa"
     },
     "context": {
       "school_profile": { ... },
       "katalog_barang_tersedia": [ ... ],
       "my_pengaduan_terbaru": [ ... ]
     }
   }
   ```
4. Server AI Anda cukup membalas dengan format JSON sederhana:
   ```json
   {
     "reply": "Halo Andi! Ya, saat ini tersedia 6 unit Proyektor Epson X1 di Sarpras Atas. Jangan lupa bahwa pengembalian barang pinjaman Sarpras Atas maksimal pukul 15.20 WIB ya! 😊",
     "type": "text",
     "actions": [
       { "label": "📦 Pinjam Proyektor Sekarang", "url": "/pinjaman/create" }
     ]
   }
   ```

---

## 🐍 Contoh Boilerplate Server AI (Python FastAPI)

Berikut adalah contoh script server Python (`ai_service.py`) yang siap Anda jalankan dan kembangkan:

```python
from fastapi import FastAPI, Request
import uvicorn
import requests

app = FastAPI(title="SFCS AI Backend Service")

SFCS_API_URL = "http://127.0.0.1:8000/api/v1/ai"
SFCS_API_KEY = "sfcs-ai-secret-2026"

def build_system_prompt(role: str, user_name: str, context: dict) -> str:
    return f"""
Anda adalah 'SFCS Assistant', asisten AI resmi untuk Sekolah SFCS.
Anda sedang berbicara dengan: {user_name} (Role: {role}).

ATURAN PRIVASI & KEAMANAN:
1. Jika user memiliki role 'siswa' atau 'guru', Anda DILARANG KERAS memberikan informasi pribadi guru lain, data siswa lain, nomor handphone orang lain, atau data akun sistem. Jika diminta, tolak dengan sopan.
2. Jawablah hanya berdasarkan data konteks sekolah di bawah ini. Jangan mengarang data fasilitas atau stok barang yang tidak ada.
3. Jam operasional sekolah: {context.get('school_profile', {}).get('jam_operasional')}. Batas pengembalian pinjaman Sarpras Atas adalah pukul 15.20 WIB.

KONTEKS DATABASE SFCS:
{context}
"""

@app.post("/chat")
async def chat(request: Request):
    payload = await request.json()
    user_message = payload.get("message", "")
    user_info = payload.get("user", {})
    context_data = payload.get("context", {})

    role = user_info.get("role", "siswa")
    user_name = user_info.get("name", "User")

    system_prompt = build_system_prompt(role, user_name, context_data)

    # TODO: Panggil LLM favorit Anda di sini (Gemini / OpenAI / Ollama / DeepSeek)
    # Contoh jawaban cerdas:
    ai_reply = f"Halo {user_name}! Saya menerima pesan Anda: '{user_message}'. Data konteks role ({role}) berhasil disinkronkan."

    return {
        "reply": ai_reply,
        "type": "text",
        "actions": []
    }

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=5000)
```

---

## 🚀 Catatan Deployment di InfinityFree
- InfinityFree menggunakan hosting PHP standar (Apache dengan `.htaccess`).
- Endpoint API ini sudah sepenuhnya kompatibel tanpa membutuhkan background worker atau ekstensi PHP khusus.
- Ketika dideploy ke `https://sfcs.freedev.app`, server AI Anda cukup memanggil URL `https://sfcs.freedev.app/api/v1/ai/context` dengan API Key yang sama.
