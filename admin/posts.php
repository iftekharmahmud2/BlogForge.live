<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
require_admin();

$pageTitle = 'Manage posts — BlogForge';
$adminPage = 'posts';

$statusFilter = isset($_GET['status']) ? (string)$_GET['status'] : null;
$postRepo = new BlogForge\Repositories\PostRepository();
$posts = $postRepo->all(150, $statusFilter);

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Posts (<?= count($posts) ?>)</h1>
            <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-xs font-semibold px-4 py-2 shadow-sm hover:shadow transition self-start sm:self-auto" href="<?= e(app_url('write.php')) ?>">+ New story</a>
        </div>

        <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm">
            <table class="w-full min-w-[640px] text-left text-sm divide-y divide-[#e9e5df]">
                <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Author</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-gray-400 py-12">No posts found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $p): ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4">
                                    <b class="text-gray-900 font-semibold line-clamp-1"><?= e((string)$p['title']) ?></b>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600"><?= e((string)($p['author']['name'] ?? '')) ?></td>
                                <td class="py-3.5 px-4 text-gray-600"><?= e((string)$p['category']) ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= ($p['status'] ?? '') === 'draft' ? 'bg-gray-100 text-gray-600' : 'bg-emerald-50 text-emerald-700' ?>">
                                        <?= e((string)$p['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-gray-500"><?= e(format_date($p['published_at'] ?? $p['created_at'] ?? time())) ?></td>
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
    </section>
</div>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
