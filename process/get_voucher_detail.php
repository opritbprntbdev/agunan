<?php
/**
 * Get Voucher Detail - Backend API
 * Query detail voucher dari IBS berdasarkan no_bukti
 * Return JSON dengan master data + detail_rows array
 */

header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config.php';

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Cek login
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil no_bukti dari request
$no_bukti = isset($_POST['no_bukti']) ? trim($_POST['no_bukti']) : '';
$kode_kantor = $_SESSION['kode_kantor'] ?? '';

if (empty($no_bukti)) {
    echo json_encode(['success' => false, 'message' => 'No bukti tidak boleh kosong']);
    exit;
}

if (empty($kode_kantor)) {
    echo json_encode(['success' => false, 'message' => 'Kode kantor tidak ditemukan di session']);
    exit;
}

// Query ke database IBS (READ ONLY)
try {
    // Sanitize input
    $no_bukti_esc = $conn_dbibs->real_escape_string($no_bukti);
    $kode_kantor_esc = $conn_dbibs->real_escape_string($kode_kantor);
    
    // Query master data
    $query_master = "
        SELECT 
            tm.trans_id,
            tm.no_bukti,
            tm.tgl_trans,
            tm.uraian,
            tm.kode_jurnal,
            tm.kode_kantor,
            ak.nama_kantor,
            (SELECT SUM(debet) FROM transaksi_detail WHERE master_id = tm.trans_id) AS total_debet,
            (SELECT SUM(kredit) FROM transaksi_detail WHERE master_id = tm.trans_id) AS total_kredit
        FROM transaksi_master tm
        LEFT JOIN app_kode_kantor ak ON tm.kode_kantor = ak.kode_kantor
        WHERE tm.no_bukti = '$no_bukti_esc'
          AND tm.kode_kantor = '$kode_kantor_esc'
        LIMIT 1
    ";
    
    $result_master = $conn_dbibs->query($query_master);
    
    if (!$result_master) {
        throw new Exception('Query master failed: ' . $conn_dbibs->error);
    }
    
    if (!($row_master = $result_master->fetch_assoc())) {
        echo json_encode([
            'success' => false,
            'message' => 'Voucher dengan no bukti "' . htmlspecialchars($no_bukti) . '" tidak ditemukan'
        ]);
        exit;
    }
    
    $trans_id = $row_master['trans_id'];
    
    // Query detail rows
    $query_detail = "
        SELECT 
            kode_perk,
            debet,
            kredit,
            keterangan
        FROM transaksi_detail
        WHERE master_id = '$trans_id'
        ORDER BY kode_perk
    ";
    
    $result_detail = $conn_dbibs->query($query_detail);
    
    if (!$result_detail) {
        throw new Exception('Query detail failed: ' . $conn_dbibs->error);
    }
    
    $detail_rows = [];
    while ($row_detail = $result_detail->fetch_assoc()) {
        $detail_rows[] = [
            'kode_perk' => $row_detail['kode_perk'],
            'debet' => $row_detail['debet'] ? floatval($row_detail['debet']) : 0,
            'kredit' => $row_detail['kredit'] ? floatval($row_detail['kredit']) : 0,
            'keterangan' => $row_detail['keterangan']
        ];
    }
    
    $result_master->free();
    $result_detail->free();
    
    // Build response data
    $data = [
        'trans_id' => $row_master['trans_id'],
        'no_bukti' => $row_master['no_bukti'],
        'tgl_trans' => $row_master['tgl_trans'],
        'uraian' => $row_master['uraian'],
        'kode_jurnal' => $row_master['kode_jurnal'],
        'kode_kantor' => $row_master['kode_kantor'],
        'nama_kantor' => $row_master['nama_kantor'] ?: '-',
        'total_debet' => $row_master['total_debet'] ? floatval($row_master['total_debet']) : 0,
        'total_kredit' => $row_master['total_kredit'] ? floatval($row_master['total_kredit']) : 0,
        'detail_rows' => $detail_rows,
        'jumlah_detail' => count($detail_rows)
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    error_log('Get voucher detail error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memuat data: ' . $e->getMessage()
    ]);
}
?>
