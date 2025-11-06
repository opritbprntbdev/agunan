<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$agunan_data_id = isset($_POST['agunan_data_id']) ? (int)$_POST['agunan_data_id'] : 0;
if ($agunan_data_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'agunan_data_id wajib']);
    exit;
}

// Ambil data batch
$stmt = $conn->prepare('SELECT id_agunan FROM agunan_data WHERE id = ?');
$stmt->bind_param('i', $agunan_data_id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$stmt->close();
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Batch tidak ditemukan']);
    exit;
}
$id_agunan = $data['id_agunan'];

// Ambil foto-foto
$stmt = $conn->prepare('SELECT foto_path FROM agunan_foto WHERE agunan_data_id = ? ORDER BY foto_order ASC, id ASC');
$stmt->bind_param('i', $agunan_data_id);
$stmt->execute();
$photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$photos || count($photos) === 0) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada foto untuk dibuat PDF']);
    exit;
}

// Siapkan folder PDF
$year = date('Y');
$month = date('m');
$pdfDirRel = 'pdf/' . $year . '/' . $month;
$pdfDirAbs = __DIR__ . '/../' . $pdfDirRel;
if (!is_dir($pdfDirAbs)) {
    if (!mkdir($pdfDirAbs, 0777, true) && !is_dir($pdfDirAbs)) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat folder PDF']);
        exit;
    }
}

// Nama file PDF
$ts = date('YmdHis');
$pdfFilename = 'agunan_' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $id_agunan) . '_' . $ts . '.pdf';
$pdfPathRel = $pdfDirRel . '/' . $pdfFilename;
$pdfPathAbs = $pdfDirAbs . '/' . $pdfFilename;

// Generate PDF dengan FPDF
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';

try {
    $pdf = new FPDF('P', 'mm', 'A4');
    $margin = 10; // margin 10mm
    foreach ($photos as $ph) {
        $imgRel = $ph['foto_path'];
        $imgAbs = __DIR__ . '/../' . $imgRel;
        if (!file_exists($imgAbs)) {
            continue; // skip yang tidak ada
        }
        $pdf->AddPage();
        // Hitung scaling agar fit ke halaman
        list($w, $h) = getimagesize($imgAbs);
        $pageW = $pdf->GetPageWidth() - 2*$margin;
        $pageH = $pdf->GetPageHeight() - 2*$margin;
        $ratio = $w/$h;
        $targetW = $pageW;
        $targetH = $targetW / $ratio;
        if ($targetH > $pageH) {
            $targetH = $pageH;
            $targetW = $targetH * $ratio;
        }
        $x = ($pdf->GetPageWidth() - $targetW) / 2;
        $y = ($pdf->GetPageHeight() - $targetH) / 2;
        $pdf->Image($imgAbs, $x, $y, $targetW, $targetH);
    }
    $pdf->Output('F', $pdfPathAbs);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'FPDF error: ' . $e->getMessage()]);
    exit;
}

// Update agunan_data
$stmt = $conn->prepare('UPDATE agunan_data SET pdf_filename = ?, pdf_path = ? WHERE id = ?');
$stmt->bind_param('ssi', $pdfFilename, $pdfPathRel, $agunan_data_id);
$stmt->execute();
$stmt->close();

// Ambil username dari session untuk notifikasi
$username = $_SESSION['username'] ?? 'User';
$jumlahFoto = count($photos);

echo json_encode([
    'success' => true, 
    'pdf_filename' => $pdfFilename, 
    'pdf_path' => $pdfPathRel,
    'notification' => [
        'username' => $username,
        'id_agunan' => $id_agunan,
        'jumlah_foto' => $jumlahFoto
    ]
]);
exit;
?>
