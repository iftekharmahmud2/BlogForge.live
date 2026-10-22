<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_login();

$u = current_user();
$pageTitle = $u['name'] . ' — BlogForge';

$full = BlogForge\Auth::fetchUser((string)$u['id']) ?? $u;
$bio = trim((string)($full['bio'] ?? ''));
$avatarUrl = trim((string)($full['avatar'] ?? ''));

$postRepo = new BlogForge\Repositories\PostRepository();
$posts = $postRepo->forAuthor((string)$u['id'], 100);

require __DIR__ . '/partials/header.php';
?>

<section class="profile-head">
    <div class="container profile-box">
        <?php if ($avatarUrl !== ''): ?>
            <img class="profile-avatar profile-avatar-img" src="<?= e($avatarUrl) ?>" alt="<?= e($u['name']) ?>">
        <?php else: ?>
            <div class="profile-avatar"><?= e(initials($u['name'])) ?></div>
        <?php endif; ?>
        <div>
            <h1><?= e($u['name']) ?></h1>
            <p>@<?= e($u['handle']) ?> · Author on BlogForge</p>
            <?php if ($bio !== ''): ?>
                <p class="profile-bio"><?= e($bio) ?></p>
            <?php else: ?>
                <p class="profile-bio profile-bio-empty">No bio yet — <a href="<?= e(app_url('profile-edit.php')) ?>">add one</a> so readers know who you are.</p>
            <?php endif; ?>
            <div class="profile-stats">
                <b><?= count($posts) ?> stories</b>
                <span>Role: <?= e(ucfirst($u['role'] ?? 'author')) ?></span>
            </div>
        </div>
        <div class="profile-actions">
            <a class="btn" href="<?= e(app_url('profile-edit.php')) ?>">Edit profile</a>
            <a class="btn btn-primary" href="<?= e(app_url('write.php')) ?>">Write a story</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Your stories & drafts</h2>
        </div>

        <?php if (empty($posts)): ?>
            <div style="padding: 40px 0; color: var(--muted); background:#faf8f5; border-radius:16px; text-align:center;">
                <h3>No stories created yet.</h3>
                <p>Ready to share your voice with the world?</p>
                <a class="btn btn-primary" href="<?= e(app_url('write.php')) ?>" style="margin-top:12px;">Start writing your first story</a>
            </div>
        <?php else: ?>
            <div class="story-grid">
                <?php foreach ($posts as $p): ?>
                    <article class="story-card">
                        <img class="story-cover" src="<?= e(cover_url($p['cover'] ?? null)) ?>" alt="<?= e($p['title']) ?>">
                        <div class="story-body">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                <span class="tag"><?= e($p['category']) ?></span>
                                <span class="status <?= ($p['status'] ?? '') === 'draft' ? 'draft' : 'approved' ?>">
                                    <?= e(ucfirst((string)($p['status'] ?? 'draft'))) ?>
                                </span>
                            </div>
                            <h3><?= e((string)$p['title']) ?></h3>
                            <p><?= e((string)$p['excerpt']) ?></p>
                            <div class="meta" style="margin-bottom:12px;">
                                <span><?= e(format_date($p['published_at'] ?? $p['created_at'] ?? time())) ?></span>
                                <span>· <?= e((string)($p['read_time'] ?? 1)) ?> min read</span>
                                <span>· ♥ <?= e((string)($p['likes'] ?? 0)) ?></span>
                            </div>
                        </div>
                        <div class="story-card-actions">
                            <a class="btn btn-sm btn-outline" href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>">View</a>
                            <a class="btn btn-sm btn-secondary" href="<?= e(app_url('write.php?id=' . urlencode((string)$p['_id']))) ?>">Edit</a>
                            <button class="btn btn-sm btn-danger" data-delete-post="<?= e((string)$p['_id']) ?>" data-post-title="<?= e($p['title']) ?>">Delete</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
