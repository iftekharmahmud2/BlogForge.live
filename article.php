<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$key = isset($_GET['id']) && is_string($_GET['id']) ? trim($_GET['id']) : '';
$post = find_post($key);

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Story not found — BlogForge';
    require __DIR__ . '/partials/header.php';
    echo '<div class="article-wrap"><h1>Story not found.</h1><p>The story may be deleted, private, or does not exist.</p><a class="btn" href="' . e(app_url('explore.php')) . '">← Back to stories</a></div>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

$pageTitle = $post['title'] . ' — BlogForge';
$postId = (string)($post['_id'] ?? $post['slug']);

$comments = (new BlogForge\Repositories\CommentRepository())->forPost($postId);

$currentUser = current_user();
$isAuthor = $currentUser && ((string)($post['author']['id'] ?? '') === (string)$currentUser['id']);
$canManage = $currentUser && ($isAuthor || is_admin());

require __DIR__ . '/partials/header.php';
?>

<article class="article-wrap">
    <div class="article-kicker">
        <span class="tag"><?= e($post['category']) ?></span>
        <?php if (($post['status'] ?? 'published') === 'draft'): ?>
            <span class="status draft">Draft</span>
        <?php endif; ?>
        <span class="meta"><?= e((string)$post['read_time']) ?> min read</span>
    </div>

    <h1 class="article-title"><?= e($post['title']) ?></h1>
    <?php if (!empty($post['excerpt'])): ?>
        <p class="article-deck"><?= e($post['excerpt']) ?></p>
    <?php endif; ?>

    <div class="article-author">
        <span class="mini-avatar"><?= e(initials($post['author']['name'] ?? 'Author')) ?></span>
        <div>
            <b><?= e($post['author']['name'] ?? 'Author') ?></b>
            <div class="meta"><?= e(format_date($post['published_at'] ?? $post['created_at'] ?? time())) ?></div>
        </div>

        <div class="article-actions">
            <?php if ($canManage): ?>
                <a class="btn btn-sm btn-outline" href="<?= e(app_url('write.php?id=' . urlencode((string)$post['_id']))) ?>" title="Edit Story">Edit</a>
                <button class="btn btn-sm btn-danger" data-delete-post="<?= e((string)$post['_id']) ?>" data-post-title="<?= e($post['title']) ?>" title="Delete Story">Delete</button>
            <?php endif; ?>
            <button class="icon-btn" data-like="<?= e($postId) ?>" title="Like">♥ <span><?= e((string)($post['likes'] ?? 0)) ?></span></button>
            <button class="icon-btn" data-bookmark title="Save">♡</button>
            <button class="icon-btn" onclick="navigator.clipboard.writeText(location.href);toast('Link copied to clipboard');" title="Share">↗</button>
        </div>
    </div>

    <img class="article-cover" src="<?= e(cover_url($post['cover'] ?? null)) ?>" alt="<?= e($post['title']) ?>">

    <div class="article-content">
        <?= body_to_html((string)$post['body']) ?>
    </div>

    <section class="comments">
        <h2>Responses (<?= count($comments) ?>)</h2>

        <?php if (is_logged_in()): ?>
            <form class="comment-form" data-comment-form data-post-id="<?= e($postId) ?>">
                <textarea placeholder="What are your thoughts? Respond respectfully…" required></textarea>
                <div style="margin-top:10px;text-align:right;">
                    <button class="btn btn-dark" type="submit">Publish response</button>
                </div>
            </form>
        <?php else: ?>
            <p style="padding:15px;background:#faf8f5;border-radius:12px;color:var(--muted)">
                <a href="<?= e(app_url('auth.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'))) ?>" style="color:var(--orange);font-weight:600">Sign in</a> to leave a response.
            </p>
        <?php endif; ?>

        <div data-comments-container style="margin-top:25px;">
            <?php if (empty($comments)): ?>
                <p data-no-comments style="color:var(--muted);">No responses yet. Be the first to share your perspective!</p>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <div class="comment">
                        <b><?= e((string)($c['author']['name'] ?? 'Reader')) ?></b>
                        <p><?= e((string)$c['body']) ?></p>
                        <small><?= e(format_date($c['created_at'] ?? time())) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</article>

<?php require __DIR__ . '/partials/footer.php'; ?>
