<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_GET['token'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($token)) {
        $error = "Token tidak ditemukan.";
    } elseif (empty($password)) {
        $error = "Password baru harus diisi.";
    } else {
        // Cek apakah token valid dan belum kadaluarsa
        $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            // Update password dan hapus token
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET 
                password = ?, 
                reset_token = NULL, 
                reset_expires = NULL 
                WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user['id']);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Password berhasil diubah. Silakan login dengan akun Anda.";
                header("Location: login.php");
                exit;
            } else {
                $error = "Gagal mengubah password. Silakan coba lagi nanti.";
            }
        } else {
            $error = "Token tidak valid atau telah kedaluwarsa.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ubah Password - StreamFlix</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com "></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md p-8 bg-gray-900 rounded-xl shadow-lg">
    <h3 class="text-2xl font-bold text-center text-[#FFD700] mb-6">Ubah Password</h3>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="password" name="password" placeholder="Masukkan password baru" required
               class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-[#FFD700] mb-4">

        <button type="submit"
                class="w-full bg-[#FFD700] hover:bg-yellow-500 text-black font-bold py-3 rounded-lg transition-all duration-300 transform hover:scale-105">
            Simpan Password
        </button>
    </form>
</div>

</body>
</html>