<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$subscription = null;

if ($user['subscription_id']) {
    $sub = $conn->query("SELECT * FROM subscriptions WHERE id = {$user['subscription_id']}");
    if ($sub && $row = $sub->fetch_assoc()) {
        $subscription = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - StreamFlix</title>
</head>
<body>
    <h2>Halo, <?= htmlspecialchars($user['name']) ?></h2>

    <?php if ($subscription): ?>
        <p>Langganan Anda: <?= htmlspecialchars($subscription['name']) ?></p>
    <?php else: ?>
        <p>Anda belum berlangganan</p>
        <a href="pricing.php">Pilih Paket</a>
    <?php endif; ?>

    <br><br>
    <a href="logout.php">Logout</a>
</body>
</html>