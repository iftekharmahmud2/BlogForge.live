<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

// Strict server-side Admin Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        json_response(['error' => 'Forbidden: administrator access required'], 403);
    }
    redirect(app_url('auth.php?next=' . urlencode('/admin/categories.php')));
}
require_admin();

$pageTitle = 'Category Management — BlogForge Admin';
$adminPage = 'categories';

$repo = new BlogForge\Repositories\CategoryRepository();
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['_csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    $action = (string)($_POST['action'] ?? 'create');

    try {
        if ($action === 'delete') {
            $slug = trim((string)($_POST['slug'] ?? ''));
            if ($slug === '') {
                throw new \InvalidArgumentException('Missing category slug.');
            }
            $repo->delete($slug);
            $message = 'Category removed successfully.';
        } elseif ($action === 'update') {
            $oldSlug = trim((string)($_POST['old_slug'] ?? ''));
            $name = trim((string)($_POST['name'] ?? ''));
            $newSlug = trim((string)($_POST['slug'] ?? ''));
            if ($oldSlug === '') {
                throw new \InvalidArgumentException('Missing existing category reference.');
            }
            $repo->update($oldSlug, $name, $newSlug !== '' ? $newSlug : null);
            $message = 'Category updated successfully.';
        } else {
            $name = trim((string)($_POST['name'] ?? ''));
            $repo->create($name);
            $message = 'Category created successfully.';
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
        <span class="uppercase tracking-widest text-[11px] sm:text-xs font-bold text-[#ff6b13] block mb-1">Taxonomy</span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-6">Categories (<?= count($cats) ?>)</h1>

        <?php if ($message): ?>
            <div class="p-3.5 rounded-2xl mb-6 text-sm font-medium border <?= $messageType === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <!-- Create Category Form -->
        <div class="bg-white border border-[#e9e5df] rounded-2xl p-5 mb-8 shadow-sm max-w-2xl">
            <h2 class="text-sm font-bold text-gray-900 mb-3">Add New Category</h2>
            <form method="post" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="create">
                <input class="flex-1 bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff6b13] transition shadow-sm" name="name" placeholder="Category name (e.g. Case Studies)" required maxlength="50">
                <button class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-5 py-2.5 text-xs font-semibold shadow-sm hover:brightness-105 transition cursor-pointer shrink-0" type="submit">
                    Add Category
                </button>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm max-w-4xl">
            <table class="w-full min-w-[540px] text-left text-sm divide-y divide-[#e9e5df]">
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
                            <td colspan="3" class="text-center text-gray-400 py-10">No categories configured yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cats as $idx => $c): ?>
                            <?php
                            $cName = (string)$c['name'];
                            $cSlug = (string)$c['slug'];
                            $formId = 'edit-cat-' . $idx;
                            ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4 font-semibold text-gray-900">
                                    <span id="label-name-<?= $idx ?>"><?= e($cName) ?></span>
                                    <!-- Inline Edit Form (Hidden by default) -->
                                    <form id="<?= $formId ?>" method="post" class="hidden mt-2 flex items-center gap-2">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="old_slug" value="<?= e($cSlug) ?>">
                                        <input type="text" name="name" value="<?= e($cName) ?>" class="bg-[#fbfaf8] border border-[#e9e5df] rounded-lg px-2.5 py-1 text-xs outline-none focus:border-[#ff6b13]" required>
                                        <input type="text" name="slug" value="<?= e($cSlug) ?>" class="bg-[#fbfaf8] border border-[#e9e5df] rounded-lg px-2.5 py-1 text-xs font-mono outline-none focus:border-[#ff6b13]" placeholder="slug">
                                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-[#ff6b13] text-white hover:brightness-105 cursor-pointer">Save</button>
                                        <button type="button" onclick="document.getElementById('<?= $formId ?>').classList.add('hidden')" class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700 cursor-pointer">Cancel</button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-gray-500"><?= e($cSlug) ?></td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Edit button toggles inline form -->
                                        <button type="button" onclick="document.getElementById('<?= $formId ?>').classList.toggle('hidden')" class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition cursor-pointer">
                                            Edit
                                        </button>

                                        <!-- Delete category -->
                                        <form method="post" class="inline" onsubmit="return confirm('Remove category &quot;<?= e(addslashes($cName)) ?>&quot;?');">
                                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="slug" value="<?= e($cSlug) ?>">
                                            <button class="px-2.5 py-1 text-xs font-medium rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 transition cursor-pointer" type="submit">Delete</button>
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
