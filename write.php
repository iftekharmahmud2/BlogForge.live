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
        echo '<div class="max-w-2xl mx-auto px-4 py-20 text-center"><h1 class="text-3xl font-bold mb-3">Story not found</h1><p class="text-gray-500 mb-6">The story you are trying to edit does not exist.</p><a class="inline-flex items-center justify-center rounded-full border border-gray-300 px-6 py-2.5 text-sm font-semibold hover:border-gray-400" href="' . e(app_url('drafts.php')) . '">Back to drafts</a></div>';
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

$savedCategory = $isEdit ? trim((string)($post['category'] ?? '')) : '';
$categories = (new BlogForge\Repositories\CategoryRepository())->names($savedCategory);
$currentCover = (string)($post['cover'] ?? '');
if (
    preg_match('~^/?assets/img/covers/[^/]+\.(svg|webp)$~i', $currentCover) ||
    preg_match('~^https?://([^/]+\.)?(fbcdn\.net|fbsbx\.com)/~i', $currentCover)
) {
    $currentCover = '';
}
$currentBody = (string)($post['body'] ?? '');

require __DIR__ . '/partials/header.php';
?>

<section class="py-8 sm:py-12 bg-[#fbfaf8] min-h-[calc(100vh-140px)]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <form data-editor-form <?= $isEdit ? 'data-post-id="' . e((string)$post['_id']) . '"' : '' ?> data-default-status="<?= e((string)($post['status'] ?? 'draft')) ?>">
            <!-- Top Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <a href="<?= e(app_url('drafts.php')) ?>" class="text-xs font-semibold text-gray-500 hover:text-gray-800 transition">
                        ← My Drafts
                    </a>
                    <span class="text-gray-300">/</span>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                        <?= $isEdit ? 'Edit story' : 'New story' ?>
                    </h1>
                </div>

                <div class="flex items-center gap-2.5">
                    <?php if ($isEdit): ?>
                        <a class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:border-gray-400 transition" href="<?= e(app_url('article.php?id=' . urlencode((string)($post['slug'] ?? $post['_id'])))) ?>" target="_blank">
                            View live ↗
                        </a>
                    <?php endif; ?>
                    <button class="inline-flex items-center rounded-full border border-gray-300 bg-white px-5 py-2 text-xs font-semibold text-gray-700 hover:border-gray-400 transition cursor-pointer shadow-sm" type="submit" value="draft">
                        Save draft
                    </button>
                    <button class="inline-flex items-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] px-6 py-2 text-xs font-semibold text-white shadow-sm hover:shadow hover:brightness-105 transition cursor-pointer" type="submit" value="published">
                        Publish story
                    </button>
                </div>
            </div>

            <!-- Main Editor Canvas -->
            <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-10 shadow-sm space-y-6">
                
                <!-- Live Cover Preview (if selected) -->
                <div id="cover-preview-container" class="<?= empty($currentCover) ? 'hidden' : '' ?> relative w-full aspect-[21/9] rounded-2xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm mb-4">
                    <img id="cover-preview-img" src="<?= e(cover_url($currentCover, $post['category'] ?? null)) ?>" alt="Cover preview" class="w-full h-full object-cover">
                    <button type="button" id="remove-cover-btn" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/60 hover:bg-black text-white text-xs flex items-center justify-center transition cursor-pointer" title="Remove cover">✕</button>
                </div>

                <!-- Story Title Input -->
                <input class="w-full text-2xl sm:text-4xl font-black text-gray-900 border-0 outline-none placeholder-gray-300 tracking-tight" name="title" placeholder="Title of your story…" value="<?= e((string)($post['title'] ?? '')) ?>" required>

                <!-- Subtitle / Excerpt -->
                <input class="w-full text-base sm:text-lg text-gray-600 border-0 border-b border-gray-100 pb-4 outline-none placeholder-gray-400" name="excerpt" placeholder="Add an inspiring subtitle or summary…" value="<?= e((string)($post['excerpt'] ?? '')) ?>">

                <!-- Visual WYSIWYG Toolbar (No raw markdown!) -->
                <div class="sticky top-16 z-20 bg-white/95 backdrop-blur-sm border-y border-gray-100 py-2.5 flex flex-wrap items-center gap-1 sm:gap-1.5">
                    <button type="button" data-cmd="bold" title="Bold" class="w-8 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 font-bold text-xs flex items-center justify-center transition border border-transparent hover:border-orange-100">B</button>
                    <button type="button" data-cmd="italic" title="Italic" class="w-8 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 italic font-serif text-xs flex items-center justify-center transition border border-transparent hover:border-orange-100">I</button>
                    <button type="button" data-cmd="underline" title="Underline" class="w-8 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 underline text-xs flex items-center justify-center transition border border-transparent hover:border-orange-100">U</button>
                    <span class="w-px h-5 bg-gray-200 mx-1"></span>

                    <button type="button" data-cmd="formatBlock" data-val="H2" title="Large Heading" class="px-2.5 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 font-bold text-xs flex items-center justify-center transition border border-transparent hover:border-orange-100">H2</button>
                    <button type="button" data-cmd="formatBlock" data-val="H3" title="Sub Heading" class="px-2.5 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 font-bold text-xs flex items-center justify-center transition border border-transparent hover:border-orange-100">H3</button>
                    <button type="button" data-cmd="formatBlock" data-val="BLOCKQUOTE" title="Quote block" class="w-8 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 font-serif text-base flex items-center justify-center transition border border-transparent hover:border-orange-100">“</button>
                    <span class="w-px h-5 bg-gray-200 mx-1"></span>

                    <button type="button" data-cmd="insertUnorderedList" title="Bullet List" class="w-8 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 text-xs flex items-center justify-center transition border border-transparent hover:border-orange-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01"/></svg>
                    </button>
                    <button type="button" data-cmd="insertOrderedList" title="Numbered List" class="w-8 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 text-xs flex items-center justify-center transition border border-transparent hover:border-orange-100">
                        <span class="font-mono text-xs">1.</span>
                    </button>
                    <button type="button" id="wysiwyg-link-btn" title="Add Link" class="w-8 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 text-xs flex items-center justify-center transition border border-transparent hover:border-orange-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </button>

                    <button type="button" id="wysiwyg-img-btn" title="Insert In-Story Image" class="w-8 h-8 rounded-lg hover:bg-orange-50 hover:text-[#ff6b13] text-gray-700 text-xs flex items-center justify-center transition border border-transparent hover:border-orange-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </button>

                    <button type="button" data-cmd="removeFormat" title="Clear Formatting" class="w-8 h-8 rounded-lg hover:bg-red-50 hover:text-red-600 text-gray-500 text-xs flex items-center justify-center transition border border-transparent ml-auto" aria-label="Clear formatting">
                        ✕
                    </button>
                </div>

                <!-- Visual WYSIWYG Editable Area (Writers see formatted content directly!) -->
                <div id="wysiwyg-editor" contenteditable="true" class="w-full min-h-[380px] outline-none text-base sm:text-lg text-gray-800 leading-[1.8] py-2 focus:ring-0 empty:before:content-[attr(data-placeholder)] empty:before:text-gray-300 [&>p]:leading-[1.8] [&>p]:mb-4 [&>h2]:text-2xl [&>h2]:font-extrabold [&>h2]:text-gray-900 [&>h2]:mt-6 [&>h2]:mb-2 [&>h3]:text-xl [&>h3]:font-bold [&>h3]:text-gray-900 [&>h3]:mt-4 [&>h3]:mb-2 [&>blockquote]:border-l-4 [&>blockquote]:border-[#ff6b13] [&>blockquote]:bg-[#fbfaf8] [&>blockquote]:p-4 [&>blockquote]:rounded-r-xl [&>blockquote]:italic [&>blockquote]:text-gray-700 [&>blockquote]:my-4 [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:mb-4 [&>ol]:list-decimal [&>ol]:pl-6 [&>ol]:mb-4" data-placeholder="Tell your story… What's on your mind?"><?php
                    if (!empty($currentBody)) {
                        echo body_to_html($currentBody);
                    }
                ?></div>

                <!-- Hidden Input holding formatted content for form submission -->
                <textarea id="body-hidden-input" name="body" class="hidden"><?= e(!empty($currentBody) ? body_to_html($currentBody) : '') ?></textarea>

                <!-- Metadata & Cover Image Section -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6 border-t border-gray-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Category</label>
                        <select class="w-full bg-[#fbfaf8] border border-gray-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" name="category">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= strcasecmp($savedCategory, $cat) === 0 ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Cover Image <span class="text-gray-400 font-normal lowercase">(auto from category if blank, URL, or Upload)</span>
                        </label>
                        <div class="space-y-2">
                            <input id="cover-input" class="w-full bg-[#fbfaf8] border border-gray-200 rounded-2xl px-4 py-2.5 text-xs sm:text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" name="cover" placeholder="Leave blank for category cover, or paste image URL…" value="<?= e($currentCover) ?>">
                            
                            <div class="flex items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 cursor-pointer transition shadow-sm">
                                    <svg class="w-3.5 h-3.5 text-[#ff6b13]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span>Upload &amp; Compress (&lt;2MB)</span>
                                    <input type="file" id="cover-file-upload" accept="image/png,image/jpeg,image/webp" class="hidden">
                                </label>
                                <span id="upload-status" class="text-xs text-gray-500"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tags Input -->
                <div class="pt-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Tags <span class="text-gray-400 font-normal lowercase">(comma separated)</span></label>
                    <input class="w-full bg-[#fbfaf8] border border-gray-200 rounded-2xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" name="tags" placeholder="e.g. Technology, AI, Philosophy, Design" value="<?= e(implode(', ', $post['tags'] ?? [])) ?>">
                </div>

            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
