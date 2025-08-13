<?php
// movie_detail.php - Detail Film
session_start();
include 'config.php';

// Middleware: Cek Login dan Status VIP
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

if (!$user['is_vip'] || new DateTime($user['expires_at']) < new DateTime()) {
    header("Location: pricing.php");
    exit();
}

// Get content ID from URL parameter
$id = $_GET['id'] ?? null; // Fix: Use $id instead of $content_id

// Validate that ID exists and is a positive integer
if (!$id || !is_numeric($id) || $id <= 0) {
    // Log the error for debugging
    error_log("Invalid content ID received: " . ($id ?? 'null'));
    
    // Redirect to a safe page or show error
    $_SESSION['error'] = "ID konten tidak valid.";
    header("Location: catalog.php");
    exit();
}

// Use the correct table structure for movies
$stmt = $conn->prepare("SELECT m.*, 
                               GROUP_CONCAT(DISTINCT c.name) as categories, 
                               GROUP_CONCAT(DISTINCT r.name) as regions
                        FROM movies m
                        LEFT JOIN movie_categories mc ON m.id = mc.movie_id
                        LEFT JOIN categories c ON mc.category_id = c.id
                        LEFT JOIN movie_regions mr ON m.id = mr.movie_id
                        LEFT JOIN regions r ON mr.region_id = r.id
                        WHERE m.id = ?
                        GROUP BY m.id");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    die("Gagal memproses permintaan.");
}

$id = intval($id);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();
$stmt->close();

if (!$movie) {
    $_SESSION['error'] = "Film tidak ditemukan.";
    header("Location: catalog.php");
    exit();
}

// Save to watch history
$stmt = $conn->prepare("
    INSERT INTO watch_history (user_id, movie_id, watched_at) 
    VALUES (?, ?, NOW()) 
    ON DUPLICATE KEY UPDATE watched_at = NOW()
");
$stmt->bind_param("ii", $user['id'], $movie['id']);
$stmt->execute();
$stmt->close();

// Set default values if not set
$movie['categories'] = $movie['categories'] ?? '';
$movie['regions'] = $movie['regions'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8') ?> - StreamFlix</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- AOS Animation -->
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
            border-radius: 1rem;
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(255, 215, 0, 0.25);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-gold {
            @apply px-6 py-3 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-black font-bold transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-2xl relative overflow-hidden inline-block w-full text-center;
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
        .gradient-text {
            background: linear-gradient(90deg, #FFD700 0%, #F59E0B 50%, #D97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .tab-button {
            @apply px-5 py-2 font-medium transition-all duration-300 rounded-full cursor-pointer select-none;
        }
        .tab-button.active {
            @apply bg-gradient-to-r from-yellow-400 to-yellow-500 text-black font-semibold;
        }
        .tab-button.inactive {
            @apply text-gray-400 hover:text-yellow-400;
        }
        .line-clamp-4 {
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .hover-glow:hover {
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
            transform: translateY(-6px);
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">

<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- Header -->
<?php include 'header.php'; ?>

<!-- Film Detail -->
<section class="py-16 px-6 container mx-auto" data-aos="fade-up">
    <div class="flex flex-col lg:flex-row gap-10 items-start">
        <!-- Poster di Kiri -->
        <div class="lg:w-1/3 w-full" data-aos="zoom-in" data-aos-delay="300">
            <div class="group relative overflow-hidden rounded-2xl shadow-2xl cursor-pointer hover-glow" onclick="switchTab('trailer')">
                <img
                    src="<?= htmlspecialchars($movie['image'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8') ?>"
                    class="w-full h-auto object-cover transition-transform duration-700 group-hover:scale-105"
                >
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <i class="bi bi-play-circle-fill text-6xl text-yellow-400"></i>
                </div>
            </div>
        </div>

        <!-- Informasi Film di Kanan -->
        <div class="lg:w-2/3 space-y-6" data-aos="fade-left" data-aos-delay="400">
            <h1 class="text-4xl md:text-5xl font-extrabold gradient-text"><?= htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8') ?></h1>

            <!-- Info Film (Grid) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div class="flex items-center gap-3">
                    <i class="bi bi-tags text-yellow-400 text-lg"></i>
                    <div>
                        <p class="text-sm text-gray-400">Genre</p>
                        <p class="font-medium"><?= htmlspecialchars($movie['categories'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                 <div class="flex items-center gap-3">
                    <i class="bi bi-flag-fill text-yellow-400 text-lg"></i>
                    <div>
                        <p class="text-sm text-gray-400">Negara</p>
                        <p class="font-medium"><?= htmlspecialchars($movie['regions'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <i class="bi bi-clock text-yellow-400 text-lg"></i>
                    <div>
                        <p class="text-sm text-gray-400">Durasi</p>
                        <p class="font-medium"><?= htmlspecialchars($movie['duration'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <i class="bi bi-calendar text-yellow-400 text-lg"></i>
                    <div>
                        <p class="text-sm text-gray-400">Tahun</p>
                        <p class="font-medium"><?= htmlspecialchars($movie['year'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <i class="bi bi-star-fill text-yellow-400 text-lg"></i>
                    <div>
                        <p class="text-sm text-gray-400">Rating</p>
                        <p class="font-medium"><?= number_format($movie['rating'] ?? 0, 1) ?></p>
                    </div>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-yellow-400 mb-2">Deskripsi</h3>
                <p class="text-gray-300 leading-relaxed line-clamp-4">
                    <?= htmlspecialchars($movie['description'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-4 mt-8">
                <button onclick="switchTab('trailer')" class="px-5 py-2 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-500 text-black font-semibold transition-all duration-300 transform hover:scale-105 shadow-md">
                    <i class="bi bi-film me-2"></i> Lihat Trailer
                </button>
                <button onclick="switchTab('nonton')" class="px-5 py-2 rounded-full bg-gray-700 text-gray-300 hover:bg-yellow-600 hover:text-black font-medium transition-all duration-300">
                    <i class="bi bi-play-circle me-2"></i> Nonton Sekarang
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Video Player -->
<section id="videoTabs" class="py-10 px-6 container mx-auto" data-aos="fade-up" data-aos-delay="600">
    <div class="max-w-4xl mx-auto glass rounded-2xl overflow-hidden shadow-2xl border border-yellow-400/20">
        <!-- Trailer Tab -->
        <div id="trailer" class="tab-content p-6">
            <h3 class="text-2xl font-bold mb-4 text-yellow-400">🎬 Trailer Film</h3>
            <?php if (!empty($movie['trailer_url'])): ?>
                <?php
                $trailerUrl = str_replace('watch?v=', 'embed/', htmlspecialchars($movie['trailer_url'], ENT_QUOTES, 'UTF-8'));
                $trailerUrl = preg_replace('/[?&]autoplay=\d/', '', $trailerUrl); // bersihkan autoplay
                ?>
                <iframe
                    id="trailerVideo"
                    class="w-full h-64 sm:h-96 rounded-xl shadow-lg"
                    src="<?= $trailerUrl ?>"
                    frameborder="0"
                    allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                ></iframe>
            <?php else: ?>
                <p class="text-center py-8 text-gray-400">Trailer belum tersedia.</p>
            <?php endif; ?>
        </div>

        <!-- Nonton Tab -->
        <div id="nonton" class="tab-content hidden p-6">
            <h3 class="text-2xl font-bold mb-4 text-yellow-400">▶️ Tonton Film</h3>
            <?php if (!empty($movie['video_url'])): ?>
                <?php if (strpos($movie['video_url'], 'youtube') !== false): ?>
                    <?php
                    $videoUrl = str_replace('watch?v=', 'embed/', htmlspecialchars($movie['video_url'], ENT_QUOTES, 'UTF-8'));
                    $videoUrl = preg_replace('/[?&]autoplay=\d/', '', $videoUrl);
                    ?>
                    <iframe
                        id="filmVideo"
                        class="w-full h-64 sm:h-96 rounded-xl shadow-lg"
                        src="<?= $videoUrl ?>"
                        frameborder="0"
                        allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    ></iframe>
                <?php elseif (strpos($movie['video_url'], 'vimeo') !== false): ?>
                    <?php
                    $videoUrl = preg_replace('/https?:\/\/(www\.)?vimeo\.com\/(\d+)/', 'https://player.vimeo.com/video/$2', $movie['video_url']);
                    ?>
                    <iframe
                        id="filmVideo"
                        class="w-full h-64 sm:h-96 rounded-xl shadow-lg"
                        src="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>"
                        frameborder="0"
                        webkitallowfullscreen
                        mozallowfullscreen
                        allowfullscreen
                    ></iframe>
                <?php else: ?>
                    <video id="filmVideo" class="w-full h-64 sm:h-96 rounded-xl shadow-lg" controls controlsList="nodownload">
                        <source src="<?= htmlspecialchars($movie['video_url'], ENT_QUOTES, 'UTF-8') ?>" type="video/mp4">
                        Browser Anda tidak mendukung tag video.
                    </video>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center py-8 text-gray-400">Film belum tersedia untuk ditonton.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Rekomendasi -->
<?php include 'rekomended.php'; ?>

<!-- Fitur: Unduh untuk Nonton Offline -->
<?php include 'download-movie.php'; ?>

<!-- Footer -->
<?php include 'footer.php'; ?>

<!-- JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 100
    });

    // Switch Tab
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
        document.getElementById(tabId)?.classList.remove('hidden');

        // Scroll ke video
        const videoSection = document.getElementById('videoTabs');
        if (videoSection) {
            videoSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Pause semua video
    function pauseAllVideos() {
        const filmVideo = document.getElementById('filmVideo');
        const trailerVideo = document.getElementById('trailerVideo');
        if (filmVideo && filmVideo.tagName === 'VIDEO') filmVideo.pause();
        if (trailerVideo) trailerVideo.src = trailerVideo.src; // reload iframe
    }

    // Auto-play tab trailer saat diklik
    document.querySelector('[onclick="switchTab(\'trailer\')"]')?.addEventListener('click', pauseAllVideos);
    document.querySelector('[onclick="switchTab(\'nonton\')"]')?.addEventListener('click', pauseAllVideos);
</script>
</body>
</html>