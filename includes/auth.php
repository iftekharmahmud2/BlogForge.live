<?php
declare(strict_types=1);

/**
 * BlogForge Authentication Helper Wrapper
 * Connects standard auth helpers with BlogForge\Auth and session management.
 */

require_once dirname(__DIR__) . '/bootstrap.php';

use BlogForge\Auth;

function auth_current_user(): ?array
{
    return current_user();
}

function auth_is_logged_in(): bool
{
    return is_logged_in();
}

function auth_is_admin(): bool
{
    return is_admin();
}

function auth_require_login(): void
{
    require_login();
}

function auth_require_admin(): void
{
    require_admin();
}

function auth_login(string $identifier, string $password): array
{
    return Auth::loginEmail($identifier, $password);
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
