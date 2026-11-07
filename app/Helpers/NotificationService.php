<?php
/**
 * SmartExam AI - Unified Multi-Channel Notification Service
 * Mengorkestrasi In-App Notifications, WhatsApp Gateway, Web Push, dan Idempotent Reminders.
 */

namespace App\Helpers;

use App\Models\NotificationModel;
use App\Models\ExamModel;
use App\Core\Database;
use PDO;

class NotificationService
{
    private NotificationModel $notifModel;
    private ExamModel $examModel;
    private ?PDO $db;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
        $this->examModel  = new ExamModel();
        $this->db         = Database::getInstance();
    }

    /**
     * Dapatkan nomor telepon dan info user berdasarkan user_id
     */
    public function getUserPhoneAndProfile(int $userId): ?array
    {
        if (!$this->db) {
            return null;
        }

        $sql = "SELECT u.id, u.name, u.role, u.email,
                       COALESCE(s.phone, t.phone, '') AS phone,
                       c.class_name
                FROM users u
                LEFT JOIN students s ON s.user_id = u.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN teachers t ON t.user_id = u.id
                WHERE u.id = :user_id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Kirim notifikasi multi-channel (In-App + WhatsApp)
     */
    public function send(
        int $userId,
        string $title,
        string $inAppMessage,
        ?string $whatsappMessage = null,
        array $channels = ['in_app', 'whatsapp'],
        string $type = 'info',
        string $icon = 'bi-bell',
        ?string $link = null
    ): array {
        $results = [
            'in_app'   => false,
            'whatsapp' => false,
            'phone'    => null
        ];

        // 1. Channel In-App
        if (in_array('in_app', $channels)) {
            $results['in_app'] = $this->notifModel->createNotification(
                $userId,
                $title,
                $inAppMessage,
                $type,
                $icon,
                $link
            );
        }

        // 2. Channel WhatsApp
        $waEnabled = function_exists('app_setting') ? (bool)app_setting('wa_gateway_enabled', defined('WA_GATEWAY_ENABLED') ? WA_GATEWAY_ENABLED : true) : (defined('WA_GATEWAY_ENABLED') ? WA_GATEWAY_ENABLED : true);
        if (in_array('whatsapp', $channels) && $waEnabled) {
            $userProfile = $this->getUserPhoneAndProfile($userId);
            $phone = $userProfile['phone'] ?? null;
            $results['phone'] = $phone;

            if (!empty($phone)) {
                $textToSend = $whatsappMessage ?: "📢 *{$title}*\n\n{$inAppMessage}";
                $waRes = WhatsAppGateway::sendMessage($phone, $textToSend);
                $results['whatsapp'] = $waRes['success'] ?? false;
                $results['wa_detail'] = $waRes;
            }
        }

        return $results;
    }

    /**
     * Cek apakah pengingat tertentu sudah pernah dikirim (Mencegah Spam / Idempotent)
     */
    public function isReminderAlreadySent(int $examId, int $userId, string $reminderType): bool
    {
        if (!$this->db) {
            return false;
        }

        $sql = "SELECT id FROM exam_reminders 
                WHERE exam_id = :exam_id AND user_id = :user_id AND reminder_type = :type LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':exam_id' => $examId,
            ':user_id' => $userId,
            ':type'    => $reminderType
        ]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Catat log pengiriman pengingat
     */
    public function logReminderSent(
        int $examId,
        int $userId,
        string $reminderType,
        string $channel = 'all',
        string $status = 'sent',
        ?string $phone = null,
        ?string $preview = null
    ): bool {
        if (!$this->db) {
            return false;
        }

        try {
            $sql = "INSERT INTO exam_reminders 
                    (exam_id, user_id, reminder_type, channel, status, recipient_phone, message_preview, sent_at)
                    VALUES (:exam_id, :user_id, :reminder_type, :channel, :status, :phone, :preview, NOW())
                    ON DUPLICATE KEY UPDATE status = :status_dup, sent_at = NOW()";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':exam_id'       => $examId,
                ':user_id'       => $userId,
                ':reminder_type' => $reminderType,
                ':channel'       => $channel,
                ':status'        => $status,
                ':phone'         => $phone,
                ':preview'       => $preview ? substr($preview, 0, 500) : null,
                ':status_dup'    => $status
            ]);
        } catch (\Throwable $e) {
            error_log("Failed to log exam reminder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim Pengingat H-1 untuk suatu Ujian ke Seluruh Peserta Terdaftar
     */
    public function sendExamReminderH1(int $examId): array
    {
        $exam = $this->getExamFullDetails($examId);
        if (!$exam) {
            return ['success' => false, 'message' => 'Ujian tidak ditemukan.'];
        }

        $participants = $this->getExamParticipantsUsers($examId);
        $sentCount = 0;
        $skippedCount = 0;

        foreach ($participants as $p) {
            $userId = (int)$p['user_id'];
            $studentName = $p['student_name'] ?? 'Peserta Didik';
            $phone = $p['phone'] ?? null;

            // Idempotency check: Lewati jika sudah pernah dikirim
            if ($this->isReminderAlreadySent($examId, $userId, 'h_minus_1')) {
                $skippedCount++;
                continue;
            }

            // Pesan In-App
            $inAppTitle = "Pengingat Ujian Besok: {$exam['title']}";
            $inAppMsg = "Besok Anda memiliki jadwal ujian {$exam['title']} ({$exam['subject_name']}) pada pukul {$exam['start_time']} WIB. Siapkan perangkat dan koneksi Anda.";

            // Pesan WhatsApp
            $waMsg = WhatsAppGateway::templateExamReminderH1($studentName, $exam);

            // Eksekusi pengiriman
            $res = $this->send(
                $userId,
                $inAppTitle,
                $inAppMsg,
                $waMsg,
                ['in_app', 'whatsapp'],
                'warning',
                'bi-alarm-fill',
                'student/exams'
            );

            // Catat log
            $this->logReminderSent(
                $examId,
                $userId,
                'h_minus_1',
                $res['whatsapp'] ? 'all' : 'in_app',
                'sent',
                $phone,
                $waMsg
            );

            $sentCount++;
        }

        return [
            'success'   => true,
            'sent'      => $sentCount,
            'skipped'   => $skippedCount,
            'total'     => count($participants)
        ];
    }

    /**
     * Kirim Pengingat 30 Menit Sebelum Mulai ke Seluruh Peserta Terdaftar
     */
    public function sendExamReminder30M(int $examId): array
    {
        $exam = $this->getExamFullDetails($examId);
        if (!$exam) {
            return ['success' => false, 'message' => 'Ujian tidak ditemukan.'];
        }

        $participants = $this->getExamParticipantsUsers($examId);
        $sentCount = 0;
        $skippedCount = 0;

        foreach ($participants as $p) {
            $userId = (int)$p['user_id'];
            $studentName = $p['student_name'] ?? 'Peserta Didik';
            $phone = $p['phone'] ?? null;

            // Idempotency check: Lewati jika sudah pernah dikirim
            if ($this->isReminderAlreadySent($examId, $userId, 'm_minus_30')) {
                $skippedCount++;
                continue;
            }

            // Pesan In-App
            $inAppTitle = "Ujian Dimulai 30 Menit Lagi!";
            $inAppMsg = "Ujian '{$exam['title']}' akan dibuka pukul {$exam['start_time']} WIB. Token masuk: {$exam['token']}. Segera sambungkan kamera dan standby di ruang ujian.";

            // Pesan WhatsApp
            $waMsg = WhatsAppGateway::templateExamReminder30M($studentName, $exam);

            // Eksekusi pengiriman
            $res = $this->send(
                $userId,
                $inAppTitle,
                $inAppMsg,
                $waMsg,
                ['in_app', 'whatsapp'],
                'danger',
                'bi-hourglass-split',
                'student/exams'
            );

            // Catat log
            $this->logReminderSent(
                $examId,
                $userId,
                'm_minus_30',
                $res['whatsapp'] ? 'all' : 'in_app',
                'sent',
                $phone,
                $waMsg
            );

            $sentCount++;
        }

        return [
            'success'   => true,
            'sent'      => $sentCount,
            'skipped'   => $skippedCount,
            'total'     => count($participants)
        ];
    }

    /**
     * Kirim Notifikasi Nilai & Hasil Ujian Resmi Dirilis ke Siswa Tertentu
     */
    public function sendScoreReleasedNotification(
        int $participantId,
        float $score,
        float $kkm,
        bool $isPassed,
        ?string $notes = null
    ): array {
        if (!$this->db) {
            return ['success' => false, 'message' => 'DB offline'];
        }

        $sql = "SELECT ep.id AS participant_id, ep.exam_id,
                       u.id AS user_id, u.name AS student_name,
                       s.phone,
                       e.title AS exam_title, e.passing_grade,
                       sub.name AS subject_name
                FROM exam_participants ep
                JOIN students s ON ep.student_id = s.id
                JOIN users u ON s.user_id = u.id
                JOIN exams e ON ep.exam_id = e.id
                JOIN subjects sub ON e.subject_id = sub.id
                WHERE ep.id = :participant_id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':participant_id' => $participantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['success' => false, 'message' => 'Data partisipasi tidak ditemukan'];
        }

        $userId = (int)$row['user_id'];
        $examId = (int)$row['exam_id'];
        $studentName = $row['student_name'];
        $examTitle = $row['exam_title'];
        $subjectName = $row['subject_name'];
        $phone = $row['phone'];
        $statusText = $isPassed ? 'LULUS KKM' : 'REMEDIAL';

        // Pesan In-App
        $inAppTitle = "Nilai Ujian Dirilis: {$examTitle}";
        $inAppMsg = "Nilai resmi Anda untuk '{$examTitle}' telah disahkan: {$score} (Batas KKM: {$kkm} - {$statusText}).";

        // Pesan WhatsApp
        $waMsg = WhatsAppGateway::templateScoreReleased(
            $studentName,
            $examTitle,
            $subjectName,
            $score,
            $kkm,
            $isPassed,
            $notes
        );

        // Eksekusi pengiriman multi-channel
        $res = $this->send(
            $userId,
            $inAppTitle,
            $inAppMsg,
            $waMsg,
            ['in_app', 'whatsapp'],
            $isPassed ? 'success' : 'warning',
            'bi-award-fill',
            "student/exams/{$examId}/result"
        );

        // Catat di log
        $this->logReminderSent(
            $examId,
            $userId,
            'score_released',
            $res['whatsapp'] ? 'all' : 'in_app',
            'sent',
            $phone,
            $waMsg
        );

        return [
            'success'  => true,
            'user_id'  => $userId,
            'channels' => $res
        ];
    }

    /**
     * Mesin Pengecekan Otomatis Pengingat Jadwal Ujian (Scheduler Engine)
     * Dapat dipanggil oleh Cron Job, CLI, maupun Polling Background
     */
    public function checkAndDispatchUpcomingReminders(): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'DB offline'];
        }

        $summary = [
            'h1_dispatched'  => 0,
            'm30_dispatched' => 0,
            'exams_checked'  => 0
        ];

        // 1. Ambil Ujian Besok (H-1)
        // Tanggal ujian = besok (CURDATE() + INTERVAL 1 DAY)
        $sqlH1 = "SELECT id, title, exam_date, start_time 
                  FROM exams 
                  WHERE exam_date = CURDATE() + INTERVAL 1 DAY 
                    AND status IN ('published', 'scheduled')";
        $stmtH1 = $this->db->query($sqlH1);
        $examsH1 = $stmtH1 ? $stmtH1->fetchAll(PDO::FETCH_ASSOC) : [];

        foreach ($examsH1 as $ex) {
            $res = $this->sendExamReminderH1((int)$ex['id']);
            $summary['h1_dispatched'] += ($res['sent'] ?? 0);
            $summary['exams_checked']++;
        }

        // 2. Ambil Ujian Hari Ini yang Mulai dalam 30 Menit ke Depan
        // Tanggal ujian = hari ini DAN waktu mulai antara SEKARANG s/d SEKARANG + 35 MENIT
        $sqlM30 = "SELECT id, title, exam_date, start_time 
                   FROM exams 
                   WHERE exam_date = CURDATE() 
                     AND start_time BETWEEN CURTIME() AND CURTIME() + INTERVAL 35 MINUTE
                     AND status IN ('published', 'scheduled', 'ongoing')";
        $stmtM30 = $this->db->query($sqlM30);
        $examsM30 = $stmtM30 ? $stmtM30->fetchAll(PDO::FETCH_ASSOC) : [];

        foreach ($examsM30 as $ex) {
            $res = $this->sendExamReminder30M((int)$ex['id']);
            $summary['m30_dispatched'] += ($res['sent'] ?? 0);
            $summary['exams_checked']++;
        }

        return [
            'success' => true,
            'summary' => $summary,
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Helper: Ambil data lengkap ujian beserta nama mapel
     */
    private function getExamFullDetails(int $examId): ?array
    {
        if (!$this->db) return null;

        $sql = "SELECT e.*, s.name AS subject_name, s.code AS subject_code,
                       DATE_FORMAT(e.exam_date, '%d-%m-%Y') AS formatted_date,
                       TIME_FORMAT(e.start_time, '%H:%i') AS start_time,
                       TIME_FORMAT(e.end_time, '%H:%i') AS end_time
                FROM exams e
                JOIN subjects s ON e.subject_id = s.id
                WHERE e.id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $examId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Helper: Ambil peserta ujian beserta user_id dan nomor HP
     */
    private function getExamParticipantsUsers(int $examId): array
    {
        if (!$this->db) return [];

        $sql = "SELECT ep.id AS participant_id, ep.student_id,
                       s.user_id, s.nisn, s.phone,
                       u.name AS student_name, u.email
                FROM exam_participants ep
                JOIN students s ON ep.student_id = s.id
                JOIN users u ON s.user_id = u.id
                WHERE ep.exam_id = :exam_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':exam_id' => $examId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
