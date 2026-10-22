<?php
declare(strict_types=1);

/**
 * Removes the sample stories that scripts/seed.php used to insert.
 * Usage:  php scripts/remove_demo_posts.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

require dirname(__DIR__) . '/bootstrap.php';

$db = db();
if (!$db) {
    echo "[-] MongoDB connection failed.\n";
    exit(1);
}

$slugs = array_map(fn($p) => $p['slug'], sample_posts());
$result = $db->posts->deleteMany(['slug' => ['$in' => $slugs]]);

echo "[+] Removed " . $result->getDeletedCount() . " demo stories.\n";
