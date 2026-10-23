<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'Explore stories — BlogForge';

$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$cat = isset($_GET['category']) && is_string($_GET['category']) ? trim($_GET['category']) : '';

// Execute optimized database query with server-side filters
$posts = public_posts(50, $cat !== '' ? $cat : null, $q !== '' ? $q : null);

$topics = ['Technology', 'Lifestyle', 'Business', 'Education', 'Health', 'Travel', 'Culture', 'Self Improvement'];

require __DIR__ . '/partials/header.php';
?>

<section class="border-b border-[#e9e5df] bg-[#fbfaf8] py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="uppercase tracking-widest text-xs font-bold text-[#ff6b13] block mb-2">Discover</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-gray-900 mb-3">Stories worth your time.</h1>
        <p class="text-gray-500 text-base sm:text-lg max-w-2xl leading-relaxed">Fresh thinking from independent writers across technology, culture, work, life, and everything between.</p>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Filter Toolbar -->
    <form class="flex flex-col sm:flex-row items-center gap-3 mb-10" method="GET" action="<?= e(app_url('explore.php')) ?>">
        <div class="w-full sm:flex-1 relative">
            <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
            <input name="q" class="w-full bg-white border border-[#e9e5df] rounded-full pl-11 pr-4 py-3 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 shadow-sm transition" placeholder="Search by title, topic or idea…" value="<?= e($q) ?>">
        </div>
        <select class="w-full sm:w-auto bg-white border border-[#e9e5df] rounded-full px-5 py-3 text-sm text-gray-700 outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 shadow-sm transition cursor-pointer" name="category" onchange="this.form.submit()">
            <option value="">All topics</option>
            <?php foreach ($topics as $t): ?>
                <option value="<?= e($t) ?>" <?= strcasecmp($cat, $t) === 0 ? 'selected' : '' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Articles Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-12 gap-y-8">
        <?php foreach ($posts as $p): ?>
            <article class="flex flex-col-reverse sm:flex-row items-start sm:items-center justify-between gap-6 pt-6 border-t border-[#e9e5df] group">
                <div class="flex-1 min-w-0">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-700 mb-2.5"><?= e($p['category']) ?></span>
                    <a href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>" class="block">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 group-hover:text-[#ff6b13] transition-colors leading-snug mb-2 line-clamp-2"><?= e($p['title']) ?></h2>
                    </a>
                    <p class="text-gray-500 text-sm line-clamp-2 mb-4 leading-relaxed"><?= e($p['excerpt']) ?></p>
                    <div class="flex items-center gap-3 text-xs text-gray-400 font-medium">
                        <span class="w-6 h-6 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-[9px]"><?= e(initials($p['author']['name'] ?? 'Author')) ?></span>
                        <span class="text-gray-700"><?= e($p['author']['name'] ?? 'Author') ?></span>
                        <span>·</span>
                        <span><?= e((string)$p['read_time']) ?> min read</span>
                    </div>
                </div>
                <a href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>" class="shrink-0 w-full sm:w-44 h-36 rounded-2xl overflow-hidden bg-gray-100 shadow-sm group-hover:shadow transition">
                    <img src="<?= e(cover_url($p['cover'] ?? null)) ?>" alt="<?= e($p['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </a>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (empty($posts)): ?>
        <div class="py-20 text-center text-gray-500">
            <h3 class="text-xl font-bold text-gray-800 mb-2">No stories matched your search.</h3>
            <p class="text-sm">Try searching for a different keyword or explore all topics.</p>
            <a class="inline-flex items-center justify-center rounded-full border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:border-gray-400 mt-4 transition" href="<?= e(app_url('explore.php')) ?>">Clear filters</a>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
