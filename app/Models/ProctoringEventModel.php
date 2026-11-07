<?php
/**
 * SmartExam AI - Proctoring Event Model
 * Mengelola catatan insiden pengawasan cerdas (AI) dan relasi ke Peserta Ujian.
 */

namespace App\Models;

use App\Core\Model;

class ProctoringEventModel extends Model
{
    protected string $table = 'proctoring_events';
    protected string $primaryKey = 'id';

    /**
     * Cari semua log insiden untuk suatu peserta ujian
     */
    public function findByParticipant(int $participantId): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE exam_participant_id = :p_id 
                ORDER BY event_time DESC";
        return $this->fetchAll($sql, [':p_id' => $participantId]);
    }

    /**
     * Relasi N:1: Dapatkan data Peserta Ujian yang diawasi
     */
    public function getParticipant(int $eventId): ?array
    {
        $sql = "SELECT ep.*, e.title as exam_title, s.nisn, u.name as student_name, c.class_name
                FROM `exam_participants` ep
                JOIN `exams` e ON e.id = ep.exam_id
                JOIN `students` s ON s.id = ep.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                JOIN `{$this->table}` pe ON pe.exam_participant_id = ep.id
                WHERE pe.id = :event_id LIMIT 1";
        return $this->fetchOne($sql, [':event_id' => $eventId]);
    }

    /**
     * Catat insiden pengawasan baru (Komprehensif dengan dukungan struktur AI baru)
     */
    public function createEvent(array $data): ?int
    {
        $sql = "INSERT INTO `{$this->table}` 
                (exam_participant_id, camera, event_type, confidence_score, duration, status, 
                 severity, description, evidence_reference, snapshot_url, event_time)
                VALUES 
                (:p_id, :camera, :event_type, :confidence_score, :duration, :status, 
                 :severity, :description, :evidence_reference, :snapshot_url, NOW())";

        $evidence = $data['evidence_reference'] ?? $data['snapshot_url'] ?? null;
        $status = in_array(strtoupper($data['status'] ?? ''), ['NORMAL', 'REVIEW', 'REVIEWED', 'DISMISSED'], true) 
            ? strtoupper($data['status']) 
            : 'REVIEW';

        $params = [
            ':p_id'               => (int)$data['exam_participant_id'],
            ':camera'             => in_array($data['camera'] ?? '', ['cam1', 'cam2', 'system']) ? $data['camera'] : 'cam1',
            ':event_type'         => (string)$data['event_type'],
            ':confidence_score'   => (float)($data['confidence_score'] ?? 0.00),
            ':duration'           => (int)($data['duration'] ?? 0),
            ':status'             => $status,
            ':severity'           => in_array($data['severity'] ?? '', ['low', 'medium', 'high']) ? $data['severity'] : 'medium',
            ':description'        => $data['description'] ?? null,
            ':evidence_reference' => $evidence,
            ':snapshot_url'       => $evidence
        ];

        $success = $this->execute($sql, $params);
        return $success ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Update status review insiden oleh Guru / Administrator
     */
    public function updateStatus(int $eventId, string $newStatus, ?string $reviewerNotes = null): bool
    {
        $validStatuses = ['NORMAL', 'REVIEW', 'REVIEWED', 'DISMISSED'];
        $cleanStatus = strtoupper(trim($newStatus));
        if (!in_array($cleanStatus, $validStatuses, true)) {
            return false;
        }

        if (!empty($reviewerNotes)) {
            $sql = "UPDATE `{$this->table}` 
                    SET `status` = :status,
                        `description` = CONCAT(COALESCE(`description`, ''), ' [Review: ', :notes, ']')
                    WHERE `id` = :id";
            return $this->execute($sql, [
                ':status' => $cleanStatus,
                ':notes'  => $reviewerNotes,
                ':id'     => $eventId
            ]);
        }

        $sql = "UPDATE `{$this->table}` SET `status` = :status WHERE `id` = :id";
        return $this->execute($sql, [
            ':status' => $cleanStatus,
            ':id'     => $eventId
        ]);
    }

    /**
     * Dapatkan daftar event terfilter untuk konsol peninjau (Guru/Admin)
     */
    public function getFilteredEvents(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT pe.*, ep.exam_id, ep.student_id, 
                       e.title as exam_title, u.name as student_name, s.nisn, c.class_name
                FROM `{$this->table}` pe
                JOIN `exam_participants` ep ON ep.id = pe.exam_participant_id
                JOIN `exams` e ON e.id = ep.exam_id
                JOIN `students` s ON s.id = ep.student_id
                JOIN `users` u ON u.id = s.user_id
                JOIN `classes` c ON c.id = s.class_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['exam_id'])) {
            $sql .= " AND ep.exam_id = :exam_id";
            $params[':exam_id'] = (int)$filters['exam_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND pe.status = :status";
            $params[':status'] = strtoupper($filters['status']);
        }

        if (!empty($filters['event_type'])) {
            $sql .= " AND pe.event_type = :event_type";
            $params[':event_type'] = $filters['event_type'];
        }

        if (!empty($filters['camera'])) {
            $sql .= " AND pe.camera = :camera";
            $params[':camera'] = $filters['camera'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.name LIKE :search OR s.nisn LIKE :search OR pe.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY pe.event_time DESC LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Hitung total event terfilter
     */
    public function countFilteredEvents(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM `{$this->table}` pe
                JOIN `exam_participants` ep ON ep.id = pe.exam_participant_id
                JOIN `students` s ON s.id = ep.student_id
                JOIN `users` u ON u.id = s.user_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['exam_id'])) {
            $sql .= " AND ep.exam_id = :exam_id";
            $params[':exam_id'] = (int)$filters['exam_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND pe.status = :status";
            $params[':status'] = strtoupper($filters['status']);
        }

        if (!empty($filters['event_type'])) {
            $sql .= " AND pe.event_type = :event_type";
            $params[':event_type'] = $filters['event_type'];
        }

        if (!empty($filters['camera'])) {
            $sql .= " AND pe.camera = :camera";
            $params[':camera'] = $filters['camera'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.name LIKE :search OR s.nisn LIKE :search OR pe.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $row = $this->fetchOne($sql, $params);
        return (int)($row['total'] ?? 0);
    }

    /**
     * Dapatkan statistik ringkasan status review untuk dashboard
     */
    public function getSummaryStats(?int $examId = null): array
    {
        $sql = "SELECT pe.status, COUNT(*) as count 
                FROM `{$this->table}` pe";

        $params = [];
        if ($examId) {
            $sql .= " JOIN `exam_participants` ep ON ep.id = pe.exam_participant_id WHERE ep.exam_id = :exam_id";
            $params[':exam_id'] = $examId;
        }

        $sql .= " GROUP BY pe.status";
        $rows = $this->fetchAll($sql, $params);

        $summary = [
            'NORMAL'    => 0,
            'REVIEW'    => 0,
            'REVIEWED'  => 0,
            'DISMISSED' => 0,
            'total'     => 0
        ];

        foreach ($rows as $r) {
            $st = strtoupper($r['status']);
            if (isset($summary[$st])) {
                $summary[$st] = (int)$r['count'];
            }
            $summary['total'] += (int)$r['count'];
        }

        return $summary;
    }

    /**
     * Catat insiden pengawasan baru (Legacy method)
     */
    public function logEvent(
        int $participantId,
        string $eventType,
        string $severity = 'low',
        ?string $description = null,
        ?string $snapshotUrl = null
    ): bool {
        $id = $this->createEvent([
            'exam_participant_id' => $participantId,
            'camera'              => 'cam1',
            'event_type'          => $eventType,
            'confidence_score'    => 85.00,
            'duration'            => 1,
            'status'              => 'REVIEW',
            'severity'            => $severity,
            'description'         => $description,
            'evidence_reference'  => $snapshotUrl,
            'snapshot_url'        => $snapshotUrl
        ]);
        return $id !== null;
    }

    /**
     * Hitung ringkasan jumlah insiden berdasarkan tingkat keparahan (severity)
     */
    public function countBySeverity(int $participantId): array
    {
        $sql = "SELECT severity, COUNT(*) as count 
                FROM `{$this->table}` 
                WHERE exam_participant_id = :p_id 
                GROUP BY severity";
        $rows = $this->fetchAll($sql, [':p_id' => $participantId]);

        $summary = ['low' => 0, 'medium' => 0, 'high' => 0, 'total' => 0];
        foreach ($rows as $r) {
            $summary[$r['severity']] = (int)$r['count'];
            $summary['total'] += (int)$r['count'];
        }
        return $summary;
    }
}
