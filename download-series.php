<!-- Fitur: Unduh untuk Nonton Offline -->
<section class="py-12 px-6" data-aos="fade-up" data-aos-delay="600">
    <div class="container mx-auto max-w-4xl">
        <div class="glass rounded-2xl p-8 text-center border border-yellow-400/30 relative overflow-hidden group" data-aos="zoom-in" data-aos-delay="200">
            <!-- Background Gradient Animasi -->
            <div class="absolute inset-0 bg-gradient-to-r from-yellow-400/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

            <!-- Ikon Utama -->
            <div class="relative z-10 mb-4 transform transition-transform duration-500 group-hover:scale-110">
                <i class="bi bi-cloud-download text-5xl text-yellow-400 animate-pulse"></i>
            </div>

            <!-- Judul -->
            <h3 class="text-2xl font-bold gradient-text mb-4 opacity-0 animate-fade-in-up" style="animation-delay: 0.2s;">
                Unduh untuk Nonton Offline
            </h3>

            <!-- Deskripsi -->
            <p class="text-gray-300 mb-6 opacity-0 animate-fade-in-up" style="animation-delay: 0.4s;">
                Simpan episode ini dan tonton kapan saja, bahkan tanpa koneksi internet.
            </p>

            <!-- Konten Utama -->
            <div class="opacity-0 animate-fade-in-up space-y-4" style="animation-delay: 0.6s;">
                <?php 
                // Validasi data series dan episode
                $canDownload = false;
                $downloadUrl = '';
                $fileName = '';
                
                if (isset($current_episode) && !empty($current_episode)) {
                    if (!empty($current_episode['video_url'])) {
                        // Cek apakah URL video bukan dari YouTube atau Vimeo
                        $isExternalVideo = (strpos($current_episode['video_url'], 'youtube') !== false || 
                                          strpos($current_episode['video_url'], 'vimeo') !== false);
                        
                        if (!$isExternalVideo) {
                            $canDownload = true;
                            $downloadUrl = htmlspecialchars($current_episode['video_url']);
                            
                            // Buat nama file yang aman
                            $seriesTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $series['title'] ?? 'Series');
                            $fileName = "{$seriesTitle}_S" . str_pad($current_episode['season'] ?? 1, 2, '0', STR_PAD_LEFT) . 
                                       "E" . str_pad($current_episode['episode'] ?? 1, 2, '0', STR_PAD_LEFT) . ".mp4";
                        }
                    }
                }
                ?>
                
                <?php if ($canDownload): ?>
                    <a 
                        href="<?= $downloadUrl ?>" 
                        download="<?= $fileName ?>"
                        class="btn-gold text-lg py-3 px-8 inline-flex items-center gap-3 transition-all transform hover:scale-105 group/btn"
                        onmousedown="trackDownload('<?= $series['title'] ?? 'Unknown' ?>', 'S<?= $current_episode['season'] ?? 1 ?>E<?= $current_episode['episode'] ?? 1 ?>')">
                        <i class="bi bi-download group-hover/btn:rotate-12 transition-transform duration-300"></i> 
                        <span>Unduh Episode</span>
                    </a>
                    <p class="text-xs text-gray-400 mt-3">
                        Format: MP4 | Ukuran file tergantung episode
                    </p>
                <?php else: ?>
                    <?php if (isset($current_episode) && !empty($current_episode['video_url'])): ?>
                        <p class="text-yellow-400">Fitur download tidak tersedia untuk video dari YouTube/Vimeo.</p>
                    <?php else: ?>
                        <p class="text-gray-400">Episode belum tersedia untuk diunduh.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
// Fungsi untuk melacak download (opsional)
function trackDownload(seriesTitle, episode) {
    console.log(`Download started: ${seriesTitle} - ${episode}`);
    // Bisa ditambahkan tracking ke analytics atau database
}

// Pastikan animasi fade-in berjalan dengan benar
document.addEventListener('DOMContentLoaded', function() {
    const animateElements = document.querySelectorAll('.animate-fade-in-up');
    animateElements.forEach(el => {
        el.style.animation = 'fade-in-up 0.6s ease-out forwards';
    });
});
</script>

<style>
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