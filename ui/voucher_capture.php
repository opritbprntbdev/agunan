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
  <title>Voucher Capture</title>
  <link rel="manifest" href="../manifest.json">
  <meta name="theme-color" content="#16a34a">
  <style>
    *{box-sizing:border-box}body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f5f5f5;color:#333}header{padding:12px 16px;background:#16a34a;color:#fff;display:flex;justify-content:space-between;align-items:center}.wrap{padding:12px;max-width:900px;margin:0 auto}.card{background:#fff;border:1px solid #ddd;border-radius:12px;overflow:hidden;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.1)}.head{padding:12px;border-bottom:1px solid #eee;display:flex;gap:8px;flex-wrap:wrap}.input{flex:1;min-width:160px;padding:10px 12px;border-radius:10px;border:1px solid #ddd;background:#fff;color:#333}.btn{padding:10px 14px;border-radius:10px;border:none;background:#16a34a;color:#fff;cursor:pointer;font-size:14px}.btn.secondary{background:#fff;border:1px solid #ddd;color:#333}.btn.danger{background:#dc2626}.btn:disabled{opacity:0.5;cursor:not-allowed}video,canvas{width:100%;max-height:50vh;background:#000;border-radius:10px}canvas{display:none}.verified-badge{background:#dcfce7;color:#16a34a;padding:6px 10px;border-radius:8px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px}.info-box{background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:12px;margin-top:8px;font-size:13px;line-height:1.5}.info-box strong{color:#0284c7}.spinner{display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}.toolbar{display:flex;gap:8px;justify-content:center;align-items:center;padding:10px;border-top:1px solid #eee;flex-wrap:wrap}.hint{font-size:12px;color:#666;text-align:center;margin:6px 0}.thumbs{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}.thumb{border:1px solid #ddd;border-radius:10px;overflow:hidden;background:#fff}.thumb img{width:100%;height:120px;object-fit:cover;display:block}.thumb .controls{padding:6px;display:flex;flex-direction:column;gap:6px}.thumb .keterangan{font-size:11px;color:#666;font-style:italic;padding:4px;background:#f9f9f9;border-radius:4px;max-height:40px;overflow:hidden}.badge{padding:4px 8px;border-radius:999px;background:#f5f5f5;border:1px solid #ddd;font-size:12px;color:#666}.center{display:flex;gap:12px;justify-content:center;align-items:center;flex-wrap:wrap}.progress{height:10px;background:#e5e7eb;border:1px solid #ddd;border-radius:999px;overflow:hidden}.bar{height:100%;background:#22c55e;width:0%}.keterangan-input{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin-top:8px;font-size:14px}
  </style>
</head>
<body>
  <header>
    <div>💳 Voucher Capture</div>
    <div style="font-size:12px;opacity:.9">User: <?= htmlspecialchars($username) ?> · KC: <?= htmlspecialchars($nama_kc) ?></div>
  </header>
  <div class="wrap">
    <div class="card">
      <div class="head">
        <input class="input" id="trans_id_input" placeholder="Trans ID dari IBS" required style="flex:2">
        <button class="btn" id="verifyBtn" type="button">🔍 Verifikasi</button>
      </div>
      <div id="verifiedInfo" style="padding:12px;display:none">
        <span class="verified-badge">✅ Verified dari IBS</span>
        <div class="info-box" id="voucherInfo"></div>
      </div>
      <div style="padding:12px">
        <video id="preview" playsinline autoplay muted></video>
        <canvas id="canvas"></canvas>
        <div class="hint" id="hint">1. Masukkan Trans ID → klik Verifikasi</div>
      </div>
      <div style="padding:0 12px 12px" id="keteranganSection" hidden>
        <input type="text" class="keterangan-input" id="keteranganInput" placeholder="Keterangan foto (misal: Halaman 1, Lampiran, TTD)">
      </div>
      <div class="toolbar">
        <button class="btn secondary" id="switchBtn" disabled>🔁 Ganti</button>
        <button class="btn" id="captureBtn" disabled>📸 Ambil</button>
        <button class="btn secondary" id="finishBtn" disabled>✅ Selesai</button>
        <span class="badge" id="status">Verifikasi voucher dulu</span>
        <span class="badge">Foto: <span id="count">0</span>/20</span>
      </div>
    </div>
    <div class="card" id="reviewCard" hidden>
      <div style="padding:12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
        <div class="center"><span class="badge" id="reviewInfo">Review batch</span></div>
        <div class="center">
          <button class="btn secondary" id="backToCamera">↩️ Kembali</button>
          <button class="btn" id="saveAllBtn">⬆️ Simpan</button>
        </div>
      </div>
      <div style="padding:12px">
        <div class="thumbs" id="thumbs"></div>
        <div class="hint">Cek keterangan tiap foto. Hapus jika tidak perlu.</div>
      </div>
      <div style="padding:12px">
        <div class="progress"><div class="bar" id="bar"></div></div>
        <div class="center" style="margin-top:6px"><span class="badge" id="progressTxt">0/0</span></div>
      </div>
    </div>
    <p class="hint">Pengolahan berat (simpan & PDF) dilakukan di server.</p>
  </div>
  <script>
let stream,track,usingBack=true;const limit=20;const queue=[];let voucherDataId=null;let verifiedData=null;const els={transIdInput:document.getElementById('trans_id_input'),verifyBtn:document.getElementById('verifyBtn'),verifiedInfo:document.getElementById('verifiedInfo'),voucherInfo:document.getElementById('voucherInfo'),keteranganSection:document.getElementById('keteranganSection'),keteranganInput:document.getElementById('keteranganInput'),preview:document.getElementById('preview'),canvas:document.getElementById('canvas'),hint:document.getElementById('hint'),status:document.getElementById('status'),count:document.getElementById('count'),switchBtn:document.getElementById('switchBtn'),captureBtn:document.getElementById('captureBtn'),finishBtn:document.getElementById('finishBtn'),reviewCard:document.getElementById('reviewCard'),thumbs:document.getElementById('thumbs'),reviewInfo:document.getElementById('reviewInfo'),backToCamera:document.getElementById('backToCamera'),saveAllBtn:document.getElementById('saveAllBtn'),bar:document.getElementById('bar'),progressTxt:document.getElementById('progressTxt')};function formatVoucherInfo(data){return`<strong>Trans ID:</strong> ${data.trans_id}<br><strong>No Bukti:</strong> ${data.no_bukti||'-'}<br><strong>Tanggal:</strong> ${data.tgl_trans}<br><strong>Uraian:</strong> ${data.uraian}<br><strong>Kantor:</strong> ${data.nama_kantor} (${data.kode_kantor_ibs})<br><strong>Total Debet:</strong> Rp ${parseFloat(data.total_debet).toLocaleString('id-ID')}<br><strong>Total Kredit:</strong> Rp ${parseFloat(data.total_kredit).toLocaleString('id-ID')}`}async function verifyVoucher(){const transId=els.transIdInput.value.trim();if(!transId){alert('Masukkan Trans ID');els.transIdInput.focus();return}els.verifyBtn.disabled=true;els.verifyBtn.innerHTML='<span class="spinner"></span> Verifikasi...';try{const formData=new FormData();formData.append('trans_id',transId);const response=await fetch('../process/verify_voucher.php',{method:'POST',body:formData,credentials:'same-origin'});const result=await response.json();if(result.success){verifiedData=result.data;els.verifiedInfo.style.display='block';els.voucherInfo.innerHTML=formatVoucherInfo(result.data);els.keteranganSection.hidden=false;els.captureBtn.disabled=false;els.finishBtn.disabled=false;els.switchBtn.disabled=false;els.status.textContent='Membuka kamera...';els.hint.textContent='2. Data voucher verified. Membuka kamera...';await openCamera();els.status.textContent='Kamera siap ✅';els.hint.textContent='2. Isi keterangan lalu klik 📸 Foto'}else{alert(result.message)}}catch(error){console.error('Verify error:',error);alert('Gagal verifikasi: '+error.message)}finally{els.verifyBtn.disabled=false;els.verifyBtn.innerHTML='🔍 Verifikasi'}}async function openCamera(){const constraints={video:{facingMode:usingBack?'environment':'user',width:{ideal:1920},height:{ideal:1080}}};if(stream)stream.getTracks().forEach(t=>t.stop());stream=await navigator.mediaDevices.getUserMedia(constraints);els.preview.srcObject=stream;track=stream.getVideoTracks()[0]}function setStatus(txt){els.status.textContent=txt}function capture(){if(queue.length>=limit){alert(`Maksimal ${limit} foto`);return}const keterangan=els.keteranganInput.value.trim();const ctx=els.canvas.getContext('2d');const v=els.preview;els.canvas.width=v.videoWidth;els.canvas.height=v.videoHeight;ctx.drawImage(v,0,0);const dataUrl=els.canvas.toDataURL('image/jpeg',0.85);queue.push({dataUrl,keterangan});els.count.textContent=queue.length;els.keteranganInput.value='';els.keteranganInput.focus();setStatus(`Foto ${queue.length}/${limit} ✅`)}function toReview(){if(queue.length===0){alert('Belum ada foto');return}if(stream)stream.getTracks().forEach(t=>t.stop());document.querySelector('.card:first-of-type').hidden=true;els.reviewCard.hidden=false;renderReview()}function backCamera(){els.reviewCard.hidden=true;document.querySelector('.card:first-of-type').hidden=false;openCamera()}function removeAt(i){if(confirm('Hapus foto ini?')){queue.splice(i,1);renderReview();els.count.textContent=queue.length}}function renderReview(){els.thumbs.innerHTML='';queue.forEach((it,i)=>{const div=document.createElement('div');div.className='thumb';div.innerHTML=`<img src="${it.dataUrl}"><div class="controls"><div class="keterangan">${it.keterangan||'(Tanpa keterangan)'}</div><button class='btn danger' onclick='removeAt(${i})' style="font-size:12px;padding:6px">🗑️ Hapus</button></div>`;els.thumbs.appendChild(div)});els.reviewInfo.textContent=`${queue.length} foto siap upload`;els.progressTxt.textContent=`0/${queue.length}`;els.bar.style.width='0%'}async function dataURLToBlob(dataURL){const r=await fetch(dataURL);return await r.blob()}async function uploadAll(){if(!verifiedData){alert('Data voucher belum diverifikasi');return}if(queue.length===0){alert('Belum ada foto');return}els.saveAllBtn.disabled=true;let ok=0;for(let i=0;i<queue.length;i++){const blob=await dataURLToBlob(queue[i].dataUrl);const form=new FormData();form.append('image',blob,`voucher_${i+1}.jpg`);form.append('trans_id',verifiedData.trans_id);form.append('keterangan',queue[i].keterangan||'');form.append('verified_data',JSON.stringify(verifiedData));if(voucherDataId)form.append('voucher_data_id',voucherDataId);try{const resp=await fetch('../process/upload_voucher_photo.php',{method:'POST',body:form,credentials:'same-origin'});const json=await resp.json();if(!json.success)throw new Error(json.message||'Gagal upload');voucherDataId=json.voucher_data_id;ok++;els.progressTxt.textContent=`${ok}/${queue.length}`;els.bar.style.width=`${Math.round(ok/queue.length*100)}%`}catch(e){console.error('Upload gagal',e);alert('Upload gagal di foto ke-'+(i+1)+': '+e.message);els.saveAllBtn.disabled=false;return}}try{const fd=new FormData();fd.append('voucher_data_id',voucherDataId);const r=await fetch('../process/finalize_voucher.php',{method:'POST',body:fd,credentials:'same-origin'});const j=await r.json();if(!j.success)throw new Error(j.message||'Gagal PDF');if(j.notification&&'Notification'in window&&Notification.permission==='granted'){if('serviceWorker'in navigator&&navigator.serviceWorker.controller){navigator.serviceWorker.ready.then(registration=>{registration.showNotification('✅ Voucher Berhasil Disimpan',{body:`${j.notification.username} menyimpan voucher ${j.notification.no_bukti} (${j.notification.jumlah_foto} foto)`,icon:'../assets/icon-192.svg',tag:'voucher-saved',vibrate:[200,100,200]})}).catch(err=>console.log('SW error:',err))}}alert('Batch selesai. PDF dibuat otomatis.');window.location.href='../home.php'}catch(e){alert('Finalisasi gagal: '+e.message);els.saveAllBtn.disabled=false}}els.verifyBtn.addEventListener('click',verifyVoucher);els.switchBtn.addEventListener('click',async()=>{usingBack=!usingBack;setStatus('Membuka kamera...');await openCamera();setStatus('Kamera siap ✅')});els.captureBtn.addEventListener('click',capture);els.finishBtn.addEventListener('click',toReview);els.backToCamera.addEventListener('click',backCamera);els.saveAllBtn.addEventListener('click',uploadAll);window.addEventListener('beforeunload',()=>{if(stream)stream.getTracks().forEach(t=>t.stop())});window.removeAt=removeAt;
  </script>
</body>
</html>
