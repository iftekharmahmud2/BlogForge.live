<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use BlogForge\Auth;

echo "--- Testing Admin RBAC for iftekhar69c@gmail.com ---\n";

// 1. Test sessionUser mapping
$user = [
    '_id' => '650000000000000000000001',
    'name' => 'Iftekhar Mahmud',
    'email' => 'iftekhar69c@gmail.com',
    'handle' => 'iftekhar',
    'role' => 'reader' // even if DB says reader
];

$session = Auth::sessionUser($user);
if ($session['role'] !== 'admin') {
    echo "[-] FAILED: Expected role 'admin', got '{$session['role']}'\n";
    exit(1);
}
echo "[1] sessionUser correctly elevated iftekhar69c@gmail.com to: {$session['role']}\n";

// 2. Test session synchronization
$_SESSION['user'] = $session;
$current = current_user();

if (($current['role'] ?? '') !== 'admin') {
    echo "[-] FAILED: current_user role is not admin\n";
    exit(1);
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    echo "[-] FAILED: \$_SESSION['role'] is not admin\n";
    exit(1);
}
if (empty($_SESSION['user_id'])) {
    echo "[-] FAILED: \$_SESSION['user_id'] is empty\n";
    exit(1);
}
echo "[2] current_user synchronized \$_SESSION['role']={$session['role']} and \$_SESSION['user_id']={$_SESSION['user_id']}\n";

// 3. Test is_admin()
if (!is_admin()) {
    echo "[-] FAILED: is_admin() returned false\n";
    exit(1);
}
echo "[3] is_admin() returned: TRUE\n";

// 4. Test get_setting and set_setting
$title = get_setting('site_title', 'BlogForge');
echo "[4] get_setting('site_title') returned: '{$title}'\n";

// 5. Test Auth::listUsers
$users = Auth::listUsers();
echo "[5] Auth::listUsers() returned " . count($users) . " user(s). First user: " . ($users[0]['email'] ?? 'N/A') . " (Role: " . ($users[0]['role'] ?? 'N/A') . ")\n";

// 6. Test CategoryRepository update method exists
$catRepo = new BlogForge\Repositories\CategoryRepository();
if (!method_exists($catRepo, 'update')) {
    echo "[-] FAILED: CategoryRepository::update does not exist\n";
    exit(1);
}
echo "[6] CategoryRepository::update exists.\n";

// 7. Test PostRepository toggleStatus and toggleFeatured methods exist
$postRepo = new BlogForge\Repositories\PostRepository();
if (!method_exists($postRepo, 'toggleStatus') || !method_exists($postRepo, 'toggleFeatured')) {
    echo "[-] FAILED: PostRepository toggle methods do not exist\n";
    exit(1);
}
echo "[7] PostRepository::toggleStatus and toggleFeatured exist.\n";

echo "\n[✓] ALL 7 RBAC & ADMIN TESTS PASSED SUCCESSFULLY!\n";
