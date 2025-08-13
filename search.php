<?php
session_start();
include 'config.php';

// Middleware: cek apakah pengguna adalah VIP
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_vip'] || new DateTime($_SESSION['user']['expires_at']) < new DateTime()) {
    header("Location: pricing.php");
    exit;
}

$user = $_SESSION['user'];

// Ambil parameter pencarian dan filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_categories = isset($_GET['categories']) ? array_map('intval', $_GET['categories']) : [];
$year = isset($_GET['year']) ? intval($_GET['year']) : '';
$popular = isset($_GET['popular']) ? $_GET['popular'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : '';

// Bangun query dinamis
$query = "SELECT DISTINCT m.* FROM movies m";

$params = [];

// Filter berdasarkan kategori
if (!empty($selected_categories)) {
    $query .= " JOIN movie_categories mc ON m.id = mc.movie_id WHERE mc.category_id IN (" . implode(',', $selected_categories) . ")";
    $query .= " GROUP BY m.id HAVING COUNT(DISTINCT mc.category_id) = " . count($selected_categories);
} else {
    $query .= " WHERE 1=1";
}

// Filter pencarian
if (!empty($search)) {
    $query .= " AND m.title LIKE ?";
    $params[] = "%{$search}%";
}

// Filter tahun
if (!empty($year)) {
    $query .= " AND m.year = ?";
    $params[] = $year;
}

// Urutan film
if ($popular === 'high') {
    $query .= " ORDER BY m.rating DESC";
} elseif ($popular === 'low') {
    $query .= " ORDER BY m.rating ASC";
} elseif ($order === 'az') {
    $query .= " ORDER BY m.title ASC";
} elseif ($order === 'za') {
    $query .= " ORDER BY m.title DESC";
} else {
    $query .= " ORDER BY m.year DESC";
}

// Eksekusi query
$stmt = $conn->prepare($query);
if (!$stmt) die("Prepare gagal: " . $conn->error);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...array_values($params)); // Hindari string keys
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hasil Pencarian - StreamFlix</title>

    <!-- Tailwind CSS -->
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
            background: rgba(31, 41, 55, 0.8);
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.2);
        }

        .btn-gold {
            @apply px-4 py-2 rounded-full bg-[#FFD700] hover:bg-yellow-500 text-black font-bold transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg relative overflow-hidden inline-block w-full text-center;
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
            transform: translate(-50%, -50%) scale(2);
            opacity: 0.3;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen">

<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>
<div class="mt-16"></div> <!-- Jarak navbar -->

<div class="container mx-auto px-4">
    <div class="mb-8 space-y-6">

        <!-- Form Pencarian -->
        <div class="flex flex-col gap-4 p-6 bg-gray-800 glass rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold">Cari Film</h2>
            <form method="get" class="relative group">
                <!-- Input Pencarian -->
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Masukkan judul film..." 
                    value="<?= htmlspecialchars($search ?? '') ?>" 
                    class="w-full p-3 pl-10 pr-28 rounded-lg bg-gray-700 text-white placeholder:text-gray-400 border border-transparent focus:border-[#FFD700] outline-none transition-all duration-300"
                    autofocus
                >
                <!-- Icon Search -->
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>

                <!-- Tombol Submit -->
                <button 
                    type="submit" 
                    class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-1.5 bg-gray-700 hover:bg-gray-600 text-white rounded-full text-sm transition-all duration-300 shadow-sm hover:shadow-md">
                    Cari
                </button>
            </form>

            <!-- Tombol Reset -->
            <?php if (!empty($search)): ?>
                <a href="<?= http_build_query(array_filter($_GET, function($key) { return $key !== 'search'; }, ARRAY_FILTER_USE_KEY)) ?>"
                   class="mt-2 text-sm text-gray-400 hover:text-white underline transition-colors duration-300 self-start">
                   Batal Cari / Kembali ke Semua Film
                </a>
            <?php endif; ?>
        </div>

        <!-- Filter Tambahan (Opsional) -->
        <div class="space-y-4">
            <!-- Filter Kategori -->
            <div class="flex flex-wrap gap-2">
                <span class="text-sm font-medium text-gray-400">Kategori:</span>
                <?php
                // Ambil semua kategori dari database
                $categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                $categories = [];
                while ($row = $categories_result->fetch_assoc()) {
                    $categories[] = $row;
                }
                foreach ($categories as $cat): 
                    // Toggle logic
                    $new_cats = $selected_categories;
                    $key = array_search($cat['id'], $new_cats);

                    if ($key !== false) {
                        unset($new_cats[$key]);
                    } else {
                        $new_cats[] = $cat['id'];
                    }

                    // Build URL
                    $url_params = $_GET;

                    if (!empty($new_cats)) {
                        $url_params['categories'] = array_values($new_cats);
                    } else {
                        unset($url_params['categories']);
                    }

                    $url = http_build_query($url_params);
                ?>
                    <a 
                        href="<?= $url ?: 'search.php' ?>" 
                        class="<?= in_array($cat['id'], $selected_categories) ? 'bg-[#FFD700] text-black' : 'bg-gray-800 hover:bg-gray-700 text-white' ?> 
                        px-4 py-2 rounded-full transition-all duration-300 shadow-sm hover:shadow-md cursor-pointer select-none">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Filter Tahun -->
            <?php
            $years_result = $conn->query("SELECT DISTINCT year FROM movies ORDER BY year DESC");
            $years = [];
            while ($row = $years_result->fetch_assoc()) {
                $years[] = $row['year'];
            }
            ?>
            <div class="flex flex-wrap gap-2">
                <span class="text-sm font-medium text-gray-400">Tahun Rilis:</span>
                <?php foreach ($years as $release_year): ?>
                    <a href="<?= http_build_query(array_merge($_GET, ['year' => $release_year])) ?>" 
                       class="<?= $year == $release_year ? 'bg-[#FFD700] text-black' : 'bg-gray-800 hover:bg-gray-700 text-white' ?> 
                       px-4 py-2 rounded-full transition-all duration-300 shadow-sm hover:shadow-md">
                       <?= htmlspecialchars($release_year) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Filter Popularitas -->
            <div class="flex flex-wrap gap-2">
                <span class="text-sm font-medium text-gray-400">Rating:</span>
                <a href="<?= http_build_query(array_merge($_GET, ['popular' => 'high'])) ?>" 
                   class="<?= $popular === 'high' ? 'bg-[#FFD700] text-black' : 'bg-gray-800 hover:bg-gray-700 text-white' ?> 
                   px-4 py-2 rounded-full transition-all duration-300 shadow-sm hover:shadow-md">
                   Populer (Tinggi)
                </a>
                <a href="<?= http_build_query(array_merge($_GET, ['popular' => 'low'])) ?>" 
                   class="<?= $popular === 'low' ? 'bg-[#FFD700] text-black' : 'bg-gray-800 hover:bg-gray-700 text-white' ?> 
                   px-4 py-2 rounded-full transition-all duration-300 shadow-sm hover:shadow-md">
                   Tidak Populer (Rendah)
                </a>
            </div>

            <!-- Urutan Judul -->
            <div class="flex flex-wrap gap-2">
                <span class="text-sm font-medium text-gray-400">Urutan Judul:</span>
                <a href="<?= http_build_query(array_merge($_GET, ['order' => 'az'])) ?>" 
                   class="<?= $order === 'az' ? 'bg-[#FFD700] text-black' : 'bg-gray-800 hover:bg-gray-700 text-white' ?> 
                   px-4 py-2 rounded-full transition-all duration-300 shadow-sm hover:shadow-md">
                   A → Z
                </a>
                <a href="<?= http_build_query(array_merge($_GET, ['order' => 'za'])) ?>" 
                   class="<?= $order === 'za' ? 'bg-[#FFD700] text-black' : 'bg-gray-800 hover:bg-gray-700 text-white' ?> 
                   px-4 py-2 rounded-full transition-all duration-300 shadow-sm hover:shadow-md">
                   Z → A
                </a>
            </div>
        </div>

        <!-- Daftar Hasil Pencarian -->
        <h3 class="text-lg font-semibold mt-6">Hasil Pencarian<?= $search ? ' untuk "' . htmlspecialchars($search) . '"' : '' ?></h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php if (!$result): ?>
                <p class="col-span-full text-center text-red-500 mt-8">Query database gagal.</p>
            <?php elseif ($result->num_rows == 0): ?>
                <p class="col-span-full text-center text-gray-400 mt-8"><?= $search ? 'Belum ada film yang cocok dengan kata kunci "' . htmlspecialchars($search) . '".' : 'Silakan masukkan kata pencarian di atas untuk melanjutkan.' ?></p>
            <?php else: ?>
                <?php while ($movie = $result->fetch_assoc()): ?>
                    <div data-aos="fade-up" class="bg-gray-800 glass card-hover rounded-xl overflow-hidden shadow-lg group flex flex-col h-full transition-all duration-500">
                        <img 
                            src="<?= htmlspecialchars($movie['image']) ?>" 
                            alt="<?= htmlspecialchars($movie['title']) ?>" 
                            onerror="this.onerror=null; this.src='https://via.placeholder.com/300x450?text=No+Image';"
                            class="w-full h-48 object-cover transition-transform duration-700 group-hover:scale-105 group-hover:rotate-1"
                        >
                        <div class="p-5 flex-grow">
                            <h5 class="text-xl font-semibold text-[#FFFDD0]"><?= htmlspecialchars($movie['title']) ?></h5>
                            <p class="text-sm text-gray-400 mt-1"><?= htmlspecialchars($movie['year']) ?> • Rating: <?= htmlspecialchars($movie['rating']) ?: '-' ?></p>
                            <p class="text-gray-300 mt-2 line-clamp-2"><?= htmlspecialchars($movie['description']) ?></p>
                        </div>
                        <div class="p-5 pt-0">
                            <a href="movie-detail.php?id=<?= $movie['id'] ?>" class="btn-gold">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-6 text-gray-500 mt-12">
    &copy; <?= date('Y') ?> StreamFlix | All rights reserved.
</footer>

<!-- JS AOS -->
<script src=" https://unpkg.com/aos @2.3.1/dist/aos.js"></script>
<link href="https://unpkg.com/aos @2.3.1/dist/aos.css" rel="stylesheet">
<script>
    AOS.init({
        duration: 600,
        once: true
    });
</script>