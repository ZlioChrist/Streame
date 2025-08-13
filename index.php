<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamFlix - Nonton Film Online</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com "></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href=" https://cdn.jsdelivr.net/npm/bootstrap-icons @1.10.5/font/bootstrap-icons.css">

    <!-- Animate On Scroll (AOS) -->
    <link href="https://unpkg.com/aos @2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .hero {
            background: linear-gradient(to bottom, #000, #111);
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: url('../images/banner.jpg') no-repeat center center/cover;
            z-index: -1;
            filter: brightness(0.5);
        }

        .btn-custom {
            @apply px-6 py-3 rounded-full font-bold text-white transition-all duration-300 transform hover:scale-105 bg-[#FFD700] hover:bg-yellow-500 shadow-lg hover:shadow-xl relative overflow-hidden group;
        }

        .btn-custom::before {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 30px; height: 30px;
            background: radial-gradient(circle closest-side, rgba(255,255,255,0.3) 0%, transparent 100%);
            transform: translate(-50%, -50%) scale(0);
            opacity: 0;
            border-radius: 999px;
            pointer-events: none;
            transition: transform 0.6s ease, opacity 0.6s ease;
        }

        .btn-custom:active::before {
            transform: translate(-50%, -50%) scale(2);
            opacity: 0.3;
        }

        .glass {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-hover:hover {
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.3);
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-black text-white font-poppins overflow-x-hidden">

<!-- Navbar -->
<?php include 'navbar_index.php'; ?>

<!-- Hero Section -->
<section class="hero flex items-center justify-center">
    <div class="relative z-10 text-center px-4 max-w-3xl mx-auto">
        <h1 data-aos="fade-down" class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-[#FFD700] leading-tight mb-4">
            Tonton Ribuan Film Favoritmu
        </h1>
        <p data-aos="fade-up" data-aos-delay="300" class="text-lg md:text-xl text-gray-300 mb-6">
            Nikmati pengalaman nonton film berkualitas dalam nuansa emas yang mewah.
        </p>
        <a href="pricing.php"
           data-aos="zoom-in"
           data-aos-delay="500"
           class="btn-custom inline-block">
           Mulai Sekarang
        </a>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-gradient-to-br from-black via-gray-900 to-black">
    <div class="container mx-auto px-4 text-center">
        <h2 data-aos="fade-up" class="text-2xl md:text-3xl font-bold mb-10 text-[#FFD700]">Kenapa Harus StreamFlix?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div data-aos="fade-right" class="bg-gray-800 glass p-6 rounded-lg shadow-lg card-hover transition-transform transform hover:-translate-y-2">
                <i class="bi bi-hd-display text-[#FFD700] text-4xl mb-4 block mx-auto"></i>
                <h4 class="text-xl font-semibold mb-2 text-[#FFFDD0]">📺 HD Quality</h4>
                <p class="text-gray-300">Nikmati film dalam resolusi tinggi tanpa buffering.</p>
            </div>
            <div data-aos="fade-up" class="bg-gray-800 glass p-6 rounded-lg shadow-lg card-hover transition-transform transform hover:-translate-y-2">
                <i class="bi bi-phone text-[#FFD700] text-4xl mb-4 block mx-auto"></i>
                <h4 class="text-xl font-semibold mb-2 text-[#FFFDD0]">📱 Tonton Dimana Saja</h4>
                <p class="text-gray-300">Akses di perangkat apa pun, kapan saja, di mana saja.</p>
            </div>
            <div data-aos="fade-left" class="bg-gray-800 glass p-6 rounded-lg shadow-lg card-hover transition-transform transform hover:-translate-y-2">
                <i class="bi bi-film text-[#FFD700] text-4xl mb-4 block mx-auto"></i>
                <h4 class="text-xl font-semibold mb-2 text-[#FFFDD0]">📚 Semua Genre</h4>
                <p class="text-gray-300">Dari Action hingga Romance, semua tersedia untuk kamu.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-black via-gray-900 to-black text-center">
    <div class="container mx-auto px-4">
        <h2 data-aos="fade-up" class="text-2xl md:text-3xl font-bold mb-4 text-[#FFD700]">Siap Menonton?</h2>
        <p data-aos="fade-up" data-aos-delay="100" class="mb-6 text-gray-400">Streaming ribuan film, hanya dengan satu klik!</p>
        <a href="pricing.php"
           data-aos="fade-up"
           data-aos-delay="200"
           class="btn-custom inline-block">
           Langganan Sekarang
        </a>
    </div>
</section>

<!-- Footer -->
<footer class="bg-black bg-opacity-90 text-center py-6 text-gray-500 mt-12">
    &copy; <?= date('Y') ?>, <?= SITE_COPY ?>
</footer>

<!-- JS AOS -->
<script src="https://unpkg.com/aos @2.3.1/dist/aos.js"></script>
<script>
    AOS.init();
</script>

<!-- JS Toggle Navbar -->
<script>
    document.getElementById('navToggle').addEventListener('click', function () {
        document.getElementById('navMenu').classList.toggle('hidden');
    });
</script>
</body>
</html>