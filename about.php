<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'About Us — BlogForge';
require __DIR__ . '/partials/header.php';
?>

<!-- SECTION 1: HERO HEADER -->
<section class="border-b border-gray-100 bg-[#fbfaf8] py-16 sm:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 text-[#ff6b13] mb-4 border border-orange-100">
            About BlogForge
        </span>
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-gray-900 mb-6 leading-tight">
            Built for ideas that deserve to travel.
        </h1>
        <p class="text-gray-600 text-base sm:text-xl max-w-2xl mx-auto leading-relaxed">
            BlogForge is an independent, distraction-free publishing ecosystem built for thinkers, writers, and curious readers who value depth, clarity, and genuine human insight over algorithmic noise.
        </p>
    </div>
</section>

<!-- SECTION 2: THE BLOGFORGE MANIFESTO -->
<section class="py-16 sm:py-24 bg-white border-b border-gray-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="prose prose-lg max-w-none text-gray-700 space-y-6 leading-relaxed">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">The Problem with Modern Publishing</h2>
            <p>
                The modern web has become increasingly hostile to both writers and readers. Content platforms optimize for endless scroll, aggressive popups, clickbait headlines, and invasive advertising trackers that commoditize human attention.
            </p>
            <p>
                Writers spend hours crafting thoughtful essays, only to have their work locked behind aggressive paywalls or buried underneath algorithmic feeds designed to provoke outrage rather than spark understanding.
            </p>
            <blockquote class="border-l-4 border-[#ff6b13] bg-[#fbfaf8] p-5 rounded-r-2xl italic text-gray-800 font-serif text-lg my-6">
                “We built BlogForge on a simple premise: great writing needs room to breathe. When you eliminate distraction, ideas regain their power to change minds.”
            </blockquote>
        </div>
    </div>
</section>

<!-- SECTION 3: THREE CORE PILLARS (WITH SVG ICONS) -->
<section class="py-16 sm:py-24 bg-[#fbfaf8] border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-[#ff6b13] mb-2 block">Our Foundations</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">What guides every decision we make</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Pillar 1 -->
            <div class="bg-white border border-gray-200 rounded-3xl p-8 hover:border-orange-200 hover:shadow-md transition">
                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-[#ff6b13] flex items-center justify-center mb-6">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">1. Dignified Reading</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Zero interstitial popups, zero intrusive overlay banners, and zero paywall traps. High-legibility typography designed for prolonged reading comfort on mobile and desktop.
                </p>
            </div>

            <!-- Pillar 2 -->
            <div class="bg-white border border-gray-200 rounded-3xl p-8 hover:border-orange-200 hover:shadow-md transition">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">2. Intuitive Authoring</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    A true visual WYSIWYG editor. Format headings, quotes, lists, and images directly without struggling with raw markdown tokens or syntax errors.
                </p>
            </div>

            <!-- Pillar 3 -->
            <div class="bg-white border border-gray-200 rounded-3xl p-8 hover:border-orange-200 hover:shadow-md transition">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">3. Creator Sovereignty</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    You own 100% of your work. Edit, unpublish, or delete stories whenever you choose. No vendor lock-in, no content clawbacks, and full privacy compliance.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 4: PLATFORM ARCHITECTURE & TECH SPECS -->
<section class="py-16 sm:py-24 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-5 space-y-4">
                <span class="text-xs font-bold uppercase tracking-wider text-[#ff6b13] block">Engineered for Performance</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Modern Web Architecture</h2>
                <p class="text-gray-600 text-sm sm:text-base leading-relaxed">
                    Behind BlogForge's calm exterior lies a fast, resilient web application stack built to handle high concurrency with low overhead.
                </p>

                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-3 text-sm text-gray-800">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">✓</span>
                        <span><strong>PHP 8.3 &amp; MongoDB NoSQL:</strong> Native JSON document persistence and high-throughput query caching.</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-800">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">✓</span>
                        <span><strong>Client-Side WebP Compression:</strong> Converts large uploads to ~120KB WebP images directly in-browser.</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-800">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">✓</span>
                        <span><strong>Zero-Trust Security:</strong> Strict CSRF tokens, strict rate limiters, and Argon2id/Bcrypt password hashing.</span>
                    </div>
                </div>
            </div>

            <!-- Stats & Benchmarks Card -->
            <div class="lg:col-span-7 grid grid-cols-2 gap-4 sm:gap-6">
                <div class="p-6 sm:p-8 rounded-3xl bg-[#fbfaf8] border border-gray-200">
                    <span class="text-3xl sm:text-4xl font-black text-gray-900 block mb-1">&lt; 150ms</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Average Page Response</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Optimized server rendering and minimal client scripts.</p>
                </div>
                <div class="p-6 sm:p-8 rounded-3xl bg-[#fbfaf8] border border-gray-200">
                    <span class="text-3xl sm:text-4xl font-black text-[#ff6b13] block mb-1">0 Trackers</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Third-Party Scripts</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">No Google Tag Manager, no Facebook Pixel, pure privacy.</p>
                </div>
                <div class="p-6 sm:p-8 rounded-3xl bg-[#fbfaf8] border border-gray-200">
                    <span class="text-3xl sm:text-4xl font-black text-gray-900 block mb-1">100%</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Responsive Design</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Engineered seamlessly for mobile, tablet, and ultra-wide screens.</p>
                </div>
                <div class="p-6 sm:p-8 rounded-3xl bg-[#fbfaf8] border border-gray-200">
                    <span class="text-3xl sm:text-4xl font-black text-[#ff6b13] block mb-1">99.9%</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Uptime Reliability</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Docker, Serverless Vercel, and Cloudinary ready.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 5: CALL TO ACTION -->
<section class="py-16 sm:py-24 bg-[#fbfaf8]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mb-4">Be part of the new chapter in publishing.</h2>
        <p class="text-gray-600 text-base sm:text-lg max-w-xl mx-auto mb-8 leading-relaxed">
            Whether you want to read great minds or write your own stories, BlogForge is open to you today.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="<?= e(app_url('write.php')) ?>" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-sm sm:text-base px-8 py-3.5 shadow-sm hover:shadow hover:brightness-105 transition">
                Start writing a story →
            </a>
            <a href="<?= e(app_url('explore.php')) ?>" class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white text-gray-700 font-semibold text-sm sm:text-base px-7 py-3.5 hover:border-gray-400 transition">
                Explore stories
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
