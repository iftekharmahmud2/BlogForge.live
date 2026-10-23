<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use BlogForge\Database;

handle_cors();

$extensionLoaded = extension_loaded('mongodb');
$rawUri = $_ENV['MONGODB_URI'] ?? $_SERVER['MONGODB_URI'] ?? getenv('MONGODB_URI') ?: '';
$uriSet = trim((string)$rawUri) !== '';

// Redact password for safe display
$safeUri = 'not set (defaulting to 127.0.0.1)';
if ($uriSet) {
    $safeUri = preg_replace('/(:\/\/[^:]+:)[^@]+(@)/', '$1*****$2', (string)$rawUri);
}

$db = Database::connection();
$lastError = Database::getLastError();

$status = $db !== null ? 'online' : 'offline';
$httpCode = $db !== null ? 200 : 503;

json_response([
    'status' => $status,
    'php_version' => PHP_VERSION,
    'mongodb_extension' => $extensionLoaded,
    'mongodb_uri_set' => $uriSet,
    'mongodb_target' => $safeUri,
    'error' => $lastError,
], $httpCode);
