<?php
// watch_series.php - Simpan riwayat menonton series

session_start();
include 'config.php';

// Cek login
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    http_response_code(401);
    die("Unauthorized");
}

$userId = $_SESSION['user']['id'];

// Ambil id dari query string (bukan series_id)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die("Invalid series ID");
}
$seriesId = (int)$_GET['id'];

// Cek apakah series ada
$stmt = $conn->prepare("SELECT id FROM series WHERE id = ?");
$stmt->bind_param("i", $seriesId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die("Series not found");
}
$stmt->close();

// Cek riwayat: update atau insert
$stmt = $conn->prepare("SELECT id FROM series_history WHERE user_id = ? AND series_id = ?");
$stmt->bind_param("ii", $userId, $seriesId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update waktu tonton
    $stmt = $conn->prepare("UPDATE series_history SET watched_at = CURRENT_TIMESTAMP WHERE user_id = ? AND series_id = ?");
    $stmt->bind_param("ii", $userId, $seriesId);
} else {
    // Insert baru
    $stmt = $conn->prepare("INSERT INTO series_history (user_id, series_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $seriesId);
}

if ($stmt->execute()) {
    // Opsional: jangan redirect, biarkan halaman tetap
    // Bisa kosongkan output, karena dipanggil via fetch()
} else {
    error_log("Gagal simpan riwayat series: " . $stmt->error);
}

$stmt->close();
$conn->close();
// Tidak perlu redirect — ini async
?>