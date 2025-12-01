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

// Ambil data batch lengkap
$stmt = $conn->prepare('SELECT * FROM agunan_data WHERE id = ?');
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
$nama_nasabah = $data['nama_nasabah'];
$no_rek = $data['no_rek'];
$kode_kantor = $data['kode_kantor'];
$created_at = $data['created_at'];

// Parse verified_data jika ada
$verified_data = null;
if (!empty($data['verified_data'])) {
    $verified_data = json_decode($data['verified_data'], true);
}

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

// ========== FIX: Delete PDF lama jika ada (saat edit agunan) ==========
if (!empty($data['pdf_path']) && file_exists(__DIR__ . '/../' . $data['pdf_path'])) {
    $oldPdfPath = __DIR__ . '/../' . $data['pdf_path'];
    if (@unlink($oldPdfPath)) {
        error_log("Deleted old PDF: " . $oldPdfPath);
    } else {
        error_log("Failed to delete old PDF: " . $oldPdfPath);
    }
}
// ========== END FIX ==========

// Nama file PDF
$ts = date('YmdHis');
$pdfFilename = 'agunan_' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $id_agunan) . '_' . $ts . '.pdf';
$pdfPathRel = $pdfDirRel . '/' . $pdfFilename;
$pdfPathAbs = $pdfDirAbs . '/' . $pdfFilename;

// Generate PDF dengan FPDF
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';

class PDF extends FPDF {
    private $agunanData;
    private $verifiedData;
    private $totalFoto;
    
    function __construct($agunanData, $verifiedData, $totalFoto) {
        parent::__construct('P', 'mm', 'A4');
        $this->agunanData = $agunanData;
        $this->verifiedData = $verifiedData;
        $this->totalFoto = $totalFoto;
    }
    
    function Header() {
        // Header dengan background gradient (simulasi dengan rect)
        $this->SetFillColor(102, 126, 234);
        $this->Rect(0, 0, 210, 15, 'F');
        
        // Title putih
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 14);
        $this->SetY(5);
        $this->Cell(0, 8, 'INFORMASI AGUNAN', 0, 1, 'C');
        
        // Box info dengan background abu-abu muda (estimasi tinggi 70mm untuk semua konten)
        $this->SetY(18);
        $this->SetDrawColor(102, 126, 234);
        $this->SetLineWidth(0.5);
        $this->SetFillColor(248, 249, 252);
        $this->RoundRect(10, 18, 190, 70, 2, 'DF');
        
        // Reset line width
        $this->SetLineWidth(0.2);
        
        // Isi info dalam box
        $this->SetY(23);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $labelX = 15;
        $valueX = 65;
        
        // ID Agunan
        $this->SetX($labelX);
        $this->Cell(45, 6, 'ID Agunan', 0, 0);
        $this->SetX($valueX);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(102, 126, 234);
        $this->Cell(0, 6, $this->agunanData['id_agunan'], 0, 1);
        
        // Nama Nasabah
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->SetX($labelX);
        $this->Cell(45, 5, 'Nama Nasabah', 0, 0);
        $this->SetX($valueX);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 5, $this->agunanData['nama_nasabah'] ?? '-', 0, 1);
        
        // No. Rekening
        $this->SetTextColor(100, 100, 100);
        $this->SetX($labelX);
        $this->Cell(45, 5, 'No. Rekening', 0, 0);
        $this->SetX($valueX);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 5, $this->agunanData['no_rek'] ?? '-', 0, 1);
        
        // Kode Kantor
        $this->SetTextColor(100, 100, 100);
        $this->SetX($labelX);
        $this->Cell(45, 5, 'Kode Kantor', 0, 0);
        $this->SetX($valueX);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 5, $this->agunanData['kode_kantor'] ?? '-', 0, 1);
        
        // Tanggal Capture
        $this->SetTextColor(100, 100, 100);
        $this->SetX($labelX);
        $this->Cell(45, 5, 'Tanggal Capture', 0, 0);
        $this->SetX($valueX);
        $this->SetTextColor(0, 0, 0);
        $tgl = !empty($this->agunanData['created_at']) ? date('d M Y H:i', strtotime($this->agunanData['created_at'])) : '-';
        $this->Cell(0, 5, $tgl, 0, 1);
        
        // Jumlah Foto
        $this->SetTextColor(100, 100, 100);
        $this->SetX($labelX);
        $this->Cell(45, 5, 'Jumlah Foto', 0, 0);
        $this->SetX($valueX);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, $this->totalFoto . ' Foto', 0, 1);
        
        // Data dari IBS (jika ada)
        if ($this->verifiedData) {
            $this->Ln(2);
            $this->SetX($labelX);
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(102, 126, 234);
            $this->Cell(0, 4, 'Data dari IBS:', 0, 1);
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(80, 80, 80);
            
            // CIF
            if (!empty($this->verifiedData['cif'])) {
                $this->SetX($labelX + 2);
                $this->Cell(0, 4, 'CIF: ' . $this->verifiedData['cif'], 0, 1);
            }
            
            // Jenis Agunan
            if (!empty($this->verifiedData['jenis_agunan'])) {
                $this->SetX($labelX + 2);
                $this->Cell(0, 4, 'Jenis: ' . $this->verifiedData['jenis_agunan'], 0, 1);
            }
            
            // Detail Kendaraan
            if (!empty($this->verifiedData['kend_no_polisi'])) {
                $this->SetX($labelX + 2);
                $this->Cell(0, 4, 'No. Polisi: ' . $this->verifiedData['kend_no_polisi'], 0, 1);
            }
            
            if (!empty($this->verifiedData['kend_merk'])) {
                $merk = $this->verifiedData['kend_merk'];
                if (!empty($this->verifiedData['kend_tahun'])) {
                    $merk .= ' (' . $this->verifiedData['kend_tahun'] . ')';
                }
                $this->SetX($labelX + 2);
                $this->Cell(0, 4, 'Merk: ' . $merk, 0, 1);
            }
        }
        
        // Set posisi Y setelah header untuk konten
        $this->SetY(92);
    }
    
    // Method untuk rounded rectangle
    function RoundRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }
    
    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . ' - Generated: ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
}

try {
    $totalFoto = count($photos);
    $pdf = new PDF($data, $verified_data, $totalFoto);
    $pdf->SetAutoPageBreak(true, 20);
    $margin = 10;
    
    foreach ($photos as $idx => $ph) {
        $imgRel = $ph['foto_path'];
        $imgAbs = __DIR__ . '/../' . $imgRel;
        if (!file_exists($imgAbs)) {
            continue; // skip yang tidak ada
        }
        $pdf->AddPage();
        
        // Tambahkan label foto
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 6, 'Foto ' . ($idx + 1) . ' dari ' . count($photos), 0, 1, 'C');
        $pdf->Ln(3);
        
        // Hitung scaling agar fit ke halaman
        list($w, $h) = getimagesize($imgAbs);
        $pageW = $pdf->GetPageWidth() - 2*$margin;
        $pageH = $pdf->GetPageHeight() - 60; // Kurangi space untuk header & footer
        $ratio = $w/$h;
        $targetW = $pageW;
        $targetH = $targetW / $ratio;
        if ($targetH > $pageH) {
            $targetH = $pageH;
            $targetW = $targetH * $ratio;
        }
        $x = ($pdf->GetPageWidth() - $targetW) / 2;
        $y = $pdf->GetY();
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
