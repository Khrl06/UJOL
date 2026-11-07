<?php
/**
 * SmartExam AI - Audit Log Model
 * Mengelola pencatatan jejak audit aktivitas pengguna dan sistem.
 */

namespace App\Models;

use App\Core\Model;

class AuditLogModel extends Model
{
    protected string $table = 'audit_logs';
    protected string $primaryKey = 'id';

    /**
     * Relasi N:1: Dapatkan data Akun Pengguna pelaku aksi
     */
    public function getUser(int $logId): ?array
    {
        $sql = "SELECT u.* 
                FROM `users` u
                JOIN `{$this->table}` al ON al.user_id = u.id
                WHERE al.id = :log_id LIMIT 1";
        return $this->fetchOne($sql, [':log_id' => $logId]);
    }

    /**
     * Dapatkan riwayat aktivitas pengguna tertentu
     */
    public function findByUser(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit;
        return $this->fetchAll($sql, [':user_id' => $userId]);
    }

    /**
     * Catat log aktivitas baru secara otomatis
     */
    public function logActivity(?int $userId, string $action, ?string $description = null): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Agent';

        $sql = "INSERT INTO `{$this->table}` 
                (user_id, action, description, ip_address, user_agent, created_at)
                VALUES (:user_id, :action, :description, :ip, :ua, NOW())";
        return $this->execute($sql, [
            ':user_id'     => $userId,
            ':action'      => strtoupper(trim($action)),
            ':description' => $description,
            ':ip'          => substr($ip, 0, 45),
            ':ua'          => $ua
        ]);
    }

    /**
     * Dapatkan log aktivitas terbaru dengan data user pelaku
     */
    public function getRecentLogs(int $limit = 20): array
    {
        $sql = "SELECT al.*, u.name as user_name, u.role as user_role, u.username
                FROM `{$this->table}` al
                LEFT JOIN `users` u ON u.id = al.user_id
                ORDER BY al.created_at DESC 
                LIMIT " . (int)$limit;
        return $this->fetchAll($sql);
    }

    /**
     * Alias helper untuk logActivity
     */
    public function log(?int $userId, string $action, ?string $description = null): bool
    {
        return $this->logActivity($userId, $action, $description);
    }
}
