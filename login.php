<?php
// login.php - Halaman Login

session_start();
include 'config.php';

// Inisialisasi variabel
$error = '';
$success = '';
$email = '';

// Cek apakah user sudah login
if (isset($_SESSION['user'])) {
    // Arahkan ke dashboard sesuai status
    if ($_SESSION['user']['is_vip'] ?? false) {
        header("Location: dashboard_vip.php");
    } else {
        header("Location: pricing.php");
    }
    exit();
}

// Proses login saat form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($email)) {
        $error = "Email harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (empty($password)) {
        $error = "Password harus diisi.";
    } else {
        // Cari user berdasarkan email
        $stmt = $conn->prepare("SELECT id, name, email, password, is_vip, expires_at, profile_image FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "Email tidak ditemukan.";
        } else {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (!password_verify($password, $user['password'])) {
                $error = "Password salah.";
            } else {
                // ✅ Login berhasil → simpan session
                $_SESSION['user'] = [
                    'id'            => $user['id'],
                    'name'          => $user['name'],
                    'email'         => $user['email'],
                    'is_vip'        => $user['is_vip'],
                    'expires_at'    => $user['expires_at'],
                    'profile_image' => $user['profile_image'] ?? null
                ];

                // Cek status VIP dan masa aktif
                $is_vip_active = $user['is_vip'] && new DateTime($user['expires_at']) > new DateTime();

                // Redirect sesuai status
                if ($is_vip_active) {
                    header("Location: dashboard_vip.php");
                } else {
                    header("Location: pricing.php");
                }
                exit();
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Masuk - Streamé</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <style>
        .input-focus {
            @apply focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500;
        }
        .btn-hover {
            @apply hover:bg-yellow-600 hover:shadow-lg hover:shadow-yellow-500/30;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 text-white font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-gray-800 glass rounded-2xl shadow-2xl p-8 border border-yellow-400/10">
        <div class="text-center mb-6">
            <i class="bi bi-lock text-4xl text-yellow-400 mb-3"></i>
            <h3 class="text-2xl font-bold text-yellow-400">Masuk ke Akun Anda</h3>
            <p class="text-gray-400 text-sm mt-1">Silakan masukkan data Anda</p>
        </div>

        <!-- Alert Error -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-900/60 border-l-4 border-red-500 text-red-200 p-3 mb-6 rounded text-sm flex items-center gap-2">
                <i class="bi bi-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form method="post" class="space-y-5">
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                <div class="relative">
                    <i class="bi bi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="email@example.com"
                        class="w-full pl-10 pr-4 py-3 bg-gray-700 border border-gray-600 rounded-lg input-focus outline-none transition"
                        required
                    >
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                <div class="relative">
                    <i class="bi bi-lock-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-3 bg-gray-700 border border-gray-600 rounded-lg input-focus outline-none transition"
                        required
                    >
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-yellow-500 text-black font-bold py-3 rounded-lg transition btn-hover shadow-md hover:shadow-yellow-500/40 transform hover:scale-105 duration-300 flex items-center justify-center gap-2">
                <i class="bi bi-box-arrow-in-right"></i>
                Masuk
            </button>
        </form>

        <!-- Register Link -->
        <p class="mt-6 text-center text-sm text-gray-400">
            Belum punya akun?
            <a href="register.php" class="text-yellow-400 hover:text-yellow-300 font-medium transition">
                Daftar di sini
            </a>
        </p>
    </div>

    <!-- Bootstrap Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.js"></script>
</body>
</html>