<?php
/**
 * SmartExam AI - Home Controller (Landing Page)
 * Menampilkan halaman beranda publik sebelum pengguna login ke sistem.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthMiddleware;
use App\Models\ExamModel;
use App\Models\QuestionBankModel;
use App\Models\StudentModel;
use App\Models\TeacherModel;

class HomeController extends Controller
{
    private ExamModel $examModel;
    private QuestionBankModel $bankModel;
    private StudentModel $studentModel;
    private TeacherModel $teacherModel;

    public function __construct()
    {
        $this->examModel = new ExamModel();
        $this->bankModel = new QuestionBankModel();
        $this->studentModel = new StudentModel();
        $this->teacherModel = new TeacherModel();
    }

    /**
     * Halaman Depan / Landing Page Utama
     */
    public function index(): void
    {
        $isLoggedIn = AuthMiddleware::isLoggedIn();
        $currentUser = $isLoggedIn ? AuthMiddleware::user() : null;
        $userRole = $isLoggedIn ? AuthMiddleware::role() : null;

        // Ambil statistik sistem untuk display publik
        $stats = [
            'total_students' => $this->studentModel->count(),
            'total_teachers' => $this->teacherModel->count(),
            'total_banks'    => $this->bankModel->count(),
            'total_exams'    => $this->examModel->count(),
        ];

        // Ujian aktif yang sedang berjalan hari ini
        $activeExams = [];
        try {
            $activeExams = $this->examModel->getActiveExams();
        } catch (\Throwable $e) {
            $activeExams = [];
        }

        $this->view('home/index', [
            'pageTitle'   => APP_NAME . ' - Platform Ujian Online Cerdas & AI Proctoring',
            'isLoggedIn'  => $isLoggedIn,
            'currentUser' => $currentUser,
            'userRole'    => $userRole,
            'stats'       => $stats,
            'activeExams' => $activeExams
        ], 'landing');
    }
}
