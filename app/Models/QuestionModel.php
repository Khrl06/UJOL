<?php
/**
 * SmartExam AI - Question Model
 * Mengelola butir soal ujian (Pilihan Ganda & Essay), opsi A-E, preview, duplikasi, dan filter kesulitan.
 */

namespace App\Models;

use App\Core\Model;

class QuestionModel extends Model
{
    protected string $table = 'questions';
    protected string $primaryKey = 'id';

    /**
     * Cari semua soal pada bank soal tertentu
     */
    public function findByBank(int $bankId): array
    {
        return $this->fetchAll("SELECT * FROM `{$this->table}` WHERE question_bank_id = :bank_id ORDER BY id ASC", [
            ':bank_id' => $bankId
        ]);
    }

    /**
     * Relasi N:1: Dapatkan entitas Bank Soal induk
     */
    public function getQuestionBank(int $questionId): ?array
    {
        $sql = "SELECT qb.*, s.name as subject_name, s.code as subject_code
                FROM `question_banks` qb
                JOIN `{$this->table}` q ON q.question_bank_id = qb.id
                JOIN `subjects` s ON s.id = qb.subject_id
                WHERE q.id = :question_id LIMIT 1";
        return $this->fetchOne($sql, [':question_id' => $questionId]);
    }

    /**
     * Paginasi, Pencarian, dan Filter Tingkat Kesulitan untuk Butir Soal
     */
    public function paginateByBank(int $bankId, string $search = '', string $difficulty = '', int $page = 1, int $perPage = 10): array
    {
        $whereConditions = ["question_bank_id = :bank_id"];
        $params = [':bank_id' => $bankId];

        if (!empty($search)) {
            $whereConditions[] = "(question_text LIKE :s1 OR options LIKE :s2 OR explanation LIKE :s3)";
            $term = '%' . trim($search) . '%';
            $params[':s1'] = $term;
            $params[':s2'] = $term;
            $params[':s3'] = $term;
        }

        if (!empty($difficulty) && in_array($difficulty, ['mudah', 'sedang', 'sulit'], true)) {
            $whereConditions[] = "difficulty = :diff";
            $params[':diff'] = $difficulty;
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        // Hitung total
        $countSql = "SELECT COUNT(*) as total FROM `{$this->table}` {$whereClause}";
        $countRes = $this->fetchOne($countSql, $params);
        $total = (int)($countRes['total'] ?? 0);

        // Paginasi
        $page = max(1, $page);
        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $dataSql = "SELECT * FROM `{$this->table}` 
                    {$whereClause} 
                    ORDER BY id ASC 
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
     * Tambah Butir Soal Baru
     */
    public function create(array $data): int
    {
        $optionsJson = !empty($data['options']) ? json_encode($data['options'], JSON_UNESCAPED_UNICODE) : null;

        $sql = "INSERT INTO `{$this->table}` 
                (question_bank_id, question_text, question_type, options, correct_answer, score_weight, difficulty, is_active, explanation, created_at)
                VALUES 
                (:bank_id, :text, :type, :options, :correct, :weight, :diff, :active, :explanation, NOW())";
        
        $this->execute($sql, [
            ':bank_id'     => $data['question_bank_id'],
            ':text'        => $data['question_text'],
            ':type'        => $data['question_type'] ?? 'multiple_choice',
            ':options'     => $optionsJson,
            ':correct'     => $data['correct_answer'],
            ':weight'      => (float)($data['score_weight'] ?? 1.00),
            ':diff'        => $data['difficulty'] ?? 'sedang',
            ':active'      => (int)($data['is_active'] ?? 1),
            ':explanation' => $data['explanation'] ?? null
        ]);

        $questionId = (int)$this->lastInsertId();

        // Sinkronisasi total soal pada bank induk
        $bankModel = new QuestionBankModel();
        $bankModel->syncTotalQuestions($data['question_bank_id']);

        return $questionId;
    }

    /**
     * Perbarui Butir Soal
     */
    public function update(int $id, array $data): bool
    {
        $optionsJson = !empty($data['options']) ? json_encode($data['options'], JSON_UNESCAPED_UNICODE) : null;

        $sql = "UPDATE `{$this->table}` 
                SET question_text = :text,
                    question_type = :type,
                    options = :options,
                    correct_answer = :correct,
                    score_weight = :weight,
                    difficulty = :diff,
                    is_active = :active,
                    explanation = :explanation
                WHERE id = :id";

        $success = $this->execute($sql, [
            ':text'        => $data['question_text'],
            ':type'        => $data['question_type'] ?? 'multiple_choice',
            ':options'     => $optionsJson,
            ':correct'     => $data['correct_answer'],
            ':weight'      => (float)($data['score_weight'] ?? 1.00),
            ':diff'        => $data['difficulty'] ?? 'sedang',
            ':active'      => (int)($data['is_active'] ?? 1),
            ':explanation' => $data['explanation'] ?? null,
            ':id'          => $id
        ]);

        if (isset($data['question_bank_id'])) {
            $bankModel = new QuestionBankModel();
            $bankModel->syncTotalQuestions((int)$data['question_bank_id']);
        }

        return $success;
    }

    /**
     * Duplikasi Soal (1-Klik Salin Varian Soal)
     */
    public function duplicate(int $id): int
    {
        $source = $this->find($id);
        if (!$source) {
            return 0;
        }

        $bankId = (int)$source['question_bank_id'];
        $options = self::decodeOptions($source['options']);

        $newId = $this->create([
            'question_bank_id' => $bankId,
            'question_text'    => $source['question_text'] . ' (Salinan)',
            'question_type'    => $source['question_type'],
            'options'          => $options,
            'correct_answer'   => $source['correct_answer'],
            'score_weight'     => $source['score_weight'],
            'difficulty'       => $source['difficulty'] ?? 'sedang',
            'is_active'        => 1,
            'explanation'      => $source['explanation']
        ]);

        return $newId;
    }

    /**
     * Toggle Status Aktif / Nonaktif Butir Soal
     */
    public function toggleStatus(int $id): bool
    {
        $q = $this->find($id);
        if (!$q) {
            return false;
        }
        $newStatus = ((int)($q['is_active'] ?? 1) === 1) ? 0 : 1;
        return $this->execute("UPDATE `{$this->table}` SET is_active = :status WHERE id = :id", [
            ':status' => $newStatus,
            ':id'     => $id
        ]);
    }

    /**
     * Hapus Butir Soal dan Sinkronisasi Total
     */
    public function delete(int $id): bool
    {
        $q = $this->find($id);
        if (!$q) {
            return false;
        }

        $bankId = (int)$q['question_bank_id'];
        $success = $this->execute("DELETE FROM `{$this->table}` WHERE id = :id", [':id' => $id]);

        $bankModel = new QuestionBankModel();
        $bankModel->syncTotalQuestions($bankId);

        return $success;
    }

    /**
     * Helper: Decode JSON string opsi jawaban menjadi array PHP
     */
    public static function decodeOptions(mixed $options): array
    {
        if (is_array($options)) {
            return $options;
        }
        if (is_string($options)) {
            $decoded = json_decode($options, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Hitung total skor maksimal suatu bank soal
     */
    public function getTotalMaxScore(int $bankId): float
    {
        $result = $this->fetchOne("SELECT SUM(score_weight) as total FROM `{$this->table}` WHERE question_bank_id = :bank_id AND is_active = 1", [
            ':bank_id' => $bankId
        ]);
        return (float)($result['total'] ?? 0.0);
    }
}
