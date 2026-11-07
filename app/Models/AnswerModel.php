<?php
/**
 * SmartExam AI - Answer Model
 * Mengelola lembar jawaban siswa, autosave jawaban, penandaan ragu-ragu, dan penilaian.
 */

namespace App\Models;

use App\Core\Model;

class AnswerModel extends Model
{
    protected string $table = 'answers';
    protected string $primaryKey = 'id';

    /**
     * Cari jawaban berdasarkan ID peserta dan ID soal
     */
    public function findByParticipantAndQuestion(int $participantId, int $questionId): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE exam_participant_id = :p_id AND question_id = :q_id 
                LIMIT 1";
        return $this->fetchOne($sql, [
            ':p_id' => $participantId,
            ':q_id' => $questionId
        ]);
    }

    /**
     * Dapatkan semua jawaban peserta dalam format associative map berindeks question_id
     */
    public function getAnswersByParticipant(int $participantId): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE exam_participant_id = :p_id";
        $rows = $this->fetchAll($sql, [':p_id' => $participantId]);
        
        $result = [];
        foreach ($rows as $r) {
            $result[(int)$r['question_id']] = $r;
        }
        return $result;
    }

    /**
     * Simpan atau perbarui jawaban peserta (Upsert logic dengan status ragu-ragu)
     */
    public function saveAnswer(int $participantId, int $questionId, ?string $answer, int $isDoubtful = 0, ?bool $isCorrect = null, float $score = 0.0): bool
    {
        $existing = $this->findByParticipantAndQuestion($participantId, $questionId);

        if ($existing) {
            $sql = "UPDATE `{$this->table}` 
                    SET student_answer = :answer,
                        is_doubtful = :doubtful,
                        is_correct = :is_correct, 
                        score_obtained = :score,
                        updated_at = NOW()
                    WHERE id = :id";
            return $this->execute($sql, [
                ':answer'     => $answer !== null ? trim($answer) : null,
                ':doubtful'   => $isDoubtful ? 1 : 0,
                ':is_correct' => $isCorrect !== null ? (int)$isCorrect : null,
                ':score'      => $score,
                ':id'         => $existing['id']
            ]);
        } else {
            $sql = "INSERT INTO `{$this->table}` 
                    (exam_participant_id, question_id, student_answer, is_doubtful, is_correct, score_obtained, created_at) 
                    VALUES (:p_id, :q_id, :answer, :doubtful, :is_correct, :score, NOW())";
            return $this->execute($sql, [
                ':p_id'       => $participantId,
                ':q_id'       => $questionId,
                ':answer'     => $answer !== null ? trim($answer) : null,
                ':doubtful'   => $isDoubtful ? 1 : 0,
                ':is_correct' => $isCorrect !== null ? (int)$isCorrect : null,
                ':score'      => $score
            ]);
        }
    }

    /**
     * Toggle penanda ragu-ragu (is_doubtful)
     */
    public function setDoubtful(int $participantId, int $questionId, int $isDoubtful): bool
    {
        $existing = $this->findByParticipantAndQuestion($participantId, $questionId);
        if ($existing) {
            return $this->execute("UPDATE `{$this->table}` SET is_doubtful = :d WHERE id = :id", [
                ':d'  => $isDoubtful ? 1 : 0,
                ':id' => $existing['id']
            ]);
        } else {
            return $this->execute("INSERT INTO `{$this->table}` (exam_participant_id, question_id, student_answer, is_doubtful, created_at) VALUES (:p_id, :q_id, NULL, :d, NOW())", [
                ':p_id' => $participantId,
                ':q_id' => $questionId,
                ':d'    => $isDoubtful ? 1 : 0
            ]);
        }
    }
}
