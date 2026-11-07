<?php
/**
 * SmartExam AI - Subject Model
 * Mengelola data mata pelajaran SMA dan operasi CRUD.
 */

namespace App\Models;

use App\Core\Model;

class SubjectModel extends Model
{
    protected string $table = 'subjects';
    protected string $primaryKey = 'id';

    /**
     * Cari mata pelajaran berdasarkan kode (misal: 'INF-12')
     */
    public function findByCode(string $code, ?int $excludeId = null): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `code` = :code";
        $params = [':code' => $code];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";
        return $this->fetchOne($sql, $params);
    }

    /**
     * Relasi 1:N: Dapatkan seluruh bank soal pada mata pelajaran ini
     */
    public function getQuestionBanks(int $subjectId): array
    {
        $sql = "SELECT qb.*, u.name as teacher_name
                FROM `question_banks` qb
                JOIN `teachers` t ON t.id = qb.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE qb.subject_id = :subject_id
                ORDER BY qb.created_at DESC";
        return $this->fetchAll($sql, [':subject_id' => $subjectId]);
    }

    /**
     * Relasi 1:N: Dapatkan seluruh jadwal ujian untuk mata pelajaran ini
     */
    public function getExams(int $subjectId): array
    {
        $sql = "SELECT e.*, u.name as teacher_name
                FROM `exams` e
                JOIN `teachers` t ON t.id = e.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE e.subject_id = :subject_id
                ORDER BY e.exam_date DESC, e.start_time DESC";
        return $this->fetchAll($sql, [':subject_id' => $subjectId]);
    }

    /**
     * Cek apakah mata pelajaran memiliki bank soal atau ujian terhubung
     */
    public function hasRelatedData(int $subjectId): bool
    {
        $sql = "SELECT (
            (SELECT COUNT(*) FROM `question_banks` WHERE subject_id = :s1) +
            (SELECT COUNT(*) FROM `exams` WHERE subject_id = :s2)
        ) as total";
        $res = $this->fetchOne($sql, [':s1' => $subjectId, ':s2' => $subjectId]);
        return ((int)($res['total'] ?? 0)) > 0;
    }

    /**
     * Dapatkan semua mapel beserta statistik bank soal dan ujian terkait
     */
    public function getAllWithStats(string $search = ''): array
    {
        $where = "";
        $params = [];

        if (!empty($search)) {
            $where = "WHERE s.name LIKE :s1 OR s.code LIKE :s2 OR s.description LIKE :s3";
            $term = '%' . trim($search) . '%';
            $params[':s1'] = $term;
            $params[':s2'] = $term;
            $params[':s3'] = $term;
        }

        $sql = "SELECT s.*,
                       (SELECT COUNT(*) FROM `question_banks` qb WHERE qb.subject_id = s.id) as question_bank_count,
                       (SELECT COUNT(*) FROM `exams` e WHERE e.subject_id = s.id) as exam_count
                FROM `{$this->table}` s
                {$where}
                ORDER BY s.name ASC";
        return $this->fetchAll($sql, $params);
    }

    /**
     * Tambah data mata pelajaran baru
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `{$this->table}` (code, name, description, created_at)
                VALUES (:code, :name, :description, NOW())";
        $this->execute($sql, [
            ':code'        => strtoupper(trim($data['code'])),
            ':name'        => trim($data['name']),
            ':description' => trim($data['description'] ?? '') ?: null
        ]);
        return (int)$this->lastInsertId();
    }

    /**
     * Perbarui data mata pelajaran
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET code = :code, name = :name, description = :description 
                WHERE id = :id";
        return $this->execute($sql, [
            ':code'        => strtoupper(trim($data['code'])),
            ':name'        => trim($data['name']),
            ':description' => trim($data['description'] ?? '') ?: null,
            ':id'          => $id
        ]);
    }

    /**
     * Hapus mata pelajaran (dengan proteksi data relasional)
     */
    public function delete(int $id): bool
    {
        if ($this->hasRelatedData($id)) {
            return false;
        }
        return $this->execute("DELETE FROM `{$this->table}` WHERE id = :id", [':id' => $id]);
    }
}
