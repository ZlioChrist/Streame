<?php
$host = 'localhost';
$user = 'root';         // default untuk XAMPP
$pass = '';             // default untuk XAMPP
$db = 'streamflix';

// config.php

// Cek apakah sesi belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan constant belum didefinisikan
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Streamé');
}
if (!defined('SITE_COPY')) {
    define('SITE_COPY', 'Streamé | All rights reserved. ');
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}
if (!defined('LOGO')) {
    define('LOGO', 'assets/images/logo.png');
}
if (!defined('COVER')) {
    define('COVER', 'assets/images/cover.jpg');
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "streamflix");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>