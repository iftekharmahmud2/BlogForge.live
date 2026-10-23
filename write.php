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
        echo '<div class="max-w-2xl mx-auto px-4 py-20 text-center"><h1 class="text-3xl font-bold mb-3">Story not found</h1><p class="text-gray-500 mb-6">The story you are trying to edit does not exist.</p><a class="inline-flex items-center justify-center rounded-full border border-gray-300 px-6 py-2.5 text-sm font-semibold hover:border-gray-400" href="' . e(app_url('profile.php')) . '">Back to profile</a></div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }

    $authorId = (string)($post['author']['id'] ?? '');
    if (!is_admin() && $authorId !== (string)$user['id']) {
        http_response_code(403);
        $pageTitle = 'Forbidden — BlogForge';
        require __DIR__ . '/partials/header.php';
        echo '<div class="max-w-2xl mx-auto px-4 py-20 text-center"><h1 class="text-3xl font-bold text-red-600 mb-3">Forbidden</h1><p class="text-gray-500">You are not allowed to edit this story.</p></div>';
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

<section class="py-8 sm:py-12 bg-[#fbfaf8] min-h-[calc(100vh-140px)]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <form data-editor-form <?= $isEdit ? 'data-post-id="' . e((string)$post['_id']) . '"' : '' ?> data-default-status="<?= e((string)($post['status'] ?? 'draft')) ?>">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight"><?= $isEdit ? 'Edit story' : 'New story' ?></h1>
                <div class="flex items-center gap-2.5">
                    <?php if ($isEdit): ?>
                        <a class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:border-gray-400 transition" href="<?= e(app_url('article.php?id=' . urlencode((string)($post['slug'] ?? $post['_id'])))) ?>">View story</a>
                    <?php endif; ?>
                    <button class="inline-flex items-center rounded-full border border-gray-300 bg-white px-5 py-2 text-xs font-semibold text-gray-700 hover:border-gray-400 transition cursor-pointer" type="submit" value="draft">Save draft</button>
                    <button class="inline-flex items-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] px-5 py-2 text-xs font-semibold text-white shadow-sm hover:shadow transition cursor-pointer" type="submit" value="published">Publish</button>
                </div>
            </div>

            <div class="bg-white border border-[#e9e5df] rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                <input class="w-full text-2xl sm:text-4xl font-extrabold text-gray-900 border-0 outline-none placeholder-gray-300" name="title" placeholder="Write a captivating title…" value="<?= e((string)($post['title'] ?? '')) ?>" required>
                <input class="w-full text-base sm:text-lg text-gray-600 border-0 border-b border-[#e9e5df] pb-3 outline-none placeholder-gray-400" name="excerpt" placeholder="Add a short subtitle or summary…" value="<?= e((string)($post['excerpt'] ?? '')) ?>">

                <!-- Toolbar -->
                <div class="flex flex-wrap items-center gap-1.5 py-2 border-b border-[#e9e5df]">
                    <button type="button" data-format="**" title="Bold" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-orange-100 hover:text-[#ff6b13] text-gray-700 font-bold text-xs flex items-center justify-center transition">B</button>
                    <button type="button" data-format="*" title="Italic" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-orange-100 hover:text-[#ff6b13] text-gray-700 italic font-serif text-xs flex items-center justify-center transition">I</button>
                    <button type="button" data-format="## " title="Heading 2" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-orange-100 hover:text-[#ff6b13] text-gray-700 font-bold text-xs flex items-center justify-center transition">H2</button>
                    <button type="button" data-format="### " title="Heading 3" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-orange-100 hover:text-[#ff6b13] text-gray-700 font-bold text-xs flex items-center justify-center transition">H3</button>
                    <button type="button" data-format="> " title="Quote" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-orange-100 hover:text-[#ff6b13] text-gray-700 font-serif text-xs flex items-center justify-center transition">“</button>
                    <button type="button" data-format="`" title="Code" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-orange-100 hover:text-[#ff6b13] text-gray-700 font-mono text-xs flex items-center justify-center transition">&lt;&gt;</button>
                    <button type="button" data-format="[link](https://)" title="Link" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-orange-100 hover:text-[#ff6b13] text-gray-700 text-xs flex items-center justify-center transition">🔗</button>
                    <button type="button" data-insert-image title="Add Image" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-orange-100 hover:text-[#ff6b13] text-gray-700 text-xs flex items-center justify-center transition">🖼</button>
                    <button type="button" data-clear-format title="Clear" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-700 text-xs flex items-center justify-center transition ml-auto">✕</button>
                </div>

                <!-- Textarea -->
                <textarea class="w-full min-h-[380px] border-0 outline-none text-base sm:text-lg text-gray-800 leading-relaxed resize-y py-4 placeholder-gray-300" name="body" placeholder="Tell your story… Markdown formatting is supported." required><?= e((string)($post['body'] ?? '')) ?></textarea>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-[#e9e5df]">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Category</label>
                        <select class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" name="category">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= (($post['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Cover image URL <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" name="cover" placeholder="https://images.unsplash.com/…" value="<?= e((string)($post['cover'] ?? '')) ?>">
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
