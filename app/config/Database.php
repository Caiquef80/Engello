<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::$connection = self::createConnection();
        }

        return self::$connection;
    }

    private static function createConnection(): PDO
    {
        $connectionString = getenv('DATABASE_URL');

        if (!$connectionString) {
            throw new PDOException('A variável DATABASE_URL não foi configurada.');
        }

        $parts = parse_url($connectionString);

        if ($parts === false) {
            throw new PDOException('DATABASE_URL inválida.');
        }

        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? 5432;
        $database = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
        $username = $parts['user'] ?? '';
        $password = $parts['pass'] ?? '';

        if ($host === '' || $database === '' || $username === '') {
            throw new PDOException('DATABASE_URL não contém os dados necessários.');
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$database}";

        return new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }
}
