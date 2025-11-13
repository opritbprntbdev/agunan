<?php
/**
 * Get List Agunan by No Rekening
 * Query semua agunan yang terkait dengan no_rekening kredit
 */

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

$no_rek = isset($_POST['no_rekening']) ? trim($_POST['no_rekening']) : '';
$show_captured = isset($_POST['show_captured']) ? (bool)$_POST['show_captured'] : false;

if (empty($no_rek)) {
    echo json_encode(['success' => false, 'message' => 'No Rekening tidak boleh kosong']);
    exit;
}

try {
    // Sanitize input
    $no_rek_escaped = $conn_dbibs->real_escape_string($no_rek);
    
    // Query semua agunan untuk no_rekening ini
    $query = "
        SELECT 
            c.AGUNAN_ID,
            c.KODE_JENIS_AGUNAN,
            c.DESKRIPSI_RINGKAS,
            c.TANAH_NO_SHM,
            c.TANAH_NO_SHGB,
            c.TANAH_TGL_SERTIFIKAT,
            c.TANAH_LUAS_TANAH,
            c.TANAH_NAMA_PEMILIK,
            c.TANAH_DESA,
            c.TANAH_KELURAHAN,
            c.TANAH_KECAMATAN,
            c.TANAH_KABUPATEN,
            c.KEND_JENIS_KENDARAAN,
            c.KEND_MERK,
            c.KEND_TAHUN_PEMBUATAN,
            c.KEND_NO_POLISI,
            r.no_rekening AS NO_REKENING_KREDIT,
            r.kode_kantor AS KODE_KANTOR,
            r.primer AS PREMIER,
            r.status AS STATUS_AGUNAN,
            k.nasabah_id AS CIF,
            n.nama_nasabah AS NAMA_NASABAH,
            n.alamat AS ALAMAT_NASABAH
        FROM kre_agunan AS c
        INNER JOIN kre_agunan_relasi AS r ON c.AGUNAN_ID = r.agunan_id
        LEFT JOIN kredit AS k ON r.no_rekening = k.no_rekening
        LEFT JOIN nasabah AS n ON k.nasabah_id = n.nasabah_id
        WHERE r.no_rekening = '$no_rek_escaped'
        ORDER BY c.AGUNAN_ID ASC
    ";
    
    $result = $conn_dbibs->query($query);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn_dbibs->error);
    }
    
    $agunan_list = [];
    $nasabah_info = null;
    
    while ($row = $result->fetch_assoc()) {
        // Simpan info nasabah (sama untuk semua agunan)
        if (!$nasabah_info) {
            $nasabah_info = [
                'cif' => $row['CIF'],
                'nama' => $row['NAMA_NASABAH'],
                'alamat' => $row['ALAMAT_NASABAH'],
                'no_rek' => $row['NO_REKENING_KREDIT']
            ];
        }
        
        // Format data agunan
        $kode_jenis = $row['KODE_JENIS_AGUNAN'];
        
        // Gabungkan lokasi untuk tanah
        $lokasi_parts = array_filter([
            $row['TANAH_DESA'],
            $row['TANAH_KELURAHAN'],
            $row['TANAH_KECAMATAN'],
            $row['TANAH_KABUPATEN']
        ]);
        $tanah_lokasi = !empty($lokasi_parts) ? implode(', ', $lokasi_parts) : null;
        
        // Tentukan jenis agunan display
        $jenis_display = '';
        if (in_array($kode_jenis, ['5', '6'])) {
            $no_sertifikat = $row['TANAH_NO_SHM'] ?: $row['TANAH_NO_SHGB'];
            $jenis_display = '🏠 Tanah - ' . ($no_sertifikat ?: 'Tidak ada nomor sertifikat');
        } else {
            $jenis_display = '🚗 Kendaraan - ' . ($row['KEND_NO_POLISI'] ?: 'Tidak ada nomor polisi');
        }
        
        $agunan_list[] = [
            'agunan_id' => $row['AGUNAN_ID'],
            'kode_jenis' => $kode_jenis,
            'jenis_display' => $jenis_display,
            'deskripsi' => $row['DESKRIPSI_RINGKAS'],
            'kode_kantor' => $row['KODE_KANTOR'],
            'kode_status' => $row['STATUS_AGUNAN'],
            'nilai_agunan' => null, // Bisa ditambahkan jika ada field nilai di tabel
            
            // Tanah
            'tanah_no_shm' => $row['TANAH_NO_SHM'],
            'tanah_no_shgb' => $row['TANAH_NO_SHGB'],
            'tanah_tgl_sertifikat' => $row['TANAH_TGL_SERTIFIKAT'],
            'tanah_luas' => $row['TANAH_LUAS_TANAH'],
            'tanah_nama_pemilik' => $row['TANAH_NAMA_PEMILIK'],
            'tanah_lokasi' => $tanah_lokasi,
            
            // Kendaraan
            'kend_jenis' => $row['KEND_JENIS_KENDARAAN'],
            'kend_merk' => $row['KEND_MERK'],
            'kend_tahun' => $row['KEND_TAHUN_PEMBUATAN'],
            'kend_no_polisi' => $row['KEND_NO_POLISI'],
        ];
    }
    
    $result->free();
    
    if (empty($agunan_list)) {
        echo json_encode([
            'success' => false,
            'message' => 'No Rekening "' . htmlspecialchars($no_rek) . '" tidak memiliki agunan atau tidak ditemukan',
            'not_found' => true
        ]);
        exit;
    }
    
    // Check status capture untuk setiap agunan dari database lokal
    $agunan_ids = array_column($agunan_list, 'agunan_id');
    $agunan_ids_escaped = array_map(function($id) use ($conn) {
        return "'" . $conn->real_escape_string($id) . "'";
    }, $agunan_ids);
    
    $captured_query = "
        SELECT 
            id_agunan,
            COUNT(*) as jumlah_foto,
            DATE_FORMAT(created_at, '%d %b %Y') as tanggal_capture,
            kode_kantor
        FROM agunan_data
        WHERE id_agunan IN (" . implode(',', $agunan_ids_escaped) . ")
        GROUP BY id_agunan, kode_kantor
    ";
    
    $captured_result = $conn->query($captured_query);
    $captured_map = [];
    
    if ($captured_result) {
        while ($row = $captured_result->fetch_assoc()) {
            $captured_map[$row['id_agunan']] = [
                'jumlah_foto' => $row['jumlah_foto'],
                'tanggal' => $row['tanggal_capture'],
                'kode_kantor' => $row['kode_kantor']
            ];
        }
        $captured_result->free();
    }
    
    // Tambahkan status capture ke setiap agunan
    foreach ($agunan_list as &$agunan) {
        if (isset($captured_map[$agunan['agunan_id']])) {
            $agunan['sudah_capture'] = true;
            $agunan['capture_info'] = $captured_map[$agunan['agunan_id']];
        } else {
            $agunan['sudah_capture'] = false;
            $agunan['capture_info'] = null;
        }
    }
    unset($agunan);
    
    // Filter agunan berdasarkan show_captured
    if (!$show_captured) {
        // Hanya tampilkan yang belum di-capture
        $agunan_list = array_filter($agunan_list, function($agunan) {
            return !$agunan['sudah_capture'];
        });
        $agunan_list = array_values($agunan_list); // Re-index array
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Ditemukan ' . count($agunan_list) . ' agunan',
        'nasabah' => $nasabah_info,
        'agunan_list' => $agunan_list,
        'total' => count($agunan_list),
        'show_captured' => $show_captured
    ]);
    
} catch (Exception $e) {
    if (isset($result) && $result instanceof mysqli_result) {
        $result->free();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error koneksi ke IBS: ' . $e->getMessage(),
        'error_type' => 'connection_error'
    ]);
}
?>
