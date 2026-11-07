<?php
/**
 * SmartExam AI - Student Controller & Exam Engine
 * Mengelola ruang asesmen peserta didik: Dasbor, Daftar Ujian, Persiapan, Engine Pengerjaan, Autosave, dan Hasil.
 * Hak Akses: Khusus Role 'siswa'.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthMiddleware;
use App\Helpers\CsrfHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\SessionHelper;
use App\Models\AnswerModel;
use App\Models\AuditLogModel;
use App\Models\ExamModel;
use App\Models\ExamParticipantModel;
use App\Models\QuestionModel;
use App\Models\ResultModel;
use App\Models\StudentModel;
use App\Models\UserModel;
use App\Services\Proctoring\AIProctoringService;

class StudentController extends Controller
{
    private StudentModel $studentModel;
    private UserModel $userModel;
    private ExamModel $examModel;
    private ExamParticipantModel $participantModel;
    private QuestionModel $questionModel;
    private AnswerModel $answerModel;
    private ResultModel $resultModel;
    private AuditLogModel $auditModel;
    private AIProctoringService $proctoringService;

    public function __construct()
    {
        // Proteksi Hak Akses Khusus Siswa
        AuthMiddleware::requireRole('siswa');

        $this->studentModel = new StudentModel();
        $this->userModel = new UserModel();
        $this->examModel = new ExamModel();
        $this->participantModel = new ExamParticipantModel();
        $this->questionModel = new QuestionModel();
        $this->answerModel = new AnswerModel();
        $this->resultModel = new ResultModel();
        $this->auditModel = new AuditLogModel();
        $this->proctoringService = new AIProctoringService();
    }

    /**
     * Dapatkan Student ID dari user siswa yang sedang login
     */
    private function getLoggedInStudentId(): int
    {
        $userId = AuthMiddleware::id();
        $student = $this->studentModel->findByUserId((int)$userId);
        return (int)($student['id'] ?? 0);
    }

    /**
     * Dasbor Utama Peserta Didik (/student atau /student/dashboard)
     */
    public function index(): void
    {
        $studentId = $this->getLoggedInStudentId();
        $currentUser = AuthMiddleware::user();
        $student = $this->studentModel->getDetails($studentId);

        $allExams = $this->participantModel->getStudentExams($studentId);
        $availableExams = [];
        $ongoingExams = [];
        $completedExams = [];

        foreach ($allExams as $ex) {
            if ($ex['status'] === 'ongoing') {
                $ongoingExams[] = $ex;
            } elseif (in_array($ex['status'], ['submitted', 'graded'], true)) {
                $completedExams[] = $ex;
            } elseif (in_array($ex['exam_status'], ['published', 'ongoing'], true)) {
                $availableExams[] = $ex;
            }
        }

        $this->view('student/dashboard', [
            'pageTitle'      => 'Dasbor Peserta Didik - ' . APP_NAME,
            'user'           => $currentUser,
            'student'        => $student,
            'availableExams' => $availableExams,
            'ongoingExams'   => $ongoingExams,
            'completedExams' => $completedExams,
        ], 'main');
    }

    public function dashboard(): void
    {
        $this->index();
    }

    /**
     * Halaman Daftar Seluruh Ujian Peserta (/student/exams)
     */
    public function exams(): void
    {
        $studentId = $this->getLoggedInStudentId();
        $tab = $this->getQuery('tab', 'available');

        $allExams = $this->participantModel->getStudentExams($studentId);
        $available = [];
        $ongoing = [];
        $completed = [];

        foreach ($allExams as $ex) {
            if ($ex['status'] === 'ongoing') {
                $ongoing[] = $ex;
            } elseif (in_array($ex['status'], ['submitted', 'graded'], true)) {
                $completed[] = $ex;
            } elseif (in_array($ex['exam_status'], ['published', 'ongoing'], true)) {
                $available[] = $ex;
            }
        }

        $this->view('student/exams/index', [
            'pageTitle' => 'Jadwal & Riwayat Ujian - ' . APP_NAME,
            'activeTab' => $tab,
            'available' => $available,
            'ongoing'   => $ongoing,
            'completed' => $completed,
            'user'      => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Halaman Riwayat & Transkrip Nilai Ujian Peserta Didik (/student/history)
     */
    public function history(): void
    {
        $studentId = $this->getLoggedInStudentId();
        $currentUser = AuthMiddleware::user();
        $student = $this->studentModel->getDetails($studentId);

        // Ambil seluruh ujian yang berstatus submitted atau graded
        $completedExams = $this->participantModel->getStudentExams($studentId, 'completed');

        // Kalkulasi ringkasan statistik nilai
        $totalCompleted = count($completedExams);
        $totalPassed = 0;
        $totalRemedial = 0;
        $sumScore = 0.0;
        $highestScore = 0.0;
        $lowestScore = $totalCompleted > 0 ? 100.0 : 0.0;

        foreach ($completedExams as $ex) {
            $score = (float)($ex['final_score'] ?? 0.0);
            $sumScore += $score;
            if ($score > $highestScore) {
                $highestScore = $score;
            }
            if ($score < $lowestScore) {
                $lowestScore = $score;
            }
            if ((int)($ex['is_passed'] ?? 0) === 1) {
                $totalPassed++;
            } else {
                $totalRemedial++;
            }
        }

        $avgScore = $totalCompleted > 0 ? round($sumScore / $totalCompleted, 1) : 0.0;

        $this->view('student/history', [
            'pageTitle'      => 'Riwayat & Nilai Ujian - ' . APP_NAME,
            'user'           => $currentUser,
            'student'        => $student,
            'completedExams' => $completedExams,
            'stats'          => [
                'total_completed' => $totalCompleted,
                'total_passed'    => $totalPassed,
                'total_remedial'  => $totalRemedial,
                'average_score'   => $avgScore,
                'highest_score'   => $highestScore,
                'lowest_score'    => $totalCompleted > 0 ? $lowestScore : 0.0,
            ]
        ], 'main');
    }

    /**
     * Halaman Detail & Persiapan Ujian (/student/exams/{id})
     */
    public function examDetail(int $examId): void
    {
        $studentId = $this->getLoggedInStudentId();
        $participant = $this->participantModel->getParticipantExamInfo($examId, $studentId);

        if (!$participant) {
            SessionHelper::setFlash('danger', 'Anda tidak terdaftar dalam paket ujian ini.');
            $this->redirect('student/exams');
            return;
        }

        // Jika siswa sedang dalam pengerjaan aktif (ongoing), langsung arahkan ke ruang ujian
        if ($participant['status'] === 'ongoing') {
            $this->redirect("student/exams/{$examId}/attempt");
            return;
        }

        // Jika siswa sudah mengumpulkan ujian, arahkan ke lembar hasil
        if (in_array($participant['status'], ['submitted', 'graded'], true)) {
            $this->redirect("student/exams/{$examId}/result");
            return;
        }

        $this->view('student/exams/detail', [
            'pageTitle'   => 'Persiapan Ujian - ' . e($participant['exam_title']),
            'participant' => $participant,
            'user'        => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Halaman Persiapan & Uji Perangkat Dual Camera (/student/exams/{id}/device-check)
     */
    public function deviceCheck(int $examId): void
    {
        $studentId = $this->getLoggedInStudentId();
        $participant = $this->participantModel->getParticipantExamInfo($examId, $studentId);

        if (!$participant) {
            SessionHelper::setFlash('danger', 'Anda tidak terdaftar dalam paket ujian ini.');
            $this->redirect('student/exams');
            return;
        }

        // Jika siswa sudah menyelesaikan ujian, arahkan ke halaman hasil
        if (in_array($participant['status'], ['submitted', 'graded'], true)) {
            $this->redirect("student/exams/{$examId}/result");
            return;
        }

        $this->view('student/exams/device_check', [
            'pageTitle'   => 'Pemeriksaan Perangkat Dual Camera - ' . e($participant['exam_title']),
            'participant' => $participant,
            'user'        => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Handler Memulai Ujian (POST /student/exams/{id}/start)
     */
    public function startExam(int $examId): void
    {
        if (!$this->isPost()) {
            $this->redirect("student/exams/{$examId}");
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token keamanan formulir kedaluwarsa. Silakan muat ulang.');
            $this->redirect("student/exams/{$examId}");
            return;
        }

        $studentId = $this->getLoggedInStudentId();
        $participant = $this->participantModel->getParticipantExamInfo($examId, $studentId);

        if (!$participant) {
            SessionHelper::setFlash('danger', 'Anda tidak terdaftar pada sesi ujian ini.');
            $this->redirect('student/exams');
            return;
        }

        if ($participant['status'] === 'ongoing') {
            $this->redirect("student/exams/{$examId}/attempt");
            return;
        }

        if (in_array($participant['status'], ['submitted', 'graded'], true)) {
            $this->redirect("student/exams/{$examId}/result");
            return;
        }

        // Validasi Jendela Jadwal Pelaksanaan Ujian (Server Time Authority)
        $now = time();
        if (!empty($participant['start_date']) && !empty($participant['exam_start_time'])) {
            $examStart = strtotime("{$participant['start_date']} {$participant['exam_start_time']}");
            if ($examStart !== false && $now < $examStart) {
                SessionHelper::setFlash('warning', 'Ujian belum dibuka. Jadwal mulai: <strong>' . date('d/m/Y H:i', $examStart) . ' WIB</strong>.');
                $this->redirect("student/exams/{$examId}");
                return;
            }
        }
        if (!empty($participant['end_date']) && !empty($participant['exam_end_time'])) {
            $examEnd = strtotime("{$participant['end_date']} {$participant['exam_end_time']}");
            if ($examEnd !== false && $now > $examEnd) {
                SessionHelper::setFlash('danger', 'Jadwal pelaksanaan ujian telah berakhir pada <strong>' . date('d/m/Y H:i', $examEnd) . ' WIB</strong>.');
                $this->redirect("student/exams");
                return;
            }
        }

        // Validasi Token Ujian
        $inputToken = strtoupper(trim($this->getPost('token', '')));
        if (!empty($participant['exam_token']) && $inputToken !== strtoupper($participant['exam_token'])) {
            SessionHelper::setFlash('danger', 'Token ujian yang Anda masukkan salah. Mintalah token resmi kepada pengawas/guru.');
            $this->redirect("student/exams/{$examId}");
            return;
        }

        // Siapkan Urutan Butir Soal (Konsisten & Resilient)
        $rawQuestions = $this->questionModel->findByBank((int)$participant['question_bank_id']);
        if (empty($rawQuestions)) {
            SessionHelper::setFlash('danger', 'Paket soal belum siap atau belum memiliki butir soal.');
            $this->redirect("student/exams/{$examId}");
            return;
        }

        // Filter hanya soal aktif
        $activeQuestions = array_filter($rawQuestions, function($q) {
            return (int)($q['is_active'] ?? 1) === 1;
        });

        $questionIds = array_column($activeQuestions, 'id');

        // Acak urutan butir soal jika opsi acak aktif
        if (!empty($participant['is_random_questions'])) {
            shuffle($questionIds);
        }

        // Inisialisasi Attempt
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $started = $this->participantModel->startAttempt((int)$participant['id'], $questionIds, $ip, $ua);

        if ($started) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'START_EXAM', "Mulai mengerjakan ujian ID: {$examId} ({$participant['exam_title']})");
            $this->redirect("student/exams/{$examId}/attempt");
            return;
        }

        SessionHelper::setFlash('danger', 'Gagal memulai sesi ujian.');
        $this->redirect("student/exams/{$examId}");
    }

    /**
     * Ruang Ujian / Exam Engine Workspace (/student/exams/{id}/attempt)
     */
    public function attempt(int $examId): void
    {
        $studentId = $this->getLoggedInStudentId();
        $participant = $this->participantModel->getParticipantExamInfo($examId, $studentId);

        if (!$participant) {
            SessionHelper::setFlash('danger', 'Sesi ujian tidak ditemukan.');
            $this->redirect('student/exams');
            return;
        }

        if (in_array($participant['status'], ['submitted', 'graded'], true)) {
            $this->redirect("student/exams/{$examId}/result");
            return;
        }

        if ($participant['status'] === 'registered') {
            $this->redirect("student/exams/{$examId}");
            return;
        }

        // Validasi Integritas Waktu Server
        $startTime = strtotime($participant['start_time']);
        $durationSeconds = (int)$participant['duration_minutes'] * 60;
        $now = time();
        $elapsedSeconds = $now - $startTime;
        $remainingSeconds = $durationSeconds - $elapsedSeconds;

        // Jika waktu di server sudah habis -> Auto-submit
        if ($remainingSeconds <= 0) {
            $this->processExamSubmission($participant, true);
            SessionHelper::setFlash('warning', 'Waktu pengerjaan ujian Anda telah habis. Ujian otomatis dikumpulkan oleh sistem.');
            $this->redirect("student/exams/{$examId}/result");
            return;
        }

        // Ambil Urutan Soal Tersimpan
        $questionOrder = json_decode($participant['question_order'] ?? '[]', true);
        if (empty($questionOrder)) {
            $rawQuestions = $this->questionModel->findByBank((int)$participant['question_bank_id']);
            $questionOrder = array_column($rawQuestions, 'id');
        }

        // Ambil data butir soal terurut
        $orderedQuestions = [];
        foreach ($questionOrder as $qId) {
            $q = $this->questionModel->find((int)$qId);
            if ($q && (int)($q['is_active'] ?? 1) === 1) {
                $q['options_decoded'] = QuestionModel::decodeOptions($q['options']);
                $orderedQuestions[] = $q;
            }
        }

        // Ambil jawaban yang telah tersimpan dari database
        $savedAnswers = $this->answerModel->getAnswersByParticipant((int)$participant['id']);

        $this->view('student/exams/attempt', [
            'pageTitle'        => 'Ujian Sedang Berlangsung - ' . e($participant['exam_title']),
            'participant'      => $participant,
            'questions'        => $orderedQuestions,
            'savedAnswers'     => $savedAnswers,
            'remainingSeconds' => max(1, $remainingSeconds),
            'totalSeconds'     => $durationSeconds,
            'user'             => AuthMiddleware::user()
        ], null); // Render mandiri tanpa layout auth (Full Viewport responsive, bebas dari auth-card)
    }

    /**
     * AJAX Endpoint: Simpan Jawaban Siswa secara Asinkron (/student/saveAnswer)
     */
    public function saveAnswerAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        // Validasi Keamanan CSRF Token
        $csrfToken = $this->getPost('csrf_token', '') ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!CsrfHelper::validateToken($csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid atau sesi formulir kedaluwarsa.']);
            exit;
        }

        $studentId     = $this->getLoggedInStudentId();
        $participantId = (int)$this->getPost('participant_id', 0);
        $questionId    = (int)$this->getPost('question_id', 0);
        $answerText    = $this->getPost('answer', null);
        $isDoubtful    = (int)$this->getPost('is_doubtful', 0);

        // Validasi Kepemilikan Sesi (Peserta tidak dapat mengubah lembar jawaban peserta lain)
        $participant = $this->participantModel->find($participantId);
        if (!$participant || (int)$participant['student_id'] !== $studentId || $participant['status'] !== 'ongoing') {
            echo json_encode(['success' => false, 'expired' => true, 'message' => 'Sesi ujian telah selesai atau tidak valid.']);
            exit;
        }

        // Validasi Waktu Server (Peserta tidak dapat memanipulasi waktu ujian)
        $exam = $this->examModel->find((int)$participant['exam_id']);
        $startTime = strtotime($participant['start_time']);
        $durationSeconds = (int)($exam['duration_minutes'] ?? 90) * 60;
        if ((time() - $startTime) > ($durationSeconds + 10)) { // Grace period 10 detik
            echo json_encode(['success' => false, 'expired' => true, 'message' => 'Waktu pengerjaan telah habis.']);
            exit;
        }

        $saved = $this->answerModel->saveAnswer($participantId, $questionId, $answerText, $isDoubtful);

        if ($saved) {
            // Catat Jejak Audit CHANGE_ANSWER
            $this->auditModel->logActivity(
                AuthMiddleware::id(),
                'CHANGE_ANSWER',
                "Peserta ID: {$participantId} menyimpan/mengubah jawaban butir soal ID: {$questionId}"
            );
        }

        echo json_encode([
            'success'   => $saved,
            'saved_at'  => date('H:i:s'),
            'doubtful'  => $isDoubtful
        ]);
        exit;
    }

    /**
     * AJAX Endpoint: Toggle Penanda Ragu-ragu (/student/setDoubtful)
     */
    public function setDoubtfulAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        // Validasi Keamanan CSRF Token
        $csrfToken = $this->getPost('csrf_token', '') ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!CsrfHelper::validateToken($csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
            exit;
        }

        $studentId     = $this->getLoggedInStudentId();
        $participantId = (int)$this->getPost('participant_id', 0);
        $questionId    = (int)$this->getPost('question_id', 0);
        $isDoubtful    = (int)$this->getPost('is_doubtful', 0);

        $participant = $this->participantModel->find($participantId);
        if (!$participant || (int)$participant['student_id'] !== $studentId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $this->answerModel->setDoubtful($participantId, $questionId, $isDoubtful);
        echo json_encode(['success' => true, 'doubtful' => $isDoubtful]);
        exit;
    }

    /**
     * AJAX Endpoint: Catat Insiden Pengawasan AI (/student/proctoringEvent)
     * Menerima event anomali dari Exam Engine klien dengan prinsip Human-in-the-Loop & Privacy
     */
    public function recordProctoringEventAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF kedaluwarsa.']);
            exit;
        }

        $studentId = $this->getLoggedInStudentId();
        $participantId = (int)$this->getPost('participant_id', 0);

        // Validasi Kepemilikan Sesi Partisipan
        $participant = $this->participantModel->find($participantId);
        if (!$participant || (int)$participant['student_id'] !== $studentId || $participant['status'] !== 'ongoing') {
            echo json_encode(['success' => false, 'message' => 'Sesi ujian tidak valid atau telah berakhir.']);
            exit;
        }

        $payload = [
            'event_type'        => trim($this->getPost('event_type', 'ANOMALY')),
            'camera'            => trim($this->getPost('camera', 'cam1')),
            'confidence_score'  => (float)$this->getPost('confidence_score', 85.00),
            'duration'          => (int)$this->getPost('duration', 0),
            'severity'          => trim($this->getPost('severity', 'medium')),
            'description'       => trim($this->getPost('description', '')),
            'snapshot_base64'   => $this->getPost('snapshot_base64', null),
            'face_count'        => isset($_POST['face_count']) ? (int)$_POST['face_count'] : 1,
            'face_ratio'        => isset($_POST['face_ratio']) ? (float)$_POST['face_ratio'] : 0.15,
            'luminance'         => isset($_POST['luminance']) ? (float)$_POST['luminance'] : 50.0,
            'is_disconnected'   => !empty($this->getPost('is_disconnected')),
            'suspicious_objects'=> $this->getPost('suspicious_objects', []),
            'browser_event'     => $this->getPost('browser_event', null),
            'browser_event_desc'=> $this->getPost('browser_event_desc', null),
        ];

        $result = $this->proctoringService->processIncomingAnomaly($participantId, $payload);

        // Jika terdeteksi anomali berat (severity high), kirim alert ke guru pengampu
        if ($payload['severity'] === 'high') {
            try {
                $examInfo = $this->examModel->find((int)$participant['exam_id']);
                if ($examInfo && !empty($examInfo['teacher_id'])) {
                    $teacherData = (new \App\Models\TeacherModel())->find((int)$examInfo['teacher_id']);
                    if ($teacherData && !empty($teacherData['user_id'])) {
                        $studentUser = AuthMiddleware::user();
                        $studentName = $studentUser['name'] ?? 'Peserta';
                        $examTitle = $examInfo['title'] ?? 'Ujian';
                        (new \App\Models\NotificationModel())->createNotification(
                            (int)$teacherData['user_id'],
                            'Peringatan Pengawasan AI',
                            "Terdeteksi anomali tinggi ({$payload['event_type']}) pada peserta {$studentName} saat ujian '{$examTitle}'.",
                            'danger',
                            'bi-shield-exclamation',
                            "teacher/monitoring"
                        );
                    }
                }
            } catch (\Throwable $e) {
                error_log("Failed to notify proctoring alert: " . $e->getMessage());
            }
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Submit Ujian Mandiri oleh Siswa (/student/submitExam)
     */
    public function submitExam(): void
    {
        if (!$this->isPost()) {
            $this->redirect('student/exams');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token keamanan kedaluwarsa.');
            $this->redirect('student/exams');
            return;
        }

        $studentId     = $this->getLoggedInStudentId();
        $participantId = (int)$this->getPost('participant_id', 0);
        $examId        = (int)$this->getPost('exam_id', 0);

        $participant = $this->participantModel->getParticipantExamInfo($examId, $studentId);
        if (!$participant || (int)$participant['id'] !== $participantId) {
            SessionHelper::setFlash('danger', 'Sesi ujian tidak valid.');
            $this->redirect('student/exams');
            return;
        }

        if ($participant['status'] === 'ongoing') {
            $this->processExamSubmission($participant, false);
            SessionHelper::setFlash('success', 'Ujian Anda telah <strong>berhasil dikumpulkan</strong>.');
        }

        $this->redirect("student/exams/{$examId}/result");
    }

    /**
     * Core Engine: Hitung Skor dan Kunci Lembar Jawaban Ujian
     */
    private function processExamSubmission(array $participant, bool $isAutoTimeout = false): void
    {
        $participantId = (int)$participant['id'];
        $bankId = (int)$participant['question_bank_id'];
        $passingGrade = (float)($participant['passing_grade'] ?? 75.00);

        // Ambil semua soal dari bank
        $questions = $this->questionModel->findByBank($bankId);
        $answers = $this->answerModel->getAnswersByParticipant($participantId);

        $totalQuestions = count($questions);
        $correctCount = 0;
        $wrongCount = 0;
        $unattemptedCount = 0;
        $earnedScore = 0.0;
        $maxScore = 0.0;

        foreach ($questions as $q) {
            $qId = (int)$q['id'];
            $weight = (float)$q['score_weight'];
            $maxScore += $weight;

            $userAns = $answers[$qId]['student_answer'] ?? null;

            if ($userAns === null || trim($userAns) === '') {
                $unattemptedCount++;
                $this->answerModel->saveAnswer($participantId, $qId, null, 0, false, 0.0);
            } elseif ($q['question_type'] === 'multiple_choice') {
                // Auto-grading Pilihan Ganda
                $isCorrect = (strtoupper(trim($userAns)) === strtoupper(trim($q['correct_answer'])));
                if ($isCorrect) {
                    $correctCount++;
                    $earnedScore += $weight;
                    $this->answerModel->saveAnswer($participantId, $qId, $userAns, 0, true, $weight);
                } else {
                    $wrongCount++;
                    $this->answerModel->saveAnswer($participantId, $qId, $userAns, 0, false, 0.0);
                }
            } else {
                // Essay: Menunggu Penilaian Guru
                $this->answerModel->saveAnswer($participantId, $qId, $userAns, 0, null, 0.0);
            }
        }

        // Skala Nilai 0 - 100
        $finalScore = $maxScore > 0 ? round(($earnedScore / $maxScore) * 100, 2) : 0.00;
        $isPassed = ($finalScore >= $passingGrade) ? 1 : 0;

        // Update exam_participants
        $this->participantModel->finishAttempt($participantId, $finalScore);

        // Simpan ke tabel results
        $note = $isAutoTimeout ? 'Ujian dikumpulkan otomatis oleh sistem karena waktu habis.' : 'Ujian diselesaikan secara mandiri oleh peserta didik.';
        $this->resultModel->calculateAndSave($participantId, $passingGrade, $note);

        $this->auditModel->logActivity(AuthMiddleware::id(), 'SUBMIT_EXAM', "Mengumpulkan ujian ID: {$participant['exam_id']}, Nilai: {$finalScore}");

        // Trigger Notifikasi In-App
        try {
            $notifModel = new \App\Models\NotificationModel();
            $studentUserId = AuthMiddleware::id();
            $examTitle = $participant['exam_title'] ?? 'Ujian';
            $statusText = $isPassed ? 'LULUS KKM' : 'REMEDIAL';

            // 1. Notifikasi untuk Siswa
            $notifModel->createNotification(
                $studentUserId,
                'Ujian Berhasil Dikumpulkan',
                "Lembar ujian '{$examTitle}' telah tersimpan. Nilai sementara: {$finalScore} ({$statusText}).",
                $isPassed ? 'success' : 'warning',
                'bi-check-circle-fill',
                "student/exams/{$participant['exam_id']}/result"
            );

            // 2. Notifikasi untuk Guru Pengampu
            $examInfo = $this->examModel->find((int)$participant['exam_id']);
            if ($examInfo && !empty($examInfo['teacher_id'])) {
                $teacherData = (new \App\Models\TeacherModel())->find((int)$examInfo['teacher_id']);
                if ($teacherData && !empty($teacherData['user_id'])) {
                    $studentUser = AuthMiddleware::user();
                    $studentName = $studentUser['name'] ?? 'Peserta';
                    $notifModel->createNotification(
                        (int)$teacherData['user_id'],
                        'Pengumpulan Lembar Ujian',
                        "Siswa {$studentName} telah mengumpulkan lembar jawaban '{$examTitle}'.",
                        'info',
                        'bi-person-check-fill',
                        "teacher/results"
                    );
                }
            }
        } catch (\Throwable $e) {
            error_log("Failed to trigger submitExam notification: " . $e->getMessage());
        }
    }

    /**
     * Halaman Hasil & Rekapitulasi Nilai Ujian (/student/exams/{id}/result)
     */
    public function examResult(int $examId): void
    {
        $studentId = $this->getLoggedInStudentId();
        $participant = $this->participantModel->getParticipantExamInfo($examId, $studentId);

        if (!$participant) {
            SessionHelper::setFlash('danger', 'Sesi ujian tidak ditemukan.');
            $this->redirect('student/exams');
            return;
        }

        if ($participant['status'] === 'ongoing') {
            $this->redirect("student/exams/{$examId}/attempt");
            return;
        }

        if ($participant['status'] === 'registered') {
            $this->redirect("student/exams/{$examId}");
            return;
        }

        $result = $this->resultModel->findByParticipantId((int)$participant['id']);

        $this->view('student/exams/result', [
            'pageTitle'   => 'Hasil Ujian - ' . e($participant['exam_title']),
            'participant' => $participant,
            'result'      => $result,
            'user'        => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Halaman Profil Peserta Didik (/student/profile)
     */
    public function profile(): void
    {
        $userId = (int)AuthMiddleware::id();
        $studentId = $this->getLoggedInStudentId();

        $student = $this->studentModel->getDetails($studentId);
        if (!$student) {
            $student = $this->userModel->getUserProfile($userId) ?? AuthMiddleware::user() ?? [];
        }

        $stats = $this->studentModel->getStats($studentId);
        $participations = $studentId > 0 ? $this->studentModel->getExamParticipations($studentId) : [];
        $logs = $this->userModel->getAuditLogs($userId, 10);

        $this->view('student/profile/index', [
            'pageTitle'      => 'Profil Peserta Didik - ' . APP_NAME,
            'student'        => $student,
            'stats'          => $stats,
            'participations' => array_slice($participations, 0, 5),
            'logs'           => $logs,
            'user'           => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Proses Pembaruan Profil Peserta Didik (POST)
     */
    public function updateProfile(): void
    {
        if (!$this->isPost()) {
            $this->redirect('student/profile');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid atau sesi formulir kedaluwarsa.');
            $this->redirect('student/profile');
            return;
        }

        $userId    = (int)AuthMiddleware::id();
        $studentId = $this->getLoggedInStudentId();

        $name     = trim(SanitizeHelper::clean($this->getPost('name', '')));
        $username = strtolower(trim(SanitizeHelper::clean($this->getPost('username', ''))));
        $email    = strtolower(trim(SanitizeHelper::clean($this->getPost('email', ''))));
        $gender   = $this->getPost('gender', 'L') === 'P' ? 'P' : 'L';
        $phone    = trim(SanitizeHelper::clean($this->getPost('phone', '')));
        $address  = trim(SanitizeHelper::clean($this->getPost('address', '')));

        if (empty($name) || empty($username) || empty($email)) {
            SessionHelper::setFlash('warning', 'Nama lengkap, username, dan email wajib diisi.');
            $this->redirect('student/profile');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            SessionHelper::setFlash('danger', 'Format alamat email tidak valid.');
            $this->redirect('student/profile');
            return;
        }

        if ($this->userModel->isUsernameTaken($username, $userId)) {
            SessionHelper::setFlash('danger', "Username <strong>{$username}</strong> sudah dipakai oleh pengguna lain.");
            $this->redirect('student/profile');
            return;
        }

        if ($this->userModel->isEmailTaken($email, $userId)) {
            SessionHelper::setFlash('danger', "Email <strong>{$email}</strong> sudah dipakai oleh akun lain.");
            $this->redirect('student/profile');
            return;
        }

        $success = false;
        if ($studentId > 0) {
            $success = $this->studentModel->updateProfileByStudent($studentId, [
                'name'     => $name,
                'username' => $username,
                'email'    => $email
            ], [
                'gender'  => $gender,
                'phone'   => $phone,
                'address' => $address
            ]);
        } else {
            $success = $this->userModel->updateBasicProfile($userId, $name, $username, $email);
        }

        if ($success) {
            AuthMiddleware::updateSessionUser([
                'name'     => $name,
                'username' => $username,
                'email'    => $email
            ]);
            $this->auditModel->logActivity($userId, 'UPDATE_PROFILE', "Siswa {$name} ({$username}) memperbarui informasi profil.");
            SessionHelper::setFlash('success', 'Data profil Anda berhasil diperbarui.');
        } else {
            SessionHelper::setFlash('danger', 'Gagal memperbarui profil siswa. Silakan coba kembali.');
        }

        $this->redirect('student/profile');
    }

    /**
     * Proses Pembaruan Kata Sandi Peserta Didik (POST)
     */
    public function updatePassword(): void
    {
        if (!$this->isPost()) {
            $this->redirect('student/profile');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('student/profile');
            return;
        }

        $userId          = (int)AuthMiddleware::id();
        $currentPassword = (string)$this->getPost('current_password', '');
        $newPassword     = (string)$this->getPost('new_password', '');
        $confirmPassword = (string)$this->getPost('confirm_password', '');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            SessionHelper::setFlash('warning', 'Semua kolom kata sandi wajib diisi.');
            $this->redirect('student/profile');
            return;
        }

        if (!$this->userModel->verifyPassword($userId, $currentPassword)) {
            SessionHelper::setFlash('danger', 'Kata sandi saat ini yang Anda masukkan salah.');
            $this->redirect('student/profile');
            return;
        }

        if (strlen($newPassword) < 6) {
            SessionHelper::setFlash('danger', 'Kata sandi baru minimal 6 karakter.');
            $this->redirect('student/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            SessionHelper::setFlash('danger', 'Konfirmasi kata sandi baru tidak cocok.');
            $this->redirect('student/profile');
            return;
        }

        $success = $this->userModel->updateUserPassword($userId, $newPassword);

        if ($success) {
            $this->auditModel->logActivity($userId, 'CHANGE_PASSWORD', 'Siswa berhasil memperbarui kata sandi akun.');
            SessionHelper::setFlash('success', 'Kata sandi berhasil diperbarui dengan aman.');
        } else {
            SessionHelper::setFlash('danger', 'Gagal memperbarui kata sandi.');
        }

        $this->redirect('student/profile');
    }
}