<?php
declare(strict_types=1);

/**
 * BlogForge Database Configuration & Connection Provider
 * Supports both MySQL (PDO) and MongoDB Atlas depending on environment configuration.
 */

require_once dirname(__DIR__) . '/bootstrap.php';

function pdo_db(): ?\PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = envv('DB_HOST', '127.0.0.1');
    $port = envv('DB_PORT', '3306');
    $name = envv('DB_NAME', 'blogforge');
    $user = envv('DB_USER', 'root');
    $pass = envv('DB_PASS', '');
    $charset = envv('DB_CHARSET', 'utf8mb4');

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
    $options = [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new \PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (\PDOException $e) {
        error_log('[BlogForge] PDO Connection Failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Default database connector - returns active MongoDB database instance or PDO fallback
 */
function get_db_connection()
{
    $mongo = db();
    if ($mongo !== null) {
        return $mongo;
    }
    return pdo_db();
}
