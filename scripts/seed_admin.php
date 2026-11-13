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
    echo "[-] MongoDB connection offline. Ensure MONGODB_URI is set.\n";
    exit(1);
}

echo "[*] Seeding BlogForge Admin, Categories, and Default Settings...\n";

// 1. Seed Admin
$adminEmail = 'admin@blogforge.live';
$adminPass = 'Admin@123';
$now = new UTCDateTime();

$adminDoc = [
    'first_name' => 'Admin',
    'last_name' => 'BlogForge',
    'name' => 'Admin BlogForge',
    'email' => $adminEmail,
    'handle' => 'admin',
    'dob' => '01/01/1990',
    'password_hash' => password_hash($adminPass, PASSWORD_DEFAULT),
    'role' => 'admin',
    'status' => 'active',
    'bio' => 'Official platform administrator for BlogForge.',
    'avatar' => '',
    'updated_at' => $now,
];

$existingAdmin = $db->users->findOne(['email' => $adminEmail]);
if ($existingAdmin) {
    $db->users->updateOne(['email' => $adminEmail], ['$set' => $adminDoc]);
    echo "[+] Updated Admin account: {$adminEmail} (Role: admin)\n";
    $adminId = (string)$existingAdmin['_id'];
} else {
    $adminDoc['created_at'] = $now;
    $res = $db->users->insertOne($adminDoc);
    echo "[+] Created Admin account: {$adminEmail} (Role: admin)\n";
    $adminId = (string)$res->getInsertedId();
}

// 2. Seed 5 Requested Categories
$categories = [
    ['name' => 'Technology', 'slug' => 'technology'],
    ['name' => 'Tutorials', 'slug' => 'tutorials'],
    ['name' => 'Case Studies', 'slug' => 'case-studies'],
    ['name' => 'Opinion', 'slug' => 'opinion'],
    ['name' => 'News', 'slug' => 'news'],
    ['name' => 'Lifestyle', 'slug' => 'lifestyle'],
    ['name' => 'Business', 'slug' => 'business'],
    ['name' => 'Travel', 'slug' => 'travel'],
];

foreach ($categories as $cat) {
    $db->categories->updateOne(
        ['slug' => $cat['slug']],
        [
            '$set' => [
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'updated_at' => $now,
            ],
            '$setOnInsert' => ['created_at' => $now],
        ],
        ['upsert' => true]
    );
}
echo "[+] Seeded categories.\n";

// 3. Seed Default Site Settings
$settings = [
    'site_title' => 'BlogForge',
    'site_tagline' => 'Where ideas meet ink. A distraction-free publishing space for deep work, essays, and stories.',
    'site_description' => 'BlogForge is an independent editorial publishing platform designed for writers, essayists, and curious readers.',
    'maintenance_mode' => '0',
    'default_role' => 'reader',
    'comments_policy' => 'approved',
];

foreach ($settings as $k => $v) {
    $db->settings->updateOne(
        ['key' => $k],
        [
            '$set' => [
                'key' => $k,
                'value' => $v,
                'updated_at' => $now,
            ],
            '$setOnInsert' => ['created_at' => $now],
        ],
        ['upsert' => true]
    );
}
echo "[+] Seeded default site settings.\n";

echo "\n[✓] Seed completed successfully!\n";
echo "    Login: {$adminEmail}\n";
echo "    Password: {$adminPass}\n";
echo "    Role: admin\n";
