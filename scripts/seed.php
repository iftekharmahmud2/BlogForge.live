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
    echo "[-] MongoDB connection failed. Cannot seed database.\n";
    exit(1);
}

echo "[*] Seeding default categories...\n";
$cats = ['Technology', 'Lifestyle', 'Business', 'Education', 'Health', 'Travel', 'Culture', 'Self Improvement'];
foreach ($cats as $name) {
    $db->categories->updateOne(
        ['slug' => slugify($name)],
        ['$set' => ['name' => $name, 'slug' => slugify($name), 'updated_at' => new UTCDateTime()]],
        ['upsert' => true]
    );
}

if (!in_array('--demo', $argv ?? [], true)) {
    echo "[+] Categories seeded. (Pass --demo to also insert sample stories.)\n";
    exit(0);
}

echo "[*] Seeding sample published stories...\n";
foreach (sample_posts() as $p) {
    unset($p['_id']);
    $p['status'] = 'published';
    $timeMs = ((int)$p['published_at']) * 1000;
    $date = new UTCDateTime($timeMs);
    $p['published_at'] = $date;
    $p['created_at'] = $date;
    $p['updated_at'] = $date;

    $db->posts->updateOne(
        ['slug' => $p['slug']],
        ['$set' => $p],
        ['upsert' => true]
    );
}

echo "[+] Successfully seeded categories and sample stories into MongoDB.\n";
