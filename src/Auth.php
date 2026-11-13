<?php
declare(strict_types=1);

namespace BlogForge;

use MongoDB\BSON\UTCDateTime;

final class Auth
{
    private const NAME_PATTERN = '/^[\p{L}\p{M}][\p{L}\p{M}\s\'’.\-]*$/u';

    /**
     * Authenticate user with email and password.
     */
    public const HANDLE_PATTERN = '/^[a-z0-9][a-z0-9_-]{2,29}$/';
    public const RESERVED_HANDLES = [
        'admin', 'administrator', 'root', 'system', 'blogforge',
        'api', 'auth', 'login', 'register', 'logout',
        'profile', 'drafts', 'write', 'explore', 'about',
        'contact', 'faq', 'terms', 'privacy', 'cookies', 'help',
        'null', 'undefined'
    ];

    public static function cleanHandle(string $handle): string
    {
        return ltrim(strtolower(trim($handle)), '@');
    }

    public static function assertHandle(string $handle): void
    {
        if ($handle === '') {
            throw new \RuntimeException('Please choose a username.');
        }
        if (!preg_match(self::HANDLE_PATTERN, $handle)) {
            throw new \RuntimeException('Username must be 3–30 characters: letters, numbers, dashes or underscores.');
        }
        if (in_array($handle, self::RESERVED_HANDLES, true)) {
            throw new \RuntimeException('This username is reserved. Please choose another.');
        }
    }

    public static function isHandleAvailable(string $handle, ?string $excludeUserId = null): bool
    {
        $handle = self::cleanHandle($handle);
        try {
            self::assertHandle($handle);
        } catch (\RuntimeException) {
            return false;
        }

        $db = Database::connection();
        if (!$db) {
            return false;
        }

        $filter = [
            '$or' => [
                ['handle' => $handle],
                ['handle' => new \MongoDB\BSON\Regex('^' . preg_quote($handle, '/') . '$', 'i')],
            ]
        ];

        if ($excludeUserId !== null && ($oid = oid($excludeUserId))) {
            $filter = ['$and' => [$filter, ['_id' => ['$ne' => $oid]]]];
        }

        return $db->users->findOne($filter) === null;
    }

    public static function loginEmail(string $identifier, string $password): array
    {
        $db = self::requireDb();

        $identifier = trim($identifier);
        if ($identifier === '') {
            throw new \RuntimeException('Please provide your email address or username.');
        }

        if ($password === '') {
            throw new \RuntimeException('Password cannot be empty.');
        }

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = $db->users->findOne(['email' => strtolower($identifier)]);
        } else {
            $cleanHandle = self::cleanHandle($identifier);
            $user = $db->users->findOne([
                '$or' => [
                    ['handle' => $cleanHandle],
                    ['handle' => new \MongoDB\BSON\Regex('^' . preg_quote($cleanHandle, '/') . '$', 'i')],
                ]
            ]);
        }

        // Account lockout check: block login if too many consecutive failures
        if ($user) {
            $failedAttempts = (int)($user['failed_attempts'] ?? 0);
            $lockedUntil = $user['locked_until'] ?? null;

            if ($lockedUntil instanceof \MongoDB\BSON\UTCDateTime) {
                $lockTime = $lockedUntil->toDateTime()->getTimestamp();
                if (time() < $lockTime) {
                    $minutesLeft = (int)ceil(($lockTime - time()) / 60);
                    throw new \RuntimeException('Account temporarily locked due to too many failed login attempts. Please try again in ' . $minutesLeft . ' minute(s).');
                }
                // Lock period expired — reset counters
                $db->users->updateOne(['_id' => $user['_id']], [
                    '$set' => ['failed_attempts' => 0],
                    '$unset' => ['locked_until' => ''],
                ]);
                $failedAttempts = 0;
            }
        }

        if (!$user || empty($user['password_hash']) || !password_verify($password, (string)$user['password_hash'])) {
            // Increment failed_attempts on the user document (if the email exists)
            if ($user) {
                $newCount = ((int)($user['failed_attempts'] ?? 0)) + 1;
                $update = ['$set' => ['failed_attempts' => $newCount]];
                if ($newCount >= 5) {
                    // Lock account for 15 minutes after 5 consecutive failures
                    $update['$set']['locked_until'] = new UTCDateTime((time() + 900) * 1000);
                }
                $db->users->updateOne(['_id' => $user['_id']], $update);
            }
            throw new \RuntimeException('Invalid email address or password.');
        }

        // Successful login — reset failed attempts counter
        if (($user['failed_attempts'] ?? 0) > 0 || isset($user['locked_until'])) {
            $db->users->updateOne(['_id' => $user['_id']], [
                '$set' => ['failed_attempts' => 0],
                '$unset' => ['locked_until' => ''],
            ]);
        }

        // Automatically upgrade password hash if algorithm requirements change
        if (password_needs_rehash((string)$user['password_hash'], PASSWORD_DEFAULT)) {
            $db->users->updateOne(
                ['_id' => $user['_id']],
                ['$set' => ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]]
            );
        }

        return self::startSession($user);
    }

    /**
     * Register a new author account.
     */
    public static function registerEmail(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        string $confirmPassword,
        bool $acceptedTerms,
        string $handle = ''
    ): array {
        $db = self::requireDb();

        $firstName = self::cleanName($firstName);
        $lastName = self::cleanName($lastName);
        $email = strtolower(trim($email));
        $handle = self::cleanHandle($handle);

        self::assertName($firstName, 'First name');
        self::assertName($lastName, 'Last name');

        if ($handle === '') {
            throw new \RuntimeException('Please choose a username.');
        }
        self::assertHandle($handle);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Please provide a valid email address.');
        }

        self::assertPassword($password);

        if (!hash_equals($password, $confirmPassword)) {
            throw new \RuntimeException('Passwords do not match.');
        }

        if (!$acceptedTerms) {
            throw new \RuntimeException('You must agree to the Terms & Privacy Policy to create an account.');
        }

        if ($db->users->findOne(['email' => $email])) {
            throw new \RuntimeException('An account with this email address already exists.');
        }

        if (!self::isHandleAvailable($handle)) {
            throw new \RuntimeException('That username is already taken. Please choose another.');
        }

        $now = new UTCDateTime();
        $doc = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => trim($firstName . ' ' . $lastName),
            'email' => $email,
            'handle' => $handle,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'author',
            'bio' => '',
            'avatar' => '',
            'terms_accepted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        try {
            $result = $db->users->insertOne($doc);
            $doc['_id'] = $result->getInsertedId();
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '11000') || str_contains($msg, 'duplicate key')) {
                if (str_contains($msg, 'email')) {
                    throw new \RuntimeException('An account with this email address already exists.');
                }
                throw new \RuntimeException('That username is already taken. Please choose another.');
            }
            throw $e;
        }

        return self::startSession($doc);
    }

    /**
     * Update the signed-in author's public profile.
     * Also refreshes the denormalised author name/handle on their stories and responses.
     */
    public static function updateProfile(string $userId, array $input): array
    {
        $db = self::requireDb();
        $oid = oid($userId);
        if (!$oid) {
            throw new \RuntimeException('Invalid account.');
        }

        $user = $db->users->findOne(['_id' => $oid]);
        if (!$user) {
            throw new \RuntimeException('Account not found.');
        }

        $firstName = self::cleanName((string)($input['first_name'] ?? ''));
        $lastName = self::cleanName((string)($input['last_name'] ?? ''));
        self::assertName($firstName, 'First name');
        self::assertName($lastName, 'Last name');

        $handle = self::cleanHandle((string)($input['handle'] ?? ''));
        self::assertHandle($handle);
        if ($handle !== (string)($user['handle'] ?? '') && !self::isHandleAvailable($handle, (string)$oid)) {
            throw new \RuntimeException('That username is already taken. Please choose another.');
        }

        $bio = trim((string)($input['bio'] ?? ''));
        $bio = preg_replace('/\s+/u', ' ', $bio) ?? $bio;
        if (mb_strlen($bio) > 280) {
            throw new \RuntimeException('Bio must be 280 characters or fewer.');
        }

        $avatar = trim((string)($input['avatar'] ?? ''));
        if ($avatar !== '' && (!preg_match('~^https?://~i', $avatar) || !filter_var($avatar, FILTER_VALIDATE_URL) || mb_strlen($avatar) > 1000)) {
            throw new \RuntimeException('Profile photo must be a valid http(s) image URL.');
        }

        $name = trim($firstName . ' ' . $lastName);

        $db->users->updateOne(['_id' => $oid], ['$set' => [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $name,
            'handle' => $handle,
            'bio' => $bio,
            'avatar' => $avatar,
            'updated_at' => new UTCDateTime(),
        ]]);

        // Keep denormalised author info on stories & comments in sync
        $authorSet = ['author.name' => $name, 'author.handle' => $handle, 'author.avatar' => $avatar];
        $db->posts->updateMany(['author.id' => $userId], ['$set' => $authorSet]);
        $db->comments->updateMany(['author.id' => $userId], ['$set' => $authorSet]);

        $fresh = $db->users->findOne(['_id' => $oid]);
        $session = self::sessionUser($fresh);
        $_SESSION['user'] = $session;
        $_SESSION['_user_synced_at'] = time();

        return $session;
    }

    /**
     * Change the signed-in user's password.
     */
    public static function changePassword(string $userId, string $current, string $new, string $confirm): void
    {
        $db = self::requireDb();
        $oid = oid($userId);
        $user = $oid ? $db->users->findOne(['_id' => $oid]) : null;
        if (!$user) {
            throw new \RuntimeException('Account not found.');
        }

        if (empty($user['password_hash']) || !password_verify($current, (string)$user['password_hash'])) {
            throw new \RuntimeException('Your current password is incorrect.');
        }

        self::assertPassword($new);

        if (!hash_equals($new, $confirm)) {
            throw new \RuntimeException('New passwords do not match.');
        }

        if (hash_equals($current, $new)) {
            throw new \RuntimeException('Choose a new password that is different from the current one.');
        }

        $db->users->updateOne(['_id' => $oid], ['$set' => [
            'password_hash' => password_hash($new, PASSWORD_DEFAULT),
            'updated_at' => new UTCDateTime(),
        ]]);
    }

    /**
     * Load a full user record (including bio and name parts) as a plain array.
     */
    public static function fetchUser(string $userId): ?array
    {
        $db = Database::connection();
        $oid = oid($userId);
        if (!$db || !$oid) {
            return null;
        }

        $user = $db->users->findOne(['_id' => $oid]);
        if (!$user) {
            return null;
        }

        $u = normalize_mongo($user);
        unset($u['password_hash']);

        // Older accounts only stored a single "name"; split it for the edit form
        if (empty($u['first_name'])) {
            $parts = preg_split('/\s+/', trim((string)($u['name'] ?? '')), 2) ?: [];
            $u['first_name'] = $parts[0] ?? '';
            $u['last_name'] = $parts[1] ?? '';
        }

        return $u;
    }

    /**
     * Load a public-safe user or author/commenter profile by ID or handle.
     * Never exposes private fields such as email or password hashes.
     */
    public static function findPublicUser(string $userId = '', string $handle = ''): ?array
    {
        $userId = trim($userId);
        $handle = ltrim(trim($handle), '@');
        if ($userId === '' && $handle === '') {
            return null;
        }

        $db = Database::connection();
        if ($db) {
            $safeHandleRegex = ($handle !== '' && preg_match('/^[a-zA-Z0-9_.\-]{1,50}$/', $handle))
                ? new \MongoDB\BSON\Regex('^' . preg_quote($handle, '/') . '$', 'i')
                : null;

            $userOr = [];
            if ($oid = oid($userId)) {
                $userOr[] = ['_id' => $oid];
            }
            if ($safeHandleRegex !== null) {
                $userOr[] = ['handle' => $safeHandleRegex];
            }

            if (!empty($userOr)) {
                $user = $db->users->findOne(count($userOr) === 1 ? $userOr[0] : ['$or' => $userOr]);
                if ($user) {
                    $u = normalize_mongo($user);
                    return [
                        'id' => (string)($u['_id'] ?? $u['id'] ?? ''),
                        'name' => (string)($u['name'] ?? 'Author'),
                        'handle' => (string)($u['handle'] ?? ''),
                        'bio' => (string)($u['bio'] ?? ''),
                        'avatar' => (string)($u['avatar'] ?? ''),
                        'role' => (string)($u['role'] ?? 'author'),
                        'created_at' => $u['created_at'] ?? null,
                    ];
                }
            }

            // Fallback for demo/seeded authors or commenters without a users collection record
            $authorOr = [];
            if ($userId !== '') {
                $authorOr[] = ['author.id' => $userId];
            }
            if ($safeHandleRegex !== null) {
                $authorOr[] = ['author.handle' => $safeHandleRegex];
            }

            if (!empty($authorOr)) {
                $authorMatch = count($authorOr) === 1 ? $authorOr[0] : ['$or' => $authorOr];
                $postMatch = $db->posts->findOne(
                    ['status' => 'published'] + $authorMatch,
                    ['sort' => ['published_at' => -1]]
                );
                if ($postMatch) {
                    $p = normalize_mongo($postMatch);
                    $a = $p['author'] ?? [];
                    return [
                        'id' => (string)($a['id'] ?? $userId),
                        'name' => (string)($a['name'] ?? 'Author'),
                        'handle' => (string)($a['handle'] ?? $handle),
                        'bio' => '',
                        'avatar' => (string)($a['avatar'] ?? ''),
                        'role' => 'author',
                        'created_at' => null,
                    ];
                }

                $commentMatch = $db->comments->findOne(
                    ['status' => 'approved'] + $authorMatch,
                    ['sort' => ['created_at' => -1]]
                );
                if ($commentMatch) {
                    $c = normalize_mongo($commentMatch);
                    $a = $c['author'] ?? [];
                    return [
                        'id' => (string)($a['id'] ?? $userId),
                        'name' => (string)($a['name'] ?? 'Reader'),
                        'handle' => (string)($a['handle'] ?? $handle),
                        'bio' => '',
                        'avatar' => (string)($a['avatar'] ?? ''),
                        'role' => 'author',
                        'created_at' => null,
                    ];
                }
            }

            return null;
        }

        // Offline demo fallback from sample posts
        foreach (sample_posts() as $sp) {
            $a = $sp['author'] ?? [];
            $idMatch = ($userId !== '' && (string)($a['id'] ?? '') === $userId);
            $handleMatch = ($handle !== '' && strcasecmp((string)($a['handle'] ?? ''), $handle) === 0);
            if ($idMatch || $handleMatch) {
                return [
                    'id' => (string)($a['id'] ?? ''),
                    'name' => (string)($a['name'] ?? 'Author'),
                    'handle' => (string)($a['handle'] ?? ''),
                    'bio' => '',
                    'avatar' => '',
                    'role' => 'author',
                    'created_at' => null,
                ];
            }
        }

        return null;
    }

    /**
     * Map database user record to safe session payload.
     */
    public static function sessionUser(mixed $user): array
    {
        $id = '';
        if (isset($user['_id'])) {
            $id = (string)$user['_id'];
        }

        return [
            'id' => $id,
            'name' => (string)($user['name'] ?? 'Author'),
            'email' => (string)($user['email'] ?? ''),
            'handle' => (string)($user['handle'] ?? ''),
            'role' => (string)($user['role'] ?? 'author'),
            'avatar' => (string)($user['avatar'] ?? ''),
        ];
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    private static function requireDb(): \MongoDB\Database
    {
        $db = Database::connection();
        if (!$db) {
            $lastErr = Database::getLastError();
            $hint = $lastErr ? ' (' . $lastErr . ')' : ' Please try again shortly.';
            throw new \RuntimeException('Database is currently offline.' . $hint);
        }
        return $db;
    }

    private static function startSession(mixed $user): array
    {
        // Regenerate session to prevent session fixation
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $session = self::sessionUser($user);
        $_SESSION['user'] = $session;
        $_SESSION['_user_synced_at'] = time();
        $_SESSION['api_token'] = bin2hex(random_bytes(32));
        $session['token'] = $_SESSION['api_token'];

        return $session;
    }

    private static function cleanName(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    private static function assertName(string $value, string $label): void
    {
        $len = mb_strlen($value);
        if ($len < 1) {
            throw new \RuntimeException($label . ' is required.');
        }
        if ($len > 40) {
            throw new \RuntimeException($label . ' must be 40 characters or fewer.');
        }
        if (!preg_match(self::NAME_PATTERN, $value)) {
            throw new \RuntimeException($label . ' contains characters that are not allowed.');
        }
    }

    private static function assertPassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new \RuntimeException('Password must be at least 8 characters long.');
        }
        if (strlen($password) > 200) {
            throw new \RuntimeException('Password is too long.');
        }
        if (!preg_match('/[A-Za-z]/', $password)) {
            throw new \RuntimeException('Password must include at least one letter.');
        }
        if (!preg_match('/[0-9]/', $password)) {
            throw new \RuntimeException('Password must include at least one number.');
        }
    }

    private static function uniqueHandle(\MongoDB\Database $db, string $base): string
    {
        $base = substr($base, 0, 24) ?: 'author';
        $handle = $base;
        $counter = 1;
        while ($db->users->findOne(['handle' => $handle])) {
            $handle = $base . '-' . (++$counter);
        }
        return $handle;
    }
}
