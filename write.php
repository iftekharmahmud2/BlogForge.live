<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_login();

$user = current_user();
$editId = trim((string)($_GET['id'] ?? ''));
$isEdit = false;
$post = null;

if ($editId !== '') {
    $post = find_post($editId, true);
    if (!$post) {
        http_response_code(404);
        $pageTitle = 'Story not found — BlogForge';
        require __DIR__ . '/partials/header.php';
        echo '<div class="container" style="padding:60px 0;"><h1>Story not found</h1><p>The story you are trying to edit does not exist.</p><a class="btn" href="' . e(app_url('profile.php')) . '">Back to profile</a></div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }

    $authorId = (string)($post['author']['id'] ?? '');
    if (!is_admin() && $authorId !== (string)$user['id']) {
        http_response_code(403);
        $pageTitle = 'Forbidden — BlogForge';
        require __DIR__ . '/partials/header.php';
        echo '<div class="container" style="padding:60px 0;"><h1>Forbidden</h1><p>You are not allowed to edit this story.</p></div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }

    $isEdit = true;
    $pageTitle = 'Edit — ' . ($post['title'] ?? 'Story') . ' — BlogForge';
} else {
    $pageTitle = 'Write a new story — BlogForge';
}

$categories = ['Technology', 'Lifestyle', 'Business', 'Education', 'Health', 'Travel', 'Culture', 'Self Improvement'];
require __DIR__ . '/partials/header.php';
?>

<section class="editor-page">
    <div class="editor-shell">
        <form data-editor-form <?= $isEdit ? 'data-post-id="' . e((string)$post['_id']) . '"' : '' ?> data-default-status="<?= e((string)($post['status'] ?? 'draft')) ?>">
            <div class="editor-top">
                <h1><?= $isEdit ? 'Edit story' : 'New story' ?></h1>
                <?php if ($isEdit): ?>
                    <a class="btn btn-secondary btn-sm" href="<?= e(app_url('article.php?id=' . urlencode((string)($post['slug'] ?? $post['_id'])))) ?>">View story</a>
                <?php endif; ?>
                <button class="btn" type="submit" value="draft">Save draft</button>
                <button class="btn btn-primary" type="submit" value="published">Publish</button>
            </div>

            <div class="editor-card">
                <input class="title-input" name="title" placeholder="Write a captivating title…" value="<?= e((string)($post['title'] ?? '')) ?>" required>
                <input class="excerpt-input" name="excerpt" placeholder="Add a short subtitle or summary…" value="<?= e((string)($post['excerpt'] ?? '')) ?>">

                <div class="editor-toolbar">
                    <button type="button" data-format="**" title="Bold">B</button>
                    <button type="button" data-format="_" title="Italic"><i>I</i></button>
                    <button type="button" data-format="`" title="Code">&lt;/&gt;</button>
                    <button type="button" data-format="## " title="Heading 2">H2</button>
                    <button type="button" data-format="> " title="Blockquote">❞</button>
                </div>

                <textarea class="body-editor" name="body" placeholder="Tell your story…" required><?= e((string)($post['body'] ?? '')) ?></textarea>

                <div class="editor-meta">
                    <input class="input" name="tags" placeholder="Tags: Technology, AI, Productivity" value="<?= e(implode(', ', (array)($post['tags'] ?? []))) ?>">
                    <select class="input" name="category">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= strcasecmp((string)($post['category'] ?? ''), $cat) === 0 ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label>Cover image URL (optional)</label>
                    <input class="input" name="cover" placeholder="https://… or assets/img/covers/ai.svg" value="<?= e((string)($post['cover'] ?? '')) ?>">
                </div>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
