<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use BlogForge\Database;

handle_cors();

check_rate_limit('health_check', 30, 60);

$db = Database::connection();
$status = $db !== null ? 'online' : 'offline';
$httpCode = $db !== null ? 200 : 503;

// Detailed internal diagnostics restricted to administrators or CLI
if (is_admin() || PHP_SAPI === 'cli') {
    $extensionLoaded = extension_loaded('mongodb');
    $rawUri = $_ENV['MONGODB_URI'] ?? $_SERVER['MONGODB_URI'] ?? getenv('MONGODB_URI') ?: '';
    $uriSet = trim((string)$rawUri) !== '';

    $safeUri = 'not set (defaulting to 127.0.0.1)';
    if ($uriSet) {
        $safeUri = preg_replace('/(:\/\/[^:]+:)[^@]+(@)/', '$1*****$2', (string)$rawUri);
    }

    $lastError = Database::getLastError();

    json_response([
        'status' => $status,
        'php_version' => PHP_VERSION,
        'mongodb_extension' => $extensionLoaded,
        'mongodb_uri_set' => $uriSet,
        'mongodb_target' => $safeUri,
        'error' => $lastError,
    ], $httpCode);
}

// Minimal safe public payload without leaking infrastructure details
json_response([
    'status' => $status,
    'timestamp' => time(),
], $httpCode);

