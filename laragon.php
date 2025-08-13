<?php
// config.php - Konfigurasi Proyek StreamFlix untuk Laragon

// 🔐 Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🌐 Definisikan konstanta situs (hanya sekali)
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Streamé');
}
if (!defined('SITE_COPY')) {
    define('SITE_COPY', 'Streamé | All rights reserved. ');
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/'); // Sesuaikan jika di subfolder: '/streamflix/'
}
if (!defined('LOGO')) {
    define('LOGO', 'assets/images/logo.png');
}
if (!defined('COVER')) {
    define('COVER', 'assets/images/cover.jpg');
}

// 🛢️ Konfigurasi Database (Default Laragon)
$host = 'localhost';     // Laragon: MySQL berjalan di localhost
$username = 'root';      // Default user Laragon
$password = '';          // Default password kosong
$database = 'streamflix'; // Pastikan database ini sudah dibuat di phpMyAdmin

// 🔗 Buat koneksi menggunakan MySQLi
$conn = new mysqli($host, $username, $password, $database);

// ❌ Cek koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . htmlspecialchars($conn->connect_error));
}

// ✅ Set charset ke utf8mb4 untuk mendukung emoji dan karakter internasional
$conn->set_charset('utf8mb4');

// Optional: Tambahkan header security dasar
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
?>