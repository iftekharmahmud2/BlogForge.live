<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;

$email = strtolower(trim($argv[1] ?? 'iftekhar69c@gmail.com'));
$customUri = trim($argv[2] ?? '');

echo "=============================================\n";
echo "   BlogForge Admin Role Elevation Tool\n";
echo "=============================================\n";
echo "[*] Target user: {$email}\n";

$db = null;
if ($customUri !== '') {
    echo "[*] Using custom MongoDB URI provided via CLI...\n";
    try {
        $client = new Client($customUri, ['serverSelectionTimeoutMS' => 5000]);
        $dbName = envv('MONGODB_DATABASE', 'blogforge') ?: 'blogforge';
        $client->selectDatabase($dbName)->command(['ping' => 1]);
        $db = $client->selectDatabase($dbName);
    } catch (\Throwable $e) {
        echo "[-] Custom URI connection failed: " . $e->getMessage() . "\n";
    }
}

if (!$db) {
    $db = db();
}

// Always ensure offline admin registry is updated
$adminsFile = dirname(__DIR__) . '/config/admins.json';
$adminsDir = dirname($adminsFile);
if (!is_dir($adminsDir)) {
    mkdir($adminsDir, 0755, true);
}
$adminList = file_exists($adminsFile) ? json_decode((string)file_get_contents($adminsFile), true) : [];
if (!is_array($adminList)) {
    $adminList = [];
}
if (!in_array($email, $adminList, true)) {
    $adminList[] = $email;
    file_put_contents($adminsFile, json_encode(array_values(array_unique($adminList)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "[+] Added {$email} to config/admins.json\n";
}

if ($db) {
    try {
        $user = $db->users->findOne([
            '$or' => [
                ['email' => $email],
                ['email' => new MongoDB\BSON\Regex('^' . preg_quote($email, '/') . '$', 'i')],
            ]
        ]);

        $now = new UTCDateTime();

        if ($user) {
            $db->users->updateOne(
                ['_id' => $user['_id']],
                [
                    '$set' => [
                        'role' => 'admin',
                        'status' => 'active',
                        'updated_at' => $now,
                    ]
                ]
            );

            echo "\n[✓] SUCCESS: User {$email} elevated to ADMIN in MongoDB database!\n";
            echo "    Document ID: " . (string)$user['_id'] . "\n";
            echo "    Full Name:   " . ($user['name'] ?? 'Iftekhar Mahmud') . "\n";
            echo "    Handle:      @" . ($user['handle'] ?? 'iftekhar') . "\n";
            echo "    Role:        admin\n";
            echo "    Status:      active\n";
        } else {
            echo "[-] User {$email} was not found in MongoDB. Creating new Admin record...\n";
            $defaultPass = 'Admin@123';
            $handle = 'iftekhar';

            $taken = $db->users->findOne(['handle' => $handle]);
            if ($taken) {
                $handle = 'iftekhar_' . substr(bin2hex(random_bytes(2)), 0, 4);
            }

            $doc = [
                'first_name' => 'Iftekhar',
                'last_name' => 'Mahmud',
                'name' => 'Iftekhar Mahmud',
                'email' => $email,
                'handle' => $handle,
                'dob' => '01/01/1998',
                'password_hash' => password_hash($defaultPass, PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active',
                'bio' => 'BlogForge Administrator & Lead Engineer',
                'avatar' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $res = $db->users->insertOne($doc);
            echo "\n[✓] SUCCESS: Created new Administrator account in MongoDB!\n";
            echo "    Document ID: " . (string)$res->getInsertedId() . "\n";
            echo "    Email:       {$email}\n";
            echo "    Handle:      @{$handle}\n";
            echo "    Password:    {$defaultPass}\n";
            echo "    Role:        admin\n";
            echo "    Status:      active\n";
        }
    } catch (\Throwable $e) {
        echo "[-] Database update error: " . $e->getMessage() . "\n";
    }
} else {
    echo "\n[!] Notice: Live MongoDB database is currently offline or unreachable.\n";
    echo "[✓] Built-in admin promotion configured for: {$email}\n";
    echo "    - Role 'admin' is enforced automatically upon sign-in and session start.\n";
    echo "    - Strict guards across /admin/ will recognize {$email} as Administrator.\n";
    echo "    - When MongoDB connection is restored, the role will sync to the database.\n";
}

echo "=============================================\n";
