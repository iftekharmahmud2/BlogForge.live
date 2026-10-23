<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
require_admin();

$pageTitle = 'Categories — BlogForge';
$adminPage = 'categories';

$repo = new BlogForge\Repositories\CategoryRepository();
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)$token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? 'create';

    try {
        if ($action === 'delete') {
            $slug = trim((string)($_POST['slug'] ?? ''));
            $repo->delete($slug);
            $message = 'Category removed successfully.';
        } else {
            $name = trim((string)($_POST['name'] ?? ''));
            $repo->create($name);
            $message = 'Category saved successfully.';
        }
    } catch (\Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$cats = $repo->all();

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-6">Categories</h1>

        <?php if ($message): ?>
            <div class="p-3.5 rounded-2xl mb-6 text-sm font-medium border <?= $messageType === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="flex flex-wrap items-center gap-2.5 max-w-lg mb-8">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">
            <input class="flex-1 min-w-[200px] bg-white border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition shadow-sm" name="name" placeholder="New category name (e.g. Design)" required>
            <button class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-5 py-2.5 text-xs font-semibold shadow-sm hover:shadow transition cursor-pointer shrink-0" type="submit">Add Category</button>
        </form>

        <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm max-w-3xl">
            <table class="w-full min-w-[480px] text-left text-sm divide-y divide-[#e9e5df]">
                <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="py-3 px-4">Category Name</th>
                        <th class="py-3 px-4">Slug</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($cats)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-gray-400 py-10">No categories configured.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cats as $c): ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4 font-semibold text-gray-900"><?= e((string)$c['name']) ?></td>
                                <td class="py-3.5 px-4 font-mono text-xs text-gray-500"><?= e((string)$c['slug']) ?></td>
                                <td class="py-3.5 px-4 text-right">
                                    <form method="post" class="inline" onsubmit="return confirm('Remove category <?= e((string)$c['name']) ?>?');">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="slug" value="<?= e((string)$c['slug']) ?>">
                                        <button class="px-2.5 py-1 text-xs font-medium rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 transition cursor-pointer" type="submit">Delete</button>
                                    </form>
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
