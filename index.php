<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'BlogForge — Ideas into Impact';
$user = current_user();

// Fetch public stories for the landing page showcase; fallback to sample stories if DB is empty
$featuredStories = public_posts(3);
if (empty($featuredStories)) {
    $allSamples = sample_posts();
    $featuredStories = array_slice($allSamples, 0, 3);
}

$latestStories = public_posts(6);
if (empty($latestStories)) {
    $latestStories = sample_posts();
}

require __DIR__ . '/partials/header.php';
?>

<!-- SECTION 1: HERO BANNER -->
<section class="relative bg-neutral-950 text-white overflow-hidden py-16 sm:py-24 lg:py-28 px-4 sm:px-6 lg:px-8 border-b border-neutral-800">
    <!-- Ambient Lighting Glows -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-orange-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-amber-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 items-center gap-12 lg:gap-8 relative z-10">
        <!-- Hero Copy & Call to Actions (Left) -->
        <div class="lg:col-span-7 flex flex-col items-start text-left">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-orange-500/10 text-orange-400 border border-orange-500/20 mb-6 uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-[#ff6b13] animate-pulse"></span>
                BlogForge.live · Open Publishing Platform
            </span>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-[1.08] mb-6 text-white">
                Ideas<br>into <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff6b13] via-[#ff8833] to-[#ffaa44]">Impact.</span>
            </h1>

            <p class="text-base sm:text-xl text-neutral-300 max-w-xl leading-relaxed mb-8 font-normal">
                A home for curious minds, bold ideas, and meaningful stories. Read deeply, write clearly, and be part of a growing community of thinkers.
            </p>

            <div class="flex flex-wrap items-center gap-3.5 sm:gap-4">
                <?php if ($user): ?>
                    <a class="inline-flex items-center justify-center bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-sm sm:text-base px-7 py-3.5 rounded-full shadow-lg hover:brightness-110 hover:scale-[1.02] transition" href="<?= e(app_url('explore.php')) ?>">
                        Start reading →
                    </a>
                    <a class="inline-flex items-center justify-center bg-neutral-900 hover:bg-neutral-800 text-neutral-200 border border-neutral-700 font-semibold text-sm sm:text-base px-6 py-3.5 rounded-full transition" href="<?= e(app_url('write.php')) ?>">
                        Write a story
                    </a>
                    <a class="inline-flex items-center justify-center text-neutral-400 hover:text-white font-medium text-sm sm:text-base px-4 py-3.5 transition" href="<?= e(app_url('drafts.php')) ?>">
                        My Drafts
                    </a>
                <?php else: ?>
                    <a class="inline-flex items-center justify-center bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-sm sm:text-base px-7 py-3.5 rounded-full shadow-lg hover:brightness-110 hover:scale-[1.02] transition" href="<?= e(app_url('auth.php?mode=register')) ?>">
                        Get started →
                    </a>
                    <a class="inline-flex items-center justify-center bg-neutral-900 hover:bg-neutral-800 text-neutral-200 border border-neutral-700 font-semibold text-sm sm:text-base px-6 py-3.5 rounded-full transition" href="<?= e(app_url('write.php')) ?>">
                        Write a story
                    </a>
                    <a class="inline-flex items-center justify-center text-neutral-400 hover:text-white font-medium text-sm sm:text-base px-4 py-3.5 transition" href="<?= e(app_url('explore.php')) ?>">
                        Browse stories
                    </a>
                <?php endif; ?>
            </div>

            <!-- Trust Badge Line -->
            <div class="mt-8 flex items-center gap-6 text-xs text-neutral-400">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    100% Free
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Zero Paywalls
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    WYSIWYG Publishing
                </span>
            </div>
        </div>

        <!-- 3D Logo Emblem (Right) -->
        <div class="lg:col-span-5 flex items-center justify-center lg:justify-end">
            <div class="relative group">
                <div class="absolute -inset-4 bg-gradient-to-r from-orange-500/20 to-amber-500/20 rounded-full blur-2xl group-hover:blur-3xl transition duration-500"></div>
                <img src="/assets/img/blogforge-icon.png" alt="BlogForge emblem" class="relative w-64 sm:w-80 lg:w-[26rem] max-w-full object-contain drop-shadow-[0_25px_35px_rgba(0,0,0,0.6)] select-none hover:scale-105 transition-transform duration-500">
            </div>
        </div>
    </div>
</section>

<!-- SECTION 2: FEATURED / TRENDING STORIES -->
<section class="py-16 sm:py-20 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
            <div>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#ff6b13] mb-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.316.492-.474.908-.504 1.255-.07.785-.42 1.488-.95 1.954-.53.465-1.22.7-1.954.7-.68 0-1.32.22-1.84.62-.51.39-.88.94-1.05 1.57-.16.63-.1 1.31.18 1.89.28.58.74 1.05 1.31 1.33.56.28 1.2.37 1.83.27.63-.1 1.21-.4 1.66-.86.44-.45.74-1.04.84-1.68.03-.22.1-.44.2-.64.1-.2.24-.38.41-.53.17-.15.37-.26.59-.32.22-.06.45-.07.67-.02.22.05.42.15.58.3.16.15.28.34.34.56.06.22.07.45.02.67-.05.22-.15.42-.3.58a3.96 3.96 0 01-1.38 1.05c-.52.26-1.1.39-1.69.39-.6 0-1.18-.13-1.71-.39a4.01 4.01 0 01-1.38-1.05 3.99 3.99 0 01-.81-1.47 3.98 3.98 0 01-.15-1.67c.12-.56.39-1.08.79-1.5.4-.42.9-.72 1.45-.87.55-.15 1.13-.14 1.67.03.54.17 1.02.48 1.39.9.37.42.61.94.7 1.5.09.56.02 1.14-.2 1.66-.22.52-.58.96-1.04 1.28-.46.32-1 .5-1.57.52-.57.02-1.14-.12-1.63-.41-.49-.29-.88-.71-1.13-1.22a3.99 3.99 0 01-.36-1.63c.04-.56.24-1.1.58-1.55.34-.45.8-.8 1.32-1.02.52-.22 1.1-.29 1.66-.2.56.09 1.08.34 1.5.73.42.39.72.9.87 1.46.15.56.14 1.15-.03 1.7-.17.55-.49 1.03-.92 1.4-.43.37-.96.61-1.53.69z" clip-rule="evenodd"/></svg>
                    Trending on BlogForge
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Featured Stories</h2>
            </div>
            <a href="<?= e(app_url('explore.php')) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#ff6b13] hover:gap-2 transition-all">
                <span>View all stories</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($featuredStories as $story): ?>
                <article class="bg-[#fbfaf8] border border-gray-200/80 rounded-3xl overflow-hidden flex flex-col hover:border-gray-300 hover:shadow-md transition-all group">
                    <a href="<?= e(app_url('article.php?id=' . urlencode((string)($story['slug'] ?? $story['_id'])))) ?>" class="block w-full h-48 sm:h-52 overflow-hidden bg-gray-100">
                        <img src="<?= e(cover_url($story['cover'] ?? null)) ?>" alt="<?= e($story['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                    </a>

                    <div class="p-6 flex flex-col flex-1 justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-700">
                                    <?= e($story['category'] ?? 'General') ?>
                                </span>
                                <span class="text-xs text-gray-400 font-medium"><?= e((string)($story['read_time'] ?? 5)) ?> min read</span>
                            </div>

                            <a href="<?= e(app_url('article.php?id=' . urlencode((string)($story['slug'] ?? $story['_id'])))) ?>" class="block mb-2">
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 group-hover:text-[#ff6b13] transition line-clamp-2">
                                    <?= e($story['title']) ?>
                                </h3>
                            </a>

                            <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed mb-6">
                                <?= e($story['excerpt'] ?? '') ?>
                            </p>
                        </div>

                        <div class="pt-4 border-t border-gray-200/80 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-[10px]">
                                    <?= e(initials($story['author']['name'] ?? 'Author')) ?>
                                </span>
                                <span class="text-xs font-semibold text-gray-800"><?= e($story['author']['name'] ?? 'Author') ?></span>
                            </div>
                            <span class="text-xs text-gray-400"><?= e(format_date($story['published_at'] ?? time())) ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECTION 3: WHY BLOGFORGE (CORE ADVANTAGES) -->
<section class="py-16 sm:py-24 bg-[#fbfaf8] border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 text-[#ff6b13] border border-orange-100 mb-3">
                Why BlogForge
            </span>
            <h2 class="text-3xl sm:text-5xl font-black text-gray-900 tracking-tight mb-4">Publishing without compromise.</h2>
            <p class="text-gray-600 text-base sm:text-lg leading-relaxed">
                We re-imagined online writing from the ground up. Fast, distraction-free, and respectful of both authors and readers.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            <!-- Feature Card 1 -->
            <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 hover:border-[#ff6b13]/40 hover:shadow-sm transition group">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#ff6b13] flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Distraction-Free</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Zero popups, autoplay videos, or clickbait banners. Beautiful typography crafted for uninterrupted deep reading.
                </p>
            </div>

            <!-- Feature Card 2 -->
            <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 hover:border-[#ff6b13]/40 hover:shadow-sm transition group">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Visual WYSIWYG</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Write naturally. Real-time visual formatting lets you style headings, lists, quotes, and links without raw markdown syntax.
                </p>
            </div>

            <!-- Feature Card 3 -->
            <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 hover:border-[#ff6b13]/40 hover:shadow-sm transition group">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Fast WebP Media</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Smart client compression turns large images into ~120KB WebP files. Support for Unsplash, Cloudinary, and direct embeds.
                </p>
            </div>

            <!-- Feature Card 4 -->
            <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 hover:border-[#ff6b13]/40 hover:shadow-sm transition group">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Open Community</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Thoughtful responses, story likes, personal reading lists, and author profiles that put your ideas in front of engaged minds.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 4: LATEST COMMUNITY ARTICLES & TOPIC PILLS -->
<section class="py-16 sm:py-24 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#ff6b13] mb-1 block">Community Voices</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Recent Articles</h2>
            </div>
            
            <!-- Quick Category Filter Pills -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?= e(app_url('explore.php')) ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-900 text-white hover:bg-black transition">All</a>
                <a href="<?= e(app_url('explore.php?category=Technology')) ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition">Technology</a>
                <a href="<?= e(app_url('explore.php?category=Lifestyle')) ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition">Lifestyle</a>
                <a href="<?= e(app_url('explore.php?category=Business')) ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition">Business</a>
                <a href="<?= e(app_url('explore.php?category=Culture')) ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition">Culture</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($latestStories as $story): ?>
                <article class="flex flex-col justify-between p-6 rounded-3xl bg-[#fbfaf8] border border-gray-200 hover:border-gray-300 transition group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-700">
                                <?= e($story['category'] ?? 'General') ?>
                            </span>
                            <span class="text-xs text-gray-400 font-medium"><?= e((string)($story['read_time'] ?? 4)) ?> min read</span>
                        </div>

                        <a href="<?= e(app_url('article.php?id=' . urlencode((string)($story['slug'] ?? $story['_id'])))) ?>" class="block mb-2">
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#ff6b13] transition line-clamp-2">
                                <?= e($story['title']) ?>
                            </h3>
                        </a>

                        <p class="text-gray-500 text-xs sm:text-sm line-clamp-3 leading-relaxed mb-6">
                            <?= e($story['excerpt'] ?? '') ?>
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-200/80 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-[9px]">
                                <?= e(initials($story['author']['name'] ?? 'Author')) ?>
                            </span>
                            <span class="text-xs font-medium text-gray-700"><?= e($story['author']['name'] ?? 'Author') ?></span>
                        </div>
                        <a href="<?= e(app_url('article.php?id=' . urlencode((string)($story['slug'] ?? $story['_id'])))) ?>" class="text-xs font-semibold text-[#ff6b13] hover:underline">
                            Read story →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECTION 5: CREATOR METRICS & SOCIAL PROOF -->
<section class="py-16 sm:py-24 bg-[#11151b] text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center pb-16 border-b border-neutral-800">
            <div>
                <b class="block text-4xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-[#ff6b13] to-amber-400 mb-2">15,000+</b>
                <span class="text-xs sm:text-sm text-neutral-400 uppercase tracking-wider font-semibold">Stories Published</span>
            </div>
            <div>
                <b class="block text-4xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-[#ff6b13] to-amber-400 mb-2">250K+</b>
                <span class="text-xs sm:text-sm text-neutral-400 uppercase tracking-wider font-semibold">Monthly Readers</span>
            </div>
            <div>
                <b class="block text-4xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-[#ff6b13] to-amber-400 mb-2">100%</b>
                <span class="text-xs sm:text-sm text-neutral-400 uppercase tracking-wider font-semibold">Independent Writers</span>
            </div>
            <div>
                <b class="block text-4xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-[#ff6b13] to-amber-400 mb-2">0</b>
                <span class="text-xs sm:text-sm text-neutral-400 uppercase tracking-wider font-semibold">Paywall Barriers</span>
            </div>
        </div>

        <!-- Featured Writer Quote -->
        <div class="pt-16 max-w-3xl mx-auto text-center">
            <svg class="w-10 h-10 text-orange-500/40 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
            <p class="text-lg sm:text-2xl font-medium text-neutral-200 leading-relaxed mb-6">
                “BlogForge gave my technical essays a clean, dignified home without the algorithm games of other platforms. Readers actually read to the very last word.”
            </p>
            <div class="flex items-center justify-center gap-3">
                <span class="w-10 h-10 rounded-full bg-gradient-to-tr from-orange-600 to-amber-400 text-white font-bold flex items-center justify-center text-xs">SA</span>
                <div class="text-left">
                    <b class="block text-sm font-semibold text-white">Sarah Ahmed</b>
                    <small class="text-xs text-neutral-400">Software Architect &amp; Essayist</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 6: HIGH-IMPACT CALL TO ACTION -->
<section class="py-16 sm:py-24 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative rounded-3xl bg-gradient-to-br from-orange-500 via-[#ff6b13] to-amber-600 p-8 sm:p-14 text-white text-center shadow-xl overflow-hidden">
            <!-- Decorative circle shapes -->
            <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -left-16 w-56 h-56 rounded-full bg-black/10 blur-xl pointer-events-none"></div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="inline-block uppercase tracking-widest text-xs font-bold text-orange-200 mb-3">Your ideas belong here</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight mb-4">Start writing your story today.</h2>
                <p class="text-orange-100 text-sm sm:text-base leading-relaxed mb-8">
                    Whether it's an engineering deep dive, cultural critique, or personal journey — give your thoughts the stage they deserve.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3.5">
                    <a href="<?= e(app_url('write.php')) ?>" class="inline-flex items-center justify-center rounded-full bg-white text-gray-900 font-bold text-sm sm:text-base px-8 py-3.5 shadow-md hover:bg-neutral-100 hover:scale-105 transition">
                        Write a story →
                    </a>
                    <a href="<?= e(app_url('explore.php')) ?>" class="inline-flex items-center justify-center rounded-full bg-black/20 hover:bg-black/30 border border-white/20 text-white font-semibold text-sm sm:text-base px-7 py-3.5 transition">
                        Explore stories
                    </a>
                </div>
                <small class="block text-xs text-orange-200 mt-6">Free forever · No credit card required · Instant setup</small>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
