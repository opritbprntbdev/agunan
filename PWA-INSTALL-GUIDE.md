# 📱 CARA INSTALL PWA DI HP SAMSUNG

## ✅ PERSIAPAN (Sudah selesai)
- [x] manifest.json dibuat
- [x] Service Worker (sw.js) dibuat
- [x] Meta tags PWA ditambahkan
- [x] Icon generator tersedia

---

## 🎨 STEP 1: BUAT ICON (Opsional - bisa skip dulu)

1. Buka browser di PC/laptop: `http://localhost/agunan-capture/generate-icons.html`
2. Klik tombol "Download 192x192" dan "Download 512x512"
3. Simpan kedua file PNG ke folder `C:\wamp64\www\agunan-capture\assets\`
4. Rename file jadi:
   - `icon-192.png`
   - `icon-512.png`

**ATAU** skip dulu (pakai icon default browser)

---

## 🚀 STEP 2: JALANKAN CLOUDFLARE TUNNEL

Di terminal PowerShell:

```powershell
cd C:\cloudflared
.\cloudflared.exe tunnel --url http://localhost:80
```

Tunggu sampai muncul URL HTTPS, contoh:
```
https://entertaining-leads-pin-settlement.trycloudflare.com
```

**PENTING:** Catat URL ini!

---

## 📱 STEP 3: BUKA DI HP SAMSUNG

### A. Pakai Samsung Internet (Recommended)

1. Buka **Samsung Internet** browser
2. Ketik URL tunnel di address bar:
   ```
   https://your-tunnel-url.trycloudflare.com/agunan-capture/
   ```
3. Login dengan username/password

### B. Pakai Chrome Mobile

1. Buka **Chrome** app
2. Ketik URL yang sama
3. Login

---

## 💾 STEP 4: INSTALL PWA

### Samsung Internet:

1. Setelah login, lihat di **bottom bar** atau **menu (⋮)**
2. Cari opsi:
   - **"Add page to"** → **"Home screen"** ATAU
   - **"Install app"** ATAU
   - **"Add to Home screen"**
3. Klik → kasih nama "Agunan Capture"
4. Klik **"Add"** atau **"Install"**

### Chrome Mobile:

1. Klik **menu (⋮)** di pojok kanan atas
2. Pilih **"Add to Home screen"** atau **"Install app"**
3. Klik **"Add"** atau **"Install"**

**Popup install mungkin muncul otomatis:**
- Jika muncul banner "Install Agunan Capture?" → langsung klik **Install**

---

## ✅ STEP 5: TEST APP

1. Keluar dari browser
2. Lihat **Home Screen** HP → ada icon **Agunan Capture**
3. Tap icon → app buka **full screen** (tanpa address bar)
4. Login → klik **Batch Capture**
5. Izinkan akses kamera → **CAMERA HARUS JALAN!** ✅

---

## 🎯 YANG BERUBAH SETELAH INSTALL PWA:

| Sebelum (Browser) | Sesudah (PWA) |
|-------------------|---------------|
| Ada address bar | Full screen (no bar) |
| Icon browser | Icon custom "Agunan" |
| Tab browser | Standalone app |
| Buka via URL | Buka via icon home screen |
| **Camera:** Butuh HTTPS | **Camera:** Tetap jalan (HTTPS via tunnel) |

---

## 🔧 TROUBLESHOOTING:

### ❌ "Add to Home screen" tidak muncul?

**Solusi:**
1. Pastikan pakai **HTTPS** (tunnel harus running)
2. Clear cache browser:
   - Samsung Internet: Menu → Settings → Privacy → Delete browsing data
   - Chrome: Menu → Settings → Privacy → Clear browsing data
3. Reload halaman (Ctrl+R atau pull to refresh)
4. Tunggu 5-10 detik → menu install harusnya muncul

---

### ❌ Camera tidak bisa dibuka di PWA?

**Solusi:**
1. Buka **Settings** HP → **Apps** → **Agunan Capture** (atau Chrome/Samsung Internet)
2. Pilih **Permissions** → aktifkan **Camera**
3. Buka lagi PWA → test camera

---

### ❌ Icon tidak muncul (blank/default)?

**Solusi:**
- Generate icon lewat `generate-icons.html` (lihat Step 1)
- Upload `icon-192.png` dan `icon-512.png` ke folder `assets/`
- Uninstall PWA dari home screen
- Install ulang → icon harusnya muncul

---

## 🎉 SELESAI!

Sekarang kamu punya **native-like app** di HP Samsung:
- ✅ Install via browser (no APK)
- ✅ Full screen experience
- ✅ Camera langsung jalan (getUserMedia via HTTPS)
- ✅ Update instant (tinggal reload atau refresh server)
- ✅ Offline support (halaman ter-cache)

---

## 📝 CATATAN PENTING:

1. **Tunnel harus running** saat akses pertama kali
2. Setelah install, beberapa halaman bisa **offline** (ter-cache)
3. Untuk update app: buka PWA → pull to refresh
4. PWA tetap butuh **internet** untuk upload foto & generate PDF

---

## 🔄 CARA UNINSTALL PWA:

### Samsung:
1. Tahan icon di home screen
2. Pilih **"Remove"** atau **"Uninstall"**

### Chrome:
1. Settings HP → Apps → Chrome
2. Storage → Manage space → pilih site → Clear data

---

**Selamat mencoba! Kalau ada masalah, screenshot dan kasih tahu! 🚀**
