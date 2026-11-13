<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

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
check_rate_limit('admin_settings', 60, 60);

$d = json_input();
if (empty($d)) {
    $d = $_POST;
}

try {
    if (isset($d['site_title'])) {
        set_setting('site_title', trim((string)$d['site_title']));
    }
    if (isset($d['site_tagline'])) {
        set_setting('site_tagline', trim((string)$d['site_tagline']));
    }
    if (isset($d['site_description'])) {
        set_setting('site_description', trim((string)$d['site_description']));
    }
    if (isset($d['maintenance_mode'])) {
        $m = ($d['maintenance_mode'] === '1' || $d['maintenance_mode'] === true) ? '1' : '0';
        set_setting('maintenance_mode', $m);
    }
    if (isset($d['default_role'])) {
        $r = in_array($d['default_role'], ['reader', 'author'], true) ? $d['default_role'] : 'reader';
        set_setting('default_role', $r);
    }
    if (isset($d['comments_policy'])) {
        $c = in_array($d['comments_policy'], ['approved', 'pending'], true) ? $d['comments_policy'] : 'approved';
        set_setting('comments_policy', $c);
    }

    json_response(['ok' => true, 'message' => 'Settings saved successfully.']);
} catch (\Throwable $e) {
    json_response(['error' => $e->getMessage()], 422);
}
