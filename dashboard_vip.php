<?php
// dashboard_vip.php - Dashboard VIP StreamFlix

// 🔐 Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Ambil data user dari session
$user = $_SESSION['user'];

// ✅ Validasi: pastikan $user adalah array
if (!is_array($user)) {
    error_log("Session user bukan array: " . print_r($user, true));
    session_destroy();
    header("Location: login.php?error=invalid_session");
    exit();
}

// ✅ Validasi kunci penting
$required_keys = ['id', 'name', 'is_vip', 'expires_at'];
foreach ($required_keys as $key) {
    if (!isset($user[$key])) {
        die("Data pengguna tidak lengkap: kunci '$key' tidak ditemukan.");
    }
}

// Ambil data pengguna
$userId = $user['id'];
$username = $user['name'];
$is_vip = $user['is_vip'];
$expires_at = $user['expires_at'];

// URL foto profil
$profile_image = $user['profile_image'] ?? null;
$upload_dir = 'uploads/profiles/';
$profile_url = $profile_image && file_exists($upload_dir . $profile_image)
    ? $upload_dir . $profile_image
    : 'https://ui-avatars.com/api/?name=' . urlencode($username) . '&background=FFD700&color=000000&size=128';

// Cek status VIP aktif
$is_vip_active = $is_vip && (!empty($expires_at) && new DateTime($expires_at) > new DateTime());

// Fungsi bantuan: escape output
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 🔗 Sertakan koneksi database
include 'config.php';

// Fungsi deteksi musim
function getCurrentSeason() {
    $month = date('n');
    if (in_array($month, [12, 1, 2])) return 'winter';
    if (in_array($month, [3, 4, 5])) return 'spring';
    if (in_array($month, [6, 7, 8])) return 'summer';
    return 'autumn';
}

$season = getCurrentSeason();

$seasonNames = [
    'summer' => 'Musim Panas',
    'winter' => 'Musim Dingin',
    'autumn' => 'Musim Gugur',
    'spring' => 'Musim Semi'
];

$seasonBadges = [
    'summer' => 'bg-orange-500',
    'winter' => 'bg-blue-500',
    'autumn' => 'bg-yellow-600',
    'spring' => 'bg-green-500'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard VIP - StreamFlix</title>

    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet" />

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

     <?php include "icon.php"; ?>

    <!-- Custom Styles -->
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
            @apply px-6 py-3 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-black font-bold transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-2xl relative overflow-hidden inline-block text-center;
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
        .hover-glow:hover {
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
            transform: translateY(-6px);
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .gradient-text {
            background: linear-gradient(90deg, #FFD700 0%, #F59E0B 50%, #D97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .season-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">

    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Notifikasi Status VIP -->
    <div class="container mx-auto px-6 py-4">
        <div class="glass p-4 text-sm flex justify-between items-center 
            <?php echo $is_vip_active ? 'bg-green-500/20 text-green-400 border-l-4 border-green-400' : 'bg-red-500/20 text-red-400 border-l-4 border-red-400'; ?>">
            <span>
                <i class="bi bi-shield-check"></i>
                Status VIP: <strong><?php echo $is_vip_active ? 'Aktif' : 'Kadaluarsa'; ?></strong>
                <?php if ($is_vip_active): ?>
                    (Berakhir: <?php echo date('d M Y', strtotime($expires_at)); ?>)
                <?php else: ?>
                    (Silakan perbarui langganan Anda)
                <?php endif; ?>
            </span>
            <?php if (!$is_vip_active): ?>
                <a href="pricing.php" class="btn-gold text-xs py-1 px-3">Perbarui</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="py-16 px-6 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold gradient-text mb-4">
            Halo, <?php echo e($username); ?>! 🎉
        </h1>
        <p class="text-lg text-gray-300">Temukan film terbaik untuk hari ini.</p>
    </section>

    <!-- Film Terpopuler -->
    <section class="py-12 px-6" data-aos="fade-up">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold mb-8 text-center gradient-text">
                <i class="bi bi-easel"></i> Film Terpopuler
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <?php
                $popular_query = "
                    SELECT m.*
                    FROM movies m
                    WHERE m.is_vip = 1 
                      AND m.rating IS NOT NULL
                    ORDER BY m.rating DESC
                    LIMIT 5
                ";
                $result = $conn->query($popular_query);

                if (!$result) {
                    echo "<p class='col-span-5 text-center text-red-400'>Error: " . $conn->error . "</p>";
                } elseif ($result->num_rows === 0) {
                    echo "<p class='col-span-5 text-center text-gray-400'>Belum ada film VIP tersedia.</p>";
                } else {
                    while ($movie = $result->fetch_assoc()) {
                        echo '
                        <a href="movie-detail.php?id=' . e($movie['id']) . '" class="block bg-gray-800 glass rounded-xl overflow-hidden card-hover hover-glow transition-all group">
                            <div class="relative">
                                <img src="' . e($movie['image']) . '" alt="' . e($movie['title']) . '" class="w-full h-48 object-cover transition-transform duration-700 group-hover:scale-110">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-500 flex items-center justify-center">
                                    <i class="bi bi-play-circle text-yellow-400 text-5xl opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100"></i>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-white line-clamp-2">' . e($movie['title']) . '</h3>
                                <div class="flex items-center mt-2 text-yellow-400 text-sm">
                                    <i class="bi bi-star-fill"></i>
                                    <span class="ml-1">' . number_format($movie['rating'] ?? 0, 1) . '</span>
                                </div>
                            </div>
                        </a>';
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Event Musiman -->
    <section class="py-6 px-6">
        <div class="container mx-auto">
            <div class="glass p-6 text-center border-l-4 <?= $seasonBadges[$season] ?> text-white flex flex-col sm:flex-row items-center justify-center gap-4">
                <i class="bi bi-calendar2-event text-2xl"></i>
                <div>
                    <strong class="text-yellow-400">Event Hari Ini:</strong>
                    <span class="ml-2">Selamat datang di <?= $seasonNames[$season] ?>! 🎉</span>
                </div>
                <a href="catalog.php?season=<?= $season ?>" class="btn-gold text-xs py-1 px-4 mt-2 sm:mt-0">
                    Lihat Rekomendasi
                </a>
            </div>
        </div>
    </section>

    <!-- Rekomendasi Berdasarkan Musim -->
    <section class="py-12 px-6" data-aos="fade-up">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold mb-8 text-center gradient-text">
                <i class="bi bi-film me-2"></i>
                Rekomendasi <?= $seasonNames[$season] ?>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <?php
                $seasonGenres = [
                    'summer' => ['Adventure', 'Comedy', 'Romance'],
                    'winter' => ['Drama', 'Family', 'Romance'],
                    'autumn' => ['Thriller', 'Mystery', 'Horror'],
                    'spring' => ['Romance', 'Comedy', 'Feel-good']
                ];

                $genres = "'" . implode("','", array_map([$conn, 'real_escape_string'], $seasonGenres[$season])) . "'";
                $recommendation_query = "
                    SELECT DISTINCT m.*
                    FROM movies m
                    JOIN movie_categories mc ON m.id = mc.movie_id
                    JOIN categories c ON mc.category_id = c.id
                    WHERE c.name IN ($genres)
                      AND m.is_vip = 1
                      AND m.rating IS NOT NULL
                    ORDER BY m.rating DESC
                    LIMIT 5
                ";

                $result = $conn->query($recommendation_query);

                if (!$result) {
                    echo "<p class='col-span-5 text-center text-red-400'>Error: " . $conn->error . "</p>";
                } elseif ($result->num_rows === 0) {
                    echo "<p class='col-span-5 text-center text-gray-400'>Belum ada film untuk musim ini.</p>";
                } else {
                    while ($movie = $result->fetch_assoc()) {
                        echo '
                        <a href="movie-detail.php?id=' . e($movie['id']) . '" class="block bg-gray-800 glass rounded-xl overflow-hidden card-hover hover-glow transition-all group">
                            <div class="relative">
                                <img src="' . e($movie['image']) . '" alt="' . e($movie['title']) . '" class="w-full h-48 object-cover transition-transform duration-700 group-hover:scale-110">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-500 flex items-center justify-center">
                                    <i class="bi bi-play-circle text-yellow-400 text-5xl opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100"></i>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-white line-clamp-2">' . e($movie['title']) . '</h3>
                                <div class="flex items-center mt-2 text-yellow-400 text-sm">
                                    <i class="bi bi-star-fill"></i>
                                    <span class="ml-1">' . number_format($movie['rating'] ?? 0, 1) . '</span>
                                </div>
                            </div>
                        </a>';
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Riwayat Tontonan -->
    <section class="py-12 px-6" data-aos="fade-up" data-aos-delay="200">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold mb-8 text-center gradient-text">
                <i class="bi bi-clock-history me-2"></i>
                Riwayat Tontonan
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-6">
                <?php
                $historyItems = [];

                // Ambil riwayat film
                $stmt = $conn->prepare("
                    SELECT m.*, wh.watched_at, wh.note, 'movie' as content_type
                    FROM movies m
                    JOIN watch_history wh ON m.id = wh.movie_id
                    WHERE wh.user_id = ?
                    GROUP BY m.id
                    ORDER BY wh.watched_at DESC
                    LIMIT 5
                ");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $historyItems[] = $row;
                }
                $stmt->close();

                // Ambil riwayat series
                $stmt = $conn->prepare("
                    SELECT s.*, sh.watched_at, sh.note, 'series' as content_type
                    FROM series s
                    JOIN series_history sh ON s.id = sh.series_id
                    WHERE sh.user_id = ?
                    GROUP BY s.id
                    ORDER BY sh.watched_at DESC
                    LIMIT 5
                ");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $historyItems[] = $row;
                }
                $stmt->close();

                // Gabung dan urutkan berdasarkan waktu
                usort($historyItems, function ($a, $b) {
                    return strtotime($b['watched_at']) - strtotime($a['watched_at']);
                });

                // Tampilkan maksimal 5 item
                $count = 0;
                foreach ($historyItems as $item) {
                    if ($count >= 5) break;

                    $id = $item['id'];
                    $title = $item['title'];
                    $image = $item['cover_image'] ?? $item['image'] ?? 'https://via.placeholder.com/300x450?text=No+Image';
                    $note = $item['note'] ?? '';
                    $contentType = $item['content_type'];
                    $detailPage = $contentType === 'movie' ? 'movie-detail.php' : 'series-detail.php';

                    echo '
                    <div class="bg-gray-800 glass rounded-xl overflow-hidden relative group">
                        <a href="' . $detailPage . '?id=' . e($id) . '" class="block relative">
                            <img src="' . e($image) . '" alt="' . e($title) . '" class="w-full h-48 object-cover" onerror="this.onerror=null; this.src=\'https://via.placeholder.com/300x450?text=No+Image\';">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-500 flex items-center justify-center">
                                <i class="bi bi-' . ($contentType === 'movie' ? 'film' : 'collection-play') . ' text-yellow-400 text-5xl opacity-0 group-hover:opacity-100"></i>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="font-bold text-white line-clamp-2">' . e($title) . '</h3>
                            <p class="text-xs text-gray-400 mt-1">Ditonton ' . date('d M, H:i', strtotime($item['watched_at'])) . '</p>
                            ' . (!empty($note) ? '<p class="text-xs text-yellow-400 mt-1 italic line-clamp-1">"' . e($note) . '"</p>' : '') . '
                        </div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col gap-2">
                            <button onclick="openEditModal(' . $id . ', \'' . addslashes($title) . '\', \'' . addslashes($note) . '\', \'' . $contentType . '\')" class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-full w-8 h-8 flex items-center justify-center shadow-md" title="Edit Catatan">
                                <i class="bi bi-pencil text-xs"></i>
                            </button>
                            <a href="delete_history.php?content_id=' . $id . '&content_type=' . $contentType . '&user_id=' . $userId . '" onclick="return confirm(\'Yakin hapus dari riwayat?\')" class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-full w-8 h-8 flex items-center justify-center shadow-md" title="Hapus">
                                <i class="bi bi-trash text-xs"></i>
                            </a>
                        </div>
                    </div>';
                    $count++;
                }

                if ($count === 0) {
                    echo '<p class="col-span-5 text-center text-gray-400">Belum ada riwayat tontonan.</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Modal Edit Catatan -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-gray-800 glass p-6 rounded-xl max-w-sm w-full mx-4">
            <h3 class="text-lg font-bold text-white mb-4">Edit Catatan</h3>
            <p class="text-sm text-gray-300 mb-3" id="modalMovieTitle"></p>
            <form method="POST" action="update_note.php">
                <input type="hidden" name="content_id" id="modalContentId">
                <input type="hidden" name="content_type" id="modalContentType">
                <textarea name="note" class="w-full bg-gray-700 text-white rounded p-2 text-sm mb-4" placeholder="Tulis catatan tentang konten ini..." rows="3" id="modalNote"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-3 py-1 text-sm bg-gray-600 rounded hover:bg-gray-700">Batal</button>
                    <button type="submit" class="px-3 py-1 text-sm bg-yellow-500 text-black rounded hover:bg-yellow-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, easing: 'ease-out', once: true });
    </script>

    <script>
        function openEditModal(id, title, note, type) {
            document.getElementById('modalContentId').value = id;
            document.getElementById('modalContentType').value = type;
            document.getElementById('modalMovieTitle').textContent = title;
            document.getElementById('modalNote').value = note;
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</body>
</html>