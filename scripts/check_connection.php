<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

$root = dirname(__DIR__);

if (!is_file($root . '/.env')) {
    echo "=========================================\n";
    echo "  BlogForge MongoDB Diagnostic Check\n";
    echo "=========================================\n";
    echo "[-] Project .env file: MISSING\n";
    echo "    Fix: Create .env inside blogforge-site by copying .env.example\n";
    exit(1);
}

require_once $root . '/bootstrap.php';

echo "=========================================\n";
echo "  BlogForge MongoDB Diagnostic Check\n";
echo "=========================================\n";
echo "[+] PHP Version: ", PHP_VERSION, "\n";
echo "[+] Project .env: FOUND\n";

$uri = envv('MONGODB_URI', '');
if ($uri === '') {
    echo "[-] MONGODB_URI: MISSING in .env\n";
    exit(1);
}
echo "[+] MONGODB_URI: SET (credentials hidden)\n";

if (str_contains($uri, '<db_password>')) {
    echo "[-] WARNING: MONGODB_URI contains unreplaced placeholder '<db_password>'.\n";
    echo "    Please replace '<db_password>' in your .env with your actual MongoDB Atlas database user password.\n";
    exit(1);
}

$extensionLoaded = extension_loaded('mongodb');
echo $extensionLoaded ? "[+] MongoDB PHP extension: ENABLED\n" : "[-] MongoDB PHP extension: MISSING\n";
if (!$extensionLoaded) {
    echo "    Fix: Enable the 'mongodb' extension in php.ini.\n";
    exit(1);
}

$autoloadFound = is_file($root . '/vendor/autoload.php');
echo $autoloadFound ? "[+] Composer autoload: FOUND\n" : "[-] Composer autoload: MISSING\n";
if (!$autoloadFound) {
    echo "    Fix: Run 'composer install' inside the blogforge-site folder.\n";
    exit(1);
}

if (!class_exists(\MongoDB\Client::class)) {
    echo "[-] MongoDB PHP library class not found. Run 'composer install'.\n";
    exit(1);
}

$dbName = envv('MONGODB_DATABASE', 'blogforge') ?? 'blogforge';
echo "[*] Attempting connection to database '{$dbName}'...\n";

try {
    $client = new \MongoDB\Client($uri, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
    ]);

    $client->selectDatabase($dbName)->command(['ping' => 1]);
    echo "[+] SUCCESS: Connected to MongoDB successfully!\n";
    echo "    Database '{$dbName}' is ready for operations.\n";
    exit(0);
} catch (\Throwable $e) {
    $msg = $e->getMessage();
    $msg = preg_replace('~mongodb(?:\+srv)?://[^\s]+~i', '[REDACTED_URI]', $msg) ?? $msg;
    $pass = parse_url($uri, PHP_URL_PASS);
    if (is_string($pass) && $pass !== '') {
        $msg = str_replace($pass, '[REDACTED_PASSWORD]', $msg);
    }

    echo "[-] FAILED: ", $msg, "\n\n";
    echo "TROUBLESHOOTING GUIDE:\n";
    echo "  1. If using MongoDB Atlas:\n";
    echo "     - Confirm your Atlas IP Access List allows your current IP (or 0.0.0.0/0 for testing).\n";
    echo "     - Check Database Access: ensure database user exists and password is correct.\n";
    echo "     - Ensure '<db_password>' in .env is replaced with the real password.\n";
    echo "  2. If using Local MongoDB:\n";
    echo "     - Ensure MongoDB service is running (mongod) on 127.0.0.1:27017.\n";
    echo "     - Set MONGODB_URI=mongodb://127.0.0.1:27017 in your .env.\n";
    exit(1);
}
