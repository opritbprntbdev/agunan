<?php
/**
 * Verify Agunan - Backend API
 * Query data agunan dari Core Banking IBS
 * Return JSON dengan data agunan (tanah/kendaraan)
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

// Ambil agunan_id dari request
$agunan_id = isset($_POST['agunan_id']) ? trim($_POST['agunan_id']) : '';

if (empty($agunan_id)) {
    echo json_encode(['success' => false, 'message' => 'Agunan ID tidak boleh kosong']);
    exit;
}

// Query ke database IBS
try {
    // Query lengkap dengan JOIN ke nasabah via kre_agunan_relasi
    $stmt = $conn_dbibs->prepare('
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
        LEFT JOIN kre_agunan_relasi AS r ON c.AGUNAN_ID = r.agunan_id
        LEFT JOIN kredit AS k ON r.no_rekening = k.no_rekening
        LEFT JOIN nasabah AS n ON k.nasabah_id = n.nasabah_id
        WHERE c.AGUNAN_ID = ?
        LIMIT 1
    ');

    if (!$stmt) {
        throw new Exception('Query preparation failed: ' . $conn_dbibs->error);
    }

    $stmt->bind_param('s', $agunan_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Data ditemukan
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
        $jenis_agunan_display = '';
        if (in_array($kode_jenis, ['5', '6'])) {
            // Tanah
            $no_sertifikat = $row['TANAH_NO_SHM'] ?: $row['TANAH_NO_SHGB'];
            $jenis_agunan_display = 'Tanah - ' . ($no_sertifikat ?: 'Tidak ada nomor sertifikat');
        } else {
            // Kendaraan atau lainnya
            $jenis_agunan_display = 'Kendaraan - ' . ($row['KEND_NO_POLISI'] ?: 'Tidak ada nomor polisi');
        }

        // Prepare response data
        $data = [
            'agunan_id' => $row['AGUNAN_ID'],
            'no_alternatif_agunan' => $row['AGUNAN_ID'], // ID agunan juga sebagai no alternatif
            'no_rekening_kredit' => $row['NO_REKENING_KREDIT'],
            'cif' => $row['CIF'], // CIF / Nasabah ID
            'nama_nasabah' => $row['NAMA_NASABAH'] ?: ($row['TANAH_NAMA_PEMILIK'] ?: '-'),
            'alamat' => $row['ALAMAT_NASABAH'] ?: ($tanah_lokasi ?: '-'),
            'kode_jenis_agunan' => $kode_jenis,
            'jenis_agunan' => $kode_jenis == '5' ? 'Tanah SHM' : ($kode_jenis == '6' ? 'Tanah SHGB' : 'Kendaraan'),
            'deskripsi_ringkas' => $row['DESKRIPSI_RINGKAS'],
            'jenis_agunan_display' => $jenis_agunan_display,
            'kode_kantor' => $row['KODE_KANTOR'],
            'kode_status' => $row['STATUS_AGUNAN'],
            'premier' => $row['PREMIER'] == 1 ? 'Ya' : 'Tidak',

            // Data Tanah
            'tanah_no_shm' => $row['TANAH_NO_SHM'],
            'tanah_no_shgb' => $row['TANAH_NO_SHGB'],
            'tanah_tgl_sertifikat' => $row['TANAH_TGL_SERTIFIKAT'],
            'tanah_luas' => $row['TANAH_LUAS_TANAH'] ? number_format($row['TANAH_LUAS_TANAH'], 2, '.', '') : null,
            'tanah_nama_pemilik' => $row['TANAH_NAMA_PEMILIK'],
            'tanah_lokasi' => $tanah_lokasi,

            // Data Kendaraan
            'kend_jenis' => $row['KEND_JENIS_KENDARAAN'],
            'kend_merk' => $row['KEND_MERK'],
            'kend_tahun' => $row['KEND_TAHUN_PEMBUATAN'],
            'kend_no_polisi' => $row['KEND_NO_POLISI'],

            // Metadata
            'verified_at' => date('Y-m-d H:i:s'),
            'verified_by' => $_SESSION['username']
        ];
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Data agunan ditemukan di sistem IBS',
            'data' => $data
        ]);

    } else {
        // Data tidak ditemukan
        $stmt->close();
        echo json_encode([
            'success' => false,
            'message' => 'Agunan ID "' . htmlspecialchars($agunan_id) . '" tidak ditemukan di sistem IBS',
            'not_found' => true
        ]);
    }

} catch (Exception $e) {
    // Error koneksi atau query
    echo json_encode([
        'success' => false,
        'message' => 'Error koneksi ke IBS: ' . $e->getMessage(),
        'error_type' => 'connection_error'
    ]);
}
?>