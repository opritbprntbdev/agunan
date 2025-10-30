<?php
$host = "localhost";
$user = "root";
$pass = ""; // ganti jika password MySQL kamu tidak kosong
$db_port = 3308; // pakai port 3308, sesuai contoh kamu
$db   = "agunan_capture";

$conn = new mysqli($host, $user, $pass, $db, $db_port);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>