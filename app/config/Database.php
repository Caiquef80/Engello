<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $envPath = dirname(__DIR__, 2) . '/.env';

        if (!file_exists($envPath)) {
            throw new PDOException('.env não encontrado.');
        }

        $env = parse_ini_file($envPath);

        if ($env === false) {
            throw new PDOException('Não foi possível carregar o .env.');
        }

        $host = $env['DB_HOST'];
        $port = $env['DB_PORT'] ?? '5432';
        $database = $env['DB_NAME'];
        $username = $env['DB_USER'];
        $password = $env['DB_PASSWORD'];

        $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode=require";

        self::$connection = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        return self::$connection;
    }
}