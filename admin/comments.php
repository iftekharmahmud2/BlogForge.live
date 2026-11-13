<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

// Strict server-side Admin Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        json_response(['error' => 'Forbidden: administrator access required'], 403);
    }
    redirect(app_url('auth.php?next=' . urlencode('/admin/comments.php')));
}
require_admin();

$pageTitle = 'Comment Moderation — BlogForge Admin';
$adminPage = 'comments';

$commentRepo = new BlogForge\Repositories\CommentRepository();
$message = '';
$messageType = 'success';
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['_csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    $action = (string)($_POST['action'] ?? '');
    $commentId = trim((string)($_POST['id'] ?? ''));

    try {
        if ($commentId === '') {
            throw new \InvalidArgumentException('Missing comment ID.');
        }

        // Safe ObjectId validation
        $oid = null;
        try {
            $oid = new MongoDB\BSON\ObjectId($commentId);
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Invalid comment ID format.');
        }

        if ($action === 'delete') {
            $commentRepo->delete($commentId, current_user());
            $message = 'Comment permanently removed.';
        } elseif ($action === 'status') {
            $newStatus = (string)($_POST['status'] ?? 'approved');
            $commentRepo->updateStatus($commentId, $newStatus);
            $message = 'Comment marked as ' . strtoupper($newStatus) . '.';
        }
    } catch (\Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Status filter
$statusFilter = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
$filter = [];
if ($statusFilter !== '' && in_array($statusFilter, ['approved', 'pending', 'spam'], true)) {
    $filter['status'] = $statusFilter;
}

$comments = [];
if ($db) {
    try {
        $cursor = $db->comments->find($filter, [
            'sort' => ['created_at' => -1],
            'limit' => 150
        ]);
        $comments = array_map('normalize_mongo', iterator_to_array($cursor, false));
    } catch (\Throwable $e) {
        error_log('[BlogForge] admin comments query error: ' . $e->getMessage());
        $comments = [];
    }
}

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <span class="uppercase tracking-widest text-[11px] sm:text-xs font-bold text-[#ff6b13] block mb-1">Community Moderation</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Comment Moderation (<?= count($comments) ?>)</h1>
            </div>

            <!-- Status Tabs -->
            <div class="flex items-center gap-1.5 bg-white border border-[#e9e5df] p-1 rounded-xl shadow-sm text-xs">
                <a href="<?= e(app_url('admin/comments.php')) ?>" class="px-3 py-1.5 rounded-lg font-medium transition <?= $statusFilter === '' ? 'bg-gray-900 text-white font-bold' : 'text-gray-600 hover:text-gray-900' ?>">All</a>
                <a href="<?= e(app_url('admin/comments.php?status=pending')) ?>" class="px-3 py-1.5 rounded-lg font-medium transition <?= $statusFilter === 'pending' ? 'bg-amber-500 text-white font-bold' : 'text-gray-600 hover:text-gray-900' ?>">Pending</a>
                <a href="<?= e(app_url('admin/comments.php?status=approved')) ?>" class="px-3 py-1.5 rounded-lg font-medium transition <?= $statusFilter === 'approved' ? 'bg-emerald-600 text-white font-bold' : 'text-gray-600 hover:text-gray-900' ?>">Approved</a>
                <a href="<?= e(app_url('admin/comments.php?status=spam')) ?>" class="px-3 py-1.5 rounded-lg font-medium transition <?= $statusFilter === 'spam' ? 'bg-red-600 text-white font-bold' : 'text-gray-600 hover:text-gray-900' ?>">Spam</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="p-3.5 rounded-2xl mb-6 text-sm font-medium border <?= $messageType === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <!-- Comments Table -->
        <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm">
            <table class="w-full min-w-[680px] text-left text-sm divide-y divide-[#e9e5df]">
                <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="py-3 px-4">Author</th>
                        <th class="py-3 px-4">Comment Body</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Moderation Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($comments)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-gray-400 py-12">No comments found matching this status.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                            <?php
                            $cid = (string)$c['_id'];
                            $cStatus = (string)($c['status'] ?? 'approved');
                            $badgeClass = match($cStatus) {
                                'approved' => 'bg-emerald-50 text-emerald-700',
                                'spam' => 'bg-red-50 text-red-700',
                                default => 'bg-amber-50 text-amber-700',
                            };
                            ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <b class="text-gray-900 font-semibold block text-xs"><?= e((string)($c['author']['name'] ?? 'User')) ?></b>
                                    <span class="text-gray-400 text-xs block">@<?= e((string)($c['author']['handle'] ?? 'user')) ?></span>
                                    <?php if (!empty($c['post_id'])): ?>
                                        <a href="<?= e(app_url('article.php?id=' . urlencode((string)$c['post_id']))) ?>" target="_blank" class="text-[11px] text-[#ff6b13] hover:underline font-semibold block mt-1">View Story →</a>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-gray-700 text-xs max-w-xs sm:max-w-md">
                                    <p class="line-clamp-3 leading-relaxed"><?= e((string)$c['body']) ?></p>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span data-comment-status-badge class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize <?= $badgeClass ?>">
                                        <?= e($cStatus) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-gray-500 whitespace-nowrap"><?= e(format_date($c['created_at'] ?? time())) ?></td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Approve Action -->
                                        <?php if ($cStatus !== 'approved'): ?>
                                            <form method="post" class="inline">
                                                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="id" value="<?= e($cid) ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer">
                                                    Approve
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Spam Action -->
                                        <?php if ($cStatus !== 'spam'): ?>
                                            <form method="post" class="inline">
                                                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="id" value="<?= e($cid) ?>">
                                                <input type="hidden" name="status" value="spam">
                                                <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-700 transition cursor-pointer">
                                                    Spam
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Delete Action -->
                                        <form method="post" class="inline" onsubmit="return confirm('Permanently delete this comment?');">
                                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= e($cid) ?>">
                                            <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 transition cursor-pointer">
                                                Delete
                                            </button>
                                        </form>
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
