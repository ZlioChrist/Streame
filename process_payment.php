<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah package_id tersedia
if (!isset($_POST['package_id'])) {
    die("Paket tidak ditemukan.");
}

$package_id = intval($_POST['package_id']);

// Ambil data paket
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE id = ? AND is_active = TRUE");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Paket tidak ditemukan atau tidak aktif.");
}

$package = $result->fetch_assoc();

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran - StreamFlix</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com "></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/bootstrap-icons @1.10.5/font/bootstrap-icons.css">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos @2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .glass {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.2);
        }

        .btn-gold {
            position: relative;
            overflow: hidden;
        }

        .btn-gold::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30px;
            height: 30px;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 100%);
            opacity: 0;
            border-radius: 999px;
            pointer-events: none;
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.6s ease, opacity 0.6s ease;
        }

        .btn-gold:active::before {
            transform: translate(-50%, -50%) scale(2);
            opacity: 0.3;
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen">

<!-- Navbar -->
<?php include 'navbar_index.php'; ?>

<!-- Payment Section -->
<section class="py-16 px-4 container mx-auto">
    <div class="text-center mb-10">
        <h2 class="text-3xl md:text-4xl font-extrabold text-[#FFD700] mb-4">Pembayaran Langganan</h2>
        <p class="text-gray-300 max-w-xl mx-auto">
            Isi form di bawah ini untuk melanjutkan proses pembayaran.
        </p>
    </div>

    <div class="max-w-lg mx-auto bg-gray-800 glass rounded-xl p-8 shadow-lg card-hover transition-all duration-300">
        <form action="confirm_payment.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="package_id" value="<?= $package['id'] ?>">

            <!-- Nama -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Nama Lengkap</label>
                <input type="text" id="name" name="name"
                       value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                       required
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#FFD700]">
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                       required
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#FFD700]">
            </div>

            <!-- Metode Pembayaran -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Metode Pembayaran</label>
                <div class="space-y-3">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="radio" name="payment_method" value="bank_transfer" checked
                               class="w-4 h-4 accent-[#FFD700]" onchange="togglePaymentOptions()">
                        <span>Transfer Bank</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="radio" name="payment_method" value="qris"
                               class="w-4 h-4 accent-[#FFD700]" onchange="togglePaymentOptions()">
                        <span>QRIS</span>
                    </label>
                </div>
            </div>

            <!-- Detail Paket -->
            <div class="bg-gray-900/50 p-4 rounded-lg mb-6">
                <h4 class="font-semibold text-[#FFFDD0]"><?= htmlspecialchars($package['name']) ?></h4>
                <p class="text-amber-400 text-lg font-bold">Rp <?= number_format($package['price'], 2, ',', '.') ?></p>
                <p class="text-sm text-gray-400">Langganan selama <?= $package['duration_days'] ?> hari</p>
            </div>

            <!-- Upload Bukti Transfer (default hidden) -->
            <!-- <div id="transfer-section" class="mb-6 hidden">
                <label class="block text-sm font-medium text-gray-300 mb-2">Upload Bukti Transfer</label>
                <input type="file" name="proof_of_payment" accept="image/*,.pdf"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#FFD700]">
            </div> -->

            <!-- QRIS Display (default hidden) -->
            <div id="qris-section" class="mb-6 hidden text-center">
                <label class="block text-sm font-medium text-gray-300 mb-2">Scan QRIS untuk Bayar</label>
                <img src="images/qris_example.png" alt="QRIS Code" id="qris-image"
                     class="mx-auto w-48 h-48 object-contain border border-dashed border-gray-600 p-2 rounded">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-gold w-full py-3 px-6 rounded-full font-bold transition-all duration-300">
                Bayar Sekarang
            </button>
        </form>
    </div>
</section>

<!-- Footer -->
<footer class="bg-black bg-opacity-90 text-center py-6 text-gray-500 mt-12">
    &copy; <?= date('Y') ?>, <?= SITE_COPY ?>
</footer>
<!-- JS Logic -->
<script>
    function togglePaymentOptions() {
        const bankSelected = document.querySelector('input[name="payment_method"][value="bank_transfer"]').checked;
        const qrisSelected = document.querySelector('input[name="payment_method"][value="qris"]').checked;

        document.getElementById('transfer-section').classList.toggle('hidden', !bankSelected);
        document.getElementById('qris-section').classList.toggle('hidden', !qrisSelected);
    }

    // Panggil pertama kali
    window.onload = () => {
        togglePaymentOptions();
    };
</script>

<!-- JS AOS -->
<script src="https://unpkg.com/aos @2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 600,
        once: true
    });
</script>

</body>
</html>