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

$heroStory = $featuredStories[0] ?? null;
$sideStories = array_slice($featuredStories, 1, 2);

require __DIR__ . '/partials/header.php';
?>

<!-- SECTION 1: EDITORIAL HERO SHOWCASE -->
<section class="relative overflow-hidden bg-[#fffdfa] pt-14 pb-20 sm:pt-20 sm:pb-28 border-b border-[#e9e5df]">
    <!-- Subtle Editorial Dot Matrix & Warm Atmospheric Glow -->
    <div class="absolute inset-0 bg-[radial-gradient(#e5dfd5_1px,transparent_1px)] [background-size:24px_24px] opacity-45 [mask-image:radial-gradient(ellipse_80%_70%_at_50%_30%,#000_55%,transparent_100%)] pointer-events-none"></div>
    <div class="absolute -top-24 right-0 w-[34rem] h-[34rem] bg-orange-200/35 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 -left-24 w-96 h-96 bg-amber-100/40 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 items-center gap-12 lg:gap-14">
            
            <!-- Left Column: Editorial Headline & Clear Actions -->
            <div class="lg:col-span-6 flex flex-col items-start text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-[#e9e5df] shadow-sm text-xs font-semibold text-gray-700 mb-7">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b13]"></span>
                    <span>Independent Publishing · Zero Paywalls</span>
                </div>

                <h1 class="text-5xl sm:text-6xl lg:text-[4.25rem] font-extrabold tracking-tight text-[#11151b] leading-[1.04] mb-6">
                    Turn quiet ideas into lasting
                    <span class="font-serif italic font-normal text-[#ff6b13] tracking-normal">impact.</span>
                </h1>

                <p class="text-base sm:text-xl text-gray-600 max-w-xl leading-relaxed mb-9 font-normal">
                    A calm, distraction-free home for thoughtful essays, technical deep dives, and independent voices. Write visually without clutter, publish in seconds.
                </p>

                <!-- Focused Dual CTAs -->
                <div class="flex flex-wrap items-center gap-3.5 sm:gap-4 mb-10">
                    <?php if ($user): ?>
                        <a class="inline-flex items-center justify-center gap-2 bg-[#11151b] hover:bg-[#ff6b13] text-white font-semibold text-sm sm:text-base px-7 py-3.5 rounded-full shadow-sm hover:shadow-md transition-all duration-200" href="<?= e(app_url('explore.php')) ?>">
                            <span>Start reading</span>
                            <span aria-hidden="true">→</span>
                        </a>
                        <a class="inline-flex items-center justify-center gap-2 bg-white hover:bg-[#fbfaf8] text-[#11151b] border border-[#e9e5df] hover:border-gray-400 font-semibold text-sm sm:text-base px-6 py-3.5 rounded-full transition-all" href="<?= e(app_url('write.php')) ?>">
                            <span>Write a story</span>
                        </a>
                    <?php else: ?>
                        <a class="inline-flex items-center justify-center gap-2 bg-[#11151b] hover:bg-[#ff6b13] text-white font-semibold text-sm sm:text-base px-7 py-3.5 rounded-full shadow-sm hover:shadow-md transition-all duration-200" href="<?= e(app_url('auth.php?mode=register')) ?>">
                            <span>Get started free</span>
                            <span aria-hidden="true">→</span>
                        </a>
                        <a class="inline-flex items-center justify-center gap-2 bg-white hover:bg-[#fbfaf8] text-[#11151b] border border-[#e9e5df] hover:border-gray-400 font-semibold text-sm sm:text-base px-6 py-3.5 rounded-full transition-all" href="<?= e(app_url('write.php')) ?>">
                            <span>Write a story</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Clean Social Proof Strip -->
                <div class="flex flex-wrap items-center gap-4 pt-6 border-t border-[#e9e5df] w-full max-w-lg">
                    <div class="flex -space-x-2.5">
                        <span class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#11151b] to-[#ff6b13] text-white text-[11px] font-bold flex items-center justify-center ring-2 ring-[#fffdfa]">SA</span>
                        <span class="w-8 h-8 rounded-full bg-gradient-to-tr from-amber-600 to-orange-400 text-white text-[11px] font-bold flex items-center justify-center ring-2 ring-[#fffdfa]">MH</span>
                        <span class="w-8 h-8 rounded-full bg-gradient-to-tr from-stone-800 to-stone-500 text-white text-[11px] font-bold flex items-center justify-center ring-2 ring-[#fffdfa]">NJ</span>
                        <span class="w-8 h-8 rounded-full bg-orange-100 text-[#ff6b13] text-[10px] font-extrabold flex items-center justify-center ring-2 ring-[#fffdfa]">+15k</span>
                    </div>
                    <div class="text-xs sm:text-sm text-gray-600 leading-snug">
                        <strong class="font-semibold text-[#11151b] block">Trusted by independent writers</strong>
                        <span>100% free · Visual WYSIWYG · No ad clutter</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Editorial Showcase Card Composition -->
            <div class="lg:col-span-6 relative lg:pl-4">
                <!-- Floating Dark Studio Toolbar Badge (Top-Left) -->
                <div class="hidden sm:flex items-center gap-3 absolute -top-5 -left-2 z-20 bg-[#11151b] text-white px-4 py-2.5 rounded-2xl shadow-xl border border-white/10">
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-neutral-300 pr-3 border-r border-neutral-700">
                        <span class="w-6 h-6 rounded-md bg-white/10 flex items-center justify-center font-bold text-white">B</span>
                        <span class="w-6 h-6 rounded-md bg-white/5 flex items-center justify-center italic font-serif">I</span>
                        <span class="w-6 h-6 rounded-md bg-white/5 flex items-center justify-center text-[11px]">H2</span>
                        <span class="w-6 h-6 rounded-md bg-white/5 flex items-center justify-center font-serif">“</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-medium text-orange-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Visual Editor Ready</span>
                    </div>
                </div>

                <?php if ($heroStory): ?>
                    <!-- Primary Featured Story Frame -->
                    <a href="<?= e(app_url('article.php?id=' . urlencode((string)($heroStory['slug'] ?? $heroStory['_id'])))) ?>" class="block group bg-white border border-[#e9e5df] rounded-[28px] p-4 sm:p-5 shadow-[0_24px_60px_-15px_rgba(17,21,27,0.1)] hover:shadow-[0_28px_70px_-15px_rgba(255,107,19,0.16)] hover:border-orange-200 transition-all duration-300">
                        <div class="relative aspect-[16/10] w-full rounded-2xl overflow-hidden bg-[#f7f4ef] mb-5">
                            <img src="<?= e(cover_url($heroStory['cover'] ?? null)) ?>" alt="<?= e($heroStory['title']) ?>" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500">
                            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/95 backdrop-blur-sm text-[#11151b] text-[11px] font-bold uppercase tracking-wider shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#ff6b13]"></span>
                                Featured Essay
                            </span>
                        </div>

                        <div class="px-1.5 pb-1">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-2.5">
                                <span class="font-bold text-[#ff6b13] uppercase tracking-wider"><?= e($heroStory['category'] ?? 'General') ?></span>
                                <span><?= e((string)($heroStory['read_time'] ?? 5)) ?> min read</span>
                            </div>

                            <h2 class="text-xl sm:text-2xl font-extrabold text-[#11151b] group-hover:text-[#ff6b13] transition leading-snug line-clamp-2 mb-2.5">
                                <?= e($heroStory['title']) ?>
                            </h2>

                            <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed mb-5">
                                <?= e($heroStory['excerpt'] ?? '') ?>
                            </p>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#11151b] to-[#ff7b22] text-white flex items-center justify-center font-bold text-xs">
                                        <?= e(initials($heroStory['author']['name'] ?? 'Author')) ?>
                                    </span>
                                    <div>
                                        <span class="block text-xs font-bold text-[#11151b]"><?= e($heroStory['author']['name'] ?? 'Author') ?></span>
                                        <span class="block text-[11px] text-gray-400"><?= e(format_date($heroStory['published_at'] ?? time())) ?></span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-[#11151b] group-hover:text-[#ff6b13] group-hover:translate-x-1 transition-all">
                                    Read essay <span aria-hidden="true">→</span>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>

                <!-- Floating Reader Reach Pill (Bottom-Right) -->
                <div class="hidden sm:flex items-center gap-2.5 absolute -bottom-5 right-4 z-20 bg-white/95 backdrop-blur-md border border-[#e9e5df] px-4 py-2.5 rounded-2xl shadow-lg">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-[#ff6b13] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                    </span>
                    <div>
                        <b class="block text-xs font-extrabold text-[#11151b]">250K+ Readers</b>
                        <span class="block text-[11px] text-gray-500">Reading ad-free every month</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- SECTION 2: CURATED TOPIC STRIP & ASYMMETRIC EDITORIAL SPOTLIGHT -->
<section class="py-16 sm:py-20 bg-[#fbfaf8] border-b border-[#e9e5df]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Topic Pill Strip -->
        <div class="flex flex-wrap items-center justify-between gap-4 pb-10 mb-10 border-b border-[#e9e5df]">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400 mr-2">Topics</span>
                <a href="<?= e(app_url('explore.php')) ?>" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-[#11151b] text-white transition">All Stories</a>
                <a href="<?= e(app_url('explore.php?category=Technology')) ?>" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white border border-[#e9e5df] text-gray-700 hover:border-[#ff6b13] hover:text-[#ff6b13] transition">Technology</a>
                <a href="<?= e(app_url('explore.php?category=Lifestyle')) ?>" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white border border-[#e9e5df] text-gray-700 hover:border-[#ff6b13] hover:text-[#ff6b13] transition">Lifestyle</a>
                <a href="<?= e(app_url('explore.php?category=Business')) ?>" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white border border-[#e9e5df] text-gray-700 hover:border-[#ff6b13] hover:text-[#ff6b13] transition">Business</a>
                <a href="<?= e(app_url('explore.php?category=Culture')) ?>" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white border border-[#e9e5df] text-gray-700 hover:border-[#ff6b13] hover:text-[#ff6b13] transition">Culture</a>
                <a href="<?= e(app_url('explore.php?category=Travel')) ?>" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white border border-[#e9e5df] text-gray-700 hover:border-[#ff6b13] hover:text-[#ff6b13] transition">Travel</a>
            </div>
            <a href="<?= e(app_url('explore.php')) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#ff6b13] hover:gap-2.5 transition-all">
                <span>Browse full archive</span>
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="mb-8">
            <span class="text-xs font-bold uppercase tracking-widest text-[#ff6b13] block mb-1.5">Editor's Selection</span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-[#11151b] tracking-tight">Featured Stories</h2>
        </div>

        <!-- Asymmetric Magazine Grid (1 Lead + 2 Stacked) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <?php if ($heroStory): ?>
                <article class="lg:col-span-7 bg-white border border-[#e9e5df] rounded-3xl overflow-hidden flex flex-col hover:border-gray-300 hover:shadow-md transition-all group">
                    <a href="<?= e(app_url('article.php?id=' . urlencode((string)($heroStory['slug'] ?? $heroStory['_id'])))) ?>" class="block w-full aspect-[16/9] overflow-hidden bg-gray-100">
                        <img src="<?= e(cover_url($heroStory['cover'] ?? null)) ?>" alt="<?= e($heroStory['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    </a>
                    <div class="p-6 sm:p-8 flex flex-col flex-1 justify-between">
                        <div>
                            <div class="flex items-center gap-3 text-xs mb-3">
                                <span class="px-2.5 py-0.5 rounded-full font-semibold bg-orange-50 text-[#ff6b13]">
                                    <?= e($heroStory['category'] ?? 'General') ?>
                                </span>
                                <span class="text-gray-400 font-medium"><?= e((string)($heroStory['read_time'] ?? 5)) ?> min read</span>
                            </div>
                            <a href="<?= e(app_url('article.php?id=' . urlencode((string)($heroStory['slug'] ?? $heroStory['_id'])))) ?>" class="block mb-3">
                                <h3 class="text-2xl sm:text-3xl font-extrabold text-[#11151b] group-hover:text-[#ff6b13] transition leading-tight">
                                    <?= e($heroStory['title']) ?>
                                </h3>
                            </a>
                            <p class="text-gray-600 text-sm sm:text-base line-clamp-3 leading-relaxed mb-6">
                                <?= e($heroStory['excerpt'] ?? '') ?>
                            </p>
                        </div>
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#11151b] to-[#ff7b22] text-white flex items-center justify-center font-bold text-xs">
                                    <?= e(initials($heroStory['author']['name'] ?? 'Author')) ?>
                                </span>
                                <span class="text-xs font-bold text-[#11151b]"><?= e($heroStory['author']['name'] ?? 'Author') ?></span>
                            </div>
                            <span class="text-xs text-gray-400"><?= e(format_date($heroStory['published_at'] ?? time())) ?></span>
                        </div>
                    </div>
                </article>
            <?php endif; ?>

            <div class="lg:col-span-5 flex flex-col gap-6 justify-between">
                <?php foreach ($sideStories as $story): ?>
                    <article class="flex-1 bg-white border border-[#e9e5df] rounded-3xl p-5 sm:p-6 flex flex-col sm:flex-row lg:flex-col xl:flex-row gap-5 hover:border-gray-300 hover:shadow-md transition-all group">
                        <a href="<?= e(app_url('article.php?id=' . urlencode((string)($story['slug'] ?? $story['_id'])))) ?>" class="block w-full sm:w-40 lg:w-full xl:w-40 aspect-[16/10] sm:aspect-square lg:aspect-[16/9] xl:aspect-square rounded-2xl overflow-hidden bg-gray-100 shrink-0">
                            <img src="<?= e(cover_url($story['cover'] ?? null)) ?>" alt="<?= e($story['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        </a>
                        <div class="flex flex-col justify-between flex-1 min-w-0">
                            <div>
                                <div class="flex items-center justify-between gap-2 text-xs mb-2">
                                    <span class="font-bold text-[#ff6b13]"><?= e($story['category'] ?? 'General') ?></span>
                                    <span class="text-gray-400"><?= e((string)($story['read_time'] ?? 5)) ?> min</span>
                                </div>
                                <a href="<?= e(app_url('article.php?id=' . urlencode((string)($story['slug'] ?? $story['_id'])))) ?>" class="block mb-2">
                                    <h3 class="text-lg font-extrabold text-[#11151b] group-hover:text-[#ff6b13] transition leading-snug line-clamp-2">
                                        <?= e($story['title']) ?>
                                    </h3>
                                </a>
                                <p class="text-gray-500 text-xs line-clamp-2 leading-relaxed mb-4">
                                    <?= e($story['excerpt'] ?? '') ?>
                                </p>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
                                <span class="font-semibold text-[#11151b] truncate"><?= e($story['author']['name'] ?? 'Author') ?></span>
                                <span class="text-gray-400 shrink-0"><?= e(format_date($story['published_at'] ?? time())) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 3: BUILT FOR FOCUS (EDITORIAL CRAFT PILLARS) -->
<section class="py-16 sm:py-24 bg-[#fffdfa] border-b border-[#e9e5df]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
            
            <!-- Left Sticky Manifesto -->
            <div class="lg:col-span-5 lg:sticky lg:top-28">
                <span class="text-xs font-bold uppercase tracking-widest text-[#ff6b13] block mb-2">Why BlogForge</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-[#11151b] tracking-tight leading-[1.08] mb-5">
                    Publishing without
                    <span class="font-serif italic font-normal text-[#ff6b13]">compromise.</span>
                </h2>
                <p class="text-gray-600 text-base sm:text-lg leading-relaxed mb-8">
                    Modern writing platforms became noisy feeds chasing clicks. We stripped away the clutter to leave only what matters: crisp typography, effortless visual editing, and genuine readers.
                </p>
                <a href="<?= e(app_url('about.php')) ?>" class="inline-flex items-center gap-2 text-sm font-bold text-[#11151b] hover:text-[#ff6b13] transition group">
                    <span>Read our publishing manifesto</span>
                    <span class="group-hover:translate-x-1 transition-transform" aria-hidden="true">→</span>
                </a>
            </div>

            <!-- Right 2x2 Unified Stone Cards -->
            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-[#fbfaf8] border border-[#e9e5df] rounded-3xl p-7 hover:border-gray-300 transition">
                    <div class="flex items-center justify-between mb-6">
                        <span class="font-serif italic text-3xl text-[#ff6b13]">01</span>
                        <span class="w-10 h-10 rounded-xl bg-white border border-[#e9e5df] text-[#11151b] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-[#11151b] mb-2">Distraction-Free</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Zero popups, autoplay banners, or paywall walls. Editorial typography calibrated for long-form focus.
                    </p>
                </div>

                <div class="bg-[#fbfaf8] border border-[#e9e5df] rounded-3xl p-7 hover:border-gray-300 transition">
                    <div class="flex items-center justify-between mb-6">
                        <span class="font-serif italic text-3xl text-[#ff6b13]">02</span>
                        <span class="w-10 h-10 rounded-xl bg-white border border-[#e9e5df] text-[#11151b] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-[#11151b] mb-2">Visual WYSIWYG</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Style headings, quotes, lists, and smart-pasted excerpts directly on the page — never wrestle raw markup.
                    </p>
                </div>

                <div class="bg-[#fbfaf8] border border-[#e9e5df] rounded-3xl p-7 hover:border-gray-300 transition">
                    <div class="flex items-center justify-between mb-6">
                        <span class="font-serif italic text-3xl text-[#ff6b13]">03</span>
                        <span class="w-10 h-10 rounded-xl bg-white border border-[#e9e5df] text-[#11151b] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-[#11151b] mb-2">Instant WebP Media</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Automatic browser-side WebP compression trims large cover photos down to ~120KB for instantaneous loads.
                    </p>
                </div>

                <div class="bg-[#fbfaf8] border border-[#e9e5df] rounded-3xl p-7 hover:border-gray-300 transition">
                    <div class="flex items-center justify-between mb-6">
                        <span class="font-serif italic text-3xl text-[#ff6b13]">04</span>
                        <span class="w-10 h-10 rounded-xl bg-white border border-[#e9e5df] text-[#11151b] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-[#11151b] mb-2">Open Community</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Direct reader responses, saved reading lists, and author spaces built to connect curious minds organically.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- SECTION 4: FRESH FROM THE COMMUNITY -->
<section class="py-16 sm:py-24 bg-[#fbfaf8] border-b border-[#e9e5df]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-[#ff6b13] mb-1.5 block">Community Voices</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-[#11151b] tracking-tight">Recent Articles</h2>
            </div>
            <a href="<?= e(app_url('explore.php')) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 hover:text-[#ff6b13] transition">
                <span>View all articles</span>
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
            <?php foreach ($latestStories as $story): ?>
                <article class="flex flex-col justify-between p-6 rounded-3xl bg-white border border-[#e9e5df] hover:border-gray-300 hover:-translate-y-0.5 hover:shadow-md transition-all group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-[#ff6b13]">
                                <?= e($story['category'] ?? 'General') ?>
                            </span>
                            <span class="text-xs text-gray-400 font-medium"><?= e((string)($story['read_time'] ?? 4)) ?> min read</span>
                        </div>

                        <a href="<?= e(app_url('article.php?id=' . urlencode((string)($story['slug'] ?? $story['_id'])))) ?>" class="block mb-2.5">
                            <h3 class="text-lg font-extrabold text-[#11151b] group-hover:text-[#ff6b13] transition line-clamp-2 leading-snug">
                                <?= e($story['title']) ?>
                            </h3>
                        </a>

                        <p class="text-gray-500 text-sm line-clamp-3 leading-relaxed mb-6">
                            <?= e($story['excerpt'] ?? '') ?>
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-gradient-to-tr from-[#11151b] to-[#ff7b22] text-white flex items-center justify-center font-bold text-[10px]">
                                <?= e(initials($story['author']['name'] ?? 'Author')) ?>
                            </span>
                            <span class="text-xs font-semibold text-gray-800"><?= e($story['author']['name'] ?? 'Author') ?></span>
                        </div>
                        <a href="<?= e(app_url('article.php?id=' . urlencode((string)($story['slug'] ?? $story['_id'])))) ?>" class="text-xs font-bold text-[#ff6b13] hover:underline">
                            Read story →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECTION 5: CONTAINED COMMUNITY PROOF & TESTIMONIAL -->
<section class="pt-16 sm:pt-20 bg-[#fffdfa]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative rounded-[32px] bg-[#11151b] text-white p-8 sm:p-14 lg:p-16 overflow-hidden shadow-xl">
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-orange-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center pb-12 sm:pb-14 border-b border-white/10 relative z-10">
                <div>
                    <b class="block text-3xl sm:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-[#ff6b13] to-amber-300 mb-2">15,000+</b>
                    <span class="text-xs text-neutral-400 uppercase tracking-wider font-semibold">Stories Published</span>
                </div>
                <div>
                    <b class="block text-3xl sm:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-[#ff6b13] to-amber-300 mb-2">250K+</b>
                    <span class="text-xs text-neutral-400 uppercase tracking-wider font-semibold">Monthly Readers</span>
                </div>
                <div>
                    <b class="block text-3xl sm:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-[#ff6b13] to-amber-300 mb-2">100%</b>
                    <span class="text-xs text-neutral-400 uppercase tracking-wider font-semibold">Independent Writers</span>
                </div>
                <div>
                    <b class="block text-3xl sm:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-[#ff6b13] to-amber-300 mb-2">0</b>
                    <span class="text-xs text-neutral-400 uppercase tracking-wider font-semibold">Paywall Barriers</span>
                </div>
            </div>

            <div class="pt-12 max-w-3xl mx-auto text-center relative z-10">
                <p class="font-serif italic text-2xl sm:text-3xl text-neutral-100 leading-relaxed mb-7">
                    “BlogForge gave my technical essays a clean, dignified home without the algorithm games of other platforms. Readers actually read to the very last word.”
                </p>
                <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full bg-white/5 border border-white/10">
                    <span class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#ff6b13] to-amber-400 text-white font-bold flex items-center justify-center text-xs">SA</span>
                    <div class="text-left pr-2">
                        <b class="block text-xs font-semibold text-white">Sarah Ahmed</b>
                        <small class="text-[11px] text-neutral-400">Software Architect &amp; Essayist</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 6: EDITORIAL CALL TO ACTION -->
<section class="pt-10 sm:pt-14 pb-6 bg-[#fffdfa]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative rounded-[32px] bg-[#fbfaf8] border border-[#e9e5df] p-8 sm:p-14 text-center overflow-hidden">
            <div class="absolute -bottom-24 left-1/2 -translate-x-1/2 w-[28rem] h-48 bg-orange-200/40 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="inline-block uppercase tracking-widest text-xs font-bold text-[#ff6b13] mb-3">Your voice matters</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-[#11151b] tracking-tight mb-4">
                    Start writing your
                    <span class="font-serif italic font-normal text-[#ff6b13]">story</span> today.
                </h2>
                <p class="text-gray-600 text-sm sm:text-base leading-relaxed mb-8">
                    Whether it’s an engineering deep dive, cultural critique, or personal essay — give your words the typography and stage they deserve.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3.5">
                    <a href="<?= e(app_url('write.php')) ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#11151b] hover:bg-[#ff6b13] text-white font-semibold text-sm sm:text-base px-8 py-3.5 shadow-sm transition-all">
                        <span>Write a story</span>
                        <span aria-hidden="true">→</span>
                    </a>
                    <?php if (!$user): ?>
                        <a href="<?= e(app_url('auth.php?mode=register')) ?>" class="inline-flex items-center justify-center rounded-full bg-white hover:bg-gray-50 border border-[#e9e5df] text-[#11151b] font-semibold text-sm sm:text-base px-7 py-3.5 transition">
                            Create free account
                        </a>
                    <?php else: ?>
                        <a href="<?= e(app_url('explore.php')) ?>" class="inline-flex items-center justify-center rounded-full bg-white hover:bg-gray-50 border border-[#e9e5df] text-[#11151b] font-semibold text-sm sm:text-base px-7 py-3.5 transition">
                            Explore stories
                        </a>
                    <?php endif; ?>
                </div>
                <small class="block text-xs text-gray-400 mt-6">Free forever · No credit card required · Instant setup</small>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
