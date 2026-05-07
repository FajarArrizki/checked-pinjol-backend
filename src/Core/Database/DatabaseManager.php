<?php

declare(strict_types=1);

namespace App\Core\Database;

use App\Core\Config\ConfigRepository;
use PDO;
use PDOStatement;

final class DatabaseManager
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly ConfigRepository $config,
        private readonly ConnectionFactory $factory,
    ) {
    }

    /**
     * Mendapatkan atau membuat koneksi PDO
     */
    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $default = (string) $this->config->get('database.default', 'mysql');
        $config = (array) $this->config->get("database.connections.{$default}", []);

        $this->connection = $this->factory->make($config);

        return $this->connection;
    }

    /**
     * Helper untuk eksekusi query
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Helper Insert yang memudahkan (dipakai di AuthController tadi)
     */
    public function insert(string $table, array $data): int|string
    {
        $columns = implode(', ', array_map(fn($c) => "`$c`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $this->query(
            "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})", 
            array_values($data)
        );
        
        return $this->connection()->lastInsertId();
    }

    /**
     * Helper Update
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($data)));
        $stmt = $this->query(
            "UPDATE `{$table}` SET {$set} WHERE {$where}",
            array_merge(array_values($data), $whereParams)
        );
        return $stmt->rowCount();
    }
    public function count(string $table, string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Menghapus data dari tabel
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Shortcut untuk memulai transaksi database
     */
    public function beginTransaction(): bool
    {
        return $this->connection()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection()->commit();
    }

    public function rollBack(): bool
    {
        return $this->connection()->rollBack();
    }
}