<?php
/**
 * SmartExam AI - Class Model
 * Mengelola rombongan belajar / kelas SMA dan operasi CRUD.
 */

namespace App\Models;

use App\Core\Model;

class ClassModel extends Model
{
    protected string $table = 'classes';
    protected string $primaryKey = 'id';

    /**
     * Cari kelas berdasarkan nama kelas (misal: 'XII MIPA 1')
     */
    public function findByName(string $className, ?int $excludeId = null): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `class_name` = :name";
        $params = [':name' => $className];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";
        return $this->fetchOne($sql, $params);
    }

    /**
     * Dapatkan kelas berdasarkan tingkatan jenjang (10, 11, 12)
     */
    public function findByGrade(string $gradeLevel): array
    {
        return $this->fetchAll("SELECT * FROM `{$this->table}` WHERE `grade_level` = :grade ORDER BY `class_name` ASC", [
            ':grade' => $gradeLevel
        ]);
    }

    /**
     * Relasi 1:N: Dapatkan seluruh siswa dalam kelas ini
     */
    public function getStudents(int $classId): array
    {
        $sql = "SELECT s.*, u.name, u.email, u.username, u.is_active
                FROM `students` s
                JOIN `users` u ON u.id = s.user_id
                WHERE s.class_id = :class_id
                ORDER BY u.name ASC";
        return $this->fetchAll($sql, [':class_id' => $classId]);
    }

    /**
     * Dapatkan total jumlah siswa dalam suatu kelas
     */
    public function getStudentCount(int $classId): int
    {
        $result = $this->fetchOne("SELECT COUNT(*) as total FROM `students` WHERE `class_id` = :class_id", [
            ':class_id' => $classId
        ]);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Cek apakah kelas memiliki siswa terhubung
     */
    public function hasStudents(int $classId): bool
    {
        return $this->getStudentCount($classId) > 0;
    }

    /**
     * Dapatkan semua kelas beserta jumlah siswa masing-masing
     */
    public function getAllWithCounts(string $search = ''): array
    {
        $where = "";
        $params = [];

        if (!empty($search)) {
            $where = "WHERE c.class_name LIKE :s1 OR c.major LIKE :s2 OR c.academic_year LIKE :s3";
            $term = '%' . trim($search) . '%';
            $params[':s1'] = $term;
            $params[':s2'] = $term;
            $params[':s3'] = $term;
        }

        $sql = "SELECT c.*, COUNT(s.id) as total_students
                FROM `{$this->table}` c
                LEFT JOIN `students` s ON s.class_id = c.id
                {$where}
                GROUP BY c.id
                ORDER BY c.grade_level DESC, c.class_name ASC";
        return $this->fetchAll($sql, $params);
    }

    /**
     * Tambah data kelas baru
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `{$this->table}` (class_name, grade_level, major, academic_year, created_at)
                VALUES (:name, :grade, :major, :year, NOW())";
        $this->execute($sql, [
            ':name'  => $data['class_name'],
            ':grade' => $data['grade_level'],
            ':major' => $data['major'] ?? 'MIPA',
            ':year'  => $data['academic_year'] ?? '2026/2027'
        ]);
        return (int)$this->lastInsertId();
    }

    /**
     * Perbarui data kelas
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET class_name = :name, grade_level = :grade, major = :major, academic_year = :year 
                WHERE id = :id";
        return $this->execute($sql, [
            ':name'  => $data['class_name'],
            ':grade' => $data['grade_level'],
            ':major' => $data['major'] ?? 'MIPA',
            ':year'  => $data['academic_year'] ?? '2026/2027',
            ':id'    => $id
        ]);
    }

    /**
     * Hapus kelas (dengan proteksi data anak)
     */
    public function delete(int $id): bool
    {
        if ($this->hasStudents($id)) {
            return false;
        }
        return $this->execute("DELETE FROM `{$this->table}` WHERE id = :id", [':id' => $id]);
    }
}
