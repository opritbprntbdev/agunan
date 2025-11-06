<?php
session_start();
if (!isset($_SESSION['login'])) {
  header('Location: ../index.php');
  exit;
}
$nama_kc = $_SESSION['nama_kc'] ?? '';
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Batch Capture Agunan</title>
  <style>
    * {
      box-sizing: border-box
    }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: #f5f5f5;
      color: #333
    }

    header {
      padding: 12px 16px;
      background: #2563eb;
      color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center
    }

    .wrap {
      padding: 12px;
      max-width: 900px;
      margin: 0 auto
    }

    .card {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, .1)
    }

    .head {
      padding: 12px;
      border-bottom: 1px solid #eee;
      display: flex;
      gap: 8px;
      flex-wrap: wrap
    }

    .input {
      flex: 1;
      min-width: 160px;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid #ddd;
      background: #fff;
      color: #333
    }

    .btn {
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid #2563eb;
      background: #2563eb;
      color: #fff;
      cursor: pointer
    }

    .btn.secondary {
      background: #fff;
      border-color: #ddd;
      color: #333
    }

    .btn.danger {
      background: #dc2626;
      border-color: #dc2626
    }

    .btn.success {
      background: #16a34a;
      border-color: #16a34a
    }

    .btn:disabled {
      opacity: 0.5;
      cursor: not-allowed
    }

    video,
    canvas,
    img {
      width: 100%;
      max-height: 55vh;
      background: #000;
      border-radius: 10px
    }

    .verified-badge {
      background: #dcfce7;
      color: #16a34a;
      padding: 6px 10px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 4px
    }

    .info-box {
      background: #f0f9ff;
      border: 1px solid #bae6fd;
      border-radius: 10px;
      padding: 12px;
      margin-top: 8px;
      font-size: 13px;
      line-height: 1.5
    }

    .info-box strong {
      color: #0284c7
    }

    .spinner {
      display: inline-block;
      width: 14px;
      height: 14px;
      border: 2px solid #fff;
      border-top-color: transparent;
      border-radius: 50%;
      animation: spin 0.6s linear infinite
    }

    @keyframes spin {
      to {
        transform: rotate(360deg)
      }
    }

    .toolbar {
      display: flex;
      gap: 8px;
      justify-content: center;
      align-items: center;
      padding: 10px;
      border-top: 1px solid #eee
    }

    .hint {
      font-size: 12px;
      color: #666;
      text-align: center;
      margin: 6px 0
    }

    .thumbs {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 8px
    }

    .thumb {
      border: 1px solid #ddd;
      border-radius: 10px;
      overflow: hidden;
      background: #fff
    }

    .thumb img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      display: block
    }

    .thumb .controls {
      display: flex;
      gap: 6px;
      justify-content: space-between;
      padding: 6px
    }

    .badge {
      padding: 4px 8px;
      border-radius: 999px;
      background: #f5f5f5;
      border: 1px solid #ddd;
      font-size: 12px;
      color: #666
    }

    .center {
      display: flex;
      gap: 12px;
      justify-content: center;
      align-items: center
    }

    .progress {
      height: 10px;
      background: #e5e7eb;
      border: 1px solid #ddd;
      border-radius: 999px;
      overflow: hidden
    }

    .bar {
      height: 100%;
      background: #22c55e;
      width: 0%
    }
  </style>
</head>

<body>
  <header>
    <div>📷 Batch Capture Agunan</div>
    <div style="font-size:12px;opacity:.9">User: <?= htmlspecialchars($username) ?> · KC:
      <?= htmlspecialchars($nama_kc) ?>
    </div>
  </header>
  <div class="wrap">
    <div class="card">
      <div class="head">
        <input class="input" id="agunan_id_input" placeholder="ID Agunan dari IBS (contoh: 000000001)" required
          style="flex:2">
        <button class="btn success" id="verifyBtn" type="button">🔍 Verifikasi</button>
      </div>
      <div id="verifiedInfo" style="padding:12px;display:none">
        <span class="verified-badge">✅ Verified dari IBS</span>
        <div class="info-box" id="agunanInfo"></div>
      </div>
      <div class="head" id="manualInputs" style="display:none">
        <input class="input" id="id_agunan" placeholder="ID Agunan (lokal)" required>
        <input class="input" id="nama_nasabah" placeholder="Nama Nasabah" required>
        <input class="input" id="no_rek" placeholder="No Rekening" required>
      </div>
      <div style="padding:12px">
        <video id="preview" playsinline autoplay muted></video>
        <canvas id="canvas" hidden></canvas>
        <div class="hint" id="hint">1. Masukkan ID Agunan → klik Verifikasi untuk ambil data dari IBS. Atau skip
          verifikasi untuk input manual.</div>
      </div>
      <div class="toolbar">
        <button class="btn secondary" id="switchBtn">🔁 Ganti</button>
        <button class="btn" id="captureBtn" disabled>📸 Ambil</button>
        <button class="btn secondary" id="finishBtn" disabled>✅ Selesai</button>
        <span class="badge" id="status">Verifikasi agunan terlebih dahulu</span>
        <span class="badge">Foto: <span id="count">0</span>/20</span>
      </div>
    </div>

    <div class="card" id="reviewCard" hidden>
      <div style="padding:12px;display:flex;justify-content:space-between;align-items:center">
        <div class="center"><span class="badge" id="reviewInfo">Review batch</span></div>
        <div class="center">
          <button class="btn secondary" id="backToCamera">↩️ Kembali</button>
          <button class="btn" id="saveAllBtn">⬆️ Simpan Semua</button>
        </div>
      </div>
      <div style="padding:12px">
        <div class="thumbs" id="thumbs"></div>
        <div class="hint">Urutkan dengan tombol ⬆️⬇️, atau hapus jika tidak perlu.</div>
      </div>
      <div style="padding:12px">
        <div class="progress">
          <div class="bar" id="bar"></div>
        </div>
        <div class="center" style="margin-top:6px"><span class="badge" id="progressTxt">0/0</span></div>
      </div>
    </div>

    <p class="hint">Semua pengolahan berat (simpan file dan pembuatan PDF) dilakukan di server. Halaman ini hanya
      menangkap dan mengirim foto secara sekuensial.</p>
  </div>

  <script>
    let stream, track, usingBack = true;
    const limit = 20;
    const queue = []; // {dataUrl}
    let agunanDataId = null;

    // Data agunan dari IBS (akan diisi setelah verifikasi)
    let verifiedData = null;

    const els = {
      agunanIdInput: document.getElementById('agunan_id_input'),
      verifyBtn: document.getElementById('verifyBtn'),
      verifiedInfo: document.getElementById('verifiedInfo'),
      agunanInfo: document.getElementById('agunanInfo'),
      manualInputs: document.getElementById('manualInputs'),
      idAgunan: document.getElementById('id_agunan'),
      namaNasabah: document.getElementById('nama_nasabah'),
      noRek: document.getElementById('no_rek'),
      preview: document.getElementById('preview'),
      canvas: document.getElementById('canvas'),
      hint: document.getElementById('hint'),
      status: document.getElementById('status'),
      count: document.getElementById('count'),
      switchBtn: document.getElementById('switchBtn'),
      captureBtn: document.getElementById('captureBtn'),
      finishBtn: document.getElementById('finishBtn'),
      reviewCard: document.getElementById('reviewCard'),
      thumbs: document.getElementById('thumbs'),
      reviewInfo: document.getElementById('reviewInfo'),
      backToCamera: document.getElementById('backToCamera'),
      saveAllBtn: document.getElementById('saveAllBtn'),
      bar: document.getElementById('bar'),
      progressTxt: document.getElementById('progressTxt')
    };

    // Fungsi Verifikasi Agunan ke IBS
    async function verifyAgunan() {
      const agunanId = els.agunanIdInput.value.trim();

      if (!agunanId) {
        alert('Masukkan ID Agunan terlebih dahulu');
        els.agunanIdInput.focus();
        return;
      }

      // Disable button & show loading
      els.verifyBtn.disabled = true;
      els.verifyBtn.innerHTML = '<span class="spinner"></span> Verifikasi...';

      try {
        const formData = new FormData();
        formData.append('agunan_id', agunanId);

        const response = await fetch('../process/verify_agunan.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
          // Data ditemukan di IBS
          verifiedData = result.data;

          // Tampilkan info agunan
          els.verifiedInfo.style.display = 'block';
          els.agunanInfo.innerHTML = formatAgunanInfo(result.data);

          // Sembunyikan input manual
          els.manualInputs.style.display = 'none';

          // Enable tombol foto
          els.captureBtn.disabled = false;
          els.finishBtn.disabled = false;
          els.status.textContent = 'Verified ✅ - Siap foto';
          els.hint.textContent = '2. Data agunan berhasil diverifikasi dari IBS. Mulai foto berkas agunan.';

          // Auto-open camera
          openCamera();

        } else {
          // Data tidak ditemukan atau error
          if (result.not_found) {
            // Agunan tidak ditemukan, tawarkan input manual
            if (confirm(result.message + '\n\nLanjut input manual?')) {
              switchToManualMode();
            }
          } else {
            // Error koneksi IBS
            alert(result.message + '\n\nSilakan coba lagi atau input manual.');
            if (confirm('Lanjut dengan input manual?')) {
              switchToManualMode();
            }
          }
        }

      } catch (error) {
        console.error('Verify error:', error);
        alert('Gagal verifikasi agunan: ' + error.message);
      } finally {
        // Reset button
        els.verifyBtn.disabled = false;
        els.verifyBtn.innerHTML = '🔍 Verifikasi';
      }
    }

    // Format info agunan untuk ditampilkan
    function formatAgunanInfo(data) {
      let html = '';

      // Agunan ID & No Alternatif
      html += '<strong>Agunan ID:</strong> ' + data.agunan_id + '<br>';
      if (data.no_alternatif_agunan && data.no_alternatif_agunan !== data.agunan_id) {
        html += '<strong>No. Alternatif Agunan:</strong> ' + data.no_alternatif_agunan + '<br>';
      }

      // No Rekening Kredit
      if (data.no_rekening_kredit) {
        html += '<strong>No. Rekening Kredit:</strong> ' + data.no_rekening_kredit + '<br>';
      }

      // CIF
      if (data.cif) {
        html += '<strong>CIF:</strong> ' + data.cif + '<br>';
      }

      // Nama Nasabah
      if (data.nama_nasabah) {
        html += '<strong>Nama Nasabah:</strong> ' + data.nama_nasabah + '<br>';
      }

      // Alamat
      if (data.alamat) {
        html += '<strong>Alamat:</strong> ' + data.alamat + '<br>';
      }

      // Jenis Agunan
      if (data.jenis_agunan) {
        html += '<strong>Jenis:</strong> ' + data.jenis_agunan + '<br>';
      }

      // Deskripsi Ringkas
      if (data.deskripsi_ringkas) {
        html += '<strong>Deskripsi Ringkas:</strong> ' + data.deskripsi_ringkas + '<br>';
      }

      // Verifikasi Status
      html += '<strong>Verifikasi:</strong> ✅ Verified dari IBS<br>';

      // Detail Tanah atau Kendaraan
      if (data.kode_jenis_agunan === '5' || data.kode_jenis_agunan === '6') {
        // Tanah
        html += '<hr style="margin:8px 0;border:none;border-top:1px solid #ddd">';
        html += '<strong style="color:#0284c7">📋 Detail Agunan Tanah:</strong><br>';

        if (data.tanah_no_shm) html += '<strong>No. SHM:</strong> ' + data.tanah_no_shm + '<br>';
        if (data.tanah_no_shgb) html += '<strong>No. SHGB:</strong> ' + data.tanah_no_shgb + '<br>';
        if (data.tanah_luas) html += '<strong>Luas:</strong> ' + data.tanah_luas + ' m²<br>';
        if (data.tanah_nama_pemilik) html += '<strong>Pemilik:</strong> ' + data.tanah_nama_pemilik + '<br>';
        if (data.tanah_lokasi) html += '<strong>Lokasi:</strong> ' + data.tanah_lokasi + '<br>';
      } else {
        // Kendaraan
        html += '<hr style="margin:8px 0;border:none;border-top:1px solid #ddd">';
        html += '<strong style="color:#0284c7">🚗 Detail Agunan Kendaraan:</strong><br>';

        if (data.kend_jenis) html += '<strong>Jenis:</strong> ' + data.kend_jenis + '<br>';
        if (data.kend_merk) html += '<strong>Merk:</strong> ' + data.kend_merk + '<br>';
        if (data.kend_tahun) html += '<strong>Tahun:</strong> ' + data.kend_tahun + '<br>';
        if (data.kend_no_polisi) html += '<strong>No. Polisi:</strong> ' + data.kend_no_polisi + '<br>';
      }

      // Info Tambahan
      html += '<hr style="margin:8px 0;border:none;border-top:1px solid #ddd">';
      html += '<strong style="color:#0284c7">ℹ️ Info Tambahan:</strong><br>';

      if (data.kode_kantor) {
        html += '<strong>Kode Kantor:</strong> ' + data.kode_kantor + '<br>';
      }

      if (data.kode_status) {
        html += '<strong>Kode Status:</strong> ' + data.kode_status + '<br>';
      }

      if (data.premier) {
        html += '<strong>Premier:</strong> ' + data.premier + '<br>';
      }

      return html;
    }

    // Switch ke mode manual input
    function switchToManualMode() {
      verifiedData = null;
      els.verifiedInfo.style.display = 'none';
      els.manualInputs.style.display = 'flex';
      els.captureBtn.disabled = false;
      els.finishBtn.disabled = false;
      els.status.textContent = 'Manual Mode - Siap foto';
      els.hint.textContent = 'Mode manual: Isi data agunan lalu foto berkas.';
      els.idAgunan.focus();
      openCamera();
    }

    function setStatus(t) { els.status.textContent = t; }

    async function openCamera() {
      try {
        if (stream) stream.getTracks().forEach(t => t.stop());
        const constraints = { video: { facingMode: usingBack ? 'environment' : 'user' }, audio: false };
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        track = stream.getVideoTracks()[0];
        els.preview.srcObject = stream;
        await els.preview.play();
        setStatus('Kamera siap ✅');
      } catch (e) {
        console.error(e);
        setStatus('Gagal buka kamera');
        els.hint.textContent = 'Pastikan akses HTTPS dan izin kamera diberikan.';
      }
    }

    function updateCount() { els.count.textContent = String(queue.length); }

    function capture() {
      if (queue.length >= limit) { alert('Batas 20 foto tercapai'); return; }
      const v = els.preview, c = els.canvas, ctx = c.getContext('2d');
      const w = v.videoWidth || 1280; const h = v.videoHeight || 720;
      c.width = w; c.height = h; ctx.drawImage(v, 0, 0, w, h);
      const url = c.toDataURL('image/jpeg', 0.92); // biarkan kualitas tinggi; backend yang proses berat
      queue.push({ dataUrl: url });
      updateCount();
    }

    function toReview() {
      els.reviewCard.hidden = false;
      renderThumbs();
      window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }

    function backCamera() { els.reviewCard.hidden = true; }

    function move(idx, dir) {
      const ni = idx + dir; if (ni < 0 || ni >= queue.length) return;
      const tmp = queue[idx]; queue[idx] = queue[ni]; queue[ni] = tmp; renderThumbs();
    }

    function removeAt(idx) { queue.splice(idx, 1); renderThumbs(); updateCount(); }

    function renderThumbs() {
      els.thumbs.innerHTML = '';
      queue.forEach((it, i) => {
        const div = document.createElement('div');
        div.className = 'thumb';
        div.innerHTML = `<img src="${it.dataUrl}"><div class="controls"><div><button class='btn secondary' onclick='move(${i},-1)'>⬆️</button> <button class='btn secondary' onclick='move(${i},1)'>⬇️</button></div><button class='btn danger' onclick='removeAt(${i})'>🗑️ Hapus</button></div>`;
        els.thumbs.appendChild(div);
      });
      els.progressTxt.textContent = `0/${queue.length}`;
      els.bar.style.width = '0%';
    }

    async function dataURLToBlob(dataURL) { const r = await fetch(dataURL); return await r.blob(); }

    async function uploadAll() {
      // Ambil data dari verifiedData atau manual input
      let id_agunan, nama_nasabah, no_rek;

      if (verifiedData) {
        // Mode verified - data dari IBS
        id_agunan = verifiedData.agunan_id;
        nama_nasabah = verifiedData.tanah_nama_pemilik || verifiedData.deskripsi_ringkas || verifiedData.agunan_id;
        no_rek = '-'; // Bisa diambil dari relasi kredit jika perlu
      } else {
        // Mode manual
        id_agunan = els.idAgunan.value.trim();
        nama_nasabah = els.namaNasabah.value.trim();
        no_rek = els.noRek.value.trim();

        if (!id_agunan || !nama_nasabah || !no_rek) {
          alert('Lengkapi form agunan terlebih dahulu');
          return;
        }
      }

      if (queue.length === 0) { alert('Belum ada foto'); return; }

      els.saveAllBtn.disabled = true;
      let ok = 0;

      for (let i = 0; i < queue.length; i++) {
        const blob = await dataURLToBlob(queue[i].dataUrl);
        const form = new FormData();
        form.append('image', blob, `capture_${i + 1}.jpg`);
        form.append('id_agunan', id_agunan);
        form.append('nama_nasabah', nama_nasabah);
        form.append('no_rek', no_rek);
        form.append('ket', '');

        // Kirim data verified jika ada
        if (verifiedData) {
          form.append('verified_data', JSON.stringify(verifiedData));
        }

        if (agunanDataId) form.append('agunan_data_id', agunanDataId);

        try {
          const resp = await fetch('../process/upload_photo.php', { method: 'POST', body: form, credentials: 'same-origin' });
          const json = await resp.json();
          if (!json.success) throw new Error(json.message || 'Gagal upload');
          agunanDataId = json.agunan_data_id;
          ok++;
          els.progressTxt.textContent = `${ok}/${queue.length}`;
          els.bar.style.width = `${Math.round(ok / queue.length * 100)}%`;
        } catch (e) {
          console.error('Upload gagal', e);
          alert('Upload gagal di foto ke-' + (i + 1) + ': ' + e.message);
          els.saveAllBtn.disabled = false; return;
        }
      }
      // finalize PDF
      try {
        const fd = new FormData(); fd.append('agunan_data_id', agunanDataId);
        const r = await fetch('../process/finalize_batch.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        const j = await r.json();
        if (!j.success) throw new Error(j.message || 'Gagal membuat PDF');

        // Tampilkan notifikasi jika berhasil (pakai Service Worker untuk PWA compatibility)
        if (j.notification && 'Notification' in window && Notification.permission === 'granted') {
          // Cek apakah ada Service Worker registration
          if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            // Pakai Service Worker showNotification (untuk PWA)
            navigator.serviceWorker.ready.then(registration => {
              registration.showNotification('✅ Agunan Berhasil Disimpan', {
                body: `${j.notification.username} menyimpan agunan ${j.notification.id_agunan} (${j.notification.jumlah_foto} foto)`,
                icon: '../assets/icon-192.svg',
                badge: '../assets/icon-192.svg',
                tag: 'agunan-saved',
                requireInteraction: false,
                vibrate: [200, 100, 200]
              });
            }).catch(err => {
              console.log('Service Worker notification error:', err);
            });
          } else {
            // Fallback ke Notification API biasa (untuk browser)
            try {
              const notif = new Notification('✅ Agunan Berhasil Disimpan', {
                body: `${j.notification.username} menyimpan agunan ${j.notification.id_agunan} (${j.notification.jumlah_foto} foto)`,
                icon: '../assets/icon-192.svg',
                badge: '../assets/icon-192.svg',
                tag: 'agunan-saved',
                requireInteraction: false
              });

              // Auto close setelah 5 detik
              setTimeout(() => notif.close(), 5000);
            } catch (notifError) {
              console.log('Notification API error:', notifError);
              // Silent fail - tidak perlu alert ke user
            }
          }
        }

        alert('Batch selesai. PDF dibuat otomatis.');
        window.location.href = 'history.php';
      } catch (e) {
        alert('Finalisasi gagal: ' + e.message);
        els.saveAllBtn.disabled = false;
      }
    }

    // wire event listeners
    els.verifyBtn.addEventListener('click', verifyAgunan);
    els.switchBtn.addEventListener('click', () => { usingBack = !usingBack; openCamera(); });
    els.captureBtn.addEventListener('click', capture);
    els.finishBtn.addEventListener('click', toReview);
    els.backToCamera.addEventListener('click', backCamera);
    els.saveAllBtn.addEventListener('click', uploadAll);

    // Jangan auto-open camera, tunggu verifikasi dulu
    // window.addEventListener('pageshow', openCamera);
    window.addEventListener('beforeunload', () => { if (stream) stream.getTracks().forEach(t => t.stop()); });

    // expose helpers for inline onclick
    window.move = move; window.removeAt = removeAt;
  </script>
</body>

</html>