Agunan Capture - Mobile UI + Process Split
=========================================

Ringkas
- UI kamera baru: ui/capture.php
- UI batch capture (rekomendasi): ui/capture_batch.php
- Endpoint proses upload: process/upload_photo.php
- Finalisasi batch (buat PDF): process/finalize_batch.php
- Penyimpanan foto: uploads/YYYY/MM/foto_{agunan_data_id}_{order}_{timestamp}.jpg
- DB: Insert ke agunan_data (jika baru) dan agunan_foto; total_foto ikut diupdate
- pakai cloud fire untuk HTTPS : unuk aksesnya saya taruh cloud fire di download / C caranya : 
.\cloudflared.exe tunnel --url http://localhost:80 - jika wamp tunel 80 portnya
.\cloudflared.exe tunnel --url http://localhost:8080 - jika wamp tunel 8080 portnya
Cara pakai cepat
1) Login di index.php
2) Buka UI batch: /agunan-capture/ui/capture_batch.php (disarankan)
	atau UI kamera tunggal: /agunan-capture/ui/capture.php
3) Isi ID Agunan, Nama Nasabah, No Rek
4) Ambil foto → Upload. Foto berikutnya otomatis pakai record yang sama.

Catatan kamera/flash (torch)
- Kamera via getUserMedia butuh Secure Context:
	- Desktop: http://localhost boleh
	- Mobile (akses dari HP ke PC LAN): gunakan HTTPS (self-signed) di host PC
- Torch/flash web hanya didukung di sebagian perangkat (Chrome Android tertentu). iOS Safari belum mendukung pengaturan torch langsung.

Struktur baru
- ui/ → halaman tampilan (UI) minimalis untuk kamera
- ui/history.php → daftar batch + Preview/Download PDF
- process/ → PHP endpoint proses simpan file dan insert DB
- process/finalize_batch.php → membuat PDF otomatis

Konfigurasi
- Koneksi DB ada di config.php (pakai port 3308 sesuai WAMP lokal)
- Pastikan folder uploads dapat ditulis web server
- Jika ukuran foto mengikuti kamera HP, naikkan batas di php.ini (restart Apache):
	- upload_max_filesize: 64M (atau sesuai kebutuhan)
	- post_max_size: 64M
	- max_file_uploads: 50

Next
- Validasi & batas ukuran gambar di sisi server (opsional)
- Drag & drop reorder di halaman review (saat ini tombol naik/turun)
