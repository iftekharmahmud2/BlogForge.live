<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

// Strict server-side Admin Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        json_response(['error' => 'Forbidden: administrator access required'], 403);
    }
    redirect(app_url('auth.php?next=' . urlencode('/admin/posts.php')));
}
require_admin();

$pageTitle = 'All Posts Management — BlogForge Admin';
$adminPage = 'posts';

$message = '';
$messageType = 'success';
$db = db();
$user = current_user();

// Handle Admin POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['_csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    $action = (string)($_POST['action'] ?? '');
    $postId = trim((string)($_POST['id'] ?? ''));

    try {
        if ($postId === '') {
            throw new \InvalidArgumentException('Missing post ID.');
        }

        // Safe ObjectId cast in try-catch
        $oid = null;
        try {
            $oid = new MongoDB\BSON\ObjectId($postId);
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Invalid post ID format.');
        }

        if (!$db) {
            throw new \RuntimeException('Database is currently offline.');
        }

        if ($action === 'delete') {
            // Full Admin override: delete post document without filtering by author_id
            $res = $db->posts->deleteOne(['_id' => $oid]);
            if ($res->getDeletedCount() > 0) {
                // Clean up related comments and reactions
                $db->comments->deleteMany(['post_id' => $postId]);
                $db->reactions->deleteMany(['post_id' => $postId]);
                $message = 'Post and related comments deleted permanently.';
            } else {
                throw new \RuntimeException('Post not found.');
            }
        } elseif ($action === 'toggle_status') {
            $post = $db->posts->findOne(['_id' => $oid]);
            if (!$post) {
                throw new \RuntimeException('Post not found.');
            }
            $currentStatus = (string)($post['status'] ?? 'draft');
            $newStatus = ($currentStatus === 'published') ? 'draft' : 'published';
            $set = [
                'status' => $newStatus,
                'updated_at' => new MongoDB\BSON\UTCDateTime(),
            ];
            if ($newStatus === 'published' && empty($post['published_at'])) {
                $set['published_at'] = new MongoDB\BSON\UTCDateTime();
            }
            $db->posts->updateOne(['_id' => $oid], ['$set' => $set]);
            $message = 'Post status updated to ' . strtoupper($newStatus) . '.';
        } elseif ($action === 'toggle_featured') {
            $post = $db->posts->findOne(['_id' => $oid]);
            if (!$post) {
                throw new \RuntimeException('Post not found.');
            }
            $newVal = empty($post['is_featured']);
            $db->posts->updateOne(
                ['_id' => $oid],
                ['$set' => ['is_featured' => $newVal, 'updated_at' => new MongoDB\BSON\UTCDateTime()]]
            );
            $message = $newVal ? 'Post marked as Featured.' : 'Post unmarked from Featured.';
        }
    } catch (\Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Filters & Query
$search = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$statusFilter = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
$categoryFilter = isset($_GET['category']) ? trim((string)$_GET['category']) : '';

$filter = [];
if ($search !== '') {
    $cleanQ = preg_quote($search, '/');
    $filter['$or'] = [
        ['title' => new MongoDB\BSON\Regex($cleanQ, 'i')],
        ['excerpt' => new MongoDB\BSON\Regex($cleanQ, 'i')],
        ['author.name' => new MongoDB\BSON\Regex($cleanQ, 'i')],
        ['author.handle' => new MongoDB\BSON\Regex($cleanQ, 'i')],
    ];
}
if ($statusFilter !== '' && in_array($statusFilter, ['published', 'draft'], true)) {
    $filter['status'] = $statusFilter;
}
if ($categoryFilter !== '') {
    $filter['category'] = (string)$categoryFilter;
}

$posts = [];
if ($db) {
    try {
        $cursor = $db->posts->find($filter, [
            'sort' => ['created_at' => -1],
            'limit' => 200,
        ]);
        $posts = array_map('normalize_mongo', iterator_to_array($cursor, false));
        $posts = enrich_post_authors($posts, $db);
    } catch (\Throwable $e) {
        error_log('[BlogForge] admin posts query error: ' . $e->getMessage());
        $posts = [];
    }
}

$catRepo = new BlogForge\Repositories\CategoryRepository();
$categories = $catRepo->all();

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <span class="uppercase tracking-widest text-[11px] sm:text-xs font-bold text-[#ff6b13] block mb-1">Content Management</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">All Posts (<?= count($posts) ?>)</h1>
            </div>
            <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-xs font-semibold px-4 py-2 shadow-sm hover:shadow transition self-start sm:self-auto" href="<?= e(app_url('write.php')) ?>">
                + New story
            </a>
        </div>

        <?php if ($message): ?>
            <div class="p-3.5 rounded-2xl mb-6 text-sm font-medium border <?= $messageType === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Bar -->
        <form method="get" class="flex flex-wrap items-center gap-3 mb-6 bg-white border border-[#e9e5df] p-3 rounded-2xl shadow-sm">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search posts by title, author, or excerpt…" class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-3.5 py-2 text-xs sm:text-sm outline-none focus:border-[#ff6b13] transition">
            </div>

            <div class="w-36">
                <select name="status" class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-3 py-2 text-xs sm:text-sm outline-none focus:border-[#ff6b13] transition">
                    <option value="">All Statuses</option>
                    <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>

            <div class="w-40">
                <select name="category" class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-3 py-2 text-xs sm:text-sm outline-none focus:border-[#ff6b13] transition">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= e((string)$c['name']) ?>" <?= $categoryFilter === (string)$c['name'] ? 'selected' : '' ?>><?= e((string)$c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-xl transition shadow-sm cursor-pointer">
                Filter
            </button>

            <?php if ($search !== '' || $statusFilter !== '' || $categoryFilter !== ''): ?>
                <a href="<?= e(app_url('admin/posts.php')) ?>" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-900 transition">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Posts Table -->
        <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm">
            <table class="w-full min-w-[760px] text-left text-sm divide-y divide-[#e9e5df]">
                <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Author</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Featured</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-gray-400 py-12">No posts matched the criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $p): ?>
                            <?php
                            $pid = (string)$p['_id'];
                            $pStatus = (string)($p['status'] ?? 'draft');
                            $isFeatured = !empty($p['is_featured']);
                            ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4 max-w-xs">
                                    <b class="text-gray-900 font-semibold line-clamp-1 block"><?= e((string)$p['title']) ?></b>
                                    <span class="text-xs text-gray-400 line-clamp-1"><?= e((string)($p['excerpt'] ?? '')) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 text-xs whitespace-nowrap">
                                    <span class="font-medium text-gray-900 block"><?= e((string)($p['author']['name'] ?? 'Author')) ?></span>
                                    <span class="text-gray-400">@<?= e((string)($p['author']['handle'] ?? 'author')) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 text-xs whitespace-nowrap"><?= e((string)$p['category']) ?></td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $pStatus === 'draft' ? 'bg-gray-100 text-gray-600' : 'bg-emerald-50 text-emerald-700' ?>">
                                        <?= e($pStatus) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <form method="post" class="inline">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="id" value="<?= e($pid) ?>">
                                        <button type="submit" class="px-2 py-0.5 rounded-full text-xs font-semibold cursor-pointer transition <?= $isFeatured ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>" title="Click to toggle featured state">
                                            <?= $isFeatured ? '★ Featured' : '☆ Normal' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-gray-500 whitespace-nowrap"><?= e(format_date($p['published_at'] ?? $p['created_at'] ?? time())) ?></td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition" href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>" target="_blank">View</a>
                                        <a class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-orange-600 transition" href="<?= e(app_url('write.php?id=' . urlencode($pid))) ?>">Edit</a>
                                        
                                        <!-- Quick Publish / Unpublish Toggle -->
                                        <form method="post" class="inline">
                                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?= e($pid) ?>">
                                            <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition cursor-pointer" title="<?= $pStatus === 'published' ? 'Revert to draft' : 'Publish post' ?>">
                                                <?= $pStatus === 'published' ? 'Unpublish' : 'Publish' ?>
                                            </button>
                                        </form>

                                        <!-- Delete with Confirmation (Admin Override) -->
                                        <form method="post" class="inline" onsubmit="return confirm('Permanently delete &quot;<?= e(addslashes((string)$p['title'])) ?>&quot;? This cannot be undone.');">
                                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= e($pid) ?>">
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
