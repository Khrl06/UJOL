<?php
/**
 * SmartExam AI - Base Model
 * Kelas dasar untuk seluruh model data aplikasi dengan helper query PDO.
 */

namespace App\Core;

use PDO;
use PDOException;

abstract class Model
{
    protected ?PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Cek ketersediaan koneksi database
     */
    public function isDbConnected(): bool
    {
        return $this->db !== null && Database::isConnected();
    }

    /**
     * Eksekusi query dengan prepared statement
     */
    public function query(string $sql, array $params = []): ?\PDOStatement
    {
        if (!$this->isDbConnected()) {
            return null;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage() . " SQL: " . $sql);
            return null;
        }
    }

    /**
     * Ambil satu baris data (associative array)
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return null;
        }
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Ambil semua baris data
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    /**
     * Eksekusi insert/update/delete, return boolean atau affected rows
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->query($sql, $params);
        return $stmt !== null;
    }

    /**
     * Dapatkan ID hasil insert terakhir
     */
    public function lastInsertId(): string|int
    {
        return $this->db ? $this->db->lastInsertId() : 0;
    }

    /**
     * Ambil semua record dari tabel saat ini
     */
    public function all(): array
    {
        if (empty($this->table)) {
            return [];
        }
        return $this->fetchAll("SELECT * FROM `{$this->table}` ORDER BY `{$this->primaryKey}` DESC");
    }

    /**
     * Temukan record berdasarkan primary key
     */
    public function find(int|string $id): ?array
    {
        if (empty($this->table)) {
            return null;
        }
        return $this->fetchOne("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1", [
            ':id' => $id
        ]);
    }

    /**
     * Hitung total baris pada tabel
     */
    public function count(string $where = '', array $params = []): int
    {
        if (empty($this->table)) {
            return 0;
        }
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $result = $this->fetchOne($sql, $params);
        return (int) ($result['total'] ?? 0);
    }
}
