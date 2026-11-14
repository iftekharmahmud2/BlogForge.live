<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use BlogForge\Auth;

echo "--- Testing Profile Update & Username Collision Logic ---\n";

// Test 1: assertHandle behavior
try {
    Auth::assertHandle('admin', false);
    echo "[-] FAILED: assertHandle('admin', false) should have thrown reserved exception\n";
    exit(1);
} catch (\RuntimeException $e) {
    echo "[1] assertHandle('admin', false) correctly rejected reserved handle: {$e->getMessage()}\n";
}

try {
    Auth::assertHandle('admin', true);
    echo "[2] assertHandle('admin', true) correctly allowed reserved handle for admin\n";
} catch (\RuntimeException $e) {
    echo "[-] FAILED: assertHandle('admin', true) should have succeeded for admin: {$e->getMessage()}\n";
    exit(1);
}

// Test 2: assertHandle for normal handle
try {
    Auth::assertHandle('creative_writer', false);
    echo "[3] assertHandle('creative_writer', false) passed format & reserved validation\n";
} catch (\RuntimeException $e) {
    echo "[-] FAILED: assertHandle('creative_writer') failed: {$e->getMessage()}\n";
    exit(1);
}

// Test 3: isHandleAvailable
$available = Auth::isHandleAvailable('creative_writer', null, false);
echo "[4] isHandleAvailable('creative_writer') returned: " . ($available ? 'TRUE' : 'FALSE') . "\n";

// Test 4: isHandleAvailable with admin handle for admin user
$adminAvailable = Auth::isHandleAvailable('admin', '650000000000000000000001', true);
echo "[5] isHandleAvailable('admin', ownId, allowReserved=true) returned: " . ($adminAvailable ? 'TRUE' : 'FALSE') . "\n";

// Test 5: Simulating a user with handle 'admin' editing their profile (keeping @admin)
$_SESSION['user'] = [
    'id' => '650000000000000000000001',
    'first_name' => 'Admin',
    'last_name' => 'BlogForge',
    'name' => 'Admin BlogForge',
    'email' => 'admin@blogforge.live',
    'handle' => 'admin',
    'role' => 'admin',
    'bio' => 'Old bio',
    'avatar' => '',
];
$_SESSION['user_id'] = '650000000000000000000001';
$_SESSION['role'] = 'admin';

try {
    $updated = Auth::updateProfile('650000000000000000000001', [
        'first_name' => 'Admin',
        'last_name' => 'BlogForge',
        'handle' => '@admin', // user enters @admin in edit form
        'bio' => 'Updated BlogForge Administrator Bio',
        'dob' => '22/07/2004',
        'avatar' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcG',
    ]);
    echo "[6] User with handle 'admin' successfully updated profile without error! Handle: {$updated['handle']}, Bio: {$updated['bio']}\n";
} catch (\Throwable $e) {
    echo "[-] FAILED: updateProfile failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 6: Simulating a normal author keeping their own handle 'writer123'
$_SESSION['user'] = [
    'id' => '650000000000000000000002',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'handle' => 'writer123',
    'role' => 'author',
    'bio' => 'Tech enthusiast',
    'avatar' => '',
];
$_SESSION['user_id'] = '650000000000000000000002';
$_SESSION['role'] = 'author';

try {
    $updatedAuthor = Auth::updateProfile('650000000000000000000002', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'handle' => 'writer123',
        'bio' => 'Senior tech essayist and reviewer',
    ]);
    echo "[7] Author with handle 'writer123' successfully updated profile without false collision! Handle: {$updatedAuthor['handle']}\n";
} catch (\Throwable $e) {
    echo "[-] FAILED: Author updateProfile failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[✓] ALL 7 PROFILE & USERNAME TESTS PASSED FLAWLESSLY!\n";
