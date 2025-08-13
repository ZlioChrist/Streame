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

// Ambil ID film dari URL (hanya untuk contoh rekomendasi)
$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
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
        transform: translateY(-6px);
    }

    /* Carousel Container */
    #rekomendasiCarousel {
        position: relative;
        overflow: hidden;
    }

    #carouselInner {
        display: flex;
        gap: 1.5rem;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
        scroll-snap-type: x mandatory;
        padding-bottom: 1rem;
    }

    #carouselInner::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }

    /* Gradient Mask (opsional) */
    .gradient-left::before,
    .gradient-right::after {
        content: '';
        position: absolute;
        top: 0;
        width: 60px;
        height: 100%;
        pointer-events: none;
        z-index: 10;
    }
    .gradient-left::before {
        left: 0;
        background: linear-gradient(to right, rgba(0,0,0,0.8), transparent);
    }
    .gradient-right::after {
        right: 0;
        background: linear-gradient(to left, rgba(0,0,0,0.8), transparent);
    }

    /* Responsive: Tablet & Mobile */
    @media (max-width: 640px) {
        .w-56 { width: 140px; }
        .text-3xl { font-size: 1.5rem; }
    }
</style>

<!-- Rekomendasi Film - Carousel Horizontal -->
<section class="py-16 px-6" data-aos="fade-up" data-aos-delay="400">
    <div class="container mx-auto">
        <!-- Judul -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold gradient-text">🎬 Rekomendasi Untuk Anda</h2>
            <div class="flex gap-3">
                <button 
                    onclick="scrollRekomendasi('left')" 
                    aria-label="Geser kiri"
                    class="p-3 rounded-full bg-gray-800 hover:bg-yellow-400 text-yellow-400 hover:text-black transition-all duration-300 shadow-lg hover:shadow-yellow-400/30 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-gray-900"
                >
                    <i class="bi bi-chevron-left text-xl"></i>
                </button>
                <button 
                    onclick="scrollRekomendasi('right')" 
                    aria-label="Geser kanan"
                    class="p-3 rounded-full bg-gray-800 hover:bg-yellow-400 text-yellow-400 hover:text-black transition-all duration-300 shadow-lg hover:shadow-yellow-400/30 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-gray-900"
                >
                    <i class="bi bi-chevron-right text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Carousel Wrapper -->
        <div id="rekomendasiCarousel" class="gradient-left gradient-right">
            <div id="carouselInner" class="flex gap-6">
                <?php
                $stmt = $conn->prepare("SELECT id, title, image, category, year, rating FROM movies WHERE id != ? ORDER BY RAND() LIMIT 8");
                $stmt->bind_param("i", $movie_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0): ?>
                    <p class="text-gray-400 text-center w-full py-8">Tidak ada film lain untuk ditampilkan.</p>
                <?php else: ?>
                    <?php while ($film = $result->fetch_assoc()): ?>
                        <div 
                            class="flex-shrink-0 w-56 snap-start bg-gray-800 glass rounded-xl overflow-hidden shadow-xl card-hover hover-glow transition-all duration-500"
                            data-aos="fade-up"
                        >
                            <!-- Poster -->
                            <div class="relative aspect-[2/3] overflow-hidden group cursor-pointer">
                                <img 
                                    src="<?= htmlspecialchars($film['image'], ENT_QUOTES, 'UTF-8') ?>" 
                                    alt="<?= htmlspecialchars($film['title'], ENT_QUOTES, 'UTF-8') ?>" 
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                >
                                <!-- Genre Badge -->
                                <div class="absolute top-2 left-2 px-2 py-1 bg-yellow-400 text-black text-xs font-bold rounded-full">
                                    <?= htmlspecialchars($film['category'] ?? 'Film', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <!-- Hover Overlay -->
                                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                    <i class="bi bi-play-circle-fill text-4xl text-yellow-400"></i>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-4">
                                <h5 class="text-white font-semibold text-sm line-clamp-2 mb-1">
                                    <?= htmlspecialchars($film['title'], ENT_QUOTES, 'UTF-8') ?>
                                </h5>
                                <p class="text-gray-400 text-xs mb-3">
                                    <?= htmlspecialchars($film['year']) ?> • ⭐ <?= number_format($film['rating'] ?? 0, 1) ?>
                                </p>
                                <a href="movie-detail.php?id=<?= (int)$film['id'] ?>" class="btn-gold text-sm py-2 inline-flex items-center">
                                    <i class="bi bi-eye me-1"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                <?php $stmt->close(); ?>
            </div>
        </div>
    </div>
</section>

<script>
function scrollRekomendasi(direction) {
    const carousel = document.getElementById('carouselInner');
    const cardWidth = 224 + 24; // lebar card + gap (w-56 = 224px, gap-6 = 24px)
    const scrollAmount = direction === 'left' ? -cardWidth : cardWidth;

    carousel.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
    });
}

// Scroll horizontal dengan mouse wheel
document.getElementById('rekomendasiCarousel').addEventListener('wheel', function (e) {
    e.preventDefault();
    this.scrollLeft += e.deltaY * 1.2;
}, { passive: false });

// Tambahkan delay AOS bertahap
document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll('#carouselInner > div');
    cards.forEach((card, i) => {
        card.setAttribute('data-aos-delay', (100 + i * 100));
    });
});
</script>