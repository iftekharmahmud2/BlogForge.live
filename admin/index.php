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

<div class="admin-layout">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="admin-main">
        <span class="eyebrow">BlogForge control room</span>
        <h1>Dashboard</h1>

        <div class="stat-grid">
            <?php foreach ($stats as $k => $v): ?>
                <div class="stat">
                    <b><?= number_format($v) ?></b>
                    <small><?= e(ucfirst($k)) ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-section-header">
            <h2>Recent posts</h2>
            <a href="<?= e(app_url('admin/posts.php')) ?>">Manage all posts →</a>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:var(--muted);padding:30px;">No posts available yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent as $p): ?>
                            <tr>
                                <td>
                                    <b><?= e((string)$p['title']) ?></b>
                                </td>
                                <td><?= e((string)($p['author']['name'] ?? '')) ?></td>
                                <td>
                                    <span class="status <?= ($p['status'] ?? '') === 'draft' ? 'draft' : 'approved' ?>">
                                        <?= e((string)$p['status']) ?>
                                    </span>
                                </td>
                                <td><?= e((string)$p['category']) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn-table" href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>">View</a>
                                        <a class="btn-table" href="<?= e(app_url('write.php?id=' . urlencode((string)$p['_id']))) ?>">Edit</a>
                                        <button class="btn-table btn-table-danger" data-delete-post="<?= e((string)$p['_id']) ?>" data-post-title="<?= e($p['title']) ?>">Delete</button>
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
