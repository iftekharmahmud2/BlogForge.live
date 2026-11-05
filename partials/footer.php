</main>
<footer class="border-t <?= ($isHome ?? false) ? 'border-neutral-800/80 bg-neutral-950 text-neutral-400 py-8 mt-0' : 'border-[#e9e5df] bg-[#fffdfa] text-gray-500 py-10 mt-16' ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
        <a class="flex items-center gap-2.5 shrink-0" href="<?= e(app_url()) ?>">
            <img src="/assets/img/blogforge-icon.png" alt="BlogForge Logo" class="w-8 h-8 object-contain">
            <span class="text-lg font-bold tracking-tight <?= ($isHome ?? false) ? 'text-white' : 'text-[#11151b]' ?>">Blog<strong class="text-[#ff6b13] font-bold">Forge</strong></span>
        </a>
        <div class="flex flex-wrap items-center justify-center gap-5 sm:gap-6 text-sm font-medium <?= ($isHome ?? false) ? 'text-neutral-400' : 'text-gray-500' ?>">
            <a href="<?= e(app_url('about.php')) ?>" class="hover:text-[#ff6b13] transition">About</a>
            <a href="<?= e(app_url('explore.php')) ?>" class="hover:text-[#ff6b13] transition">Explore</a>
            <a href="<?= e(app_url('write.php')) ?>" class="hover:text-[#ff6b13] transition">Write</a>
            <a href="<?= e(app_url('terms.php')) ?>" class="hover:text-[#ff6b13] transition">Terms &amp; Conditions</a>
            <a href="<?= e(app_url('privacy.php')) ?>" class="hover:text-[#ff6b13] transition">Privacy Policy</a>
            <a href="<?= e(app_url('cookies.php')) ?>" class="hover:text-[#ff6b13] transition">Cookie Policy</a>
        </div>
        <small class="text-xs <?= ($isHome ?? false) ? 'text-neutral-500' : 'text-gray-400' ?>">© <?= date('Y') ?> BlogForge.live — All rights reserved.</small>
    </div>
</footer>

<div class="fixed right-5 bottom-5 bg-gray-900 text-white px-4 py-3 rounded-xl shadow-lg opacity-0 translate-y-3 pointer-events-none transition-all duration-200 z-50 text-sm font-medium" data-toast></div>
<script src="/assets/js/app.js"></script>
</body>
</html>
