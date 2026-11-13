<?php
/**
 * SmartExam AI - Teacher Model
 * Mengelola profil guru pengajar, akun user, pencarian, paginasi, dan operasi CRUD.
 */

namespace App\Models;

use App\Core\Model;

class TeacherModel extends Model
{
    protected string $table = 'teachers';
    protected string $primaryKey = 'id';

    /**
     * Dapatkan detail guru lengkap dengan akun pengguna
     */
    public function getDetails(int $id): ?array
    {
        $sql = "SELECT t.*, u.name, u.email, u.username, u.is_active
                FROM `{$this->table}` t
                JOIN `users` u ON u.id = t.user_id
                WHERE t.id = :id LIMIT 1";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    /**
     * Cari profil guru berdasarkan user_id
     */
    public function findByUserId(int $userId): ?array
    {
        $sql = "SELECT t.*, u.name, u.email, u.username
                FROM `{$this->table}` t
                JOIN `users` u ON u.id = t.user_id
                WHERE t.user_id = :user_id LIMIT 1";
        return $this->fetchOne($sql, [':user_id' => $userId]);
    }

    /**
     * Cari profil guru berdasarkan NIP
     */
    public function findByNip(string $nip, ?int $excludeId = null): ?array
    {
        $sql = "SELECT t.*, u.name, u.email
                FROM `{$this->table}` t
                JOIN `users` u ON u.id = t.user_id
                WHERE t.nip = :nip";
        $params = [':nip' => $nip];

        if ($excludeId !== null) {
            $sql .= " AND t.id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";
        return $this->fetchOne($sql, $params);
    }

    /**
     * Relasi N:1: Dapatkan entitas User untuk guru ini
     */
    public function getUser(int $teacherId): ?array
    {
        $sql = "SELECT u.* FROM `users` u
                JOIN `{$this->table}` t ON t.user_id = u.id
                WHERE t.id = :teacher_id LIMIT 1";
        return $this->fetchOne($sql, [':teacher_id' => $teacherId]);
    }

    /**
     * Relasi 1:N: Dapatkan semua bank soal yang dibuat guru ini
     */
    public function getQuestionBanks(int $teacherId): array
    {
        $sql = "SELECT qb.*, s.name as subject_name, s.code as subject_code
                FROM `question_banks` qb
                JOIN `subjects` s ON s.id = qb.subject_id
                WHERE qb.teacher_id = :teacher_id
                ORDER BY qb.created_at DESC";
        return $this->fetchAll($sql, [':teacher_id' => $teacherId]);
    }

    /**
     * Relasi 1:N: Dapatkan seluruh paket ujian yang dibuat oleh guru ini
     */
    public function getExams(int $teacherId): array
    {
        $sql = "SELECT e.*, s.name as subject_name, s.code as subject_code,
                       qb.title as question_bank_title,
                       (SELECT COUNT(*) FROM `exam_participants` ep WHERE ep.exam_id = e.id) as participant_count
                FROM `exams` e
                JOIN `subjects` s ON s.id = e.subject_id
                LEFT JOIN `question_banks` qb ON qb.id = e.question_bank_id
                WHERE e.teacher_id = :teacher_id
                ORDER BY e.exam_date DESC, e.start_time DESC";
        return $this->fetchAll($sql, [':teacher_id' => $teacherId]);
    }

    /**
     * Dapatkan ringkasan statistik aktivitas guru (Bank Soal, Ujian, Butir Soal, Peserta)
     */
    public function getStats(int $teacherId): array
    {
        if ($this->isDbConnected()) {
            $totalBanks = $this->fetchOne("SELECT COUNT(*) as c FROM `question_banks` WHERE teacher_id = :id", [':id' => $teacherId])['c'] ?? 0;
            $totalExams = $this->fetchOne("SELECT COUNT(*) as c FROM `exams` WHERE teacher_id = :id", [':id' => $teacherId])['c'] ?? 0;
            $totalQuestions = $this->fetchOne("SELECT COUNT(*) as c FROM `questions` q JOIN `question_banks` qb ON qb.id = q.question_bank_id WHERE qb.teacher_id = :id", [':id' => $teacherId])['c'] ?? 0;
            $totalParticipants = $this->fetchOne("SELECT COUNT(*) as c FROM `exam_participants` ep JOIN `exams` e ON e.id = ep.exam_id WHERE e.teacher_id = :id", [':id' => $teacherId])['c'] ?? 0;

            return [
                'total_banks'        => (int)$totalBanks,
                'total_exams'        => (int)$totalExams,
                'total_questions'    => (int)$totalQuestions,
                'total_participants' => (int)$totalParticipants
            ];
        }

        return [
            'total_banks'        => 3,
            'total_exams'        => 2,
            'total_questions'    => 45,
            'total_participants' => 72
        ];
    }

    /**
     * Dapatkan semua guru dengan data akun terhubung
     */
    public function getAllWithDetails(): array
    {
        $sql = "SELECT t.*, u.name, u.email, u.username
                FROM `{$this->table}` t
                JOIN `users` u ON u.id = t.user_id
                ORDER BY u.name ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Paginasi dan Pencarian Guru
     */
    public function paginate(string $search = '', int $page = 1, int $perPage = 10): array
    {
        $search = trim($search);
        $where = "WHERE u.role = 'guru'";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (u.name LIKE :s1 OR t.nip LIKE :s2 OR u.email LIKE :s3 OR u.username LIKE :s4)";
            $searchTerm = '%' . $search . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
        }

        // Hitung total data
        $countSql = "SELECT COUNT(*) as total 
                     FROM `{$this->table}` t
                     JOIN `users` u ON u.id = t.user_id
                     {$where}";
        $countRes = $this->fetchOne($countSql, $params);
        $total = (int)($countRes['total'] ?? 0);

        // Batasi nilai halaman
        $page = max(1, $page);
        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        // Ambil data halaman ini
        $dataSql = "SELECT t.*, u.name, u.email, u.username, u.is_active,
                           (SELECT COUNT(*) FROM `question_banks` qb WHERE qb.teacher_id = t.id) as total_banks,
                           (SELECT COUNT(*) FROM `exams` e WHERE e.teacher_id = t.id) as total_exams
                    FROM `{$this->table}` t
                    JOIN `users` u ON u.id = t.user_id
                    {$where}
                    ORDER BY u.name ASC
                    LIMIT {$perPage} OFFSET {$offset}";
        $rows = $this->fetchAll($dataSql, $params);

        return [
            'data'        => $rows,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => max(1, $totalPages)
        ];
    }

    /**
     * Tambah Guru dan Akun Pengguna sekaligus dalam satu transaksi aman
     */
    public function createWithUser(array $userData, array $teacherData): int
    {
        if (!$this->isDbConnected()) {
            return 0;
        }

        try {
            $this->db->beginTransaction();

            // 1. Insert ke tabel users
            $hashPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
            $sqlUser = "INSERT INTO `users` (name, username, email, password, role, is_active, created_at)
                        VALUES (:name, :username, :email, :password, 'guru', :is_active, NOW())";
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([
                ':name'      => $userData['name'],
                ':username'  => $userData['username'],
                ':email'     => !empty($userData['email']) ? $userData['email'] : null,
                ':password'  => $hashPassword,
                ':is_active' => $userData['is_active'] ?? 1
            ]);
            $userId = (int)$this->db->lastInsertId();

            // 2. Insert ke tabel teachers
            $sqlTeacher = "INSERT INTO `teachers` (user_id, nip, nuptk, gender, phone, address, created_at)
                           VALUES (:user_id, :nip, :nuptk, :gender, :phone, :address, NOW())";
            $stmtTeacher = $this->db->prepare($sqlTeacher);
            $stmtTeacher->execute([
                ':user_id' => $userId,
                ':nip'     => $teacherData['nip'] ?: null,
                ':nuptk'   => $teacherData['nuptk'] ?: null,
                ':gender'  => $teacherData['gender'] ?? 'L',
                ':phone'   => $teacherData['phone'] ?: null,
                ':address' => $teacherData['address'] ?: null
            ]);
            $teacherId = (int)$this->db->lastInsertId();

            $this->db->commit();
            return $teacherId;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Gagal membuat data guru: " . $e->getMessage());
            \App\Helpers\SessionHelper::setFlash('danger', 'Detail error DB: ' . htmlspecialchars($e->getMessage()));
            return 0;
        }
    }

    /**
     * Perbarui data Guru dan Akun Pengguna
     */
    public function updateWithUser(int $teacherId, array $userData, array $teacherData): bool
    {
        $teacher = $this->find($teacherId);
        if (!$teacher) {
            return false;
        }

        $userId = (int)$teacher['user_id'];

        try {
            $this->db->beginTransaction();

            // 1. Update tabel users
            $userParams = [
                ':name'      => $userData['name'],
                ':username'  => $userData['username'],
                ':email'     => $userData['email'],
                ':is_active' => $userData['is_active'] ?? 1,
                ':id'        => $userId
            ];

            if (!empty($userData['password'])) {
                $sqlUser = "UPDATE `users` 
                            SET name = :name, username = :username, email = :email, password = :password, is_active = :is_active 
                            WHERE id = :id";
                $userParams[':password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
            } else {
                $sqlUser = "UPDATE `users` 
                            SET name = :name, username = :username, email = :email, is_active = :is_active 
                            WHERE id = :id";
            }
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute($userParams);

            // 2. Update tabel teachers
            $sqlTeacher = "UPDATE `teachers` 
                           SET nip = :nip, nuptk = :nuptk, gender = :gender, phone = :phone, address = :address 
                           WHERE id = :id";
            $stmtTeacher = $this->db->prepare($sqlTeacher);
            $stmtTeacher->execute([
                ':nip'     => $teacherData['nip'] ?: null,
                ':nuptk'   => $teacherData['nuptk'] ?: null,
                ':gender'  => $teacherData['gender'] ?? 'L',
                ':phone'   => $teacherData['phone'] ?: null,
                ':address' => $teacherData['address'] ?: null,
                ':id'      => $teacherId
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Gagal memperbarui data guru: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus Guru dan Akun User (Cascade)
     */
    public function deleteWithUser(int $teacherId): bool
    {
        $teacher = $this->find($teacherId);
        if (!$teacher) {
            return false;
        }

        $userId = (int)$teacher['user_id'];
        // Menghapus user akan otomatis menghapus teacher karena ON DELETE CASCADE
        return $this->execute("DELETE FROM `users` WHERE id = :id", [':id' => $userId]);
    }
}
