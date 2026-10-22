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

<div class="admin-layout">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="admin-main">
        <h1>Categories</h1>

        <?php if ($message): ?>
            <div style="padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;background:<?= $messageType === 'error' ? '#fff0ed;color:#a53118;' : '#e8f7ed;color:#24703a;' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" style="display:flex;gap:10px;max-width:520px;margin-bottom:25px">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">
            <input class="input" name="name" placeholder="New category name (e.g. Design)" required>
            <button class="btn btn-primary" type="submit">Add Category</button>
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cats)): ?>
                    <tr>
                        <td colspan="3" style="text-align:center;color:var(--muted);padding:30px;">No categories configured.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cats as $c): ?>
                        <tr>
                            <td><b><?= e((string)$c['name']) ?></b></td>
                            <td><code><?= e((string)$c['slug']) ?></code></td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Remove category <?= e((string)$c['name']) ?>?');">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="slug" value="<?= e((string)$c['slug']) ?>">
                                    <button class="btn-table btn-table-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
