<?php
// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Deteksi environment (localhost vs production)
$is_localhost = (
    $_SERVER['SERVER_NAME'] === 'localhost' || 
    $_SERVER['SERVER_ADDR'] === '127.0.0.1' ||
    $_SERVER['SERVER_ADDR'] === '::1' ||
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '191.69.1.24') !== false
);

if ($is_localhost) {
    // Konfigurasi LOCALHOST
    $host = "localhost";
    $user = "root";
    $pass = ""; 
    $db_port = 3308;
    $db = "agunan_capture";
} else {
    // Konfigurasi PRODUCTION (appdev.bprntb.co.id)
    $host = "localhost"; // biasanya localhost di hosting
    $user = "root"; // sesuaikan dengan user DB production
    $pass = ""; // sesuaikan dengan password DB production
    $db_port = 3306; // port default MySQL
    $db = "agunan_capture";
}

$conn = new mysqli($host, $user, $pass, $db, $db_port);

if ($conn->connect_error) {
    error_log("Koneksi DB gagal: " . $conn->connect_error);
    die("Database connection failed: " . $conn->connect_error); // Tampilkan error untuk debugging
}

// Set charset
$conn->set_charset("utf8mb4");

// ============================================== //
// KONEKSI DATABASE PATROLI SECURITY (TERPISAH)
// ============================================== //

$db_patroli = "patroli_security";
$conn_patroli = new mysqli($host, $user, $pass, $db_patroli, $db_port);

if ($conn_patroli->connect_error) {
    error_log("Koneksi DB Patroli gagal: " . $conn_patroli->connect_error);
    // Tidak die, biar tidak ganggu aplikasi agunan/voucher
}

$conn_patroli->set_charset("utf8mb4");

// ============================================== //

// Konfigurasi database ke IBS transaksi
$servername_dbibs = "191.69.1.1";
$username_dbibs = "edpit";
$password_dbibs = "M4t4r4m!@#";
$dbname_dbibs = "bprntb";
$dbport_dbibs = "3306";

// Membuat koneksi ke IBS transaksi
$conn_dbibs = new mysqli($servername_dbibs, $username_dbibs, $password_dbibs, $dbname_dbibs, $dbport_dbibs);
if ($conn_dbibs->connect_error) {
    error_log("Koneksi IBS gagal: " . $conn_dbibs->connect_error);
    // Jangan die di sini, biar API bisa handle error dengan JSON response
}
?>