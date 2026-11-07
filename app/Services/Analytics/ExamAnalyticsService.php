<?php
/**
 * SmartExam AI - Exam Analytics & Reporting Service
 * Mengelola agregasi metrik evaluasi guru, analisis butir soal,
 * statistik proctoring AI, rekapitulasi laporan, dan ekspor CSV/Print.
 */

namespace App\Services\Analytics;

use App\Core\Database;
use PDO;

class ExamAnalyticsService
{
    protected ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Dapatkan ringkasan KPI Analytics untuk Guru
     */
    public function getTeacherAnalytics(int $teacherId, array $filters = []): array
    {
        if (!$this->db) return [];

        $whereClause = "WHERE e.teacher_id = :teacher_id";
        $params = [':teacher_id' => $teacherId];

        $this->applyFilters($whereClause, $params, $filters);

        // 1. KPI Nilai & Peserta
        $sqlKpi = "SELECT 
                    COUNT(DISTINCT ep.id) as total_participants,
                    COUNT(DISTINCT CASE WHEN ep.status = 'ongoing' THEN ep.id END) as ongoing_count,
                    COUNT(DISTINCT CASE WHEN ep.status IN ('submitted', 'graded') THEN ep.id END) as completed_count,
                    COALESCE(AVG(CASE WHEN ep.status IN ('submitted', 'graded') THEN COALESCE(r.final_score, ep.score, 0) END), 0) as avg_score,
                    COALESCE(MAX(CASE WHEN ep.status IN ('submitted', 'graded') THEN COALESCE(r.final_score, ep.score, 0) END), 0) as max_score,
                    COALESCE(MIN(CASE WHEN ep.status IN ('submitted', 'graded') THEN COALESCE(r.final_score, ep.score, 0) END), 0) as min_score,
                    COUNT(DISTINCT CASE WHEN ep.status IN ('submitted', 'graded') AND COALESCE(r.final_score, ep.score, 0) >= e.passing_grade THEN ep.id END) as passed_count,
                    COUNT(DISTINCT CASE WHEN ep.status IN ('submitted', 'graded') AND COALESCE(r.final_score, ep.score, 0) < e.passing_grade THEN ep.id END) as failed_count
                   FROM exam_participants ep
                   JOIN exams e ON e.id = ep.exam_id
                   JOIN students s ON s.id = ep.student_id
                   JOIN classes c ON c.id = s.class_id
                   LEFT JOIN results r ON r.exam_participant_id = ep.id
                   {$whereClause}";

        $stmtKpi = $this->db->prepare($sqlKpi);
        $stmtKpi->execute($params);
        $kpi = $stmtKpi->fetch(PDO::FETCH_ASSOC) ?: [];

        $completed = (int)($kpi['completed_count'] ?? 0);
        $passed = (int)($kpi['passed_count'] ?? 0);
        $passingRate = $completed > 0 ? round(($passed / $completed) * 100, 1) : 0.0;

        // 2. Hitung Total Proctoring Event
        $sqlEventCount = "SELECT COUNT(pe.id) as total_events
                          FROM proctoring_events pe
                          JOIN exam_participants ep ON ep.id = pe.exam_participant_id
                          JOIN exams e ON e.id = ep.exam_id
                          JOIN students s ON s.id = ep.student_id
                          JOIN classes c ON c.id = s.class_id
                          {$whereClause}";

        $stmtEvents = $this->db->prepare($sqlEventCount);
        $stmtEvents->execute($params);
        $eventRes = $stmtEvents->fetch(PDO::FETCH_ASSOC);
        $totalEvents = (int)($eventRes['total_events'] ?? 0);

        return [
            'total_participants' => (int)($kpi['total_participants'] ?? 0),
            'completed_count'    => $completed,
            'ongoing_count'      => (int)($kpi['ongoing_count'] ?? 0),
            'avg_score'          => round((float)($kpi['avg_score'] ?? 0.0), 2),
            'max_score'          => round((float)($kpi['max_score'] ?? 0.0), 2),
            'min_score'          => round((float)($kpi['min_score'] ?? 0.0), 2),
            'passed_count'       => $passed,
            'failed_count'       => (int)($kpi['failed_count'] ?? 0),
            'passing_rate'       => $passingRate,
            'total_events'       => $totalEvents
        ];
    }

    /**
     * Dapatkan Distribusi Nilai (Grade Brackets)
     */
    public function getScoreDistribution(int $teacherId, array $filters = []): array
    {
        if (!$this->db) return [];

        $whereClause = "WHERE e.teacher_id = :teacher_id AND ep.status IN ('submitted', 'graded')";
        $params = [':teacher_id' => $teacherId];

        $this->applyFilters($whereClause, $params, $filters);

        $sql = "SELECT 
                    SUM(CASE WHEN final_val >= 90 THEN 1 ELSE 0 END) as grade_a,
                    SUM(CASE WHEN final_val >= 80 AND final_val < 90 THEN 1 ELSE 0 END) as grade_b,
                    SUM(CASE WHEN final_val >= 70 AND final_val < 80 THEN 1 ELSE 0 END) as grade_c,
                    SUM(CASE WHEN final_val >= 50 AND final_val < 70 THEN 1 ELSE 0 END) as grade_d,
                    SUM(CASE WHEN final_val < 50 THEN 1 ELSE 0 END) as grade_e,
                    COUNT(*) as total_scored
                FROM (
                    SELECT COALESCE(r.final_score, ep.score, 0) as final_val
                    FROM exam_participants ep
                    JOIN exams e ON e.id = ep.exam_id
                    JOIN students s ON s.id = ep.student_id
                    JOIN classes c ON c.id = s.class_id
                    LEFT JOIN results r ON r.exam_participant_id = ep.id
                    {$whereClause}
                ) as scored_table";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = max(1, (int)($row['total_scored'] ?? 0));
        $actualTotal = (int)($row['total_scored'] ?? 0);

        return [
            'total'   => $actualTotal,
            'ranges'  => [
                [
                    'label'      => '90 - 100 (Sangat Baik / A)',
                    'short'      => '90 - 100',
                    'count'      => (int)($row['grade_a'] ?? 0),
                    'percentage' => round(((int)($row['grade_a'] ?? 0) / $total) * 100, 1),
                    'color'      => 'success'
                ],
                [
                    'label'      => '80 - 89 (Baik / B)',
                    'short'      => '80 - 89',
                    'count'      => (int)($row['grade_b'] ?? 0),
                    'percentage' => round(((int)($row['grade_b'] ?? 0) / $total) * 100, 1),
                    'color'      => 'primary'
                ],
                [
                    'label'      => '70 - 79 (Cukup / C)',
                    'short'      => '70 - 79',
                    'count'      => (int)($row['grade_c'] ?? 0),
                    'percentage' => round(((int)($row['grade_c'] ?? 0) / $total) * 100, 1),
                    'color'      => 'info'
                ],
                [
                    'label'      => '50 - 69 (Kurang / D)',
                    'short'      => '50 - 69',
                    'count'      => (int)($row['grade_d'] ?? 0),
                    'percentage' => round(((int)($row['grade_d'] ?? 0) / $total) * 100, 1),
                    'color'      => 'warning'
                ],
                [
                    'label'      => '< 50 (Sangat Kurang / E)',
                    'short'      => '< 50',
                    'count'      => (int)($row['grade_e'] ?? 0),
                    'percentage' => round(((int)($row['grade_e'] ?? 0) / $total) * 100, 1),
                    'color'      => 'danger'
                ],
            ]
        ];
    }

    /**
     * Analisis Butir Soal: Soal Paling Banyak Salah & Soal Paling Banyak Benar
     */
    public function getItemAnalysis(int $teacherId, array $filters = [], int $limit = 5): array
    {
        if (!$this->db) return ['most_wrong' => [], 'most_correct' => []];

        $whereClause = "WHERE e.teacher_id = :teacher_id";
        $params = [':teacher_id' => $teacherId];

        $this->applyFilters($whereClause, $params, $filters);

        $sql = "SELECT 
                    q.id as question_id,
                    q.question_text,
                    q.question_type,
                    q.question_bank_id,
                    qb.title as bank_title,
                    COUNT(a.id) as total_answers,
                    SUM(CASE WHEN a.is_correct = 1 THEN 1 ELSE 0 END) as correct_count,
                    SUM(CASE WHEN a.is_correct = 0 OR a.is_correct IS NULL THEN 1 ELSE 0 END) as wrong_count
                FROM answers a
                JOIN questions q ON q.id = a.question_id
                JOIN question_banks qb ON qb.id = q.question_bank_id
                JOIN exam_participants ep ON ep.id = a.exam_participant_id
                JOIN exams e ON e.id = ep.exam_id
                JOIN students s ON s.id = ep.student_id
                JOIN classes c ON c.id = s.class_id
                {$whereClause}
                GROUP BY q.id, q.question_text, q.question_type, q.question_bank_id, qb.title
                HAVING total_answers > 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($rows as $r) {
            $total = (int)$r['total_answers'];
            $correct = (int)$r['correct_count'];
            $wrong = (int)$r['wrong_count'];
            $correctPct = round(($correct / $total) * 100, 1);
            $wrongPct = round(($wrong / $total) * 100, 1);

            $cleanText = strip_tags($r['question_text']);
            if (mb_strlen($cleanText) > 120) {
                $cleanText = mb_substr($cleanText, 0, 120) . '...';
            }

            $items[] = [
                'question_id'        => (int)$r['question_id'],
                'question_text'      => $cleanText,
                'question_type'      => $r['question_type'],
                'bank_title'         => $r['bank_title'],
                'total_answers'      => $total,
                'correct_count'      => $correct,
                'wrong_count'        => $wrong,
                'correct_percentage' => $correctPct,
                'wrong_percentage'   => $wrongPct
            ];
        }

        // Urutkan soal paling banyak salah
        $mostWrong = $items;
        usort($mostWrong, fn($a, $b) => $b['wrong_percentage'] <=> $a['wrong_percentage'] ?: $b['wrong_count'] <=> $a['wrong_count']);

        // Urutkan soal paling banyak benar
        $mostCorrect = $items;
        usort($mostCorrect, fn($a, $b) => $b['correct_percentage'] <=> $a['correct_percentage'] ?: $b['correct_count'] <=> $a['correct_count']);

        return [
            'most_wrong'   => array_slice($mostWrong, 0, $limit),
            'most_correct' => array_slice($mostCorrect, 0, $limit)
        ];
    }

    /**
     * Dapatkan Analitik Proctoring Komprehensif
     */
    public function getProctoringAnalytics(int $teacherId, array $filters = []): array
    {
        if (!$this->db) return [];

        $whereClause = "WHERE e.teacher_id = :teacher_id";
        $params = [':teacher_id' => $teacherId];

        $this->applyFilters($whereClause, $params, $filters);

        // 1. Total Event & Unreviewed
        $sqlOverview = "SELECT 
                            COUNT(pe.id) as total_events,
                            SUM(CASE WHEN pe.status = 'REVIEW' THEN 1 ELSE 0 END) as unreviewed_count,
                            SUM(CASE WHEN pe.status = 'REVIEWED' THEN 1 ELSE 0 END) as reviewed_count,
                            SUM(CASE WHEN pe.status = 'DISMISSED' THEN 1 ELSE 0 END) as dismissed_count,
                            SUM(CASE WHEN pe.status = 'NORMAL' THEN 1 ELSE 0 END) as normal_count,
                            COALESCE(AVG(pe.confidence_score), 0) as avg_confidence
                        FROM proctoring_events pe
                        JOIN exam_participants ep ON ep.id = pe.exam_participant_id
                        JOIN exams e ON e.id = ep.exam_id
                        JOIN students s ON s.id = ep.student_id
                        JOIN classes c ON c.id = s.class_id
                        {$whereClause}";

        $stmtOverview = $this->db->prepare($sqlOverview);
        $stmtOverview->execute($params);
        $overview = $stmtOverview->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalEvents = (int)($overview['total_events'] ?? 0);
        $divisor = max(1, $totalEvents);

        // 2. Event Berdasarkan Jenis
        $sqlByType = "SELECT pe.event_type, COUNT(pe.id) as count
                      FROM proctoring_events pe
                      JOIN exam_participants ep ON ep.id = pe.exam_participant_id
                      JOIN exams e ON e.id = ep.exam_id
                      JOIN students s ON s.id = ep.student_id
                      JOIN classes c ON c.id = s.class_id
                      {$whereClause}
                      GROUP BY pe.event_type
                      ORDER BY count DESC";
        $stmtType = $this->db->prepare($sqlByType);
        $stmtType->execute($params);
        $byTypeRaw = $stmtType->fetchAll(PDO::FETCH_ASSOC);

        $byType = [];
        foreach ($byTypeRaw as $t) {
            $byType[] = [
                'type'       => $t['event_type'],
                'count'      => (int)$t['count'],
                'percentage' => round(((int)$t['count'] / $divisor) * 100, 1)
            ];
        }

        // 3. Event Berdasarkan Kamera
        $sqlByCam = "SELECT pe.camera, COUNT(pe.id) as count
                     FROM proctoring_events pe
                     JOIN exam_participants ep ON ep.id = pe.exam_participant_id
                     JOIN exams e ON e.id = ep.exam_id
                     JOIN students s ON s.id = ep.student_id
                     JOIN classes c ON c.id = s.class_id
                     {$whereClause}
                     GROUP BY pe.camera
                     ORDER BY count DESC";
        $stmtCam = $this->db->prepare($sqlByCam);
        $stmtCam->execute($params);
        $byCamRaw = $stmtCam->fetchAll(PDO::FETCH_ASSOC);

        $byCamera = [];
        foreach ($byCamRaw as $c) {
            $camName = match ($c['camera']) {
                'cam1'   => 'Kamera 1 (Wajah)',
                'cam2'   => 'Kamera 2 (Meja Kerja)',
                'system' => 'Sistem (Browser)',
                default  => $c['camera']
            };
            $byCamera[] = [
                'camera'     => $c['camera'],
                'label'      => $camName,
                'count'      => (int)$c['count'],
                'percentage' => round(((int)$c['count'] / $divisor) * 100, 1)
            ];
        }

        // 4. Event Berdasarkan Confidence
        $sqlByConf = "SELECT 
                        SUM(CASE WHEN pe.confidence_score >= 90 THEN 1 ELSE 0 END) as high_conf,
                        SUM(CASE WHEN pe.confidence_score >= 75 AND pe.confidence_score < 90 THEN 1 ELSE 0 END) as med_conf,
                        SUM(CASE WHEN pe.confidence_score < 75 THEN 1 ELSE 0 END) as low_conf
                      FROM proctoring_events pe
                      JOIN exam_participants ep ON ep.id = pe.exam_participant_id
                      JOIN exams e ON e.id = ep.exam_id
                      JOIN students s ON s.id = ep.student_id
                      JOIN classes c ON c.id = s.class_id
                      {$whereClause}";
        $stmtConf = $this->db->prepare($sqlByConf);
        $stmtConf->execute($params);
        $confRaw = $stmtConf->fetch(PDO::FETCH_ASSOC) ?: [];

        $byConfidence = [
            [
                'label'      => 'Tinggi (≥ 90%)',
                'count'      => (int)($confRaw['high_conf'] ?? 0),
                'percentage' => round(((int)($confRaw['high_conf'] ?? 0) / $divisor) * 100, 1),
                'color'      => 'danger'
            ],
            [
                'label'      => 'Sedang (75% - 89%)',
                'count'      => (int)($confRaw['med_conf'] ?? 0),
                'percentage' => round(((int)($confRaw['med_conf'] ?? 0) / $divisor) * 100, 1),
                'color'      => 'warning'
            ],
            [
                'label'      => 'Rendah (< 75%)',
                'count'      => (int)($confRaw['low_conf'] ?? 0),
                'percentage' => round(((int)($confRaw['low_conf'] ?? 0) / $divisor) * 100, 1),
                'color'      => 'info'
            ],
        ];

        // 5. Event Berdasarkan Peserta (Leaderboard Anomali)
        $sqlByStudent = "SELECT 
                            s.id as student_id,
                            s.nisn,
                            u.name as student_name,
                            c.class_name,
                            COUNT(pe.id) as event_count,
                            SUM(CASE WHEN pe.status = 'REVIEW' THEN 1 ELSE 0 END) as unreviewed_count,
                            MAX(pe.confidence_score) as max_confidence
                         FROM proctoring_events pe
                         JOIN exam_participants ep ON ep.id = pe.exam_participant_id
                         JOIN exams e ON e.id = ep.exam_id
                         JOIN students s ON s.id = ep.student_id
                         JOIN users u ON u.id = s.user_id
                         JOIN classes c ON c.id = s.class_id
                         {$whereClause}
                         GROUP BY s.id, s.nisn, u.name, c.class_name
                         ORDER BY event_count DESC, unreviewed_count DESC
                         LIMIT 8";
        $stmtStudent = $this->db->prepare($sqlByStudent);
        $stmtStudent->execute($params);
        $byStudent = $stmtStudent->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_events'     => $totalEvents,
            'unreviewed_count' => (int)($overview['unreviewed_count'] ?? 0),
            'reviewed_count'   => (int)($overview['reviewed_count'] ?? 0),
            'dismissed_count'  => (int)($overview['dismissed_count'] ?? 0),
            'normal_count'     => (int)($overview['normal_count'] ?? 0),
            'avg_confidence'   => round((float)($overview['avg_confidence'] ?? 0.0), 1),
            'by_type'          => $byType,
            'by_camera'        => $byCamera,
            'by_confidence'    => $byConfidence,
            'by_student'       => $byStudent
        ];
    }

    /**
     * Dapatkan Data Rekapitulasi untuk Halaman Laporan
     * Tipe: 'rekap_nilai', 'rekap_peserta', 'rekap_ujian', 'rekap_proctoring'
     */
    public function getReportData(string $reportType, int $teacherId, array $filters = []): array
    {
        if (!$this->db) return [];

        $whereClause = "WHERE e.teacher_id = :teacher_id";
        $params = [':teacher_id' => $teacherId];

        $this->applyFilters($whereClause, $params, $filters);

        return match ($reportType) {
            'rekap_nilai'      => $this->fetchRekapNilai($whereClause, $params),
            'rekap_peserta'    => $this->fetchRekapPeserta($whereClause, $params),
            'rekap_ujian'      => $this->fetchRekapUjian($teacherId, $filters),
            'rekap_proctoring' => $this->fetchRekapProctoring($whereClause, $params),
            default            => []
        };
    }

    protected function fetchRekapNilai(string $whereClause, array $params): array
    {
        $sql = "SELECT 
                    ep.id as participant_id,
                    s.nisn,
                    u.name as student_name,
                    c.class_name,
                    e.title as exam_title,
                    e.passing_grade,
                    COALESCE(r.total_questions, e.total_questions, 0) as total_questions,
                    COALESCE(r.correct_answers, (SELECT COUNT(*) FROM answers a WHERE a.exam_participant_id = ep.id AND a.is_correct = 1), 0) as correct_answers,
                    COALESCE(r.wrong_answers, (SELECT COUNT(*) FROM answers a WHERE a.exam_participant_id = ep.id AND a.is_correct = 0), 0) as wrong_answers,
                    COALESCE(r.unattempted, 0) as unattempted,
                    COALESCE(r.final_score, ep.score, 0) as final_score,
                    CASE 
                        WHEN COALESCE(r.final_score, ep.score, 0) >= e.passing_grade THEN 'LULUS'
                        ELSE 'TIDAK LULUS'
                    END as passing_status
                FROM exam_participants ep
                JOIN exams e ON e.id = ep.exam_id
                JOIN students s ON s.id = ep.student_id
                JOIN users u ON u.id = s.user_id
                JOIN classes c ON c.id = s.class_id
                LEFT JOIN results r ON r.exam_participant_id = ep.id
                {$whereClause}
                ORDER BY c.class_name ASC, u.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function fetchRekapPeserta(string $whereClause, array $params): array
    {
        $sql = "SELECT 
                    ep.id as participant_id,
                    s.nisn,
                    u.name as student_name,
                    c.class_name,
                    e.title as exam_title,
                    ep.status as attempt_status,
                    ep.start_time,
                    ep.submit_time,
                    ep.ip_address,
                    TIMESTAMPDIFF(MINUTE, ep.start_time, ep.submit_time) as duration_spent_minutes
                FROM exam_participants ep
                JOIN exams e ON e.id = ep.exam_id
                JOIN students s ON s.id = ep.student_id
                JOIN users u ON u.id = s.user_id
                JOIN classes c ON c.id = s.class_id
                {$whereClause}
                ORDER BY ep.start_time DESC, u.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function fetchRekapUjian(int $teacherId, array $filters): array
    {
        $whereClause = "WHERE e.teacher_id = :teacher_id";
        $params = [':teacher_id' => $teacherId];

        if (!empty($filters['exam_id'])) {
            $whereClause .= " AND e.id = :exam_id";
            $params[':exam_id'] = (int)$filters['exam_id'];
        }
        if (!empty($filters['subject_id'])) {
            $whereClause .= " AND e.subject_id = :subject_id";
            $params[':subject_id'] = (int)$filters['subject_id'];
        }
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND e.start_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND e.start_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql = "SELECT 
                    e.id as exam_id,
                    e.title as exam_title,
                    sub.name as subject_name,
                    e.total_questions,
                    e.passing_grade,
                    e.duration_minutes,
                    e.status as publish_status,
                    e.start_date,
                    COUNT(DISTINCT ep.id) as total_participants,
                    COUNT(DISTINCT CASE WHEN ep.status IN ('submitted', 'graded') THEN ep.id END) as completed_count,
                    COALESCE(AVG(CASE WHEN ep.status IN ('submitted', 'graded') THEN COALESCE(r.final_score, ep.score, 0) END), 0) as avg_score,
                    COUNT(DISTINCT CASE WHEN ep.status IN ('submitted', 'graded') AND COALESCE(r.final_score, ep.score, 0) >= e.passing_grade THEN ep.id END) as passed_count
                FROM exams e
                JOIN subjects sub ON sub.id = e.subject_id
                LEFT JOIN exam_participants ep ON ep.exam_id = e.id
                LEFT JOIN results r ON r.exam_participant_id = ep.id
                {$whereClause}
                GROUP BY e.id, e.title, sub.name, e.total_questions, e.passing_grade, e.duration_minutes, e.status, e.start_date
                ORDER BY e.start_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $completed = (int)$r['completed_count'];
            $passed = (int)$r['passed_count'];
            $r['passing_percentage'] = $completed > 0 ? round(($passed / $completed) * 100, 1) : 0.0;
            $r['avg_score'] = round((float)$r['avg_score'], 2);
        }
        return $rows;
    }

    protected function fetchRekapProctoring(string $whereClause, array $params): array
    {
        $sql = "SELECT 
                    pe.id as event_id,
                    pe.event_time,
                    s.nisn,
                    u.name as student_name,
                    c.class_name,
                    e.title as exam_title,
                    pe.event_type,
                    pe.camera,
                    pe.confidence_score,
                    pe.duration,
                    pe.status as review_status,
                    pe.description,
                    pe.evidence_reference,
                    pe.snapshot_url
                FROM proctoring_events pe
                JOIN exam_participants ep ON ep.id = pe.exam_participant_id
                JOIN exams e ON e.id = ep.exam_id
                JOIN students s ON s.id = ep.student_id
                JOIN users u ON u.id = s.user_id
                JOIN classes c ON c.id = s.class_id
                {$whereClause}
                ORDER BY pe.event_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Terapkan filter seragam (exam_id, class_id, subject_id, date range)
     */
    protected function applyFilters(string &$whereClause, array &$params, array $filters): void
    {
        if (!empty($filters['exam_id'])) {
            $whereClause .= " AND e.id = :exam_id";
            $params[':exam_id'] = (int)$filters['exam_id'];
        }
        if (!empty($filters['class_id'])) {
            $whereClause .= " AND c.id = :class_id";
            $params[':class_id'] = (int)$filters['class_id'];
        }
        if (!empty($filters['subject_id'])) {
            $whereClause .= " AND e.subject_id = :subject_id";
            $params[':subject_id'] = (int)$filters['subject_id'];
        }
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND e.start_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND e.start_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
    }

    /**
     * Generate Konten CSV dengan UTF-8 BOM untuk Kompatibilitas Excel
     */
    public function generateCsv(string $reportType, array $data): string
    {
        $fp = fopen('php://temp', 'r+');
        // Tambahkan UTF-8 BOM agar Excel menampilkan huruf/tanda baca tanpa garbled
        fputs($fp, "\xEF\xBB\xBF");

        switch ($reportType) {
            case 'rekap_nilai':
                fputcsv($fp, ['No', 'NISN', 'Nama Siswa', 'Kelas', 'Paket Ujian', 'Total Soal', 'Benar', 'Salah', 'Kosong', 'Nilai Akhir', 'KKM', 'Status Kelulusan']);
                $no = 1;
                foreach ($data as $r) {
                    fputcsv($fp, [
                        $no++,
                        $r['nisn'] ?? '',
                        $r['student_name'] ?? '',
                        $r['class_name'] ?? '',
                        $r['exam_title'] ?? '',
                        $r['total_questions'] ?? 0,
                        $r['correct_answers'] ?? 0,
                        $r['wrong_answers'] ?? 0,
                        $r['unattempted'] ?? 0,
                        $r['final_score'] ?? 0,
                        $r['passing_grade'] ?? 75,
                        $r['passing_status'] ?? ''
                    ]);
                }
                break;

            case 'rekap_peserta':
                fputcsv($fp, ['No', 'NISN', 'Nama Siswa', 'Kelas', 'Paket Ujian', 'Status Sesi', 'Waktu Mulai', 'Waktu Selesai', 'Durasi (Menit)', 'IP Address']);
                $no = 1;
                foreach ($data as $r) {
                    fputcsv($fp, [
                        $no++,
                        $r['nisn'] ?? '',
                        $r['student_name'] ?? '',
                        $r['class_name'] ?? '',
                        $r['exam_title'] ?? '',
                        strtoupper($r['attempt_status'] ?? ''),
                        $r['start_time'] ?? '-',
                        $r['submit_time'] ?? '-',
                        $r['duration_spent_minutes'] ?? 0,
                        $r['ip_address'] ?? '-'
                    ]);
                }
                break;

            case 'rekap_ujian':
                fputcsv($fp, ['No', 'ID Ujian', 'Judul Paket Ujian', 'Mata Pelajaran', 'Total Soal', 'KKM', 'Durasi (Menit)', 'Tanggal Ujian', 'Total Peserta', 'Peserta Selesai', 'Rata-Rata Nilai', 'Kelulusan (%)', 'Status Publikasi']);
                $no = 1;
                foreach ($data as $r) {
                    fputcsv($fp, [
                        $no++,
                        $r['exam_id'] ?? '',
                        $r['exam_title'] ?? '',
                        $r['subject_name'] ?? '',
                        $r['total_questions'] ?? 0,
                        $r['passing_grade'] ?? 75,
                        $r['duration_minutes'] ?? 0,
                        $r['start_date'] ?? '',
                        $r['total_participants'] ?? 0,
                        $r['completed_count'] ?? 0,
                        $r['avg_score'] ?? 0,
                        $r['passing_percentage'] ?? 0,
                        strtoupper($r['publish_status'] ?? '')
                    ]);
                }
                break;

            case 'rekap_proctoring':
                fputcsv($fp, ['No', 'ID Event', 'Waktu Kejadian', 'NISN', 'Nama Siswa', 'Kelas', 'Paket Ujian', 'Jenis Anomali', 'Kamera', 'Confidence AI (%)', 'Durasi (dtk)', 'Status Review', 'Keterangan']);
                $no = 1;
                foreach ($data as $r) {
                    fputcsv($fp, [
                        $no++,
                        $r['event_id'] ?? '',
                        $r['event_time'] ?? '',
                        $r['nisn'] ?? '',
                        $r['student_name'] ?? '',
                        $r['class_name'] ?? '',
                        $r['exam_title'] ?? '',
                        $r['event_type'] ?? '',
                        $r['camera'] ?? '',
                        $r['confidence_score'] ?? 0,
                        $r['duration'] ?? 0,
                        $r['review_status'] ?? '',
                        $r['description'] ?? ''
                    ]);
                }
                break;
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        return $csv;
    }
}
