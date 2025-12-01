<?php
session_start();

// CRITICAL SECURITY: Load security guard
require_once '../security_guard.php';

// Additional check for admin role
if ($_SESSION['role'] !== 'admin_pusat') {
    die('<h1 style="color:red;">⛔ Admin Only</h1>');
}

require_once '../config_patroli.php';
require_once '../../vendor/fpdf/fpdf.php';

$kode_kantor = $_GET['kode_kantor'] ?? '';

if (empty($kode_kantor)) {
    die('Kode kantor tidak valid');
}

// Get ruangan list
$stmt = $conn_patroli->prepare("SELECT * FROM ruangan WHERE kode_kantor=? ORDER BY kode_ruangan");
$stmt->bind_param('s', $kode_kantor);
$stmt->execute();
$result = $stmt->get_result();
$ruangan_list = [];
while ($row = $result->fetch_assoc()) {
    $ruangan_list[] = $row;
}
$stmt->close();

if (empty($ruangan_list)) {
    die('<h2 style="color:red;">Tidak ada ruangan ditemukan untuk KC ' . htmlspecialchars($kode_kantor) . '</h2><p><a href="../ui/patroli_manage_room.php?kode_kantor=' . htmlspecialchars($kode_kantor) . '">Kembali</a></p>');
}

// Create PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(10, 10, 10);

// Title page
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, 'QR CODE RUANGAN PATROLI', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, "Kode Kantor: $kode_kantor", 0, 1, 'C');
$pdf->Cell(0, 8, 'Total Ruangan: ' . count($ruangan_list), 0, 1, 'C');
$pdf->Ln(10);

// QR Codes (2 per page)
$counter = 0;
foreach ($ruangan_list as $ruangan) {
    if ($counter % 2 == 0 && $counter > 0) {
        $pdf->AddPage();
    }
    
    $y_pos = ($counter % 2) * 130 + 40;
    
    // Border box
    $pdf->Rect(30, $y_pos - 10, 150, 120);
    
    // Nama ruangan
    $pdf->SetXY(30, $y_pos);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(150, 10, $ruangan['nama_ruangan'], 0, 1, 'C');
    
    // Kode ruangan
    $pdf->SetX(30);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(150, 8, 'Kode: ' . $ruangan['qr_code_content'], 0, 1, 'C');
    
    // QR Code image
    // Path di DB: qr-codes/028/R01.png
    $qr_relative_path = $ruangan['qr_code_image_path'];
    $qr_path = __DIR__ . '/../' . $qr_relative_path;
    
    // Normalize path untuk Windows
    $qr_path = str_replace('/', DIRECTORY_SEPARATOR, $qr_path);
    
    if (file_exists($qr_path) && is_file($qr_path)) {
        $pdf->Image($qr_path, 65, $y_pos + 20, 60, 60, 'PNG');
    } else {
        $pdf->SetXY(30, $y_pos + 40);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(150, 10, '[QR Code tidak ditemukan]', 0, 1, 'C');
        $pdf->SetXY(30, $y_pos + 45);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(150, 5, 'Path: ' . $qr_relative_path, 0, 1, 'C');
    }
    
    // Instruksi
    $pdf->SetXY(30, $y_pos + 85);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(150, 5, "Scan QR Code ini saat patroli untuk mencatat kehadiran di ruangan ini.", 0, 'C');
    
    $counter++;
}

// Output PDF
$pdf->Output('D', "QR_Patroli_{$kode_kantor}_" . date('YmdHis') . ".pdf");
