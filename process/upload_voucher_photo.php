<?php
/**
 * Upload Voucher Photo - Sequential Upload Handler
 * Simpan foto voucher satu per satu dengan KETERANGAN PER FOTO
 * Data IBS hanya dibaca, tidak ada INSERT/UPDATE ke IBS
 */

// Prevent any output before JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_upload.log');

session_start();
require_once __DIR__ . '/../config.php';

// Clear any buffered output and set JSON header
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Auth
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validasi field dasar
$required = ['trans_id'];
foreach ($required as $r) {
    if (!isset($_POST[$r]) || $_POST[$r] === '') {
        echo json_encode(['success' => false, 'message' => "Field $r wajib"]);
        exit;
    }
}

$trans_id = trim($_POST['trans_id']);
$keterangan = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : ''; // KETERANGAN PER FOTO
$voucher_data_id = isset($_POST['voucher_data_id']) && $_POST['voucher_data_id'] !== '' ? (int)$_POST['voucher_data_id'] : null;

// Validasi file image
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File image tidak ditemukan / error upload']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;
$created_by = $_SESSION['username'] ?? 'unknown';
$nama_kc = $_SESSION['nama_kc'] ?? '';
$kode_kantor = $_SESSION['kode_kantor'] ?? '000';

// Parse verified_data dari JSON jika ada
$verified_data = null;
if (isset($_POST['verified_data']) && !empty($_POST['verified_data'])) {
    $verified_data = json_decode($_POST['verified_data'], true);
}

// Pastikan folder upload
$year = date('Y');
$month = date('m');
$baseDir = __DIR__ . '/../uploads/voucher/' . $year . '/' . $month;
if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0777, true) && !is_dir($baseDir)) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat folder upload']);
        exit;
    }
}

// Jika belum ada voucher_data_id, buat record baru
if (!$voucher_data_id) {
    // Buat nama PDF placeholder
    $ts = date('YmdHis');
    $pdf_filename = 'voucher_' . $conn->real_escape_string($trans_id) . '_' . $ts . '.pdf';
    $pdf_path_rel = 'pdf/voucher/' . $year . '/' . $month . '/' . $pdf_filename;
    
    // Jika ada data verified dari IBS, simpan semua field
    if ($verified_data) {
        $stmt = $conn->prepare("
            INSERT INTO voucher_data (
                trans_id, no_bukti, tgl_trans, uraian, kode_jurnal, 
                kode_kantor_ibs, nama_kantor, alamat_kantor, kota_kantor, nama_pimpinan,
                total_debet, total_kredit,
                verified_from_ibs, verified_at, verified_by,
                user_id, photo_taken_by, kode_kantor, nama_kc,
                pdf_filename, pdf_path, total_foto, photo_taken_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), ?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
            exit;
        }
        
        // Type string: 19 parameters (verified_at di-hardcode NOW(), photo_taken_at di-hardcode NOW())
        $stmt->bind_param(
            'ssssssssssddssissss',  // 19 chars: 10s + 2d + 1s + 1i + 5s = 19
            $verified_data['trans_id'],       // s - 1
            $verified_data['no_bukti'],       // s - 2
            $verified_data['tgl_trans'],      // s - 3
            $verified_data['uraian'],         // s - 4
            $verified_data['kode_jurnal'],    // s - 5
            $verified_data['kode_kantor_ibs'], // s - 6
            $verified_data['nama_kantor'],    // s - 7
            $verified_data['alamat_kantor'],  // s - 8
            $verified_data['kota_kantor'],    // s - 9
            $verified_data['nama_pimpinan'],  // s - 10
            $verified_data['total_debet'],    // d - 11
            $verified_data['total_kredit'],   // d - 12
            $verified_data['verified_by'],    // s - 13
            $user_id,                         // i - 14
            $created_by,                      // s - 15 (photo_taken_by)
            $kode_kantor,                     // s - 16
            $nama_kc,                         // s - 17
            $pdf_filename,                    // s - 18
            $pdf_path_rel                     // s - 19
        );
        
    } else {
        // Mode manual (jarang terjadi untuk voucher, tapi tetap support)
        $stmt = $conn->prepare("
            INSERT INTO voucher_data (
                trans_id, user_id, photo_taken_by, kode_kantor, nama_kc,
                pdf_filename, pdf_path, total_foto, verified_from_ibs, photo_taken_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, NOW())
        ");
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param('sissss', 
            $trans_id, $user_id, $created_by, $kode_kantor, $nama_kc,
            $pdf_filename, $pdf_path_rel
        );
    }
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat voucher_data: ' . $stmt->error]);
        exit;
    }
    $voucher_data_id = (int)$stmt->insert_id;
    $stmt->close();
}

// Hitung foto order berikutnya
$next_order = 1;
$stmt = $conn->prepare('SELECT COALESCE(MAX(foto_order),0) + 1 AS next_order FROM voucher_foto WHERE voucher_data_id = ?');
$stmt->bind_param('i', $voucher_data_id);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $next_order = (int)$row['next_order'];
    }
}
$stmt->close();

// Simpan file
$tmp = $_FILES['image']['tmp_name'];
$fileSize = (int)$_FILES['image']['size'];
$ts_short = time();
$filename = 'voucher_' . $voucher_data_id . '_' . $next_order . '_' . $ts_short . '.jpg';
$destRel = 'uploads/voucher/' . $year . '/' . $month . '/' . $filename;
$destAbs = __DIR__ . '/../' . $destRel;

if (!move_uploaded_file($tmp, $destAbs)) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file ke server']);
    exit;
}

// Insert voucher_foto dengan KETERANGAN
$stmt = $conn->prepare('INSERT INTO voucher_foto (voucher_data_id, foto_filename, foto_path, keterangan, file_size, foto_order, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('isssiis', $voucher_data_id, $filename, $destRel, $keterangan, $fileSize, $next_order, $created_by);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Gagal insert foto']);
    exit;
}
$foto_id = (int)$stmt->insert_id;
$stmt->close();

// Update total_foto
$conn->query('UPDATE voucher_data SET total_foto = total_foto + 1 WHERE id = ' . (int)$voucher_data_id);

echo json_encode([
    'success' => true,
    'message' => 'Foto voucher tersimpan',
    'voucher_data_id' => $voucher_data_id,
    'foto_id' => $foto_id,
    'next_order' => $next_order + 1,
    'foto_path' => $destRel
]);
exit;
?>
