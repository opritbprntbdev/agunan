<?php
session_start();
require 'config.php';

// Cegah akses langsung tanpa POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

// Cari user dengan username dan password plain text
$stmt = $conn->prepare("SELECT * FROM user WHERE username=? AND password=?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $_SESSION['login'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama_kc'] = $user['nama_kc'];
    $_SESSION['username'] = $user['username'];
    header("Location: home.php");
} else {
    header("Location: index.php?error=Login gagal!");
}
exit;
?>