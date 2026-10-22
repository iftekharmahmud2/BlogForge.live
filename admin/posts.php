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

<div class="admin-layout">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="admin-main">
        <div class="admin-section-header" style="margin-top:0;margin-bottom:20px;">
            <h1 style="margin:0;">Posts (<?= count($posts) ?>)</h1>
            <a class="btn btn-primary btn-sm" href="<?= e(app_url('write.php')) ?>">+ New story</a>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--muted);padding:40px;">No posts found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $p): ?>
                        <tr>
                            <td>
                                <b><?= e((string)$p['title']) ?></b>
                            </td>
                            <td><?= e((string)($p['author']['name'] ?? '')) ?></td>
                            <td><?= e((string)$p['category']) ?></td>
                            <td>
                                <span class="status <?= ($p['status'] ?? '') === 'draft' ? 'draft' : 'approved' ?>">
                                    <?= e((string)$p['status']) ?>
                                </span>
                            </td>
                            <td><?= e(format_date($p['published_at'] ?? $p['created_at'] ?? time())) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-table" href="<?= e(app_url('article.php?id=' . urlencode((string)($p['slug'] ?? $p['_id'])))) ?>" target="_blank">View</a>
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
