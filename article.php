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

$authorMeta = is_array($post['author'] ?? null) ? $post['author'] : [];
$authorProfile = BlogForge\Auth::findPublicUser(
    (string)($authorMeta['id'] ?? ''),
    (string)($authorMeta['handle'] ?? '')
);
$authorName = (string)($authorProfile['name'] ?? $authorMeta['name'] ?? 'Author');
$authorHandle = ltrim((string)($authorProfile['handle'] ?? $authorMeta['handle'] ?? ''), '@');
$authorAvatar = avatar_url((string)($authorProfile['avatar'] ?? $authorMeta['avatar'] ?? ''));
$authorProfileUrl = profile_url([
    'id' => (string)($authorProfile['id'] ?? $authorMeta['id'] ?? ''),
    'handle' => $authorHandle,
]);

$currentUser = current_user();
$isAuthor = $currentUser && ((string)($post['author']['id'] ?? '') === (string)$currentUser['id']);
$canManage = $currentUser && ($isAuthor || is_admin());

require __DIR__ . '/partials/header.php';
?>

<article class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
    <!-- Prominent UX Navigation: Back to Stories Button -->
    <div class="mb-6 flex items-center justify-between">
        <a href="<?= e(app_url('explore.php')) ?>" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-gray-600 hover:text-[#ff6b13] transition group">
            <span class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center group-hover:border-orange-300 group-hover:bg-orange-50 transition shadow-sm text-gray-500 group-hover:text-[#ff6b13]">←</span>
            <span>Back to stories</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-700">
                <?= e($post['category']) ?>
            </span>
            <?php if (($post['status'] ?? 'published') === 'draft'): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">
                    Draft
                </span>
            <?php endif; ?>
            <span class="text-gray-400 text-xs">·</span>
            <span class="text-gray-500 text-xs font-medium"><?= e((string)($post['read_time'] ?? 3)) ?> min read</span>
        </div>
    </div>

    <!-- Title and Subtitle -->
    <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-gray-900 leading-[1.15] mb-4">
        <?= e($post['title']) ?>
    </h1>
    <?php if (!empty($post['excerpt'])): ?>
        <p class="text-lg sm:text-xl text-gray-600 font-normal leading-relaxed mb-8">
            <?= e($post['excerpt']) ?>
        </p>
    <?php endif; ?>

    <!-- Author & Action Bar (Redesigned with SVG icons and clear labels) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-5 border-y border-gray-200 mb-8">
        <a href="<?= e($authorProfileUrl) ?>" class="flex items-center gap-3 group" title="View <?= e($authorName) ?>'s profile">
            <?php if ($authorAvatar !== ''): ?>
                <img data-avatar-img src="<?= e($authorAvatar) ?>" alt="<?= e($authorName) ?>" class="w-11 h-11 rounded-full object-cover border border-gray-200 shrink-0 shadow-sm group-hover:ring-2 group-hover:ring-orange-200 transition">
                <span data-avatar-fallback class="hidden w-11 h-11 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                    <?= e(initials($authorName)) ?>
                </span>
            <?php else: ?>
                <span class="w-11 h-11 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm group-hover:opacity-90 transition">
                    <?= e(initials($authorName)) ?>
                </span>
            <?php endif; ?>
            <div>
                <div class="flex items-center gap-1.5">
                    <b class="text-sm font-bold text-gray-900 group-hover:text-[#ff6b13] transition"><?= e($authorName) ?></b>
                    <?php if ($authorHandle !== ''): ?>
                        <span class="text-xs text-gray-400 group-hover:text-[#ff6b13] transition">@<?= e($authorHandle) ?></span>
                    <?php endif; ?>
                </div>
                <span class="text-xs text-gray-500 block"><?= e(format_date($post['published_at'] ?? $post['created_at'] ?? time())) ?></span>
            </div>
        </a>

        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            <?php if ($canManage): ?>
                <a class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:border-gray-400 transition shadow-sm" href="<?= e(app_url('write.php?id=' . urlencode((string)$post['_id']))) ?>" title="Edit Story">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Edit</span>
                </a>
                <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-red-50 text-red-600 border border-red-200 text-xs font-semibold hover:bg-red-100 transition cursor-pointer shadow-sm" data-delete-post="<?= e((string)$post['_id']) ?>" data-post-title="<?= e($post['title']) ?>" title="Delete Story">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Delete</span>
                </button>
            <?php endif; ?>

            <!-- Like Pill -->
            <button class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-gray-200 bg-white hover:border-red-200 hover:bg-red-50 hover:text-red-600 text-xs font-semibold text-gray-700 transition cursor-pointer shadow-sm" data-like="<?= e($postId) ?>" title="Like story">
                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                <span class="font-bold"><?= e((string)($post['likes'] ?? 0)) ?></span>
            </button>

            <!-- Save / Bookmark Pill -->
            <button class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-gray-200 bg-white hover:border-orange-200 hover:bg-orange-50 hover:text-[#ff6b13] text-xs font-semibold text-gray-700 transition cursor-pointer shadow-sm" data-bookmark title="Save to reading list">
                <svg class="w-4 h-4 text-gray-500 bookmark-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                <span class="bookmark-text">Save</span>
            </button>

            <!-- Share Story Pill -->
            <button class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 text-xs font-semibold text-gray-700 transition cursor-pointer shadow-sm" onclick="navigator.clipboard.writeText(location.href);toast('Story link copied to clipboard!');" title="Share story">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span>Share</span>
            </button>
        </div>
    </div>

    <!-- Cover Image -->
    <div class="w-full aspect-[16/9] rounded-3xl overflow-hidden bg-gray-100 mb-10 shadow-sm border border-gray-100">
        <img class="w-full h-full object-cover" src="<?= e(cover_url($post['cover'] ?? null, $post['category'] ?? null)) ?>" data-fallback-cover="<?= e(category_cover($post['category'] ?? null)) ?>" alt="<?= e($post['title']) ?>">
    </div>

    <!-- Story Content -->
    <div class="text-[#25282c] text-lg leading-[1.8] space-y-6 [&>p]:leading-[1.8] [&>blockquote]:border-l-4 [&>blockquote]:border-[#ff6b13] [&>blockquote]:bg-[#faf7f2] [&>blockquote]:p-5 [&>blockquote]:rounded-r-2xl [&>blockquote]:italic [&>blockquote]:text-gray-800 [&>h2]:text-2xl sm:[&>h2]:text-3xl [&>h2]:font-extrabold [&>h2]:mt-10 [&>h2]:mb-4 [&>h2]:text-gray-900 [&>h3]:text-xl sm:[&>h3]:text-2xl [&>h3]:font-bold [&>h3]:mt-8 [&>h3]:mb-3 [&>h3]:text-gray-900 [&>code]:bg-[#f2efe9] [&>code]:px-1.5 [&>code]:py-0.5 [&>code]:rounded [&>code]:text-sm [&>code]:font-mono [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:space-y-2 [&>ol]:list-decimal [&>ol]:pl-6 [&>ol]:space-y-2">
        <?= body_to_html((string)$post['body']) ?>
    </div>

    <!-- Tags -->
    <?php if (!empty($post['tags']) && is_array($post['tags'])): ?>
        <div class="flex flex-wrap items-center gap-2 mt-12 pt-6 border-t border-gray-100">
            <?php foreach ($post['tags'] as $tag): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                    #<?= e(trim((string)$tag)) ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Bottom Navigation Callout -->
    <div class="mt-12 p-6 rounded-3xl bg-[#fbfaf8] border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="<?= e($authorProfileUrl) ?>" class="shrink-0 group" title="View <?= e($authorName) ?>'s profile">
                <?php if ($authorAvatar !== ''): ?>
                    <img data-avatar-img src="<?= e($authorAvatar) ?>" alt="<?= e($authorName) ?>" class="w-10 h-10 rounded-full object-cover border border-gray-200 group-hover:ring-2 group-hover:ring-orange-200 transition">
                    <span data-avatar-fallback class="hidden w-10 h-10 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white items-center justify-center font-bold text-xs">
                        <?= e(initials($authorName)) ?>
                    </span>
                <?php else: ?>
                    <span class="w-10 h-10 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-xs group-hover:opacity-90 transition">
                        <?= e(initials($authorName)) ?>
                    </span>
                <?php endif; ?>
            </a>
            <div>
                <b class="text-sm text-gray-900 block">
                    Written by <a href="<?= e($authorProfileUrl) ?>" class="hover:text-[#ff6b13] transition"><?= e($authorName) ?></a>
                </b>
                <span class="text-xs text-gray-500">Read more stories in <a href="<?= e(app_url('explore.php?category=' . urlencode($post['category'] ?? ''))) ?>" class="text-[#ff6b13] font-semibold hover:underline"><?= e($post['category'] ?? 'General') ?></a></span>
            </div>
        </div>
        <a href="<?= e(app_url('explore.php')) ?>" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-5 py-2 text-xs font-semibold text-gray-700 hover:border-gray-400 transition shadow-sm">
            ← Back to all stories
        </a>
    </div>

    <!-- Comments / Responses -->
    <section class="mt-14 pt-10 border-t border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Responses (<span data-comments-count><?= count($comments) ?></span>)</h2>

        <?php if (is_logged_in()): ?>
            <form class="space-y-3" data-comment-form data-post-id="<?= e($postId) ?>">
                <textarea class="w-full min-h-[110px] p-4 bg-white border border-gray-200 rounded-2xl text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition shadow-sm resize-y" placeholder="What are your thoughts? Respond respectfully…" required></textarea>
                <div class="text-right">
                    <button class="inline-flex items-center justify-center rounded-full bg-gray-900 text-white font-semibold text-sm px-6 py-2.5 hover:bg-black transition shadow-sm cursor-pointer" type="submit">Publish response</button>
                </div>
            </form>
        <?php else: ?>
            <div class="p-5 bg-orange-50/60 border border-orange-100 rounded-2xl text-sm text-gray-600 flex items-center justify-between gap-4">
                <span>Sign in to join the conversation and leave a response.</span>
                <a href="<?= e(app_url('auth.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'))) ?>" class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-xs shadow-sm hover:brightness-105 transition shrink-0">Sign in</a>
            </div>
        <?php endif; ?>

        <div class="mt-8 divide-y divide-gray-100" data-comments-container>
            <?php if (empty($comments)): ?>
                <p class="text-sm text-gray-500 py-6" data-no-comments>No responses yet. Be the first to share your perspective!</p>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <?php
                    $cAuthor = is_array($c['author'] ?? null) ? $c['author'] : [];
                    $cName = (string)($cAuthor['name'] ?? 'Reader');
                    $cHandle = ltrim((string)($cAuthor['handle'] ?? ''), '@');
                    $cAvatar = avatar_url((string)($cAuthor['avatar'] ?? ''));
                    $cProfileUrl = profile_url($cAuthor);
                    ?>
                    <div class="py-5 flex items-start gap-3.5">
                        <a href="<?= e($cProfileUrl) ?>" class="shrink-0 group" title="View <?= e($cName) ?>'s profile">
                            <?php if ($cAvatar !== ''): ?>
                                <img data-avatar-img src="<?= e($cAvatar) ?>" alt="<?= e($cName) ?>" class="w-9 h-9 rounded-full object-cover border border-gray-200 shadow-sm group-hover:ring-2 group-hover:ring-orange-200 transition">
                                <span data-avatar-fallback class="hidden w-9 h-9 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white items-center justify-center font-bold text-xs shadow-sm">
                                    <?= e(initials($cName)) ?>
                                </span>
                            <?php else: ?>
                                <span class="w-9 h-9 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-xs shadow-sm group-hover:opacity-90 transition">
                                    <?= e(initials($cName)) ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <div class="flex-1 min-w-0 space-y-1.5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <a href="<?= e($cProfileUrl) ?>" class="inline-flex items-center gap-1.5 group">
                                    <b class="text-sm font-semibold text-gray-900 group-hover:text-[#ff6b13] transition"><?= e($cName) ?></b>
                                    <?php if ($cHandle !== ''): ?>
                                        <span class="text-xs text-gray-400 group-hover:text-[#ff6b13] transition">@<?= e($cHandle) ?></span>
                                    <?php endif; ?>
                                </a>
                                <small class="text-xs text-gray-400"><?= e(format_date($c['created_at'] ?? time())) ?></small>
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed"><?= e((string)$c['body']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</article>

<?php require __DIR__ . '/partials/footer.php'; ?>
