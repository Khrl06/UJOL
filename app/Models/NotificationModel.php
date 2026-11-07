<?php
/**
 * SmartExam AI - Notification Model
 * Mengelola penyimpanan, pembacaan, dan update notifikasi in-app pengguna.
 */

namespace App\Models;

use App\Core\Model;

class NotificationModel extends Model
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id';

    /**
     * Buat notifikasi baru untuk pengguna tertentu
     */
    public function createNotification(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        string $icon = 'bi-bell',
        ?string $link = null
    ): bool {
        $allowedTypes = ['info', 'success', 'warning', 'danger'];
        $type = in_array($type, $allowedTypes) ? $type : 'info';

        $sql = "INSERT INTO `{$this->table}` 
                (`user_id`, `title`, `message`, `type`, `icon`, `link`, `is_read`, `created_at`) 
                VALUES (:user_id, :title, :message, :type, :icon, :link, 0, NOW())";

        return $this->execute($sql, [
            ':user_id' => $userId,
            ':title'   => trim($title),
            ':message' => trim($message),
            ':type'    => $type,
            ':icon'    => trim($icon),
            ':link'    => $link ? trim($link) : null
        ]);
    }

    /**
     * Ambil daftar notifikasi pengguna (terbaru di atas)
     */
    public function getByUser(int $userId, int $limit = 15): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `user_id` = :user_id 
                ORDER BY `created_at` DESC 
                LIMIT " . (int)$limit;

        return $this->fetchAll($sql, [':user_id' => $userId]);
    }

    /**
     * Ambil notifikasi yang belum dibaca saja
     */
    public function getUnreadByUser(int $userId, int $limit = 10): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `user_id` = :user_id AND `is_read` = 0 
                ORDER BY `created_at` DESC 
                LIMIT " . (int)$limit;

        return $this->fetchAll($sql, [':user_id' => $userId]);
    }

    /**
     * Hitung total notifikasi belum dibaca untuk badge lonceng
     */
    public function countUnread(int $userId): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}` 
                WHERE `user_id` = :user_id AND `is_read` = 0";

        $res = $this->fetchOne($sql, [':user_id' => $userId]);
        return (int)($res['total'] ?? 0);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca
     */
    public function markAsRead(int $id, int $userId): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET `is_read` = 1, `updated_at` = NOW() 
                WHERE `id` = :id AND `user_id` = :user_id";

        return $this->execute($sql, [
            ':id'      => $id,
            ':user_id' => $userId
        ]);
    }

    /**
     * Tandai seluruh notifikasi pengguna sebagai sudah dibaca
     */
    public function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET `is_read` = 1, `updated_at` = NOW() 
                WHERE `user_id` = :user_id AND `is_read` = 0";

        return $this->execute($sql, [':user_id' => $userId]);
    }

    /**
     * Kirim notifikasi broadcast ke seluruh pengguna dengan role tertentu (cth: 'siswa' atau 'guru')
     */
    public function notifyRole(
        string $role,
        string $title,
        string $message,
        string $type = 'info',
        string $icon = 'bi-bell',
        ?string $link = null
    ): int {
        $users = $this->fetchAll("SELECT id FROM users WHERE role = :role AND is_active = 1", [':role' => $role]);
        $count = 0;
        foreach ($users as $u) {
            if ($this->createNotification((int)$u['id'], $title, $message, $type, $icon, $link)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Kirim notifikasi ke seluruh peserta dalam paket ujian tertentu
     */
    public function notifyExamParticipants(
        int $examId,
        string $title,
        string $message,
        string $type = 'info',
        string $icon = 'bi-bell',
        ?string $link = null
    ): int {
        $sql = "SELECT s.user_id 
                FROM exam_participants ep 
                JOIN students s ON ep.student_id = s.id 
                WHERE ep.exam_id = :exam_id";

        $participants = $this->fetchAll($sql, [':exam_id' => $examId]);
        $count = 0;
        foreach ($participants as $p) {
            if ($this->createNotification((int)$p['user_id'], $title, $message, $type, $icon, $link)) {
                $count++;
            }
        }
        return $count;
    }
}
