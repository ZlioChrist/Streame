<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if (!$user['is_vip'] || new DateTime($user['expires_at']) < new DateTime()) {
    header("Location: pricing.php");
    exit;
}

?>