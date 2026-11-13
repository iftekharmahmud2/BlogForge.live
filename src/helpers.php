<?php
declare(strict_types=1);

use BlogForge\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;

/**
 * Retrieve environment variable across $_ENV, $_SERVER, and getenv.
 */
function envv(string $key, ?string $default = null): ?string
{
    $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($val !== false && $val !== null && trim((string)$val) !== '') {
        return trim((string)$val);
    }
    return $default;
}

/**
 * Build an absolute URL relative to the application base URL.
 */
function app_url(string $path = ''): string
{
    $default = '';
    if (!empty($_SERVER['HTTP_HOST'])) {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                  (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                  ? 'https' : 'http';
        $default = $scheme . '://' . $_SERVER['HTTP_HOST'];
    } else {
        $default = 'http://localhost:8000';
    }

    $configured = envv('APP_URL');
    $base = rtrim($configured !== null && $configured !== '' ? $configured : $default, '/');
    $path = ltrim($path, '/');
    return $path !== '' ? $base . '/' . $path : ($base !== '' ? $base : '/');
}

/**
 * Map a post category to its default editorial cover image in assets/img/covers/.
 */
function category_cover(?string $category): string
{
    static $map = [
        'business'         => '/assets/img/covers/Business.png',
        'culture'          => '/assets/img/covers/Culture.png',
        'education'        => '/assets/img/covers/Education.png',
        'health'           => '/assets/img/covers/Health.png',
        'lifestyle'        => '/assets/img/covers/Lifestyle.png',
        'self-improvement' => '/assets/img/covers/Self-improvement.png',
        'technology'       => '/assets/img/covers/Technology.png',
        'travel'           => '/assets/img/covers/Travel.png',
    ];

    $key = trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower(trim((string)$category))), '-');
    if (isset($map[$key])) {
        return $map[$key];
    }

    if ($key !== '') {
        $coversDir = dirname(__DIR__) . '/assets/img/covers';
        $entries = @scandir($coversDir);
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..' || !is_file($coversDir . '/' . $entry)) {
                    continue;
                }
                $stem = pathinfo($entry, PATHINFO_FILENAME);
                $entryKey = trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower($stem)), '-');
                if ($entryKey === $key) {
                    return '/assets/img/covers/' . $entry;
                }
            }
        }
    }

    return '/assets/img/covers/Technology.png';
}

/**
 * Resolve a cover image URL: supports external HTTP/HTTPS URLs (Unsplash/Cloudinary),
 * base64 data URIs (WebP/JPEG/PNG compressed uploads), local assets, and category fallbacks.
 */
function cover_url(?string $cover, ?string $category = null): string
{
    $cover = trim((string)$cover);
    if ($cover === '') {
        return category_cover($category);
    }

    // 1. Base64 Data URI (WebP/JPEG/PNG/GIF)
    if (preg_match('~^data:image\/(webp|jpeg|png|gif);base64,[a-zA-Z0-9\/+]+={0,2}$~i', $cover)) {
        return $cover;
    }

    // 2. HTTP/HTTPS external image (Unsplash, Cloudinary, etc.)
    if (preg_match('~^https?://~i', $cover)) {
        if (!filter_var($cover, FILTER_VALIDATE_URL)) {
            return category_cover($category);
        }
        // Ephemeral Facebook/Instagram CDN URLs expire quickly and break; use category cover instead
        $host = (string)parse_url($cover, PHP_URL_HOST);
        if (preg_match('~(^|\.)(fbcdn\.net|fbsbx\.com)$~i', $host)) {
            return category_cover($category);
        }
        // Auto-optimize Unsplash images with responsive parameters if missing
        if (str_contains($cover, 'images.unsplash.com') && !str_contains($cover, 'auto=format')) {
            $separator = str_contains($cover, '?') ? '&' : '?';
            $cover .= $separator . 'auto=format&fit=crop&w=1200&q=80';
        }
        return $cover;
    }

    // 3. Local asset path
    $clean = ltrim($cover, '/');
    if (str_contains($clean, '..') || !preg_match('~^[a-zA-Z0-9_\-\/\.]+$~', $clean)) {
        return category_cover($category);
    }

    // Resolve local cover assets with exact disk filename casing (safe for Linux/Vercel)
    // and automatically upgrade removed legacy .svg / .webp paths to the category cover.
    if (str_starts_with(strtolower($clean), 'assets/img/covers/')) {
        static $coverFiles = null;
        if ($coverFiles === null) {
            $coverFiles = [];
            $coversDir = dirname(__DIR__) . '/assets/img/covers';
            $entries = @scandir($coversDir);
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    if ($entry !== '.' && $entry !== '..' && is_file($coversDir . '/' . $entry)) {
                        $coverFiles[strtolower($entry)] = $entry;
                    }
                }
            }
        }

        if (strtolower(dirname($clean)) === 'assets/img/covers') {
            $lowerFile = strtolower(basename($clean));
            if (isset($coverFiles[$lowerFile])) {
                return '/assets/img/covers/' . $coverFiles[$lowerFile];
            }
        }

        return category_cover($category);
    }

    return '/' . $clean;
}

/**
 * Build a public profile URL for a post or comment author.
 */
function profile_url(array $author): string
{
    $handle = ltrim(trim((string)($author['handle'] ?? '')), '@');
    $id = trim((string)($author['id'] ?? $author['_id'] ?? ''));

    if ($handle !== '') {
        return app_url('profile.php?handle=' . urlencode($handle));
    }
    if ($id !== '') {
        return app_url('profile.php?id=' . urlencode($id));
    }

    return app_url('profile.php');
}

/**
 * Resolve a safe avatar URL supporting local uploads, data URIs, and valid HTTPS image links.
 */
function avatar_url(?string $avatar): string
{
    $avatar = trim((string)$avatar);
    if ($avatar === '') {
        return '';
    }

    // Local uploads directory (e.g. /uploads/avatars/xyz.webp or uploads/avatars/xyz.webp)
    if (preg_match('~^/?uploads/(avatars|covers)/[a-zA-Z0-9_\-.]+\.(webp|png|jpe?g|gif)$~i', $avatar)) {
        return app_url(ltrim($avatar, '/'));
    }

    // Local static assets directory (e.g. /assets/... or assets/...)
    if (preg_match('~^/?assets/[a-zA-Z0-9_\-/.]+\.(webp|png|jpe?g|gif|svg)$~i', $avatar)) {
        return app_url(ltrim($avatar, '/'));
    }

    // Direct Data URI format
    if (str_starts_with($avatar, 'data:image/')) {
        return $avatar;
    }

    // Remote HTTP/HTTPS URL
    if (!preg_match('~^https?://~i', $avatar) || !filter_var($avatar, FILTER_VALIDATE_URL)) {
        return '';
    }

    $host = (string)parse_url($avatar, PHP_URL_HOST);
    if (preg_match('~(^|\.)(fbcdn\.net|fbsbx\.com)$~i', $host)) {
        return '';
    }

    return $avatar;
}

/**
 * Render inner avatar image with an adjacent initials fallback element for app.js error handling.
 */
function avatar_inner(?string $avatar, string $name, bool $lazy = false): string
{
    $label = trim($name) !== '' ? $name : 'Author';
    $init = e(initials($label));
    $safeAvatar = avatar_url($avatar);

    if ($safeAvatar !== '') {
        $loadingAttr = $lazy ? ' loading="lazy"' : ' loading="eager" decoding="async"';
        return '<img data-avatar-img src="' . e($safeAvatar) . '" alt="' . e($label) . '" class="w-full h-full object-cover"' . $loadingAttr . '>'
            . '<span data-avatar-fallback class="hidden w-full h-full items-center justify-center">' . $init . '</span>';
    }

    return $init;
}

/**
 * Render a circular author/commenter avatar badge with initials fallback.
 */
function avatar_badge(?string $avatar, string $name, string $sizeClasses = 'w-8 h-8 text-xs', string $extraClasses = '', bool $lazy = false): string
{
    $base = 'rounded-full bg-gradient-to-tr from-[#11151b] to-[#ff7b22] text-white inline-flex items-center justify-center font-bold shrink-0 overflow-hidden';
    $classes = trim($base . ' ' . $sizeClasses . ' ' . $extraClasses);

    return '<span class="' . e($classes) . '">' . avatar_inner($avatar, $name, $lazy) . '</span>';
}

/**
 * Upload an image (data URI, remote URL, or binary) to Cloudinary if configured.
 * Supports CLOUDINARY_URL (cloudinary://api_key:api_secret@cloud_name) or discrete keys.
 */
function upload_to_cloudinary(string $imageData, string $folder = 'blogforge'): ?string
{
    $cloudinaryUrl = envv('CLOUDINARY_URL');
    $cloudName = envv('CLOUDINARY_CLOUD_NAME');
    $apiKey = envv('CLOUDINARY_API_KEY');
    $apiSecret = envv('CLOUDINARY_API_SECRET');
    $uploadPreset = envv('CLOUDINARY_UPLOAD_PRESET', 'blogforge_preset');

    if ($cloudinaryUrl && preg_match('~cloudinary://([^:]+):([^@]+)@([^/]+)~i', $cloudinaryUrl, $m)) {
        $apiKey = $m[1];
        $apiSecret = $m[2];
        $cloudName = $m[3];
    }

    if (!$cloudName) {
        return null;
    }

    $timestamp = time();
    $params = [
        'timestamp' => $timestamp,
        'folder' => $folder,
    ];

    if ($apiSecret && $apiKey) {
        ksort($params);
        $toSign = '';
        foreach ($params as $k => $v) {
            $toSign .= ($toSign === '' ? '' : '&') . $k . '=' . $v;
        }
        $params['signature'] = hash('sha256', $toSign . $apiSecret);
        $params['api_key'] = $apiKey;
    } else {
        $params['upload_preset'] = $uploadPreset;
    }

    $params['file'] = $imageData;

    $ch = curl_init('https://api.cloudinary.com/v1_1/' . rawurlencode($cloudName) . '/image/upload');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 200 && is_string($res)) {
        $json = json_decode($res, true);
        if (!empty($json['secure_url'])) {
            return (string)$json['secure_url'];
        }
    }

    return null;
}

/**
 * Sanitize output for HTML context to prevent Cross-Site Scripting (XSS).
 */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Get active MongoDB connection instance.
 */
function db(): ?\MongoDB\Database
{
    return Database::connection();
}

/**
 * Get client IP address accurately and securely.
 * Only trusts proxy headers when TRUSTED_PROXY=true is set in .env.
 */
function get_client_ip(): string
{
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // Only trust proxy-forwarded headers if the application is explicitly configured
    // to run behind a trusted reverse proxy (Cloudflare, AWS ALB, Vercel, etc.)
    $trustedProxy = envv('TRUSTED_PROXY', 'false') === 'true';
    if ($trustedProxy) {
        $proxyHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
        ];

        foreach ($proxyHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ipList = explode(',', (string)$_SERVER[$header]);
                $ip = trim($ipList[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
    }

    return filter_var($remoteAddr, FILTER_VALIDATE_IP) ? $remoteAddr : '127.0.0.1';
}

/**
 * Check rate limit for an action. Emits HTTP 429 if the request limit is exceeded.
 */
function check_rate_limit(string $action, int $maxAttempts = 60, int $decaySeconds = 60): void
{
    $ip = get_client_ip();
    $now = time();
    $db = db();

    // MongoDB-backed rate limit tracking (persists across sessions & prevents cookie tampering)
    if ($db) {
        try {
            $cutoff = new UTCDateTime(($now - $decaySeconds) * 1000);
            $filter = [
                'ip' => $ip,
                'action' => $action,
                'created_at' => ['$gte' => $cutoff]
            ];
            $count = $db->rate_limits->countDocuments($filter);

            if ($count >= $maxAttempts) {
                header('Retry-After: ' . $decaySeconds);
                http_response_code(429);
                json_response([
                    'error' => 'Too many requests. Please slow down and try again later.'
                ], 429);
            }

            $db->rate_limits->insertOne([
                'ip' => $ip,
                'action' => $action,
                'created_at' => new UTCDateTime($now * 1000),
            ]);
            return;
        } catch (\Throwable) {
            // Fall back to session-based rate limiting if DB query fails
        }
    }

    // In-session fallback
    $key = 'rl_' . md5($action . ':' . $ip);
    if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key]) || $now > ($_SESSION[$key]['expires'] ?? 0)) {
        $_SESSION[$key] = ['count' => 1, 'expires' => $now + $decaySeconds];
        return;
    }

    $_SESSION[$key]['count']++;
    if ($_SESSION[$key]['count'] > $maxAttempts) {
        $retryAfter = max(1, (int)$_SESSION[$key]['expires'] - $now);
        header('Retry-After: ' . $retryAfter);
        http_response_code(429);
        json_response([
            'error' => 'Too many requests. Please wait ' . $retryAfter . ' seconds before trying again.'
        ], 429);
    }
}

/**
 * Apply Cross-Origin Resource Sharing (CORS) headers securely with origin validation.
 */
function handle_cors(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
    $appUrl = envv('APP_URL', '');
    $appHost = $appUrl ? parse_url($appUrl, PHP_URL_HOST) : null;
    $serverHost = $_SERVER['HTTP_HOST'] ?? '';

    // Allowed origins: matching current host, matching configured APP_URL, or localhost in non-production
    $isAllowed = false;
    if ($origin !== null && $origin !== '') {
        $originHost = parse_url($origin, PHP_URL_HOST);
        $isLocal = in_array($originHost, ['localhost', '127.0.0.1'], true) && envv('APP_ENV') !== 'production';
        if ($originHost === $serverHost || ($appHost && $originHost === $appHost) || $isLocal) {
            $isAllowed = true;
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
            header('Vary: Origin');
        }
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With');
    header('Access-Control-Max-Age: 86400');

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code($isAllowed || empty($_SERVER['HTTP_ORIGIN']) ? 204 : 403);
        exit;
    }
}

/**
 * Generate or retrieve session CSRF token.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

/**
 * Verify CSRF token from request header, post field, or Authorization Bearer header.
 */
function verify_csrf(): void
{
    // Allow Bearer token authentication bypass for API clients if configured
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (str_starts_with($authHeader, 'Bearer ')) {
        $token = substr($authHeader, 7);
        if (!empty($_SESSION['api_token']) && hash_equals($_SESSION['api_token'], $token)) {
            return;
        }
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
    $sessionToken = $_SESSION['csrf'] ?? '';

    if (!is_string($token) || $token === '' || !is_string($sessionToken) || !hash_equals($sessionToken, $token)) {
        json_response(['error' => 'Invalid or expired CSRF token. Please refresh the page.'], 419);
    }
}

/**
 * Safely parse incoming JSON payload into an associative array.
 */
function json_input(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw || trim($raw) === '') {
        return [];
    }

    try {
        $data = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        return is_array($data) ? $data : [];
    } catch (\JsonException) {
        return [];
    }
}

/**
 * Send JSON response with correct headers, status code, and CORS.
 */
function json_response(array $data, int $status = 200): never
{
    handle_cors();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Safe redirect to a given URL.
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Validate redirect path to prevent Open Redirect vulnerabilities.
 */
function safe_next_url(?string $next, string $default = ''): string
{
    if ($default === '') {
        $default = app_url();
    }

    if ($next === null || trim($next) === '') {
        return $default;
    }

    $next = trim($next);

    // Only allow paths starting with '/' and not '//' (protocol-relative) or scheme injection
    if (str_starts_with($next, '/') && !str_starts_with($next, '//') && !str_starts_with($next, '/\\')) {
        return $next;
    }

    $appUrl = app_url();
    if (str_starts_with($next, $appUrl)) {
        return $next;
    }

    return $default;
}

/**
 * Get the currently authenticated user from session.
 */
function current_user(): ?array
{
    $user = $_SESSION['user'] ?? null;
    if (!is_array($user) || empty($user['id'])) {
        return $user;
    }

    // Keep session user (including avatar) hydrated for existing sessions or after updates
    $lastSync = (int)($_SESSION['_user_synced_at'] ?? 0);
    if ((!array_key_exists('avatar', $user) || (time() - $lastSync) > 60)
        && PHP_SAPI !== 'cli'
        && class_exists(\BlogForge\Auth::class)) {
        static $syncedThisRequest = false;
        if (!$syncedThisRequest) {
            $syncedThisRequest = true;
            $_SESSION['_user_synced_at'] = time();
            try {
                $fresh = \BlogForge\Auth::fetchUser((string)$user['id']);
                if ($fresh) {
                    $user['name'] = (string)($fresh['name'] ?? $user['name'] ?? 'Author');
                    $user['handle'] = (string)($fresh['handle'] ?? $user['handle'] ?? '');
                    $user['avatar'] = (string)($fresh['avatar'] ?? '');
                    $user['role'] = (string)($fresh['role'] ?? $user['role'] ?? 'author');
                    $_SESSION['user'] = $user;
                }
            } catch (\Throwable $e) {
                // Keep existing session user if database is unreachable
            }
        }
    }

    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $u = current_user();
    return is_array($u) && ($u['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $isApi = str_contains($uri, '/api/') || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        if ($isApi) {
            json_response(['error' => 'Authentication required. Please sign in.'], 401);
        }
        redirect(app_url('auth.php?next=' . urlencode($uri)));
    }
}

function require_admin(): void
{
    if (!is_logged_in()) {
        $uri = $_SERVER['REQUEST_URI'] ?? '/admin/';
        $isApi = str_contains($uri, '/api/') || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        if ($isApi) {
            json_response(['error' => 'Authentication required. Please sign in as an administrator.'], 401);
        }
        redirect(app_url('auth.php?next=' . urlencode($uri)));
    }

    if (!is_admin()) {
        http_response_code(403);
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || str_contains($uri, '/api/')) {
            json_response(['error' => 'Forbidden: administrator access required'], 403);
        }
        exit('Forbidden: administrator access required.');
    }
}

/**
 * Safely parse a 24-character hexadecimal string into a MongoDB ObjectId.
 */
function oid(mixed $id): ?ObjectId
{
    if (!is_string($id)) {
        return null;
    }
    $id = trim($id);
    if (!preg_match('/^[a-f0-9]{24}$/i', $id)) {
        return null;
    }
    try {
        return new ObjectId($id);
    } catch (\Throwable) {
        return null;
    }
}

/**
 * Generate a clean URL-friendly slug.
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?? '';
    $slug = trim($text, '-');
    return $slug !== '' ? $slug : 'post-' . time();
}

/**
 * Calculate reading time in minutes.
 */
function reading_time(string $body): int
{
    $clean = strip_tags($body);
    $words = str_word_count($clean);
    return max(1, (int)ceil($words / 220));
}

/**
 * Safely render story body content to HTML.
 * Handles visual WYSIWYG HTML output, mixed content, and traditional Markdown.
 * Auto-heals previously double-escaped tags and strips proprietary data attributes.
 */
function body_to_html(string $body): string
{
    $body = trim($body);
    if ($body === '') {
        return '';
    }

    // 1. Auto-heal previously corrupted/double-escaped HTML entities (e.g. &lt;b ...&gt; or &lt;p&gt;)
    if (preg_match('/&lt;\s*\/?\s*(p|h[1-6]|blockquote|ul|ol|li|div|span|strong|b|em|i|u|s|code|pre|img|a|br|hr)\b/i', $body)) {
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // 2. Comprehensive HTML tag detection (includes b, strong, i, u, headings, lists, etc.)
    $hasHtmlTags = (bool)preg_match('/<\s*\/?\s*(p|h[1-6]|blockquote|ul|ol|li|div|span|strong|b|em|i|u|s|code|pre|img|a|br|hr)\b/i', $body);

    if ($hasHtmlTags) {
        // Strip non-whitelisted tags
        $allowedTags = '<h2><h3><h4><p><blockquote><strong><b><em><i><u><s><code><pre><ul><ol><li><a><img><br><hr><div><span>';
        $cleaned = strip_tags($body, $allowedTags);

        // Sanitize attributes: only allow safe href on <a> and safe src/alt on <img>.
        // Strips all other attributes (e.g. data-path-to-node, data-index-in-node, inline styles, onclick)
        $cleaned = preg_replace_callback('/<([a-z0-9]+)([^>]*)>/i', function ($matches) {
            $tag = strtolower($matches[1]);
            $attrs = $matches[2];

            $safeAttrs = [];

            if ($tag === 'a') {
                if (preg_match('/href="([^"]+)"/i', $attrs, $m) || preg_match("/href='([^']+)'/i", $attrs, $m)) {
                    $url = trim($m[1]);
                    $isSafeUrl = (bool)preg_match('~^https?://~i', $url) ||
                                 (str_starts_with($url, '/') && !str_starts_with($url, '//') && !str_starts_with($url, '/\\'));
                    if ($isSafeUrl) {
                        $safeAttrs[] = 'href="' . e($url) . '" target="_blank" rel="noopener noreferrer" class="text-[#ff6b13] font-medium hover:underline"';
                    }
                }
            } elseif ($tag === 'img') {
                if (preg_match('/src="([^"]+)"/i', $attrs, $m) || preg_match("/src='([^']+)'/i", $attrs, $m)) {
                    $src = trim($m[1]);
                    if (preg_match('~^https?://~i', $src) || preg_match('~^data:image\/(webp|jpeg|png|gif);base64,~i', $src) || str_starts_with($src, '/')) {
                        $safeAttrs[] = 'src="' . e($src) . '"';
                    }
                }
                if (preg_match('/alt="([^"]*)"/i', $attrs, $m) || preg_match("/alt='([^']*)'/i", $attrs, $m)) {
                    $safeAttrs[] = 'alt="' . e($m[1]) . '"';
                }
                $safeAttrs[] = 'class="rounded-2xl my-4 max-w-full shadow-sm"';
                $safeAttrs[] = 'loading="lazy"';
            }

            return '<' . $tag . ($safeAttrs ? ' ' . implode(' ', $safeAttrs) : '') . '>';
        }, $cleaned);

        // Also convert any raw markdown that was typed/pasted inside HTML paragraphs
        $cleaned = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/`([^`]+)`/', '<code>$1</code>', $cleaned) ?? $cleaned;

        return $cleaned;
    }

    // 3. Traditional Markdown processing fallback for plain text stories
    $blocks = preg_split('/\r\n\r\n|\n\n/', $body) ?: [];
    $htmlParts = [];

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }

        // H2 heading
        if (str_starts_with($block, '## ')) {
            $text = substr($block, 3);
            $htmlParts[] = '<h2>' . e($text) . '</h2>';
            continue;
        }

        // H3 heading
        if (str_starts_with($block, '### ')) {
            $text = substr($block, 4);
            $htmlParts[] = '<h3>' . e($text) . '</h3>';
            continue;
        }

        // Blockquote
        if (str_starts_with($block, '> ')) {
            $text = substr($block, 2);
            $htmlParts[] = '<blockquote>' . nl2br(e($text)) . '</blockquote>';
            continue;
        }

        // Escape HTML before applying inline markdown
        $safe = e($block);

        // Code spans: `code`
        $safe = preg_replace('/`([^`]+)`/', '<code>$1</code>', $safe) ?? $safe;

        // Bold: **text**
        $safe = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $safe) ?? $safe;

        // Italic: *text* or _text_
        $safe = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $safe) ?? $safe;
        $safe = preg_replace('/(?<!_)_([^_]+)_(?!_)/', '<em>$1</em>', $safe) ?? $safe;

        // Images: ![alt](https://...) or data URIs
        $safe = preg_replace('~!\[([^\]]*)\]\((https?://[^\s)]+|data:image\/[a-zA-Z0-9\+\/=;]+)\)~i', '<img src="$2" alt="$1" class="rounded-2xl my-4 max-w-full shadow-sm" loading="lazy">', $safe) ?? $safe;

        // Links: [text](https://...)
        $safe = preg_replace('~\[([^\]]+)\]\((https?://[^\s)]+)\)~i', '<a href="$2" target="_blank" rel="noopener noreferrer" class="text-[#ff6b13] font-medium hover:underline">$1</a>', $safe) ?? $safe;

        $htmlParts[] = '<p>' . nl2br($safe) . '</p>';
    }

    return implode("\n", $htmlParts);
}

/**
 * Compute initials from a display name.
 */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $first = mb_substr($parts[0] ?? 'B', 0, 1);
    $second = isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '';
    return strtoupper($first . $second);
}

/**
 * Format date for user display.
 */
function format_date(mixed $date): string
{
    if ($date instanceof UTCDateTime) {
        $date = $date->toDateTime();
    }
    if ($date instanceof DateTimeInterface) {
        return $date->format('M j, Y');
    }
    if (is_numeric($date)) {
        return date('M j, Y', (int)$date);
    }
    if (is_string($date) && strtotime($date) !== false) {
        return date('M j, Y', (int)strtotime($date));
    }
    return date('M j, Y');
}

/**
 * Recursively convert MongoDB BSON types into native PHP arrays, strings, and integers.
 */
function normalize_mongo(mixed $v): mixed
{
    if ($v instanceof ObjectId) {
        return (string)$v;
    }
    if ($v instanceof UTCDateTime) {
        return $v->toDateTime()->getTimestamp();
    }
    if ($v instanceof \MongoDB\Model\BSONDocument || $v instanceof \MongoDB\Model\BSONArray) {
        $v = $v->getArrayCopy();
    } elseif ($v instanceof \stdClass) {
        $v = (array)$v;
    }

    if (is_array($v)) {
        foreach ($v as $k => $x) {
            $v[$k] = normalize_mongo($x);
        }
    }

    return $v;
}

/**
 * Sample stories. Only used by `php scripts/seed.php` if you explicitly want demo content;
 * the public site never falls back to these.
 */
function sample_posts(): array
{
    return [
        [
            '_id' => 'demo-ai',
            'slug' => 'how-ai-is-changing-the-future-of-work',
            'title' => 'How AI Is Changing the Future of Work',
            'excerpt' => 'Exploring the opportunities and challenges of AI in modern workplaces.',
            'body' => "Artificial intelligence is no longer a concept of the future — it is already reshaping how we work, create, and collaborate. From automating repetitive tasks to enhancing decision-making, AI brings both exciting opportunities and complex challenges.\n\nThe most important shift is not simply automation. It is augmentation: people using intelligent tools to move faster, think more broadly, and spend more time on work that requires judgment, empathy, and originality.\n\nFor organizations, the challenge is to redesign roles responsibly, invest in new skills, and keep human accountability at the center of important decisions.",
            'category' => 'Technology',
            'tags' => ['AI', 'Future of Work', 'Technology'],
            'status' => 'published',
            'author' => ['id' => 'demo-author-1', 'name' => 'Sarah Ahmed', 'handle' => 'sarahahmed'],
            'published_at' => time() - 86400 * 2,
            'read_time' => 6,
            'cover' => 'assets/img/covers/Technology.png',
            'likes' => 284,
            'comments' => 12,
        ],
        [
            '_id' => 'demo-travel',
            'slug' => 'hidden-gems-southeast-asia',
            'title' => 'A Guide to the Hidden Gems of Southeast Asia',
            'excerpt' => 'Offbeat destinations that deserve a spot on your travel list.',
            'body' => "Southeast Asia is filled with more than the usual tourist hotspots. Beyond crowded cities and famous beaches are quieter places shaped by nature, culture, and local memory.\n\nTravel slowly, ask better questions, and leave enough room for the unexpected. The best stories are often found between destinations.",
            'category' => 'Travel',
            'tags' => ['Travel', 'Adventure', 'Culture'],
            'status' => 'published',
            'author' => ['id' => 'demo-author-2', 'name' => 'Mehedi Hasan', 'handle' => 'mehedihasan'],
            'published_at' => time() - 86400 * 4,
            'read_time' => 5,
            'cover' => 'assets/img/covers/Travel.png',
            'likes' => 191,
            'comments' => 8,
        ],
        [
            '_id' => 'demo-life',
            'slug' => 'power-of-a-slower-life',
            'title' => 'The Power of a Slower Life',
            'excerpt' => 'Why slowing down can help you become more productive and happier.',
            'body' => "Slowness is not the opposite of ambition. It can be a way to choose attention over noise.\n\nA slower life makes room for deep work, meaningful relationships, and deliberate rest — the things that often disappear when every minute is optimized.",
            'category' => 'Lifestyle',
            'tags' => ['Lifestyle', 'Mindfulness', 'Productivity'],
            'status' => 'published',
            'author' => ['id' => 'demo-author-3', 'name' => 'Nusrat Jahan', 'handle' => 'nusratjahan'],
            'published_at' => time() - 86400 * 8,
            'read_time' => 4,
            'cover' => 'assets/img/covers/Lifestyle.png',
            'likes' => 156,
            'comments' => 6,
        ],
        [
            '_id' => 'demo-min',
            'slug' => 'minimalism-in-a-digital-world',
            'title' => 'Minimalism in a Digital World',
            'excerpt' => 'How to declutter your digital life and focus on what truly matters.',
            'body' => "Digital minimalism starts with intention. Keep the tools that serve a clear purpose, remove the ones that compete for attention, and build small rituals that protect focus.",
            'category' => 'Self Improvement',
            'tags' => ['Minimalism', 'Focus', 'Technology'],
            'status' => 'published',
            'author' => ['id' => 'demo-author-4', 'name' => 'Ayesha Rahman', 'handle' => 'ayesha'],
            'published_at' => time() - 86400,
            'read_time' => 5,
            'cover' => 'assets/img/covers/Self-improvement.png',
            'likes' => 98,
            'comments' => 4,
        ],
        [
            '_id' => 'demo-remote',
            'slug' => 'rise-of-remote-teams',
            'title' => 'The Rise of Remote Teams',
            'excerpt' => 'Why remote work is here to stay and how to make it work.',
            'body' => "Remote teams succeed when communication is explicit, documentation is normal, and trust is measured through outcomes rather than presence.",
            'category' => 'Business',
            'tags' => ['Remote Work', 'Business', 'Management'],
            'status' => 'published',
            'author' => ['id' => 'demo-author-5', 'name' => 'Tanvir Rahman', 'handle' => 'tanvir'],
            'published_at' => time() - 86400 * 3,
            'read_time' => 7,
            'cover' => 'assets/img/covers/Business.png',
            'likes' => 122,
            'comments' => 9,
        ],
        [
            '_id' => 'demo-ethics',
            'slug' => 'can-ai-be-ethical',
            'title' => 'Can AI Be Ethical?',
            'excerpt' => 'A deeper look into the ethics of artificial intelligence.',
            'body' => "Ethical AI is not a single technical feature. It is a system of decisions about data, incentives, accountability, transparency, and who bears the cost when systems fail.",
            'category' => 'Technology',
            'tags' => ['AI', 'Ethics', 'Philosophy'],
            'status' => 'published',
            'author' => ['id' => 'demo-author-6', 'name' => 'Farhan Karim', 'handle' => 'farhan'],
            'published_at' => time() - 86400 * 6,
            'read_time' => 6,
            'cover' => 'assets/img/covers/Technology.png',
            'likes' => 203,
            'comments' => 14,
        ],
    ];
}

/**
 * Enrich post author blocks with live name, handle, and avatar from the users collection.
 * Matches by both author ID (ObjectId) and author handle so avatars update across all posts.
 */
function enrich_post_authors(array $posts, ?\MongoDB\Database $database = null): array
{
    if (empty($posts)) {
        return $posts;
    }

    $database = $database ?: db();
    if (!$database) {
        return $posts;
    }

    $userOids = [];
    $handles = [];
    foreach ($posts as $post) {
        $author = is_array($post['author'] ?? null) ? $post['author'] : [];
        $uid = (string)($author['id'] ?? '');
        if ($uid !== '' && ($uOid = oid($uid))) {
            $userOids[(string)$uOid] = $uOid;
        }
        $h = ltrim(trim((string)($author['handle'] ?? '')), '@');
        if ($h !== '') {
            $handles[strtolower($h)] = $h;
        }
    }

    if (empty($userOids) && empty($handles)) {
        return $posts;
    }

    try {
        $or = [];
        if (!empty($userOids)) {
            $or[] = ['_id' => ['$in' => array_values($userOids)]];
        }
        if (!empty($handles)) {
            $or[] = ['handle' => ['$in' => array_values($handles)]];
        }

        $filter = count($or) === 1 ? $or[0] : ['$or' => $or];
        $uCursor = $database->users->find(
            $filter,
            ['projection' => ['name' => 1, 'handle' => 1, 'avatar' => 1]]
        );
        $usersById = [];
        $usersByHandle = [];
        foreach ($uCursor as $uDoc) {
            $u = normalize_mongo($uDoc);
            $uId = (string)($u['_id'] ?? '');
            if ($uId !== '') {
                $usersById[$uId] = $u;
            }
            $uHandle = strtolower(ltrim((string)($u['handle'] ?? ''), '@'));
            if ($uHandle !== '') {
                $usersByHandle[$uHandle] = $u;
            }
        }

        foreach ($posts as &$post) {
            if (!isset($post['author']) || !is_array($post['author'])) {
                continue;
            }
            $uid = (string)($post['author']['id'] ?? '');
            $uHandle = strtolower(ltrim((string)($post['author']['handle'] ?? ''), '@'));

            $liveUser = $usersById[$uid] ?? $usersByHandle[$uHandle] ?? null;
            if ($liveUser) {
                $post['author']['name'] = (string)($liveUser['name'] ?? $post['author']['name'] ?? 'Author');
                $post['author']['handle'] = (string)($liveUser['handle'] ?? $post['author']['handle'] ?? '');
                $post['author']['avatar'] = (string)($liveUser['avatar'] ?? $post['author']['avatar'] ?? '');
            }
        }
        unset($post);
    } catch (\Throwable $e) {
        error_log('[BlogForge] enrich_post_authors error: ' . $e->getMessage());
    }

    return $posts;
}

/**
 * Fetch public published posts with optional category filter, search keyword, and limit.
 */
function public_posts(int $limit = 20, ?string $category = null, ?string $search = null): array
{
    $limit = max(1, min(100, $limit));
    $database = db();

    if (!$database) {
        return [];
    }

    $filter = ['status' => 'published'];

    if ($category !== null && trim($category) !== '') {
        $filter['category'] = mb_substr(trim($category), 0, 50);
    }

    if ($search !== null && trim($search) !== '') {
        $cleanSearch = mb_substr(trim($search), 0, 100);
        // Use MongoDB $text search index (created in scripts/create_indexes.php)
        // instead of unanchored regex queries to prevent ReDoS and full collection scans
        $filter['$text'] = ['$search' => $cleanSearch];
    }

    try {
        $cursor = $database->posts->find($filter, [
            'sort' => ['published_at' => -1],
            'limit' => $limit,
        ]);
        $posts = array_map('normalize_mongo', iterator_to_array($cursor, false));
        return enrich_post_authors($posts, $database);
    } catch (\Throwable $e) {
        error_log('[BlogForge] public_posts query error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Find a post by slug or ObjectId.
 * Honors status permissions: drafts are only accessible by their author or an administrator.
 */
function find_post(string $key, bool $allowDrafts = false): ?array
{
    $key = trim($key);
    if ($key === '') {
        return null;
    }

    $database = db();
    if (!$database) {
        return null;
    }

    $orConditions = [['slug' => $key]];
    if ($o = oid($key)) {
        $orConditions[] = ['_id' => $o];
    } else {
        $orConditions[] = ['_id' => $key];
    }

    try {
        $post = $database->posts->findOne(['$or' => $orConditions]);
        if (!$post) {
            return null;
        }

        $normalized = normalize_mongo($post);

        // Security check: If post is draft, only author or admin may view
        if (($normalized['status'] ?? 'published') === 'draft' && !$allowDrafts) {
            $user = current_user();
            $authorId = (string)($normalized['author']['id'] ?? '');
            if (!$user || (!is_admin() && (string)$user['id'] !== $authorId)) {
                return null;
            }
        }

        $enriched = enrich_post_authors([$normalized], $database);
        return $enriched[0] ?? $normalized;
    } catch (\Throwable $e) {
        error_log('[BlogForge] find_post error: ' . $e->getMessage());
        return null;
    }
}
