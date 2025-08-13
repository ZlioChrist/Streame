<?php
// about.php - Tentang Streamé (Khusus Pengguna VIP)
include 'config.php';
// session_start();

// 🔐 Hanya untuk pengguna VIP
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_vip']) {
    header("Location: pricing.php");
    exit();
}

// Ambil data pengguna
$user = $_SESSION['user'];
$username = $user['name'];
$profile_image = $user['profile_image'] ?? null;

// URL foto profil
$upload_dir = 'uploads/profiles/';
$profile_url = $profile_image && file_exists($upload_dir . $profile_image)
    ? $upload_dir . $profile_image
    : 'https://ui-avatars.com/api/?name=' . urlencode($username) . '&background=FFD700&color=000000&size=128';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About - StreamFlix</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Animate On Scroll (AOS) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

     <?php include "icon.php"; ?>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }

        .glass {
            backdrop-filter: blur(12px);
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 215, 0, 0.15);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(255, 215, 0, 0.25);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-gold {
            @apply px-8 py-4 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-black font-bold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-2xl relative overflow-hidden inline-block text-center font-semibold;
        }

        .btn-gold::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30px;
            height: 30px;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            opacity: 0;
            border-radius: 999px;
            pointer-events: none;
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.6s ease, opacity 0.6s ease;
        }

        .btn-gold:active::before {
            transform: translate(-50%, -50%) scale(3);
            opacity: 0.4;
        }

        .feature-icon {
            transition: all 0.3s ease;
        }

        .feature-icon:hover {
            transform: scale(1.1);
            color: #fbbf24;
        }

        .testimonial-card:hover .testimonial-img {
            transform: scale(1.05) rotate(2deg);
        }

        .testimonial-img {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .star-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .gradient-text {
            background: linear-gradient(90deg, #FFD700 0%, #F59E0B 50%, #D97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }


        .floating {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .glow {
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
        }

        .hover-glow:hover {
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
            transform: translateY(-6px);
            transition: all 0.4s ease;
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen overflow-x-hidden">

<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>
<br><br><br>

<!-- Hero About Section -->
<section class="py-32 bg-gradient-to-br from-gray-900 via-black to-gray-900 text-center relative overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1626814026160-2237a95fc5a2?ixlib=rb-4.0.3')] bg-cover bg-center opacity-10"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
    
    <div class="container mx-auto px-4 max-w-5xl relative">
        <div data-aos="fade-up" data-aos-delay="100">
            <h1 class="text-4xl md:text-6xl font-extrabold mb-6">
                <span class="gradient-text">Tentang <?= SITE_NAME ?></span>
            </h1>
            <p class="text-lg md:text-xl text-gray-300 mb-12 leading-relaxed max-w-3xl mx-auto">
                <?= SITE_NAME ?> adalah platform streaming film dan serial eksklusif yang dirancang khusus untuk pecinta sinema.
                Nikmati ribuan judul dalam resolusi tinggi, tanpa iklan, dan fitur download untuk ditonton offline.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-6">
                <a href="#features" class="btn-gold glow">
                    <i class="bi bi-arrow-down-circle me-2"></i> Jelajahi Fitur
                </a>
                <a href="#testimonials" class="btn-gold bg-transparent border-2 border-yellow-400 text-yellow-400 hover:bg-yellow-400 hover:text-black transition-all duration-300">
                    <i class="bi bi-chat-left-text me-2"></i> Lihat Ulasan
                </a>
            </div>
        </div>
    </div>
    
    <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-black to-transparent"></div>
</section>

<!-- Features Section -->
<section id="features" class="py-20 relative">
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1616530078916-8192d67bc365?ixlib=rb-4.0.3')] bg-cover bg-center opacity-10"></div>
    
    <div class="container mx-auto px-4 relative">
        <div data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold mb-16 text-center gradient-text">Mengapa Harus <?= SITE_NAME ?> ?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 justify-items-center">
            
            <!-- Feature 1 -->
            <div class="bg-gray-800 glass p-8 rounded-2xl shadow-xl card-hover transition-all duration-500 max-w-sm" data-aos="fade-up" data-aos-delay="200">
                <div class="flex justify-center mb-6">
                    <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 p-5 rounded-full shadow-lg">
                        <i class="bi bi-film text-4xl text-black"></i>
                    </div>
                </div>
                <h3 class="font-bold text-2xl text-center text-[#FFFDD0] mb-4">Film HD Berkualitas</h3>
                <p class="text-gray-300 text-center leading-relaxed">
                    Streaming film dalam kualitas Full HD 4K tanpa buffering, dengan audio surround 7.1 untuk pengalaman bioskop di rumah.
                </p>
                <div class="mt-6 text-center">
                    <i class="bi bi-check-circle text-green-400 me-2"></i>
                    <span class="text-sm text-gray-400">Resolusi hingga 4K HDR</span>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="bg-gray-800 glass p-8 rounded-2xl shadow-xl card-hover transition-all duration-500 max-w-sm" data-aos="fade-up" data-aos-delay="400">
                <div class="flex justify-center mb-6">
                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 p-5 rounded-full shadow-lg">
                        <i class="bi bi-cloud-download text-4xl text-white"></i>
                    </div>
                </div>
                <h3 class="font-bold text-2xl text-center text-[#FFFDD0] mb-4">Offline Download</h3>
                <p class="text-gray-300 text-center leading-relaxed">
                    Simpan film favoritmu dan tonton kapan saja tanpa koneksi internet. Sinkronisasi otomatis antar perangkat.
                </p>
                <div class="mt-6 text-center">
                    <i class="bi bi-check-circle text-green-400 me-2"></i>
                    <span class="text-sm text-gray-400">Download hingga 10 perangkat</span>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="bg-gray-800 glass p-8 rounded-2xl shadow-xl card-hover transition-all duration-500 max-w-sm" data-aos="fade-up" data-aos-delay="600">
                <div class="flex justify-center mb-6">
                    <div class="bg-gradient-to-br from-purple-400 to-purple-600 p-5 rounded-full shadow-lg">
                        <i class="bi bi-stars text-4xl text-white"></i>
                    </div>
                </div>
                <h3 class="font-bold text-2xl text-center text-[#FFFDD0] mb-4">Koleksi Eksklusif</h3>
                <p class="text-gray-300 text-center leading-relaxed">
                    Film dokumenter, behind the scenes, hingga konten original eksklusif hanya tersedia di StreamFlix. Update konten setiap minggu.
                </p>
                <div class="mt-6 text-center">
                    <i class="bi bi-check-circle text-green-400 me-2"></i>
                    <span class="text-sm text-gray-400">100+ konten eksklusif</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-black bg-opacity-80">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div data-aos="fade-up" data-aos-delay="100">
                <div class="text-4xl font-bold text-yellow-400 mb-2">10K+</div>
                <div class="text-gray-300">Film & Serial</div>
            </div>
            <div data-aos="fade-up" data-aos-delay="300">
                <div class="text-4xl font-bold text-yellow-400 mb-2">100+</div>
                <div class="text-gray-300">Negara</div>
            </div>
            <div data-aos="fade-up" data-aos-delay="500">
                <div class="text-4xl font-bold text-yellow-400 mb-2">99.9%</div>
                <div class="text-gray-300">Uptime</div>
            </div>
            <div data-aos="fade-up" data-aos-delay="700">
                <div class="text-4xl font-bold text-yellow-400 mb-2">24/7</div>
                <div class="text-gray-300">Support</div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="py-20 relative">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1611510338559-2f463335093c?ixlib=rb-4.0.3')] bg-cover bg-center opacity-5"></div>
    
    <div class="container mx-auto px-4 max-w-6xl relative">
        <div data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold mb-16 text-center gradient-text">Apa Kata Pelanggan Kami ?</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">

            <!-- Testimonial 1 -->
            <div class="bg-gray-800 glass p-8 rounded-2xl shadow-xl transition-all duration-500 testimonial-card hover-glow" data-aos="fade-up" data-aos-delay="200">
                <div class="flex justify-center mb-6">
                    <div class="relative w-24 h-24 overflow-hidden rounded-full border-4 border-yellow-400 border-opacity-50">
                        <img src="images/juliper.jpg" 
                             alt="Juliper" 
                             class="w-full h-full object-cover testimonial-img">
                    </div>
                </div>
                <div class="flex justify-center mb-4 text-yellow-400">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-half"></i>
                </div>
                <p class="text-gray-200 italic text-center mb-6 leading-relaxed">
                    "StreamFlix memberikan pengalaman nonton terbaik. Tidak ada iklan dan koleksinya sangat lengkap! Fitur download sangat membantu saat traveling."
                </p>
                <div class="text-center">
                    <div class="font-semibold text-yellow-400">Juliper</div>
                    <div class="text-sm text-gray-400">Pengguna VIP sejak 2022</div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="bg-gray-800 glass p-8 rounded-2xl shadow-xl transition-all duration-500 testimonial-card hover-glow" data-aos="fade-up" data-aos-delay="400">
                <div class="flex justify-center mb-6">
                    <div class="relative w-24 h-24 overflow-hidden rounded-full border-4 border-yellow-400 border-opacity-50">
                        <img src="images/fauzi.jpg" 
                             alt="Fauzi" 
                             class="w-full h-full object-cover testimonial-img">
                    </div>
                </div>
                <div class="flex justify-center mb-4 text-yellow-400">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                </div>
                <p class="text-gray-200 italic text-center mb-6 leading-relaxed">
                    "Saya sudah langganan VIP selama 6 bulan dan tidak pernah menyesal. Streaming lancar dan fitur download sangat membantu. Koleksi dokumenter-nya luar biasa!"
                </p>
                <div class="text-center">
                    <div class="font-semibold text-yellow-400">Fauzi</div>
                    <div class="text-sm text-gray-400">Pengguna sejak 2021</div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="bg-gray-800 glass p-8 rounded-2xl shadow-xl transition-all duration-500 testimonial-card hover-glow" data-aos="fade-up" data-aos-delay="600">
                <div class="flex justify-center mb-6">
                    <div class="relative w-24 h-24 overflow-hidden rounded-full border-4 border-yellow-400 border-opacity-50">
                        <img src="images/fendy.png" 
                             alt="Fendy" 
                             class="w-full h-full object-cover testimonial-img">
                    </div>
                </div>
                <div class="flex justify-center mb-4 text-yellow-400">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                </div>
                <p class="text-gray-200 italic text-center mb-6 leading-relaxed">
                    "Fitur live event streaming seru banget! Bisa nonton konser artis kesayangan langsung dari rumah. Kualitas gambar dan suaranya sangat memuaskan."
                </p>
                <div class="text-center">
                    <div class="font-semibold text-yellow-400">Fendy</div>
                    <div class="text-sm text-gray-400">Pengguna VIP sejak 2023</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-24 bg-black bg-opacity-90">
    <div class="container mx-auto px-6">
        <!-- Judul -->
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-4xl md:text-5xl font-bold gradient-text">Tim Kami</h2>
            <p class="text-gray-400 mt-4 text-lg max-w-xl mx-auto">
                Orang-orang di balik layar yang membuat pengalaman streaming Anda luar biasa.
            </p>
        </div>

        <!-- Grid Anggota Tim -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <!-- Team Member 1 -->
            <div 
                class="text-center group hover-glow" 
                data-aos="fade-up" 
                data-aos-delay="200"
            >
                <!-- Foto Profil -->
                <div class="relative w-36 h-36 mx-auto mb-5 overflow-hidden rounded-full border-4 border-yellow-400/70 group-hover:border-yellow-400 transition-all duration-500 shadow-lg">
                    <img 
                        src="images/christ.jpg" 
                        alt="CEO" 
                        class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                    >
                    <!-- Overlay saat hover -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-500 flex items-center justify-center">
                        <i class="bi bi-arrow-up-right-circle text-yellow-400 text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                    </div>
                </div>

                <!-- Info -->
                <h3 class="text-2xl font-bold text-white mb-1 group-hover:text-yellow-400 transition-colors duration-300">
                    Christian
                </h3>
                <p class="text-yellow-400 font-medium">CEO & Founder</p>
                <p class="text-gray-400 text-sm mt-2">Visi dan kepemimpinan di balik Streamé.</p>
            </div>

            <!-- Team Member 2 -->
            <div 
                class="text-center group hover-glow" 
                data-aos="fade-up" 
                data-aos-delay="300"
            >
                <div class="relative w-36 h-36 mx-auto mb-5 overflow-hidden rounded-full border-4 border-yellow-400/70 group-hover:border-yellow-400 transition-all duration-500 shadow-lg">
                    <img 
                        src="images/ravina.jpg" 
                        alt="CTO" 
                        class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                    >
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-500 flex items-center justify-center">
                        <i class="bi bi-arrow-up-right-circle text-yellow-400 text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white mb-1 group-hover:text-yellow-400 transition-colors duration-300">
                    Ravina
                </h3>
                <p class="text-yellow-400 font-medium">CTO</p>
                <p class="text-gray-400 text-sm mt-2">Mengawasi teknologi dan inovasi platform.</p>
            </div>

            <!-- Team Member 3 -->
            <div 
                class="text-center group hover-glow" 
                data-aos="fade-up" 
                data-aos-delay="400"
            >
                <div class="relative w-36 h-36 mx-auto mb-5 overflow-hidden rounded-full border-4 border-yellow-400/70 group-hover:border-yellow-400 transition-all duration-500 shadow-lg">
                    <img 
                        src="images/jonathan.jpg" 
                        alt="CFO" 
                        class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                    >
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-500 flex items-center justify-center">
                        <i class="bi bi-arrow-up-right-circle text-yellow-400 text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white mb-1 group-hover:text-yellow-400 transition-colors duration-300">
                    Jonathan
                </h3>
                <p class="text-yellow-400 font-medium">CFO</p>
                <p class="text-gray-400 text-sm mt-2">Mengelola keuangan dan pertumbuhan bisnis.</p>
            </div>

            <!-- Team Member 4 -->
            <div 
                class="text-center group hover-glow" 
                data-aos="fade-up" 
                data-aos-delay="500"
            >
                <div class="relative w-36 h-36 mx-auto mb-5 overflow-hidden rounded-full border-4 border-yellow-400/70 group-hover:border-yellow-400 transition-all duration-500 shadow-lg">
                    <img 
                        src="images/alycia.jpg" 
                        alt="CMO" 
                        class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                    >
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-500 flex items-center justify-center">
                        <i class="bi bi-arrow-up-right-circle text-yellow-400 text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white mb-1 group-hover:text-yellow-400 transition-colors duration-300">
                    Alycia
                </h3>
                <p class="text-yellow-400 font-medium">CMO</p>
                <p class="text-gray-400 text-sm mt-2">Membawa Streamé ke hati jutaan penonton.</p>
            </div>
        </div>
    </div>
</section>

<!-- Exclusive Note -->
<section class="py-20 px-6 text-center bg-gradient-to-b from-transparent to-black">
    <div class="container mx-auto max-w-4xl">
        <i class="bi bi-award text-6xl text-yellow-400 mb-6"></i>
        <h2 class="text-3xl md:text-4xl font-bold gradient-text mb-6">Anda Sudah di Tempat yang Tepat</h2>
        <p class="text-xl text-gray-300 leading-relaxed">
            Halaman ini adalah bentuk apresiasi kami untuk Anda — anggota VIP yang setia. 
            Terima kasih telah mempercayai Streamé sebagai teman setia menonton Anda. 
            Kami akan terus memberikan yang terbaik, karena Anda layak mendapatkannya.
        </p>
        <div class="mt-10">
            <img 
                src="<?= htmlspecialchars($profile_url) ?>" 
                alt="Foto Anda" 
                class="w-20 h-20 rounded-full border-4 border-yellow-400 mx-auto shadow-2xl"
            >
            <p class="text-yellow-400 font-semibold mt-4">Dengan hangat, <br><span class="text-white"><?= htmlspecialchars($username) ?></span></p>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include 'footer.php'; ?>

<!-- AOS CSS & JS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'smooth',
        once: true,
        offset: 100
    });
</script>

</body>
</html>