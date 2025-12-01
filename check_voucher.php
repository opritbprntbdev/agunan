<?php
require_once 'config.php';

$no_bukti = '018-11-25-0349';

// Cek struktur tabel dulu
echo "=== STRUKTUR TABEL voucher_data ===\n";
$desc = $conn->query("DESCRIBE voucher_data");
while ($col = $desc->fetch_assoc()) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n=== DATA VOUCHER $no_bukti ===\n";
$stmt = $conn->prepare("SELECT * FROM voucher_data WHERE no_bukti = ?");
$stmt->bind_param('s', $no_bukti);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "Data ditemukan:\n";
    print_r($row);
    
    if (!empty($row['pdf_path'])) {
        $fullPath = __DIR__ . '/' . $row['pdf_path'];
        echo "\n\nFull PDF path: $fullPath\n";
        echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
    } else {
        echo "\n\nPDF path kosong - voucher belum di-finalize\n";
    }
} else {
    echo "Data tidak ditemukan di database lokal\n";
    echo "Kemungkinan: Baru upload foto, belum klik SIMPAN & CETAK PDF\n";
}
