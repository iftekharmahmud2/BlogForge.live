<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

// Strict server-side Admin Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        json_response(['error' => 'Forbidden: administrator access required'], 403);
    }
    redirect(app_url('auth.php?next=' . urlencode('/admin/users.php')));
}
require_admin();

$pageTitle = 'User & Role Management — BlogForge Admin';
$adminPage = 'users';

$currentAdminId = (string)($_SESSION['user_id'] ?? '');
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['_csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    $action = (string)($_POST['action'] ?? '');
    $targetUserId = trim((string)($_POST['user_id'] ?? ''));

    try {
        if ($targetUserId === '') {
            throw new \InvalidArgumentException('Missing target user ID.');
        }

        if ($action === 'change_role') {
            $newRole = (string)($_POST['role'] ?? 'reader');
            BlogForge\Auth::updateUserRole($targetUserId, $newRole, $currentAdminId);
            $message = 'User role updated successfully.';
        } elseif ($action === 'toggle_status') {
            $newStatus = (string)($_POST['status'] ?? 'active');
            BlogForge\Auth::updateUserStatus($targetUserId, $newStatus, $currentAdminId);
            $message = $newStatus === 'banned' ? 'User account has been suspended/banned.' : 'User account has been reactivated.';
        } elseif ($action === 'delete') {
            BlogForge\Auth::deleteUser($targetUserId, $currentAdminId);
            $message = 'User account deleted successfully.';
        }
    } catch (\Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Search and filter parameters
$search = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
$roleFilter = isset($_GET['role']) && in_array($_GET['role'], ['admin', 'author', 'reader'], true) ? (string)$_GET['role'] : null;
$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['active', 'banned'], true) ? (string)$_GET['status'] : null;

$users = BlogForge\Auth::listUsers(150, $search, $roleFilter, $statusFilter);

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    <?php require __DIR__ . '/_nav.php'; ?>
    <section class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <span class="uppercase tracking-widest text-[11px] sm:text-xs font-bold text-[#ff6b13] block mb-1">Access Control &amp; RBAC</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">User Management (<?= count($users) ?>)</h1>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="p-3.5 rounded-2xl mb-6 text-sm font-medium border <?= $messageType === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Controls -->
        <form method="get" class="flex flex-wrap items-center gap-3 mb-6 bg-white border border-[#e9e5df] p-3 rounded-2xl shadow-sm">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Search users by name, username, or email…" class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-3.5 py-2 text-xs sm:text-sm outline-none focus:border-[#ff6b13] transition">
            </div>

            <div class="w-36">
                <select name="role" class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-3 py-2 text-xs sm:text-sm outline-none focus:border-[#ff6b13] transition">
                    <option value="">All Roles</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="author" <?= $roleFilter === 'author' ? 'selected' : '' ?>>Author</option>
                    <option value="reader" <?= $roleFilter === 'reader' ? 'selected' : '' ?>>Reader</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-xl transition shadow-sm cursor-pointer">
                Filter
            </button>

            <?php if (!empty($search) || !empty($roleFilter)): ?>
                <a href="<?= e(app_url('admin/users.php')) ?>" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-900 transition">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Users Table -->
        <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm">
            <table class="w-full min-w-[720px] text-left text-sm divide-y divide-[#e9e5df]">
                <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Joined</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-gray-400 py-12">No registered users matched the filter criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <?php
                            $uid = (string)$u['_id'];
                            $isSelf = ($uid === $currentAdminId);
                            $uRole = (string)($u['role'] ?? 'reader');
                            $uStatus = (string)($u['status'] ?? 'active');
                            ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <?= avatar_badge((string)($u['avatar'] ?? ''), (string)$u['name'], 'w-8 h-8 text-xs') ?>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <b class="text-gray-900 font-semibold truncate block"><?= e((string)$u['name']) ?></b>
                                                <?php if ($isSelf): ?>
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-orange-100 text-[#ff6b13] uppercase tracking-wider">You</span>
                                                <?php elseif ($uStatus === 'banned'): ?>
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wider">Suspended</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-xs text-gray-400 block truncate">@<?= e((string)($u['handle'] ?? 'user')) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono text-gray-600"><?= e((string)$u['email']) ?></td>
                                <td class="py-3.5 px-4">
                                    <?php if ($isSelf): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-[#ff6b13] uppercase">
                                            Admin
                                        </span>
                                    <?php else: ?>
                                        <!-- Inline Role Selector with CSRF -->
                                        <form method="post" class="inline-flex items-center gap-1.5">
                                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="user_id" value="<?= e($uid) ?>">
                                            <select name="role" onchange="this.form.submit()" class="bg-[#fbfaf8] border border-[#e9e5df] rounded-lg px-2 py-1 text-xs font-semibold text-gray-800 outline-none focus:border-[#ff6b13] transition cursor-pointer">
                                                <option value="admin" <?= $uRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="author" <?= $uRole === 'author' ? 'selected' : '' ?>>Author</option>
                                                <option value="reader" <?= $uRole === 'reader' ? 'selected' : '' ?>>Reader</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-gray-500 whitespace-nowrap">
                                    <?= e(format_date($u['created_at'] ?? time())) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="<?= e(profile_url($u)) ?>" target="_blank" class="px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition">
                                            View
                                        </a>

                                        <?php if (!$isSelf): ?>
                                            <!-- Suspend / Reactivate Account Button -->
                                            <form method="post" class="inline" onsubmit="return confirm('<?= $uStatus === 'active' ? 'Suspend this account? User will be blocked from logging in.' : 'Reactivate this user account?' ?>');">
                                                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?= e($uid) ?>">
                                                <input type="hidden" name="status" value="<?= $uStatus === 'active' ? 'banned' : 'active' ?>">
                                                <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg border transition cursor-pointer <?= $uStatus === 'active' ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' ?>">
                                                    <?= $uStatus === 'active' ? 'Suspend' : 'Reactivate' ?>
                                                </button>
                                            </form>

                                            <!-- Delete User Account -->
                                            <form method="post" class="inline" onsubmit="return confirm('Permanently delete user &quot;<?= e(addslashes((string)$u['name'])) ?>&quot;? All associated records will be removed.');">
                                                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?= e($uid) ?>">
                                                <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 transition cursor-pointer">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400 italic">Self (Protected)</span>
                                        <?php endif; ?>
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
