<?php
/**
 * Test Voucher Upload - Diagnostic Script
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_upload.log');

header('Content-Type: application/json; charset=utf-8');

session_start();

// Test 1: Session check
$test_results = [];
$test_results['session_exists'] = isset($_SESSION['login']);
$test_results['user_id'] = $_SESSION['user_id'] ?? 'NOT SET';
$test_results['username'] = $_SESSION['username'] ?? 'NOT SET';
$test_results['kode_kantor'] = $_SESSION['kode_kantor'] ?? 'NOT SET';

// Test 2: Database connection
try {
    require_once __DIR__ . '/../config.php';
    $test_results['db_connection'] = $conn ? 'Connected' : 'Failed';
    $test_results['db_error'] = $conn->connect_error ?? 'None';
} catch (Exception $e) {
    $test_results['db_connection'] = 'Exception: ' . $e->getMessage();
}

// Test 3: Check tables exist
if ($conn && !$conn->connect_error) {
    $check_voucher_data = $conn->query("SHOW TABLES LIKE 'voucher_data'");
    $check_voucher_foto = $conn->query("SHOW TABLES LIKE 'voucher_foto'");
    
    $test_results['table_voucher_data'] = $check_voucher_data && $check_voucher_data->num_rows > 0 ? 'EXISTS' : 'NOT FOUND';
    $test_results['table_voucher_foto'] = $check_voucher_foto && $check_voucher_foto->num_rows > 0 ? 'EXISTS' : 'NOT FOUND';
    
    // Test 4: Test INSERT capability
    if ($test_results['table_voucher_data'] === 'EXISTS') {
        $test_insert = $conn->query("
            INSERT INTO voucher_data (
                trans_id, user_id, photo_taken_by, kode_kantor, nama_kc, 
                pdf_filename, pdf_path, total_foto
            ) VALUES (
                'TEST_" . time() . "', 1, 'test_user', '000', 'Test KC',
                'test.pdf', 'pdf/test.pdf', 0
            )
        ");
        
        if ($test_insert) {
            $insert_id = $conn->insert_id;
            $test_results['insert_test'] = 'SUCCESS - ID: ' . $insert_id;
            
            // Delete test record
            $conn->query("DELETE FROM voucher_data WHERE id = $insert_id");
        } else {
            $test_results['insert_test'] = 'FAILED: ' . $conn->error;
        }
    }
    
    // Test 5: Check IBS connection
    if ($conn_dbibs && !$conn_dbibs->connect_error) {
        $test_results['ibs_connection'] = 'Connected';
        
        // Test query to transaksi_master
        $test_ibs_query = $conn_dbibs->query("SELECT COUNT(*) as total FROM transaksi_master LIMIT 1");
        if ($test_ibs_query) {
            $row = $test_ibs_query->fetch_assoc();
            $test_results['ibs_transaksi_count'] = $row['total'];
        } else {
            $test_results['ibs_query_error'] = $conn_dbibs->error;
        }
    } else {
        $test_results['ibs_connection'] = 'Failed: ' . ($conn_dbibs->connect_error ?? 'Unknown');
    }
}

// Test 6: POST data received
$test_results['post_data'] = $_POST;
$test_results['files_data'] = isset($_FILES['image']) ? [
    'name' => $_FILES['image']['name'] ?? '',
    'size' => $_FILES['image']['size'] ?? 0,
    'error' => $_FILES['image']['error'] ?? -1,
] : 'No file uploaded';

// Test 7: Check upload directory
$year = date('Y');
$month = date('m');
$upload_dir = __DIR__ . '/../uploads/voucher/' . $year . '/' . $month;
$test_results['upload_dir'] = $upload_dir;
$test_results['upload_dir_exists'] = is_dir($upload_dir) ? 'YES' : 'NO';
$test_results['upload_dir_writable'] = is_writable(dirname($upload_dir, 3)) ? 'YES' : 'NO';

// Output results
echo json_encode($test_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
