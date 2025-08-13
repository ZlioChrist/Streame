<?php
include 'config.php';

// Cari semua langganan yang akan diperbarui
$now = date("Y-m-d H:i:s");

$stmt = $conn->query("
    SELECT o.* FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.next_billing_date <= '$now' AND o.status = 'paid'
");

while ($order = $stmt->fetch_assoc()) {
    // Ambil info paket
    $sub_stmt = $conn->prepare("SELECT * FROM subscriptions WHERE id = ?");
    $sub_stmt->bind_param("i", $order['subscription_id']);
    $sub_stmt->execute();
    $package = $sub_stmt->get_result()->fetch_assoc();

    if (!$package) continue;

    // Buat transaksi baru
    $new_transaction_id = "renew_".$order['transaction_id']."_".date("Ymd");
    $amount = $package['price'];
    $start_date = $order['end_date'];
    $end_date = date("Y-m-d H:i:s", strtotime("+$package[duration_days] days", strtotime($start_date)));
    $next_billing_date = date("Y-m-d H:i:s", strtotime("+1 month", strtotime($end_date)));

    // Simpan order baru
    $insert_stmt = $conn->prepare("INSERT INTO orders 
        (user_id, subscription_id, transaction_id, amount, start_date, end_date, next_billing_date, payment_method, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Credit Card', 'paid')");
    $insert_stmt->bind_param("iissdds", $order['user_id'], $order['subscription_id'], $new_transaction_id, $amount, $start_date, $end_date, $next_billing_date);
    $insert_stmt->execute();

    // Update user expiration
    $update_user = $conn->prepare("UPDATE users SET expires_at = ? WHERE id = ?");
    $update_user->bind_param("si", $end_date, $order['user_id']);
    $update_user->execute();

    // Update order lama jadi selesai
    $mark_complete = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
    $mark_complete->bind_param("i", $order['id']);
    $mark_complete->execute();
}
echo "Langganan bulanan telah diproses.";
?>