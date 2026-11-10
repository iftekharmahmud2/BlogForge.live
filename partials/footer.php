</main>
<footer class="border-t border-gray-200 bg-[#11151b] text-neutral-300 pt-16 pb-12 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Top Main Footer Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 pb-14 border-b border-neutral-800">
            
            <!-- Column 1: Brand & Mission (4 cols) -->
            <div class="lg:col-span-4 space-y-4">
                <a class="flex items-center gap-2.5 group w-fit" href="<?= e(app_url()) ?>">
                    <img src="/assets/img/blogforge-icon.png" alt="BlogForge Logo" class="w-9 h-9 object-contain group-hover:scale-105 transition-transform duration-200">
                    <span class="text-xl font-bold tracking-tight text-white">Blog<strong class="text-[#ff6b13] font-bold">Forge</strong></span>
                </a>
                <p class="text-sm text-neutral-400 max-w-sm leading-relaxed">
                    Ideas into impact. BlogForge is an open, modern publishing platform built for curious minds, bold thinkers, and writers who value depth over noise.
                </p>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-neutral-900 border border-neutral-800 text-xs text-neutral-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>All systems operational · v2.4</span>
                </div>
            </div>

            <!-- Column 2: Discover & Topics (2 cols) -->
            <div class="lg:col-span-2 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-200">Discover</h3>
                <ul class="space-y-2 text-sm text-neutral-400">
                    <li><a href="<?= e(app_url('explore.php')) ?>" class="hover:text-[#ff6b13] transition">All Stories</a></li>
                    <li><a href="<?= e(app_url('explore.php?category=Technology')) ?>" class="hover:text-[#ff6b13] transition">Technology</a></li>
                    <li><a href="<?= e(app_url('explore.php?category=Lifestyle')) ?>" class="hover:text-[#ff6b13] transition">Lifestyle</a></li>
                    <li><a href="<?= e(app_url('explore.php?category=Business')) ?>" class="hover:text-[#ff6b13] transition">Business</a></li>
                    <li><a href="<?= e(app_url('explore.php?category=Culture')) ?>" class="hover:text-[#ff6b13] transition">Culture</a></li>
                </ul>
            </div>

            <!-- Column 3: Writers & Workspace (2 cols) -->
            <div class="lg:col-span-2 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-200">For Writers</h3>
                <ul class="space-y-2 text-sm text-neutral-400">
                    <li><a href="<?= e(app_url('write.php')) ?>" class="hover:text-[#ff6b13] transition">Write a story</a></li>
                    <li><a href="<?= e(app_url('drafts.php')) ?>" class="hover:text-[#ff6b13] transition">My Drafts</a></li>
                    <li><a href="<?= e(app_url('about.php')) ?>" class="hover:text-[#ff6b13] transition">Author Guidelines</a></li>
                    <li><a href="<?= e(app_url('profile.php')) ?>" class="hover:text-[#ff6b13] transition">Creator Profile</a></li>
                    <li><a href="<?= e(app_url('faq.php')) ?>" class="hover:text-[#ff6b13] transition">Publishing FAQ</a></li>
                </ul>
            </div>

            <!-- Column 4: Company & Help (2 cols) -->
            <div class="lg:col-span-2 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-200">Company</h3>
                <ul class="space-y-2 text-sm text-neutral-400">
                    <li><a href="<?= e(app_url('about.php')) ?>" class="hover:text-[#ff6b13] transition">About Us</a></li>
                    <li><a href="<?= e(app_url('contact.php')) ?>" class="hover:text-[#ff6b13] transition">Contact Support</a></li>
                    <li><a href="<?= e(app_url('faq.php')) ?>" class="hover:text-[#ff6b13] transition">Help Center</a></li>
                    <li><a href="<?= e(app_url('terms.php')) ?>" class="hover:text-[#ff6b13] transition">Terms of Service</a></li>
                    <li><a href="<?= e(app_url('privacy.php')) ?>" class="hover:text-[#ff6b13] transition">Privacy Policy</a></li>
                    <li><a href="<?= e(app_url('cookies.php')) ?>" class="hover:text-[#ff6b13] transition">Cookie Policy</a></li>
                </ul>
            </div>

            <!-- Column 5: Stay Connected (2 cols) -->
            <div class="lg:col-span-2 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-200">Weekly Digest</h3>
                <p class="text-xs text-neutral-400 leading-relaxed">
                    Get top essays and ideas delivered to your inbox every Sunday.
                </p>
                <form class="space-y-2" onsubmit="event.preventDefault();toast('Subscribed to weekly digest!');this.reset();">
                    <input type="email" required placeholder="Enter your email" class="w-full bg-neutral-900 border border-neutral-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-neutral-500 outline-none focus:border-[#ff6b13] transition">
                    <button type="submit" class="w-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-xs font-semibold py-2 rounded-xl shadow-sm hover:brightness-105 transition cursor-pointer">
                        Subscribe
                    </button>
                </form>
            </div>

        </div>

        <!-- Bottom Bar -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-500">
            <p>© <?= date('Y') ?> BlogForge.live — All rights reserved.</p>
            <div class="flex items-center gap-6">
                <a href="<?= e(app_url('terms.php')) ?>" class="hover:text-neutral-300 transition">Terms</a>
                <a href="<?= e(app_url('privacy.php')) ?>" class="hover:text-neutral-300 transition">Privacy</a>
                <a href="<?= e(app_url('cookies.php')) ?>" class="hover:text-neutral-300 transition">Cookies</a>
                <a href="<?= e(app_url('contact.php')) ?>" class="hover:text-neutral-300 transition">Contact</a>
            </div>
        </div>
    </div>
</footer>

<div class="fixed right-5 bottom-5 bg-gray-900 text-white px-4 py-3 rounded-xl shadow-xl opacity-0 translate-y-3 pointer-events-none transition-all duration-200 z-50 text-sm font-medium" data-toast></div>
<script src="/assets/js/app.js"></script>
</body>
</html>
