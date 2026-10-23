</main>
<footer class="border-t border-[#e9e5df] py-10 mt-16 bg-[#fffdfa]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
        <a class="flex items-center gap-2.5 shrink-0" href="<?= e(app_url()) ?>">
            <img src="/assets/img/blogforge-icon.png" alt="BlogForge Logo" class="w-8 h-8 object-contain">
            <span class="text-lg font-bold tracking-tight text-[#11151b]">Blog<strong class="text-[#ff6b13] font-bold">Forge</strong></span>
        </a>
        <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-500 font-medium">
            <a href="<?= e(app_url('about.php')) ?>" class="hover:text-[#ff6b13] transition">About</a>
            <a href="<?= e(app_url('explore.php')) ?>" class="hover:text-[#ff6b13] transition">Explore</a>
            <a href="<?= e(app_url('write.php')) ?>" class="hover:text-[#ff6b13] transition">Write</a>
            <a href="<?= e(app_url('terms.php')) ?>" class="hover:text-[#ff6b13] transition">Terms &amp; Privacy</a>
        </div>
        <small class="text-xs text-gray-400">© <?= date('Y') ?> BlogForge.live — All rights reserved.</small>
    </div>
</footer>

<div class="fixed right-5 bottom-5 bg-gray-900 text-white px-4 py-3 rounded-xl shadow-lg opacity-0 translate-y-3 pointer-events-none transition-all duration-200 z-50 text-sm font-medium" data-toast></div>
<script src="/assets/js/app.js"></script>
</body>
</html>
