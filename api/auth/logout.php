<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

// Protect against CSRF-triggered logout via <img> tags or cross-site links.
// Allow POST with CSRF token (preferred), or GET if the user is actually signed in
// and navigated here intentionally (referer from same host).
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    verify_csrf();
} elseif ($method === 'GET') {
    // For direct GET navigation: only allow if user is actually logged in
    // and the request comes from the same host (not an external <img> embed)
    if (!is_logged_in()) {
        redirect(app_url());
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $serverHost = $_SERVER['HTTP_HOST'] ?? '';
    if ($referer !== '' && $serverHost !== '') {
        $refererHost = parse_url($referer, PHP_URL_HOST);
        if ($refererHost !== null && $refererHost !== $serverHost) {
            // Cross-origin GET request — likely a CSRF attempt, silently ignore
            redirect(app_url());
        }
    }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

$next = safe_next_url($_GET['next'] ?? $_POST['next'] ?? null, app_url());
redirect($next);
