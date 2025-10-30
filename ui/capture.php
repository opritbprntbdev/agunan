<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../index.php");
    exit;
}

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
        <video id="preview" playsinline autoplay muted></video>
        <canvas id="canvas" hidden></canvas>
        <img id="snapshot" alt="Snapshot" hidden>
        <input type="file" id="fileFallback" accept="image/*;capture=camera" hidden>
                <div class="hint" id="supportHint">Membuka kamera belakang…</div>
                <div class="toolbar">
                    <button class="btn secondary" id="switchBtn" title="Ganti kamera">🔁 Ganti</button>
                    <button class="btn secondary" id="torchBtn" title="Senter" disabled>💡 Torch</button>
                    <button class="btn" id="captureBtn">📷 Ambil</button>
                    <button class="btn" id="retakeBtn" hidden>↩️ Ulangi</button>
          <button class="btn" id="uploadBtn" hidden>⬆️ Upload</button>
          <button class="btn secondary" id="fallbackBtn" hidden>Pilih Foto (Fallback)</button>
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

const els = {
  video: document.getElementById('preview'),
  canvas: document.getElementById('canvas'),
  img: document.getElementById('snapshot'),
  torchBtn: document.getElementById('torchBtn'),
  switchBtn: document.getElementById('switchBtn'),
  captureBtn: document.getElementById('captureBtn'),
  retakeBtn: document.getElementById('retakeBtn'),
  uploadBtn: document.getElementById('uploadBtn'),
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
  els.img.src = dataUrl;
  const sizeKB = Math.round((dataUrl.length * 3 / 4) / 1024);
  els.kbBadge.textContent = sizeKB + ' KB';
}

function showPreviewMode(on) {
  els.video.hidden = on;
  els.img.hidden = !on;
  els.retakeBtn.hidden = !on;
  els.uploadBtn.hidden = !on;
  els.captureBtn.hidden = on;
}

els.captureBtn.addEventListener('click', () => {
  captureFrame();
  showPreviewMode(true);
});

els.retakeBtn.addEventListener('click', () => {
  showPreviewMode(false);
  setStatus('Siap ambil lagi');
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

async function uploadSnapshot() {
  const id_agunan = els.id_agunan.value.trim();
  const nama_nasabah = els.nama_nasabah.value.trim();
  const no_rek = els.no_rek.value.trim();
  if (!id_agunan || !nama_nasabah || !no_rek) {
    alert('Lengkapi ID Agunan, Nama Nasabah, dan No Rek');
    return;
  }
  setStatus('Mengunggah…');

  const blob = await dataURLToBlob(els.img.src);
  const form = new FormData();
  form.append('image', blob, 'capture.jpg');
  form.append('id_agunan', id_agunan);
  form.append('nama_nasabah', nama_nasabah);
  form.append('no_rek', no_rek);
  form.append('ket', 'Foto ' + nextOrder);
  if (agunanDataId) form.append('agunan_data_id', agunanDataId);

  try {
    const resp = await fetch('../process/upload_photo.php', { method: 'POST', body: form, credentials: 'same-origin' });
    const json = await resp.json();
    if (json.success) {
      agunanDataId = json.agunan_data_id;
      nextOrder = json.next_order;
      els.order.textContent = String(nextOrder);
      setStatus('Tersimpan (' + (nextOrder-1) + ') ✅', 'success');
      showPreviewMode(false);
    } else {
      throw new Error(json.message || 'Gagal simpan');
    }
  } catch (e) {
    console.error(e);
    setStatus('Upload gagal', 'error');
    alert('Upload gagal: ' + e.message);
  }
}

els.uploadBtn.addEventListener('click', uploadSnapshot);

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
      els.img.src = dataUrl;
      const sizeKB = Math.round((dataUrl.length * 3 / 4) / 1024);
      els.kbBadge.textContent = sizeKB + ' KB';
      showPreviewMode(true);
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
