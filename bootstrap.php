<?php
declare(strict_types=1);

if (defined('BLOGFORGE_BOOTSTRAPPED')) {
    return;
}
define('BLOGFORGE_BOOTSTRAPPED', true);

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

// Environment & Debug Mode Configuration
$appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';
if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// Global Security Headers (web only)
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header_remove('X-Powered-By');
    @ini_set('expose_php', '0');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https: blob:; connect-src 'self' https://api.cloudinary.com; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");

    $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if ($isSecure) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Session security setup (web only, when headers have not been sent)
if (PHP_SAPI !== 'cli' && !headers_sent() && session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('blogforge_session');
    $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => $isSecure,
    ]);

    // Server-side session inactivity expiry: auto-destroy idle sessions after 2 hours
    $maxIdleTime = 7200; // 2 hours in seconds
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxIdleTime)) {
        session_unset();
        session_destroy();
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_secure' => $isSecure,
        ]);
    }
    $_SESSION['last_activity'] = time();
}

date_default_timezone_set('UTC');
require_once __DIR__ . '/src/helpers.php';

// Auto-synchronize session user, ID, and role if active
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user'])) {
    current_user();
}

// Global Maintenance Mode Guard
if (PHP_SAPI !== 'cli') {
    $isExempt = false;

    $exemptPrefixes = [
        '/admin/',
        '/api/admin/',
        '/api/auth/',
        '/api/health',
        '/assets/',
        '/uploads/',
    ];

    $exemptFiles = [
        'maintenance.php',
        'auth.php',
    ];

    $scriptBase = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $rootDir = str_replace('\\', '/', __DIR__);
    $relFile = str_starts_with($scriptFile, $rootDir) ? substr($scriptFile, strlen($rootDir)) : '';

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';

    if (in_array($scriptBase, $exemptFiles, true)) {
        $isExempt = true;
    }

    if (!$isExempt) {
        foreach ($exemptPrefixes as $prefix) {
            if (str_starts_with($relFile, $prefix) || str_contains($path, $prefix)) {
                $isExempt = true;
                break;
            }
        }
    }

    if (!$isExempt && (str_contains($path, 'auth.php') || str_contains($path, 'maintenance.php'))) {
        $isExempt = true;
    }

    if (!$isExempt) {
        $isMaintenance = ((string)get_setting('maintenance_mode', '0')) === '1';
        if ($isMaintenance && !is_admin()) {
            http_response_code(503);
            header('Retry-After: 300');

            $isApi = str_starts_with($path, '/api/') ||
                     str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

            if ($isApi) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'error' => 'Site is currently undergoing maintenance. Please check back shortly.',
                    'maintenance' => true,
                ], JSON_UNESCAPED_SLASHES);
                exit;
            }

            require __DIR__ . '/maintenance.php';
            exit;
        }
    }
}
