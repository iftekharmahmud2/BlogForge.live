<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
require_admin();

$pageTitle = 'Admin dashboard — BlogForge';
$adminPage = 'dashboard';

$db = db();
$stats = ['posts' => 0, 'categories' => 0, 'comments' => 0, 'users' => 0];

if ($db) {
    foreach (array_keys($stats) as $coll) {
        try {
            $stats[$coll] = $db->$coll->countDocuments();
        } catch (\Throwable) {
            $stats[$coll] = 0;
        }
    }
}

$postRepo = new BlogForge\Repositories\PostRepository();
$recent = $postRepo->all(8);

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">
        <span class="uppercase tracking-widest text-[11px] sm:text-xs font-bold text-[#ff6b13] block mb-1">BlogForge control room</span>
        <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight mb-6">Dashboard</h1>

        <!-- KPI Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-10">
            <?php foreach ($stats as $k => $v): ?>
                <div class="bg-white border border-[#e9e5df] rounded-2xl p-4 sm:p-5 shadow-sm">
                    <b class="text-2xl sm:text-3xl font-extrabold text-gray-900 block leading-tight"><?= number_format($v) ?></b>
                    <small class="text-xs text-gray-500 font-medium capitalize mt-1 block"><?= e($k) ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Section Header -->
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-gray-900">Recent posts</h2>
            <a href="<?= e(app_url('admin/posts.php')) ?>" class="text-xs sm:text-sm font-semibold text-[#ff6b13] hover:underline">Manage all posts →</a>
        </div>

        <!-- Table Responsive Wrapper -->
        <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm">
            <table class="w-full min-w-[580px] text-left text-sm divide-y divide-[#e9e5df]">
                <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Author</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($recent)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-gray-400 py-10">No posts available yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent as $p): ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3 px-4">
                                    <b class="text-gray-900 font-semibold line-clamp-1"><?= e((string)$p['title']) ?></b>
                                </td>
                                <td class="py-3 px-4 text-gray-600"><?= e((string)($p['author']['name'] ?? '')) ?></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= ($p['status'] ?? '') === 'draft' ? 'bg-gray-100 text-gray-600' : 'bg-emerald-50 text-emerald-700' ?>">
                                        <?= e((string)$p['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600"><?= e((string)$p['category']) ?></td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition" href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>">View</a>
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
    </section>
</div>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
