<?php
// update_note.php

session_start();
include 'config.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    die("Unauthorized");
}

$userId = $_SESSION['user']['id'];
$contentId = (int)($_POST['content_id'] ?? 0);
$contentType = $_POST['content_type'] ?? '';
$note = trim($_POST['note'] ?? '');

if ($contentId <= 0 || !in_array($contentType, ['movie', 'series'])) {
    die("Invalid request");
}

if ($contentType === 'movie') {
    $check = $conn->prepare("SELECT id FROM watch_history WHERE user_id = ? AND movie_id = ?");
    $check->bind_param("ii", $userId, $contentId);
    $check->execute();

    if ($check->get_result()->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO watch_history (user_id, movie_id, note) VALUES (?, ?, ?)");
        $insert->bind_param("iis", $userId, $contentId, $note);
        $insert->execute();
    } else {
        $update = $conn->prepare("UPDATE watch_history SET note = ? WHERE user_id = ? AND movie_id = ?");
        $update->bind_param("sii", $note, $userId, $contentId);
        $update->execute();
    }
} else {
    $check = $conn->prepare("SELECT id FROM series_history WHERE user_id = ? AND series_id = ?");
    $check->bind_param("ii", $userId, $contentId);
    $check->execute();

    if ($check->get_result()->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO series_history (user_id, series_id, note) VALUES (?, ?, ?)");
        $insert->bind_param("iis", $userId, $contentId, $note);
        $insert->execute();
    } else {
        $update = $conn->prepare("UPDATE series_history SET note = ? WHERE user_id = ? AND series_id = ?");
        $update->bind_param("sii", $note, $userId, $contentId);
        $update->execute();
    }
}

header("Location: " . $_SERVER['HTTP_REFERER'] ?? 'dashboard_vip.php');
exit;
?>