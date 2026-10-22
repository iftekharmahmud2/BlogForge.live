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
    public static function loginEmail(string $email, string $password): array
    {
        $db = self::requireDb();

        $email = strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Please provide a valid email address.');
        }

        if ($password === '') {
            throw new \RuntimeException('Password cannot be empty.');
        }

        $user = $db->users->findOne(['email' => $email]);
        if (!$user || empty($user['password_hash']) || !password_verify($password, (string)$user['password_hash'])) {
            throw new \RuntimeException('Invalid email address or password.');
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
        bool $acceptedTerms
    ): array {
        $db = self::requireDb();

        $firstName = self::cleanName($firstName);
        $lastName = self::cleanName($lastName);
        $email = strtolower(trim($email));

        self::assertName($firstName, 'First name');
        self::assertName($lastName, 'Last name');

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

        $handle = self::uniqueHandle($db, slugify(strstr($email, '@', true) ?: ($firstName . $lastName)));

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

        $result = $db->users->insertOne($doc);
        $doc['_id'] = $result->getInsertedId();

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

        $handle = strtolower(trim((string)($input['handle'] ?? '')));
        $handle = ltrim($handle, '@');
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{2,29}$/', $handle)) {
            throw new \RuntimeException('Username must be 3–30 characters: letters, numbers, dashes or underscores.');
        }
        if ($handle !== (string)($user['handle'] ?? '')
            && $db->users->findOne(['handle' => $handle, '_id' => ['$ne' => $oid]])) {
            throw new \RuntimeException('That username is already taken.');
        }

        $bio = trim((string)($input['bio'] ?? ''));
        $bio = preg_replace('/\s+/u', ' ', $bio) ?? $bio;
        if (mb_strlen($bio) > 280) {
            throw new \RuntimeException('Bio must be 280 characters or fewer.');
        }

        $avatar = trim((string)($input['avatar'] ?? ''));
        if ($avatar !== '' && (!preg_match('~^https?://~i', $avatar) || !filter_var($avatar, FILTER_VALIDATE_URL) || mb_strlen($avatar) > 500)) {
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
        $authorSet = ['author.name' => $name, 'author.handle' => $handle];
        $db->posts->updateMany(['author.id' => $userId], ['$set' => $authorSet]);
        $db->comments->updateMany(['author.id' => $userId], ['$set' => $authorSet + ['author.avatar' => $avatar]]);

        $fresh = $db->users->findOne(['_id' => $oid]);
        $session = self::sessionUser($fresh);
        $_SESSION['user'] = $session;

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
            throw new \RuntimeException('Database is currently offline. Please try again shortly.');
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
