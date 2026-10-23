<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$key = isset($_GET['id']) && is_string($_GET['id']) ? trim($_GET['id']) : '';
$post = find_post($key);

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Story not found — BlogForge';
    require __DIR__ . '/partials/header.php';
    echo '<div class="max-w-3xl mx-auto px-4 py-20 text-center"><h1 class="text-3xl font-bold mb-3">Story not found.</h1><p class="text-gray-500 mb-6">The story may be deleted, private, or does not exist.</p><a class="inline-flex items-center justify-center rounded-full border border-gray-300 px-6 py-2.5 text-sm font-semibold hover:border-gray-400" href="' . e(app_url('explore.php')) . '">← Back to stories</a></div>';
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

<article class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
    <!-- Story Kicker -->
    <div class="flex items-center gap-3 text-xs font-semibold mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-orange-50 text-orange-700"><?= e($post['category']) ?></span>
        <?php if (($post['status'] ?? 'published') === 'draft'): ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 font-medium">Draft</span>
        <?php endif; ?>
        <span class="text-gray-400 font-normal">·</span>
        <span class="text-gray-500 font-normal"><?= e((string)$post['read_time']) ?> min read</span>
    </div>

    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-gray-900 leading-[1.15] mb-4"><?= e($post['title']) ?></h1>
    <?php if (!empty($post['excerpt'])): ?>
        <p class="text-lg sm:text-xl text-gray-500 font-normal leading-relaxed mb-8"><?= e($post['excerpt']) ?></p>
    <?php endif; ?>

    <!-- Author & Story Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-5 border-y border-[#e9e5df] mb-8">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-xs shrink-0"><?= e(initials($post['author']['name'] ?? 'Author')) ?></span>
            <div>
                <b class="block text-sm font-semibold text-gray-900"><?= e($post['author']['name'] ?? 'Author') ?></b>
                <span class="text-xs text-gray-400"><?= e(format_date($post['published_at'] ?? $post['created_at'] ?? time())) ?></span>
            </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto">
            <?php if ($canManage): ?>
                <a class="inline-flex items-center px-3 py-1.5 rounded-full border border-gray-300 text-xs font-semibold text-gray-700 hover:border-gray-400 transition" href="<?= e(app_url('write.php?id=' . urlencode((string)$post['_id']))) ?>" title="Edit Story">Edit</a>
                <button class="inline-flex items-center px-3 py-1.5 rounded-full bg-red-50 text-red-600 border border-red-200 text-xs font-semibold hover:bg-red-100 transition cursor-pointer" data-delete-post="<?= e((string)$post['_id']) ?>" data-post-title="<?= e($post['title']) ?>" title="Delete Story">Delete</button>
            <?php endif; ?>
            <button class="w-9 h-9 rounded-full border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-xs font-semibold text-gray-600 transition cursor-pointer" data-like="<?= e($postId) ?>" title="Like">♥ <span class="ml-1 text-[11px]"><?= e((string)($post['likes'] ?? 0)) ?></span></button>
            <button class="w-9 h-9 rounded-full border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-xs text-gray-600 transition cursor-pointer" data-bookmark title="Save">♡</button>
            <button class="w-9 h-9 rounded-full border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-xs text-gray-600 transition cursor-pointer" onclick="navigator.clipboard.writeText(location.href);toast('Link copied to clipboard');" title="Share">↗</button>
        </div>
    </div>

    <!-- Cover Image -->
    <div class="w-full aspect-[16/9] rounded-2xl overflow-hidden bg-gray-100 mb-10 shadow-sm">
        <img class="w-full h-full object-cover" src="<?= e(cover_url($post['cover'] ?? null)) ?>" alt="<?= e($post['title']) ?>">
    </div>

    <!-- Content -->
    <div class="text-[#25282c] text-lg leading-[1.8] space-y-6 [&>p]:leading-[1.8] [&>blockquote]:border-l-4 [&>blockquote]:border-[#ff6b13] [&>blockquote]:bg-[#faf7f2] [&>blockquote]:p-4 [&>blockquote]:rounded-r-xl [&>blockquote]:italic [&>blockquote]:text-gray-700 [&>h2]:text-2xl [&>h2]:font-bold [&>h2]:mt-8 [&>h2]:mb-3 [&>h3]:text-xl [&>h3]:font-bold [&>h3]:mt-6 [&>h3]:mb-2 [&>code]:bg-[#f2efe9] [&>code]:px-1.5 [&>code]:py-0.5 [&>code]:rounded [&>code]:text-sm [&>code]:font-mono">
        <?= body_to_html((string)$post['body']) ?>
    </div>

    <!-- Comments / Responses -->
    <section class="mt-16 pt-10 border-t border-[#e9e5df]">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Responses (<?= count($comments) ?>)</h2>

        <?php if (is_logged_in()): ?>
            <form class="space-y-3" data-comment-form data-post-id="<?= e($postId) ?>">
                <textarea class="w-full min-h-[110px] p-4 bg-white border border-[#e9e5df] rounded-2xl text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition shadow-sm resize-y" placeholder="What are your thoughts? Respond respectfully…" required></textarea>
                <div class="text-right">
                    <button class="inline-flex items-center justify-center rounded-full bg-gray-900 text-white font-semibold text-sm px-5 py-2.5 hover:bg-black transition shadow-sm cursor-pointer" type="submit">Publish response</button>
                </div>
            </form>
        <?php else: ?>
            <div class="p-4 bg-orange-50/50 border border-orange-100 rounded-2xl text-sm text-gray-600">
                <a href="<?= e(app_url('auth.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'))) ?>" class="text-[#ff6b13] font-semibold hover:underline">Sign in</a> to leave a response.
            </div>
        <?php endif; ?>

        <div class="mt-8 divide-y divide-[#e9e5df]" data-comments-container>
            <?php if (empty($comments)): ?>
                <p class="text-sm text-gray-500 py-4" data-no-comments>No responses yet. Be the first to share your perspective!</p>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <div class="py-5 space-y-1.5">
                        <b class="block text-sm font-semibold text-gray-900"><?= e((string)($c['author']['name'] ?? 'Reader')) ?></b>
                        <p class="text-sm text-gray-700 leading-relaxed"><?= e((string)$c['body']) ?></p>
                        <small class="text-xs text-gray-400 block"><?= e(format_date($c['created_at'] ?? time())) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</article>

<?php require __DIR__ . '/partials/footer.php'; ?>
