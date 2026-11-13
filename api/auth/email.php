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
    if ($action === 'check_handle') {
        check_rate_limit('check_handle', 60, 60);
        $handle = trim((string)($d['handle'] ?? $d['username'] ?? ''));
        $available = Auth::isHandleAvailable($handle);
        json_response([
            'ok' => true,
            'handle' => Auth::cleanHandle($handle),
            'available' => $available,
        ]);
    }

    check_rate_limit('auth_email', 15, 60);

    if ($action === 'register') {
        $user = Auth::registerEmail(
            (string)($d['first_name'] ?? ''),
            (string)($d['last_name'] ?? ''),
            (string)($d['email'] ?? ''),
            (string)($d['password'] ?? ''),
            (string)($d['confirm_password'] ?? ''),
            filter_var($d['terms'] ?? false, FILTER_VALIDATE_BOOLEAN),
            (string)($d['handle'] ?? $d['username'] ?? ''),
            (string)($d['dob'] ?? '')
        );
    } else {
        $user = Auth::loginEmail(
            (string)($d['email'] ?? $d['username'] ?? ''),
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
