<?php
declare(strict_types=1);

namespace BlogForge;

use MongoDB\Client;
use MongoDB\Database as MongoDatabase;
use Throwable;

final class Database
{
    private static ?MongoDatabase $database = null;
    private static bool $attempted = false;
    private static ?string $lastError = null;

    /**
     * Get the active MongoDB database instance, or null if connection fails.
     */
    public static function connection(): ?MongoDatabase
    {
        if (self::$attempted) {
            return self::$database;
        }

        self::$attempted = true;
        self::$lastError = null;

        try {
            $rawUri = self::resolveEnv('MONGODB_URI', '');
            $isDefault = false;

            if ($rawUri === '') {
                $rawUri = 'mongodb://127.0.0.1:27017';
                $isDefault = true;
            }

            $uri = $rawUri;
            $name = self::resolveEnv('MONGODB_DATABASE', 'blogforge');

            if (str_contains($uri, '<db_password>')) {
                throw new \RuntimeException('MONGODB_URI contains unreplaced placeholder <db_password>. Update environment variables with your actual Atlas password.');
            }

            $timeout = (int)self::resolveEnv('MONGODB_TIMEOUT', '10000');
            if ($timeout < 1000) {
                $timeout = 10000;
            }

            // UriOptions must be passed as the second argument, not driverOptions!
            $uriOptions = [
                'serverSelectionTimeoutMS' => $timeout,
                'connectTimeoutMS' => $timeout,
            ];

            // Driver-specific options in 3rd argument
            $driverOptions = [];

            $client = new Client($uri, $uriOptions, $driverOptions);
            
            // Validate connection with a lightweight ping command
            $client->selectDatabase($name)->command(['ping' => 1]);

            self::$database = $client->selectDatabase($name);
        } catch (Throwable $e) {
            self::$database = null;
            $errMsg = $e->getMessage();
            if ($isDefault) {
                $errMsg = 'MONGODB_URI environment variable is missing in Render dashboard (defaulted to 127.0.0.1). Details: ' . $errMsg;
            } elseif (str_contains($errMsg, 'serverSelectionTimeoutMS')) {
                $errMsg = 'Connection timed out (' . $timeout . 'ms). Check if MongoDB Atlas Network Access has 0.0.0.0/0 allowed. Details: ' . $errMsg;
            }
            self::$lastError = $errMsg;
            error_log('[BlogForge] MongoDB Connection Error: ' . $errMsg);
        }

        return self::$database;
    }

    /**
     * Get the last error encountered during connection attempt, if any.
     */
    public static function getLastError(): ?string
    {
        return self::$lastError;
    }

    /**
     * Reset connection state to allow retry attempts.
     */
    public static function reset(): void
    {
        self::$database = null;
        self::$attempted = false;
        self::$lastError = null;
    }

    /**
     * Safely resolve environment variables across various PHP environments.
     */
    private static function resolveEnv(string $key, string $default): string
    {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($val !== false && $val !== null && trim((string)$val) !== '') {
            return trim((string)$val);
        }
        return $default;
    }
}
