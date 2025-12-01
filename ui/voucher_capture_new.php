<?php
session_start();
if (!isset($_SESSION['login'])) {
  header('Location: ../index.php');
  exit;
}

$nama_kc = $_SESSION['nama_kc'] ?? '';
$username = $_SESSION['username'] ?? '';
$kode_kantor = $_SESSION['kode_kantor'] ?? '';
$no_bukti = isset($_GET['no_bukti']) ? trim($_GET['no_bukti']) : '';

if (empty($no_bukti)) {
  header('Location: voucher_list.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Capture Voucher</title>
  <link rel="manifest" href="../manifest.json">
  <meta name="theme-color" content="#16a34a">
  <style>
    *{box-sizing:border-box}body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f5f5f5;color:#333}header{padding:12px 16px;background:#16a34a;color:#fff;display:flex;justify-content:space-between;align-items:center}.wrap{padding:12px;max-width:900px;margin:0 auto}.card{background:#fff;border:1px solid #ddd;border-radius:12px;overflow:hidden;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.1)}.head{padding:12px;border-bottom:1px solid #eee}.btn{padding:10px 14px;border-radius:10px;border:none;background:#16a34a;color:#fff;cursor:pointer;font-size:14px;margin:4px}.btn.secondary{background:#fff;border:1px solid #ddd;color:#333}.btn.danger{background:#dc2626}.btn:disabled{opacity:0.5;cursor:not-allowed}video,canvas{width:100%;max-height:50vh;background:#000;border-radius:10px}canvas{display:none}.info-box{background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:12px;margin:12px;font-size:13px;line-height:1.6}.info-box strong{color:#0284c7}.toolbar{display:flex;gap:8px;justify-content:center;align-items:center;padding:10px;border-top:1px solid #eee;flex-wrap:wrap}.hint{font-size:12px;color:#666;text-align:center;margin:6px 0}.thumbs{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;padding:12px}.thumb{border:1px solid #ddd;border-radius:10px;overflow:hidden;background:#fff}.thumb img{width:100%;height:120px;object-fit:cover;display:block}.thumb .controls{padding:6px;display:flex;flex-direction:column;gap:6px}.thumb .keterangan{font-size:11px;color:#666;font-style:italic;padding:4px;background:#f9f9f9;border-radius:4px;max-height:40px;overflow:hidden}.badge{padding:4px 8px;border-radius:999px;background:#f5f5f5;border:1px solid #ddd;font-size:12px;color:#666}.progress{height:10px;background:#e5e7eb;border:1px solid #ddd;border-radius:999px;overflow:hidden;margin:12px}.bar{height:100%;background:#22c55e;width:0%}.keterangan-input{width:calc(100% - 24px);padding:10px;border:1px solid #ddd;border-radius:8px;margin:8px 12px;font-size:14px}.spinner{display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}.detail-table{width:100%;border-collapse:collapse;font-size:12px;margin-top:12px}.detail-table th{background:#f9fafb;padding:8px;text-align:left;font-weight:600;border-bottom:2px solid #e5e7eb}.detail-table td{padding:8px;border-bottom:1px solid #f3f4f6}.amount{text-align:right;font-family:monospace}
  </style>
</head>
<body>
  <header>
    <div>📸 Capture Voucher</div>
    <div style="font-size:12px;opacity:.9">User: <?= htmlspecialchars($username) ?> · KC: <?= htmlspecialchars($nama_kc) ?></div>
  </header>
  
  <div class="wrap">
    <div class="card">
      <div class="head">
        <button class="btn secondary" onclick="window.location.href='voucher_list.php'">← Kembali ke List</button>
        <button class="btn" id="loadDataBtn">🔍 Load Data</button>
      </div>
      
      <div id="voucherInfo" style="display:none">
        <div class="info-box">
          <strong>No. Bukti:</strong> <span id="infNoBukti"></span><br>
          <strong>Tanggal:</strong> <span id="infTglTrans"></span><br>
          <strong>Kode Jurnal:</strong> <span id="infKodeJurnal"></span><br>
          <strong>Total Debet:</strong> Rp <span id="infTotalDebet"></span><br>
          <strong>Total Kredit:</strong> Rp <span id="infTotalKredit"></span>
        </div>
        
        <div style="padding:0 12px">
          <table class="detail-table">
            <thead>
              <tr>
                <th>Kode Perk</th>
                <th>Keterangan</th>
                <th class="amount">Debet</th>
                <th class="amount">Kredit</th>
              </tr>
            </thead>
            <tbody id="detailTableBody"></tbody>
          </table>
        </div>
      </div>
      
      <div style="padding:12px">
        <video id="preview" playsinline autoplay muted style="display:none"></video>
        <canvas id="canvas"></canvas>
        <div class="hint" id="hint">Klik Load Data untuk memuat voucher dari IBS</div>
      </div>
      
      <div style="padding:0 12px 12px" id="keteranganSection" hidden>
        <input type="text" class="keterangan-input" id="keteranganInput" placeholder="Keterangan foto (misal: Halaman 1, Lampiran, TTD)">
      </div>
      
      <div class="toolbar">
        <button class="btn secondary" id="switchBtn" disabled>🔁 Ganti</button>
        <button class="btn" id="captureBtn" disabled>📸 Ambil</button>
        <button class="btn secondary" id="finishBtn" disabled>✅ Selesai</button>
        <span class="badge" id="status">Load data dulu</span>
        <span class="badge">Foto: <span id="count">0</span>/20</span>
      </div>
    </div>
    
    <div class="card" id="reviewCard" hidden>
      <div style="padding:12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
        <div><span class="badge" id="reviewInfo">Review batch</span></div>
        <div>
          <button class="btn secondary" id="backToCamera">↩️ Kembali</button>
          <button class="btn" id="saveAllBtn">⬆️ Simpan</button>
        </div>
      </div>
      <div class="thumbs" id="thumbs"></div>
      <div class="progress"><div class="bar" id="bar"></div></div>
      <div style="text-align:center;padding:8px"><span class="badge" id="progressTxt">0/0</span></div>
    </div>
  </div>

  <script>
    const NO_BUKTI = <?= json_encode($no_bukti) ?>;
    const KODE_KANTOR = <?= json_encode($kode_kantor) ?>;
    
    let stream, track, usingBack = true;
    const limit = 20;
    const queue = [];
    let voucherDataId = null;
    let verifiedData = null;
    
    const els = {
      loadDataBtn: document.getElementById('loadDataBtn'),
      voucherInfo: document.getElementById('voucherInfo'),
      infNoBukti: document.getElementById('infNoBukti'),
      infTglTrans: document.getElementById('infTglTrans'),
      infKodeJurnal: document.getElementById('infKodeJurnal'),
      infTotalDebet: document.getElementById('infTotalDebet'),
      infTotalKredit: document.getElementById('infTotalKredit'),
      detailTableBody: document.getElementById('detailTableBody'),
      keteranganSection: document.getElementById('keteranganSection'),
      keteranganInput: document.getElementById('keteranganInput'),
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

    function formatAmount(value) {
      if (!value || value == 0) return '0';
      return new Intl.NumberFormat('id-ID').format(value);
    }

    async function loadVoucherData() {
      els.loadDataBtn.disabled = true;
      els.loadDataBtn.innerHTML = '<span class="spinner"></span> Loading...';
      els.status.textContent = 'Loading...';
      
      try {
        const formData = new FormData();
        formData.append('no_bukti', NO_BUKTI);
        
        const response = await fetch('../process/get_voucher_detail.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });
        
        const result = await response.json();
        
        if (result.success) {
          verifiedData = result.data;
          
          // Populate info
          els.infNoBukti.textContent = result.data.no_bukti;
          els.infTglTrans.textContent = result.data.tgl_trans;
          els.infKodeJurnal.textContent = result.data.kode_jurnal;
          els.infTotalDebet.textContent = formatAmount(result.data.total_debet);
          els.infTotalKredit.textContent = formatAmount(result.data.total_kredit);
          
          // Populate detail table
          els.detailTableBody.innerHTML = result.data.detail_rows.map(row => `
            <tr>
              <td style="font-family:monospace;font-size:11px">${row.kode_perk}</td>
              <td>${row.keterangan || '-'}</td>
              <td class="amount">${formatAmount(row.debet)}</td>
              <td class="amount">${formatAmount(row.kredit)}</td>
            </tr>
          `).join('');
          
          els.voucherInfo.style.display = 'block';
          els.keteranganSection.hidden = false;
          els.captureBtn.disabled = false;
          els.finishBtn.disabled = false;
          els.switchBtn.disabled = false;
          els.status.textContent = 'Membuka kamera...';
          els.hint.textContent = 'Membuka kamera...';
          
          await openCamera();
          
          els.status.textContent = 'Kamera siap ✅';
          els.hint.textContent = 'Isi keterangan lalu klik 📸 Foto';
        } else {
          alert(result.message || 'Gagal load data');
        }
      } catch (error) {
        console.error('Load error:', error);
        alert('Gagal load data: ' + error.message);
      } finally {
        els.loadDataBtn.disabled = false;
        els.loadDataBtn.innerHTML = '🔍 Load Data';
      }
    }

    async function openCamera() {
      const constraints = {
        video: {
          facingMode: usingBack ? 'environment' : 'user',
          width: { ideal: 1920 },
          height: { ideal: 1080 }
        }
      };
      
      if (stream) stream.getTracks().forEach(t => t.stop());
      
      stream = await navigator.mediaDevices.getUserMedia(constraints);
      els.preview.srcObject = stream;
      els.preview.style.display = 'block';
      track = stream.getVideoTracks()[0];
    }

    function capture() {
      if (queue.length >= limit) {
        alert(`Maksimal ${limit} foto`);
        return;
      }
      
      const keterangan = els.keteranganInput.value.trim();
      const ctx = els.canvas.getContext('2d');
      const v = els.preview;
      
      els.canvas.width = v.videoWidth;
      els.canvas.height = v.videoHeight;
      ctx.drawImage(v, 0, 0);
      
      const dataUrl = els.canvas.toDataURL('image/jpeg', 0.85);
      queue.push({ dataUrl, keterangan });
      
      els.count.textContent = queue.length;
      els.keteranganInput.value = '';
      els.keteranganInput.focus();
      els.status.textContent = `Foto ${queue.length}/${limit} ✅`;
    }

    function toReview() {
      if (queue.length === 0) {
        alert('Belum ada foto');
        return;
      }
      
      if (stream) stream.getTracks().forEach(t => t.stop());
      
      document.querySelector('.card:first-of-type').hidden = true;
      els.reviewCard.hidden = false;
      renderReview();
    }

    function backCamera() {
      els.reviewCard.hidden = true;
      document.querySelector('.card:first-of-type').hidden = false;
      openCamera();
    }

    function removeAt(i) {
      if (confirm('Hapus foto ini?')) {
        queue.splice(i, 1);
        renderReview();
        els.count.textContent = queue.length;
      }
    }

    function renderReview() {
      els.thumbs.innerHTML = queue.map((it, i) => `
        <div class="thumb">
          <img src="${it.dataUrl}">
          <div class="controls">
            <div class="keterangan">${it.keterangan || '(Tanpa keterangan)'}</div>
            <button class="btn danger" onclick="removeAt(${i})" style="font-size:12px;padding:6px">🗑️ Hapus</button>
          </div>
        </div>
      `).join('');
      
      els.reviewInfo.textContent = `${queue.length} foto siap upload`;
      els.progressTxt.textContent = `0/${queue.length}`;
      els.bar.style.width = '0%';
    }

    async function dataURLToBlob(dataURL) {
      const r = await fetch(dataURL);
      return await r.blob();
    }

    async function uploadAll() {
      if (!verifiedData) {
        alert('Data voucher belum di-load');
        return;
      }
      
      if (queue.length === 0) {
        alert('Belum ada foto');
        return;
      }
      
      els.saveAllBtn.disabled = true;
      let ok = 0;
      
      for (let i = 0; i < queue.length; i++) {
        const blob = await dataURLToBlob(queue[i].dataUrl);
        const form = new FormData();
        form.append('image', blob, `voucher_${i + 1}.jpg`);
        form.append('no_bukti', verifiedData.no_bukti);
        form.append('keterangan', queue[i].keterangan || '');
        form.append('verified_data', JSON.stringify(verifiedData));
        
        if (voucherDataId) form.append('voucher_data_id', voucherDataId);
        
        try {
          const resp = await fetch('../process/upload_voucher_photo.php', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
          });
          
          const json = await resp.json();
          
          if (!json.success) throw new Error(json.message || 'Gagal upload');
          
          voucherDataId = json.voucher_data_id;
          ok++;
          els.progressTxt.textContent = `${ok}/${queue.length}`;
          els.bar.style.width = `${Math.round(ok / queue.length * 100)}%`;
        } catch (e) {
          console.error('Upload gagal', e);
          alert('Upload gagal di foto ke-' + (i + 1) + ': ' + e.message);
          els.saveAllBtn.disabled = false;
          return;
        }
      }
      
      // Finalize
      try {
        const fd = new FormData();
        fd.append('voucher_data_id', voucherDataId);
        
        const r = await fetch('../process/finalize_voucher.php', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        });
        
        const j = await r.json();
        
        if (!j.success) throw new Error(j.message || 'Gagal PDF');
        
        alert('Batch selesai. PDF dibuat otomatis.');
        window.location.href = 'voucher_list.php';
      } catch (e) {
        alert('Finalisasi gagal: ' + e.message);
        els.saveAllBtn.disabled = false;
      }
    }

    // Event listeners
    els.loadDataBtn.addEventListener('click', loadVoucherData);
    els.switchBtn.addEventListener('click', async () => {
      usingBack = !usingBack;
      els.status.textContent = 'Membuka kamera...';
      await openCamera();
      els.status.textContent = 'Kamera siap ✅';
    });
    els.captureBtn.addEventListener('click', capture);
    els.finishBtn.addEventListener('click', toReview);
    els.backToCamera.addEventListener('click', backCamera);
    els.saveAllBtn.addEventListener('click', uploadAll);
    
    window.addEventListener('beforeunload', () => {
      if (stream) stream.getTracks().forEach(t => t.stop());
    });
    
    window.removeAt = removeAt;
    
    // Auto load on page load
    window.addEventListener('DOMContentLoaded', () => {
      if (NO_BUKTI) {
        loadVoucherData();
      }
    });
  </script>
</body>
</html>
