<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

// Strict server-side Admin Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        json_response(['error' => 'Forbidden: administrator access required'], 403);
    }
    redirect(app_url('auth.php?next=' . urlencode('/admin/settings.php')));
}
require_admin();

$pageTitle = 'Site Settings — BlogForge Admin';
$adminPage = 'settings';

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['_csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    try {
        $title = trim((string)($_POST['site_title'] ?? 'BlogForge'));
        $tagline = trim((string)($_POST['site_tagline'] ?? ''));
        $description = trim((string)($_POST['site_description'] ?? ''));
        $maintenance = isset($_POST['maintenance_mode']) && $_POST['maintenance_mode'] === '1' ? '1' : '0';
        $defaultRole = (string)($_POST['default_role'] ?? 'reader');
        $commentsPolicy = (string)($_POST['comments_policy'] ?? 'approved');

        if ($title === '') {
            throw new \InvalidArgumentException('Site title cannot be empty.');
        }

        if (!in_array($defaultRole, ['reader', 'author'], true)) {
            $defaultRole = 'reader';
        }

        if (!in_array($commentsPolicy, ['approved', 'pending'], true)) {
            $commentsPolicy = 'approved';
        }

        set_setting('site_title', $title);
        set_setting('site_tagline', $tagline);
        set_setting('site_description', $description);
        set_setting('maintenance_mode', $maintenance);
        set_setting('default_role', $defaultRole);
        set_setting('comments_policy', $commentsPolicy);

        $message = 'Site settings updated successfully.';
    } catch (\Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$siteTitle = (string)get_setting('site_title', 'BlogForge');
$siteTagline = (string)get_setting('site_tagline', 'Where ideas meet ink. A distraction-free publishing space for deep work, essays, and stories.');
$siteDesc = (string)get_setting('site_description', 'BlogForge is an independent editorial publishing platform designed for writers, essayists, and curious readers.');
$maintenanceMode = (string)get_setting('maintenance_mode', '0');
$defaultRole = (string)get_setting('default_role', 'reader');
$commentsPolicy = (string)get_setting('comments_policy', 'approved');

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0 max-w-4xl">
        <span class="uppercase tracking-widest text-[11px] sm:text-xs font-bold text-[#ff6b13] block mb-1">Configuration</span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-6">Global Site Settings</h1>

        <?php if ($message): ?>
            <div class="p-3.5 rounded-2xl mb-6 text-sm font-medium border <?= $messageType === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="bg-white border border-[#e9e5df] rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

            <!-- Site Identity -->
            <div class="space-y-4">
                <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2">Site Identity &amp; Meta</h2>
                
                <div>
                    <label for="site_title" class="block text-xs font-semibold text-gray-700 mb-1.5">Site Title</label>
                    <input type="text" id="site_title" name="site_title" value="<?= e($siteTitle) ?>" required class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff6b13] focus:ring-2 focus:ring-orange-100 transition shadow-sm">
                </div>

                <div>
                    <label for="site_tagline" class="block text-xs font-semibold text-gray-700 mb-1.5">Site Tagline</label>
                    <input type="text" id="site_tagline" name="site_tagline" value="<?= e($siteTagline) ?>" class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff6b13] focus:ring-2 focus:ring-orange-100 transition shadow-sm">
                    <small class="text-[11px] text-gray-400 mt-1 block">Displayed on homepage hero and metadata.</small>
                </div>

                <div>
                    <label for="site_description" class="block text-xs font-semibold text-gray-700 mb-1.5">Meta Description</label>
                    <textarea id="site_description" name="site_description" rows="3" class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff6b13] focus:ring-2 focus:ring-orange-100 transition shadow-sm"><?= e($siteDesc) ?></textarea>
                </div>
            </div>

            <!-- Access & Registration Controls -->
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2">User Registration &amp; Policy</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="default_role" class="block text-xs font-semibold text-gray-700 mb-1.5">Default Role on Signup</label>
                        <select id="default_role" name="default_role" class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff6b13] transition shadow-sm">
                            <option value="reader" <?= $defaultRole === 'reader' ? 'selected' : '' ?>>Reader (Read &amp; comment only)</option>
                            <option value="author" <?= $defaultRole === 'author' ? 'selected' : '' ?>>Author (Can write stories)</option>
                        </select>
                        <small class="text-[11px] text-gray-400 mt-1 block">Admins can promote any user at any time.</small>
                    </div>

                    <div>
                        <label for="comments_policy" class="block text-xs font-semibold text-gray-700 mb-1.5">Comments Moderation Policy</label>
                        <select id="comments_policy" name="comments_policy" class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff6b13] transition shadow-sm">
                            <option value="approved" <?= $commentsPolicy === 'approved' ? 'selected' : '' ?>>Auto-Approved (Visible immediately)</option>
                            <option value="pending" <?= $commentsPolicy === 'pending' ? 'selected' : '' ?>>Require Moderation (Hold in queue)</option>
                        </select>
                        <small class="text-[11px] text-gray-400 mt-1 block">Spam filter rules still apply to all comments.</small>
                    </div>
                </div>
            </div>

            <!-- Maintenance Mode Toggle -->
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2">Maintenance &amp; Availability</h2>

                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex items-start gap-3.5">
                    <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" <?= $maintenanceMode === '1' ? 'checked' : '' ?> class="mt-1 w-4 h-4 text-[#ff6b13] rounded border-gray-300 focus:ring-[#ff6b13]">
                    <div class="text-xs">
                        <label for="maintenance_mode" class="font-bold text-amber-900 block cursor-pointer">Enable Maintenance Mode</label>
                        <p class="text-amber-800/80 mt-0.5 leading-relaxed">
                            When enabled, regular visitors see an under-maintenance screen. Authenticated administrators retain full access to the site and control room.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Save CTA -->
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-xs sm:text-sm font-semibold shadow-sm hover:brightness-105 transition cursor-pointer">
                    Save Site Settings
                </button>
            </div>
        </form>
    </section>
</div>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
