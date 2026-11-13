<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

// Strict server-side Admin Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        json_response(['error' => 'Forbidden: administrator access required'], 403);
    }
    redirect(app_url('auth.php?next=' . urlencode('/admin/')));
}
require_admin();

$pageTitle = 'Admin Dashboard — BlogForge';
$adminPage = 'dashboard';

$db = db();
$stats = [
    'total_posts' => 0,
    'published_posts' => 0,
    'draft_posts' => 0,
    'total_users' => 0,
    'authors' => 0,
    'readers' => 0,
    'total_comments' => 0,
    'pending_comments' => 0,
    'total_categories' => 0,
];

if ($db) {
    try {
        $stats['total_posts'] = $db->posts->countDocuments();
        $stats['published_posts'] = $db->posts->countDocuments(['status' => 'published']);
        $stats['draft_posts'] = $db->posts->countDocuments(['status' => 'draft']);
        $stats['total_users'] = $db->users->countDocuments();
        $stats['authors'] = $db->users->countDocuments(['role' => 'author']);
        $stats['readers'] = $db->users->countDocuments(['role' => 'reader']);
        $stats['total_comments'] = $db->comments->countDocuments();
        $stats['pending_comments'] = $db->comments->countDocuments(['status' => 'pending']);
        $stats['total_categories'] = $db->categories->countDocuments();
    } catch (\Throwable $e) {
        error_log('[BlogForge] Admin stats error: ' . $e->getMessage());
    }
}

$postRepo = new BlogForge\Repositories\PostRepository();
$recentPosts = $postRepo->all(6);

$commentRepo = new BlogForge\Repositories\CommentRepository();
$recentComments = $commentRepo->all(5);

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <span class="uppercase tracking-widest text-[11px] sm:text-xs font-bold text-[#ff6b13] block mb-1">Administrative Overview</span>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Control Room</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= e(app_url('write.php')) ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-xs font-semibold shadow-sm hover:shadow transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Story</span>
                </a>
                <a href="<?= e(app_url('admin/settings.php')) ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-medium transition shadow-sm">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Settings</span>
                </a>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
            <!-- Posts KPI -->
            <a href="<?= e(app_url('admin/posts.php')) ?>" class="bg-white border border-[#e9e5df] rounded-2xl p-5 shadow-sm hover:border-gray-300 hover:shadow-md transition group block">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">All Posts</span>
                    <span class="w-8 h-8 rounded-lg bg-orange-50 text-[#ff6b13] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </span>
                </div>
                <b class="text-3xl font-extrabold text-gray-900 block leading-tight group-hover:text-[#ff6b13] transition"><?= number_format($stats['total_posts']) ?></b>
                <div class="flex items-center gap-2 mt-2 text-xs text-gray-500 font-medium">
                    <span class="text-emerald-600"><?= number_format($stats['published_posts']) ?> published</span>
                    <span>·</span>
                    <span class="text-gray-400"><?= number_format($stats['draft_posts']) ?> drafts</span>
                </div>
            </a>

            <!-- Users KPI -->
            <a href="<?= e(app_url('admin/users.php')) ?>" class="bg-white border border-[#e9e5df] rounded-2xl p-5 shadow-sm hover:border-gray-300 hover:shadow-md transition group block">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Users</span>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </span>
                </div>
                <b class="text-3xl font-extrabold text-gray-900 block leading-tight group-hover:text-blue-600 transition"><?= number_format($stats['total_users']) ?></b>
                <div class="flex items-center gap-2 mt-2 text-xs text-gray-500 font-medium">
                    <span class="text-blue-600"><?= number_format($stats['authors']) ?> authors</span>
                    <span>·</span>
                    <span class="text-gray-400"><?= number_format($stats['readers']) ?> readers</span>
                </div>
            </a>

            <!-- Comments KPI -->
            <a href="<?= e(app_url('admin/comments.php')) ?>" class="bg-white border border-[#e9e5df] rounded-2xl p-5 shadow-sm hover:border-gray-300 hover:shadow-md transition group block">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Comments</span>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </span>
                </div>
                <b class="text-3xl font-extrabold text-gray-900 block leading-tight group-hover:text-emerald-600 transition"><?= number_format($stats['total_comments']) ?></b>
                <div class="flex items-center gap-2 mt-2 text-xs font-medium">
                    <?php if ($stats['pending_comments'] > 0): ?>
                        <span class="text-amber-600 font-bold"><?= number_format($stats['pending_comments']) ?> pending moderation</span>
                    <?php else: ?>
                        <span class="text-gray-400">All moderated</span>
                    <?php endif; ?>
                </div>
            </a>

            <!-- Categories KPI -->
            <a href="<?= e(app_url('admin/categories.php')) ?>" class="bg-white border border-[#e9e5df] rounded-2xl p-5 shadow-sm hover:border-gray-300 hover:shadow-md transition group block">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Categories</span>
                    <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </span>
                </div>
                <b class="text-3xl font-extrabold text-gray-900 block leading-tight group-hover:text-purple-600 transition"><?= number_format($stats['total_categories']) ?></b>
                <small class="text-xs text-gray-400 font-medium block mt-2">Active topic channels</small>
            </a>
        </div>

        <!-- Recent Posts Section -->
        <div class="mb-10">
            <div class="flex items-center justify-between gap-4 mb-4">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900">Recent posts (Admin CRUD Override)</h2>
                <a href="<?= e(app_url('admin/posts.php')) ?>" class="text-xs sm:text-sm font-semibold text-[#ff6b13] hover:underline">View all posts →</a>
            </div>

            <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm">
                <table class="w-full min-w-[620px] text-left text-sm divide-y divide-[#e9e5df]">
                    <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="py-3 px-4">Title</th>
                            <th class="py-3 px-4">Author</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Featured</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($recentPosts)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-gray-400 py-10">No posts available yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentPosts as $p): ?>
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3.5 px-4">
                                        <b class="text-gray-900 font-semibold line-clamp-1"><?= e((string)$p['title']) ?></b>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 text-xs">
                                        <?= e((string)($p['author']['name'] ?? 'Author')) ?>
                                        <span class="text-gray-400 block">@<?= e((string)($p['author']['handle'] ?? 'author')) ?></span>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 text-xs"><?= e((string)$p['category']) ?></td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= ($p['status'] ?? '') === 'draft' ? 'bg-gray-100 text-gray-600' : 'bg-emerald-50 text-emerald-700' ?>">
                                            <?= e((string)$p['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if (!empty($p['is_featured'])): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">★ Featured</span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">Regular</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition" href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>" target="_blank">View</a>
                                            <a class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-orange-600 transition" href="<?= e(app_url('write.php?id=' . urlencode((string)$p['_id']))) ?>">Edit</a>
                                            <button class="px-2.5 py-1 text-xs font-medium rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 transition cursor-pointer" data-delete-post="<?= e((string)$p['_id']) ?>" data-post-title="<?= e($p['title']) ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Comments Moderation -->
        <div>
            <div class="flex items-center justify-between gap-4 mb-4">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900">Recent responses</h2>
                <a href="<?= e(app_url('admin/comments.php')) ?>" class="text-xs sm:text-sm font-semibold text-[#ff6b13] hover:underline">Moderate all comments →</a>
            </div>

            <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm">
                <table class="w-full min-w-[580px] text-left text-sm divide-y divide-[#e9e5df]">
                    <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="py-3 px-4">Commenter</th>
                            <th class="py-3 px-4">Excerpt</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($recentComments)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-gray-400 py-8">No comments submitted yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentComments as $c): ?>
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3 px-4">
                                        <b class="text-gray-900 font-semibold block text-xs"><?= e((string)($c['author']['name'] ?? 'User')) ?></b>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 text-xs max-w-sm line-clamp-1">
                                        <?= e((string)$c['body']) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium capitalize <?= ($c['status'] ?? '') === 'approved' ? 'bg-emerald-50 text-emerald-700' : (($c['status'] ?? '') === 'spam' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') ?>">
                                            <?= e((string)($c['status'] ?? 'approved')) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="<?= e(app_url('admin/comments.php')) ?>" class="text-xs font-semibold text-[#ff6b13] hover:underline">Moderate →</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
