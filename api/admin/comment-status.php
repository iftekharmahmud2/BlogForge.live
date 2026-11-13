<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

use BlogForge\Repositories\CommentRepository;

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
check_rate_limit('admin_comment', 60, 60);

$d = json_input();
if (empty($d)) {
    $d = $_POST;
}

$id = trim((string)($d['id'] ?? ''));
if ($id === '') {
    json_response(['error' => 'Missing comment ID.'], 400);
}

// Validate ObjectId
try {
    new MongoDB\BSON\ObjectId($id);
} catch (\Throwable) {
    json_response(['error' => 'Invalid comment ID format.'], 422);
}

$repo = new CommentRepository();
$action = (string)($d['action'] ?? 'status');

try {
    if ($action === 'delete') {
        $repo->delete($id, current_user());
        json_response(['ok' => true, 'message' => 'Comment deleted successfully.']);
    }

    $status = (string)($d['status'] ?? 'approved');
    if (!in_array($status, ['approved', 'pending', 'spam'], true)) {
        json_response(['error' => 'Invalid status. Must be approved, pending, or spam.'], 422);
    }

    $repo->updateStatus($id, $status);
    json_response(['ok' => true, 'status' => $status, 'message' => 'Comment status updated.']);
} catch (\Throwable $e) {
    json_response(['error' => $e->getMessage()], 422);
}
