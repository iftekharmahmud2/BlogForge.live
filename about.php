<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'About — BlogForge';
require __DIR__ . '/partials/header.php';
?>

<section class="border-b border-[#e9e5df] bg-[#fbfaf8] py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="uppercase tracking-widest text-xs font-bold text-[#ff6b13] block mb-2">About BlogForge</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-gray-900 mb-3">Built for ideas that deserve to travel.</h1>
        <p class="text-gray-500 text-base sm:text-lg max-w-2xl leading-relaxed">BlogForge.live is a clean publishing platform for writers and readers who value useful, thoughtful stories over noise.</p>
    </div>
</section>

<section class="py-12 sm:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-4">Read deeply. Write clearly. Connect thoughtfully.</h2>
        <p class="text-base sm:text-lg text-gray-600 leading-relaxed mb-4">
            The product is intentionally editorial: strong typography, focused reading, topic discovery, author profiles, responses, bookmarks, and a distraction-free writing experience.
        </p>
        <p class="text-base sm:text-lg text-gray-600 leading-relaxed mb-8">
            Under the hood, BlogForge is built on modern PHP and MongoDB. It features complete CRUD workflows, author session management, and robust NoSQL protections.
        </p>
        <div class="flex flex-wrap items-center gap-3">
            <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-sm px-6 py-3 shadow-sm hover:shadow transition" href="<?= e(app_url('explore.php')) ?>">Explore stories</a>
            <a class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:border-gray-400 transition" href="<?= e(app_url('write.php')) ?>">Start writing</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
