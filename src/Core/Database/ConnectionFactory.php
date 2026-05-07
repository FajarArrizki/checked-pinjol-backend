<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use RuntimeException;

final class ConnectionFactory
{
    public function make(array $config): PDO
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $dbname = $config['database'] ?? '';
        $user = $config['username'] ?? 'root';
        $pass = $config['password'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        // Membuat DSN (Database Source Name)
        $dsn = "{$driver}:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            return new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new RuntimeException("Koneksi Database Gagal: " . $e->getMessage());
        }
    }
}