<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

use BlogForge\Auth;

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
check_rate_limit('admin_users', 60, 60);

$d = json_input();
if (empty($d)) {
    $d = $_POST;
}

$id = trim((string)($d['user_id'] ?? $d['id'] ?? ''));
if ($id === '') {
    json_response(['error' => 'Missing target user ID.'], 400);
}

try {
    new MongoDB\BSON\ObjectId($id);
} catch (\Throwable) {
    json_response(['error' => 'Invalid user ID format.'], 422);
}

$currentAdminId = (string)($_SESSION['user_id'] ?? '');
$action = (string)($d['action'] ?? '');

try {
    if ($action === 'change_role') {
        $role = (string)($d['role'] ?? 'reader');
        Auth::updateUserRole($id, $role, $currentAdminId);
        json_response(['ok' => true, 'role' => $role, 'message' => 'Role updated successfully.']);
    } elseif ($action === 'toggle_status') {
        $status = (string)($d['status'] ?? 'active');
        Auth::updateUserStatus($id, $status, $currentAdminId);
        json_response(['ok' => true, 'status' => $status, 'message' => 'Status updated successfully.']);
    } elseif ($action === 'delete') {
        Auth::deleteUser($id, $currentAdminId);
        json_response(['ok' => true, 'message' => 'User account deleted successfully.']);
    } else {
        json_response(['error' => 'Unknown action.'], 400);
    }
} catch (\Throwable $e) {
    json_response(['error' => $e->getMessage()], 422);
}
