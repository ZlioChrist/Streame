<?php
// delete_history.php

session_start();
include 'config.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    die("Unauthorized");
}

$userId = $_SESSION['user']['id'];
$contentId = (int)($_GET['content_id'] ?? 0);
$contentType = $_GET['content_type'] ?? '';

if ($contentId <= 0 || !in_array($contentType, ['movie', 'series'])) {
    die("Invalid request");
}

if ($contentType === 'movie') {
    $stmt = $conn->prepare("DELETE FROM watch_history WHERE user_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $userId, $contentId);
} else {
    $stmt = $conn->prepare("DELETE FROM series_history WHERE user_id = ? AND series_id = ?");
    $stmt->bind_param("ii", $userId, $contentId);
}

if ($stmt->execute()) {
    header("Location: " . $_SERVER['HTTP_REFERER'] ?? 'dashboard_vip.php');
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>