<?php
session_start();

// CRITICAL SECURITY: Load security guard
require_once '../security_guard.php';

require_once '../config_patroli.php';

$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? '';
$nama_lengkap = $_SESSION['nama_lengkap'] ?? '';

// Get list cabang (kode_kantor unique dari user_security)
$cabang_list = [];
$stmt_cabang = $conn_patroli->query("SELECT DISTINCT kode_kantor, nama_kc FROM users_security WHERE role='penjaga' AND is_active=1 ORDER BY kode_kantor");
while ($row = $stmt_cabang->fetch_assoc()) {
    $cabang_list[] = $row;
}

// Get existing ruangan
$ruangan_list = [];
$selected_kantor = $_GET['kode_kantor'] ?? '';
if ($selected_kantor) {
    $stmt_ruangan = $conn_patroli->prepare("SELECT * FROM ruangan WHERE kode_kantor=? ORDER BY kode_ruangan");
    $stmt_ruangan->bind_param('s', $selected_kantor);
    $stmt_ruangan->execute();
    $result_ruangan = $stmt_ruangan->get_result();
    while ($row = $result_ruangan->fetch_assoc()) {
        $ruangan_list[] = $row;
    }
    $stmt_ruangan->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, minimal-ui, viewport-fit=cover">
  <meta name="theme-color" content="#1e40af">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title>Kelola Ruangan & QR Code</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: system-ui, -apple-system, sans-serif;
      background: linear-gradient(135deg, #1e3a8a 0%, #312e81 100%);
      min-height: 100vh;
      padding-bottom: 80px;
    }

    .header {
      background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
      color: #fff;
      padding: 16px 20px;
      box-shadow: 0 2px 12px rgba(0,0,0,.2);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .back-btn {
      background: rgba(255,255,255,.2);
      color: #fff;
      border: none;
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 20px;
      cursor: pointer;
      text-decoration: none;
    }

    .header-title {
      font-size: 18px;
      font-weight: 700;
      flex: 1;
      text-align: center;
      margin: 0 12px;
    }

    .container {
      padding: 20px;
    }

    .card {
      background: #fff;
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }

    .card-title {
      font-size: 16px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #475569;
      margin-bottom: 6px;
    }

    .form-control {
      width: 100%;
      padding: 12px 14px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 14px;
      transition: all .3s;
    }

    .form-control:focus {
      outline: none;
      border-color: #3b82f6;
      background: #eff6ff;
    }

    .btn {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all .3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: #fff;
    }

    .btn-primary:active {
      transform: scale(0.98);
    }

    .btn-success {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: #fff;
    }

    .btn-danger {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: #fff;
      padding: 8px 12px;
      font-size: 13px;
      width: auto;
    }

    .ruangan-list {
      margin-top: 20px;
    }

    .ruangan-item {
      background: #f8fafc;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      padding: 14px;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .ruangan-info {
      flex: 1;
    }

    .ruangan-code {
      font-size: 15px;
      font-weight: 700;
      color: #1e293b;
    }

    .ruangan-name {
      font-size: 13px;
      color: #64748b;
      margin-top: 2px;
    }

    .ruangan-actions {
      display: flex;
      gap: 8px;
    }

    .btn-icon {
      background: #fff;
      border: 2px solid #e2e8f0;
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 18px;
      cursor: pointer;
      text-decoration: none;
      color: #475569;
    }

    .qr-preview {
      display: none;
      margin-top: 20px;
      text-align: center;
    }

    .qr-preview img {
      max-width: 200px;
      border: 3px solid #e2e8f0;
      border-radius: 12px;
      padding: 12px;
      background: #fff;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      margin-bottom: 16px;
      font-size: 13px;
    }

    .alert-success {
      background: #d1fae5;
      color: #065f46;
      border: 2px solid #10b981;
    }

    .alert-error {
      background: #fee2e2;
      color: #991b1b;
      border: 2px solid #ef4444;
    }

    .alert-info {
      background: #dbeafe;
      color: #1e40af;
      border: 2px solid #3b82f6;
    }

    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background: #fff;
      box-shadow: 0 -2px 12px rgba(0,0,0,.1);
      padding: 12px 20px;
      display: flex;
      justify-content: space-around;
    }

    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      color: #94a3b8;
      font-size: 11px;
      padding: 8px 12px;
      border-radius: 12px;
    }

    .nav-item.active {
      color: #1e40af;
      background: #eff6ff;
    }

    .nav-icon {
      font-size: 22px;
      margin-bottom: 4px;
    }

    .loading {
      display: none;
      text-align: center;
      padding: 20px;
      color: #64748b;
    }

    .spinner {
      border: 3px solid #f3f4f6;
      border-top: 3px solid #3b82f6;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 0 auto 10px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
  <div class="header">
    <a href="patroli_home.php" class="back-btn">←</a>
    <div class="header-title">🏢 Kelola Ruangan</div>
    <div style="width: 44px;"></div>
  </div>

  <div class="container">
    <!-- Pilih Cabang -->
    <div class="card">
      <div class="card-title">🏦 Pilih Cabang</div>
      <form method="GET" action="">
        <div class="form-group">
          <label class="form-label">Kode Kantor Cabang</label>
          <select name="kode_kantor" class="form-control" onchange="this.form.submit()">
            <option value="">-- Pilih Cabang --</option>
            <?php foreach ($cabang_list as $cabang): ?>
              <option value="<?= htmlspecialchars($cabang['kode_kantor']) ?>" <?= $selected_kantor === $cabang['kode_kantor'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cabang['kode_kantor']) ?> - <?= htmlspecialchars($cabang['nama_kc']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <?php if ($selected_kantor): ?>
    <!-- Generate Ruangan -->
    <div class="card">
      <div class="card-title">➕ Generate Ruangan & QR Code</div>
      
      <div id="alertContainer"></div>
      
      <form id="formGenerate" onsubmit="return handleGenerate(event)">
        <input type="hidden" name="kode_kantor" value="<?= htmlspecialchars($selected_kantor) ?>">
        
        <div class="form-group">
          <label class="form-label">Jumlah Ruangan</label>
          <input type="number" name="jumlah_ruangan" class="form-control" min="1" max="20" value="7" required>
          <small style="font-size: 11px; color: #64748b; display: block; margin-top: 4px;">
            Akan otomatis generate: R01, R02, R03, ... R07
          </small>
        </div>

        <div class="form-group">
          <label class="form-label">Prefix Nama Ruangan (Optional)</label>
          <input type="text" name="prefix_nama" class="form-control" placeholder="Contoh: Ruang" value="Ruang">
          <small style="font-size: 11px; color: #64748b; display: block; margin-top: 4px;">
            Hasil: "Ruang 01", "Ruang 02", dst. Kosongkan jika tidak perlu.
          </small>
        </div>

        <button type="submit" class="btn btn-success">
          <span>✨ Generate Ruangan & QR Code</span>
        </button>
      </form>

      <div class="loading" id="loadingGenerate">
        <div class="spinner"></div>
        <div>Generating QR Codes...</div>
      </div>
    </div>

    <!-- List Ruangan -->
    <?php if (!empty($ruangan_list)): ?>
    <div class="card">
      <div class="card-title">📋 Daftar Ruangan (<?= count($ruangan_list) ?>)</div>
      
      <div class="alert alert-info">
        💡 QR Code tersimpan di folder: <strong>/patroli/qr-codes/<?= htmlspecialchars($selected_kantor) ?>/</strong>
      </div>

      <div class="ruangan-list">
        <?php foreach ($ruangan_list as $ruangan): ?>
        <div class="ruangan-item">
          <div class="ruangan-info">
            <div class="ruangan-code"><?= htmlspecialchars($ruangan['kode_ruangan']) ?></div>
            <div class="ruangan-name"><?= htmlspecialchars($ruangan['nama_ruangan']) ?></div>
          </div>
          <div class="ruangan-actions">
            <a href="../qr-codes/<?= htmlspecialchars($selected_kantor) ?>/<?= htmlspecialchars($ruangan['kode_ruangan']) ?>.png" 
               target="_blank" class="btn-icon" title="Lihat QR">📷</a>
            <button onclick="deleteRuangan(<?= $ruangan['id'] ?>, '<?= htmlspecialchars($ruangan['kode_ruangan']) ?>')" 
                    class="btn-icon" title="Hapus" style="color: #ef4444;">🗑️</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="display: flex; gap: 10px; margin-top: 16px;">
        <button onclick="printAllQR()" class="btn btn-primary" style="flex: 1;">
          <span>🖨️ Print QR Code</span>
        </button>
        <button onclick="deleteAllAndRegenerate()" class="btn btn-danger" style="flex: 1; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
          <span>🔄 Delete All & Regenerate</span>
        </button>
      </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>
  </div>

  <!-- Bottom Nav -->
  <div class="bottom-nav">
    <a href="patroli_home.php" class="nav-item">
      <div class="nav-icon">🏠</div>
      <div>Home</div>
    </a>
    <a href="patroli_report.php" class="nav-item">
      <div class="nav-icon">📊</div>
      <div>Report</div>
    </a>
    <a href="patroli_manage_room.php" class="nav-item active">
      <div class="nav-icon">🏢</div>
      <div>Ruangan</div>
    </a>
    <a href="../../logout.php" class="nav-item" onclick="return confirm('Yakin ingin logout?')">
      <div class="nav-icon">🚪</div>
      <div>Logout</div>
    </a>
  </div>

  <script>
    // Debug mode - show all responses
    window.DEBUG_MODE = true;
    
    async function handleGenerate(e) {
      e.preventDefault();
      
      const form = e.target;
      const formData = new FormData(form);
      const alertContainer = document.getElementById('alertContainer');
      const loadingDiv = document.getElementById('loadingGenerate');
      
      // Show loading
      loadingDiv.style.display = 'block';
      alertContainer.innerHTML = '';
      
      try {
        const response = await fetch('../process/generate_qr.php', {
          method: 'POST',
          body: formData
        });
        
        // Get response text first for debugging
        const responseText = await response.text();
        
        if (window.DEBUG_MODE) {
          console.log('Raw response:', responseText);
        }
        
        // Try to parse JSON
        let result;
        try {
          result = JSON.parse(responseText);
        } catch (e) {
          throw new Error('Invalid JSON response: ' + responseText.substring(0, 200));
        }
        
        loadingDiv.style.display = 'none';
        
        if (result.success) {
          alertContainer.innerHTML = `
            <div class="alert alert-success">
              ✅ ${result.message}<br>
              <small>Generated: ${result.generated} ruangan, Skipped: ${result.skipped}</small>
              ${result.errors && result.errors.length > 0 ? '<br><small>Errors: ' + result.errors.join(', ') + '</small>' : ''}
            </div>
          `;
          
          // Reload after 2 seconds
          setTimeout(() => {
            window.location.reload();
          }, 2000);
        } else {
          alertContainer.innerHTML = `
            <div class="alert alert-error">
              ❌ ${result.message}
              ${result.errors ? '<br><small>' + result.errors.join('<br>') + '</small>' : ''}
            </div>
          `;
        }
      } catch (error) {
        loadingDiv.style.display = 'none';
        alertContainer.innerHTML = `
          <div class="alert alert-error">
            ❌ Error: ${error.message}
          </div>
        `;
        console.error('Generate error:', error);
      }
    }

    async function deleteRuangan(id, kodeRuangan) {
      if (!confirm(`Yakin hapus ruangan ${kodeRuangan}?`)) return;
      
      try {
        const response = await fetch('../process/delete_ruangan.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `id=${id}`
        });
        
        const result = await response.json();
        
        if (result.success) {
          alert('✅ Ruangan berhasil dihapus');
          window.location.reload();
        } else {
          alert('❌ ' + result.message);
        }
      } catch (error) {
        alert('❌ Error: ' + error.message);
      }
    }

    async function deleteAllAndRegenerate() {
      const kodeKantor = '<?= htmlspecialchars($selected_kantor) ?>';
      
      if (!confirm(`⚠️ Ini akan MENGHAPUS SEMUA ruangan KC ${kodeKantor} dan generate ulang!\n\nYakin lanjutkan?`)) {
        return;
      }
      
      const alertContainer = document.getElementById('alertContainer');
      const loadingDiv = document.getElementById('loadingGenerate');
      
      try {
        // Show loading
        loadingDiv.style.display = 'block';
        alertContainer.innerHTML = '<div class="alert alert-info">⏳ Menghapus ruangan lama...</div>';
        
        // Step 1: Delete all
        const deleteResponse = await fetch('../process/delete_all_ruangan.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `kode_kantor=${kodeKantor}`
        });
        
        const deleteResult = await deleteResponse.json();
        
        if (!deleteResult.success) {
          throw new Error('Gagal menghapus: ' + deleteResult.message);
        }
        
        alertContainer.innerHTML = '<div class="alert alert-info">✅ Dihapus! Generating QR codes baru...</div>';
        
        // Step 2: Generate new
        const jumlahRuangan = document.querySelector('input[name="jumlah_ruangan"]').value || 7;
        const prefixNama = document.querySelector('input[name="prefix_nama"]').value || 'Ruang';
        
        const formData = new FormData();
        formData.append('kode_kantor', kodeKantor);
        formData.append('jumlah_ruangan', jumlahRuangan);
        formData.append('prefix_nama', prefixNama);
        
        const genResponse = await fetch('../process/generate_qr.php', {
          method: 'POST',
          body: formData
        });
        
        const genText = await genResponse.text();
        const genResult = JSON.parse(genText);
        
        loadingDiv.style.display = 'none';
        
        if (genResult.success) {
          alertContainer.innerHTML = `
            <div class="alert alert-success">
              ✅ Berhasil! ${genResult.generated} ruangan baru telah di-generate.<br>
              <small>Deleted: ${deleteResult.deleted}, Generated: ${genResult.generated}</small>
            </div>
          `;
          
          setTimeout(() => {
            window.location.reload();
          }, 2000);
        } else {
          alertContainer.innerHTML = `
            <div class="alert alert-error">
              ⚠️ Generate error: ${genResult.message}
              ${genResult.errors ? '<br><small>' + genResult.errors.join('<br>') + '</small>' : ''}
            </div>
          `;
        }
        
      } catch (error) {
        loadingDiv.style.display = 'none';
        alertContainer.innerHTML = `
          <div class="alert alert-error">
            ❌ Error: ${error.message}
          </div>
        `;
        console.error('Delete & Regenerate error:', error);
      }
    }

    function printAllQR() {
      const kodeKantor = '<?= htmlspecialchars($selected_kantor) ?>';
      window.open(`../process/print_qr_pdf.php?kode_kantor=${kodeKantor}`, '_blank');
    }
  </script>
</body>
</html>
