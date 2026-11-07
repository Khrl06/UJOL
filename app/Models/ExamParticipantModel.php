<?php
/**
 * SmartExam AI - Exam Participant Model
 * Mengelola sesi pengerjaan ujian peserta didik, inisialisasi attempt, autosave state, dan status kelulusan.
 */

namespace App\Models;

use App\Core\Model;

class ExamParticipantModel extends Model
{
    protected string $table = 'exam_participants';
    protected string $primaryKey = 'id';

    /**
     * Dapatkan detail sesi peserta beserta data ujian dan siswa
     */
    public function getDetails(int $id): ?array
    {
        $sql = "SELECT ep.*, e.title as exam_title, e.duration_minutes, e.start_date, e.end_date, e.start_time as exam_start_time, e.end_time as exam_end_time,
                       e.total_questions, e.passing_grade, e.token as exam_token, e.enable_dual_camera, e.enable_ai_proctoring, e.question_bank_id,
                       s.nisn, u.name as student_name, u.email as student_email,
                       c.class_name, sub.name as subject_name, sub.code as subject_code,
                       r.final_score, r.is_passed, r.correct_answers, r.wrong_answers, r.unattempted
                FROM `{$this->table}` ep
                JOIN `exams` e ON e.id = ep.exam_id
                JOIN `subjects` sub ON sub.id = e.subject_id
                JOIN `students` s ON s.id = ep.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                LEFT JOIN `results` r ON r.exam_participant_id = ep.id
                WHERE ep.id = :id LIMIT 1";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    /**
     * Cari partisipan berdasarkan exam_id dan student_id
     */
    public function findByExamAndStudent(int $examId, int $studentId): ?array
    {
        return $this->fetchOne("SELECT * FROM `{$this->table}` WHERE exam_id = :exam_id AND student_id = :student_id LIMIT 1", [
            ':exam_id'    => $examId,
            ':student_id' => $studentId
        ]);
    }

    /**
     * Dapatkan informasi lengkap partisipan dan paket ujiannya
     */
    public function getParticipantExamInfo(int $examId, int $studentId): ?array
    {
        $sql = "SELECT ep.*, e.title as exam_title, e.description as exam_description, e.duration_minutes,
                       e.start_date, e.end_date, e.start_time as exam_start_time, e.end_time as exam_end_time,
                       e.total_questions, e.passing_grade, e.token as exam_token, e.is_random_questions, e.is_random_options,
                       e.enable_dual_camera, e.enable_ai_proctoring, e.status as exam_status, e.question_bank_id,
                       s.nisn, u.name as student_name, c.class_name,
                       sub.name as subject_name, sub.code as subject_code,
                       r.final_score, r.is_passed
                FROM `{$this->table}` ep
                JOIN `exams` e ON e.id = ep.exam_id
                JOIN `subjects` sub ON sub.id = e.subject_id
                JOIN `students` s ON s.id = ep.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                LEFT JOIN `results` r ON r.exam_participant_id = ep.id
                WHERE ep.exam_id = :exam_id AND ep.student_id = :student_id
                LIMIT 1";
        return $this->fetchOne($sql, [
            ':exam_id'    => $examId,
            ':student_id' => $studentId
        ]);
    }

    /**
     * Dapatkan semua paket ujian yang terdaftar untuk siswa tertentu
     */
    public function getStudentExams(int $studentId, string $statusFilter = ''): array
    {
        $where = "ep.student_id = :student_id";
        $params = [':student_id' => $studentId];

        if ($statusFilter === 'available') {
            $where .= " AND e.status IN ('published', 'ongoing') AND ep.status = 'registered'";
        } elseif ($statusFilter === 'ongoing') {
            $where .= " AND ep.status = 'ongoing'";
        } elseif ($statusFilter === 'completed') {
            $where .= " AND ep.status IN ('submitted', 'graded')";
        }

        $sql = "SELECT ep.*, e.title as exam_title, e.description as exam_description, e.duration_minutes,
                       e.start_date, e.end_date, e.start_time as exam_start_time, e.end_time as exam_end_time,
                       e.total_questions, e.passing_grade, e.enable_dual_camera, e.enable_ai_proctoring, e.status as exam_status,
                       sub.name as subject_name, sub.code as subject_code,
                       u_t.name as teacher_name,
                       r.final_score, r.is_passed
                FROM `{$this->table}` ep
                JOIN `exams` e ON e.id = ep.exam_id
                JOIN `subjects` sub ON sub.id = e.subject_id
                JOIN `teachers` t ON t.id = e.teacher_id
                JOIN `users` u_t ON u_t.id = t.user_id
                LEFT JOIN `results` r ON r.exam_participant_id = ep.id
                WHERE {$where}
                ORDER BY e.start_date DESC, e.start_time DESC";
        return $this->fetchAll($sql, $params);
    }

    /**
     * Inisialisasi Mulai Attempt Pengerjaan Ujian
     */
    public function startAttempt(int $participantId, array $questionOrder, string $ip = '', string $userAgent = ''): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET status = 'ongoing',
                    start_time = NOW(),
                    question_order = :q_order,
                    ip_address = :ip,
                    user_agent = :ua,
                    updated_at = NOW()
                WHERE id = :id";
        return $this->execute($sql, [
            ':q_order' => json_encode($questionOrder),
            ':ip'      => substr($ip, 0, 45),
            ':ua'      => $userAgent,
            ':id'      => $participantId
        ]);
    }

    /**
     * Selesaikan Attempt Ujian (Submit)
     */
    public function finishAttempt(int $participantId, float $finalScore): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET status = 'submitted',
                    submit_time = NOW(),
                    score = :score,
                    updated_at = NOW()
                WHERE id = :id";
        return $this->execute($sql, [
            ':score' => $finalScore,
            ':id'    => $participantId
        ]);
    }

    /**
     * Relasi 1:N: Dapatkan seluruh jawaban yang telah dikirimkan oleh peserta ini
     */
    public function getAnswers(int $participantId): array
    {
        $sql = "SELECT a.*, q.question_text, q.question_type, q.correct_answer, q.score_weight
                FROM `answers` a
                JOIN `questions` q ON q.id = a.question_id
                WHERE a.exam_participant_id = :participant_id
                ORDER BY q.id ASC";
        return $this->fetchAll($sql, [':participant_id' => $participantId]);
    }
}
