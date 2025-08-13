<?php
// includes/navbar.php
?>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed w-full z-50 bg-black bg-opacity-90 backdrop-blur-md shadow-lg border-b border-[#FFD700] border-opacity-30">
    <div class="container mx-auto flex justify-between items-center px-4 py-3">
        <a href="#" class="text-2xl font-bold text-[#FFD700]">🎬 <?= SITE_NAME ?></a>
        <button id="navToggle" class="md:hidden text-white focus:outline-none">
            <i class="bi bi-list text-2xl"></i>
        </button>
        <ul id="navMenu" class="hidden md:flex space-x-6 text-sm font-medium">
            <?php if (isset($_SESSION['user'])): ?>
               
               
            <?php else: ?>
               
            <?php endif; ?>
        </ul>
    </div>
</nav>