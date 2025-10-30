<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Auth: gunakan session login yang sudah ada
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validasi field dasar
$required = ['id_agunan', 'nama_nasabah', 'no_rek'];
foreach ($required as $r) {
    if (!isset($_POST[$r]) || $_POST[$r] === '') {
        echo json_encode(['success' => false, 'message' => "Field $r wajib"]);
        exit;
    }
}

$id_agunan = trim($_POST['id_agunan']);
$nama_nasabah = trim($_POST['nama_nasabah']);
$no_rek = trim($_POST['no_rek']);
$ket = isset($_POST['ket']) ? trim($_POST['ket']) : '';
$agunan_data_id = isset($_POST['agunan_data_id']) && $_POST['agunan_data_id'] !== '' ? (int)$_POST['agunan_data_id'] : null;

// Validasi file image
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File image tidak ditemukan / error upload']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;
$created_by = $_SESSION['username'] ?? 'unknown';
$nama_kc = $_SESSION['nama_kc'] ?? '';

// Pastikan folder upload
$year = date('Y');
$month = date('m');
$baseDir = __DIR__ . '/../uploads/' . $year . '/' . $month;
if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0777, true) && !is_dir($baseDir)) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat folder upload']);
        exit;
    }
}

// Mulai transaksi sederhana (MyISAM tidak support transaksi; lakukan urut dan cek error)

// Jika belum ada agunan_data_id, buat record baru
if (!$agunan_data_id) {
    // Buat nama PDF placeholder (sesuai pola yang ada)
    $ts = date('YmdHis');
    $pdf_filename = 'agunan_' . $conn->real_escape_string($id_agunan) . '_' . $ts . '.pdf';
    $pdf_path_rel = 'pdf/' . $year . '/' . $month . '/' . $pdf_filename; // folder mungkin belum ada; generasi PDF bukan di endpoint ini

    $stmt = $conn->prepare("INSERT INTO agunan_data (id_agunan, nama_nasabah, no_rek, user_id, created_by, nama_kc, pdf_filename, pdf_path, total_foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('sssissss', $id_agunan, $nama_nasabah, $no_rek, $user_id, $created_by, $nama_kc, $pdf_filename, $pdf_path_rel);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat agunan_data']);
        exit;
    }
    $agunan_data_id = (int)$stmt->insert_id;
    $stmt->close();
}

// Hitung foto order berikutnya
$next_order = 1;
$stmt = $conn->prepare('SELECT COALESCE(MAX(foto_order),0) + 1 AS next_order FROM agunan_foto WHERE agunan_data_id = ?');
$stmt->bind_param('i', $agunan_data_id);
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
$filename = 'foto_' . $agunan_data_id . '_' . $next_order . '_' . $ts_short . '.jpg';
$destRel = 'uploads/' . $year . '/' . $month . '/' . $filename;
$destAbs = __DIR__ . '/../' . $destRel;

if (!move_uploaded_file($tmp, $destAbs)) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file ke server']);
    exit;
}

// Insert agunan_foto
$stmt = $conn->prepare('INSERT INTO agunan_foto (agunan_data_id, foto_filename, foto_path, keterangan, file_size, foto_order) VALUES (?, ?, ?, ?, ?, ?)');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('isssii', $agunan_data_id, $filename, $destRel, $ket, $fileSize, $next_order);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Gagal insert foto']);
    exit;
}
$foto_id = (int)$stmt->insert_id;
$stmt->close();

// Update total_foto
$conn->query('UPDATE agunan_data SET total_foto = total_foto + 1 WHERE id = ' . (int)$agunan_data_id);

echo json_encode([
    'success' => true,
    'message' => 'Foto tersimpan',
    'agunan_data_id' => $agunan_data_id,
    'foto_id' => $foto_id,
    'next_order' => $next_order + 1,
    'file' => $destRel
]);
exit;
?>
