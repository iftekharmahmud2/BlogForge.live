<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

use BlogForge\Repositories\PostRepository;

handle_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed.'], 405);
}

// Strict server-side Admin Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    json_response(['error' => 'Forbidden: administrator access required'], 403);
}

require_admin();
verify_csrf();
check_rate_limit('admin_posts', 60, 60);

$d = json_input();
if (empty($d)) {
    $d = $_POST;
}

$id = trim((string)($d['id'] ?? ''));
if ($id === '') {
    json_response(['error' => 'Missing post ID.'], 400);
}

try {
    new MongoDB\BSON\ObjectId($id);
} catch (\Throwable) {
    json_response(['error' => 'Invalid post ID format.'], 422);
}

$repo = new PostRepository();
$action = (string)($d['action'] ?? '');
$user = current_user();

try {
    if ($action === 'delete') {
        $repo->delete($id, $user);
        json_response(['ok' => true, 'message' => 'Post deleted permanently.']);
    } elseif ($action === 'toggle_status') {
        $newStatus = $repo->toggleStatus($id, $user);
        json_response(['ok' => true, 'status' => $newStatus, 'message' => 'Post status updated to ' . strtoupper($newStatus) . '.']);
    } elseif ($action === 'toggle_featured') {
        $isFeatured = $repo->toggleFeatured($id, $user);
        json_response(['ok' => true, 'is_featured' => $isFeatured, 'message' => $isFeatured ? 'Post featured.' : 'Post unfeatured.']);
    } else {
        json_response(['error' => 'Unknown action.'], 400);
    }
} catch (\Throwable $e) {
    json_response(['error' => $e->getMessage()], 422);
}
