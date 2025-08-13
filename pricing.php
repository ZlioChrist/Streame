<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Ambil semua paket langganan aktif
$stmt = $conn->query("SELECT * FROM subscriptions WHERE is_active = TRUE");

if (!$stmt) {
    die("Query gagal: " . $conn->error);
}

$packages = $stmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pilih Paket - StreamFlix</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com "></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/bootstrap-icons @1.10.5/font/bootstrap-icons.css">

    <!-- Animate On Scroll (AOS) -->
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

<!-- Checkout Section -->
<section class="py-20 px-4 container mx-auto">
    <div class="text-center mb-12">
        <h2 data-aos="fade-up"
            class="text-3xl md:text-4xl font-extrabold text-[#FFD700] mb-4">Pilih Paket Langganan</h2>
        <p data-aos="fade-up"
           data-aos-delay="200"
           class="text-gray-300 max-w-2xl mx-auto">
            Pilih paket yang sesuai dengan kebutuhanmu dan mulai nikmati konten eksklusif  <?= SITE_NAME ?>.
        </p>
    </div>

    <!-- Grid Paket -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 justify-items-center">
        <?php foreach ($packages as $pkg): 
            // Sesuaikan deskripsi berdasarkan nama paket
            $features = [];
            if (str_contains(strtolower($pkg['name']), 'basic')) {
                $features = [
                    '✔️ Akses film biasa',
                    '✔️ Iklan muncul',
                    '❌ Tidak bisa download'
                ];
            } elseif (str_contains(strtolower($pkg['name']), 'vip')) {
                $features = [
                    '✔️ Akses semua konten',
                    '✔️ Tanpa iklan',
                    '✔️ Bisa download film'
                ];
            } elseif (str_contains(strtolower($pkg['name']), 'pro')) {
                $features = [
                    '✔️ Semua fitur VIP',
                    '✔️ Live event eksklusif',
                    '✔️ Diskon merchandise'
                ];
            }
        ?>
            <div data-aos="fade-up"
                 data-aos-delay="<?= rand(100, 600) ?>"
                 class="w-full bg-gray-800 glass rounded-xl p-6 shadow-lg card-hover transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#FFD700]/20">
                <h3 class="text-xl font-semibold text-[#FFFDD0] mb-2"><?= htmlspecialchars($pkg['name']) ?></h3>
                <p class="mt-2 text-amber-400 text-xl font-bold">
                    Rp <?= number_format($pkg['price'], 2, ',', '.') ?>
                </p>
                <p class="mt-1 text-sm text-gray-400">
                    Langganan selama <?= $pkg['duration_days'] ?> hari
                </p>
                <ul class="mt-4 space-y-2 text-sm text-gray-300">
                    <?php foreach ($features as $feature): ?>
                        <li><?= $feature ?></li>
                    <?php endforeach; ?>
                </ul>
                <form method="post" action="process_payment.php" class="mt-6">
                    <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                    <button type="submit" class="btn-gold w-full relative overflow-hidden group">
                        Pilih Paket Ini
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Footer -->
<footer class="bg-black bg-opacity-90 text-center py-6 text-gray-500 mt-12">
    &copy; <?= date('Y') ?>, <?= SITE_COPY ?>
</footer>

<!-- JS AOS -->
<script src="https://unpkg.com/aos @2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 600,
        once: true
    });
</script>

<!-- JS Toggle Navbar -->
<script>
    document.getElementById('navToggle')?.addEventListener('click', function () {
        document.getElementById('navMenu').classList.toggle('hidden');
    });
</script>

</body>
</html>