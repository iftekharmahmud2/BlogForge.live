<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

require dirname(__DIR__) . '/bootstrap.php';

use MongoDB\BSON\UTCDateTime;

$db = db();
if (!$db) {
    echo "[-] MongoDB connection failed. Please check .env and run check_connection.php\n";
    exit(1);
}

$email = trim($argv[1] ?? '');
$password = trim($argv[2] ?? '');
$name = trim($argv[3] ?? 'BlogForge Admin');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    echo "Usage: php scripts/create_admin.php admin@example.com StrongPassword123 \"Admin Name\"\n";
    exit(1);
}

$email = strtolower($email);
$now = new UTCDateTime();

$db->users->updateOne(
    ['email' => $email],
    [
        '$set' => [
            'name' => $name,
            'email' => $email,
            'handle' => 'admin',
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin',
            'updated_at' => $now,
        ],
        '$setOnInsert' => [
            'bio' => 'BlogForge Administrator',
            'avatar' => '',
            'created_at' => $now,
        ],
    ],
    ['upsert' => true]
);

echo "[+] Admin account ready: {$email}\n";
echo "    Role: admin | You can now sign in at /auth.php\n";
