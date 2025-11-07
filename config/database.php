<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gapkomp');

// Koneksi Database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Fungsi untuk mencegah SQL Injection
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk mendapatkan bobot gap berdasarkan selisih
function getBobotGap($gap) {
    $bobot_gap = [
        0 => 5,
        1 => 4.5,
        -1 => 4,
        2 => 3.5,
        -2 => 3,
        3 => 2.5,
        -3 => 2,
        4 => 1.5,
        -4 => 1
    ];
    
    return isset($bobot_gap[$gap]) ? $bobot_gap[$gap] : 1;
}

// Fungsi untuk format angka
function formatAngka($angka, $desimal = 2) {
    return number_format($angka, $desimal, ',', '.');
}

// Fungsi untuk redirect
function redirect($url) {
    echo "<script>window.location.href='$url';</script>";
    exit();
}

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>