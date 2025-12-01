<?php
session_start();

// CRITICAL SECURITY: Load agunan guard
require_once '../agunan_guard.php';

$nama_kc = $_SESSION['nama_kc'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Camera Capture - UI</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#0b0e14; color:#e6edf3; }
        header { padding: 10px 14px; background:#0d3d88; color:#fff; display:flex; align-items:center; justify-content:space-between; }
        header .info { font-size: 12px; opacity: .9; }
        .wrap { padding: 12px; max-width: 700px; margin: 0 auto; }
        .card { background: #0f172a; border:1px solid #24304a; border-radius: 12px; overflow: hidden; }
        .card-head { padding: 12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap; border-bottom:1px solid #24304a; }
        .card-body { padding: 12px; }
        .row { display:flex; gap:8px; flex-wrap:wrap; }
        input, button, select { font: inherit; }
        .input { flex:1; min-width: 140px; padding: 10px 12px; border-radius: 10px; border:1px solid #334155; background:#0b1220; color:#e6edf3; }
        .btn { padding: 10px 14px; border-radius:10px; border:1px solid #355cac; background:#1e3a8a; color:#fff; cursor:pointer; }
        .btn.secondary { background:#0b1220; border-color:#334155; color:#cbd5e1; }
        .btn.danger { background:#b91c1c; border-color:#7f1d1d; }
        .btn:disabled { opacity:.6; cursor:not-allowed; }
        video, canvas, img { width: 100%; max-height: 60vh; background:#000; border-radius: 10px; }
        .toolbar { display:flex; gap:8px; align-items:center; justify-content:center; padding:10px; border-top:1px solid #24304a; }
        .hint { font-size:12px; color:#94a3b8; margin-top:6px; text-align:center; }
        .grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:8px; }
        .badge { padding:4px 8px; border-radius:999px; background:#0b1220; border:1px solid #334155; font-size:12px; }
        .success { color:#16a34a; }
        .error { color:#ef4444; }
        .preview-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap:8px; margin-top:12px; }
        .preview-item { background:#1e293b; border:1px solid #334155; border-radius:8px; overflow:hidden; }
        .preview-item img { width:100%; height:120px; object-fit:cover; display:block; }
        .preview-item .info { padding:6px; font-size:11px; color:#94a3b8; }
        .preview-item .btn-remove { width:100%; padding:6px; font-size:11px; }
        .toast { position:fixed; top:70px; right:12px; background:#16a34a; color:#fff; padding:12px 16px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,.3); z-index:9999; animation:slideIn 0.3s ease-out; }
        .toast.error { background:#ef4444; }
        @keyframes slideIn { from { transform:translateX(400px); opacity:0; } to { transform:translateX(0); opacity:1; } }
        .toast-exit { animation:slideOut 0.3s ease-in forwards; }
        @keyframes slideOut { from { transform:translateX(0); opacity:1; } to { transform:translateX(400px); opacity:0; } }
    </style>
</head>
<body>
    <header>
        <div>📸 Capture Agunan (UI)</div>
        <div class="info">User: <?= htmlspecialchars($username) ?> · KC: <?= htmlspecialchars($nama_kc) ?></div>
    </header>
    <div class="wrap">
        <div class="card" id="card">
            <div class="card-head">
                <input class="input" id="id_agunan" placeholder="ID Agunan" required>
                <input class="input" id="nama_nasabah" placeholder="Nama Nasabah" required>
                <input class="input" id="no_rek" placeholder="No Rekening Kredit" required>
            </div>
            <div class="card-body">
                <!-- Kamera SELALU TAMPIL, tidak pernah hidden -->
        <video id="preview" playsinline autoplay muted></video>
        <canvas id="canvas" hidden></canvas>
        <input type="file" id="fileFallback" accept="image/*;capture=camera" hidden>
                
                <div class="hint" id="supportHint">Membuka kamera belakang…</div>
                
                <div class="toolbar">
                    <button class="btn secondary" id="switchBtn" title="Ganti kamera">🔁 Ganti</button>
                    <button class="btn secondary" id="torchBtn" title="Senter" disabled>💡 Torch</button>
                    <button class="btn" id="captureBtn">📷 Ambil Foto</button>
          <button class="btn secondary" id="fallbackBtn" hidden>Pilih Foto (Fallback)</button>
                </div>
                
                <!-- Preview Grid SELALU DI BAWAH kamera untuk foto-foto yang sudah diambil -->
                <div id="previewGrid" class="preview-grid" style="display:none;"></div>
                
                <div style="text-align:center; margin-top:12px;">
                    <button class="btn" id="uploadAllBtn" hidden style="width:100%; max-width:400px;">⬆️ Upload Semua Foto</button>
                </div>
                <div class="row" style="justify-content:center; gap:12px; margin-top:6px;">
                    <span class="badge" id="statusBadge">Ready</span>
                    <span class="badge" id="orderBadge">Foto ke: <span id="order">1</span></span>
                    <span class="badge" id="kbBadge">0 KB</span>
                </div>
            </div>
        </div>
        <p class="hint">Tips: Torch hanya didukung sebagian perangkat (Chrome Android tertentu). Pastikan akses via HTTPS/LAN aman.</p>
    </div>

<script>
let stream = null;
let currentDeviceId = null;
let usingBack = true;
let track = null;
let mediaStreamTrack = null;
let torchSupported = false;
let agunanDataId = null; // dari server setelah upload pertama
let nextOrder = 1;
let capturedPhotos = []; // Array untuk simpan foto-foto yang sudah diambil

const els = {
  video: document.getElementById('preview'),
  canvas: document.getElementById('canvas'),
  previewGrid: document.getElementById('previewGrid'),
  torchBtn: document.getElementById('torchBtn'),
  switchBtn: document.getElementById('switchBtn'),
  captureBtn: document.getElementById('captureBtn'),
  uploadAllBtn: document.getElementById('uploadAllBtn'),
  supportHint: document.getElementById('supportHint'),
  kbBadge: document.getElementById('kbBadge'),
  order: document.getElementById('order'),
  status: document.getElementById('statusBadge'),
  id_agunan: document.getElementById('id_agunan'),
  nama_nasabah: document.getElementById('nama_nasabah'),
  no_rek: document.getElementById('no_rek'),
  fileFallback: document.getElementById('fileFallback'),
  fallbackBtn: document.getElementById('fallbackBtn'),
};

function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = 'toast' + (type === 'error' ? ' error' : '');
  toast.textContent = message;
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.classList.add('toast-exit');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function setStatus(text, cls='') {
  els.status.textContent = text;
  els.status.className = 'badge ' + cls;
}

async function openCamera() {
  setStatus('Opening camera…');
  if (stream) {
    stream.getTracks().forEach(t=>t.stop());
  }
  const constraints = {
    audio: false,
    video: currentDeviceId ? { deviceId: { exact: currentDeviceId } } : { facingMode: usingBack ? 'environment' : 'user' }
  };
  try {
    stream = await navigator.mediaDevices.getUserMedia(constraints);
    els.video.srcObject = stream;
    track = stream.getVideoTracks()[0];
    mediaStreamTrack = track; 
    await els.video.play();
    await detectTorchSupport();
    setStatus('Kamera siap ✅', 'success');
  } catch (e) {
    console.error(e);
    setStatus('Gagal membuka kamera', 'error');
    els.supportHint.textContent = 'Pastikan browser mengizinkan kamera dan situs diakses melalui HTTPS. Atau gunakan fallback pilih foto.';
    els.fallbackBtn.hidden = false;
  }
}

async function detectTorchSupport() {
  torchSupported = false;
  els.torchBtn.disabled = true;
  if (!track) return;
  const cap = track.getCapabilities ? track.getCapabilities() : {};
  if (cap.torch) {
    torchSupported = true;
    els.torchBtn.disabled = false;
    els.supportHint.textContent = 'Torch tersedia. Tekan 💡 untuk ON/OFF.';
  } else {
    els.supportHint.textContent = 'Torch tidak tersedia di perangkat ini.';
  }
}

async function toggleTorch() {
  if (!torchSupported || !track) return;
  try {
    const settings = track.getSettings ? track.getSettings() : {};
    const currentTorch = settings.torch === true; // beberapa browser tidak expose
    await track.applyConstraints({ advanced: [{ torch: !currentTorch }] });
    els.torchBtn.textContent = !currentTorch ? '💡 Torch ON' : '💡 Torch';
  } catch (e) {
    console.warn('Torch toggle failed', e);
    setStatus('Torch tidak bisa diubah', 'error');
  }
}

function captureFrame() {
  const video = els.video;
  const canvas = els.canvas;
  const ctx = canvas.getContext('2d');
  const maxW = 1280; // resize untuk hemat ukuran
  const ratio = video.videoWidth / video.videoHeight || 4/3;
  const targetW = Math.min(maxW, video.videoWidth || maxW);
  const targetH = Math.round(targetW / ratio);

  canvas.width = targetW;
  canvas.height = targetH;
  ctx.drawImage(video, 0, 0, targetW, targetH);

  // Preview sebagai data URL agar cepat tampil
  const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
  const sizeKB = Math.round((dataUrl.length * 3 / 4) / 1024);
  
  // Tambahkan ke array
  const photoNumber = capturedPhotos.length + 1;
  capturedPhotos.push({
    dataUrl: dataUrl,
    sizeKB: sizeKB,
    order: photoNumber,
    timestamp: new Date().toLocaleString('id-ID')
  });
  
  // Update UI
  renderPreviewGrid();
  showToast(`✅ Foto ${photoNumber} berhasil diambil (${sizeKB} KB)`, 'success');
  
  // Update counter
  els.order.textContent = String(photoNumber + 1);
  setStatus(`${photoNumber} foto diambil`, 'success');
  
  // Show upload button jika ada foto
  if (capturedPhotos.length > 0) {
    els.uploadAllBtn.hidden = false;
  }
}

function renderPreviewGrid() {
  if (capturedPhotos.length === 0) {
    els.previewGrid.style.display = 'none';
    return;
  }
  
  els.previewGrid.style.display = 'grid';
  els.previewGrid.innerHTML = '';
  
  capturedPhotos.forEach((photo, index) => {
    const item = document.createElement('div');
    item.className = 'preview-item';
    item.innerHTML = `
      <img src="${photo.dataUrl}" alt="Foto ${photo.order}">
      <div class="info">
        📷 Foto ${photo.order} · ${photo.sizeKB} KB<br>
        🕐 ${photo.timestamp}
      </div>
      <button class="btn danger btn-remove" onclick="removePhoto(${index})">🗑️ Hapus</button>
    `;
    els.previewGrid.appendChild(item);
  });
}

function removePhoto(index) {
  if (confirm(`Hapus Foto ${capturedPhotos[index].order}?`)) {
    capturedPhotos.splice(index, 1);
    renderPreviewGrid();
    showToast('Foto dihapus', 'success');
    
    if (capturedPhotos.length === 0) {
      els.uploadAllBtn.hidden = true;
      els.order.textContent = '1';
      setStatus('Siap ambil foto', 'success');
    } else {
      els.order.textContent = String(capturedPhotos.length + 1);
      setStatus(`${capturedPhotos.length} foto diambil`, 'success');
    }
  }
}

// Expose ke window untuk onclick
window.removePhoto = removePhoto;

els.captureBtn.addEventListener('click', () => {
  captureFrame();
});

els.switchBtn.addEventListener('click', async () => {
  usingBack = !usingBack;
  currentDeviceId = null; // prefer facingMode saat toggle cepat
  await openCamera();
});

els.torchBtn.addEventListener('click', toggleTorch);

async function dataURLToBlob(dataURL) {
  const res = await fetch(dataURL);
  return await res.blob();
}

async function uploadAllPhotos() {
  const id_agunan = els.id_agunan.value.trim();
  const nama_nasabah = els.nama_nasabah.value.trim();
  const no_rek = els.no_rek.value.trim();
  
  if (!id_agunan || !nama_nasabah || !no_rek) {
    alert('Lengkapi ID Agunan, Nama Nasabah, dan No Rek');
    return;
  }
  
  if (capturedPhotos.length === 0) {
    alert('Belum ada foto yang diambil');
    return;
  }
  
  els.uploadAllBtn.disabled = true;
  els.uploadAllBtn.textContent = '⏳ Uploading...';
  setStatus('Mengunggah foto...');
  
  let uploaded = 0;
  
  for (let i = 0; i < capturedPhotos.length; i++) {
    const photo = capturedPhotos[i];
    
    try {
      const blob = await dataURLToBlob(photo.dataUrl);
      const form = new FormData();
      form.append('image', blob, `capture_${photo.order}.jpg`);
      form.append('id_agunan', id_agunan);
      form.append('nama_nasabah', nama_nasabah);
      form.append('no_rek', no_rek);
      form.append('ket', `Foto ${photo.order}`);
      if (agunanDataId) form.append('agunan_data_id', agunanDataId);

      const resp = await fetch('../process/upload_photo.php', { 
        method: 'POST', 
        body: form, 
        credentials: 'same-origin' 
      });
      const json = await resp.json();
      
      if (json.success) {
        agunanDataId = json.agunan_data_id;
        uploaded++;
        setStatus(`Upload ${uploaded}/${capturedPhotos.length}...`);
      } else {
        throw new Error(json.message || 'Gagal upload');
      }
    } catch (e) {
      console.error(e);
      alert(`Upload gagal pada foto ${photo.order}: ${e.message}`);
      els.uploadAllBtn.disabled = false;
      els.uploadAllBtn.textContent = '⬆️ Upload Semua';
      return;
    }
  }
  
  // Semua berhasil
  showToast(`✅ Semua ${uploaded} foto berhasil diupload!`, 'success');
  setStatus('Upload selesai ✅', 'success');
  
  // Reset
  capturedPhotos = [];
  renderPreviewGrid();
  nextOrder = uploaded + 1;
  els.order.textContent = String(nextOrder);
  els.uploadAllBtn.hidden = true;
  els.uploadAllBtn.disabled = false;
  els.uploadAllBtn.textContent = '⬆️ Upload Semua';
}

els.uploadAllBtn.addEventListener('click', uploadAllPhotos);

// Fallback pilih foto dari galeri/kamera
els.fallbackBtn.addEventListener('click', () => els.fileFallback.click());
els.fileFallback.addEventListener('change', async (e) => {
  const file = e.target.files && e.target.files[0];
  if (!file) return;
  const url = URL.createObjectURL(file);
  await new Promise((res) => {
    const tempImg = new Image();
    tempImg.onload = () => {
      const maxW = 1280;
      const ratio = tempImg.width / tempImg.height;
      const w = Math.min(maxW, tempImg.width);
      const h = Math.round(w / ratio);
      const canvas = els.canvas;
      canvas.width = w; canvas.height = h;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(tempImg, 0, 0, w, h);
      const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
      const sizeKB = Math.round((dataUrl.length * 3 / 4) / 1024);
      
      // Tambahkan ke capturedPhotos array
      const photoNumber = capturedPhotos.length + 1;
      capturedPhotos.push({
        dataUrl: dataUrl,
        sizeKB: sizeKB,
        order: photoNumber,
        timestamp: new Date().toLocaleString('id-ID')
      });
      
      renderPreviewGrid();
      showToast(`✅ Foto ${photoNumber} berhasil dipilih (${sizeKB} KB)`, 'success');
      els.order.textContent = String(photoNumber + 1);
      setStatus(`${photoNumber} foto diambil`, 'success');
      els.uploadAllBtn.hidden = false;
      
      URL.revokeObjectURL(url);
      res();
    };
    tempImg.src = url;
  });
});

window.addEventListener('pageshow', () => {
  openCamera();
});

window.addEventListener('beforeunload', () => {
  if (stream) stream.getTracks().forEach(t=>t.stop());
});
</script>
</body>
</html>
