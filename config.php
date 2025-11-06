<?php
$host = "localhost";
$user = "root";
$pass = ""; // ganti jika password MySQL kamu tidak kosong
$db_port = 3308; // pakai port 3308, sesuai contoh kamu
$db = "agunan_capture";

$conn = new mysqli($host, $user, $pass, $db, $db_port);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

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
    die("Koneksi gagal: " . $conn_dbibs->connect_error);
}
?>