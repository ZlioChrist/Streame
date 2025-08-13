<?php
// series-detail.php - Detail Series + Player
session_start();
include 'config.php';

// Middleware: Cek Login dan Status VIP
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];
if (!$user['is_vip'] || new DateTime($user['expires_at']) < new DateTime()) {
    header("Location: pricing.php");
    exit;
}

// Ambil ID series dari URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID series tidak valid.");
}

// Ambil data series beserta kategori dan daerah
$stmt = $conn->prepare("SELECT s.*, 
                               GROUP_CONCAT(DISTINCT c.name) as categories, 
                               GROUP_CONCAT(DISTINCT r.name) as regions
                        FROM series s
                        LEFT JOIN series_categories sc ON s.id = sc.series_id
                        LEFT JOIN categories c ON sc.category_id = c.id
                        LEFT JOIN series_regions sr ON s.id = sr.series_id
                        LEFT JOIN regions r ON sr.region_id = r.id
                        WHERE s.id = ?
                        GROUP BY s.id");
if (!$stmt) {
    die("Prepare gagal: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$series = $result->fetch_assoc();
$stmt->close();

if (!$series) {
    die("Series tidak ditemukan.");
}

// Ambil daftar episode
$episodes = [];
$episode_stmt = $conn->prepare("SELECT * FROM episodes WHERE series_id = ? ORDER BY season ASC, episode ASC");
if ($episode_stmt) {
    $episode_stmt->bind_param("i", $id);
    $episode_stmt->execute();
    $episode_result = $episode_stmt->get_result();
    $episodes = $episode_result->fetch_all(MYSQLI_ASSOC);
    $episode_stmt->close();
}

// Tentukan episode pertama sebagai default
$current_episode = !empty($episodes) ? $episodes[0] : null;

// Ambil semua musim unik
$seasons = array_unique(array_column($episodes, 'season'));
sort($seasons);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($series['title']) ?> - StreamFlix</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

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
        .gradient-text {
            background: linear-gradient(90deg, #FFD700 0%, #F59E0B 50%, #D97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hover-glow:hover {
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
            transform: translateY(-6px);
        }
        .line-clamp-4 {
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .player-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .player-container iframe,
        .player-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .episode-card {
            width: 48px;
            height: 48px;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 215, 0, 0.1);
        }
        .episode-card:hover {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 215, 0, 0.25);
            border-color: #fbbf24;
        }
        .episode-card.active {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #000;
            border-color: #d97706;
            box-shadow: 0 0 0 2px #fef3c7, 0 0 10px rgba(255, 215, 0, 0.5);
            font-weight: bold;
            transform: scale(1.05);
            transition: all 0.2s ease;
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>
    <br><br><br>
    <?php include 'header.php'; ?>

    <!-- Series Detail -->
    <section class="py-16 px-6 container mx-auto" data-aos="fade-up">
        <div class="flex flex-col lg:flex-row gap-10 items-start">
            <!-- Poster -->
            <div class="lg:w-1/3 w-full" data-aos="zoom-in" data-aos-delay="300">
                <div class="group relative overflow-hidden rounded-2xl shadow-2xl cursor-pointer hover-glow" onclick="switchTab('trailer')">
                    <img
                        src="<?= htmlspecialchars($series['image']) ?>"
                        alt="<?= htmlspecialchars($series['title']) ?>"
                        class="w-full h-auto object-cover transition-transform duration-700 group-hover:scale-105"
                        onerror="this.onerror=null; this.src='https://via.placeholder.com/300x450?text=No+Image';"
                    >
                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <i class="bi bi-play-circle-fill text-6xl text-yellow-400"></i>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="lg:w-2/3 space-y-6" data-aos="fade-left" data-aos-delay="400">
                <h1 class="text-4xl md:text-5xl font-extrabold gradient-text"><?= htmlspecialchars($series['title']) ?></h1>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-tags text-yellow-400 text-lg"></i>
                        <div>
                            <p class="text-sm text-gray-400">Genre</p>
                            <p class="font-medium">
                                <?= htmlspecialchars(implode(', ', array_filter(explode(',', $series['categories']), 'trim'))) ?: 'Tidak ada' ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="bi bi-geo-alt text-yellow-400 text-lg"></i>
                        <div>
                            <p class="text-sm text-gray-400">Asal</p>
                            <p class="font-medium">
                                <?= htmlspecialchars(implode(', ', array_filter(explode(',', $series['regions']), 'trim'))) ?: 'Tidak diketahui' ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="bi bi-calendar text-yellow-400 text-lg"></i>
                        <div>
                            <p class="text-sm text-gray-400">Tahun</p>
                            <p class="font-medium"><?= htmlspecialchars($series['year']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="bi bi-star-fill text-yellow-400 text-lg"></i>
                        <div>
                            <p class="text-sm text-gray-400">Rating</p>
                            <p class="font-medium"><?= number_format($series['rating'] ?? 0, 1) ?></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-yellow-400 mb-2">Deskripsi</h3>
                    <p class="text-gray-300 leading-relaxed line-clamp-4">
                        <?= htmlspecialchars($series['description']) ?>
                    </p>
                </div>

                <!-- Tab Navigation -->
                <div class="flex gap-4 mt-8">
                    <button onclick="switchTab('trailer')" class="px-5 py-2 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-500 text-black font-semibold transition-all duration-300 transform hover:scale-105 shadow-md">
                        <i class="bi bi-film me-2"></i> Lihat Trailer
                    </button>
                    <button onclick="watchNow()" class="px-5 py-2 rounded-full bg-gray-700 text-gray-300 hover:bg-yellow-600 hover:text-black font-medium transition-all duration-300">
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
                <h3 class="text-2xl font-bold mb-4 text-yellow-400">🎬 Trailer Series</h3>
                <?php if (!empty($series['trailer_url'])): ?>
                    <div class="player-container">
                        <iframe
                            id="trailerVideo"
                            src="<?= str_replace('watch?v=', 'embed/', htmlspecialchars($series['trailer_url'])) ?>?enablejsapi=1"
                            frameborder="0"
                            allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        ></iframe>
                    </div>
                <?php else: ?>
                    <p class="text-center py-8 text-gray-400">Trailer belum tersedia.</p>
                <?php endif; ?>
            </div>

            <!-- Nonton Tab -->
            <div id="nonton" class="tab-content hidden p-6">
                <h3 class="text-2xl font-bold mb-4 text-yellow-400">▶️ Tonton Series</h3>
                <?php if ($current_episode): ?>
                    <div class="player-container" id="playerContainer">
                        <?php if (strpos($current_episode['video_url'], 'youtube') !== false): ?>
                            <iframe
                                id="filmVideo"
                                src="<?= str_replace('watch?v=', 'embed/', htmlspecialchars($current_episode['video_url'])) ?>?enablejsapi=1"
                                frameborder="0"
                                allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            ></iframe>
                        <?php elseif (strpos($current_episode['video_url'], 'vimeo') !== false): ?>
                            <?php preg_match("/vimeo\.com\/(\d+)/", $current_episode['video_url'], $matches); ?>
                            <?php if (isset($matches[1])): ?>
                                <iframe
                                    id="filmVideo"
                                    src="https://player.vimeo.com/video/<?= $matches[1] ?>?api=1"
                                    frameborder="0"
                                    webkitallowfullscreen
                                    mozallowfullscreen
                                    allowfullscreen
                                ></iframe>
                            <?php else: ?>
                                <p class="text-red-400">Link Vimeo tidak valid.</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <video id="filmVideo" controls controlsList="nodownload">
                                <source src="<?= htmlspecialchars($current_episode['video_url']) ?>" type="video/mp4">
                                Browser Anda tidak mendukung video.
                            </video>
                        <?php endif; ?>
                    </div>

                    <!-- Daftar Episode -->
                    <div class="mt-8">
                        <div class="relative mb-5 w-full max-w-xs mx-auto">
                            <label for="season-select" class="block text-yellow-400 font-medium text-sm mb-2 text-center">
                                📅 Pilih Season
                            </label>
                            <div class="relative">
                                <select 
                                    id="season-select" 
                                    onchange="showEpisodes(this.value)"
                                    class="appearance-none w-full bg-gray-800 text-white text-sm pl-4 pr-10 py-2 rounded-xl border border-gray-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-300 hover:border-yellow-400"
                                >
                                    <?php foreach ($seasons as $season): ?>
                                        <option 
                                            value="<?= $season ?>" 
                                            <?= ($current_episode && $season == $current_episode['season']) ? 'selected' : '' ?>
                                        >
                                            Season <?= $season ?> (<?= count(array_filter($episodes, fn($ep) => $ep['season'] == $season)) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-yellow-400">
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        <div id="episode-grid" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2 justify-items-center">
                            <!-- Diisi oleh JS -->
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-center py-8 text-gray-400">Belum ada episode yang tersedia.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Rekomendasi -->
    <?php include 'rekomended.php'; ?>

    <!-- Fitur: Unduh untuk Nonton Offline -->
    <?php include 'download-series.php'; ?>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, easing: 'ease-in-out', once: true, offset: 100 });

        const episodesData = <?= json_encode($episodes) ?>;
        const seriesId = <?= $series['id'] ?>;

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById(tabId).classList.remove('hidden');
            pauseAllVideos();
            document.getElementById('videoTabs').scrollIntoView({ behavior: 'smooth' });
        }

        function pauseAllVideos() {
            const iframes = document.querySelectorAll('iframe');
            iframes.forEach(iframe => {
                try {
                    iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                } catch (e) {}
            });
            const videos = document.querySelectorAll('video');
            videos.forEach(video => video.pause());
        }

        function showEpisodes(season) {
            const grid = document.getElementById('episode-grid');
            grid.innerHTML = '';

            const episodesInSeason = episodesData.filter(ep => ep.season == season);

            if (episodesInSeason.length === 0) {
                const p = document.createElement('p');
                p.className = 'text-gray-400 col-span-full text-center py-4 text-sm';
                p.textContent = 'Tidak ada episode di musim ini.';
                grid.appendChild(p);
                return;
            }

            episodesInSeason.forEach(ep => {
                const button = document.createElement('button');
                button.className = 'episode-card';
                button.textContent = ep.episode;
                button.dataset.id = ep.id;
                button.dataset.url = ep.video_url;
                button.dataset.season = ep.season;
                button.dataset.episode = ep.episode;

                if (<?= $current_episode ? $current_episode['id'] : 0 ?> == ep.id) {
                    button.classList.add('active');
                }

                button.title = `Episode ${ep.episode}: ${ep.title}`;
                button.onclick = () => {
                    document.querySelectorAll('.episode-card').forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    playEpisode(ep.video_url, ep.season, ep.episode);
                    saveWatchHistory(); // Simpan riwayat saat ganti episode
                };
                grid.appendChild(button);
            });
        }

        function playEpisode(videoUrl, season, episodeNum) {
            const container = document.getElementById('playerContainer');
            const oldContent = container.querySelector('iframe, video');
            if (oldContent) container.removeChild(oldContent);

            let newElement;
            if (videoUrl.includes('youtube.com')) {
                newElement = document.createElement('iframe');
                newElement.src = videoUrl.replace('watch?v=', 'embed/') + '?enablejsapi=1';
                newElement.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
            } else if (videoUrl.includes('vimeo.com')) {
                const match = videoUrl.match(/vimeo\.com\/(\d+)/);
                if (match) {
                    newElement = document.createElement('iframe');
                    newElement.src = `https://player.vimeo.com/video/${match[1]}?api=1`;
                }
            } else {
                newElement = document.createElement('video');
                newElement.controls = true;
                newElement.controlsList = 'nodownload';
                const source = document.createElement('source');
                source.src = videoUrl;
                source.type = 'video/mp4';
                newElement.appendChild(source);
            }

            if (newElement) {
                newElement.setAttribute('frameborder', '0');
                newElement.setAttribute('allowfullscreen', '');
                container.appendChild(newElement);
            }

            switchTab('nonton');
            saveWatchHistory(); // Simpan riwayat saat mainkan episode
        }

        function saveWatchHistory() {
            fetch(`watch_series.php?id=${seriesId}`)
                .then(() => console.log("Riwayat disimpan"))
                .catch(err => console.error("Gagal simpan riwayat:", err));
        }

        function watchNow() {
            saveWatchHistory();
            switchTab('nonton');
        }

        // Inisialisasi
        document.addEventListener('DOMContentLoaded', function () {
            const initialSeason = document.getElementById('season-select').value;
            showEpisodes(initialSeason);

            // Simpan riwayat saat video dimainkan (opsional)
            const video = document.getElementById('filmVideo');
            if (video && video.tagName === 'VIDEO') {
                video.addEventListener('play', saveWatchHistory);
            }
        });
    </script>
</body>
</html>