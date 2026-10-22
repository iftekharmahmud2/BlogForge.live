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

<div class="admin-layout">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="admin-main">
        <h1>Comments Moderation (<?= count($comments) ?>)</h1>

        <div class="table-responsive">
            <table class="admin-table">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comments)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;color:var(--muted);padding:40px;">No comments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <tr>
                            <td>
                                <b><?= e((string)($c['author']['name'] ?? 'Anonymous')) ?></b>
                                <?php if (!empty($c['post_id'])): ?>
                                    <br><a href="<?= e(app_url('article.php?id=' . urlencode((string)$c['post_id']))) ?>" target="_blank" style="font-size:11px;color:var(--orange);">View story</a>
                                <?php endif; ?>
                            </td>
                            <td><?= e((string)$c['body']) ?></td>
                            <td>
                                <span class="status <?= e((string)($c['status'] ?? 'approved')) ?>">
                                    <?= e((string)($c['status'] ?? 'approved')) ?>
                                </span>
                            </td>
                            <td><?= e(format_date($c['created_at'] ?? time())) ?></td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn-table" data-moderate-comment="<?= e((string)$c['_id']) ?>" data-action="approved">Approve</button>
                                    <button class="btn-table" data-moderate-comment="<?= e((string)$c['_id']) ?>" data-action="spam">Spam</button>
                                    <button class="btn-table btn-table-danger" data-moderate-comment="<?= e((string)$c['_id']) ?>" data-action="delete">Delete</button>
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
