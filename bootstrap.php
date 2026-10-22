<?php
declare(strict_types=1);

// Autoload dependencies or register PSR-4 fallback autoloader
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'BlogForge\\';
        if (str_starts_with($class, $prefix)) {
            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = __DIR__ . '/src/' . $relative . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    });
}

// Load environment variables from .env
if (file_exists(__DIR__ . '/.env')) {
    if (class_exists('Dotenv\\Dotenv')) {
        Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
    } else {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            $v = trim($v, "\"'\r\n");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
            putenv("{$k}={$v}");
        }
    }
}

// Session security setup (web only, when headers have not been sent)
if (PHP_SAPI !== 'cli' && !headers_sent() && session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_name('blogforge_session');
    $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => $isSecure,
    ]);
}

date_default_timezone_set('UTC');
require_once __DIR__ . '/src/helpers.php';
