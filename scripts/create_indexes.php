<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

require dirname(__DIR__) . '/bootstrap.php';

$db = db();
if (!$db) {
    echo "[-] MongoDB connection failed. Check your .env configuration.\n";
    exit(1);
}

echo "[*] Creating database indexes for BlogForge...\n";

try {
    $db->users->createIndex(['email' => 1], ['unique' => true]);
    $db->users->createIndex(['handle' => 1], ['unique' => true]);
    $db->posts->createIndex(['slug' => 1], ['unique' => true]);
    $db->posts->createIndex(['status' => 1, 'published_at' => -1]);
    $db->posts->createIndex(['category' => 1, 'published_at' => -1]);
    $db->posts->createIndex(['author.id' => 1, 'created_at' => -1]);
    $db->posts->createIndex(['title' => 'text', 'excerpt' => 'text', 'body' => 'text']);
    $db->comments->createIndex(['post_id' => 1, 'created_at' => -1]);
    $db->reactions->createIndex(['post_id' => 1, 'user_id' => 1, 'type' => 1], ['unique' => true]);
    $db->categories->createIndex(['slug' => 1], ['unique' => true]);

    echo "[+] BlogForge MongoDB indexes created successfully.\n";
} catch (\Throwable $e) {
    echo "[-] Error creating indexes: ", $e->getMessage(), "\n";
    exit(1);
}
