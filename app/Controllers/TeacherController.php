<?php
/**
 * SmartExam AI - Teacher Controller
 * Mengelola fungsi Guru: Bank Soal, Butir Soal, Manajemen Ujian, dan Monitoring.
 * Hak Akses: Khusus Role 'guru'.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthMiddleware;
use App\Helpers\CsrfHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\SessionHelper;
use App\Models\AuditLogModel;
use App\Models\ClassModel;
use App\Models\ExamModel;
use App\Models\ExamParticipantModel;
use App\Models\QuestionBankModel;
use App\Models\QuestionModel;
use App\Models\ResultModel;
use App\Models\StudentModel;
use App\Models\SubjectModel;
use App\Models\TeacherModel;
use App\Models\UserModel;
use App\Models\ProctoringEventModel;
use App\Services\Proctoring\AIProctoringService;
use App\Services\Monitoring\LiveExamMonitoringService;
use App\Services\Analytics\ExamAnalyticsService;
use App\Services\Question\QuestionGeneratorService;

class TeacherController extends Controller
{
    private TeacherModel $teacherModel;
    private UserModel $userModel;
    private QuestionBankModel $bankModel;
    private QuestionModel $questionModel;
    private SubjectModel $subjectModel;
    private ClassModel $classModel;
    private StudentModel $studentModel;
    private ExamModel $examModel;
    private ExamParticipantModel $participantModel;
    private ResultModel $resultModel;
    private AuditLogModel $auditModel;
    private ProctoringEventModel $proctoringModel;
    private AIProctoringService $proctoringService;
    private LiveExamMonitoringService $monitoringService;
    private ExamAnalyticsService $analyticsService;
    private QuestionGeneratorService $questionGeneratorService;

    public function __construct()
    {
        // Proteksi Otorisasi Ketat: Hanya peran 'guru' yang diizinkan
        AuthMiddleware::requireRole('guru');

        $this->teacherModel = new TeacherModel();
        $this->userModel = new UserModel();
        $this->bankModel = new QuestionBankModel();
        $this->questionModel = new QuestionModel();
        $this->subjectModel = new SubjectModel();
        $this->classModel = new ClassModel();
        $this->studentModel = new StudentModel();
        $this->examModel = new ExamModel();
        $this->participantModel = new ExamParticipantModel();
        $this->resultModel = new ResultModel();
        $this->auditModel = new AuditLogModel();
        $this->proctoringModel = new ProctoringEventModel();
        $this->proctoringService = new AIProctoringService();
        $this->monitoringService = new LiveExamMonitoringService();
        $this->analyticsService = new ExamAnalyticsService();
        $this->questionGeneratorService = new QuestionGeneratorService();
    }

    /**
     * Dapatkan Teacher ID dari user guru yang sedang login
     */
    private function getLoggedInTeacherId(): int
    {
        $userId = AuthMiddleware::id();
        $teacher = $this->teacherModel->findByUserId((int)$userId);
        return (int)($teacher['id'] ?? 0);
    }

    /**
     * Tampilan Dasbor Utama Guru
     */
    public function index(): void
    {
        $currentUser = AuthMiddleware::user();
        $teacherId = $this->getLoggedInTeacherId();

        $banks = $this->teacherModel->getQuestionBanks($teacherId);
        $exams = $this->teacherModel->getExams($teacherId);

        $totalQuestions = 0;
        foreach ($banks as $b) {
            $totalQuestions += (int)($b['total_questions'] ?? 0);
        }

        $stats = [
            'total_banks'     => count($banks),
            'total_questions' => $totalQuestions,
            'total_exams'     => count($exams),
            'upcoming_exams'  => count($this->examModel->getActiveExams()),
        ];

        $kpi = $this->analyticsService->getTeacherAnalytics($teacherId);
        $distribution = $this->analyticsService->getScoreDistribution($teacherId);
        $itemAnalysis = $this->analyticsService->getItemAnalysis($teacherId, [], 5);
        $proctoringStats = $this->analyticsService->getProctoringAnalytics($teacherId);

        $this->view('teacher/dashboard', [
            'pageTitle'       => 'Dasbor Guru - ' . APP_NAME,
            'user'            => $currentUser,
            'stats'           => $stats,
            'kpi'             => $kpi,
            'distribution'    => $distribution,
            'itemAnalysis'    => $itemAnalysis,
            'proctoringStats' => $proctoringStats,
            'upcomingExams'   => $exams,
            'recentBanks'     => array_slice($banks, 0, 4),
        ], 'main');
    }

    public function dashboard(): void
    {
        $this->index();
    }

    // =========================================================================
    // MODUL 1: BANK SOAL GURU
    // =========================================================================

    public function banks(): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $search = SanitizeHelper::clean($this->getQuery('q', ''));
        $subjectId = (int)$this->getQuery('subject_id', 0) ?: null;
        $page = (int)$this->getQuery('page', 1);
        $perPage = 8;

        $result = $this->bankModel->paginateByTeacher($teacherId, $search, $subjectId, $page, $perPage);
        $subjects = $this->subjectModel->all();

        $this->view('teacher/banks/index', [
            'pageTitle'  => 'Bank Soal Saya - ' . APP_NAME,
            'banks'      => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'perPage'    => $result['per_page'],
            'totalPages' => $result['total_pages'],
            'search'     => $search,
            'subjectId'  => $subjectId,
            'subjects'   => $subjects,
            'user'       => AuthMiddleware::user(),
        ], 'main');
    }

    public function createBank(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId   = $this->getLoggedInTeacherId();
        $title       = trim(SanitizeHelper::clean($this->getPost('title', '')));
        $subjectId   = (int)$this->getPost('subject_id', 0);
        $gradeLevel  = trim(SanitizeHelper::clean($this->getPost('grade_level', '12')));
        $description = trim(SanitizeHelper::clean($this->getPost('description', '')));
        $isActive    = (int)$this->getPost('is_active', 1);

        if (empty($title) || $subjectId <= 0) {
            SessionHelper::setFlash('warning', 'Judul bank soal dan mata pelajaran wajib diisi.');
            $this->redirect('teacher/banks');
            return;
        }

        $bankId = $this->bankModel->create([
            'teacher_id'  => $teacherId,
            'subject_id'  => $subjectId,
            'grade_level' => $gradeLevel,
            'title'       => $title,
            'description' => $description,
            'is_active'   => $isActive
        ]);

        if ($bankId > 0) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'CREATE_BANK', "Membuat bank soal: {$title}");
            SessionHelper::setFlash('success', "Bank soal <strong>{$title}</strong> berhasil dibuat. Silakan tambahkan butir soal di bawah.");
            $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
            return;
        }

        SessionHelper::setFlash('danger', 'Gagal membuat bank soal.');
        $this->redirect('teacher/banks');
    }

    public function editBank(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId   = $this->getLoggedInTeacherId();
        $bankId      = (int)$this->getPost('id', 0);
        $title       = trim(SanitizeHelper::clean($this->getPost('title', '')));
        $subjectId   = (int)$this->getPost('subject_id', 0);
        $gradeLevel  = trim(SanitizeHelper::clean($this->getPost('grade_level', '12')));
        $description = trim(SanitizeHelper::clean($this->getPost('description', '')));
        $isActive    = (int)$this->getPost('is_active', 1);

        if (!$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak: Anda tidak memiliki wewenang atas bank soal tersebut.');
            $this->redirect('teacher/banks');
            return;
        }

        $this->bankModel->update($bankId, [
            'subject_id'  => $subjectId,
            'grade_level' => $gradeLevel,
            'title'       => $title,
            'description' => $description,
            'is_active'   => $isActive
        ]);

        SessionHelper::setFlash('success', "Bank soal <strong>{$title}</strong> berhasil diperbarui.");
        $this->redirect('teacher/banks');
    }

    public function deleteBank(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $bankId    = (int)$this->getPost('id', 0);

        if (!$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/banks');
            return;
        }

        $this->bankModel->delete($bankId);
        SessionHelper::setFlash('success', 'Bank soal beserta butir soal di dalamnya berhasil dihapus.');
        $this->redirect('teacher/banks');
    }

    // =========================================================================
    // MODUL 2: MANAJEMEN BUTIR SOAL
    // =========================================================================

    public function bankQuestions(): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $bankId = (int)$this->getQuery('bank_id', 0);

        if ($bankId <= 0 || !$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak: Bank soal tidak ditemukan atau bukan milik Anda.');
            $this->redirect('teacher/banks');
            return;
        }

        $bank = $this->bankModel->getDetails($bankId);
        $search = SanitizeHelper::clean($this->getQuery('q', ''));
        $difficulty = SanitizeHelper::clean($this->getQuery('difficulty', ''));
        $page = (int)$this->getQuery('page', 1);
        $perPage = 10;

        $result = $this->questionModel->paginateByBank($bankId, $search, $difficulty, $page, $perPage);
        $totalMaxScore = $this->questionModel->getTotalMaxScore($bankId);

        $this->view('teacher/banks/questions', [
            'pageTitle'     => 'Kelola Butir Soal - ' . e($bank['title']),
            'bank'          => $bank,
            'questions'     => $result['data'],
            'total'         => $result['total'],
            'page'          => $result['page'],
            'perPage'       => $result['per_page'],
            'totalPages'    => $result['total_pages'],
            'search'        => $search,
            'difficulty'    => $difficulty,
            'totalMaxScore' => $totalMaxScore,
            'user'          => AuthMiddleware::user(),
        ], 'main');
    }

    public function createQuestion(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $bankId    = (int)$this->getPost('question_bank_id', 0);

        if (!$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/banks');
            return;
        }

        $type        = $this->getPost('question_type', 'multiple_choice');
        $text        = trim($this->getPost('question_text', ''));
        $weight      = (float)$this->getPost('score_weight', 1.00);
        $diff        = $this->getPost('difficulty', 'sedang');
        $explanation = trim($this->getPost('explanation', ''));
        $isActive    = (int)$this->getPost('is_active', 1);

        if (empty($text)) {
            SessionHelper::setFlash('warning', 'Teks pertanyaan wajib diisi.');
            $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
            return;
        }

        $options = null;
        $correctAnswer = '';

        if ($type === 'multiple_choice') {
            $optA = trim($this->getPost('option_a', ''));
            $optB = trim($this->getPost('option_b', ''));
            $optC = trim($this->getPost('option_c', ''));
            $optD = trim($this->getPost('option_d', ''));
            $optE = trim($this->getPost('option_e', ''));
            $correctAnswer = strtoupper(trim($this->getPost('correct_answer', 'A')));

            if (empty($optA) || empty($optB) || empty($optC) || empty($optD)) {
                SessionHelper::setFlash('warning', 'Pilihan A, B, C, dan D wajib diisi.');
                $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
                return;
            }

            $options = ['A' => $optA, 'B' => $optB, 'C' => $optC, 'D' => $optD, 'E' => $optE ?: '-'];
        } else {
            $correctAnswer = trim($this->getPost('rubric', '')) ?: 'Kesesuaian dengan materi pembahasan.';
        }

        $this->questionModel->create([
            'question_bank_id' => $bankId,
            'question_text'    => $text,
            'question_type'    => $type,
            'options'          => $options,
            'correct_answer'   => $correctAnswer,
            'score_weight'     => $weight > 0 ? $weight : 1.00,
            'difficulty'       => in_array($diff, ['mudah', 'sedang', 'sulit'], true) ? $diff : 'sedang',
            'is_active'        => $isActive,
            'explanation'      => $explanation ?: null
        ]);

        SessionHelper::setFlash('success', 'Butir soal berhasil ditambahkan.');
        $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
    }

    /**
     * Generator Butir Soal & Pilihan Ganda (A-E) Otomatis
     * POST /teacher/generateQuestionsAuto
     */
    public function generateQuestionsAuto(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $bankId    = (int)$this->getPost('question_bank_id', 0);

        if (!$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/banks');
            return;
        }

        $topic      = trim($this->getPost('topic', ''));
        $count      = max(1, min(20, (int)$this->getPost('count', 5)));
        $difficulty = $this->getPost('difficulty', 'campuran');
        $weight     = (float)$this->getPost('score_weight', 2.0);

        $result = $this->questionGeneratorService->generateQuestionsForBank($bankId, $topic, $count, $difficulty, $weight);

        $this->auditModel->logActivity(
            AuthMiddleware::id(),
            'GENERATE_QUESTIONS_AUTO',
            "Guru men-generate otomatis {$result['total_saved']} butir soal PG ke bank ID {$bankId} dengan topik '{$topic}'."
        );

        SessionHelper::setFlash('success', "Berhasil membuat <strong>{$result['total_saved']} butir soal pilihan ganda (A-E)</strong> secara otomatis tanpa perlu mengisi manual!");
        $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
    }

    /**
     * AJAX Endpoint: Otomatisasi Pembuatan Opsi A-E & Kunci dari Pertanyaan Guru
     * POST /teacher/generateOptionsAjax
     */
    public function generateOptionsAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $bankId    = (int)$this->getPost('question_bank_id', 0);

        if ($bankId > 0 && !$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak ke bank soal ini.']);
            exit;
        }

        $bank = $bankId > 0 ? $this->bankModel->getDetails($bankId) : null;
        $subjectName = $bank['subject_name'] ?? null;

        $questionText = trim($this->getPost('question_text', ''));
        $difficulty   = $this->getPost('difficulty', 'sedang');

        if (empty($questionText)) {
            echo json_encode(['success' => false, 'message' => 'Tuliskan teks pertanyaan terlebih dahulu sebelum membuat opsi otomatis.']);
            exit;
        }

        $data = $this->questionGeneratorService->generateOptionsForQuestion($questionText, $subjectName, $difficulty);

        echo json_encode([
            'success'        => true,
            'options'        => $data['options'],
            'correct_answer' => $data['correct_answer'],
            'explanation'    => $data['explanation']
        ]);
        exit;
    }

    /**
     * Auto-Parser Kumpulan Teks Soal Massal Sekaligus (Tanpa Isi Form Manual)
     * POST /teacher/batchImportQuestions
     */
    public function batchImportQuestions(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $bankId    = (int)$this->getPost('question_bank_id', 0);

        if (!$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/banks');
            return;
        }

        $rawText = trim($this->getPost('raw_text', ''));
        if (empty($rawText)) {
            SessionHelper::setFlash('warning', 'Teks kumpulan soal tidak boleh kosong.');
            $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
            return;
        }

        $parsed = $this->questionGeneratorService->parseBatchText($rawText);
        if (empty($parsed)) {
            SessionHelper::setFlash('warning', 'Format teks tidak terdeteksi. Pastikan format memiliki opsi A., B., C., D. dan Kunci.');
            $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
            return;
        }

        $saved = 0;
        foreach ($parsed as $item) {
            $item['question_bank_id'] = $bankId;
            $qId = $this->questionModel->create($item);
            if ($qId > 0) $saved++;
        }

        $this->bankModel->syncTotalQuestions($bankId);

        $this->auditModel->logActivity(
            AuthMiddleware::id(),
            'BATCH_IMPORT_QUESTIONS',
            "Guru mengimpor {$saved} butir soal dari parser teks massal ke bank ID {$bankId}."
        );

        SessionHelper::setFlash('success', "Berhasil mengurai dan menyimpan <strong>{$saved} butir soal</strong> secara otomatis!");
        $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
    }

    public function editQuestion(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId   = $this->getLoggedInTeacherId();
        $questionId  = (int)$this->getPost('id', 0);
        $bankId      = (int)$this->getPost('question_bank_id', 0);

        if (!$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/banks');
            return;
        }

        $type        = $this->getPost('question_type', 'multiple_choice');
        $text        = trim($this->getPost('question_text', ''));
        $weight      = (float)$this->getPost('score_weight', 1.00);
        $diff        = $this->getPost('difficulty', 'sedang');
        $explanation = trim($this->getPost('explanation', ''));
        $isActive    = (int)$this->getPost('is_active', 1);

        $options = null;
        $correctAnswer = '';

        if ($type === 'multiple_choice') {
            $options = [
                'A' => trim($this->getPost('option_a', '')),
                'B' => trim($this->getPost('option_b', '')),
                'C' => trim($this->getPost('option_c', '')),
                'D' => trim($this->getPost('option_d', '')),
                'E' => trim($this->getPost('option_e', '')) ?: '-'
            ];
            $correctAnswer = strtoupper(trim($this->getPost('correct_answer', 'A')));
        } else {
            $correctAnswer = trim($this->getPost('rubric', ''));
        }

        $this->questionModel->update($questionId, [
            'question_bank_id' => $bankId,
            'question_text'    => $text,
            'question_type'    => $type,
            'options'          => $options,
            'correct_answer'   => $correctAnswer,
            'score_weight'     => $weight > 0 ? $weight : 1.00,
            'difficulty'       => in_array($diff, ['mudah', 'sedang', 'sulit'], true) ? $diff : 'sedang',
            'is_active'        => $isActive,
            'explanation'      => $explanation ?: null
        ]);

        SessionHelper::setFlash('success', 'Butir soal berhasil diperbarui.');
        $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
    }

    public function deleteQuestion(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId  = $this->getLoggedInTeacherId();
        $questionId = (int)$this->getPost('id', 0);
        $bankId     = (int)$this->getPost('question_bank_id', 0);

        if (!$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/banks');
            return;
        }

        $this->questionModel->delete($questionId);
        SessionHelper::setFlash('success', 'Butir soal berhasil dihapus.');
        $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
    }

    public function duplicateQuestion(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId  = $this->getLoggedInTeacherId();
        $questionId = (int)$this->getPost('id', 0);
        $bankId     = (int)$this->getPost('question_bank_id', 0);

        if (!$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/banks');
            return;
        }

        $this->questionModel->duplicate($questionId);
        SessionHelper::setFlash('success', 'Soal berhasil diduplikasi.');
        $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
    }

    public function toggleQuestion(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/banks'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/banks');
            return;
        }

        $teacherId  = $this->getLoggedInTeacherId();
        $questionId = (int)$this->getPost('id', 0);
        $bankId     = (int)$this->getPost('question_bank_id', 0);

        if (!$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/banks');
            return;
        }

        $this->questionModel->toggleStatus($questionId);
        SessionHelper::setFlash('info', 'Status keaktifan butir soal berhasil diubah.');
        $this->redirect("teacher/bankQuestions?bank_id={$bankId}");
    }

    // =========================================================================
    // MODUL 3: MANAJEMEN UJIAN (EXAM MANAGEMENT)
    // =========================================================================

    /**
     * Halaman Daftar Ujian Guru (/teacher/exams)
     */
    public function exams(): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $search = SanitizeHelper::clean($this->getQuery('q', ''));
        $status = SanitizeHelper::clean($this->getQuery('status', ''));
        $subjectId = (int)$this->getQuery('subject_id', 0) ?: null;
        $page = (int)$this->getQuery('page', 1);
        $perPage = 8;

        $result = $this->examModel->paginateByTeacher($teacherId, $search, $status, $subjectId, $page, $perPage);
        $subjects = $this->subjectModel->all();

        $this->view('teacher/exams/index', [
            'pageTitle'  => 'Manajemen Ujian - ' . APP_NAME,
            'exams'      => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'perPage'    => $result['per_page'],
            'totalPages' => $result['total_pages'],
            'search'     => $search,
            'status'     => $status,
            'subjectId'  => $subjectId,
            'subjects'   => $subjects,
            'user'       => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Formulir Buat Ujian Baru (/teacher/exams/create)
     */
    public function createExamView(): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $subjects = $this->subjectModel->all();
        $banks = $this->bankModel->getByTeacher($teacherId);
        $classes = $this->classModel->getAllWithCounts();

        $this->view('teacher/exams/create', [
            'pageTitle' => 'Buat Jadwal & Paket Ujian Baru - ' . APP_NAME,
            'subjects'  => $subjects,
            'banks'     => $banks,
            'classes'   => $classes,
            'user'      => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Simpan Ujian Baru (POST)
     */
    public function saveExam(): void
    {
        if (!$this->isPost()) {
            $this->redirect('teacher/exams');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid atau sesi formulir kedaluwarsa.');
            $this->redirect('teacher/exams/create');
            return;
        }

        $teacherId   = $this->getLoggedInTeacherId();
        $title       = trim(SanitizeHelper::clean($this->getPost('title', '')));
        $subjectId   = (int)$this->getPost('subject_id', 0);
        $bankId      = (int)$this->getPost('question_bank_id', 0);
        $description = trim(SanitizeHelper::clean($this->getPost('description', '')));
        $startDate   = trim($this->getPost('start_date', ''));
        $startTime   = trim($this->getPost('start_time', ''));
        $endDate     = trim($this->getPost('end_date', ''));
        $endTime     = trim($this->getPost('end_time', ''));
        $duration    = (int)$this->getPost('duration_minutes', 90);
        $randomQ     = !empty($this->getPost('is_random_questions')) ? 1 : 0;
        $randomOpt   = !empty($this->getPost('is_random_options')) ? 1 : 0;
        $maxAttempts = max(1, (int)$this->getPost('max_attempts', 1));
        $passingGrade= (float)$this->getPost('passing_grade', 75.00);
        $enableDualCam = !empty($this->getPost('enable_dual_camera')) ? 1 : 0;
        $enableAiProc  = !empty($this->getPost('enable_ai_proctoring')) ? 1 : 0;
        $status      = in_array($this->getPost('status', 'draft'), ['draft', 'published'], true) ? $this->getPost('status', 'draft') : 'draft';
        $classIds    = $this->getPost('class_ids', []);

        // 1. Validasi Judul & Mapel
        if (empty($title) || $subjectId <= 0) {
            SessionHelper::setFlash('warning', 'Nama ujian dan mata pelajaran wajib diisi.');
            $this->redirect('teacher/exams/create');
            return;
        }

        // 2. Validasi Bank Soal & Jumlah Soal (Minimal 1 Soal)
        if ($bankId <= 0 || !$this->bankModel->isOwnedByTeacher($bankId, $teacherId)) {
            SessionHelper::setFlash('warning', 'Silakan pilih Bank Soal yang valid.');
            $this->redirect('teacher/exams/create');
            return;
        }

        $totalQuestions = $this->bankModel->getQuestionCount($bankId);
        if ($totalQuestions < 1) {
            SessionHelper::setFlash('danger', 'Bank soal yang dipilih masih kosong! Bank soal harus memiliki <strong>minimal 1 butir soal</strong>.');
            $this->redirect('teacher/exams/create');
            return;
        }

        // 3. Validasi Alokasi Kelas / Peserta (Minimal 1 Peserta)
        if (empty($classIds) || !is_array($classIds)) {
            SessionHelper::setFlash('warning', 'Wajib memilih <strong>minimal satu rombel kelas</strong> peserta ujian.');
            $this->redirect('teacher/exams/create');
            return;
        }

        // 4. Validasi Tanggal & Waktu
        if (empty($startDate) || empty($startTime) || empty($endDate) || empty($endTime)) {
            SessionHelper::setFlash('warning', 'Jadwal tanggal dan waktu mulai/selesai harus diisi lengkap.');
            $this->redirect('teacher/exams/create');
            return;
        }

        $startTimestamp = strtotime("{$startDate} {$startTime}");
        $endTimestamp   = strtotime("{$endDate} {$endTime}");

        if ($startTimestamp === false || $endTimestamp === false) {
            SessionHelper::setFlash('danger', 'Format tanggal atau waktu tidak valid.');
            $this->redirect('teacher/exams/create');
            return;
        }

        if ($endTimestamp <= $startTimestamp) {
            SessionHelper::setFlash('danger', 'Waktu selesai ujian harus <strong>lebih akhir</strong> daripada waktu mulai.');
            $this->redirect('teacher/exams/create');
            return;
        }

        // 5. Validasi Durasi (Harus > 0)
        if ($duration <= 0) {
            SessionHelper::setFlash('warning', 'Durasi pengerjaan ujian harus <strong>lebih dari 0 menit</strong>.');
            $this->redirect('teacher/exams/create');
            return;
        }

        $examId = $this->examModel->createWithParticipants([
            'teacher_id'           => $teacherId,
            'subject_id'           => $subjectId,
            'question_bank_id'     => $bankId,
            'title'                => $title,
            'description'          => $description,
            'start_date'           => $startDate,
            'end_date'             => $endDate,
            'start_time'           => $startTime,
            'end_time'             => $endTime,
            'duration_minutes'     => $duration,
            'total_questions'      => $totalQuestions,
            'is_random_questions'  => $randomQ,
            'is_random_options'    => $randomOpt,
            'max_attempts'         => $maxAttempts,
            'passing_grade'        => $passingGrade,
            'enable_dual_camera'   => $enableDualCam,
            'enable_ai_proctoring' => $enableAiProc,
            'status'               => $status
        ], $classIds);

        if ($examId > 0) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'CREATE_EXAM', "Menjadwalkan paket ujian ID: {$examId} ({$title})");
            SessionHelper::setFlash('success', "Paket ujian <strong>{$title}</strong> berhasil dibuat dan peserta telah dialokasikan.");
            $this->redirect("teacher/exams/{$examId}");
            return;
        }

        SessionHelper::setFlash('danger', 'Gagal membuat paket ujian.');
        $this->redirect('teacher/exams/create');
    }

    /**
     * Halaman Detail Ujian (/teacher/exams/{id})
     */
    public function examDetail(int $id = 0): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $examId = $id ?: (int)$this->getQuery('id', 0);

        if ($examId <= 0 || !$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak: Paket ujian tidak ditemukan atau bukan wewenang Anda.');
            $this->redirect('teacher/exams');
            return;
        }

        $exam = $this->examModel->getDetails($examId);
        $participants = $this->examModel->getParticipants($examId);
        $questions = $this->examModel->getQuestions($examId);
        $classes = $this->classModel->getAllWithCounts();

        $this->view('teacher/exams/detail', [
            'pageTitle'    => 'Detail Ujian - ' . e($exam['title']),
            'exam'         => $exam,
            'participants' => $participants,
            'questions'    => $questions,
            'classes'      => $classes,
            'user'         => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Formulir Edit Ujian (/teacher/exams/{id}/edit)
     */
    public function editExamView(int $id = 0): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $examId = $id ?: (int)$this->getQuery('id', 0);

        if ($examId <= 0 || !$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/exams');
            return;
        }

        $exam = $this->examModel->getDetails($examId);
        $subjects = $this->subjectModel->all();
        $banks = $this->bankModel->getByTeacher($teacherId);

        $this->view('teacher/exams/edit', [
            'pageTitle' => 'Edit Paket Ujian - ' . e($exam['title']),
            'exam'      => $exam,
            'subjects'  => $subjects,
            'banks'     => $banks,
            'user'      => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Perbarui Ujian (POST)
     */
    public function updateExam(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/exams'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/exams');
            return;
        }

        $teacherId   = $this->getLoggedInTeacherId();
        $examId      = (int)$this->getPost('id', 0);

        if (!$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/exams');
            return;
        }

        $title       = trim(SanitizeHelper::clean($this->getPost('title', '')));
        $subjectId   = (int)$this->getPost('subject_id', 0);
        $bankId      = (int)$this->getPost('question_bank_id', 0);
        $description = trim(SanitizeHelper::clean($this->getPost('description', '')));
        $startDate   = trim($this->getPost('start_date', ''));
        $startTime   = trim($this->getPost('start_time', ''));
        $endDate     = trim($this->getPost('end_date', ''));
        $endTime     = trim($this->getPost('end_time', ''));
        $duration    = (int)$this->getPost('duration_minutes', 90);
        $randomQ     = !empty($this->getPost('is_random_questions')) ? 1 : 0;
        $randomOpt   = !empty($this->getPost('is_random_options')) ? 1 : 0;
        $maxAttempts = max(1, (int)$this->getPost('max_attempts', 1));
        $passingGrade= (float)$this->getPost('passing_grade', 75.00);
        $enableDualCam = !empty($this->getPost('enable_dual_camera')) ? 1 : 0;
        $enableAiProc  = !empty($this->getPost('enable_ai_proctoring')) ? 1 : 0;
        $status      = $this->getPost('status', 'draft');

        $totalQuestions = $this->bankModel->getQuestionCount($bankId);

        $this->examModel->updateExam($examId, [
            'subject_id'           => $subjectId,
            'question_bank_id'     => $bankId,
            'title'                => $title,
            'description'          => $description,
            'start_date'           => $startDate,
            'end_date'             => $endDate,
            'start_time'           => $startTime,
            'end_time'             => $endTime,
            'duration_minutes'     => $duration,
            'total_questions'      => $totalQuestions,
            'is_random_questions'  => $randomQ,
            'is_random_options'    => $randomOpt,
            'max_attempts'         => $maxAttempts,
            'passing_grade'        => $passingGrade,
            'enable_dual_camera'   => $enableDualCam,
            'enable_ai_proctoring' => $enableAiProc,
            'status'               => $status
        ]);

        $this->auditModel->logActivity(AuthMiddleware::id(), 'UPDATE_EXAM', "Memperbarui paket ujian ID: {$examId} ({$title})");
        SessionHelper::setFlash('success', "Paket ujian <strong>{$title}</strong> berhasil diperbarui.");
        $this->redirect("teacher/exams/{$examId}");
    }

    /**
     * Hapus Ujian (POST)
     */
    public function deleteExam(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/exams'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/exams');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $examId    = (int)$this->getPost('id', 0);

        if (!$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/exams');
            return;
        }

        $this->examModel->delete($examId);
        $this->auditModel->logActivity(AuthMiddleware::id(), 'DELETE_EXAM', "Menghapus paket ujian ID: {$examId}");
        SessionHelper::setFlash('success', 'Paket ujian berhasil dihapus.');
        $this->redirect('teacher/exams');
    }

    /**
     * Publish Ujian (POST)
     */
    public function publishExam(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/exams'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/exams');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $examId    = (int)$this->getPost('id', 0);

        if (!$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/exams');
            return;
        }

        $this->examModel->setStatus($examId, 'published');

        // Trigger Notifikasi ke Siswa/Peserta
        try {
            $examData = $this->examModel->find($examId);
            $examTitle = $examData['title'] ?? 'Ujian Baru';
            $notifModel = new \App\Models\NotificationModel();
            
            $notifiedCount = $notifModel->notifyExamParticipants(
                $examId,
                'Jadwal Ujian Baru',
                "Ujian '{$examTitle}' telah dipublikasikan dan siap diakses sesuai jadwal.",
                'info',
                'bi-calendar-check-fill',
                'student/exams'
            );

            if ($notifiedCount === 0) {
                $notifModel->notifyRole(
                    'siswa',
                    'Jadwal Ujian Baru',
                    "Paket ujian '{$examTitle}' telah dipublikasikan. Silakan periksa menu Jadwal Ujian Anda.",
                    'info',
                    'bi-calendar-check-fill',
                    'student/exams'
                );
            }
        } catch (\Throwable $e) {
            error_log("Failed to trigger publishExam notification: " . $e->getMessage());
        }

        SessionHelper::setFlash('success', 'Ujian berhasil <strong>DIPUBLIKASIKAN (PUBLISHED)</strong>. Peserta dapat melihat jadwal di dasbor siswa.');
        $this->redirect("teacher/exams/{$examId}");
    }

    /**
     * Unpublish / Revert ke Draft (POST)
     */
    public function unpublishExam(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/exams'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/exams');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $examId    = (int)$this->getPost('id', 0);

        if (!$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/exams');
            return;
        }

        $this->examModel->setStatus($examId, 'draft');
        SessionHelper::setFlash('info', 'Ujian dikembalikan ke status <strong>DRAFT</strong>.');
        $this->redirect("teacher/exams/{$examId}");
    }

    /**
     * Duplikasi Ujian (POST)
     */
    public function duplicateExam(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/exams'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/exams');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $examId    = (int)$this->getPost('id', 0);

        if (!$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/exams');
            return;
        }

        $newExamId = $this->examModel->duplicate($examId);
        if ($newExamId > 0) {
            SessionHelper::setFlash('success', 'Paket ujian berhasil diduplikasi beserta alokasi pesertanya.');
            $this->redirect("teacher/exams/{$newExamId}");
            return;
        }

        SessionHelper::setFlash('danger', 'Gagal menduplikasi ujian.');
        $this->redirect('teacher/exams');
    }

    /**
     * Alokasikan Rombel Kelas Tambahan (POST)
     */
    public function assignParticipants(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/exams'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/exams');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $examId    = (int)$this->getPost('exam_id', 0);
        $classIds  = $this->getPost('class_ids', []);

        if (!$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/exams');
            return;
        }

        $count = $this->examModel->assignClasses($examId, $classIds);
        SessionHelper::setFlash('success', "Berhasil menambahkan {$count} peserta baru dari kelas terpilih.");
        $this->redirect("teacher/exams/{$examId}");
    }

    /**
     * Hapus Peserta dari Sesi Ujian (POST)
     */
    public function removeParticipant(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/exams'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/exams');
            return;
        }

        $teacherId  = $this->getLoggedInTeacherId();
        $examId     = (int)$this->getPost('exam_id', 0);
        $studentId  = (int)$this->getPost('student_id', 0);

        if (!$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/exams');
            return;
        }

        $this->examModel->removeParticipant($examId, $studentId);
        SessionHelper::setFlash('success', 'Peserta berhasil dihapus dari daftar ujian.');
        $this->redirect("teacher/exams/{$examId}");
    }

    /**
     * Regenerasi Token Ujian (POST)
     */
    public function regenerateToken(): void
    {
        if (!$this->isPost()) { $this->redirect('teacher/exams'); return; }
        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/exams');
            return;
        }

        $teacherId = $this->getLoggedInTeacherId();
        $examId    = (int)$this->getPost('id', 0);

        if (!$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Akses ditolak.');
            $this->redirect('teacher/exams');
            return;
        }

        $newToken = $this->examModel->regenerateToken($examId);
        SessionHelper::setFlash('success', "Token baru berhasil dibuat: <strong>{$newToken}</strong>");
        $this->redirect("teacher/exams/{$examId}");
    }

    public function participants(int $examId = 1): void { $this->examDetail($examId); }
    
    /**
     * Tampilan Utama Live Exam Monitoring Dashboard untuk Guru (/teacher/monitoring)
     */
    public function liveMonitoring(int $examId = 0): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $myExams = $this->teacherModel->getExams($teacherId);

        $selectedExamId = $examId > 0 
            ? $examId 
            : (isset($_GET['exam_id']) && $_GET['exam_id'] !== '' ? (int)$_GET['exam_id'] : ($myExams[0]['id'] ?? 0));

        // Validasi Kepemilikan Ujian
        if ($selectedExamId > 0 && !$this->examModel->isOwnedByTeacher($selectedExamId, $teacherId)) {
            SessionHelper::setFlash('danger', 'Anda tidak memiliki hak akses pada paket ujian tersebut.');
            $this->redirect('teacher/exams');
            return;
        }

        $liveData = $selectedExamId > 0 ? $this->monitoringService->getLiveExamData($selectedExamId) : null;

        $this->view('teacher/monitoring/index', [
            'pageTitle'      => 'Live Exam Monitoring & Proctoring - ' . APP_NAME,
            'myExams'        => $myExams,
            'selectedExamId' => $selectedExamId,
            'liveData'       => $liveData,
            'user'           => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * AJAX Endpoint: Dapatkan Data Status Real-Time Peserta (Polling & WebSocket-ready)
     * GET /teacher/exams/{id}/live-status
     */
    public function liveStatusAjax(int $examId): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $teacherId = $this->getLoggedInTeacherId();
        if (!$this->examModel->isOwnedByTeacher($examId, $teacherId)) {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak ke paket ujian ini.']);
            exit;
        }

        $liveData = $this->monitoringService->getLiveExamData($examId);
        echo json_encode($liveData);
        exit;
    }

    /**
     * AJAX Endpoint: Dapatkan Detail Lengkap Peserta & Riwayat Event Proctoring
     * GET /teacher/proctoring/participantDetail?id={participantId}
     */
    public function participantDetailAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $participantId = (int)$this->getQuery('id', 0);
        $teacherId = $this->getLoggedInTeacherId();

        $detail = $this->monitoringService->getParticipantDetail($participantId);
        if (!$detail) {
            echo json_encode(['success' => false, 'message' => 'Data peserta tidak ditemukan.']);
            exit;
        }

        // Verifikasi kepemilikan paket ujian
        if (!$this->examModel->isOwnedByTeacher((int)$detail['participant']['exam_id'], $teacherId)) {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak ke data peserta ini.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $detail]);
        exit;
    }

    /**
     * AJAX Endpoint: Simpan Catatan Pengawas pada Sesi Ujian Peserta
     * POST /teacher/proctoring/addNote
     */
    public function addSupervisorNoteAjax(): void
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

        $participantId = (int)$this->getPost('participant_id', 0);
        $note = trim($this->getPost('note', ''));

        if (empty($note)) {
            echo json_encode(['success' => false, 'message' => 'Catatan tidak boleh kosong.']);
            exit;
        }

        $success = $this->monitoringService->addSupervisorNote($participantId, $note);
        echo json_encode(['success' => $success, 'message' => $success ? 'Catatan pengawas berhasil disimpan.' : 'Gagal menyimpan catatan.']);
        exit;
    }

    /**
     * Update Status Review Insiden oleh Guru (Mendukung Web Form & AJAX)
     */
    public function updateProctoringStatus(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['is_ajax']);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
        }

        if (!$this->isPost()) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
                exit;
            }
            $this->redirect('teacher/monitoring');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
                exit;
            }
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/monitoring');
            return;
        }

        $eventId = (int)$this->getPost('event_id', 0);
        $newStatus = strtoupper(trim($this->getPost('status', 'REVIEW')));
        $notes = trim($this->getPost('notes', ''));

        $success = $this->proctoringService->updateEventStatus($eventId, $newStatus, $notes);
        if ($success) {
            $this->auditModel->logActivity(
                AuthMiddleware::id(),
                'PROCTORING_REVIEW',
                "Guru meninjau insiden proctoring #{$eventId} menjadi status {$newStatus}"
            );
            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => "Insiden #{$eventId} berhasil diubah statusnya menjadi {$newStatus}."]);
                exit;
            }
            SessionHelper::setFlash('success', "Insiden #{$eventId} berhasil ditinjau: status diubah menjadi <strong>{$newStatus}</strong>.");
        } else {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status insiden proctoring.']);
                exit;
            }
            SessionHelper::setFlash('danger', "Gagal memperbarui status insiden proctoring.");
        }

        $redirectUrl = $this->getPost('redirect_url', 'teacher/monitoring');
        $this->redirect($redirectUrl);
    }

    /**
     * Dasbor Analytics Guru (/teacher/analytics)
     * Menyajikan 9 metrik utama (KPI, distribusi nilai, analisis butir soal, proctoring stats)
     */
    public function analytics(): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $myExams = $this->teacherModel->getExams($teacherId);
        $classes = $this->classModel->all();
        $subjects = $this->subjectModel->all();

        $filters = [
            'exam_id'    => !empty($_GET['exam_id']) ? (int)$_GET['exam_id'] : null,
            'class_id'   => !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null,
            'subject_id' => !empty($_GET['subject_id']) ? (int)$_GET['subject_id'] : null,
            'date_from'  => !empty($_GET['date_from']) ? trim($_GET['date_from']) : null,
            'date_to'    => !empty($_GET['date_to']) ? trim($_GET['date_to']) : null,
        ];

        $kpi = $this->analyticsService->getTeacherAnalytics($teacherId, $filters);
        $distribution = $this->analyticsService->getScoreDistribution($teacherId, $filters);
        $itemAnalysis = $this->analyticsService->getItemAnalysis($teacherId, $filters, 5);
        $proctoringStats = $this->analyticsService->getProctoringAnalytics($teacherId, $filters);

        $this->view('teacher/analytics/index', [
            'pageTitle'       => 'Analytics & Evaluasi Ujian - ' . APP_NAME,
            'myExams'         => $myExams,
            'classes'         => $classes,
            'subjects'        => $subjects,
            'filters'         => $filters,
            'kpi'             => $kpi,
            'distribution'    => $distribution,
            'itemAnalysis'    => $itemAnalysis,
            'proctoringStats' => $proctoringStats,
            'user'            => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Alias Hasil & Koreksi -> Laporan Rekap Nilai (/teacher/results)
     */
    public function results(int $examId = 0): void
    {
        if ($examId > 0) {
            $this->redirect('teacher/reports?type=rekap_nilai&exam_id=' . $examId);
            return;
        }
        $this->redirect('teacher/reports?type=rekap_nilai');
    }

    /**
     * Halaman Laporan Terpadu Guru (/teacher/reports)
     * Menyajikan tab: rekap_nilai, rekap_peserta, rekap_ujian, rekap_proctoring
     */
    public function reports(): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $myExams = $this->teacherModel->getExams($teacherId);
        $classes = $this->classModel->all();
        $subjects = $this->subjectModel->all();

        $reportType = !empty($_GET['type']) ? trim($_GET['type']) : 'rekap_nilai';
        $validTypes = ['rekap_nilai', 'rekap_peserta', 'rekap_ujian', 'rekap_proctoring'];
        if (!in_array($reportType, $validTypes, true)) {
            $reportType = 'rekap_nilai';
        }

        $filters = [
            'exam_id'    => !empty($_GET['exam_id']) ? (int)$_GET['exam_id'] : null,
            'class_id'   => !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null,
            'subject_id' => !empty($_GET['subject_id']) ? (int)$_GET['subject_id'] : null,
            'date_from'  => !empty($_GET['date_from']) ? trim($_GET['date_from']) : null,
            'date_to'    => !empty($_GET['date_to']) ? trim($_GET['date_to']) : null,
        ];

        $reportData = $this->analyticsService->getReportData($reportType, $teacherId, $filters);

        $this->view('teacher/reports/index', [
            'pageTitle'   => 'Laporan & Rekapitulasi Ujian - ' . APP_NAME,
            'reportType'  => $reportType,
            'reportData'  => $reportData,
            'myExams'     => $myExams,
            'classes'     => $classes,
            'subjects'    => $subjects,
            'filters'     => $filters,
            'user'        => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Ekspor Laporan ke Format CSV (/teacher/reports/export)
     * Menggunakan native CSV dengan UTF-8 BOM untuk Excel
     */
    public function exportReportsCsv(): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $reportType = !empty($_GET['type']) ? trim($_GET['type']) : 'rekap_nilai';
        $validTypes = ['rekap_nilai', 'rekap_peserta', 'rekap_ujian', 'rekap_proctoring'];
        if (!in_array($reportType, $validTypes, true)) {
            $reportType = 'rekap_nilai';
        }

        $filters = [
            'exam_id'    => !empty($_GET['exam_id']) ? (int)$_GET['exam_id'] : null,
            'class_id'   => !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null,
            'subject_id' => !empty($_GET['subject_id']) ? (int)$_GET['subject_id'] : null,
            'date_from'  => !empty($_GET['date_from']) ? trim($_GET['date_from']) : null,
            'date_to'    => !empty($_GET['date_to']) ? trim($_GET['date_to']) : null,
        ];

        $reportData = $this->analyticsService->getReportData($reportType, $teacherId, $filters);
        $csvContent = $this->analyticsService->generateCsv($reportType, $reportData);

        $filename = "smartexam_{$reportType}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csvContent;
        exit;
    }

    /**
     * Cetak Laporan Resmi (Print-to-PDF format) (/teacher/reports/print)
     */
    public function printReport(): void
    {
        $teacherId = $this->getLoggedInTeacherId();
        $reportType = !empty($_GET['type']) ? trim($_GET['type']) : 'rekap_nilai';
        $validTypes = ['rekap_nilai', 'rekap_peserta', 'rekap_ujian', 'rekap_proctoring'];
        if (!in_array($reportType, $validTypes, true)) {
            $reportType = 'rekap_nilai';
        }

        $filters = [
            'exam_id'    => !empty($_GET['exam_id']) ? (int)$_GET['exam_id'] : null,
            'class_id'   => !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null,
            'subject_id' => !empty($_GET['subject_id']) ? (int)$_GET['subject_id'] : null,
            'date_from'  => !empty($_GET['date_from']) ? trim($_GET['date_from']) : null,
            'date_to'    => !empty($_GET['date_to']) ? trim($_GET['date_to']) : null,
        ];

        $reportData = $this->analyticsService->getReportData($reportType, $teacherId, $filters);
        $teacher = $this->teacherModel->find($teacherId);

        // Ambil nama exam / class jika difilter
        $examName = 'Semua Paket Ujian';
        if (!empty($filters['exam_id'])) {
            $ex = $this->examModel->find((int)$filters['exam_id']);
            if ($ex) $examName = $ex['title'];
        }

        $this->view('teacher/reports/print', [
            'pageTitle'   => 'Cetak ' . ucwords(str_replace('_', ' ', $reportType)) . ' - ' . APP_NAME,
            'reportType'  => $reportType,
            'reportData'  => $reportData,
            'filters'     => $filters,
            'examName'    => $examName,
            'teacher'     => $teacher,
            'user'        => AuthMiddleware::user()
        ], 'blank'); // Layout blank khusus print tanpa navbar/sidebar
    }

    /**
     * Penyesuaian Nilai Siswa oleh Guru Pengampu (/teacher/updateScore)
     * Hak Akses: Khusus Guru yang memiliki wewenang pada paket ujian terkait
     */
    public function updateScore(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['is_ajax']);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
        }

        if (!$this->isPost()) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
                exit;
            }
            $this->redirect('teacher/reports');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid atau sesi kedaluwarsa.']);
                exit;
            }
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/reports');
            return;
        }

        $teacherId     = $this->getLoggedInTeacherId();
        $participantId = (int)$this->getPost('participant_id', 0);
        $newScore      = (float)$this->getPost('score', 0.0);
        $notes         = trim(SanitizeHelper::clean($this->getPost('notes', 'Penyesuaian nilai oleh guru pengampu.')));

        // Verifikasi kepemilikan paket ujian oleh guru
        $participant = $this->participantModel->getDetails($participantId);
        if (!$participant || !$this->examModel->isOwnedByTeacher((int)$participant['exam_id'], $teacherId)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah nilai peserta ini.']);
                exit;
            }
            SessionHelper::setFlash('danger', 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah nilai peserta ini.');
            $this->redirect('teacher/reports');
            return;
        }

        // Batasi rentang skor secara ketat 0.00 s/d 100.00
        $cleanScore = max(0.00, min(100.00, $newScore));
        $exam = $this->examModel->find((int)$participant['exam_id']);
        $passingGrade = (float)($exam['passing_grade'] ?? 75.00);

        // Update nilai di exam_participants dan lembar results
        $this->participantModel->finishAttempt($participantId, $cleanScore);
        $this->resultModel->calculateAndSave($participantId, $passingGrade, $notes);

        // Trigger Notifikasi Nilai Resmi Dirilis ke Siswa (In-App & WhatsApp)
        try {
            $isPassed = ($cleanScore >= $passingGrade);
            $notifService = new \App\Helpers\NotificationService();
            $notifService->sendScoreReleasedNotification(
                $participantId,
                $cleanScore,
                $passingGrade,
                $isPassed,
                $notes
            );
        } catch (\Throwable $e) {
            error_log("Failed to trigger sendScoreReleasedNotification: " . $e->getMessage());
        }

        // Catat Jejak Audit CHANGE_SCORE
        $this->auditModel->logActivity(
            AuthMiddleware::id(),
            'CHANGE_SCORE',
            "Guru mengubah nilai peserta ID: {$participantId} (Ujian ID: {$participant['exam_id']}) menjadi {$cleanScore}. Keterangan: {$notes}"
        );

        if ($isAjax) {
            echo json_encode([
                'success'   => true,
                'message'   => "Nilai peserta berhasil diperbarui menjadi {$cleanScore}.",
                'new_score' => $cleanScore
            ]);
            exit;
        }

        SessionHelper::setFlash('success', "Nilai peserta berhasil diperbarui menjadi <strong>{$cleanScore}</strong>.");
        $redirectUrl = $this->getPost('redirect_url', 'teacher/reports?type=rekap_nilai');
        $this->redirect($redirectUrl);
    }

    /**
     * Halaman Profil Guru Pengajar (/teacher/profile)
     */
    public function profile(): void
    {
        $userId = (int)AuthMiddleware::id();
        $teacher = $this->teacherModel->findByUserId($userId);
        $teacherId = (int)($teacher['id'] ?? 0);

        $profile = $this->userModel->getUserProfile($userId);
        if (!$profile) {
            $profile = AuthMiddleware::user() ?? [];
        }

        $stats = $this->teacherModel->getStats($teacherId);
        $banks = $teacherId > 0 ? $this->teacherModel->getQuestionBanks($teacherId) : [];
        $exams = $teacherId > 0 ? $this->teacherModel->getExams($teacherId) : [];
        $logs = $this->userModel->getAuditLogs($userId, 15);

        $this->view('teacher/profile/index', [
            'pageTitle' => 'Profil Guru Pengajar - ' . APP_NAME,
            'profile'   => $profile,
            'teacher'   => $teacher,
            'stats'     => $stats,
            'banks'     => array_slice($banks, 0, 5),
            'exams'     => array_slice($exams, 0, 5),
            'logs'      => $logs,
            'user'      => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Proses Pembaruan Profil Guru (POST)
     */
    public function updateProfile(): void
    {
        if (!$this->isPost()) {
            $this->redirect('teacher/profile');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid atau sesi formulir kedaluwarsa.');
            $this->redirect('teacher/profile');
            return;
        }

        $userId   = (int)AuthMiddleware::id();
        $teacher  = $this->teacherModel->findByUserId($userId);
        $teacherId = (int)($teacher['id'] ?? 0);

        $name     = trim(SanitizeHelper::clean($this->getPost('name', '')));
        $username = strtolower(trim(SanitizeHelper::clean($this->getPost('username', ''))));
        $email    = strtolower(trim(SanitizeHelper::clean($this->getPost('email', ''))));
        $nip      = trim(SanitizeHelper::clean($this->getPost('nip', '')));
        $nuptk    = trim(SanitizeHelper::clean($this->getPost('nuptk', '')));
        $gender   = $this->getPost('gender', 'L') === 'P' ? 'P' : 'L';
        $phone    = trim(SanitizeHelper::clean($this->getPost('phone', '')));
        $address  = trim(SanitizeHelper::clean($this->getPost('address', '')));

        if (empty($name) || empty($username) || empty($email)) {
            SessionHelper::setFlash('warning', 'Nama lengkap, username, dan email wajib diisi.');
            $this->redirect('teacher/profile');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            SessionHelper::setFlash('danger', 'Format alamat email tidak valid.');
            $this->redirect('teacher/profile');
            return;
        }

        if ($this->userModel->isUsernameTaken($username, $userId)) {
            SessionHelper::setFlash('danger', "Username <strong>{$username}</strong> sudah digunakan pengguna lain.");
            $this->redirect('teacher/profile');
            return;
        }

        if ($this->userModel->isEmailTaken($email, $userId)) {
            SessionHelper::setFlash('danger', "Email <strong>{$email}</strong> sudah terdaftar pada akun lain.");
            $this->redirect('teacher/profile');
            return;
        }

        if (!empty($nip) && $teacherId > 0 && $this->teacherModel->findByNip($nip, $teacherId)) {
            SessionHelper::setFlash('danger', "NIP <strong>{$nip}</strong> sudah digunakan oleh guru lain.");
            $this->redirect('teacher/profile');
            return;
        }

        $success = false;
        if ($teacherId > 0) {
            $success = $this->teacherModel->updateWithUser($teacherId, [
                'name'      => $name,
                'username'  => $username,
                'email'     => $email,
                'is_active' => 1
            ], [
                'nip'     => $nip,
                'nuptk'   => $nuptk,
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
                'email'    => $email,
                'nisn_nip' => $nip ?: $username
            ]);
            $this->auditModel->logActivity($userId, 'UPDATE_PROFILE', "Guru {$name} memperbarui informasi biodata profil.");
            SessionHelper::setFlash('success', 'Data profil guru berhasil diperbarui.');
        } else {
            SessionHelper::setFlash('danger', 'Gagal memperbarui data profil guru.');
        }

        $this->redirect('teacher/profile');
    }

    /**
     * Proses Pembaruan Kata Sandi Guru (POST)
     */
    public function updatePassword(): void
    {
        if (!$this->isPost()) {
            $this->redirect('teacher/profile');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('teacher/profile');
            return;
        }

        $userId          = (int)AuthMiddleware::id();
        $currentPassword = (string)$this->getPost('current_password', '');
        $newPassword     = (string)$this->getPost('new_password', '');
        $confirmPassword = (string)$this->getPost('confirm_password', '');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            SessionHelper::setFlash('warning', 'Semua kolom kata sandi wajib diisi.');
            $this->redirect('teacher/profile');
            return;
        }

        if (!$this->userModel->verifyPassword($userId, $currentPassword)) {
            SessionHelper::setFlash('danger', 'Kata sandi saat ini yang Anda masukkan salah.');
            $this->redirect('teacher/profile');
            return;
        }

        if (strlen($newPassword) < 6) {
            SessionHelper::setFlash('danger', 'Kata sandi baru minimal 6 karakter.');
            $this->redirect('teacher/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            SessionHelper::setFlash('danger', 'Konfirmasi kata sandi baru tidak cocok.');
            $this->redirect('teacher/profile');
            return;
        }

        $success = $this->userModel->updateUserPassword($userId, $newPassword);

        if ($success) {
            $this->auditModel->logActivity($userId, 'CHANGE_PASSWORD', 'Guru berhasil mengubah kata sandi akun.');
            SessionHelper::setFlash('success', 'Kata sandi berhasil diperbarui dengan aman.');
        } else {
            SessionHelper::setFlash('danger', 'Gagal memperbarui kata sandi.');
        }

        $this->redirect('teacher/profile');
    }
}