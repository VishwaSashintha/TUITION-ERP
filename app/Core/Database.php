<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * TuitionPro ERP — Database Singleton
 *
 * Provides a single PDO connection shared across the request lifecycle.
 * All queries must use prepared statements to prevent SQL injection.
 */
class Database
{
    private static ?self $instance = null;
    private ?PDO $pdo = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    private function connect(): void
    {
        $host    = env('DB_HOST',     '127.0.0.1');
        $port    = env('DB_PORT',     '3306');
        $dbname  = env('DB_DATABASE', 'tuitionpro');
        $user    = env('DB_USERNAME', 'root');
        $pass    = env('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci,
                                             time_zone='+05:30'",
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Never expose credentials or full DSN in logs
            throw new \RuntimeException(
                'Database connection failed. Check your .env configuration.',
                0,
                $e
            );
        }
    }

    // ─── Query Helpers ───────────────────────────────────────────────────────

    /**
     * Execute a query and return all rows.
     *
     * @param array<string,mixed> $params
     * @return array<int,array<string,mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a query and return a single row.
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>|null
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt   = $this->prepare($sql, $params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Execute an INSERT/UPDATE/DELETE and return affected rows.
     *
     * @param array<string,mixed> $params
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->prepare($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Execute an INSERT and return the last inserted ID.
     *
     * @param array<string,mixed> $params
     */
    public function insert(string $sql, array $params = []): int
    {
        $this->prepare($sql, $params);
        return (int) $this->pdo->lastInsertId();
    }

    // ─── Transaction Helpers ─────────────────────────────────────────────────

    public function beginTransaction(): void
    {
        $this->getConnection()->beginTransaction();
    }

    public function commit(): void
    {
        $this->getConnection()->commit();
    }

    public function rollBack(): void
    {
        $this->getConnection()->rollBack();
    }

    /**
     * Execute a callable inside a database transaction.
     * Automatically commits on success and rolls back on failure.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    // ─── Internal ────────────────────────────────────────────────────────────

    /**
     * @param array<string,mixed> $params
     */
    private function prepare(string $sql, array $params): \PDOStatement
    {
        $pdo  = $this->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

