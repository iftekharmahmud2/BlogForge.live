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
    $base = rtrim(envv('APP_URL', 'http://localhost:8000') ?? 'http://localhost:8000', '/');
    $path = ltrim($path, '/');
    return $path !== '' ? $base . '/' . $path : $base;
}

/**
 * Resolve a cover image URL: retains external HTTP/HTTPS URLs safely,
 * prefixes internal assets with app_url, and provides a default fallback.
 */
function cover_url(?string $cover): string
{
    $cover = trim((string)$cover);
    if ($cover === '') {
        return app_url('assets/img/covers/ai.svg');
    }

    // If already absolute HTTP/HTTPS URL, sanitize and return directly
    if (preg_match('~^https?://~i', $cover)) {
        return filter_var($cover, FILTER_VALIDATE_URL) ? $cover : app_url('assets/img/covers/ai.svg');
    }

    // Relative asset path
    return app_url($cover);
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
 * Apply Cross-Origin Resource Sharing (CORS) headers and handle preflight OPTIONS.
 */
function handle_cors(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
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
    return $_SESSION['user'] ?? null;
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
        $isApi = str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/') ||
                 str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        if ($isApi) {
            json_response(['error' => 'Authentication required. Please sign in.'], 401);
        }
        $next = $_SERVER['REQUEST_URI'] ?? '/';
        redirect(app_url('auth.php?next=' . urlencode($next)));
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        http_response_code(403);
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
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
 * Escapes raw HTML first to prevent XSS, then parses safe markdown formatting
 * (headings, blockquotes, bold, italics, code, paragraphs).
 */
function body_to_html(string $body): string
{
    $body = trim($body);
    if ($body === '') {
        return '';
    }

    // Split paragraphs by 2 or more line breaks
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

        // Italic: _text_
        $safe = preg_replace('/_([^_]+)_/', '<em>$1</em>', $safe) ?? $safe;

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
            'cover' => 'assets/img/covers/ai.svg',
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
            'cover' => 'assets/img/covers/travel.svg',
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
            'cover' => 'assets/img/covers/life.svg',
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
            'cover' => 'assets/img/covers/minimal.svg',
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
            'cover' => 'assets/img/covers/remote.svg',
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
            'cover' => 'assets/img/covers/ethics.svg',
            'likes' => 203,
            'comments' => 14,
        ],
    ];
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
        $filter['category'] = trim($category);
    }

    if ($search !== null && trim($search) !== '') {
        $safeSearch = preg_quote(trim($search), '/');
        $filter['$or'] = [
            ['title' => new Regex($safeSearch, 'i')],
            ['excerpt' => new Regex($safeSearch, 'i')],
            ['tags' => new Regex($safeSearch, 'i')],
        ];
    }

    try {
        $cursor = $database->posts->find($filter, [
            'sort' => ['published_at' => -1],
            'limit' => $limit,
        ]);
        return array_map('normalize_mongo', iterator_to_array($cursor, false));
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

        return $normalized;
    } catch (\Throwable $e) {
        error_log('[BlogForge] find_post error: ' . $e->getMessage());
        return null;
    }
}
