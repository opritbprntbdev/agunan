# FOLDER PATROLI SECURITY

Folder ini berisi semua file untuk sistem **Patroli Penjaga Malam**.

## 📁 Struktur Folder:

```
/patroli/
├── config_patroli.php           # Koneksi DB patroli_security
├── migration_patroli_security.sql  # SQL create database & tables
│
├── /ui/                         # Halaman frontend
│   ├── patroli_login.php        # Login penjaga/admin
│   ├── patroli_home.php         # Home sesuai role
│   ├── patroli_scan.php         # Scan QR + Selfie (penjaga)
│   ├── patroli_my_history.php   # History scan (penjaga)
│   ├── patroli_manage_room.php  # Kelola ruangan + Generate QR (admin)
│   ├── patroli_report.php       # Report monitoring (admin)
│   └── patroli_manage_user.php  # Kelola user (admin)
│
├── /process/                    # Backend API
│   ├── patroli_scan_save.php    # Save hasil scan + watermark
│   ├── patroli_generate_qr.php  # Generate QR codes
│   └── patroli_check_schedule.php # API cek jadwal
│
├── /qr-codes/                   # QR code images
│   ├── /028/                    # Per kode kantor
│   │   ├── R01.png
│   │   └── ...
│   └── /029/
│
└── /patroli-photos/             # Foto hasil patroli
    └── /2025/
        └── /11/
            ├── 028-27-11-2025-210045123.jpg
            └── 028-27-11-2025-210045123_watermarked.jpg
```

## 🔐 Database:

**Nama:** `patroli_security`

**Tables:**
1. `users_security` - User penjaga + admin pusat
2. `ruangan` - Master ruangan per cabang
3. `jadwal_patroli` - Jadwal patroli per cabang
4. `patroli_log` - Log hasil scan
5. `notifikasi_log` - Log notifikasi

## 🚀 Setup:

1. Import SQL: `migration_patroli_security.sql`
2. Cek koneksi: `config_patroli.php`
3. Set permission folder:
   - `qr-codes/` → 755 atau 777
   - `patroli-photos/` → 755 atau 777

## 📝 Default Login:

**Admin Pusat:**
- Username: `admin`
- Password: `admin123`

**Penjaga KC 028:**
- Username: `penjaga028`
- Password: `penjaga123`

---

**Terpisah dari agunan-capture untuk kemudahan maintenance!**
