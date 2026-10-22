<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

use BlogForge\Auth;

handle_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed.'], 405);
}

verify_csrf();

$d = json_input();
if (empty($d)) {
    $d = $_POST;
}

$action = trim((string)($d['action'] ?? 'login'));

try {
    if ($action === 'register') {
        $user = Auth::registerEmail(
            (string)($d['first_name'] ?? ''),
            (string)($d['last_name'] ?? ''),
            (string)($d['email'] ?? ''),
            (string)($d['password'] ?? ''),
            (string)($d['confirm_password'] ?? ''),
            filter_var($d['terms'] ?? false, FILTER_VALIDATE_BOOLEAN)
        );
    } else {
        $user = Auth::loginEmail(
            (string)($d['email'] ?? ''),
            (string)($d['password'] ?? '')
        );
    }

    json_response(['ok' => true, 'user' => $user]);
} catch (\RuntimeException $e) {
    json_response(['error' => $e->getMessage()], 422);
} catch (\Throwable $e) {
    error_log('[BlogForge] auth error: ' . $e->getMessage());
    json_response(['error' => 'Something went wrong. Please try again.'], 500);
}
