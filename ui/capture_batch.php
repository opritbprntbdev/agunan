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
  <link rel="manifest" href="../manifest.json">
  <meta name="theme-color" content="#2563eb">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
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

    .agunan-item {
      border: 1px solid #ddd;
      border-radius: 10px;
      margin-bottom: 8px;
      background: #fff;
      overflow: hidden
    }

    .agunan-header {
      padding: 12px;
      background: #f9fafb;
      border-bottom: 1px solid #e5e7eb;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600
    }

    .agunan-header:hover {
      background: #f3f4f6
    }

    .agunan-body {
      padding: 12px;
      background: #fff;
      font-size: 13px;
      line-height: 1.6;
      display: none
    }

    .agunan-body.expanded {
      display: block
    }

    .capture-agunan-btn {
      padding: 8px 14px;
      border-radius: 8px;
      border: 1px solid #16a34a;
      background: #16a34a;
      color: #fff;
      cursor: pointer;
      font-size: 13px;
      margin-top: 8px;
      display: block;
      width: 100%
    }

    .capture-agunan-btn.recapture {
      background: #f59e0b;
      border-color: #f59e0b
    }

    .status-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      margin-left: 8px
    }

    .status-badge.captured {
      background: #dcfce7;
      color: #16a34a
    }

    .status-badge.not-captured {
      background: #fee2e2;
      color: #dc2626
    }

    .agunan-item.captured {
      opacity: 0.7;
      background: #f9fafb
    }

    .toggle-wrapper {
      padding: 12px;
      background: #f0f9ff;
      border-bottom: 1px solid #bae6fd;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px
    }

    .toggle-wrapper input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer
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
    <!-- Step 1: Input No Rekening untuk cari list agunan -->
    <div class="card" id="step1Card">
      <div class="head">
        <input class="input" id="no_rek_input" placeholder="No. Rekening Nasabah" required style="flex:2">
        <button class="btn success" id="searchBtn" type="button">🔍 Cari Agunan</button>
      </div>
      <div style="padding:12px;font-size:13px;color:#666;border-bottom:1px solid #eee">
        💡 <strong>Petunjuk:</strong> Masukkan No. Rekening nasabah untuk melihat daftar agunan yang tersedia.
      </div>
      <div id="agunanListWrapper" style="display:none">
        <div style="padding:12px;background:#f0f9ff;border-bottom:1px solid #bae6fd">
          <div id="nasabahInfo" style="font-size:13px;line-height:1.6"></div>
        </div>
        <div class="toggle-wrapper">
          <input type="checkbox" id="showCapturedToggle">
          <label for="showCapturedToggle" style="cursor:pointer;user-select:none">
            Tampilkan agunan yang sudah di-capture
          </label>
        </div>
        <div id="agunanList" style="padding:12px"></div>
      </div>
    </div>

    <!-- Step 2: Capture (akan muncul setelah pilih agunan) -->
    <div class="card" id="captureCard" style="display:none">
      <div style="padding:12px;background:#dcfce7;border-bottom:1px solid #86efac;display:flex;justify-content:space-between;align-items:center">
        <div>
          <span class="verified-badge">✅ Verified dari IBS</span>
          <div class="info-box" id="selectedAgunanInfo" style="margin-top:8px;background:#fff"></div>
        </div>
        <button class="btn secondary" id="backToListBtn" style="flex-shrink:0;margin-left:8px">↩️ Ganti Agunan</button>
      </div>
      <div style="padding:12px">
        <video id="preview" playsinline autoplay muted></video>
        <canvas id="canvas" hidden></canvas>
        <div class="hint" id="hint">Agunan dipilih, membuka kamera...</div>
      </div>
      <div class="toolbar">
        <button class="btn secondary" id="switchBtn">🔁 Ganti</button>
        <button class="btn" id="captureBtn" disabled>📸 Ambil</button>
        <button class="btn secondary" id="finishBtn" disabled>✅ Selesai</button>
        <span class="badge" id="status">Pilih agunan terlebih dahulu</span>
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

    // Data agunan yang dipilih
    let selectedAgunan = null;

    const els = {
      noRekInput: document.getElementById('no_rek_input'),
      searchBtn: document.getElementById('searchBtn'),
      agunanListWrapper: document.getElementById('agunanListWrapper'),
      nasabahInfo: document.getElementById('nasabahInfo'),
      agunanList: document.getElementById('agunanList'),
      showCapturedToggle: document.getElementById('showCapturedToggle'),
      step1Card: document.getElementById('step1Card'),
      captureCard: document.getElementById('captureCard'),
      selectedAgunanInfo: document.getElementById('selectedAgunanInfo'),
      backToListBtn: document.getElementById('backToListBtn'),
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

    // Fungsi Cari Agunan by No Rekening
    async function searchAgunan() {
      const noRek = els.noRekInput.value.trim();

      if (!noRek) {
        alert('Masukkan No. Rekening terlebih dahulu');
        els.noRekInput.focus();
        return;
      }

      // Disable button & show loading
      els.searchBtn.disabled = true;
      els.searchBtn.innerHTML = '<span class="spinner"></span> Mencari...';

      try {
        const formData = new FormData();
        formData.append('no_rekening', noRek);
        formData.append('show_captured', els.showCapturedToggle.checked ? '1' : '0');

        const response = await fetch('../process/get_agunan_list.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
          // Data ditemukan
          const { nasabah, agunan_list, total } = result;

          // Tampilkan info nasabah
          els.nasabahInfo.innerHTML = `
            <strong>CIF:</strong> ${nasabah.cif}<br>
            <strong>Nama:</strong> ${nasabah.nama}<br>
            <strong>Alamat:</strong> ${nasabah.alamat}<br>
            <strong>No. Rekening:</strong> ${nasabah.no_rek}<br>
            <strong>Total Agunan ${result.show_captured ? 'Semua' : 'Belum Di-Capture'}:</strong> ${total}
          `;

          // Render accordion list
          renderAgunanList(agunan_list);

          // Simpan data agunan untuk referensi
          window.agunanListData = agunan_list;

          // Tampilkan wrapper
          els.agunanListWrapper.style.display = 'block';

        } else {
          alert(result.message || 'Tidak ada agunan ditemukan untuk no. rekening ini');
        }

      } catch (error) {
        console.error('Search error:', error);
        alert('Gagal mencari agunan: ' + error.message);
      } finally {
        // Reset button
        els.searchBtn.disabled = false;
        els.searchBtn.innerHTML = '🔍 Cari Agunan';
      }
    }

    // Render agunan list dengan badge status
    function renderAgunanList(agunan_list) {
      els.agunanList.innerHTML = '';
      
      // Hint untuk user
      const hint = document.createElement('div');
      hint.style.cssText = 'font-size:13px;color:#666;margin-bottom:12px;padding:8px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px';
      hint.innerHTML = '💡 <strong>Klik ▼</strong> untuk melihat detail agunan, lalu klik tombol untuk capture.';
      els.agunanList.appendChild(hint);

      agunan_list.forEach((agunan, index) => {
        const isCaptured = agunan.sudah_capture;
        const captureInfo = agunan.capture_info;
        
        // Status badge
        let statusBadge = '';
        if (isCaptured) {
          statusBadge = `<span class="status-badge captured">✅ Sudah (${captureInfo.jumlah_foto} foto, ${captureInfo.tanggal})</span>`;
        } else {
          statusBadge = `<span class="status-badge not-captured">❌ Belum Di-Capture</span>`;
        }
        
        // Button capture
        let captureButton = '';
        if (isCaptured) {
          captureButton = `<button class="capture-agunan-btn recapture" onclick="selectAgunan(${index})">🔄 Update Foto Agunan Ini</button>`;
        } else {
          captureButton = `<button class="capture-agunan-btn" onclick="selectAgunan(${index})">📸 Capture Agunan Ini</button>`;
        }

        const div = document.createElement('div');
        div.className = 'agunan-item' + (isCaptured ? ' captured' : '');
        div.innerHTML = `
          <div class="agunan-header" onclick="toggleAgunan(${index})">
            <span>${agunan.jenis_display} - ${agunan.agunan_id} ${statusBadge}</span>
            <span id="toggle-${index}">▼</span>
          </div>
          <div class="agunan-body" id="body-${index}">
            ${formatAgunanDetails(agunan)}
            ${captureButton}
          </div>
        `;
        els.agunanList.appendChild(div);
      });
    }

    // Toggle accordion
    function toggleAgunan(index) {
      const body = document.getElementById(`body-${index}`);
      const toggle = document.getElementById(`toggle-${index}`);
      
      if (body.classList.contains('expanded')) {
        body.classList.remove('expanded');
        toggle.textContent = '▼';
      } else {
        body.classList.add('expanded');
        toggle.textContent = '▲';
      }
    }

    // Format detail agunan
    function formatAgunanDetails(agunan) {
      let html = '';
      
      if (agunan.deskripsi) html += `<strong>Deskripsi:</strong> ${agunan.deskripsi}<br>`;
      if (agunan.nilai_agunan) html += `<strong>Nilai Agunan:</strong> Rp ${parseFloat(agunan.nilai_agunan).toLocaleString('id-ID')}<br>`;
      if (agunan.kode_status) html += `<strong>Status:</strong> ${agunan.kode_status}<br>`;
      if (agunan.kode_kantor) html += `<strong>Kode Kantor:</strong> ${agunan.kode_kantor}<br>`;

      // Detail tanah
      if (agunan.tanah_no_shm || agunan.tanah_no_shgb || agunan.tanah_luas) {
        html += '<hr style="margin:8px 0;border:none;border-top:1px solid #ddd">';
        html += '<strong style="color:#0284c7">🏠 Detail Tanah:</strong><br>';
        if (agunan.tanah_no_shm) html += `<strong>No. SHM:</strong> ${agunan.tanah_no_shm}<br>`;
        if (agunan.tanah_no_shgb) html += `<strong>No. SHGB:</strong> ${agunan.tanah_no_shgb}<br>`;
        if (agunan.tanah_luas) html += `<strong>Luas:</strong> ${agunan.tanah_luas} m²<br>`;
        if (agunan.tanah_lokasi) html += `<strong>Lokasi:</strong> ${agunan.tanah_lokasi}<br>`;
      }

      // Detail kendaraan
      if (agunan.kend_merk || agunan.kend_no_polisi || agunan.kend_tahun) {
        html += '<hr style="margin:8px 0;border:none;border-top:1px solid #ddd">';
        html += '<strong style="color:#0284c7">🚗 Detail Kendaraan:</strong><br>';
        if (agunan.kend_jenis) html += `<strong>Jenis:</strong> ${agunan.kend_jenis}<br>`;
        if (agunan.kend_merk) html += `<strong>Merk:</strong> ${agunan.kend_merk}<br>`;
        if (agunan.kend_tahun) html += `<strong>Tahun:</strong> ${agunan.kend_tahun}<br>`;
        if (agunan.kend_no_polisi) html += `<strong>No. Polisi:</strong> ${agunan.kend_no_polisi}<br>`;
      }

      return html;
    }

    // Pilih agunan untuk di-capture
    async function selectAgunan(index) {
      selectedAgunan = window.agunanListData[index];

      // Tampilkan info agunan yang dipilih
      els.selectedAgunanInfo.innerHTML = formatAgunanInfo(selectedAgunan);

      // Sembunyikan step 1, tampilkan capture card
      els.step1Card.style.display = 'none';
      els.captureCard.style.display = 'block';

      // Enable tombol capture dan finish
      els.captureBtn.disabled = false;
      els.finishBtn.disabled = false;

      // Buka kamera
      els.status.textContent = 'Membuka kamera...';
      els.hint.textContent = 'Membuka kamera...';
      await openCamera();
      els.status.textContent = 'Kamera siap ✅ - Mulai foto';
      els.hint.textContent = 'Kamera siap. Arahkan ke berkas agunan lalu klik tombol 📸 Ambil.';

      // Scroll to capture
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Kembali ke list agunan
    function backToList() {
      // Stop camera
      if (stream) stream.getTracks().forEach(t => t.stop());

      // Clear selected agunan
      selectedAgunan = null;

      // Reset queue jika ada (optional - bisa dikomen jika mau keep foto)
      if (confirm('Kembali ke daftar agunan?\n\nFoto yang sudah diambil akan dihapus.')) {
        queue.length = 0;
        updateCount();

        // Show step 1, hide capture
        els.step1Card.style.display = 'block';
        els.captureCard.style.display = 'none';
        els.reviewCard.hidden = true;

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    function setStatus(t) { els.status.textContent = t; }

    // Format info agunan untuk ditampilkan (untuk selected agunan)
    function formatAgunanInfo(data) {
      let html = '';

      // Agunan ID
      html += '<strong>Agunan ID:</strong> ' + data.agunan_id + '<br>';

      // Jenis Agunan
      if (data.jenis_display) {
        html += '<strong>Jenis:</strong> ' + data.jenis_display + '<br>';
      }

      // Deskripsi
      if (data.deskripsi) {
        html += '<strong>Deskripsi:</strong> ' + data.deskripsi + '<br>';
      }

      // Nilai Agunan
      if (data.nilai_agunan) {
        html += '<strong>Nilai Agunan:</strong> Rp ' + parseFloat(data.nilai_agunan).toLocaleString('id-ID') + '<br>';
      }

      // Detail Tanah
      if (data.tanah_no_shm || data.tanah_no_shgb || data.tanah_luas) {
        html += '<hr style="margin:8px 0;border:none;border-top:1px solid #ddd">';
        html += '<strong style="color:#0284c7">🏠 Detail Tanah:</strong><br>';
        if (data.tanah_no_shm) html += '<strong>No. SHM:</strong> ' + data.tanah_no_shm + '<br>';
        if (data.tanah_no_shgb) html += '<strong>No. SHGB:</strong> ' + data.tanah_no_shgb + '<br>';
        if (data.tanah_luas) html += '<strong>Luas:</strong> ' + data.tanah_luas + ' m²<br>';
        if (data.tanah_nama_pemilik) html += '<strong>Pemilik:</strong> ' + data.tanah_nama_pemilik + '<br>';
        if (data.tanah_lokasi) html += '<strong>Lokasi:</strong> ' + data.tanah_lokasi + '<br>';
      }

      // Detail Kendaraan
      if (data.kend_merk || data.kend_no_polisi || data.kend_tahun) {
        html += '<hr style="margin:8px 0;border:none;border-top:1px solid #ddd">';
        html += '<strong style="color:#0284c7">🚗 Detail Kendaraan:</strong><br>';
        if (data.kend_jenis) html += '<strong>Jenis:</strong> ' + data.kend_jenis + '<br>';
        if (data.kend_merk) html += '<strong>Merk:</strong> ' + data.kend_merk + '<br>';
        if (data.kend_tahun) html += '<strong>Tahun:</strong> ' + data.kend_tahun + '<br>';
        if (data.kend_no_polisi) html += '<strong>No. Polisi:</strong> ' + data.kend_no_polisi + '<br>';
      }

      // Info Tambahan
      if (data.kode_kantor || data.kode_status) {
        html += '<hr style="margin:8px 0;border:none;border-top:1px solid #ddd">';
        html += '<strong style="color:#0284c7">ℹ️ Info Tambahan:</strong><br>';
        if (data.kode_kantor) html += '<strong>Kode Kantor:</strong> ' + data.kode_kantor + '<br>';
        if (data.kode_status) html += '<strong>Status:</strong> ' + data.kode_status + '<br>';
      }

      return html;
    }

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
      // Ambil data dari selectedAgunan
      if (!selectedAgunan) {
        alert('Agunan belum dipilih');
        return;
      }

      const id_agunan = selectedAgunan.agunan_id;
      const nama_nasabah = selectedAgunan.tanah_nama_pemilik || selectedAgunan.deskripsi || selectedAgunan.agunan_id;
      const no_rek = els.noRekInput.value.trim();

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

        // Kirim data verified
        form.append('verified_data', JSON.stringify(selectedAgunan));

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
    els.searchBtn.addEventListener('click', searchAgunan);
    els.showCapturedToggle.addEventListener('change', searchAgunan); // Re-search ketika toggle berubah
    els.backToListBtn.addEventListener('click', backToList);
    els.switchBtn.addEventListener('click', async () => { 
      usingBack = !usingBack; 
      setStatus('Membuka kamera...');
      await openCamera(); 
      setStatus('Kamera siap ✅');
    });
    els.captureBtn.addEventListener('click', capture);
    els.finishBtn.addEventListener('click', toReview);
    els.backToCamera.addEventListener('click', backCamera);
    els.saveAllBtn.addEventListener('click', uploadAll);

    // Enter key untuk search
    els.noRekInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') searchAgunan();
    });

    window.addEventListener('beforeunload', () => { if (stream) stream.getTracks().forEach(t => t.stop()); });

    // expose helpers for inline onclick
    window.move = move; 
    window.removeAt = removeAt;
    window.toggleAgunan = toggleAgunan;
    window.selectAgunan = selectAgunan;
  </script>
</body>

</html>