<?php
/**
 * Get Voucher List - Backend API
 * Query GL journal transactions dari IBS dengan filter
 * Return JSON dengan list detail rows + status capture
 */

// Prevent any output before JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../config.php';

// Clear any buffered output and set JSON header
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

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

// Ambil parameters
$kode_jurnal = isset($_POST['kode_jurnal']) ? trim($_POST['kode_jurnal']) : 'GL';
$tgl_from = isset($_POST['tgl_from']) ? trim($_POST['tgl_from']) : '';
$tgl_to = isset($_POST['tgl_to']) ? trim($_POST['tgl_to']) : '';
$no_bukti_search = isset($_POST['no_bukti']) ? trim($_POST['no_bukti']) : '';
$kode_kantor = $_SESSION['kode_kantor'] ?? '';

// Validasi
if (empty($tgl_from) || empty($tgl_to)) {
    echo json_encode(['success' => false, 'message' => 'Periode tanggal harus diisi']);
    exit;
}

if (empty($kode_kantor)) {
    echo json_encode(['success' => false, 'message' => 'Kode kantor tidak ditemukan di session']);
    exit;
}

// Query ke database IBS (READ ONLY) - Gunakan query biasa untuk avoid prepared statement limit
try {
    // Cek koneksi IBS
    if (!$conn_dbibs || $conn_dbibs->connect_errno) {
        throw new Exception('Koneksi ke database IBS gagal: ' . ($conn_dbibs ? $conn_dbibs->connect_error : 'Connection object not found'));
    }
    
    // Sanitize inputs
    $kode_jurnal_esc = $conn_dbibs->real_escape_string($kode_jurnal);
    $tgl_from_esc = $conn_dbibs->real_escape_string($tgl_from);
    $tgl_to_esc = $conn_dbibs->real_escape_string($tgl_to);
    $kode_kantor_esc = $conn_dbibs->real_escape_string($kode_kantor);
    
    // Build query
    $query = "
        SELECT 
            a.trans_id,
            a.kode_kantor,
            a.kode_jurnal,
            a.no_bukti,
            a.tgl_trans,
            b.kode_perk,
            b.debet,
            b.kredit,
            b.keterangan
        FROM transaksi_master a
        INNER JOIN transaksi_detail b ON a.trans_id = b.master_id
        WHERE a.tgl_trans BETWEEN '$tgl_from_esc' AND '$tgl_to_esc'
          AND a.kode_jurnal = '$kode_jurnal_esc'
          AND a.kode_kantor = '$kode_kantor_esc'
    ";
    
    // Optional no_bukti search
    if (!empty($no_bukti_search)) {
        $no_bukti_esc = $conn_dbibs->real_escape_string($no_bukti_search);
        $query .= " AND a.no_bukti LIKE '%$no_bukti_esc%'";
    }
    
    $query .= " ORDER BY a.tgl_trans DESC, a.no_bukti, b.kode_perk";
    
    $result = $conn_dbibs->query($query);
    
    if (!$result) {
        throw new Exception('Query execution failed: ' . $conn_dbibs->error);
    }
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'trans_id' => $row['trans_id'],
            'kode_kantor' => $row['kode_kantor'],
            'kode_jurnal' => $row['kode_jurnal'],
            'no_bukti' => $row['no_bukti'],
            'tgl_trans' => $row['tgl_trans'],
            'kode_perk' => $row['kode_perk'],
            'debet' => $row['debet'] ? floatval($row['debet']) : 0,
            'kredit' => $row['kredit'] ? floatval($row['kredit']) : 0,
            'keterangan' => $row['keterangan']
        ];
    }
    
    $result->free();
    
    // Sekarang cek status capture dari local database
    // Ambil semua no_bukti yang sudah pernah di-capture
    if (count($data) > 0) {
        // Collect unique no_bukti
        $no_bukti_list = array_unique(array_column($data, 'no_bukti'));
        
        // Query local database untuk cek status
        $placeholders = str_repeat('?,', count($no_bukti_list) - 1) . '?';
        $stmt_local = $conn->prepare("
            SELECT 
                vd.no_bukti,
                COUNT(vf.id) as photo_count
            FROM voucher_data vd
            LEFT JOIN voucher_foto vf ON vd.id = vf.voucher_data_id
            WHERE vd.no_bukti IN ($placeholders)
              AND vd.kode_kantor = ?
            GROUP BY vd.no_bukti
        ");
        
        if ($stmt_local) {
            // Bind parameters
            $types = str_repeat('s', count($no_bukti_list)) . 's';
            $params = array_merge($no_bukti_list, [$kode_kantor]);
            $stmt_local->bind_param($types, ...$params);
            $stmt_local->execute();
            $result_local = $stmt_local->get_result();
            
            // Build status map
            $status_map = [];
            while ($row_local = $result_local->fetch_assoc()) {
                $status_map[$row_local['no_bukti']] = [
                    'has_photos' => true,
                    'photo_count' => (int)$row_local['photo_count']
                ];
            }
            
            $stmt_local->close();
            
            // Merge status ke data
            foreach ($data as &$item) {
                if (isset($status_map[$item['no_bukti']])) {
                    $item['has_photos'] = true;
                    $item['photo_count'] = $status_map[$item['no_bukti']]['photo_count'];
                } else {
                    $item['has_photos'] = false;
                    $item['photo_count'] = 0;
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'count' => count($data),
        'filters' => [
            'kode_jurnal' => $kode_jurnal,
            'tgl_from' => $tgl_from,
            'tgl_to' => $tgl_to,
            'no_bukti' => $no_bukti_search,
            'kode_kantor' => $kode_kantor
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Get voucher list error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memuat data: ' . $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
}
?>
