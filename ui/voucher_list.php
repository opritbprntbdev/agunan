<?php
session_start();
if (!isset($_SESSION['login'])) {
  header('Location: ../index.php');
  exit;
}
$nama_kc = $_SESSION['nama_kc'] ?? '';
$username = $_SESSION['username'] ?? '';
$kode_kantor = $_SESSION['kode_kantor'] ?? '';

// Default date range: current month
$first_day = date('Y-m-01');
$last_day = date('Y-m-t');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Voucher GL - List</title>
  <link rel="manifest" href="../manifest.json">
  <meta name="theme-color" content="#16a34a">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f5f5f5;color:#333}
    header{padding:12px 16px;background:#16a34a;color:#fff;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px}
    .wrap{padding:12px;max-width:1200px;margin:0 auto}
    .card{background:#fff;border:1px solid #ddd;border-radius:12px;overflow:hidden;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.1)}
    .filter-box{padding:16px;border-bottom:1px solid #eee;display:flex;flex-direction:column;gap:10px}
    .filter-row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .filter-label{font-size:13px;font-weight:600;color:#666;min-width:80px}
    .input,.select{flex:1;min-width:120px;padding:8px 10px;border-radius:8px;border:1px solid #ddd;background:#fff;color:#333;font-size:13px}
    .btn{padding:8px 12px;border-radius:8px;border:none;background:#16a34a;color:#fff;cursor:pointer;font-size:13px;white-space:nowrap}
    .btn:hover{background:#15803d}
    .btn:disabled{opacity:0.5;cursor:not-allowed}
    .btn.secondary{background:#fff;border:1px solid #ddd;color:#333}
    .btn.secondary:hover{background:#f9f9f9}
    .table-container{overflow-x:auto}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th{background:#f9fafb;padding:10px 8px;text-align:left;font-weight:600;color:#374151;border-bottom:2px solid #e5e7eb;position:sticky;top:0}
    td{padding:10px 8px;border-bottom:1px solid #f3f4f6}
    tr:hover{background:#f9fafb}
    .badge{padding:4px 8px;border-radius:999px;font-size:11px;font-weight:600;white-space:nowrap;display:inline-flex;align-items:center;gap:4px}
    .badge.success{background:#dcfce7;color:#16a34a}
    .badge.warning{background:#fef3c7;color:#d97706}
    .badge.gray{background:#f3f4f6;color:#6b7280}
    .amount{text-align:right;font-family:monospace;font-size:12px}
    .hint{font-size:12px;color:#666;text-align:center;padding:20px}
    .spinner{display:inline-block;width:14px;height:14px;border:2px solid #16a34a;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .empty{padding:40px 20px;text-align:center;color:#9ca3af}
    .empty-icon{font-size:48px;margin-bottom:12px}
    @media(max-width:768px){
      th,td{padding:8px 6px;font-size:12px}
      .filter-label{min-width:auto;width:100%}
      .filter-row{flex-direction:column}
      .input,.select{width:100%}
    }
  </style>
</head>
<body>
  <header>
    <div>💳 Voucher GL - Jurnal Umum</div>
    <div style="font-size:12px;opacity:.9">User: <?= htmlspecialchars($username) ?> · KC: <?= htmlspecialchars($nama_kc) ?></div>
  </header>
  
  <div class="wrap">
    <div class="card">
      <div class="filter-box">
        <div class="filter-row">
          <label class="filter-label">Kode Jurnal:</label>
          <select class="select" id="kodeJurnal" style="width:180px">
            <option value="GL">GL - Jurnal Umum</option>
          </select>
        </div>
        
        <div class="filter-row">
          <label class="filter-label">Periode:</label>
          <input type="date" class="input" id="tglFrom" value="<?= $first_day ?>" style="width:140px">
          <span style="color:#9ca3af;font-size:12px">s/d</span>
          <input type="date" class="input" id="tglTo" value="<?= $last_day ?>" style="width:140px">
        </div>
        
        <div class="filter-row">
          <label class="filter-label">Cari No. Bukti:</label>
          <input type="text" class="input" id="searchNoBukti" placeholder="Ketik sebagian no bukti..." style="width:250px">
          <button class="btn" id="searchBtn">🔍 Cari</button>
          <button class="btn secondary" id="resetBtn">🔄 Reset</button>
        </div>
      </div>
      
      <div id="loadingState" class="hint" style="display:none">
        <span class="spinner"></span> Memuat data dari IBS...
      </div>
      
      <div class="table-container" id="tableContainer" style="display:none">
        <table>
          <thead>
            <tr>
              <th style="width:90px">Tgl Trans</th>
              <th style="width:140px">No. Bukti</th>
              <th style="width:100px">Kode Perk</th>
              <th>Keterangan</th>
              <th style="width:110px" class="amount">Debet</th>
              <th style="width:110px" class="amount">Kredit</th>
              <th style="width:100px;text-align:center">Status</th>
              <th style="width:90px;text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody">
          </tbody>
        </table>
      </div>
      
      <div id="emptyState" class="empty" style="display:none">
        <div class="empty-icon">📂</div>
        <div style="font-weight:600;margin-bottom:4px">Tidak ada data</div>
        <div style="font-size:13px;color:#9ca3af">Coba ubah filter atau periode pencarian</div>
      </div>
    </div>
    
    <p class="hint">Klik tombol 📸 untuk memulai capture foto voucher. Status ✅ artinya sudah ada foto tersimpan.</p>
  </div>

  <script>
    const els = {
      kodeJurnal: document.getElementById('kodeJurnal'),
      tglFrom: document.getElementById('tglFrom'),
      tglTo: document.getElementById('tglTo'),
      searchNoBukti: document.getElementById('searchNoBukti'),
      searchBtn: document.getElementById('searchBtn'),
      resetBtn: document.getElementById('resetBtn'),
      loadingState: document.getElementById('loadingState'),
      tableContainer: document.getElementById('tableContainer'),
      tableBody: document.getElementById('tableBody'),
      emptyState: document.getElementById('emptyState')
    };

    let currentData = [];

    function formatAmount(value) {
      if (!value || value == 0) return '-';
      return new Intl.NumberFormat('id-ID').format(value);
    }

    function formatDate(dateStr) {
      if (!dateStr) return '-';
      const parts = dateStr.split('-');
      if (parts.length !== 3) return dateStr;
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function renderTable(data) {
      if (!data || data.length === 0) {
        els.tableContainer.style.display = 'none';
        els.emptyState.style.display = 'block';
        return;
      }

      els.emptyState.style.display = 'none';
      els.tableContainer.style.display = 'block';

      // Group by no_bukti untuk status
      const statusMap = {};
      data.forEach(row => {
        if (row.has_photos) {
          statusMap[row.no_bukti] = {
            has: true,
            count: row.photo_count || 0
          };
        }
      });

      els.tableBody.innerHTML = data.map(row => {
        const status = statusMap[row.no_bukti];
        let statusBadge = '';
        let actionBtn = '';
        
        if (status && status.has) {
          statusBadge = `<span class="badge success">✅ ${status.count} foto</span>`;
          actionBtn = `
            <button class="btn secondary" onclick="previewVoucher('${row.no_bukti}')" style="font-size:11px;padding:5px 8px;margin-right:4px">👁️ Preview</button>
            <button class="btn secondary" onclick="captureVoucher('${row.no_bukti}')" style="font-size:11px;padding:5px 8px">🔄 Update</button>
          `;
        } else {
          statusBadge = `<span class="badge warning">❌ Belum</span>`;
          actionBtn = `<button class="btn" onclick="captureVoucher('${row.no_bukti}')" style="font-size:12px;padding:6px 10px">📸 Foto</button>`;
        }

        return `
          <tr>
            <td>${formatDate(row.tgl_trans)}</td>
            <td style="font-weight:500">${row.no_bukti || '-'}</td>
            <td style="font-family:monospace;font-size:11px">${row.kode_perk || '-'}</td>
            <td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${row.keterangan || ''}">${row.keterangan || '-'}</td>
            <td class="amount">${formatAmount(row.debet)}</td>
            <td class="amount">${formatAmount(row.kredit)}</td>
            <td style="text-align:center">${statusBadge}</td>
            <td style="text-align:center">${actionBtn}</td>
          </tr>
        `;
      }).join('');
    }

    async function loadData() {
      const kodeJurnal = els.kodeJurnal.value;
      const tglFrom = els.tglFrom.value;
      const tglTo = els.tglTo.value;
      const searchNoBukti = els.searchNoBukti.value.trim();

      if (!tglFrom || !tglTo) {
        alert('Periode tanggal harus diisi');
        return;
      }

      els.loadingState.style.display = 'block';
      els.tableContainer.style.display = 'none';
      els.emptyState.style.display = 'none';
      els.searchBtn.disabled = true;

      try {
        const formData = new FormData();
        formData.append('kode_jurnal', kodeJurnal);
        formData.append('tgl_from', tglFrom);
        formData.append('tgl_to', tglTo);
        if (searchNoBukti) {
          formData.append('no_bukti', searchNoBukti);
        }

        const response = await fetch('../process/get_voucher_list.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        // Parse response
        const responseText = await response.text();
        
        let result;
        try {
          result = JSON.parse(responseText);
        } catch (parseError) {
          throw new Error('Server response bukan JSON valid');
        }

        if (result.success) {
          currentData = result.data || [];
          renderTable(currentData);
        } else {
          alert(result.message || 'Gagal memuat data');
          els.emptyState.style.display = 'block';
        }
      } catch (error) {
        alert('Gagal memuat data: ' + error.message);
        els.emptyState.style.display = 'block';
      } finally {
        els.loadingState.style.display = 'none';
        els.searchBtn.disabled = false;
      }
    }

    function resetFilter() {
      els.kodeJurnal.value = 'GL';
      els.tglFrom.value = '<?= $first_day ?>';
      els.tglTo.value = '<?= $last_day ?>';
      els.searchNoBukti.value = '';
      loadData();
    }

    function captureVoucher(noBukti) {
      // Redirect ke halaman capture dengan parameter no_bukti
      window.location.href = `voucher_capture_new.php?no_bukti=${encodeURIComponent(noBukti)}`;
    }

    function previewVoucher(noBukti) {
      // Query ke database lokal untuk ambil pdf_path
      const formData = new FormData();
      formData.append('action', 'get_pdf_path');
      formData.append('no_bukti', noBukti);
      
      fetch('../process/get_pdf_path.php', {
        method: 'POST',
        body: formData
      })
        .then(res => res.json())
        .then(result => {
          if (result.success && result.pdf_path) {
            // Open PDF using actual path from database
            window.open('../' + result.pdf_path, '_blank');
          } else {
            alert('PDF tidak ditemukan. ' + (result.message || 'Voucher mungkin belum di-finalize.'));
          }
        })
        .catch(err => {
          alert('Gagal membuka preview');
        });
    }

    // Event listeners
    els.searchBtn.addEventListener('click', loadData);
    els.resetBtn.addEventListener('click', resetFilter);
    
    els.searchNoBukti.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        loadData();
      }
    });

    // Load data on page load
    window.addEventListener('DOMContentLoaded', loadData);

    // Make captureVoucher global
    window.captureVoucher = captureVoucher;
  </script>
</body>
</html>
