<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Cek apakah email ada di database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = "Email tidak ditemukan di sistem kami.";
    } else {
        $token = bin2hex(random_bytes(50));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Simpan token ke database
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Buat link reset password
            $resetLink = "http://localhost/streamflix/reset-password.php?token=$token";
            
            // Kirim email (pastikan server Anda bisa mengirim email)
            $subject = "Permintaan Reset Password";
            $message = "Kami menerima permintaan untuk mereset password akun Anda.\n\n";
            $message .= "Silakan klik tautan berikut untuk mereset password:\n";
            $message .= $resetLink . "\n\n";
            $message .= "Tautan ini akan kedaluwarsa dalam 1 jam.";

            $headers = "From: no-reply@streamflix.com";

            if (mail($email, $subject, $message, $headers)) {
                $_SESSION['success'] = "Link reset password telah dikirim ke email Anda.";
                header("Location: login.php");
                exit;
            } else {
                $error = "Gagal mengirim email. Silakan coba lagi nanti.";
            }
        } else {
            $error = "Gagal menyimpan token reset password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - StreamFlix</title>

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
        <h3 class="text-2xl font-bold text-center text-[#FFD700] mb-6">Lupa Password</h3>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Masukkan email Anda" required
                   class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-[#FFD700] mb-4">

            <button type="submit"
                    class="w-full bg-[#FFD700] hover:bg-yellow-500 text-black font-bold py-3 rounded-lg transition-all duration-300 transform hover:scale-105">
                Kirim Link Reset
            </button>
        </form>

        <p class="mt-4 text-sm text-center text-gray-400">
            Ingat password?
            <a href="login.php" class="text-[#FFD700] hover:text-yellow-400 font-medium">Login kembali</a>
        </p>
    </div>

</body>
</html>