<?php
/**
 * SmartExam AI - Question Bank Model
 * Mengelola paket bank soal, otorisasi kepemilikan guru, pencarian, dan paginasi.
 */

namespace App\Models;

use App\Core\Model;

class QuestionBankModel extends Model
{
    protected string $table = 'question_banks';
    protected string $primaryKey = 'id';

    /**
     * Dapatkan detail bank soal beserta nama guru dan mata pelajaran
     */
    public function getDetails(int $id): ?array
    {
        $sql = "SELECT qb.*, s.name as subject_name, s.code as subject_code,
                       u.name as teacher_name, t.nip as teacher_nip
                FROM `{$this->table}` qb
                JOIN `subjects` s ON s.id = qb.subject_id
                JOIN `teachers` t ON t.id = qb.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE qb.id = :id LIMIT 1";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    /**
     * Cek apakah bank soal dimiliki oleh guru tertentu (Otorisasi Keamanan)
     */
    public function isOwnedByTeacher(int $bankId, int $teacherId): bool
    {
        $sql = "SELECT id FROM `{$this->table}` WHERE id = :id AND teacher_id = :tid LIMIT 1";
        return $this->fetchOne($sql, [':id' => $bankId, ':tid' => $teacherId]) !== null;
    }

    /**
     * Relasi N:1: Dapatkan data Guru pembuat bank soal ini
     */
    public function getTeacher(int $bankId): ?array
    {
        $sql = "SELECT t.*, u.name, u.email
                FROM `teachers` t
                JOIN `users` u ON u.id = t.user_id
                JOIN `{$this->table}` qb ON qb.teacher_id = t.id
                WHERE qb.id = :bank_id LIMIT 1";
        return $this->fetchOne($sql, [':bank_id' => $bankId]);
    }

    /**
     * Relasi N:1: Dapatkan data Mata Pelajaran untuk bank soal ini
     */
    public function getSubject(int $bankId): ?array
    {
        $sql = "SELECT s.*
                FROM `subjects` s
                JOIN `{$this->table}` qb ON qb.subject_id = s.id
                WHERE qb.id = :bank_id LIMIT 1";
        return $this->fetchOne($sql, [':bank_id' => $bankId]);
    }

    /**
     * Relasi 1:N: Dapatkan semua butir soal di dalam bank soal ini
     */
    public function getQuestions(int $bankId): array
    {
        $sql = "SELECT * FROM `questions` 
                WHERE question_bank_id = :bank_id 
                ORDER BY id ASC";
        return $this->fetchAll($sql, [':bank_id' => $bankId]);
    }

    /**
     * Hitung jumlah aktual butir soal dalam bank soal ini
     */
    public function getQuestionCount(int $bankId): int
    {
        $result = $this->fetchOne("SELECT COUNT(*) as total FROM `questions` WHERE question_bank_id = :bank_id", [
            ':bank_id' => $bankId
        ]);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Sinkronisasi nilai field total_questions dengan jumlah fisik butir soal
     */
    public function syncTotalQuestions(int $bankId): void
    {
        $total = $this->getQuestionCount($bankId);
        $this->execute("UPDATE `{$this->table}` SET total_questions = :total WHERE id = :id", [
            ':total' => $total,
            ':id'    => $bankId
        ]);
    }

    /**
     * Paginasi Bank Soal khusus milik Guru yang login
     */
    public function paginateByTeacher(int $teacherId, string $search = '', ?int $subjectId = null, int $page = 1, int $perPage = 8): array
    {
        $whereConditions = ["qb.teacher_id = :teacher_id"];
        $params = [':teacher_id' => $teacherId];

        if (!empty($search)) {
            $whereConditions[] = "(qb.title LIKE :s1 OR qb.description LIKE :s2 OR s.name LIKE :s3)";
            $term = '%' . trim($search) . '%';
            $params[':s1'] = $term;
            $params[':s2'] = $term;
            $params[':s3'] = $term;
        }

        if (!empty($subjectId)) {
            $whereConditions[] = "qb.subject_id = :subject_id";
            $params[':subject_id'] = $subjectId;
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        // Hitung total
        $countSql = "SELECT COUNT(*) as total 
                     FROM `{$this->table}` qb
                     JOIN `subjects` s ON s.id = qb.subject_id
                     {$whereClause}";
        $countRes = $this->fetchOne($countSql, $params);
        $total = (int)($countRes['total'] ?? 0);

        // Paginasi
        $page = max(1, $page);
        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $dataSql = "SELECT qb.*, s.name as subject_name, s.code as subject_code,
                           (SELECT COUNT(*) FROM `questions` q WHERE q.question_bank_id = qb.id AND q.question_type = 'multiple_choice') as mc_count,
                           (SELECT COUNT(*) FROM `questions` q WHERE q.question_bank_id = qb.id AND q.question_type = 'essay') as essay_count
                    FROM `{$this->table}` qb
                    JOIN `subjects` s ON s.id = qb.subject_id
                    {$whereClause}
                    ORDER BY qb.created_at DESC
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
     * Paginasi Bank Soal Global (Untuk Administrator)
     */
    public function paginateAll(string $search = '', ?int $subjectId = null, ?int $teacherId = null, int $page = 1, int $perPage = 8): array
    {
        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(qb.title LIKE :s1 OR qb.description LIKE :s2 OR s.name LIKE :s3 OR u.name LIKE :s4)";
            $term = '%' . trim($search) . '%';
            $params[':s1'] = $term;
            $params[':s2'] = $term;
            $params[':s3'] = $term;
            $params[':s4'] = $term;
        }

        if (!empty($subjectId)) {
            $whereConditions[] = "qb.subject_id = :subject_id";
            $params[':subject_id'] = $subjectId;
        }

        if (!empty($teacherId)) {
            $whereConditions[] = "qb.teacher_id = :teacher_id";
            $params[':teacher_id'] = $teacherId;
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        $countSql = "SELECT COUNT(*) as total 
                     FROM `{$this->table}` qb
                     JOIN `subjects` s ON s.id = qb.subject_id
                     JOIN `teachers` t ON t.id = qb.teacher_id
                     JOIN `users` u ON u.id = t.user_id
                     {$whereClause}";
        $countRes = $this->fetchOne($countSql, $params);
        $total = (int)($countRes['total'] ?? 0);

        $page = max(1, $page);
        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $dataSql = "SELECT qb.*, s.name as subject_name, s.code as subject_code,
                           u.name as teacher_name, t.nip as teacher_nip,
                           (SELECT COUNT(*) FROM `questions` q WHERE q.question_bank_id = qb.id AND q.question_type = 'multiple_choice') as mc_count,
                           (SELECT COUNT(*) FROM `questions` q WHERE q.question_bank_id = qb.id AND q.question_type = 'essay') as essay_count
                    FROM `{$this->table}` qb
                    JOIN `subjects` s ON s.id = qb.subject_id
                    JOIN `teachers` t ON t.id = qb.teacher_id
                    JOIN `users` u ON u.id = t.user_id
                    {$whereClause}
                    ORDER BY qb.created_at DESC
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
     * Buat Bank Soal Baru
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `{$this->table}` (teacher_id, subject_id, grade_level, title, description, total_questions, is_active, created_at)
                VALUES (:teacher_id, :subject_id, :grade_level, :title, :description, 0, :is_active, NOW())";
        $this->execute($sql, [
            ':teacher_id'   => $data['teacher_id'],
            ':subject_id'   => $data['subject_id'],
            ':grade_level'  => $data['grade_level'] ?? '12',
            ':title'        => trim($data['title']),
            ':description'  => trim($data['description'] ?? '') ?: null,
            ':is_active'    => $data['is_active'] ?? 1
        ]);
        return (int)$this->lastInsertId();
    }

    /**
     * Perbarui Data Bank Soal
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE `{$this->table}`
                SET subject_id = :subject_id, grade_level = :grade_level, title = :title, description = :description, is_active = :is_active
                WHERE id = :id";
        return $this->execute($sql, [
            ':subject_id'   => $data['subject_id'],
            ':grade_level'  => $data['grade_level'] ?? '12',
            ':title'        => trim($data['title']),
            ':description'  => trim($data['description'] ?? '') ?: null,
            ':is_active'    => $data['is_active'] ?? 1,
            ':id'           => $id
        ]);
    }

    /**
     * Hapus Bank Soal (Cascade ke questions)
     */
    public function delete(int $id): bool
    {
        return $this->execute("DELETE FROM `{$this->table}` WHERE id = :id", [':id' => $id]);
    }

    /**
     * Dapatkan semua bank soal milik guru tertentu
     */
    public function getByTeacher(int $teacherId): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE teacher_id = :tid ORDER BY title ASC";
        return $this->fetchAll($sql, [':tid' => $teacherId]);
    }
}
