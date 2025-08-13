<!-- Katalog Film -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 data-aos="fade-up" class="text-3xl md:text-4xl font-extrabold text-center text-[#FFD700] mb-10">Katalog Film</h2>

        <?php
        $result = $conn->query("SELECT * FROM movies ORDER BY title ASC");

        if ($result->num_rows == 0):
        ?>
            <p class="text-center text-gray-400">Belum ada film tersedia.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <?php while ($movie = $result->fetch_assoc()): ?>
                    <div data-aos="fade-up" class="bg-gray-900 glass rounded-xl overflow-hidden shadow-lg card-hover transition-all duration-300">
                        <img src="<?= htmlspecialchars($movie['image']) ?>" 
                             alt="<?= htmlspecialchars($movie['title']) ?>" 
                             class="w-full h-72 object-cover object-center hover:scale-110 transition-transform duration-500"
                             onerror="this.src='https://via.placeholder.com/300x450?text=No+Image'">
                        <div class="p-5">
                            <h5 class="text-xl font-semibold text-[#FFD700]"><?= htmlspecialchars($movie['title']) ?></h5>
                            <p class="text-gray-300 mt-2 line-clamp-3"><?= htmlspecialchars($movie['description']) ?></p>
                            <a href="movie-detail.php?id=<?= $movie['id'] ?>" class="btn-gold inline-block mt-4 w-full text-center">
                                <i class="bi bi-play-circle me-2"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
