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

<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Discover</span>
        <h1>Stories worth your time.</h1>
        <p>Fresh thinking from independent writers across technology, culture, work, life, and everything between.</p>
    </div>
</section>

<div class="container">
    <form class="explore-toolbar" method="GET" action="<?= e(app_url('explore.php')) ?>">
        <div class="search-large">
            <input name="q" placeholder="Search by title, topic or idea…" value="<?= e($q) ?>">
        </div>
        <select class="filter-select" name="category" onchange="this.form.submit()">
            <option value="">All topics</option>
            <?php foreach ($topics as $t): ?>
                <option value="<?= e($t) ?>" <?= strcasecmp($cat, $t) === 0 ? 'selected' : '' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="explore-grid">
        <?php foreach ($posts as $p): ?>
            <article class="explore-row">
                <div>
                    <span class="tag"><?= e($p['category']) ?></span>
                    <a href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>">
                        <h2><?= e($p['title']) ?></h2>
                    </a>
                    <p><?= e($p['excerpt']) ?></p>
                    <div class="meta">
                        <span class="mini-avatar"><?= e(initials($p['author']['name'] ?? 'Author')) ?></span>
                        <?= e($p['author']['name'] ?? 'Author') ?> · <?= e((string)$p['read_time']) ?> min read
                    </div>
                </div>
                <a href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>">
                    <img src="<?= e(cover_url($p['cover'] ?? null)) ?>" alt="<?= e($p['title']) ?>">
                </a>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (empty($posts)): ?>
        <div style="padding: 60px 0; text-align: center; color: var(--muted);">
            <h3>No stories matched your search.</h3>
            <p>Try searching for a different keyword or explore all topics.</p>
            <a class="btn" href="<?= e(app_url('explore.php')) ?>" style="margin-top:15px;">Clear filters</a>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
