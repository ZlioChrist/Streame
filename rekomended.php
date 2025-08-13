<?php
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

// Ambil ID konten saat ini (untuk menghindari duplikat)
$current_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<style>
    /* Gradient Text */
    .gradient-text {
        background: linear-gradient(90deg, #FFD700, #F59E0B);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Hover Glow */
    .hover-glow:hover {
        box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
        transition: all 0.4s ease;
    }

    /* Line Clamp */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Card Hover Effect */
    .card-hover:hover {
        transform: translateY(-4px);
        transition: all 0.3s ease;
    }

    /* Filter Buttons */
    .filter-btn {
        padding: 0.6rem 1.2rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #374151;
        color: #d1d5db;
    }
    .filter-btn.active {
        background: linear-gradient(90deg, #fbbf24, #f59e0b);
        color: black;
        box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
    }
    .filter-btn:hover:not(.active) {
        background: #4b5563;
        color: white;
    }

    /* Carousel Container */
    #rekomendasiCarousel {
        position: relative;
        overflow: hidden;
    }

    /* Tombol Geser */
    .scroll-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 20;
        width: 40px;
        height: 40px;
        background: rgba(0, 0, 0, 0.7);
        border: 2px solid rgba(255, 215, 0, 0.3);
        color: #fbbf24;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .scroll-btn:hover {
        background: rgba(251, 191, 36, 0.4);
        opacity: 1;
        transform: translateY(-50%) scale(1.1);
    }

    .scroll-btn.left {
        left: 10px;
    }

    .scroll-btn.right {
        right: 10px;
    }

    /* Carousel Inner */
    #carouselInner {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding: 0.5rem 50px;
        scroll-snap-type: x mandatory;
    }

    #carouselInner::-webkit-scrollbar {
        display: none;
    }

    /* Gradien Samping */
    .gradient-mask::before,
    .gradient-mask::after {
        content: '';
        position: absolute;
        top: 0;
        width: 40px;
        height: 100%;
        pointer-events: none;
        z-index: 10;
    }
    .gradient-mask::before {
        left: 0;
        background: linear-gradient(to right, rgba(15, 23, 42, 0.9), transparent);
    }
    .gradient-mask::after {
        right: 0;
        background: linear-gradient(to left, rgba(15, 23, 42, 0.9), transparent);
    }

    /* Card Horizontal */
    .horizontal-card {
        flex-shrink: 0;
        display: flex;
        width: 360px;
        max-width: 100%;
        height: 100px;
        background: rgba(30, 41, 59, 0.8);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 215, 0, 0.1);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    .card-img {
        width: 150px;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .card-img:hover img {
        transform: scale(1.1);
    }

    .card-img::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.7) 20%, transparent);
        pointer-events: none;
    }

    .card-info {
        flex: 1;
        padding: 0.8rem;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .card-info h5 {
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
    }

    .card-info p {
        margin: 0.2rem 0;
        font-size: 0.8rem;
        color: #d1d5db;
    }

    .badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
        border-radius: 9999px;
        font-weight: 600;
    }

    .badge-movie {
        background: #3B82F6;
        color: white;
    }

    .badge-series {
        background: #10B981;
        color: white;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .horizontal-card {
            width: 300px;
        }
        .card-img {
            width: 120px;
        }
        .card-info {
            padding: 0.6rem;
        }
        .filter-btn {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
        }
    }
</style>

<!-- Rekomendasi Acak dengan Kategori dari Tabel `categories` -->
<section class="py-16 px-6" data-aos="fade-up" data-aos-delay="400">
    <div class="container mx-auto">
        <!-- Judul & Filter -->
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
            <h2 class="text-3xl font-bold gradient-text">🎬 Rekomendasi untuk Anda</h2>

            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-3 justify-center">
                <button class="filter-btn active" onclick="filterContent('all')">Semua</button>
                <button class="filter-btn inactive" onclick="filterContent('movie')">Film</button>
                <button class="filter-btn inactive" onclick="filterContent('series')">Series</button>
            </div>
        </div>

        <!-- Carousel Wrapper -->
        <div id="rekomendasiCarousel" class="gradient-mask relative">
            <!-- Tombol Geser -->
            <button class="scroll-btn left" onclick="scrollHorizontal('left')">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button class="scroll-btn right" onclick="scrollHorizontal('right')">
                <i class="bi bi-chevron-right"></i>
            </button>

            <!-- Carousel Inner -->
            <div id="carouselInner" class="flex gap-4">
                <?php
                if (!isset($conn)) {
                    echo '<p class="text-red-400 text-center w-full py-8">Database tidak terhubung.</p>';
                } else {
                    // Query gabungan: ambil movie & series + nama kategori dari tabel categories
                    $sql = "
                        SELECT 
                            m.id,
                            m.title,
                            m.image,
                            m.year,
                            m.rating,
                            GROUP_CONCAT(c.name) as category_names,
                            'movie' as type
                        FROM movies m
                        LEFT JOIN movie_categories mc ON m.id = mc.movie_id
                        LEFT JOIN categories c ON mc.category_id = c.id
                        WHERE m.id != ?
                        GROUP BY m.id
                        UNION ALL
                        SELECT 
                            s.id,
                            s.title,
                            s.image,
                            s.year,
                            s.rating,
                            GROUP_CONCAT(c.name) as category_names,
                            'series' as type
                        FROM series s
                        LEFT JOIN series_categories sc ON s.id = sc.series_id
                        LEFT JOIN categories c ON sc.category_id = c.id
                        WHERE s.id != ?
                        GROUP BY s.id
                        ORDER BY RAND()
                        LIMIT 12
                    ";

                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        echo '<p class="text-red-400 text-center w-full py-8">Gagal mempersiapkan query: ' . $conn->error . '</p>';
                    } else {
                        $stmt->bind_param("ii", $current_id, $current_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows === 0) {
                            echo '<p class="text-gray-400 text-center w-full py-8">Tidak ada konten tersedia.</p>';
                        } else {
                            while ($item = $result->fetch_assoc()):
                                $detail_url = $item['type'] === 'movie'
                                    ? "movie-detail.php?id=" . (int)$item['id']
                                    : "series-detail.php?id=" . (int)$item['id'];

                                $badge_class = $item['type'] === 'movie' ? 'badge-movie' : 'badge-series';
                                $badge_text = ucfirst($item['type']);

                                // Ambil 1-2 kategori pertama
                                $categories = !empty($item['category_names']) 
                                    ? array_slice(explode(',', $item['category_names']), 0, 2) 
                                    : ['Unknown'];
                                $category_display = implode(', ', $categories);
                                ?>
                                <a
                                    href="<?= htmlspecialchars($detail_url, ENT_QUOTES, 'UTF-8') ?>"
                                    class="horizontal-card card-hover hover-glow"
                                    data-type="<?= htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-aos="fade-up"
                                >
                                    <!-- Thumbnail -->
                                    <div class="card-img">
                                        <img 
                                            src="<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8') ?>" 
                                            alt="<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>" 
                                            onerror="this.onerror=null; this.src='https://via.placeholder.com/150x100?text=No+Image';"
                                        >
                                        <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                                            <i class="bi bi-play-circle-fill text-3xl text-yellow-400"></i>
                                        </div>
                                    </div>

                                    <!-- Info Konten -->
                                    <div class="card-info">
                                        <h5 class="text-white font-semibold line-clamp-2">
                                            <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>
                                        </h5>
                                        <p class="text-gray-300">
                                            <?= htmlspecialchars($item['year']) ?> • 
                                            <span class="badge <?= $badge_class ?>"><?= $badge_text ?></span>
                                        </p>
                                        <p class="text-gray-400 text-xs"><?= htmlspecialchars($category_display) ?></p>
                                        <div class="flex items-center gap-1 mt-1">
                                            <i class="bi bi-star-fill text-yellow-400 text-xs"></i>
                                            <span class="text-yellow-400 text-xs"> <?= number_format($item['rating'] ?? 0, 1) ?></span>
                                        </div>
                                    </div>
                                </a>
                                <?php
                            endwhile;
                        }
                        $stmt->close();
                    }
                }
                ?>
            </div>
        </div>
    </div>
</section>

<script>
// Filter konten berdasarkan tipe
function filterContent(type) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        const text = btn.textContent.trim().toLowerCase();
        if (
            (type === 'all' && text === 'semua') ||
            (type === 'movie' && (text === 'film' || text === 'movie')) ||
            (type === 'series' && text === 'series')
        ) {
            btn.classList.remove('inactive');
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
            btn.classList.add('inactive');
        }
    });

    document.querySelectorAll('.horizontal-card').forEach(card => {
        const cardType = card.getAttribute('data-type');
        card.style.display = (type === 'all' || cardType === type) ? 'flex' : 'none';
    });
}

// Scroll horizontal
function scrollHorizontal(direction) {
    const carousel = document.getElementById('carouselInner');
    const scrollAmount = direction === 'left' ? -300 : 300;
    carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
}

// Scroll dengan mouse wheel
document.getElementById('rekomendasiCarousel')?.addEventListener('wheel', function (e) {
    e.preventDefault();
    this.scrollLeft += e.deltaY * 1.5;
}, { passive: false });

// AOS Delay Bertahap
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.horizontal-card').forEach((el, i) => {
        el.setAttribute('data-aos-delay', (100 + i * 100));
    });
});
</script>