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

<section class="border-b border-[#e9e5df] bg-[#fbfaf8] py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <?php if ($avatarUrl !== ''): ?>
                    <img class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover shadow-sm shrink-0 border-2 border-white ring-2 ring-orange-100" src="<?= e($avatarUrl) ?>" alt="<?= e($u['name']) ?>">
                <?php else: ?>
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shrink-0 shadow-sm"><?= e(initials($u['name'])) ?></div>
                <?php endif; ?>
                <div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight"><?= e($u['name']) ?></h1>
                    <p class="text-sm text-gray-500 mt-1">@<?= e($u['handle']) ?> · Author on BlogForge</p>
                    <?php if ($bio !== ''): ?>
                        <p class="text-sm text-gray-700 mt-2.5 max-w-xl leading-relaxed"><?= e($bio) ?></p>
                    <?php else: ?>
                        <p class="text-sm text-gray-400 mt-2.5">No bio yet — <a href="<?= e(app_url('profile-edit.php')) ?>" class="text-[#ff6b13] font-medium hover:underline">add one</a> so readers know who you are.</p>
                    <?php endif; ?>
                    <div class="flex items-center gap-4 text-xs font-medium text-gray-500 mt-3">
                        <b class="text-gray-900 font-semibold"><?= count($posts) ?> stories</b>
                        <span>·</span>
                        <span>Role: <?= e(ucfirst($u['role'] ?? 'author')) ?></span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <a class="flex-1 sm:flex-initial text-center rounded-full border border-gray-300 px-5 py-2.5 text-xs font-semibold text-gray-700 hover:border-gray-400 bg-white transition" href="<?= e(app_url('profile-edit.php')) ?>">Edit profile</a>
                <a class="flex-1 sm:flex-initial text-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-5 py-2.5 text-xs font-semibold shadow-sm hover:shadow transition" href="<?= e(app_url('write.php')) ?>">Write a story</a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Your stories &amp; drafts</h2>
    </div>

    <?php if (empty($posts)): ?>
        <div class="p-12 text-center bg-[#faf8f5] border border-[#e9e5df] rounded-3xl text-gray-500">
            <h3 class="text-xl font-bold text-gray-800 mb-2">No stories created yet.</h3>
            <p class="text-sm mb-5">Ready to share your voice with the world?</p>
            <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-6 py-2.5 text-sm font-semibold shadow-sm hover:shadow transition" href="<?= e(app_url('write.php')) ?>">Start writing your first story</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($posts as $p): ?>
                <div class="bg-white border border-[#e9e5df] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                            <img src="<?= e(cover_url($p['cover'] ?? null)) ?>" alt="<?= e($p['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-50 text-orange-700"><?= e($p['category']) ?></span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?= ($p['status'] ?? '') === 'draft' ? 'bg-gray-100 text-gray-600' : 'bg-emerald-50 text-emerald-700' ?>">
                                    <?= e((string)$p['status']) ?>
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#ff6b13] transition line-clamp-2 leading-snug mb-2"><?= e($p['title']) ?></h3>
                            <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-3"><?= e($p['excerpt']) ?></p>
                            <span class="text-[11px] text-gray-400 block"><?= e(format_date($p['published_at'] ?? $p['created_at'] ?? time())) ?></span>
                        </div>
                    </div>
                    <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between gap-2 bg-[#faf9f6]">
                        <a href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>" class="text-xs font-semibold text-gray-600 hover:text-gray-900">View</a>
                        <div class="flex items-center gap-2">
                            <a href="<?= e(app_url('write.php?id=' . urlencode((string)$p['_id']))) ?>" class="text-xs font-semibold text-[#ff6b13] hover:underline">Edit</a>
                            <button class="text-xs font-semibold text-red-600 hover:underline cursor-pointer" data-delete-post="<?= e((string)$p['_id']) ?>" data-post-title="<?= e($p['title']) ?>">Delete</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
