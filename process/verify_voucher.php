<?php
/**
 * Verify Voucher - Backend API
 * Query data voucher dari Core Banking IBS (READ ONLY)
 * Return JSON dengan data transaksi
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

// Ambil trans_id dari request
$trans_id = isset($_POST['trans_id']) ? trim($_POST['trans_id']) : '';

if (empty($trans_id)) {
    echo json_encode(['success' => false, 'message' => 'Trans ID tidak boleh kosong']);
    exit;
}

// Query ke database IBS (READ ONLY) - Gunakan query biasa untuk avoid prepared statement limit
try {
    // Sanitize input
    $trans_id_escaped = $conn_dbibs->real_escape_string($trans_id);
    
    // Execute query langsung tanpa prepared statement
    $query = "
        SELECT 
          tm.trans_id,
          tm.no_bukti,
          tm.tgl_trans,
          tm.uraian,
          tm.kode_jurnal,
          tm.kode_kantor,
          tm.is_verified,
          tm.user_verified,
          tm.time_verified,
          ak.nama_kantor,
          ak.alamat_kantor,
          ak.kota_kantor,
          ak.nama_pimpinan,
          (SELECT SUM(debet) FROM transaksi_detail WHERE master_id = tm.trans_id) AS total_debet,
          (SELECT SUM(kredit) FROM transaksi_detail WHERE master_id = tm.trans_id) AS total_kredit,
          (SELECT COUNT(*) FROM transaksi_detail WHERE master_id = tm.trans_id) AS jumlah_detail
        FROM transaksi_master tm
        LEFT JOIN app_kode_kantor ak ON tm.kode_kantor = ak.kode_kantor
        WHERE tm.trans_id = '$trans_id_escaped'
        LIMIT 1
    ";
    
    $result = $conn_dbibs->query($query);

    if (!$result) {
        throw new Exception('Query execution failed: ' . $conn_dbibs->error);
    }

    if ($row = $result->fetch_assoc()) {
        // Data ditemukan
        
        // Cek apakah sudah verified
        if ($row['is_verified'] != 1) {
            $result->free();
            echo json_encode([
                'success' => false,
                'message' => 'Transaksi ID "' . htmlspecialchars($trans_id) . '" belum diverifikasi di sistem IBS',
                'not_verified' => true
            ]);
            exit;
        }
        
        // Format data untuk response
        $data = [
            'trans_id' => $row['trans_id'],
            'no_bukti' => $row['no_bukti'],
            'tgl_trans' => $row['tgl_trans'],
            'uraian' => $row['uraian'],
            'kode_jurnal' => $row['kode_jurnal'],
            'kode_kantor_ibs' => $row['kode_kantor'],
            'nama_kantor' => $row['nama_kantor'] ?: '-',
            'alamat_kantor' => $row['alamat_kantor'] ?: '-',
            'kota_kantor' => $row['kota_kantor'] ?: '-',
            'nama_pimpinan' => $row['nama_pimpinan'] ?: '-',
            'total_debet' => $row['total_debet'] ? number_format($row['total_debet'], 2, '.', '') : '0.00',
            'total_kredit' => $row['total_kredit'] ? number_format($row['total_kredit'], 2, '.', '') : '0.00',
            'jumlah_detail' => (int)$row['jumlah_detail'],
            'is_verified' => $row['is_verified'],
            'user_verified' => $row['user_verified'],
            'time_verified' => $row['time_verified'],
            
            // Metadata
            'verified_at' => date('Y-m-d H:i:s'),
            'verified_by' => $_SESSION['username']
        ];
        
        $result->free();

        echo json_encode([
            'success' => true,
            'message' => 'Data voucher ditemukan di sistem IBS',
            'data' => $data
        ]);

    } else {
        // Data tidak ditemukan
        $result->free();
        echo json_encode([
            'success' => false,
            'message' => 'Trans ID "' . htmlspecialchars($trans_id) . '" tidak ditemukan di sistem IBS',
            'not_found' => true
        ]);
    }

} catch (Exception $e) {
    // Free result if exists
    if (isset($result) && $result instanceof mysqli_result) {
        $result->free();
    }
    
    // Error koneksi atau query
    echo json_encode([
        'success' => false,
        'message' => 'Error koneksi ke IBS: ' . $e->getMessage(),
        'error_type' => 'connection_error'
    ]);
}
?>
