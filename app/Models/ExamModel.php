<?php
/**
 * SmartExam AI - Exam Model
 * Mengelola siklus paket ujian, token, alokasi kelas/peserta, parameter asesmen, dan pengawasan AI.
 */

namespace App\Models;

use App\Core\Model;

class ExamModel extends Model
{
    protected string $table = 'exams';
    protected string $primaryKey = 'id';

    /**
     * Dapatkan detail lengkap ujian dengan relasi Guru, Mapel, dan Bank Soal
     */
    public function getDetails(int $id): ?array
    {
        $sql = "SELECT e.*, s.name as subject_name, s.code as subject_code,
                       u.name as teacher_name, t.nip as teacher_nip,
                       qb.title as question_bank_title, qb.total_questions as bank_question_count,
                       (SELECT COUNT(*) FROM `exam_participants` ep WHERE ep.exam_id = e.id) as total_participants,
                       (SELECT COUNT(*) FROM `exam_participants` ep WHERE ep.exam_id = e.id AND ep.status = 'submitted') as submitted_count,
                       (SELECT COUNT(*) FROM `exam_participants` ep WHERE ep.exam_id = e.id AND ep.status = 'ongoing') as ongoing_count
                FROM `{$this->table}` e
                JOIN `subjects` s ON s.id = e.subject_id
                JOIN `teachers` t ON t.id = e.teacher_id
                JOIN `users` u ON u.id = t.user_id
                LEFT JOIN `question_banks` qb ON qb.id = e.question_bank_id
                WHERE e.id = :id LIMIT 1";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    /**
     * Cek apakah paket ujian dimiliki oleh guru tertentu (Otorisasi Keamanan)
     */
    public function isOwnedByTeacher(int $examId, int $teacherId): bool
    {
        $sql = "SELECT id FROM `{$this->table}` WHERE id = :id AND teacher_id = :tid LIMIT 1";
        return $this->fetchOne($sql, [':id' => $examId, ':tid' => $teacherId]) !== null;
    }

    /**
     * Cari ujian berdasarkan token akses unik
     */
    public function findByToken(string $token): ?array
    {
        $sql = "SELECT e.*, s.name as subject_name, u.name as teacher_name
                FROM `{$this->table}` e
                JOIN `subjects` s ON s.id = e.subject_id
                JOIN `teachers` t ON t.id = e.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE e.token = :token LIMIT 1";
        return $this->fetchOne($sql, [':token' => strtoupper(trim($token))]);
    }

    /**
     * Hasilkan Token Ujian 6 Karakter Alfanumerik Unik
     */
    public function generateUniqueToken(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $token = '';
            for ($i = 0; $i < 6; $i++) {
                $token .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $exists = $this->findByToken($token);
        } while ($exists !== null);

        return $token;
    }

    /**
     * Paginasi Ujian khusus milik Guru yang login
     */
    public function paginateByTeacher(int $teacherId, string $search = '', string $status = '', ?int $subjectId = null, int $page = 1, int $perPage = 8): array
    {
        $whereConditions = ["e.teacher_id = :teacher_id"];
        $params = [':teacher_id' => $teacherId];

        if (!empty($search)) {
            $whereConditions[] = "(e.title LIKE :s1 OR e.description LIKE :s2 OR e.token LIKE :s3 OR s.name LIKE :s4)";
            $term = '%' . trim($search) . '%';
            $params[':s1'] = $term;
            $params[':s2'] = $term;
            $params[':s3'] = $term;
            $params[':s4'] = $term;
        }

        if (!empty($status) && in_array(strtolower($status), ['draft', 'published', 'ongoing', 'completed', 'cancelled'], true)) {
            $whereConditions[] = "e.status = :status";
            $params[':status'] = strtolower($status);
        }

        if (!empty($subjectId)) {
            $whereConditions[] = "e.subject_id = :subject_id";
            $params[':subject_id'] = $subjectId;
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        // Hitung total data
        $countSql = "SELECT COUNT(*) as total 
                     FROM `{$this->table}` e
                     JOIN `subjects` s ON s.id = e.subject_id
                     {$whereClause}";
        $countRes = $this->fetchOne($countSql, $params);
        $total = (int)($countRes['total'] ?? 0);

        // Paginasi
        $page = max(1, $page);
        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $dataSql = "SELECT e.*, s.name as subject_name, s.code as subject_code,
                           qb.title as question_bank_title,
                           (SELECT COUNT(*) FROM `exam_participants` ep WHERE ep.exam_id = e.id) as total_participants,
                           (SELECT COUNT(*) FROM `exam_participants` ep WHERE ep.exam_id = e.id AND ep.status = 'submitted') as submitted_count
                    FROM `{$this->table}` e
                    JOIN `subjects` s ON s.id = e.subject_id
                    LEFT JOIN `question_banks` qb ON qb.id = e.question_bank_id
                    {$whereClause}
                    ORDER BY e.created_at DESC
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
     * Buat Ujian Baru dan Alokasikan Peserta Rombel Kelas dalam Satu Transaksi Aman
     */
    public function createWithParticipants(array $examData, array $classIds = [], array $studentIds = []): int
    {
        if (!$this->isDbConnected()) {
            return 0;
        }

        try {
            $this->db->beginTransaction();

            $token = !empty($examData['token']) ? strtoupper($examData['token']) : $this->generateUniqueToken();

            $sql = "INSERT INTO `{$this->table}` 
                    (teacher_id, subject_id, question_bank_id, title, description,
                     start_date, end_date, exam_date, start_time, end_time, duration_minutes, total_questions,
                     is_random_questions, is_random_options, max_attempts, passing_grade,
                     enable_dual_camera, enable_ai_proctoring, token, status, created_at)
                    VALUES 
                    (:teacher_id, :subject_id, :question_bank_id, :title, :description,
                     :start_date, :end_date, :exam_date, :start_time, :end_time, :duration_minutes, :total_questions,
                     :is_random_questions, :is_random_options, :max_attempts, :passing_grade,
                     :enable_dual_camera, :enable_ai_proctoring, :token, :status, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':teacher_id'           => $examData['teacher_id'],
                ':subject_id'           => $examData['subject_id'],
                ':question_bank_id'     => $examData['question_bank_id'] ?: null,
                ':title'                => trim($examData['title']),
                ':description'          => trim($examData['description'] ?? '') ?: null,
                ':start_date'           => $examData['start_date'],
                ':end_date'             => $examData['end_date'],
                ':exam_date'            => $examData['start_date'],
                ':start_time'           => $examData['start_time'],
                ':end_time'             => $examData['end_time'],
                ':duration_minutes'     => (int)$examData['duration_minutes'],
                ':total_questions'      => (int)($examData['total_questions'] ?? 0),
                ':is_random_questions'  => (int)($examData['is_random_questions'] ?? 0),
                ':is_random_options'    => (int)($examData['is_random_options'] ?? 0),
                ':max_attempts'         => max(1, (int)($examData['max_attempts'] ?? 1)),
                ':passing_grade'        => (float)($examData['passing_grade'] ?? 75.00),
                ':enable_dual_camera'   => (int)($examData['enable_dual_camera'] ?? 1),
                ':enable_ai_proctoring' => (int)($examData['enable_ai_proctoring'] ?? 1),
                ':token'                => $token,
                ':status'               => $examData['status'] ?? 'draft'
            ]);

            $examId = (int)$this->db->lastInsertId();

            // Alokasikan siswa dari rombel kelas
            if (!empty($classIds)) {
                $inQuery = implode(',', array_map('intval', $classIds));
                $studentRows = $this->fetchAll("SELECT id FROM `students` WHERE class_id IN ({$inQuery})");
                foreach ($studentRows as $sr) {
                    $studentIds[] = (int)$sr['id'];
                }
            }

            $uniqueStudentIds = array_unique(array_filter($studentIds));
            if (!empty($uniqueStudentIds)) {
                $insertPartSql = "INSERT IGNORE INTO `exam_participants` (exam_id, student_id, status, created_at) VALUES (:exam_id, :student_id, 'registered', NOW())";
                $stmtPart = $this->db->prepare($insertPartSql);
                foreach ($uniqueStudentIds as $sId) {
                    $stmtPart->execute([
                        ':exam_id'    => $examId,
                        ':student_id' => $sId
                    ]);
                }
            }

            $this->db->commit();
            return $examId;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Gagal membuat ujian: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Perbarui Data Ujian
     */
    public function updateExam(int $id, array $examData): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET subject_id = :subject_id,
                    question_bank_id = :question_bank_id,
                    title = :title,
                    description = :description,
                    start_date = :start_date,
                    end_date = :end_date,
                    exam_date = :exam_date,
                    start_time = :start_time,
                    end_time = :end_time,
                    duration_minutes = :duration_minutes,
                    total_questions = :total_questions,
                    is_random_questions = :is_random_questions,
                    is_random_options = :is_random_options,
                    max_attempts = :max_attempts,
                    passing_grade = :passing_grade,
                    enable_dual_camera = :enable_dual_camera,
                    enable_ai_proctoring = :enable_ai_proctoring,
                    status = :status
                WHERE id = :id";

        return $this->execute($sql, [
            ':subject_id'           => $examData['subject_id'],
            ':question_bank_id'     => $examData['question_bank_id'] ?: null,
            ':title'                => trim($examData['title']),
            ':description'          => trim($examData['description'] ?? '') ?: null,
            ':start_date'           => $examData['start_date'],
            ':end_date'             => $examData['end_date'],
            ':exam_date'            => $examData['start_date'],
            ':start_time'           => $examData['start_time'],
            ':end_time'             => $examData['end_time'],
            ':duration_minutes'     => (int)$examData['duration_minutes'],
            ':total_questions'      => (int)($examData['total_questions'] ?? 0),
            ':is_random_questions'  => (int)($examData['is_random_questions'] ?? 0),
            ':is_random_options'    => (int)($examData['is_random_options'] ?? 0),
            ':max_attempts'         => max(1, (int)($examData['max_attempts'] ?? 1)),
            ':passing_grade'        => (float)($examData['passing_grade'] ?? 75.00),
            ':enable_dual_camera'   => (int)($examData['enable_dual_camera'] ?? 1),
            ':enable_ai_proctoring' => (int)($examData['enable_ai_proctoring'] ?? 1),
            ':status'               => $examData['status'] ?? 'draft',
            ':id'                   => $id
        ]);
    }

    /**
     * Duplikasi Paket Ujian (1-Klik Salin Paket Ujian dengan Token Unik Baru)
     */
    public function duplicate(int $id): int
    {
        $source = $this->find($id);
        if (!$source) {
            return 0;
        }

        $participants = $this->getParticipants($id);
        $studentIds = array_column($participants, 'student_id');

        $source['title'] = $source['title'] . ' (Salinan)';
        $source['token'] = $this->generateUniqueToken();
        $source['status'] = 'draft';

        return $this->createWithParticipants($source, [], $studentIds);
    }

    /**
     * Ubah Status Ujian (DRAFT, PUBLISHED, ONGOING, COMPLETED, CANCELLED)
     */
    public function setStatus(int $id, string $status): bool
    {
        $validStatuses = ['draft', 'published', 'ongoing', 'completed', 'cancelled'];
        $status = strtolower($status);
        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        return $this->execute("UPDATE `{$this->table}` SET status = :status WHERE id = :id", [
            ':status' => $status,
            ':id'     => $id
        ]);
    }

    /**
     * Regenerasi Token Akses Ujian
     */
    public function regenerateToken(int $id): string
    {
        $newToken = $this->generateUniqueToken();
        $this->execute("UPDATE `{$this->table}` SET token = :token WHERE id = :id", [
            ':token' => $newToken,
            ':id'    => $id
        ]);
        return $newToken;
    }

    /**
     * Alokasikan Siswa dari Kelas Tambahan
     */
    public function assignClasses(int $examId, array $classIds): int
    {
        if (empty($classIds)) {
            return 0;
        }

        $inQuery = implode(',', array_map('intval', $classIds));
        $studentRows = $this->fetchAll("SELECT id FROM `students` WHERE class_id IN ({$inQuery})");
        $count = 0;

        foreach ($studentRows as $sr) {
            $inserted = $this->execute("INSERT IGNORE INTO `exam_participants` (exam_id, student_id, status, created_at) VALUES (:exam_id, :student_id, 'registered', NOW())", [
                ':exam_id'    => $examId,
                ':student_id' => $sr['id']
            ]);
            if ($inserted) $count++;
        }

        return $count;
    }

    /**
     * Hapus Peserta dari Sesi Ujian
     */
    public function removeParticipant(int $examId, int $studentId): bool
    {
        return $this->execute("DELETE FROM `exam_participants` WHERE exam_id = :exam_id AND student_id = :student_id", [
            ':exam_id'    => $examId,
            ':student_id' => $studentId
        ]);
    }

    /**
     * Relasi 1:N: Dapatkan daftar seluruh peserta dalam ujian ini
     */
    public function getParticipants(int $examId): array
    {
        $sql = "SELECT ep.*, s.nisn, u.name as student_name, u.email as student_email,
                       c.class_name, r.final_score, r.is_passed
                FROM `exam_participants` ep
                JOIN `students` s ON s.id = ep.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                LEFT JOIN `results` r ON r.exam_participant_id = ep.id
                WHERE ep.exam_id = :exam_id
                ORDER BY c.class_name ASC, u.name ASC";
        return $this->fetchAll($sql, [':exam_id' => $examId]);
    }

    /**
     * Dapatkan ujian aktif / sedang berlangsung hari ini
     */
    public function getActiveExams(): array
    {
        $sql = "SELECT e.*, s.name as subject_name, u.name as teacher_name
                FROM `{$this->table}` e
                JOIN `subjects` s ON s.id = e.subject_id
                JOIN `teachers` t ON t.id = e.teacher_id
                JOIN `users` u ON u.id = t.user_id
                WHERE e.status IN ('ongoing', 'published')
                ORDER BY e.start_date ASC, e.start_time ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Hapus Paket Ujian Beserta Seluruh Data Terkait (Cascade)
     */
    public function delete(int $id): bool
    {
        return $this->execute("DELETE FROM `{$this->table}` WHERE id = :id", [':id' => $id]);
    }

    /**
     * Dapatkan butir-butir soal yang terkait dengan paket ujian ini
     */
    public function getQuestions(int $examId): array
    {
        $exam = $this->find($examId);
        if (!$exam || empty($exam['question_bank_id'])) {
            return [];
        }
        $sql = "SELECT * FROM `questions` WHERE question_bank_id = :bank_id ORDER BY id ASC";
        return $this->fetchAll($sql, [':bank_id' => (int)$exam['question_bank_id']]);
    }
}
