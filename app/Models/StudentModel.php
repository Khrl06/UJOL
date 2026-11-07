<?php
/**
 * SmartExam AI - Student Model
 * Mengelola data profil peserta didik, akun pengguna, kelas rombel, paginasi, dan CRUD.
 */

namespace App\Models;

use App\Core\Model;

class StudentModel extends Model
{
    protected string $table = 'students';
    protected string $primaryKey = 'id';

    /**
     * Dapatkan detail lengkap siswa berdasarkan ID
     */
    public function getDetails(int $id): ?array
    {
        $sql = "SELECT s.*, u.name, u.email, u.username, u.is_active,
                       c.class_name, c.grade_level, c.major, c.academic_year
                FROM `{$this->table}` s
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                WHERE s.id = :id LIMIT 1";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    /**
     * Cari profil siswa berdasarkan user_id
     */
    public function findByUserId(int $userId): ?array
    {
        $sql = "SELECT s.*, u.name, u.email, u.username,
                       c.class_name, c.grade_level, c.major
                FROM `{$this->table}` s
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                WHERE s.user_id = :user_id LIMIT 1";
        return $this->fetchOne($sql, [':user_id' => $userId]);
    }

    /**
     * Cari profil siswa berdasarkan NISN
     */
    public function findByNisn(string $nisn, ?int $excludeId = null): ?array
    {
        $sql = "SELECT s.*, u.name, u.email, c.class_name
                FROM `{$this->table}` s
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                WHERE s.nisn = :nisn";
        $params = [':nisn' => $nisn];

        if ($excludeId !== null) {
            $sql .= " AND s.id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";
        return $this->fetchOne($sql, $params);
    }

    /**
     * Relasi N:1: Dapatkan entitas User untuk siswa ini
     */
    public function getUser(int $studentId): ?array
    {
        $sql = "SELECT u.* FROM `users` u
                JOIN `{$this->table}` s ON s.user_id = u.id
                WHERE s.id = :student_id LIMIT 1";
        return $this->fetchOne($sql, [':student_id' => $studentId]);
    }

    /**
     * Relasi N:1: Dapatkan entitas Class untuk siswa ini
     */
    public function getClass(int $studentId): ?array
    {
        $sql = "SELECT c.* FROM `classes` c
                JOIN `{$this->table}` s ON s.class_id = c.id
                WHERE s.id = :student_id LIMIT 1";
        return $this->fetchOne($sql, [':student_id' => $studentId]);
    }

    /**
     * Relasi 1:N: Dapatkan riwayat partisipasi ujian siswa
     */
    public function getExamParticipations(int $studentId): array
    {
        $sql = "SELECT ep.*, e.title as exam_title, e.exam_date, e.start_time as scheduled_start,
                       e.duration_minutes, e.token, sub.name as subject_name, t_user.name as teacher_name,
                       r.final_score, r.is_passed
                FROM `exam_participants` ep
                JOIN `exams` e ON e.id = ep.exam_id
                JOIN `subjects` sub ON sub.id = e.subject_id
                JOIN `teachers` t ON t.id = e.teacher_id
                JOIN `users` t_user ON t_user.id = t.user_id
                LEFT JOIN `results` r ON r.exam_participant_id = ep.id
                WHERE ep.student_id = :student_id
                ORDER BY e.exam_date DESC, e.start_time DESC";
        return $this->fetchAll($sql, [':student_id' => $studentId]);
    }

    /**
     * Dapatkan ringkasan statistik asesmen siswa
     */
    public function getStats(int $studentId): array
    {
        if ($this->isDbConnected()) {
            $totalExams = $this->fetchOne("SELECT COUNT(*) as c FROM `exam_participants` WHERE student_id = :id", [':id' => $studentId])['c'] ?? 0;
            $completedExams = $this->fetchOne("SELECT COUNT(*) as c FROM `exam_participants` WHERE student_id = :id AND status IN ('submitted', 'graded')", [':id' => $studentId])['c'] ?? 0;
            $ongoingExams = $this->fetchOne("SELECT COUNT(*) as c FROM `exam_participants` WHERE student_id = :id AND status = 'ongoing'", [':id' => $studentId])['c'] ?? 0;
            $avgScore = $this->fetchOne("SELECT AVG(r.final_score) as avg_score FROM `results` r JOIN `exam_participants` ep ON ep.id = r.exam_participant_id WHERE ep.student_id = :id", [':id' => $studentId])['avg_score'] ?? 0;

            return [
                'total_exams'     => (int)$totalExams,
                'completed_exams' => (int)$completedExams,
                'ongoing_exams'   => (int)$ongoingExams,
                'avg_score'       => round((float)$avgScore, 1)
            ];
        }

        return [
            'total_exams'     => 5,
            'completed_exams' => 4,
            'ongoing_exams'   => 0,
            'avg_score'       => 86.5
        ];
    }

    /**
     * Dapatkan semua siswa beserta relasi user dan kelas
     */
    public function getAllWithDetails(): array
    {
        $sql = "SELECT s.*, u.name, u.email, u.username, c.class_name, c.grade_level
                FROM `{$this->table}` s
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                ORDER BY c.class_name ASC, u.name ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Paginasi, Pencarian, dan Filter Kelas untuk Siswa
     */
    public function paginate(string $search = '', ?int $classId = null, int $page = 1, int $perPage = 10): array
    {
        $search = trim($search);
        $whereConditions = ["u.role = 'siswa'"];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(u.name LIKE :s1 OR s.nisn LIKE :s2 OR u.email LIKE :s3 OR u.username LIKE :s4)";
            $searchTerm = '%' . $search . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
        }

        if (!empty($classId)) {
            $whereConditions[] = "s.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        // Hitung total data
        $countSql = "SELECT COUNT(*) as total 
                     FROM `{$this->table}` s
                     JOIN `users` u ON u.id = s.user_id
                     JOIN `classes` c ON c.id = s.class_id
                     {$whereClause}";
        $countRes = $this->fetchOne($countSql, $params);
        $total = (int)($countRes['total'] ?? 0);

        // Paginasi
        $page = max(1, $page);
        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $dataSql = "SELECT s.*, u.name, u.email, u.username, u.is_active,
                           c.class_name, c.grade_level, c.major
                    FROM `{$this->table}` s
                    JOIN `users` u ON u.id = s.user_id
                    JOIN `classes` c ON c.id = s.class_id
                    {$whereClause}
                    ORDER BY c.class_name ASC, u.name ASC
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
     * Tambah Siswa dan Akun Pengguna sekaligus dalam satu transaksi
     */
    public function createWithUser(array $userData, array $studentData): int
    {
        if (!$this->isDbConnected()) {
            return 0;
        }

        try {
            $this->db->beginTransaction();

            // 1. Insert ke tabel users
            $hashPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
            $sqlUser = "INSERT INTO `users` (name, username, email, password, role, is_active, created_at)
                        VALUES (:name, :username, :email, :password, 'siswa', :is_active, NOW())";
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([
                ':name'      => $userData['name'],
                ':username'  => $userData['username'],
                ':email'     => !empty($userData['email']) ? $userData['email'] : null,
                ':password'  => $hashPassword,
                ':is_active' => $userData['is_active'] ?? 1
            ]);
            $userId = (int)$this->db->lastInsertId();

            // 2. Insert ke tabel students
            $sqlStudent = "INSERT INTO `students` (user_id, class_id, nisn, gender, phone, address, created_at)
                           VALUES (:user_id, :class_id, :nisn, :gender, :phone, :address, NOW())";
            $stmtStudent = $this->db->prepare($sqlStudent);
            $stmtStudent->execute([
                ':user_id'  => $userId,
                ':class_id' => $studentData['class_id'],
                ':nisn'     => $studentData['nisn'],
                ':gender'   => $studentData['gender'] ?? 'L',
                ':phone'    => $studentData['phone'] ?: null,
                ':address'  => $studentData['address'] ?: null
            ]);
            $studentId = (int)$this->db->lastInsertId();

            $this->db->commit();
            return $studentId;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Gagal membuat data siswa: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Perbarui data Siswa dan Akun Pengguna
     */
    public function updateWithUser(int $studentId, array $userData, array $studentData): bool
    {
        $student = $this->find($studentId);
        if (!$student) {
            return false;
        }

        $userId = (int)$student['user_id'];

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

            // 2. Update tabel students
            $sqlStudent = "UPDATE `students` 
                           SET class_id = :class_id, nisn = :nisn, gender = :gender, phone = :phone, address = :address 
                           WHERE id = :id";
            $stmtStudent = $this->db->prepare($sqlStudent);
            $stmtStudent->execute([
                ':class_id' => $studentData['class_id'],
                ':nisn'     => $studentData['nisn'],
                ':gender'   => $studentData['gender'] ?? 'L',
                ':phone'    => $studentData['phone'] ?: null,
                ':address'  => $studentData['address'] ?: null,
                ':id'       => $studentId
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Gagal memperbarui data siswa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Perbarui data profil mandiri oleh siswa (nama, email, phone, gender, address)
     * Mempertahankan NISN dan Kelas agar tidak berubah secara sepihak
     */
    public function updateProfileByStudent(int $studentId, array $userData, array $studentData): bool
    {
        $student = $this->find($studentId);
        if (!$student) {
            return false;
        }

        $userId = (int)$student['user_id'];

        if (!$this->isDbConnected()) {
            return true;
        }

        try {
            $this->db->beginTransaction();

            // 1. Update tabel users (nama, username, email)
            $sqlUser = "UPDATE `users` 
                        SET name = :name, username = :username, email = :email, updated_at = NOW() 
                        WHERE id = :id";
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([
                ':name'     => $userData['name'],
                ':username' => $userData['username'],
                ':email'    => $userData['email'],
                ':id'       => $userId
            ]);

            // 2. Update tabel students (gender, phone, address)
            $sqlStudent = "UPDATE `students` 
                           SET gender = :gender, phone = :phone, address = :address, updated_at = NOW() 
                           WHERE id = :id";
            $stmtStudent = $this->db->prepare($sqlStudent);
            $stmtStudent->execute([
                ':gender'   => $studentData['gender'] ?? 'L',
                ':phone'    => $studentData['phone'] ?: null,
                ':address'  => $studentData['address'] ?: null,
                ':id'       => $studentId
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Gagal memperbarui profil siswa mandiri: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus Siswa dan Akun User (Cascade)
     */
    public function deleteWithUser(int $studentId): bool
    {
        $student = $this->find($studentId);
        if (!$student) {
            return false;
        }

        $userId = (int)$student['user_id'];
        return $this->execute("DELETE FROM `users` WHERE id = :id", [':id' => $userId]);
    }
}
