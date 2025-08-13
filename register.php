<?php
session_start();
include 'config.php'; // Pastikan $conn sudah terhubung

$error = "";
$success = "";

// Direktori upload
$upload_dir = "uploads/profile_images/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
$max_size = 2 * 1024 * 1024; // 2MB

// Ambil daftar paket dari database
$subscriptions = $conn->query("SELECT id, name, duration_days FROM subscriptions ORDER BY price ASC");
if (!$subscriptions) {
    die("Gagal memuat daftar langganan: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $is_vip = isset($_POST['is_vip']) ? 1 : 0;
    $selected_subscription_id = $is_vip ? (int)$_POST['subscription_id'] : null;

    // Validasi input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua kolom harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok.";
    } elseif ($is_vip && (!$selected_subscription_id || !$conn->query("SELECT id FROM subscriptions WHERE id = $selected_subscription_id")->num_rows)) {
        $error = "Pilih paket VIP yang valid.";
    } else {
        // Cek email duplikat
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email sudah terdaftar. Gunakan email lain.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Default values
            $profile_image = 'default-avatar.png';

            // Upload gambar jika ada
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profile_image'];

                if (!in_array($file['type'], $allowed_types)) {
                    $error = "Hanya file JPG, PNG, atau WebP yang diperbolehkan.";
                } elseif ($file['size'] > $max_size) {
                    $error = "Ukuran file maksimal 2MB.";
                } else {
                    $check = getimagesize($file['tmp_name']);
                    if (!$check) {
                        $error = "File yang diunggah bukan gambar valid.";
                    } else {
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = 'profile_' . uniqid() . '.' . $ext;
                        $filepath = $upload_dir . $filename;

                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $profile_image = $filename;
                        } else {
                            $error = "Gagal menyimpan gambar.";
                        }
                    }
                }
            }

            if (!empty($error)) {
                // Akan ditampilkan di form
            } else {
                // Jika VIP, ambil durasi dari subscriptions
                $expires_at = null;
                if ($is_vip) {
                    $sub_result = $conn->query("SELECT duration_days FROM subscriptions WHERE id = $selected_subscription_id");
                    if ($sub_result && $row = $sub_result->fetch_assoc()) {
                        $duration = (int)$row['duration_days'];
                        $expires_at = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                    } else {
                        $error = "Paket langganan tidak ditemukan.";
                    }
                }

                if (!empty($error)) {
                    // Akan ditampilkan di form
                } else {
                    // Simpan ke database
                    $stmt = $conn->prepare("
                        INSERT INTO users (name, email, password, is_vip, subscription_id, expires_at, profile_image) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param(
                        "sssiiss", 
                        $name, $email, $hashed_password, $is_vip, $selected_subscription_id, $expires_at, $profile_image
                    );

                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
                        header("Location: login.php");
                        exit;
                    } else {
                        $error = "Gagal menyimpan data pengguna: " . htmlspecialchars($conn->error);
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - StreamFlix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
        .glass { backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 215, 0, 0.1); }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(255, 215, 0, 0.2); }
        .btn-gold { @apply px-6 py-3 rounded-full bg-yellow-400 hover:bg-yellow-500 text-black font-bold transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-xl; }
        .subscription-card { @apply border-2 border-gray-600 p-4 rounded-lg cursor-pointer hover:border-yellow-400 transition-all duration-300; }
        .subscription-card.selected { @apply border-yellow-400 bg-yellow-400/10 ring-2 ring-yellow-400; }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-gray-800 glass card-hover rounded-xl shadow-lg p-8 text-center" data-aos="fade-up">
        <h3 class="text-2xl font-extrabold text-yellow-400 mb-6">Daftar Akun Baru</h3>

        <?php if (!empty($error)): ?>
            <div class="bg-red-900/70 border-l-4 border-red-500 text-red-200 p-3 mb-4 text-sm rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-4">
            <!-- Nama -->
            <div class="relative">
                <label for="name" class="block text-left text-sm font-medium mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($name ?? '') ?>" placeholder="John Doe" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>

            <!-- Email -->
            <div class="relative">
                <label for="email" class="block text-left text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="email@example.com" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>

            <!-- Password -->
            <div class="relative">
                <label for="password" class="block text-left text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" id="password" placeholder="••••••••" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>

            <!-- Konfirmasi Password -->
            <div class="relative">
                <label for="confirm_password" class="block text-left text-sm font-medium mb-1">Konfirmasi Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>

            <!-- Foto Profil -->
            <div class="relative">
                <label for="profile_image" class="block text-left text-sm font-medium mb-1">Foto Profil (Opsional)</label>
                <input type="file" name="profile_image" id="profile_image" accept=".jpg,.jpeg,.png,.webp" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-yellow-400 file:text-black hover:file:bg-yellow-500">
            </div>

            <!-- Pilih Langganan -->
            <div class="mt-6">
                <label class="block text-left text-sm font-medium mb-3">Langganan</label>
                <div class="space-y-3">
                    <label class="flex items-center gap-3">
                        <input type="radio" name="is_vip" value="0" checked onchange="toggleSubscriptionOptions()" class="w-4 h-4 text-yellow-500">
                        <span>Gratis</span>
                    </label>

                    <?php while ($sub = $subscriptions->fetch_assoc()): ?>
                        <label class="subscription-card" onclick="selectSubscription(this, <?= $sub['id'] ?>)">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="is_vip" value="1" class="w-4 h-4 text-yellow-500">
                                    <div>
                                        <div class="font-medium"><?= htmlspecialchars($sub['name']) ?></div>
                                        <div class="text-sm text-gray-400"><?= $sub['duration_days'] ?> hari</div>
                                    </div>
                                </div>
                                <input type="hidden" name="subscription_id" value="<?= $sub['id'] ?>" class="sub-id" disabled>
                            </div>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-gold mt-6 w-full">Daftar</button>
        </form>

        <p class="mt-4 text-sm text-gray-400">
            Sudah punya akun?
            <a href="login.php" class="text-yellow-400 hover:text-yellow-300 font-medium">Login di sini</a>
        </p>
    </div>

    <script>
        function toggleSubscriptionOptions() {
            document.querySelectorAll('.subscription-card input[type="radio"]').forEach(radio => {
                radio.checked = false;
            });
            document.querySelectorAll('.subscription-card .sub-id').forEach(input => {
                input.disabled = true;
            });
            document.querySelectorAll('.subscription-card').forEach(card => {
                card.classList.remove('selected');
            });
        }

        function selectSubscription(card, id) {
            // Reset semua
            document.querySelectorAll('.subscription-card').forEach(c => {
                c.classList.remove('selected');
                c.querySelector('.sub-id').disabled = true;
            });

            // Aktifkan yang dipilih
            card.classList.add('selected');
            const subIdInput = card.querySelector('.sub-id');
            subIdInput.disabled = false;
            subIdInput.value = id;

            // Centang radio button
            const radio = card.querySelector('input[type="radio"]');
            radio.checked = true;
        }
    </script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });
    </script>
</body>
</html>