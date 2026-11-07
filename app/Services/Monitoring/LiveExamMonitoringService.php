<?php
/**
 * SmartExam AI - Live Exam Monitoring Service
 * Mengelola komputasi status real-time peserta ujian, progress butir soal,
 * sisa waktu countdown, status kamera 1 & 2, evaluasi status AI, dan kalkulasi level risiko.
 */

namespace App\Services\Monitoring;

use App\Models\ExamModel;
use App\Models\ExamParticipantModel;
use App\Models\ProctoringEventModel;
use App\Services\Proctoring\ProctoringLogger;
use PDO;

class LiveExamMonitoringService
{
    protected ExamModel $examModel;
    protected ExamParticipantModel $participantModel;
    protected ProctoringEventModel $eventModel;
    protected ProctoringLogger $logger;

    public function __construct(
        ?ExamModel $examModel = null,
        ?ExamParticipantModel $participantModel = null,
        ?ProctoringEventModel $eventModel = null,
        ?ProctoringLogger $logger = null
    ) {
        $this->examModel = $examModel ?? new ExamModel();
        $this->participantModel = $participantModel ?? new ExamParticipantModel();
        $this->eventModel = $eventModel ?? new ProctoringEventModel();
        $this->logger = $logger ?? new ProctoringLogger();
    }

    /**
     * Dapatkan data rekapitulasi monitoring langsung untuk suatu paket ujian
     */
    public function getLiveExamData(int $examId): array
    {
        $exam = $this->examModel->getDetails($examId);
        if (!$exam) {
            return [
                'success' => false,
                'message' => 'Paket ujian tidak ditemukan.'
            ];
        }

        $participants = $this->fetchParticipantsWithLiveMetrics($examId, (int)$exam['duration_minutes'], (bool)$exam['enable_dual_camera']);

        // Hitung ringkasan statistik
        $summary = [
            'total'        => count($participants),
            'ongoing'      => 0,
            'submitted'    => 0,
            'registered'   => 0,
            'needs_review' => 0,
            'high_risk'    => 0,
            'medium_risk'  => 0,
            'low_risk'     => 0
        ];

        foreach ($participants as $p) {
            if ($p['exam_status'] === 'ongoing') $summary['ongoing']++;
            elseif ($p['exam_status'] === 'submitted' || $p['exam_status'] === 'graded') $summary['submitted']++;
            else $summary['registered']++;

            if ($p['ai_status'] === 'NEEDS REVIEW') $summary['needs_review']++;

            if ($p['risk_level'] === 'HIGH') $summary['high_risk']++;
            elseif ($p['risk_level'] === 'MEDIUM') $summary['medium_risk']++;
            else $summary['low_risk']++;
        }

        return [
            'success'      => true,
            'exam'         => [
                'id'                 => (int)$exam['id'],
                'title'              => $exam['title'],
                'subject_name'       => $exam['subject_name'] ?? 'Mata Pelajaran',
                'duration_minutes'   => (int)$exam['duration_minutes'],
                'total_questions'    => (int)($exam['bank_question_count'] ?? $exam['total_questions'] ?? 0),
                'enable_dual_camera' => (bool)$exam['enable_dual_camera'],
                'enable_ai_proctor'  => (bool)$exam['enable_ai_proctoring'],
                'status'             => $exam['status']
            ],
            'summary'      => $summary,
            'participants' => $participants,
            'server_time'  => date('Y-m-d H:i:s'),
            'timestamp_ms' => round(microtime(true) * 1000)
        ];
    }

    /**
     * Ambil data mentah peserta dari database dan hitung metrik monitoring
     */
    protected function fetchParticipantsWithLiveMetrics(int $examId, int $durationMinutes, bool $enableDualCamera): array
    {
        $db = \App\Core\Database::getInstance();
        if (!$db) return [];

        // Query gabungan untuk mendapatkan partisipan, progres soal, dan statistik insiden proctoring
        $sql = "SELECT ep.id as participant_id, ep.student_id, ep.status as exam_status, 
                       ep.start_time, ep.submit_time, ep.ip_address, ep.user_agent, ep.supervisor_notes,
                       s.nisn, u.name as student_name, c.class_name,
                       (SELECT COUNT(*) FROM answers a 
                        WHERE a.exam_participant_id = ep.id 
                          AND a.student_answer IS NOT NULL 
                          AND a.student_answer != '') as answered_count,
                       (SELECT COUNT(*) FROM questions q 
                        WHERE q.question_bank_id = (SELECT question_bank_id FROM exams WHERE id = ep.exam_id)) as total_questions,
                       (SELECT COUNT(*) FROM proctoring_events pe WHERE pe.exam_participant_id = ep.id) as event_count,
                       (SELECT COUNT(*) FROM proctoring_events pe WHERE pe.exam_participant_id = ep.id AND pe.status = 'REVIEW') as unreviewed_count,
                       (SELECT COUNT(*) FROM proctoring_events pe WHERE pe.exam_participant_id = ep.id AND pe.status = 'REVIEWED') as reviewed_count,
                       (SELECT COUNT(*) FROM proctoring_events pe WHERE pe.exam_participant_id = ep.id AND pe.event_type = 'CAMERA_DISCONNECTED' AND pe.camera = 'cam1' AND pe.event_time >= NOW() - INTERVAL 90 SECOND) as cam1_disconnect_recent,
                       (SELECT COUNT(*) FROM proctoring_events pe WHERE pe.exam_participant_id = ep.id AND pe.event_type = 'CAMERA_DISCONNECTED' AND pe.camera = 'cam2' AND pe.event_time >= NOW() - INTERVAL 90 SECOND) as cam2_disconnect_recent,
                       (SELECT COUNT(*) FROM proctoring_events pe WHERE pe.exam_participant_id = ep.id AND pe.event_type = 'CAMERA_BLOCKED' AND pe.camera = 'cam1' AND pe.event_time >= NOW() - INTERVAL 90 SECOND) as cam1_blocked_recent,
                       (SELECT MAX(confidence_score) FROM proctoring_events pe WHERE pe.exam_participant_id = ep.id AND pe.status = 'REVIEW') as max_unreviewed_confidence
                FROM exam_participants ep
                JOIN students s ON s.id = ep.student_id
                JOIN users u ON u.id = s.user_id
                JOIN classes c ON c.id = s.class_id
                WHERE ep.exam_id = :exam_id
                ORDER BY 
                    CASE 
                        WHEN ep.status = 'ongoing' THEN 1 
                        WHEN ep.status = 'registered' THEN 2 
                        ELSE 3 
                    END,
                    unreviewed_count DESC,
                    u.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':exam_id' => $examId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $now = time();
        $formattedParticipants = [];

        foreach ($rows as $r) {
            $totalQ = max(1, (int)$r['total_questions']);
            $answered = min($totalQ, (int)$r['answered_count']);
            $progressPct = round(($answered / $totalQ) * 100);

            // 1. Hitung Sisa Waktu Countdown
            $remainingSeconds = 0;
            $remainingFormatted = '00:00:00';
            if ($r['exam_status'] === 'ongoing' && !empty($r['start_time'])) {
                $startTimeEpoch = strtotime($r['start_time']);
                $totalDurationSec = $durationMinutes * 60;
                $elapsed = $now - $startTimeEpoch;
                $remainingSeconds = max(0, $totalDurationSec - $elapsed);
                
                $hrs = floor($remainingSeconds / 3600);
                $mins = floor(($remainingSeconds % 3600) / 60);
                $secs = $remainingSeconds % 60;
                $remainingFormatted = sprintf("%02d:%02d:%02d", $hrs, $mins, $secs);
            } elseif ($r['exam_status'] === 'submitted' || $r['exam_status'] === 'graded') {
                $remainingFormatted = 'SELESAI';
            } else {
                $hrs = floor($durationMinutes / 60);
                $mins = $durationMinutes % 60;
                $remainingFormatted = sprintf("%02d:%02d:00", $hrs, $mins);
            }

            // 2. Tentukan Status Camera 1 (Wajah)
            $cam1Status = 'INACTIVE';
            if ($r['exam_status'] === 'ongoing') {
                if ((int)$r['cam1_disconnect_recent'] > 0) {
                    $cam1Status = 'DISCONNECTED';
                } elseif ((int)$r['cam1_blocked_recent'] > 0) {
                    $cam1Status = 'BLOCKED';
                } else {
                    $cam1Status = 'ACTIVE';
                }
            }

            // 3. Tentukan Status Camera 2 (Meja)
            $cam2Status = 'NOT_REQUIRED';
            if ($enableDualCamera) {
                if ($r['exam_status'] === 'ongoing') {
                    if ((int)$r['cam2_disconnect_recent'] > 0) {
                        $cam2Status = 'DISCONNECTED';
                    } else {
                        $cam2Status = 'ACTIVE';
                    }
                } else {
                    $cam2Status = 'INACTIVE';
                }
            }

            // 4. Tentukan Status AI (Strict: NORMAL, NEEDS REVIEW, REVIEWED)
            $unreviewed = (int)$r['unreviewed_count'];
            $reviewed = (int)$r['reviewed_count'];
            $totalEvents = (int)$r['event_count'];

            if ($unreviewed > 0) {
                $aiStatus = 'NEEDS REVIEW';
            } elseif ($totalEvents > 0 && $reviewed > 0) {
                $aiStatus = 'REVIEWED';
            } else {
                $aiStatus = 'NORMAL';
            }

            // 5. Tentukan Indikator Risiko (Risk: LOW, MEDIUM, HIGH)
            $maxConfidence = (float)($r['max_unreviewed_confidence'] ?? 0.0);
            if ($unreviewed >= 2 || $maxConfidence >= 90.0 || $cam1Status === 'DISCONNECTED' || $cam1Status === 'BLOCKED') {
                $riskLevel = 'HIGH';
            } elseif ($unreviewed === 1 || $maxConfidence >= 75.0) {
                $riskLevel = 'MEDIUM';
            } else {
                $riskLevel = 'LOW';
            }

            $formattedParticipants[] = [
                'participant_id'      => (int)$r['participant_id'],
                'student_id'          => (int)$r['student_id'],
                'student_name'        => $r['student_name'],
                'nisn'                => $r['nisn'],
                'class_name'          => $r['class_name'],
                'exam_status'         => $r['exam_status'],
                'start_time'          => $r['start_time'],
                'ip_address'          => $r['ip_address'] ?? '127.0.0.1',
                'progress'            => [
                    'answered'   => $answered,
                    'total'      => $totalQ,
                    'text'       => "{$answered} / {$totalQ}",
                    'percentage' => $progressPct
                ],
                'time'                => [
                    'remaining_seconds'   => $remainingSeconds,
                    'remaining_formatted' => $remainingFormatted
                ],
                'camera_1'            => $cam1Status,
                'camera_2'            => $cam2Status,
                'ai_status'           => $aiStatus,
                'events_count'        => $totalEvents,
                'unreviewed_count'    => $unreviewed,
                'risk_level'          => $riskLevel,
                'supervisor_notes'    => $r['supervisor_notes'] ?? null
            ];
        }

        return $formattedParticipants;
    }

    /**
     * Dapatkan detail lengkap seorang peserta untuk ditampilkan pada drawer/modal
     */
    public function getParticipantDetail(int $participantId): ?array
    {
        $db = \App\Core\Database::getInstance();
        if (!$db) return null;

        $sql = "SELECT ep.*, s.nisn, u.name as student_name, c.class_name,
                       e.title as exam_title, e.duration_minutes, e.enable_dual_camera, e.enable_ai_proctoring
                FROM exam_participants ep
                JOIN students s ON s.id = ep.student_id
                JOIN users u ON u.id = s.user_id
                JOIN classes c ON c.id = s.class_id
                JOIN exams e ON e.id = ep.exam_id
                WHERE ep.id = :id LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $participantId]);
        $participant = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$participant) return null;

        // Ambil semua proctoring events untuk peserta ini
        $eventSql = "SELECT * FROM proctoring_events 
                     WHERE exam_participant_id = :p_id 
                     ORDER BY event_time DESC";
        $eStmt = $db->prepare($eventSql);
        $eStmt->execute([':p_id' => $participantId]);
        $events = $eStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format events list
        $formattedEvents = [];
        foreach ($events as $evt) {
            $formattedEvents[] = [
                'id'                 => (int)$evt['id'],
                'event_type'         => $evt['event_type'],
                'camera'             => $evt['camera'],
                'confidence_score'   => (float)$evt['confidence_score'],
                'duration'           => (int)$evt['duration'],
                'status'             => $evt['status'],
                'severity'           => $evt['severity'],
                'description'        => $evt['description'],
                'evidence_reference' => $evt['evidence_reference'] ?? $evt['snapshot_url'] ?? null,
                'event_time'         => $evt['event_time'],
                'formatted_time'     => date('H:i:s', strtotime($evt['event_time'])),
                'formatted_date'     => date('d M Y', strtotime($evt['event_time']))
            ];
        }

        return [
            'participant' => [
                'id'                 => (int)$participant['id'],
                'exam_id'            => (int)$participant['exam_id'],
                'student_name'       => $participant['student_name'],
                'nisn'               => $participant['nisn'],
                'class_name'         => $participant['class_name'],
                'exam_title'         => $participant['exam_title'],
                'exam_status'        => $participant['status'],
                'start_time'         => $participant['start_time'],
                'ip_address'         => $participant['ip_address'] ?? '-',
                'supervisor_notes'   => $participant['supervisor_notes'],
                'enable_dual_camera' => (bool)$participant['enable_dual_camera']
            ],
            'events'      => $formattedEvents
        ];
    }

    /**
     * Tambahkan catatan pengawas pada sesi ujian peserta
     */
    public function addSupervisorNote(int $participantId, string $note): bool
    {
        $db = \App\Core\Database::getInstance();
        if (!$db) return false;

        $timestamp = date('d/m/Y H:i:s');
        $formattedNote = "[{$timestamp}] " . trim($note);

        $sql = "UPDATE exam_participants 
                SET supervisor_notes = CONCAT(
                    COALESCE(supervisor_notes, ''),
                    IF(supervisor_notes IS NULL OR supervisor_notes = '', '', '\n'),
                    :new_note
                ),
                updated_at = NOW()
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':new_note' => $formattedNote,
            ':id'       => $participantId
        ]);
    }
}
