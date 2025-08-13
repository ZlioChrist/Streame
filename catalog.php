<?php
// catalog.php - Katalog Gabungan Movie & Series (VIP Only)
session_start();
include 'config.php';

// Middleware: Cek apakah pengguna adalah VIP
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_vip'] || new DateTime($_SESSION['user']['expires_at']) < new DateTime()) {
    header("Location: pricing.php");
    exit;
}

$user = $_SESSION['user'];

// Tipe konten: movie atau series
$type = isset($_GET['type']) && $_GET['type'] === 'series' ? 'series' : 'movie';

// --- Ambil Data Umum ---
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

$regions_result = $conn->query("SELECT * FROM regions ORDER BY name ASC");
$regions = $regions_result->fetch_all(MYSQLI_ASSOC);

$years_result = $conn->query("SELECT DISTINCT year FROM (
    SELECT year FROM movies WHERE year IS NOT NULL
    UNION
    SELECT year FROM series WHERE year IS NOT NULL
) AS combined_years ORDER BY year DESC");
$years = array_column($years_result->fetch_all(MYSQLI_ASSOC), 'year');

// --- Ambil Parameter Filter ---
$selected_category = isset($_GET['category']) ? intval($_GET['category']) : null;
$selected_region = isset($_GET['region']) ? intval($_GET['region']) : null;
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$popular = isset($_GET['popular']) ? $_GET['popular'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : '';

// --- Tentukan Tabel & Relasi Berdasarkan Tipe ---
$table = $type === 'series' ? 'series' : 'movies';
$cat_table = $type === 'series' ? 'series_categories' : 'movie_categories';
$region_table = $type === 'series' ? 'series_regions' : 'movie_regions';

// --- Bangun Query Dinamis ---
$query = "SELECT DISTINCT $table.* FROM $table";
$params = [];
$types = '';

// Filter: Kategori
if ($selected_category) {
    $query .= " INNER JOIN $cat_table cc ON $table.id = cc." . ($type === 'series' ? 'series_id' : 'movie_id') . " WHERE cc.category_id = ?";
    $params[] = $selected_category;
    $types .= 'i';
}

// Filter: Daerah
if ($selected_region) {
    if (!str_contains($query, 'WHERE')) {
        $query .= " INNER JOIN $region_table rr ON $table.id = rr." . ($type === 'series' ? 'series_id' : 'movie_id') . " WHERE rr.region_id = ?";
    } else {
        $query .= " AND $table.id IN (SELECT " . ($type === 'series' ? 'series_id' : 'movie_id') . " FROM $region_table WHERE region_id = ?)";
    }
    $params[] = $selected_region;
    $types .= 'i';
}

// Tambahkan WHERE jika belum ada
if (!str_contains($query, 'WHERE') && (empty($params) || $selected_category)) {
    $query .= " WHERE 1=1";
}

// Filter: Pencarian
if (!empty($search)) {
    $query .= " AND $table.title LIKE ?";
    $params[] = "%{$search}%";
    $types .= 's';
}

// Filter: Tahun
if (!empty($selected_year)) {
    $query .= " AND $table.year = ?";
    $params[] = $selected_year;
    $types .= 'i';
}

// Urutan
switch (true) {
    case $popular === 'high':
        $query .= " ORDER BY rating DESC";
        break;
    case $popular === 'low':
        $query .= " ORDER BY rating ASC";
        break;
    case $order === 'az':
        $query .= " ORDER BY title ASC";
        break;
    case $order === 'za':
        $query .= " ORDER BY title DESC";
        break;
    default:
        $query .= " ORDER BY year DESC, title ASC";
}

// Eksekusi Query
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare gagal: " . $conn->error);
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Katalog <?= ucfirst($type) ?> - StreamFlix</title>

    <!-- Tailwind CSS -->
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
        .tab-nav {
            @apply flex justify-center gap-8 mb-10 pb-2 border-b border-gray-700 overflow-x-auto;
        }
        .tab-link {
            @apply text-sm font-medium px-1 pb-2 transition-all duration-300;
        }
        .tab-link.active {
            @apply text-yellow-400 font-semibold border-b-2 border-yellow-400;
        }
        .tab-link.inactive {
            @apply text-gray-400 hover:text-yellow-400;
        }
        .filter-btn {
            @apply px-4 py-2 rounded-full transition-all duration-300 shadow-sm hover:shadow-md cursor-pointer select-none text-sm font-medium;
        }
        .filter-btn.active {
            @apply bg-gradient-to-r from-yellow-400 to-yellow-500 text-black font-semibold;
        }
        .filter-btn.inactive {
            @apply bg-transparent text-gray-300 hover:bg-yellow-400/20;
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #searchBox {
            animation: fadeInDown 0.3s ease-out;
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .hover-glow:hover {
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
            transform: translateY(-6px);
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>
    <br><br><br>
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-2 py-10">
  <!-- Navigation Tabs -->
<nav class="flex justify-center gap-6 sm:gap-8 mb-10 relative pb-3">
    <!-- Movie Tab -->
    <a 
        href="?<?= http_build_query(array_filter($_GET, function($key) {
            return $key !== 'type';
        }, ARRAY_FILTER_USE_KEY)) ?>"
        class="
            flex items-center gap-2 text-base sm:text-lg font-medium 
            relative px-3 py-1.5 transition-all duration-300
            <?= !isset($_GET['type']) || $_GET['type'] === 'movie' 
                ? 'text-yellow-400' 
                : 'text-gray-400 hover:text-yellow-300' 
            ?>
        "
    >
        <i class="bi bi-film text-lg sm:text-xl"></i>
        <span>Movies</span>
        <?php if (!isset($_GET['type']) || $_GET['type'] === 'movie'): ?>
            <span class="absolute -bottom-1 left-0 w-full h-0.5 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full scale-x-100 transition-transform duration-300"></span>
        <?php else: ?>
            <span class="absolute -bottom-1 left-0 w-full h-0.5 bg-transparent"></span>
        <?php endif; ?>
    </a>

    <!-- TV Series Tab -->
    <a 
        href="?<?= http_build_query(array_merge($_GET, ['type' => 'series'])) ?>"
        class="
            flex items-center gap-2 text-base sm:text-lg font-medium 
            relative px-3 py-1.5 transition-all duration-300
            <?= isset($_GET['type']) && $_GET['type'] === 'series' 
                ? 'text-yellow-400' 
                : 'text-gray-400 hover:text-yellow-300' 
            ?>
        "
    >
        <i class="bi bi-tv text-lg sm:text-xl"></i>
        <span>TV Series</span>
        <?php if (isset($_GET['type']) && $_GET['type'] === 'series'): ?>
            <span class="absolute -bottom-1 left-0 w-full h-0.5 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full scale-x-100 transition-transform duration-300"></span>
        <?php else: ?>
            <span class="absolute -bottom-1 left-0 w-full h-0.5 bg-transparent"></span>
        <?php endif; ?>
    </a>
</nav>
         <!-- Filters -->
<div class="mb-10 space-y-6">
    <!-- Kategori -->
    <div class="flex flex-wrap justify-center gap-2">
        <a href="?<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'category', ARRAY_FILTER_USE_KEY)) ?>"
           class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                  <?= is_null($selected_category) ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
            All Genres
        </a>
        <?php foreach ($categories as $cat): 
            $url_params = $_GET;
            $url_params['category'] = $cat['id'];
            $url = http_build_query($url_params);
        ?>
            <a href="?<?= $url ?>"
               class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                      <?= ($selected_category == $cat['id']) ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Daerah -->
    <div class="flex flex-wrap justify-center gap-2">
        <a href="?<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'region', ARRAY_FILTER_USE_KEY)) ?>"
           class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                  <?= is_null($selected_region) ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
            All Regions
        </a>
        <?php foreach ($regions as $region): 
            $url_params = $_GET;
            $url_params['region'] = $region['id'];
            $url = http_build_query($url_params);
        ?>
            <a href="?<?= $url ?>"
               class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                      <?= ($selected_region == $region['id']) ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
                <?= htmlspecialchars($region['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Tahun -->
    <div class="flex flex-wrap justify-center gap-2">
        <a href="?<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'year', ARRAY_FILTER_USE_KEY)) ?>"
           class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                  <?= empty($selected_year) ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
            All Years
        </a>
        <?php foreach ($years as $year): 
            $url_params = $_GET;
            $url_params['year'] = $year;
            $url = http_build_query($url_params);
        ?>
            <a href="?<?= $url ?>"
               class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                      <?= ($selected_year == $year) ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
                <?= htmlspecialchars($year) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Sort -->
    <div class="flex flex-wrap justify-center gap-2">
        <a href="?<?= http_build_query(array_filter($_GET, fn($k) => !in_array($k, ['popular', 'order']), ARRAY_FILTER_USE_KEY)) ?>"
           class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                  <?= empty($popular) && empty($order) ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
            Terbaru
        </a>
        <a href="?<?= http_build_query(array_merge($_GET, ['popular' => 'high'])) ?>"
           class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                  <?= $popular === 'high' ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
            Rating Tinggi
        </a>
        <a href="?<?= http_build_query(array_merge($_GET, ['order' => 'az'])) ?>"
           class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                  <?= $order === 'az' ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
            A → Z
        </a>
        <a href="?<?= http_build_query(array_merge($_GET, ['order' => 'za'])) ?>"
           class="filter-btn px-4 py-2 rounded-full text-sm font-medium transition-all duration-300
                  <?= $order === 'za' ? 'bg-yellow-400 text-black' : 'bg-gray-800 text-gray-300 hover:text-yellow-400' ?>">
            Z → A
        </a>
    </div>
</div>

        <!-- Header & Search -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div class="text-center md:text-left">
                <h3 class="text-3xl font-bold gradient-text">
                    <?= $search ? "Pencarian: \"$search\"" : "Daftar " . ucfirst($type) ?>
                </h3>
                <p class="text-gray-400 mt-2"><?= count($items) ?> <?= $type ?> ditemukan</p>
            </div>
            <button id="toggleSearchBtn" class="btn-gold hover-glow flex items-center gap-2 px-4 py-2 rounded-full text-sm">
                <i class="bi bi-search"></i> Cari <?= ucfirst($type) ?>
            </button>
        </div>

        <!-- Search Box -->
        <div id="searchBox" class="max-w-2xl mx-auto glass p-6 rounded-2xl shadow-2xl hidden mb-10 border border-yellow-400/20">
            <form method="get" class="relative">
                <?php foreach ($_GET as $key => $value): ?>
                    <?php if ($key === 'search') continue; ?>
                    <?php if (is_array($value)): ?>
                        <?php foreach ($value as $v): ?>
                            <input type="hidden" name="<?= htmlspecialchars($key) ?>[]" value="<?= htmlspecialchars($v) ?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                <input
                    type="text"
                    name="search"
                    placeholder="Cari <?= $type ?>, genre, atau aktor..."
                    value="<?= htmlspecialchars($search ?? '') ?>"
                    class="w-full p-4 pl-12 pr-32 rounded-xl bg-gray-800 text-white placeholder:text-gray-400 border border-transparent focus:border-yellow-400 outline-none focus:ring-2 focus:ring-yellow-400/30"
                    autofocus
                />
                <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                <button
                    type="submit"
                    class="absolute right-3 top-1/2 -translate-y-1/2 px-5 py-2 bg-yellow-400 hover:bg-yellow-500 text-black rounded-full font-medium transition-all duration-300 shadow">
                    Cari
                </button>
            </form>
            <?php if (!empty($search)): ?>
                <a href="?<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'search', ARRAY_FILTER_USE_KEY)) ?>"
                   class="mt-3 block text-center text-sm text-gray-400 hover:text-yellow-400 transition duration-300">
                   Batal Cari &laquo; <strong><?= htmlspecialchars($search) ?></strong>
                </a>
            <?php endif; ?>
        </div>

        <!-- Daftar Konten -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 justify-items-center">
            <?php if (count($items) === 0): ?>
                <div class="col-span-full text-center py-16">
                    <i class="bi bi-collection-play text-6xl text-gray-600 mb-4"></i>
                    <p class="text-gray-400 text-lg">Tidak ada <?= $type ?> yang ditemukan.</p>
                    <a href="catalog.php?type=<?= $type ?>" class="mt-4 inline-block text-yellow-400 hover:underline">Kembali ke semua <?= $type ?></a>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div data-aos="fade-up" class="bg-gray-800 glass card-hover rounded-xl overflow-hidden shadow-xl group w-full max-w-xs flex flex-col transition-all duration-500 hover-glow">
                        <img
                            src="<?= htmlspecialchars($item['image']) ?>"
                            alt="<?= htmlspecialchars($item['title']) ?>"
                            onerror="this.onerror=null; this.src='https://via.placeholder.com/300x450?text=No+Image';"
                            class="w-full h-64 object-cover transition-transform duration-700 group-hover:scale-110"
                        />
                        <div class="p-5 flex-grow">
                            <h5 class="text-xl font-bold text-[#FFFDD0]"><?= htmlspecialchars($item['title']) ?></h5>
                            <p class="text-sm text-yellow-400 mt-1"><?= htmlspecialchars($item['year']) ?> • ⭐ <?= number_format($item['rating'] ?? 0, 1) ?></p>
                            <p class="text-gray-300 mt-2 line-clamp-2"><?= htmlspecialchars($item['description']) ?></p>
                        </div>
                        <div class="p-5 pt-0">
                            <a href="<?= $type === 'movie' ? 'movie-detail.php' : 'series-detail.php' ?>?id=<?= $item['id'] ?>" class="btn-gold">
                                <i class="bi bi-play-circle me-2"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

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

        // Toggle Search Box
        const toggleBtn = document.getElementById('toggleSearchBtn');
        const searchBox = document.getElementById('searchBox');
        toggleBtn.addEventListener('click', () => {
            searchBox.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!toggleBtn.contains(e.target) && !searchBox.contains(e.target)) {
                searchBox.classList.add('hidden');
            }
        });
    </script>
</body>
</html>