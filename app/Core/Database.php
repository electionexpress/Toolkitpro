<?php
declare(strict_types=1);
namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    public static function connection(): PDO
    {
        static $pdo = null;
        if ($pdo instanceof PDO) return $pdo;

        $databaseUrl = getenv('DATABASE_URL');
        if (is_string($databaseUrl) && $databaseUrl !== '') {
            $parts = parse_url($databaseUrl);
            if (!is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
                throw new RuntimeException('DATABASE_URL is malformed.');
            }
            $host = $parts['host'];
            $port = $parts['port'] ?? 5432;
            $name = ltrim($parts['path'], '/');
            $username = rawurldecode($parts['user'] ?? '');
            $password = rawurldecode($parts['pass'] ?? '');
            $sslMode = 'require';
        } else {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '5432';
            $name = getenv('DB_DATABASE') ?: 'toolkitpro';
            $username = getenv('DB_USERNAME') ?: 'postgres';
            $password = getenv('DB_PASSWORD') ?: '';
            $sslMode = getenv('DB_SSLMODE') ?: 'prefer';
        }

        try {
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s;sslmode=%s', $host, $port, $name, $sslMode);
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed. Check PostgreSQL service and environment configuration.', 0, $e);
        }
    }
}
