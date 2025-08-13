<!-- Hero Cinematic Showcase -->
<section class="py-32 px-6 text-center relative overflow-hidden">
    <!-- Background Video (Opsional) atau Image -->
     <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1626814026160-2237a95fc5a2?ixlib=rb-4.0.3')] bg-cover bg-center opacity-10"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
    <!-- <div class="container mx-auto px-4 relative">
        <img 
            src="https://images.unsplash.com/photo-1626814026160-2237a95fc5a2?ixlib=rb-4.0.3&auto=format&fit=crop&q=80" 
            alt="Cinematic Background" 
            class="w-full h-full object-cover scale-110"
            style="filter: brightness(0.7) blur(2px); transform: scale(1.1); transition: transform 0.6s ease;"
        >
    </div> -->

    <!-- Konten Utama -->
<div class="container mx-auto relative z-10">
    <!-- Logo & Brand -->
    <div class="flex justify-center mb-6">
        <div class="flex items-center space-x-3">
            <i class="bi bi-film text-4xl text-yellow-400 drop-shadow-lg"><?= SITE_NAME ?></i>
            <span class="text-3xl md:text-4xl font-bold gradient-text"></span>
        </div>
    </div>
    <!-- Garis Pemisah Elegan -->
    <div class="flex justify-center my-8">
        <div class="w-20 h-0.5 bg-gradient-to-r from-transparent via-yellow-400 to-transparent"></div>
    </div>

    <!-- Subtext Minimalis -->
    <p class="text-sm md:text-base text-gray-400 mt-6 tracking-wider uppercase font-medium">
        Curated Collections • 4K Ultra HD • Ad-Free Streaming
    </p>
</div>

    <!-- Efek Partikel Halus (Opsional - CSS Only) -->
    <div class="absolute inset-0 pointer-events-none opacity-30">
        <div class="absolute top-1/4 left-10 w-1 h-1 bg-yellow-400 rounded-full animate-pulse delay-100"></div>
        <div class="absolute top-3/4 right-20 w-0.5 h-0.5 bg-yellow-300 rounded-full animate-ping delay-300"></div>
        <div class="absolute bottom-1/3 left-1/3 w-0.5 h-0.5 bg-amber-400 rounded-full animate-pulse delay-500"></div>
    </div>
</section>