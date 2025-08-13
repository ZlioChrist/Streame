<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Permintaan tidak valid.");
}

$package_id = intval($_POST['package_id']);
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$payment_method = $_POST['payment_method'];

// Ambil detail paket dari database
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE id = ? AND is_active = TRUE");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Paket tidak ditemukan.");
}

$package = $result->fetch_assoc();

// Simpan session dummy pembayaran sukses
$_SESSION['subscription'] = [
    'package_name' => $package['name'],
    'price' => $package['price'],
    'duration_days' => $package['duration_days'],
    'expires_at' => date('Y-m-d H:i:s', strtotime("+{$package['duration_days']} days")),
    'status' => 'pending'
];

// Handle upload bukti transfer jika metode == bank_transfer
if ($payment_method === 'bank_transfer') {
    if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === 0) {
        $uploadDir = "uploads/";
        $fileName = uniqid('payment_') . '_' . basename($_FILES['proof_of_payment']['name']);
        $uploadFilePath = $uploadDir . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($_FILES['proof_of_payment']['tmp_name'], $uploadFilePath)) {
            $_SESSION['proof_of_payment'] = $uploadFilePath;
        } else {
            die("Gagal mengunggah bukti pembayaran.");
        }
    } else {
        die("Silakan unggah bukti pembayaran.");
    }
}

// Integrasi Midtrans untuk QRIS
if ($payment_method === 'qris') {
    require_once 'midtrans_config.php';

    // Data transaksi
    $params = array(
        'transaction_details' => array(
            'order_id' => rand(),
            'gross_amount' => $package['price'] * 1000, // Harga dalam satuan sen
        ),
        'enabled_payments' => ['qris'], // Metode pembayaran QRIS
        'customer_details' => array(
            'first_name' => $name,
            'email' => $email,
        )
    );

    try {
        // Generate Snap Token
        $snapToken = \Midtrans\Snap::getSnapToken($params);
        $_SESSION['snap_token'] = $snapToken;
        header("Location: payment_gateway.php");
        exit;
    } catch (\Exception $e) {
        die("Error saat generate Snap Token: " . $e->getMessage());
    }
}


// Update session user menjadi VIP
$_SESSION['user']['is_vip'] = 1;
$_SESSION['user']['expires_at'] = date('Y-m-d H:i:s', strtotime("+{$package['duration_days']} days"));


// Redirect ke dashboard_vip.php
header("Location: dashboard_vip.php");
exit;
?>