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

    video,
    canvas,
    img {
      width: 100%;
      max-height: 55vh;
      background: #000;
      border-radius: 10px
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
        <input class="input" id="id_agunan" placeholder="ID Agunan" required>
        <input class="input" id="nama_nasabah" placeholder="Nama Nasabah" required>
        <input class="input" id="no_rek" placeholder="No Rekening" required>
      </div>
      <div style="padding:12px">
        <video id="preview" playsinline autoplay muted></video>
        <canvas id="canvas" hidden></canvas>
        <div class="hint" id="hint">Siapkan kamera. Torch tidak selalu tersedia di semua perangkat.</div>
      </div>
      <div class="toolbar">
        <button class="btn secondary" id="switchBtn">🔁 Ganti</button>
        <button class="btn" id="captureBtn">📸 Ambil</button>
        <button class="btn secondary" id="finishBtn">✅ Selesai</button>
        <span class="badge" id="status">Menyiapkan kamera…</span>
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

    const els = {
      video: document.getElementById('preview'),
      canvas: document.getElementById('canvas'),
      switchBtn: document.getElementById('switchBtn'),
      captureBtn: document.getElementById('captureBtn'),
      finishBtn: document.getElementById('finishBtn'),
      backToCamera: document.getElementById('backToCamera'),
      reviewCard: document.getElementById('reviewCard'),
      thumbs: document.getElementById('thumbs'),
      count: document.getElementById('count'),
      status: document.getElementById('status'),
      hint: document.getElementById('hint'),
      bar: document.getElementById('bar'),
      progressTxt: document.getElementById('progressTxt'),
      saveAllBtn: document.getElementById('saveAllBtn'),
      id_agunan: document.getElementById('id_agunan'),
      nama_nasabah: document.getElementById('nama_nasabah'),
      no_rek: document.getElementById('no_rek'),
    };

    function setStatus(t) { els.status.textContent = t; }

    async function openCamera() {
      try {
        if (stream) stream.getTracks().forEach(t => t.stop());
        const constraints = { video: { facingMode: usingBack ? 'environment' : 'user' }, audio: false };
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        track = stream.getVideoTracks()[0];
        els.video.srcObject = stream;
        await els.video.play();
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
      const v = els.video, c = els.canvas, ctx = c.getContext('2d');
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
      const id_agunan = els.id_agunan.value.trim();
      const nama_nasabah = els.nama_nasabah.value.trim();
      const no_rek = els.no_rek.value.trim();
      if (!id_agunan || !nama_nasabah || !no_rek) { alert('Lengkapi form global'); return; }
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
        alert('Batch selesai. PDF dibuat otomatis.');
        window.location.href = 'history.php';
      } catch (e) {
        alert('Finalisasi gagal: ' + e.message);
        els.saveAllBtn.disabled = false;
      }
    }

    // wire
    els.switchBtn.addEventListener('click', () => { usingBack = !usingBack; openCamera(); });
    els.captureBtn.addEventListener('click', capture);
    els.finishBtn.addEventListener('click', toReview);
    els.backToCamera.addEventListener('click', backCamera);
    els.saveAllBtn.addEventListener('click', uploadAll);

    window.addEventListener('pageshow', openCamera);
    window.addEventListener('beforeunload', () => { if (stream) stream.getTracks().forEach(t => t.stop()); });

    // expose helpers for inline onclick
    window.move = move; window.removeAt = removeAt;
  </script>
</body>

</html>