<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
require_admin();

$pageTitle = 'Moderate comments — BlogForge';
$adminPage = 'comments';

$commentRepo = new BlogForge\Repositories\CommentRepository();
$comments = $commentRepo->all(100);

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-6">Comments Moderation (<?= count($comments) ?>)</h1>

        <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm">
            <table class="w-full min-w-[620px] text-left text-sm divide-y divide-[#e9e5df]">
                <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="py-3 px-4">Author</th>
                        <th class="py-3 px-4">Comment</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($comments)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-gray-400 py-12">No comments found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4">
                                    <b class="text-gray-900 font-semibold block"><?= e((string)($c['author']['name'] ?? 'Anonymous')) ?></b>
                                    <?php if (!empty($c['post_id'])): ?>
                                        <a href="<?= e(app_url('article.php?id=' . urlencode((string)$c['post_id']))) ?>" target="_blank" class="text-xs text-[#ff6b13] hover:underline font-medium">View story</a>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-gray-700 max-w-xs sm:max-w-md"><?= e((string)$c['body']) ?></td>
                                <td class="py-3.5 px-4">
                                    <?php
                                    $status = (string)($c['status'] ?? 'approved');
                                    $badgeClass = match($status) {
                                        'approved' => 'bg-emerald-50 text-emerald-700',
                                        'spam' => 'bg-red-50 text-red-700',
                                        default => 'bg-amber-50 text-amber-700',
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize <?= $badgeClass ?>">
                                        <?= e($status) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-gray-500"><?= e(format_date($c['created_at'] ?? time())) ?></td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-emerald-700 transition cursor-pointer" data-moderate-comment="<?= e((string)$c['_id']) ?>" data-action="approved">Approve</button>
                                        <button class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-amber-700 transition cursor-pointer" data-moderate-comment="<?= e((string)$c['_id']) ?>" data-action="spam">Spam</button>
                                        <button class="px-2.5 py-1 text-xs font-medium rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 transition cursor-pointer" data-moderate-comment="<?= e((string)$c['_id']) ?>" data-action="delete">Delete</button>
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
