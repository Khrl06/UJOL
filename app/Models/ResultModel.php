<?php
/**
 * SmartExam AI - Result Model
 * Mengelola hasil akhir ujian, rekapitulasi nilai, status kelulusan, dan relasi ke Peserta Ujian.
 */

namespace App\Models;

use App\Core\Model;

class ResultModel extends Model
{
    protected string $table = 'results';
    protected string $primaryKey = 'id';

    /**
     * Cari hasil berdasarkan ID peserta ujian
     */
    public function findByParticipantId(int $participantId): ?array
    {
        return $this->fetchOne("SELECT * FROM `{$this->table}` WHERE exam_participant_id = :p_id LIMIT 1", [
            ':p_id' => $participantId
        ]);
    }

    /**
     * Relasi 1:1: Dapatkan data Peserta Ujian dan detail ujian/siswa terkait
     */
    public function getParticipant(int $resultId): ?array
    {
        $sql = "SELECT ep.*, e.title as exam_title, s.nisn, u.name as student_name, c.class_name
                FROM `exam_participants` ep
                JOIN `exams` e ON e.id = ep.exam_id
                JOIN `students` s ON s.id = ep.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                JOIN `{$this->table}` r ON r.exam_participant_id = ep.id
                WHERE r.id = :result_id LIMIT 1";
        return $this->fetchOne($sql, [':result_id' => $resultId]);
    }

    /**
     * Hitung otomatis rekap nilai dari tabel answers dan simpan ke results
     */
    public function calculateAndSave(int $participantId, float $passingGrade = 75.0, ?string $notes = null): ?array
    {
        // Ambil data ujian untuk mengetahui total soal
        $participantModel = new ExamParticipantModel();
        $participant = $participantModel->getDetails($participantId);
        if (!$participant) {
            return null;
        }

        // Ambil semua jawaban peserta
        $answers = $participantModel->getAnswers($participantId);

        $totalQuestions = count($answers);
        $correct = 0;
        $wrong = 0;
        $unattempted = 0;
        $totalScore = 0.0;

        foreach ($answers as $ans) {
            if ($ans['student_answer'] === null || trim($ans['student_answer']) === '') {
                $unattempted++;
            } elseif ((int)$ans['is_correct'] === 1) {
                $correct++;
                $totalScore += (float)$ans['score_obtained'];
            } else {
                $wrong++;
            }
        }

        $isPassed = ($totalScore >= $passingGrade) ? 1 : 0;

        $existing = $this->findByParticipantId($participantId);
        if ($existing) {
            $sql = "UPDATE `{$this->table}` 
                    SET total_questions = :total,
                        correct_answers = :correct,
                        wrong_answers = :wrong,
                        unattempted = :unattempted,
                        final_score = :final_score,
                        is_passed = :is_passed,
                        notes = :notes
                    WHERE id = :id";
            $this->execute($sql, [
                ':total'       => $totalQuestions,
                ':correct'     => $correct,
                ':wrong'       => $wrong,
                ':unattempted' => $unattempted,
                ':final_score' => $totalScore,
                ':is_passed'   => $isPassed,
                ':notes'       => $notes,
                ':id'          => $existing['id']
            ]);
            return $this->find($existing['id']);
        } else {
            $sql = "INSERT INTO `{$this->table}` 
                    (exam_participant_id, total_questions, correct_answers, wrong_answers, unattempted, final_score, is_passed, notes)
                    VALUES (:p_id, :total, :correct, :wrong, :unattempted, :final_score, :is_passed, :notes)";
            $this->execute($sql, [
                ':p_id'        => $participantId,
                ':total'       => $totalQuestions,
                ':correct'     => $correct,
                ':wrong'       => $wrong,
                ':unattempted' => $unattempted,
                ':final_score' => $totalScore,
                ':is_passed'   => $isPassed,
                ':notes'       => $notes
            ]);
            return $this->findByParticipantId($participantId);
        }
    }
}
