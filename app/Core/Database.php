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
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '5432';
        $name = getenv('DB_DATABASE') ?: 'toolkitpro';
        try {
            $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$name}", getenv('DB_USERNAME') ?: 'postgres', getenv('DB_PASSWORD') ?: '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed. Check environment configuration.', 0, $e);
        }
    }
}