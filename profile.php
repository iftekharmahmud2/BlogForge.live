<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$reqId = isset($_GET['id']) && is_string($_GET['id']) ? trim($_GET['id']) : '';
$reqHandle = isset($_GET['handle']) && is_string($_GET['handle']) ? ltrim(trim($_GET['handle']), '@') : '';
$isPublicLookup = ($reqId !== '' || $reqHandle !== '');

$viewer = current_user();

if (!$isPublicLookup) {
    require_login();
    $viewer = current_user();
    $u = BlogForge\Auth::fetchUser((string)$viewer['id']) ?? $viewer;
    $isOwnProfile = true;
} else {
    $u = BlogForge\Auth::findPublicUser($reqId, $reqHandle);
    if (!$u) {
        http_response_code(404);
        $pageTitle = 'Profile not found — BlogForge';
        require __DIR__ . '/partials/header.php';
        echo '<div class="max-w-3xl mx-auto px-4 py-20 text-center"><h1 class="text-3xl font-bold text-gray-900 mb-3">Profile not found.</h1><p class="text-gray-500 mb-6">This author or reader profile could not be found.</p><a class="inline-flex items-center justify-center rounded-full border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:border-gray-400 transition" href="' . e(app_url('explore.php')) . '">← Back to stories</a></div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }

    $profileIdCheck = (string)($u['id'] ?? '');
    $profileHandleCheck = (string)($u['handle'] ?? '');
    $viewerId = (string)($viewer['id'] ?? '');
    $viewerHandle = (string)($viewer['handle'] ?? '');

    $isOwnProfile = (bool)($viewer && (
        ($viewerId !== '' && $viewerId === $profileIdCheck) ||
        ($viewerHandle !== '' && $profileHandleCheck !== '' && strcasecmp($viewerHandle, $profileHandleCheck) === 0)
    ));

    if ($isOwnProfile && $viewerId !== '') {
        $u = BlogForge\Auth::fetchUser($viewerId) ?? $u;
    }
}

$profileName = (string)($u['name'] ?? 'Author');
$profileHandle = ltrim((string)($u['handle'] ?? ''), '@');
$profileId = (string)($u['id'] ?? '');
$pageTitle = $profileName . ' — BlogForge';

$bio = trim((string)($u['bio'] ?? ''));
$avatarUrl = avatar_url((string)($u['avatar'] ?? ''));

$postRepo = new BlogForge\Repositories\PostRepository();
$posts = $postRepo->forAuthor($profileId, 100, !$isOwnProfile, $profileHandle);

$commentRepo = new BlogForge\Repositories\CommentRepository();
$recentComments = $commentRepo->forAuthor($profileId, $profileHandle, 12);

$roleLabel = ucfirst((string)($u['role'] ?? 'author'));

require __DIR__ . '/partials/header.php';
?>

<section class="border-b border-[#e9e5df] bg-[#fbfaf8] py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <?php if ($avatarUrl !== ''): ?>
                    <img data-avatar-img class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover shadow-sm shrink-0 border-2 border-white ring-2 ring-orange-100" src="<?= e($avatarUrl) ?>" alt="<?= e($profileName) ?>">
                    <div data-avatar-fallback class="hidden w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white items-center justify-center font-bold text-2xl sm:text-3xl shrink-0 shadow-sm"><?= e(initials($profileName)) ?></div>
                <?php else: ?>
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shrink-0 shadow-sm"><?= e(initials($profileName)) ?></div>
                <?php endif; ?>
                <div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight"><?= e($profileName) ?></h1>
                    <p class="text-sm text-gray-500 mt-1">
                        <?= $profileHandle !== '' ? '@' . e($profileHandle) . ' · ' : '' ?><?= count($posts) > 0 ? 'Author on BlogForge' : 'Community Member on BlogForge' ?>
                    </p>
                    <?php if ($bio !== ''): ?>
                        <p class="text-sm text-gray-700 mt-2.5 max-w-xl leading-relaxed"><?= e($bio) ?></p>
                    <?php elseif ($isOwnProfile): ?>
                        <p class="text-sm text-gray-400 mt-2.5">No bio yet — <a href="<?= e(app_url('profile-edit.php')) ?>" class="text-[#ff6b13] font-medium hover:underline">add one</a> so readers know who you are.</p>
                    <?php endif; ?>
                    <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-gray-500 mt-3">
                        <b class="text-gray-900 font-semibold"><?= count($posts) ?> <?= count($posts) === 1 ? 'story' : 'stories' ?></b>
                        <span>·</span>
                        <b class="text-gray-900 font-semibold"><?= count($recentComments) ?> <?= count($recentComments) === 1 ? 'response' : 'responses' ?></b>
                        <span>·</span>
                        <span>Role: <?= e($roleLabel) ?></span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <?php if ($isOwnProfile): ?>
                    <a class="flex-1 sm:flex-initial text-center rounded-full border border-gray-300 px-5 py-2.5 text-xs font-semibold text-gray-700 hover:border-gray-400 bg-white transition" href="<?= e(app_url('profile-edit.php')) ?>">Edit profile</a>
                    <a class="flex-1 sm:flex-initial text-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-5 py-2.5 text-xs font-semibold shadow-sm hover:shadow transition" href="<?= e(app_url('write.php')) ?>">Write a story</a>
                <?php else: ?>
                    <a class="flex-1 sm:flex-initial text-center rounded-full border border-gray-300 px-5 py-2.5 text-xs font-semibold text-gray-700 hover:border-gray-400 bg-white transition" href="<?= e(app_url('explore.php')) ?>">← Browse all stories</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-14">
    <div>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight"><?= $isOwnProfile ? 'Your stories &amp; drafts' : 'Published stories' ?></h2>
        </div>

        <?php if (empty($posts)): ?>
            <div class="p-12 text-center bg-[#faf8f5] border border-[#e9e5df] rounded-3xl text-gray-500">
                <?php if ($isOwnProfile): ?>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No stories created yet.</h3>
                    <p class="text-sm mb-5">Ready to share your voice with the world?</p>
                    <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-6 py-2.5 text-sm font-semibold shadow-sm hover:shadow transition" href="<?= e(app_url('write.php')) ?>">Start writing your first story</a>
                <?php else: ?>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No published stories yet.</h3>
                    <p class="text-sm"><?= e($profileName) ?> hasn't published any stories yet.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($posts as $p): ?>
                    <div data-post-card class="bg-white border border-[#e9e5df] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                        <div>
                            <a href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>" class="block aspect-[16/10] overflow-hidden bg-gray-100">
                                <img src="<?= e(cover_url($p['cover'] ?? null, $p['category'] ?? null)) ?>" data-fallback-cover="<?= e(category_cover($p['category'] ?? null)) ?>" alt="<?= e($p['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </a>
                            <div class="p-5">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-50 text-orange-700"><?= e($p['category']) ?></span>
                                    <?php if ($isOwnProfile): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?= ($p['status'] ?? '') === 'draft' ? 'bg-gray-100 text-gray-600' : 'bg-emerald-50 text-emerald-700' ?>">
                                            <?= e((string)$p['status']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-[11px] text-gray-400"><?= e((string)($p['read_time'] ?? 4)) ?> min read</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>" class="block">
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#ff6b13] transition line-clamp-2 leading-snug mb-2"><?= e($p['title']) ?></h3>
                                </a>
                                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-3"><?= e($p['excerpt']) ?></p>
                                <span class="text-[11px] text-gray-400 block"><?= e(format_date($p['published_at'] ?? $p['created_at'] ?? time())) ?></span>
                            </div>
                        </div>
                        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between gap-2 bg-[#faf9f6]">
                            <a href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>" class="text-xs font-semibold text-gray-600 hover:text-gray-900"><?= $isOwnProfile ? 'View' : 'Read story →' ?></a>
                            <?php if ($isOwnProfile): ?>
                                <div class="flex items-center gap-2">
                                    <a href="<?= e(app_url('write.php?id=' . urlencode((string)$p['_id']))) ?>" class="text-xs font-semibold text-[#ff6b13] hover:underline">Edit</a>
                                    <button class="text-xs font-semibold text-red-600 hover:underline cursor-pointer" data-delete-post="<?= e((string)$p['_id']) ?>" data-post-title="<?= e($p['title']) ?>">Delete</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($recentComments)): ?>
        <div>
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Recent responses</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($recentComments as $rc): ?>
                    <a href="<?= e(app_url('article.php?id=' . urlencode((string)$rc['post_slug']))) ?>" class="block p-5 bg-white border border-[#e9e5df] rounded-2xl hover:border-orange-200 hover:shadow-sm transition group">
                        <div class="flex items-center justify-between gap-2 text-xs text-gray-400 mb-2">
                            <span class="font-semibold text-[#ff6b13] truncate group-hover:underline">On: <?= e((string)$rc['post_title']) ?></span>
                            <span class="shrink-0"><?= e(format_date($rc['created_at'] ?? time())) ?></span>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed line-clamp-3"><?= e((string)$rc['body']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
