<?php
declare(strict_types=1);

/**
 * Local PHP server router: protects private project files from direct access.
 */
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

if (
    str_contains($path, "\0") ||
    preg_match('~(^|/)\.(?!well-known(?:/|$))~', $path) ||
    preg_match('~^/(?:vendor|src|scripts|partials)(?:/|$)~i', $path) ||
    preg_match('~^/(?:composer\.(?:json|lock)|README\.md|bootstrap\.php|router\.php|\.htaccess|\.env.*)$~i', $path)
) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found';
    return true;
}

$file = __DIR__ . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
if (is_dir($file)) {
    $indexPath = rtrim($file, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.php';
    if (is_file($indexPath)) {
        require $indexPath;
        return true;
    }
}

return false;
