<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_login();

use BlogForge\Repositories\PostRepository;

$user = current_user();
$repo = new PostRepository();

$pageTitle = 'My Drafts — BlogForge';

// Fetch author's posts and filter only drafts
$allPosts = $repo->forAuthor((string)$user['id'], 100);
$drafts = array_values(array_filter($allPosts, static function ($p) {
    return ($p['status'] ?? 'published') === 'draft';
}));

require __DIR__ . '/partials/header.php';
?>

<!-- Header Section -->
<section class="border-b border-gray-100 bg-[#fbfaf8] py-10 sm:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    Workspace
                </span>
                <span class="text-xs text-gray-500 font-medium"><?= count($drafts) ?> <?= count($drafts) === 1 ? 'draft' : 'drafts' ?></span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-gray-900">My Drafts</h1>
            <p class="text-gray-500 text-sm sm:text-base mt-1">Stories you're crafting that haven't been shared publicly yet.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= e(app_url('write.php')) ?>" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-sm font-semibold px-5 py-2.5 shadow-sm hover:shadow transition">
                + New story
            </a>
            <a href="<?= e(app_url('profile.php')) ?>" class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white text-gray-700 text-sm font-semibold px-4 py-2.5 hover:border-gray-400 transition">
                Published stories
            </a>
        </div>
    </div>
</section>

<!-- Drafts List -->
<section class="py-10 sm:py-14 bg-white min-h-[50vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (empty($drafts)): ?>
            <div class="max-w-md mx-auto text-center py-16 px-4">
                <div class="w-16 h-16 rounded-3xl bg-orange-50 text-[#ff6b13] flex items-center justify-center mx-auto mb-4 text-2xl">
                    ✎
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">No drafts in progress</h2>
                <p class="text-gray-500 text-sm mb-6">Whenever you start writing and click "Save draft", your unpublished stories will appear here.</p>
                <a href="<?= e(app_url('write.php')) ?>" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-sm font-semibold px-6 py-3 shadow-sm hover:shadow transition">
                    Write your first draft →
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($drafts as $d): ?>
                    <article class="bg-[#fbfaf8] border border-gray-200 rounded-3xl p-6 flex flex-col justify-between hover:border-gray-300 hover:shadow-sm transition-all" data-post-card>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    <?= e($d['category'] ?? 'General') ?>
                                </span>
                                <span class="text-xs text-gray-400">
                                    <?= e(format_date($d['updated_at'] ?? $d['created_at'] ?? time())) ?>
                                </span>
                            </div>

                            <a href="<?= e(app_url('write.php?id=' . urlencode((string)$d['_id']))) ?>" class="block group">
                                <h2 class="text-lg font-bold text-gray-900 group-hover:text-[#ff6b13] transition line-clamp-2 mb-2">
                                    <?= e(!empty($d['title']) ? $d['title'] : 'Untitled Draft') ?>
                                </h2>
                            </a>

                            <p class="text-gray-500 text-xs sm:text-sm line-clamp-3 mb-4 leading-relaxed">
                                <?= e(!empty($d['excerpt']) ? $d['excerpt'] : (mb_substr(strip_tags((string)$d['body']), 0, 140) . '…')) ?>
                            </p>
                        </div>

                        <div class="pt-4 border-t border-gray-200/80 flex items-center justify-between gap-2">
                            <span class="text-xs text-gray-400 font-medium"><?= e((string)($d['read_time'] ?? 1)) ?> min read</span>
                            <div class="flex items-center gap-2">
                                <a href="<?= e(app_url('write.php?id=' . urlencode((string)$d['_id']))) ?>" class="inline-flex items-center px-3 py-1.5 rounded-full bg-white border border-gray-300 text-xs font-semibold text-gray-700 hover:border-gray-400 transition">
                                    Edit
                                </a>
                                <button type="button" class="inline-flex items-center px-2.5 py-1.5 rounded-full bg-red-50 text-red-600 border border-red-200 text-xs font-semibold hover:bg-red-100 transition cursor-pointer" data-delete-post="<?= e((string)$d['_id']) ?>" data-post-title="<?= e($d['title'] ?? 'this draft') ?>" title="Delete Draft">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
