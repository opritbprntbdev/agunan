# Agunan Capture - Mobile PWA dengan Integrasi Core Banking IBS

**Sistem Foto Digital Agunan untuk BPR**  
Capture foto agunan langsung dari HP, verifikasi data dari Core Banking IBS, auto-generate PDF

---

## 🎯 Fitur Utama

### ✅ 1. Verifikasi Agunan dari Core Banking IBS
- **Input Agunan ID** → otomatis ambil data dari database IBS
- **JOIN Multi-Tabel**: `kre_agunan` → `kre_agunan_relasi` → `kredit` → `nasabah`
- **Data Lengkap**: CIF, Nama Nasabah, Alamat, No Rekening, Detail Tanah/Kendaraan
- **Backend API**: `process/verify_agunan.php`

### ✅ 2. Batch Photo Capture (CamScanner Style)
- **Multi-shot**: Ambil banyak foto dalam 1 batch (max 20)
- **Review & Reorder**: Atur urutan foto sebelum simpan
- **Sequential Upload**: Upload foto satu per satu dengan progress bar
- **Mobile-First UI**: Optimized untuk HP (landscape/portrait)

### ✅ 3. Auto PDF Generation
- **1 Agunan = 1 PDF**: Semua foto jadi satu file PDF
- **Layout**: 1 foto per halaman, center-fit A4
- **Auto Storage**: `pdf/YYYY/MM/agunan_{id}_{timestamp}.pdf`
- **FPDF Library**: Server-side processing (tidak membebani HP)

### ✅ 4. PWA (Progressive Web App)
- **Installable**: Bisa di-install ke home screen HP
- **Fullscreen Mode**: Tanpa address bar browser
- **Offline Ready**: Service Worker caching
- **Push Notifications**: Notifikasi saat agunan berhasil disimpan

### ✅ 5. Login History & Tracking
- **User Activity**: Track login/logout, IP address, device info
- **Session Duration**: Hitung durasi penggunaan
- **Fotografer Info**: Siapa yang foto, kapan, pakai device apa
- **Database**: `user_login_history`, `agunan_data.photo_taken_by`

### ✅ 6. HTTPS via Cloudflare Tunnel
- **Remote Access**: Akses dari mana saja via internet
- **Camera Support**: getUserMedia butuh HTTPS di mobile
- **Free Tier**: Tidak perlu bayar hosting

---

## 📁 Struktur File

```
agunan-capture/
├── index.php                   # Login page
├── home.php                    # Main menu
├── login_proses.php            # Login handler + history tracking
├── logout.php                  # Logout handler + session duration
├── config.php                  # Database config (local + IBS)
├── manifest.json               # PWA manifest
├── sw.js                       # Service Worker (v2)
│
├── ui/
│   ├── capture_batch.php       # ⭐ Batch capture UI (RECOMMENDED)
│   └── history.php             # Daftar agunan + preview/download PDF
│
├── process/
│   ├── verify_agunan.php       # ⭐ API verifikasi data dari IBS
│   ├── upload_photo.php        # Upload handler + save IBS data
│   └── finalize_batch.php      # PDF generation + notification
│
├── assets/
│   ├── icon-192.svg            # PWA icon (192x192)
│   ├── icon-512.svg            # PWA icon (512x512)
│   └── css/
│       ├── style.css
│       └── mobile.css
│
├── uploads/                    # Foto storage
│   └── YYYY/MM/*.jpg
│
├── pdf/                        # PDF storage
│   └── YYYY/MM/*.pdf
│
└── vendor/
    └── fpdf/                   # PDF library
```

---

## 🗄️ Database Schema

### **Tabel: `agunan_data`** (Main table)
```sql
- id (PK)
- id_agunan (lokal)
- agunan_id_ibs (dari IBS) ⭐ NEW
- kode_jenis_agunan (5=SHM, 6=SHGB) ⭐ NEW
- nama_nasabah
- no_rek
- cif ⭐ NEW
- alamat ⭐ NEW
- deskripsi_ringkas ⭐ NEW
- tanah_* (no_shm, no_shgb, luas, pemilik, lokasi) ⭐ NEW
- kend_* (jenis, merk, tahun, no_polisi) ⭐ NEW
- verified_from_ibs (0/1) ⭐ NEW
- verified_at, verified_by ⭐ NEW
- photo_taken_by, photo_taken_at ⭐ NEW
- pdf_filename, pdf_path
- created_by, nama_kc
- created_at, updated_at
```

### **Tabel: `agunan_foto`**
```sql
- id (PK)
- agunan_data_id (FK)
- foto_filename, foto_path
- foto_order
- uploaded_by ⭐ NEW
- uploaded_at ⭐ NEW
- file_size, keterangan
```

### **Tabel: `user`**
```sql
- id (PK)
- username, password, nama_kc
- last_login_at, last_login_ip ⭐ NEW
- login_count ⭐ NEW
- created_at, updated_at ⭐ NEW
```

### **Tabel: `user_login_history`** ⭐ NEW
```sql
- id (PK)
- user_id, username
- login_at, logout_at
- ip_address, user_agent
- login_status (success/failed)
- session_duration
```

### **View: `vw_agunan_with_ibs`** ⭐ NEW
Gabungan data lokal + data IBS untuk reporting

---

## 🚀 Cara Pakai

### **1. Setup Database**
```bash
# Jalankan migration SQL di Navicat:
migration_add_ibs_columns.sql
```

### **2. Konfigurasi**
Edit `config.php`:
```php
// Database Lokal (Agunan Capture)
$host = "localhost";
$db_port = 3308;
$db = "agunan_capture";

// Database IBS (Core Banking)
$servername_dbibs = "191.69.1.1";
$dbname_dbibs = "bprntb";
$dbport_dbibs = "3306";
```

### **3. Start Cloudflare Tunnel** (untuk HTTPS)
```powershell
C:\cloudflared.exe tunnel --url http://localhost:80
```
Catat URL yang muncul: `https://xxx-yyy-zzz.trycloudflare.com`

### **4. Akses dari HP**
1. Buka URL Cloudflare Tunnel di browser HP
2. Login (username/password dari tabel `user`)
3. Menu → Batch Capture
4. **Input Agunan ID** (contoh: `000000001`)
5. Klik **Verifikasi** → data muncul dari IBS
6. Foto berkas agunan (multiple shots)
7. Review → Simpan
8. PDF auto-generate → Notifikasi muncul! 🔔

### **5. Install PWA** (Optional)
1. Browser menu → **Add to Home Screen** / **Install**
2. Buka dari icon di home screen (bukan browser)
3. Fullscreen mode aktif (tanpa address bar)

---

## 🎨 User Flow

```
┌─────────────────────────────────────────────────┐
│  LOGIN (index.php)                              │
│  - Username/Password                            │
│  - Track: IP, User Agent, Login Time            │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│  HOME MENU (home.php)                           │
│  - Batch Capture (recommended)                  │
│  - Riwayat/History                              │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│  BATCH CAPTURE (ui/capture_batch.php)           │
│  ┌───────────────────────────────────────────┐  │
│  │ 1. INPUT AGUNAN ID                        │  │
│  │    └→ Klik VERIFIKASI                     │  │
│  │       └→ API: verify_agunan.php           │  │
│  │          └→ Query IBS (4 JOIN tables)     │  │
│  └───────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────┐  │
│  │ 2. DATA AGUNAN MUNCUL ✅                  │  │
│  │    - CIF: 12345678                        │  │
│  │    - Nama: Budi Santoso                   │  │
│  │    - Alamat: Jl. Merdeka No. 10           │  │
│  │    - No Rek Kredit: 1234567890            │  │
│  │    - Jenis: Tanah SHM                     │  │
│  │    - No SHM: 190/MAPIN REA/2016          │  │
│  │    - Luas: 18100.00 m²                    │  │
│  └───────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────┐  │
│  │ 3. FOTO BERKAS                            │  │
│  │    📷 [Buka Kamera]                       │  │
│  │    → Ambil foto (multiple)                │  │
│  │    → Review & Reorder                     │  │
│  └───────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────┐  │
│  │ 4. SIMPAN SEMUA                           │  │
│  │    → Sequential upload (1-by-1)           │  │
│  │    → Progress bar: 3/5                    │  │
│  │    → Save to: uploads/2025/11/            │  │
│  │    → DB: agunan_data (verified_from_ibs=1)│  │
│  └───────────────────────────────────────────┘  │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│  PDF GENERATION (process/finalize_batch.php)    │
│  - FPDF: 1 foto per page, A4, center-fit       │
│  - Save: pdf/2025/11/agunan_xxx_20251106.pdf   │
│  - Update DB: pdf_path, pdf_filename           │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│  🔔 PUSH NOTIFICATION                           │
│  "✅ Agunan Berhasil Disimpan"                  │
│  "budi menyimpan agunan AGN001 (5 foto)"       │
│  - Vibrate: [200, 100, 200]                    │
│  - Icon: bank emoji 🏦                         │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│  HISTORY (ui/history.php)                       │
│  - Card list semua agunan                      │
│  - Preview PDF (inline)                        │
│  - Download PDF                                │
│  - Badge: Verified ✅ / Manual                 │
└─────────────────────────────────────────────────┘
```

---

## 🔧 Konfigurasi Tambahan

### **PHP Settings** (`php.ini`)
```ini
upload_max_filesize = 64M
post_max_size = 64M
max_file_uploads = 50
max_execution_time = 300
memory_limit = 256M
```

### **Apache Settings** (`.htaccess` - optional)
```apache
# Enable rewrite
RewriteEngine On

# Force HTTPS (jika pakai SSL sendiri, bukan Cloudflare)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### **Folder Permissions**
```bash
chmod 777 uploads/
chmod 777 pdf/
```

---

## 📊 Monitoring & Reporting

### **Query Login History**
```sql
-- Lihat semua login user
SELECT * FROM user_login_history 
ORDER BY login_at DESC LIMIT 20;

-- Rata-rata durasi session
SELECT AVG(session_duration)/60 AS avg_minutes 
FROM user_login_history 
WHERE session_duration IS NOT NULL;

-- Failed login attempts
SELECT * FROM user_login_history 
WHERE login_status = 'failed' 
ORDER BY login_at DESC;
```

### **Query Agunan Verified**
```sql
-- Lihat agunan yang verified dari IBS
SELECT * FROM vw_agunan_with_ibs 
WHERE verified_from_ibs = 1 
ORDER BY created_at DESC;

-- Count by photographer
SELECT photo_taken_by, COUNT(*) as total 
FROM agunan_data 
WHERE photo_taken_by IS NOT NULL 
GROUP BY photo_taken_by;
```

---

## 🛠️ Troubleshooting

### **Camera tidak buka di HP**
- ✅ **Solusi**: Harus pakai HTTPS (gunakan Cloudflare Tunnel)
- Cek: `getUserMedia` butuh secure context

### **Notifikasi tidak muncul**
- ✅ **Solusi**: Buka dari PWA installed app (bukan browser)
- Allow notification permission saat pertama buka

### **Upload gagal: JSON error**
- ✅ **Solusi**: Cek PHP error di `C:\wamp64\logs\apache_error.log`
- Biasanya masalah bind_param atau folder permission

### **PDF tidak generate**
- ✅ **Solusi**: Cek folder `pdf/` writable (chmod 777)
- Cek FPDF library ada di `vendor/fpdf/`

### **Verifikasi error: column not found**
- ✅ **Solusi**: Jalankan `migration_add_ibs_columns.sql`
- Cek JOIN query di `verify_agunan.php`

---

## 🎯 Next Development

### **Roadmap (Future)**
- [ ] Geolocation API - GPS coordinates saat foto
- [ ] Image compression - resize otomatis di client
- [ ] Multi-user notification - broadcast ke semua user
- [ ] Export Excel - laporan agunan per periode
- [ ] QR Code - scan agunan ID dari sticker
- [ ] Signature capture - tanda tangan digital debitur

### **Production Deployment**
- [ ] Named Cloudflare Tunnel (stable URL)
- [ ] SSL Certificate (jika self-hosted)
- [ ] Backup database automation
- [ ] User management (add/edit/delete user)
- [ ] Role-based access control (admin/user)

---

## 📝 Credits

**Developed by**: AI Assistant (GitHub Copilot)  
**Date**: November 6, 2025  
**Version**: 2.0 (with IBS Integration + PWA)  
**Tech Stack**: PHP 8.1, MySQL 5.7, JavaScript ES6, FPDF, Service Worker API

**Database IBS**: Core Banking System  
**Cloudflare Tunnel**: HTTPS Access  
**PWA**: Progressive Web App Standard  

---

## 📞 Support

Untuk pertanyaan atau bug report, hubungi IT Department BPR.

**Happy Capturing! 📸🏦**
