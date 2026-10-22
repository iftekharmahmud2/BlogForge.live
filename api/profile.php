<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use BlogForge\Auth;

handle_cors();

if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['POST', 'PUT', 'PATCH'], true)) {
    json_response(['error' => 'Method not allowed.'], 405);
}

require_login();
verify_csrf();

$d = json_input();
if (empty($d)) {
    $d = $_POST;
}

$user = current_user();
$action = trim((string)($d['action'] ?? 'update'));

try {
    if ($action === 'password') {
        Auth::changePassword(
            (string)$user['id'],
            (string)($d['current_password'] ?? ''),
            (string)($d['new_password'] ?? ''),
            (string)($d['confirm_password'] ?? '')
        );
        json_response(['ok' => true, 'message' => 'Password updated.']);
    }

    $updated = Auth::updateProfile((string)$user['id'], $d);
    json_response(['ok' => true, 'user' => $updated, 'message' => 'Profile updated.']);
} catch (\RuntimeException $e) {
    json_response(['error' => $e->getMessage()], 422);
} catch (\Throwable $e) {
    error_log('[BlogForge] profile error: ' . $e->getMessage());
    json_response(['error' => 'Something went wrong. Please try again.'], 500);
}
