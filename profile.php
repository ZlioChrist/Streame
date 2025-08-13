<?php
// profile.php - Halaman Profil Pengguna
session_start();

// 🔐 Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 🔗 Sertakan koneksi database
include 'config.php';

$user = $_SESSION['user'];
$user_id = $user['id'];

// 🔍 Ambil data terbaru dari database
$stmt = $conn->prepare("SELECT name, email, profile_image, is_vip, expires_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: login.php?error=user_not_found");
    exit();
}

$user_data = $result->fetch_assoc();
$username = $user_data['name'];
$email = $user_data['email'];
$profile_image = $user_data['profile_image'] ?? null;
$is_vip = $user_data['is_vip'];
$expires_at = $user_data['expires_at'];
$stmt->close();

// 📁 Folder upload
$upload_dir = 'uploads/profiles/';
$default_avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($username) . '&background=FFD700&color=000000&size=128';
$profile_image_url = $profile_image && file_exists($upload_dir . $profile_image)
    ? $upload_dir . $profile_image
    : $default_avatar_url;

// 🔧 Buat folder upload otomatis
if (!is_dir('uploads')) mkdir('uploads', 0777, true);
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// 📤 1. Proses: Upload Foto Profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_profile') {
    if (empty($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== 0) {
        $_SESSION['error'] = "Pilih gambar terlebih dahulu.";
    } else {
        $file = $_FILES['profile_image'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $tmp_path = $file['tmp_name'];

        if ($file['size'] > $max_size) {
            $_SESSION['error'] = "Ukuran gambar maksimal 2MB.";
        } elseif (!in_array(mime_content_type($tmp_path), $allowed_types)) {
            $_SESSION['error'] = "Format gambar tidak didukung.";
        } elseif (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $_SESSION['error'] = "Ekstensi tidak valid.";
        } elseif (!getimagesize($tmp_path)) {
            $_SESSION['error'] = "File bukan gambar.";
        } else {
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $target = $upload_dir . $new_filename;

            if ($profile_image && file_exists($upload_dir . $profile_image)) {
                unlink($upload_dir . $profile_image);
            }

            if (move_uploaded_file($tmp_path, $target)) {
                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $new_filename, $user_id);
                    $stmt->execute();
                    $_SESSION['user']['profile_image'] = $new_filename;
                    $_SESSION['success'] = "✅ Foto profil berhasil diperbarui!";
                    $stmt->close();
                }
            } else {
                $_SESSION['error'] = "Gagal menyimpan file.";
            }
        }
        header("Location: profile.php");
        exit();
    }
}

// ✏️ 2. Proses: Update Nama & Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);

    if (empty($new_name) || empty($new_email)) {
        $_SESSION['error'] = "Nama dan email wajib diisi.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $new_email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['error'] = "Email sudah digunakan.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_name, $new_email, $user_id);
            if ($stmt->execute()) {
                $_SESSION['user']['name'] = $new_name;
                $_SESSION['user']['email'] = $new_email;
                $_SESSION['success'] = "✅ Profil berhasil diperbarui!";
            } else {
                $_SESSION['error'] = "Gagal menyimpan.";
            }
            $stmt->close();
        }
        header("Location: profile.php");
        exit();
    }
}

// // ❤️ Ambil jumlah favorit
// $favorites_count = 0;
// $stmt = $conn->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
// if ($stmt) {
//     $stmt->bind_param("i", $user_id);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $favorites_count = $result->fetch_assoc()['total'];
//     $stmt->close();
// }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profil Saya - Streamé</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .glass {
            background: rgba(30, 30, 30, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid #FFD700;
            border-radius: 50%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .profile-img:hover {
            transform: scale(1.08);
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }
        .editable {
            @apply cursor-pointer hover:bg-gray-700/50 p-2 rounded transition;
        }
        .editable:hover .text-content {
            @apply text-yellow-400;
        }
        .editable input {
            @apply bg-gray-800 border border-yellow-400 text-white px-2 py-1 rounded text-sm w-full;
        }
        .info-item {
            @apply flex items-center gap-3 py-2 border-b border-gray-700 last:border-0;
        }
        .info-icon {
            @apply text-yellow-400;
        }
        .alert {
            @apply p-3 mb-4 text-sm rounded-lg;
        }
        .alert-error {
            @apply bg-red-900/50 text-red-200 border border-red-700;
        }
        .alert-success {
            @apply bg-green-900/50 text-green-200 border border-green-700;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 text-white min-h-screen">

<?php include 'includes/navbar.php'; ?>

<div class="container mx-auto px-6 py-20">
    <div class="max-w-3xl mx-auto glass p-8" data-aos="fade-up">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold gradient-text">Profil Saya</h1>
            <p class="text-gray-400 mt-2">Kelola informasi dan foto profil Anda</p>
        </div>

        <!-- Alert -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error mb-6">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success mb-6">
                <i class="bi bi-check-circle me-1"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Foto Profil (Klik untuk ganti) -->
        <div class="flex flex-col items-center mb-10">
            <label for="profileImageInput" class="cursor-pointer group">
                <img 
                    id="previewImage" 
                    src="<?= htmlspecialchars($profile_image_url) ?>" 
                    alt="Foto Profil" 
                    class="profile-img shadow-lg"
                >
                <div class="mt-4 text-yellow-400 hover:text-yellow-300 text-sm flex items-center gap-1 transition">
                    <i class="bi bi-pencil"></i> Klik untuk Ganti
                </div>
            </label>

            <!-- Form Upload Tersembunyi -->
            <form action="" method="post" enctype="multipart/form-data" id="uploadForm" class="hidden">
                <input type="file" name="profile_image" id="profileImageInput" accept="image/*">
                <input type="hidden" name="action" value="upload_profile">
            </form>
        </div>

        <!-- Info Pengguna -->
        <div class="space-y-4 bg-gray-800/50 rounded-xl p-6">
            <!-- Nama -->
            <div class="info-item editable" onclick="editField('name', '<?= htmlspecialchars($username, ENT_QUOTES) ?>')">
                <i class="bi bi-person info-icon"></i>
                <div class="flex-1">
                    <div class="text-sm text-gray-400">Nama</div>
                    <div id="name-display" class="font-semibold"><?= htmlspecialchars($username) ?></div>
                    <input type="text" id="name-input" class="hidden" value="<?= htmlspecialchars($username) ?>">
                </div>
            </div>

            <!-- Email -->
            <div class="info-item editable" onclick="editField('email', '<?= htmlspecialchars($email, ENT_QUOTES) ?>')">
                <i class="bi bi-envelope info-icon"></i>
                <div class="flex-1">
                    <div class="text-sm text-gray-400">Email</div>
                    <div id="email-display" class="font-semibold"><?= htmlspecialchars($email) ?></div>
                    <input type="email" id="email-input" class="hidden" value="<?= htmlspecialchars($email) ?>">
                </div>
            </div>

            <!-- Status -->
            <div class="info-item">
                <i class="bi bi-star-fill info-icon"></i>
                <div>
                    <div class="text-sm text-gray-400">Status</div>
                    <div class="font-semibold text-yellow-400"><?= $is_vip ? 'VIP Member' : 'Gratis' ?></div>
                </div>
            </div>

            <!-- Masa Aktif -->
            <?php if ($is_vip): ?>
                <div class="info-item">
                    <i class="bi bi-calendar-check text-green-400"></i>
                    <div>
                        <div class="text-sm text-gray-400">Masa Aktif</div>
                        <div class="font-semibold"><?= date('d M Y', strtotime($expires_at)) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Favorit -->
            <!-- <?php if ($favorites_count > 0): ?>
                <div class="info-item">
                    <i class="bi bi-heart text-red-400"></i>
                    <div>
                        <div class="text-sm text-gray-400">Favorit</div>
                        <div class="font-semibold"><?= $favorites_count ?> film tersimpan</div>
                    </div>
                </div>
            <?php endif; ?>
        </div> -->

        <!-- Logout -->
        <div class="mt-10 text-center">
            <a href="logout.php" class="text-red-400 hover:text-red-300 transition inline-flex items-center gap-2 text-sm">
                <i class="bi bi-box-arrow-right"></i> Keluar dari Akun
            </a>
        </div>
    </div>
</div>

<script>
    // Klik avatar → muncul dialog pilih file
    document.getElementById('previewImage').addEventListener('click', () => {
        document.getElementById('profileImageInput').click();
    });

    // Auto-submit saat pilih foto
    document.getElementById('profileImageInput').addEventListener('change', function() {
        if (this.files[0]) {
            document.getElementById('uploadForm').submit();
        }
    });

    // Edit Nama & Email (inline)
    function editField(field, currentValue) {
        const display = document.getElementById(`${field}-display`);
        const input = document.getElementById(`${field}-input`);

        // Jika sedang dalam mode input, jangan buka lagi
        if (!input.classList.contains('hidden')) return;

        // Ubah ke mode input
        display.classList.add('hidden');
        input.classList.remove('hidden');
        input.focus();

        // Simpan saat blur
        input.onblur = function () {
            const newValue = this.value.trim();
            if (newValue && newValue !== currentValue) {
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="name" value="${field === 'name' ? newValue : document.getElementById('name-input').value}">
                    <input type="hidden" name="email" value="${field === 'email' ? newValue : document.getElementById('email-input').value}">
                `;
                document.body.appendChild(form);
                form.submit();
            } else {
                // Kembali ke tampilan
                input.classList.add('hidden');
                display.classList.remove('hidden');
            }
        };
    }
</script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, easing: 'smooth' });
</script>

</body>
</html>