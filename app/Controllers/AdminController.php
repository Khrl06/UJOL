<?php
/**
 * SmartExam AI - Admin Controller
 * Mengelola seluruh fungsi Master Data, Dasbor, dan Modul Administratif.
 * Hak Akses: Khusus Role 'admin'.
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
use App\Models\StudentModel;
use App\Models\SubjectModel;
use App\Models\TeacherModel;
use App\Models\UserModel;
use App\Models\ProctoringEventModel;
use App\Models\SettingModel;
use App\Services\Proctoring\AIProctoringService;

class AdminController extends Controller
{
    private UserModel $userModel;
    private TeacherModel $teacherModel;
    private StudentModel $studentModel;
    private ClassModel $classModel;
    private SubjectModel $subjectModel;
    private QuestionBankModel $bankModel;
    private ExamModel $examModel;
    private ExamParticipantModel $participantModel;
    private AuditLogModel $auditModel;
    private ProctoringEventModel $proctoringModel;
    private AIProctoringService $proctoringService;
    private SettingModel $settingModel;

    public function __construct()
    {
        // Proteksi Otorisasi Ketat: Hanya peran 'admin' yang diizinkan
        AuthMiddleware::requireRole('admin');

        $this->userModel = new UserModel();
        $this->teacherModel = new TeacherModel();
        $this->studentModel = new StudentModel();
        $this->classModel = new ClassModel();
        $this->subjectModel = new SubjectModel();
        $this->bankModel = new QuestionBankModel();
        $this->examModel = new ExamModel();
        $this->participantModel = new ExamParticipantModel();
        $this->auditModel = new AuditLogModel();
        $this->proctoringModel = new ProctoringEventModel();
        $this->proctoringService = new AIProctoringService();
        $this->settingModel = new SettingModel();
    }

    /**
     * Tampilan Dasbor Utama Admin (/admin atau /admin/dashboard)
     * Menampilkan 8 Kartu Statistik Real-time
     */
    public function index(): void
    {
        $currentUser = AuthMiddleware::user();

        // 8 Kartu Statistik Real-time
        $stats = [
            'total_teachers'         => $this->teacherModel->count(),
            'total_students'         => $this->studentModel->count(),
            'total_classes'          => $this->classModel->count(),
            'total_subjects'         => $this->subjectModel->count(),
            'total_question_banks'   => $this->bankModel->count(),
            'total_exams'            => $this->examModel->count(),
            'active_exams'           => count($this->examModel->getActiveExams()),
            'active_exam_students'   => $this->participantModel->count("status = 'ongoing'"),
        ];

        // Informasi Server & Sistem
        $systemInfo = [
            'php_version'     => PHP_VERSION,
            'db_connected'    => $this->userModel->isDbConnected(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache/PHP Server',
            'app_version'     => APP_VERSION,
            'timezone'        => date_default_timezone_get(),
        ];

        // Daftar Pengguna Terdaftar
        $userList = $this->userModel->getAllUsers();

        // Log Aktivitas Terbaru
        $recentLogs = $this->auditModel->getRecentLogs(6);

        $this->view('admin/dashboard', [
            'pageTitle'  => 'Dasbor Administrator - ' . APP_NAME,
            'user'       => $currentUser,
            'stats'      => $stats,
            'systemInfo' => $systemInfo,
            'userList'   => $userList,
            'recentLogs' => $recentLogs
        ], 'main');
    }

    public function dashboard(): void
    {
        $this->index();
    }

    // =========================================================================
    // MODUL 1: MASTER DATA GURU
    // =========================================================================

    /**
     * Halaman Daftar Guru (dengan Search & Pagination)
     */
    public function teachers(): void
    {
        $search = SanitizeHelper::clean($this->getQuery('q', ''));
        $page = (int)$this->getQuery('page', 1);
        $perPage = 8;

        $result = $this->teacherModel->paginate($search, $page, $perPage);

        $this->view('admin/teachers/index', [
            'pageTitle' => 'Manajemen Data Guru - ' . APP_NAME,
            'teachers'  => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'perPage'   => $result['per_page'],
            'totalPages'=> $result['total_pages'],
            'search'    => $search,
            'user'      => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Tambah Guru Baru (POST)
     */
    public function createTeacher(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/teachers');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid atau sesi formulir kedaluwarsa.');
            $this->redirect('admin/teachers');
            return;
        }

        $name     = SanitizeHelper::clean($this->getPost('name', ''));
        $username = strtolower(trim(SanitizeHelper::clean($this->getPost('username', ''))));
        $email    = strtolower(trim(SanitizeHelper::clean($this->getPost('email', ''))));
        $password = (string)$this->getPost('password', 'guru123');
        $nip      = trim(SanitizeHelper::clean($this->getPost('nip', '')));
        $nuptk    = trim(SanitizeHelper::clean($this->getPost('nuptk', '')));
        $gender   = $this->getPost('gender', 'L') === 'P' ? 'P' : 'L';
        $phone    = trim(SanitizeHelper::clean($this->getPost('phone', '')));
        $address  = trim(SanitizeHelper::clean($this->getPost('address', '')));

        // Validasi
        if (empty($name) || empty($username) || empty($email)) {
            SessionHelper::setFlash('warning', 'Nama lengkap, username, dan email wajib diisi.');
            $this->redirect('admin/teachers');
            return;
        }

        if ($this->userModel->isUsernameTaken($username)) {
            SessionHelper::setFlash('danger', "Username <strong>{$username}</strong> sudah digunakan. Silakan gunakan username lain.");
            $this->redirect('admin/teachers');
            return;
        }

        if ($this->userModel->isEmailTaken($email)) {
            SessionHelper::setFlash('danger', "Email <strong>{$email}</strong> sudah terdaftar.");
            $this->redirect('admin/teachers');
            return;
        }

        if (!empty($nip) && $this->teacherModel->findByNip($nip)) {
            SessionHelper::setFlash('danger', "NIP <strong>{$nip}</strong> sudah digunakan oleh guru lain.");
            $this->redirect('admin/teachers');
            return;
        }

        $teacherId = $this->teacherModel->createWithUser([
            'name'      => $name,
            'username'  => $username,
            'email'     => $email,
            'password'  => $password ?: 'guru123',
            'is_active' => 1
        ], [
            'nip'     => $nip,
            'nuptk'   => $nuptk,
            'gender'  => $gender,
            'phone'   => $phone,
            'address' => $address
        ]);

        if ($teacherId > 0) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'CREATE_TEACHER', "Menambahkan guru baru: {$name} ({$username})");
            SessionHelper::setFlash('success', "Data guru <strong>{$name}</strong> berhasil ditambahkan beserta akun sistem.");
        } else {
            SessionHelper::setFlash('danger', "Gagal menambahkan data guru. Terjadi kesalahan database.");
        }

        $this->redirect('admin/teachers');
    }

    /**
     * Edit Guru (POST)
     */
    public function editTeacher(int $id = 0): void
    {
        $teacherId = $id ?: (int)$this->getPost('id', 0);
        if (!$this->isPost() || $teacherId <= 0) {
            $this->redirect('admin/teachers');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/teachers');
            return;
        }

        $teacher = $this->teacherModel->find($teacherId);
        if (!$teacher) {
            SessionHelper::setFlash('danger', 'Data guru tidak ditemukan.');
            $this->redirect('admin/teachers');
            return;
        }

        $userId   = (int)$teacher['user_id'];
        $name     = SanitizeHelper::clean($this->getPost('name', ''));
        $username = strtolower(trim(SanitizeHelper::clean($this->getPost('username', ''))));
        $email    = strtolower(trim(SanitizeHelper::clean($this->getPost('email', ''))));
        $password = (string)$this->getPost('password', '');
        $isActive = (int)$this->getPost('is_active', 1);
        $nip      = trim(SanitizeHelper::clean($this->getPost('nip', '')));
        $nuptk    = trim(SanitizeHelper::clean($this->getPost('nuptk', '')));
        $gender   = $this->getPost('gender', 'L') === 'P' ? 'P' : 'L';
        $phone    = trim(SanitizeHelper::clean($this->getPost('phone', '')));
        $address  = trim(SanitizeHelper::clean($this->getPost('address', '')));

        if (empty($name) || empty($username) || empty($email)) {
            SessionHelper::setFlash('warning', 'Nama lengkap, username, dan email wajib diisi.');
            $this->redirect('admin/teachers');
            return;
        }

        if ($this->userModel->isUsernameTaken($username, $userId)) {
            SessionHelper::setFlash('danger', "Username <strong>{$username}</strong> sudah dipakai akun lain.");
            $this->redirect('admin/teachers');
            return;
        }

        if ($this->userModel->isEmailTaken($email, $userId)) {
            SessionHelper::setFlash('danger', "Email <strong>{$email}</strong> sudah dipakai akun lain.");
            $this->redirect('admin/teachers');
            return;
        }

        if (!empty($nip) && $this->teacherModel->findByNip($nip, $teacherId)) {
            SessionHelper::setFlash('danger', "NIP <strong>{$nip}</strong> sudah terdaftar pada guru lain.");
            $this->redirect('admin/teachers');
            return;
        }

        $success = $this->teacherModel->updateWithUser($teacherId, [
            'name'      => $name,
            'username'  => $username,
            'email'     => $email,
            'password'  => $password,
            'is_active' => $isActive
        ], [
            'nip'     => $nip,
            'nuptk'   => $nuptk,
            'gender'  => $gender,
            'phone'   => $phone,
            'address' => $address
        ]);

        if ($success) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'UPDATE_TEACHER', "Memperbarui profil guru ID: {$teacherId} ({$name})");
            SessionHelper::setFlash('success', "Data guru <strong>{$name}</strong> berhasil diperbarui.");
        } else {
            SessionHelper::setFlash('danger', "Gagal memperbarui data guru.");
        }

        $this->redirect('admin/teachers');
    }

    /**
     * Hapus Guru (POST)
     */
    public function deleteTeacher(int $id = 0): void
    {
        $teacherId = $id ?: (int)$this->getPost('id', 0);
        if (!$this->isPost() || $teacherId <= 0) {
            $this->redirect('admin/teachers');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/teachers');
            return;
        }

        $teacher = $this->teacherModel->getDetails($teacherId);
        if (!$teacher) {
            SessionHelper::setFlash('danger', 'Data guru tidak ditemukan.');
            $this->redirect('admin/teachers');
            return;
        }

        $success = $this->teacherModel->deleteWithUser($teacherId);
        if ($success) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'DELETE_TEACHER', "Menghapus data guru: {$teacher['name']}");
            SessionHelper::setFlash('success', "Data guru <strong>{$teacher['name']}</strong> beserta akunnya berhasil dihapus.");
        } else {
            SessionHelper::setFlash('danger', "Gagal menghapus data guru.");
        }

        $this->redirect('admin/teachers');
    }

    // =========================================================================
    // MODUL 2: MASTER DATA PESERTA DIDIK (SISWA)
    // =========================================================================

    /**
     * Halaman Daftar Siswa (dengan Search, Filter Kelas, & Pagination)
     */
    public function students(): void
    {
        $search = SanitizeHelper::clean($this->getQuery('q', ''));
        $classId = (int)$this->getQuery('class_id', 0) ?: null;
        $page = (int)$this->getQuery('page', 1);
        $perPage = 8;

        $result = $this->studentModel->paginate($search, $classId, $page, $perPage);
        $classes = $this->classModel->all();

        $this->view('admin/students/index', [
            'pageTitle' => 'Manajemen Peserta Didik - ' . APP_NAME,
            'students'  => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'perPage'   => $result['per_page'],
            'totalPages'=> $result['total_pages'],
            'search'    => $search,
            'classId'   => $classId,
            'classes'   => $classes,
            'user'      => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Tambah Siswa Baru (POST)
     */
    public function createStudent(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/students');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/students');
            return;
        }

        $name     = SanitizeHelper::clean($this->getPost('name', ''));
        $username = strtolower(trim(SanitizeHelper::clean($this->getPost('username', ''))));
        $email    = strtolower(trim(SanitizeHelper::clean($this->getPost('email', ''))));
        $password = (string)$this->getPost('password', 'siswa123');
        $nisn     = trim(SanitizeHelper::clean($this->getPost('nisn', '')));
        $classId  = (int)$this->getPost('class_id', 0);
        $gender   = $this->getPost('gender', 'L') === 'P' ? 'P' : 'L';
        $phone    = trim(SanitizeHelper::clean($this->getPost('phone', '')));
        $address  = trim(SanitizeHelper::clean($this->getPost('address', '')));

        if (empty($name) || empty($username) || empty($email) || empty($nisn) || $classId <= 0) {
            SessionHelper::setFlash('warning', 'Nama lengkap, username, email, NISN, dan kelas rombel wajib diisi.');
            $this->redirect('admin/students');
            return;
        }

        if ($this->userModel->isUsernameTaken($username)) {
            SessionHelper::setFlash('danger', "Username <strong>{$username}</strong> sudah dipakai.");
            $this->redirect('admin/students');
            return;
        }

        if ($this->userModel->isEmailTaken($email)) {
            SessionHelper::setFlash('danger', "Email <strong>{$email}</strong> sudah terdaftar.");
            $this->redirect('admin/students');
            return;
        }

        if ($this->studentModel->findByNisn($nisn)) {
            SessionHelper::setFlash('danger', "NISN <strong>{$nisn}</strong> sudah terdaftar.");
            $this->redirect('admin/students');
            return;
        }

        $studentId = $this->studentModel->createWithUser([
            'name'      => $name,
            'username'  => $username,
            'email'     => $email,
            'password'  => $password ?: 'siswa123',
            'is_active' => 1
        ], [
            'class_id' => $classId,
            'nisn'     => $nisn,
            'gender'   => $gender,
            'phone'    => $phone,
            'address'  => $address
        ]);

        if ($studentId > 0) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'CREATE_STUDENT', "Menambahkan siswa baru: {$name} (NISN: {$nisn})");
            SessionHelper::setFlash('success', "Peserta didik <strong>{$name}</strong> berhasil ditambahkan.");
        } else {
            SessionHelper::setFlash('danger', "Gagal menambahkan peserta didik.");
        }

        $this->redirect('admin/students');
    }

    /**
     * Edit Siswa (POST)
     */
    public function editStudent(int $id = 0): void
    {
        $studentId = $id ?: (int)$this->getPost('id', 0);
        if (!$this->isPost() || $studentId <= 0) {
            $this->redirect('admin/students');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/students');
            return;
        }

        $student = $this->studentModel->find($studentId);
        if (!$student) {
            SessionHelper::setFlash('danger', 'Data siswa tidak ditemukan.');
            $this->redirect('admin/students');
            return;
        }

        $userId   = (int)$student['user_id'];
        $name     = SanitizeHelper::clean($this->getPost('name', ''));
        $username = strtolower(trim(SanitizeHelper::clean($this->getPost('username', ''))));
        $email    = strtolower(trim(SanitizeHelper::clean($this->getPost('email', ''))));
        $password = (string)$this->getPost('password', '');
        $isActive = (int)$this->getPost('is_active', 1);
        $nisn     = trim(SanitizeHelper::clean($this->getPost('nisn', '')));
        $classId  = (int)$this->getPost('class_id', 0);
        $gender   = $this->getPost('gender', 'L') === 'P' ? 'P' : 'L';
        $phone    = trim(SanitizeHelper::clean($this->getPost('phone', '')));
        $address  = trim(SanitizeHelper::clean($this->getPost('address', '')));

        if (empty($name) || empty($username) || empty($email) || empty($nisn) || $classId <= 0) {
            SessionHelper::setFlash('warning', 'Nama, username, email, NISN, dan kelas wajib diisi.');
            $this->redirect('admin/students');
            return;
        }

        if ($this->userModel->isUsernameTaken($username, $userId)) {
            SessionHelper::setFlash('danger', "Username <strong>{$username}</strong> sudah dipakai akun lain.");
            $this->redirect('admin/students');
            return;
        }

        if ($this->userModel->isEmailTaken($email, $userId)) {
            SessionHelper::setFlash('danger', "Email <strong>{$email}</strong> sudah dipakai akun lain.");
            $this->redirect('admin/students');
            return;
        }

        if ($this->studentModel->findByNisn($nisn, $studentId)) {
            SessionHelper::setFlash('danger', "NISN <strong>{$nisn}</strong> sudah terdaftar pada siswa lain.");
            $this->redirect('admin/students');
            return;
        }

        $success = $this->studentModel->updateWithUser($studentId, [
            'name'      => $name,
            'username'  => $username,
            'email'     => $email,
            'password'  => $password,
            'is_active' => $isActive
        ], [
            'class_id' => $classId,
            'nisn'     => $nisn,
            'gender'   => $gender,
            'phone'    => $phone,
            'address'  => $address
        ]);

        if ($success) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'UPDATE_STUDENT', "Memperbarui siswa ID: {$studentId} ({$name})");
            SessionHelper::setFlash('success', "Data peserta didik <strong>{$name}</strong> berhasil diperbarui.");
        } else {
            SessionHelper::setFlash('danger', "Gagal memperbarui data siswa.");
        }

        $this->redirect('admin/students');
    }

    /**
     * Hapus Siswa (POST)
     */
    public function deleteStudent(int $id = 0): void
    {
        $studentId = $id ?: (int)$this->getPost('id', 0);
        if (!$this->isPost() || $studentId <= 0) {
            $this->redirect('admin/students');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/students');
            return;
        }

        $student = $this->studentModel->getDetails($studentId);
        if (!$student) {
            SessionHelper::setFlash('danger', 'Data siswa tidak ditemukan.');
            $this->redirect('admin/students');
            return;
        }

        $success = $this->studentModel->deleteWithUser($studentId);
        if ($success) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'DELETE_STUDENT', "Menghapus peserta didik: {$student['name']}");
            SessionHelper::setFlash('success', "Peserta didik <strong>{$student['name']}</strong> berhasil dihapus.");
        } else {
            SessionHelper::setFlash('danger', "Gagal menghapus peserta didik.");
        }

        $this->redirect('admin/students');
    }

    // =========================================================================
    // MODUL 3: MASTER DATA KELAS (ROMBEL)
    // =========================================================================

    /**
     * Halaman Daftar Kelas
     */
    public function classes(): void
    {
        $search = SanitizeHelper::clean($this->getQuery('q', ''));
        $classes = $this->classModel->getAllWithCounts($search);

        $this->view('admin/classes/index', [
            'pageTitle' => 'Manajemen Kelas & Rombel - ' . APP_NAME,
            'classes'   => $classes,
            'search'    => $search,
            'user'      => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Tambah Kelas Baru (POST)
     */
    public function createClass(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/classes');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/classes');
            return;
        }

        $className    = trim(SanitizeHelper::clean($this->getPost('class_name', '')));
        $gradeLevel   = $this->getPost('grade_level', '12');
        $major        = trim(SanitizeHelper::clean($this->getPost('major', 'MIPA')));
        $academicYear = trim(SanitizeHelper::clean($this->getPost('academic_year', '2026/2027')));

        if (empty($className)) {
            SessionHelper::setFlash('warning', 'Nama rombel kelas wajib diisi.');
            $this->redirect('admin/classes');
            return;
        }

        if ($this->classModel->findByName($className)) {
            SessionHelper::setFlash('danger', "Kelas <strong>{$className}</strong> sudah ada.");
            $this->redirect('admin/classes');
            return;
        }

        $classId = $this->classModel->create([
            'class_name'    => $className,
            'grade_level'   => $gradeLevel,
            'major'         => $major,
            'academic_year' => $academicYear
        ]);

        if ($classId > 0) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'CREATE_CLASS', "Menambahkan rombel kelas: {$className}");
            SessionHelper::setFlash('success', "Rombel kelas <strong>{$className}</strong> berhasil ditambahkan.");
        } else {
            SessionHelper::setFlash('danger', "Gagal menambahkan kelas.");
        }

        $this->redirect('admin/classes');
    }

    /**
     * Edit Kelas (POST)
     */
    public function editClass(int $id = 0): void
    {
        $classId = $id ?: (int)$this->getPost('id', 0);
        if (!$this->isPost() || $classId <= 0) {
            $this->redirect('admin/classes');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/classes');
            return;
        }

        $className    = trim(SanitizeHelper::clean($this->getPost('class_name', '')));
        $gradeLevel   = $this->getPost('grade_level', '12');
        $major        = trim(SanitizeHelper::clean($this->getPost('major', 'MIPA')));
        $academicYear = trim(SanitizeHelper::clean($this->getPost('academic_year', '2026/2027')));

        if (empty($className)) {
            SessionHelper::setFlash('warning', 'Nama rombel kelas wajib diisi.');
            $this->redirect('admin/classes');
            return;
        }

        if ($this->classModel->findByName($className, $classId)) {
            SessionHelper::setFlash('danger', "Nama kelas <strong>{$className}</strong> sudah digunakan.");
            $this->redirect('admin/classes');
            return;
        }

        $success = $this->classModel->update($classId, [
            'class_name'    => $className,
            'grade_level'   => $gradeLevel,
            'major'         => $major,
            'academic_year' => $academicYear
        ]);

        if ($success) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'UPDATE_CLASS', "Memperbarui rombel kelas ID: {$classId} ({$className})");
            SessionHelper::setFlash('success', "Rombel kelas <strong>{$className}</strong> berhasil diperbarui.");
        } else {
            SessionHelper::setFlash('danger', "Gagal memperbarui kelas.");
        }

        $this->redirect('admin/classes');
    }

    /**
     * Hapus Kelas (POST)
     */
    public function deleteClass(int $id = 0): void
    {
        $classId = $id ?: (int)$this->getPost('id', 0);
        if (!$this->isPost() || $classId <= 0) {
            $this->redirect('admin/classes');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/classes');
            return;
        }

        $class = $this->classModel->find($classId);
        if (!$class) {
            SessionHelper::setFlash('danger', 'Kelas tidak ditemukan.');
            $this->redirect('admin/classes');
            return;
        }

        // Cek proteksi referensial
        if ($this->classModel->hasStudents($classId)) {
            $count = $this->classModel->getStudentCount($classId);
            SessionHelper::setFlash('danger', "Kelas <strong>{$class['class_name']}</strong> tidak dapat dihapus karena masih memiliki {$count} siswa terdaftar. Pindahkan siswa terlebih dahulu.");
            $this->redirect('admin/classes');
            return;
        }

        $success = $this->classModel->delete($classId);
        if ($success) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'DELETE_CLASS', "Menghapus rombel kelas: {$class['class_name']}");
            SessionHelper::setFlash('success', "Rombel kelas <strong>{$class['class_name']}</strong> berhasil dihapus.");
        } else {
            SessionHelper::setFlash('danger', "Gagal menghapus kelas.");
        }

        $this->redirect('admin/classes');
    }

    // =========================================================================
    // MODUL 4: MASTER DATA MATA PELAJARAN
    // =========================================================================

    /**
     * Halaman Daftar Mata Pelajaran
     */
    public function subjects(): void
    {
        $search = SanitizeHelper::clean($this->getQuery('q', ''));
        $subjects = $this->subjectModel->getAllWithStats($search);

        $this->view('admin/subjects/index', [
            'pageTitle' => 'Manajemen Mata Pelajaran - ' . APP_NAME,
            'subjects'  => $subjects,
            'search'    => $search,
            'user'      => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Tambah Mapel Baru (POST)
     */
    public function createSubject(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/subjects');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/subjects');
            return;
        }

        $code        = strtoupper(trim(SanitizeHelper::clean($this->getPost('code', ''))));
        $name        = trim(SanitizeHelper::clean($this->getPost('name', '')));
        $description = trim(SanitizeHelper::clean($this->getPost('description', '')));

        if (empty($code) || empty($name)) {
            SessionHelper::setFlash('warning', 'Kode dan nama mata pelajaran wajib diisi.');
            $this->redirect('admin/subjects');
            return;
        }

        if ($this->subjectModel->findByCode($code)) {
            SessionHelper::setFlash('danger', "Kode mata pelajaran <strong>{$code}</strong> sudah ada.");
            $this->redirect('admin/subjects');
            return;
        }

        $subId = $this->subjectModel->create([
            'code'        => $code,
            'name'        => $name,
            'description' => $description
        ]);

        if ($subId > 0) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'CREATE_SUBJECT', "Menambahkan mapel baru: {$code} - {$name}");
            SessionHelper::setFlash('success', "Mata pelajaran <strong>{$name} ({$code})</strong> berhasil ditambahkan.");
        } else {
            SessionHelper::setFlash('danger', "Gagal menambahkan mata pelajaran.");
        }

        $this->redirect('admin/subjects');
    }

    /**
     * Edit Mapel (POST)
     */
    public function editSubject(int $id = 0): void
    {
        $subjectId = $id ?: (int)$this->getPost('id', 0);
        if (!$this->isPost() || $subjectId <= 0) {
            $this->redirect('admin/subjects');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/subjects');
            return;
        }

        $code        = strtoupper(trim(SanitizeHelper::clean($this->getPost('code', ''))));
        $name        = trim(SanitizeHelper::clean($this->getPost('name', '')));
        $description = trim(SanitizeHelper::clean($this->getPost('description', '')));

        if (empty($code) || empty($name)) {
            SessionHelper::setFlash('warning', 'Kode dan nama mata pelajaran wajib diisi.');
            $this->redirect('admin/subjects');
            return;
        }

        if ($this->subjectModel->findByCode($code, $subjectId)) {
            SessionHelper::setFlash('danger', "Kode mata pelajaran <strong>{$code}</strong> sudah dipakai mapel lain.");
            $this->redirect('admin/subjects');
            return;
        }

        $success = $this->subjectModel->update($subjectId, [
            'code'        => $code,
            'name'        => $name,
            'description' => $description
        ]);

        if ($success) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'UPDATE_SUBJECT', "Memperbarui mapel ID: {$subjectId} ({$code} - {$name})");
            SessionHelper::setFlash('success', "Mata pelajaran <strong>{$name}</strong> berhasil diperbarui.");
        } else {
            SessionHelper::setFlash('danger', "Gagal memperbarui mata pelajaran.");
        }

        $this->redirect('admin/subjects');
    }

    /**
     * Hapus Mapel (POST)
     */
    public function deleteSubject(int $id = 0): void
    {
        $subjectId = $id ?: (int)$this->getPost('id', 0);
        if (!$this->isPost() || $subjectId <= 0) {
            $this->redirect('admin/subjects');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/subjects');
            return;
        }

        $subject = $this->subjectModel->find($subjectId);
        if (!$subject) {
            SessionHelper::setFlash('danger', 'Mata pelajaran tidak ditemukan.');
            $this->redirect('admin/subjects');
            return;
        }

        if ($this->subjectModel->hasRelatedData($subjectId)) {
            SessionHelper::setFlash('danger', "Mata pelajaran <strong>{$subject['name']}</strong> tidak dapat dihapus karena masih memiliki bank soal atau ujian yang aktif.");
            $this->redirect('admin/subjects');
            return;
        }

        $success = $this->subjectModel->delete($subjectId);
        if ($success) {
            $this->auditModel->logActivity(AuthMiddleware::id(), 'DELETE_SUBJECT', "Menghapus mapel: {$subject['name']} ({$subject['code']})");
            SessionHelper::setFlash('success', "Mata pelajaran <strong>{$subject['name']}</strong> berhasil dihapus.");
        } else {
            SessionHelper::setFlash('danger', "Gagal menghapus mata pelajaran.");
        }

        $this->redirect('admin/subjects');
    }

    // =========================================================================
    // MODUL LAINNYA SESUAI MENU ADMIN
    // =========================================================================

    public function banks(): void
    {
        $search = SanitizeHelper::clean($this->getQuery('q', ''));
        $subjectId = (int)$this->getQuery('subject_id', 0) ?: null;
        $teacherId = (int)$this->getQuery('teacher_id', 0) ?: null;
        $page = (int)$this->getQuery('page', 1);
        $perPage = 8;

        $result = $this->bankModel->paginateAll($search, $subjectId, $teacherId, $page, $perPage);
        $subjects = $this->subjectModel->all();
        $teachers = $this->teacherModel->getAllWithDetails();

        $this->view('admin/banks/index', [
            'pageTitle'  => 'Bank Soal Global Sekolah - ' . APP_NAME,
            'banks'      => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'perPage'    => $result['per_page'],
            'totalPages' => $result['total_pages'],
            'search'     => $search,
            'subjectId'  => $subjectId,
            'teacherId'  => $teacherId,
            'subjects'   => $subjects,
            'teachers'   => $teachers,
            'user'       => AuthMiddleware::user()
        ], 'main');
    }

    public function exams(): void
    {
        $this->renderModulePlaceholder('Manajemen Ujian', 'bi-calendar-check-fill', 'Pengaturan jadwal dan paket ujian terpusat sekolah.');
    }

    public function monitoring(): void
    {
        $this->renderModulePlaceholder('Ruang Monitoring Pengawasan', 'bi-broadcast-pin', 'Pengawasan pelaksanaan ujian secara langsung.');
    }

    public function results(): void
    {
        $this->renderModulePlaceholder('Rekapitulasi Hasil Ujian', 'bi-file-earmark-bar-graph-fill', 'Rekap nilai, analisis butir soal, dan ekspor data.');
    }

    /**
     * Konsol Pengawasan & Review Insiden AI Proctoring (/admin/proctoring)
     * Mengimplementasikan Human-in-the-Loop review: Guru/Admin yang mengambil keputusan.
     */
    public function proctoring(): void
    {
        $examId = isset($_GET['exam_id']) && $_GET['exam_id'] !== '' ? (int)$_GET['exam_id'] : null;
        $status = !empty($_GET['status']) ? strtoupper(trim($_GET['status'])) : null;
        $eventType = !empty($_GET['event_type']) ? trim($_GET['event_type']) : null;
        $camera = !empty($_GET['camera']) ? trim($_GET['camera']) : null;
        $search = !empty($_GET['q']) ? trim($_GET['q']) : null;

        $filters = [
            'exam_id'    => $examId,
            'status'     => $status,
            'event_type' => $eventType,
            'camera'     => $camera,
            'search'     => $search
        ];

        $page = max(1, (int)($this->getQuery('page', 1)));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $totalEvents = $this->proctoringModel->countFilteredEvents($filters);
        $events = $this->proctoringModel->getFilteredEvents($filters, $limit, $offset);
        $stats = $this->proctoringModel->getSummaryStats($examId);
        $exams = $this->examModel->all();

        $this->view('admin/proctoring/index', [
            'pageTitle'    => 'AI Proctoring & Incident Review - ' . APP_NAME,
            'events'       => $events,
            'stats'        => $stats,
            'exams'        => $exams,
            'totalEvents'  => $totalEvents,
            'page'         => $page,
            'totalPages'   => max(1, (int)ceil($totalEvents / $limit)),
            'filters'      => $filters,
            'user'         => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * POST Action: Update Status Review Insiden oleh Admin (/admin/proctoring/updateStatus)
     * Status: NORMAL, REVIEW, REVIEWED, DISMISSED
     */
    public function updateProctoringStatus(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/proctoring');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid atau kedaluwarsa.');
            $this->redirect('admin/proctoring');
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
                "Admin meninjau insiden proctoring #{$eventId} menjadi status {$newStatus}"
            );
            SessionHelper::setFlash('success', "Insiden #{$eventId} berhasil ditinjau: status diubah menjadi <strong>{$newStatus}</strong>.");
        } else {
            SessionHelper::setFlash('danger', "Gagal memperbarui status insiden proctoring.");
        }

        $redirectUrl = $this->getPost('redirect_url', 'admin/proctoring');
        $this->redirect($redirectUrl);
    }

    public function reports(): void
    {
        $this->renderModulePlaceholder('Laporan Evaluasi Sekolah', 'bi-clipboard-data-fill', 'Laporan ketercapaian asesmen akademik SMA.');
    }

    public function users(): void
    {
        $this->index();
    }

    public function logs(): void
    {
        $logs = $this->auditModel->getRecentLogs(50);
        $this->view('admin/modules/placeholder', [
            'pageTitle'   => 'Audit Log Sistem - ' . APP_NAME,
            'moduleTitle' => 'Audit Log & Jejak Keamanan Sistem',
            'moduleIcon'  => 'bi-shield-shaded',
            'moduleDesc'  => 'Catatan komprehensif riwayat aktivitas autentikasi dan otorisasi pengguna.',
            'logs'        => $logs,
            'user'        => AuthMiddleware::user()
        ], 'main');
    }

    public function settings(): void
    {
        $settingsGrouped = $this->settingModel->getAllGrouped();
        $activeTab = $this->getQuery('tab', 'exam');

        $this->view('admin/settings/index', [
            'pageTitle'       => 'Konfigurasi Parameter Sistem - ' . APP_NAME,
            'settingsGrouped' => $settingsGrouped,
            'activeTab'       => $activeTab,
            'user'            => AuthMiddleware::user()
        ], 'main');
    }

    public function saveSettings(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/settings');
            return;
        }

        $csrfToken = $this->getPost('csrf_token', '');
        if (!CsrfHelper::validateToken($csrfToken)) {
            SessionHelper::setFlash('danger', 'Sesi formulir telah kedaluwarsa. Silakan ulangi.');
            $this->redirect('admin/settings');
            return;
        }

        $tab = $this->getPost('tab', 'exam');
        $postedSettings = $_POST['settings'] ?? [];

        // Penanganan khusus untuk input jenis boolean checkbox yang tidak terkirim saat unchecked
        if ($tab === 'exam') {
            foreach (['exam_token_enabled', 'exam_random_questions', 'exam_random_options', 'exam_auto_submit_on_timeout'] as $bk) {
                if (!isset($postedSettings[$bk])) {
                    $postedSettings[$bk] = '0';
                }
            }
        } elseif ($tab === 'proctoring') {
            foreach (['proctoring_default_ai', 'proctoring_default_dual_camera', 'proctoring_audio_detection'] as $bk) {
                if (!isset($postedSettings[$bk])) {
                    $postedSettings[$bk] = '0';
                }
            }
        } elseif ($tab === 'whatsapp') {
            if (!isset($postedSettings['wa_gateway_enabled'])) {
                $postedSettings['wa_gateway_enabled'] = '0';
            }
        }

        // Sanitasi dan simpan
        $cleanSettings = [];
        foreach ($postedSettings as $key => $val) {
            $cleanKey = SanitizeHelper::clean($key);
            $cleanSettings[$cleanKey] = is_string($val) ? trim($val) : $val;
        }

        $success = $this->settingModel->updateBatch($cleanSettings);

        if ($success) {
            $adminUser = AuthMiddleware::user();
            $this->auditModel->logActivity(
                $adminUser['id'] ?? null,
                'SETTINGS_UPDATED',
                "Administrator memperbarui konfigurasi parameter sistem pada tab [{$tab}]."
            );
            SessionHelper::setFlash('success', 'Konfigurasi parameter sistem berhasil disimpan dan diperbarui.');
        } else {
            SessionHelper::setFlash('danger', 'Terjadi kesalahan saat menyimpan pengaturan sistem.');
        }

        $this->redirect('admin/settings?tab=' . urlencode($tab));
    }

    public function testWaConnection(): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Metode request tidak diizinkan.'], 405);
            return;
        }

        $csrfToken = $this->getPost('csrf_token', '');
        if (!CsrfHelper::validateToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            return;
        }

        $phone = trim($this->getPost('test_phone', ''));
        if (empty($phone)) {
            $this->json(['success' => false, 'message' => 'Nomor WhatsApp uji coba wajib diisi.'], 422);
            return;
        }

        $message = "🔔 *TES KONEKSI WHATSAPP GATEWAY*\n"
                 . "*SmartExam AI - UJOL*\n"
                 . "----------------------------------------\n"
                 . "Halo Administrator,\n\n"
                 . "Ini adalah pesan uji coba koneksi gateway WhatsApp untuk sistem SmartExam AI.\n"
                 . "Waktu pengujian: " . date('d-m-Y H:i:s') . " WIB\n\n"
                 . "Jika Anda menerima pesan ini, integrasi WhatsApp Gateway telah berfungsi dengan sempurna!";

        $res = \App\Helpers\WhatsAppGateway::sendMessage($phone, $message);
        $this->json($res);
    }

    public function testAiConnection(): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Metode request tidak diizinkan.'], 405);
            return;
        }

        $csrfToken = $this->getPost('csrf_token', '');
        if (!CsrfHelper::validateToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            return;
        }

        $provider = strtolower(trim($this->getPost('provider', 'gemini')));
        $apiKey   = trim($this->getPost('api_key', ''));
        $model    = trim($this->getPost('model_name', ''));
        $baseUrl  = trim($this->getPost('base_url', ''));

        if (empty($apiKey)) {
            $apiKey = (string)$this->settingModel->get('ai_api_key', '');
        }
        if (empty($apiKey) && $provider === 'gemini') {
            $apiKey = 'AQ.Ab8RN6IdqRznTT2HZKQfBXfHKGj7m_V3pDiKmCjeOKk58LELfA';
        }

        if (empty($apiKey) && $provider !== 'ollama') {
            $this->json(['success' => false, 'message' => 'API Key wajib diisi untuk menguji koneksi AI provider.'], 422);
            return;
        }

        try {
            if ($provider === 'gemini') {
                $requestedModel = $model ?: 'gemini-2.5-flash';
                $geminiCandidates = array_unique([$requestedModel, 'gemini-2.5-flash', 'gemini-flash-latest', 'gemini-flash-lite-latest', 'gemini-1.5-flash']);
                
                $lastErrMsg = '';
                foreach ($geminiCandidates as $modelName) {
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($apiKey);
                    $body = json_encode([
                        'contents' => [
                            ['parts' => [['text' => 'Halo, ini pesan uji koneksi. Jawab "Koneksi Google Gemini AI Berhasil!" secara singkat.']]]
                        ]
                    ]);

                    $ch = curl_init($url);
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST           => true,
                        CURLOPT_POSTFIELDS     => $body,
                        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                        CURLOPT_TIMEOUT        => 12,
                        CURLOPT_SSL_VERIFYPEER => false
                    ]);
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlErr  = curl_error($ch);
                    curl_close($ch);

                    if ($curlErr) {
                        $lastErrMsg = 'cURL Error: ' . $curlErr;
                        continue;
                    }

                    $resData = json_decode($response, true);
                    if ($httpCode >= 200 && $httpCode < 300 && isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                        $reply = $resData['candidates'][0]['content']['parts'][0]['text'];
                        $this->json(['success' => true, 'provider' => 'Google Gemini', 'model' => $modelName, 'reply' => trim($reply)]);
                        return;
                    } else {
                        $lastErrMsg = $resData['error']['message'] ?? "HTTP Status {$httpCode}";
                    }
                }

                $this->json(['success' => false, 'message' => "Gagal respon dari Google Gemini ({$lastErrMsg})"], 400);
                return;
            } elseif ($provider === 'claude') {
                $endpoint = !empty($baseUrl) ? rtrim($baseUrl, '/') : 'https://api.anthropic.com/v1/messages';
                $modelName = $model ?: 'claude-3-5-sonnet-20241022';
                $body = json_encode([
                    'model'      => $modelName,
                    'max_tokens' => 200,
                    'messages'   => [['role' => 'user', 'content' => 'Halo, ini uji koneksi. Jawab "Koneksi Anthropic Claude Berhasil!" secara singkat.']]
                ]);

                $ch = curl_init($endpoint);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $body,
                    CURLOPT_HTTPHEADER     => [
                        "x-api-key: {$apiKey}",
                        "anthropic-version: 2023-06-01",
                        "content-type: application/json"
                    ],
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr  = curl_error($ch);
                curl_close($ch);

                if ($curlErr) {
                    $this->json(['success' => false, 'message' => 'cURL Error: ' . $curlErr], 500);
                    return;
                }

                $resData = json_decode($response, true);
                if ($httpCode >= 200 && $httpCode < 300 && isset($resData['content'][0]['text'])) {
                    $reply = $resData['content'][0]['text'];
                    $this->json(['success' => true, 'provider' => 'Anthropic Claude', 'model' => $modelName, 'reply' => trim($reply)]);
                } else {
                    $errMsg = $resData['error']['message'] ?? "HTTP {$httpCode}";
                    $this->json(['success' => false, 'message' => "Gagal respon dari Claude: {$errMsg}"], 400);
                }
                return;
            } else {
                // Standar OpenAI-Compatible API: OpenAI, DeepSeek, Groq, OpenRouter, Mistral, Together, Ollama, Custom
                $endpoint = '';
                if (!empty($baseUrl)) {
                    $endpoint = rtrim($baseUrl, '/');
                    if (!str_ends_with($endpoint, '/chat/completions')) {
                        $endpoint .= '/chat/completions';
                    }
                } else {
                    $endpoint = match ($provider) {
                        'deepseek'   => 'https://api.deepseek.com/v1/chat/completions',
                        'groq'       => 'https://api.groq.com/openai/v1/chat/completions',
                        'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
                        'mistral'    => 'https://api.mistral.ai/v1/chat/completions',
                        'together'   => 'https://api.together.xyz/v1/chat/completions',
                        'perplexity' => 'https://api.perplexity.ai/chat/completions',
                        'xai'        => 'https://api.x.ai/v1/chat/completions',
                        'ollama'     => 'http://localhost:11434/v1/chat/completions',
                        'lmstudio'   => 'http://localhost:1234/v1/chat/completions',
                        default      => 'https://api.openai.com/v1/chat/completions'
                    };
                }

                $modelName = $model ?: match ($provider) {
                    'deepseek'   => 'deepseek-chat',
                    'groq'       => 'llama-3.3-70b-versatile',
                    'openrouter' => 'deepseek/deepseek-chat',
                    'mistral'    => 'mistral-large-latest',
                    'together'   => 'meta-llama/Llama-3-70b-chat-hf',
                    'perplexity' => 'sonar',
                    'xai'        => 'grok-2-latest',
                    'ollama'     => 'llama3',
                    'lmstudio'   => 'local-model',
                    default      => 'gpt-4o-mini'
                };

                $body = json_encode([
                    'model'       => $modelName,
                    'messages'    => [
                        ['role' => 'user', 'content' => "Halo, ini pesan uji koneksi. Jawab 'Koneksi AI ({$provider}) Berhasil Aktif!' secara singkat."]
                    ],
                    'temperature' => 0.6
                ]);

                $headers = [
                    'Content-Type: application/json',
                    "Authorization: Bearer {$apiKey}"
                ];
                if ($provider === 'openrouter') {
                    $headers[] = 'HTTP-Referer: http://localhost/UJOL';
                    $headers[] = 'X-Title: SmartExam AI';
                }

                $ch = curl_init($endpoint);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $body,
                    CURLOPT_HTTPHEADER     => $headers,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr  = curl_error($ch);
                curl_close($ch);

                if ($curlErr) {
                    $this->json(['success' => false, 'message' => 'cURL Error: ' . $curlErr], 500);
                    return;
                }

                $resData = json_decode($response, true);
                if ($httpCode >= 200 && $httpCode < 300 && isset($resData['choices'][0]['message']['content'])) {
                    $reply = $resData['choices'][0]['message']['content'];
                    $this->json(['success' => true, 'provider' => ucfirst($provider), 'model' => $modelName, 'reply' => trim($reply)]);
                } else {
                    $errMsg = $resData['error']['message'] ?? (is_string($resData['error'] ?? null) ? $resData['error'] : "HTTP {$httpCode}");
                    $this->json(['success' => false, 'message' => "Gagal respon dari {$provider} ({$errMsg})"], 400);
                }
                return;
            }
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    private function renderModulePlaceholder(string $title, string $icon, string $desc): void
    {
        $this->view('admin/modules/placeholder', [
            'pageTitle'   => $title . ' - ' . APP_NAME,
            'moduleTitle' => $title,
            'moduleIcon'  => $icon,
            'moduleDesc'  => $desc,
            'user'        => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Halaman Profil Pengguna Administrator (/admin/profile)
     */
    public function profile(): void
    {
        $userId = (int)AuthMiddleware::id();
        $profile = $this->userModel->getUserProfile($userId);
        if (!$profile) {
            $profile = AuthMiddleware::user() ?? [];
        }

        $logs = $this->userModel->getAuditLogs($userId, 15);

        $this->view('admin/profile/index', [
            'pageTitle' => 'Profil Administrator - ' . APP_NAME,
            'profile'   => $profile,
            'logs'      => $logs,
            'user'      => AuthMiddleware::user()
        ], 'main');
    }

    /**
     * Proses Pembaruan Informasi Profil Admin (POST)
     */
    public function updateProfile(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/profile');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token keamanan CSRF tidak valid atau formulir kedaluwarsa.');
            $this->redirect('admin/profile');
            return;
        }

        $userId   = (int)AuthMiddleware::id();
        $name     = trim(SanitizeHelper::clean($this->getPost('name', '')));
        $username = strtolower(trim(SanitizeHelper::clean($this->getPost('username', ''))));
        $email    = strtolower(trim(SanitizeHelper::clean($this->getPost('email', ''))));

        if (empty($name) || empty($username) || empty($email)) {
            SessionHelper::setFlash('warning', 'Nama lengkap, username, dan email wajib diisi.');
            $this->redirect('admin/profile');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            SessionHelper::setFlash('danger', 'Format alamat email tidak valid.');
            $this->redirect('admin/profile');
            return;
        }

        if ($this->userModel->isUsernameTaken($username, $userId)) {
            SessionHelper::setFlash('danger', "Username <strong>{$username}</strong> sudah dipakai oleh pengguna lain.");
            $this->redirect('admin/profile');
            return;
        }

        if ($this->userModel->isEmailTaken($email, $userId)) {
            SessionHelper::setFlash('danger', "Email <strong>{$email}</strong> sudah dipakai oleh akun lain.");
            $this->redirect('admin/profile');
            return;
        }

        $success = $this->userModel->updateBasicProfile($userId, $name, $username, $email);

        if ($success) {
            AuthMiddleware::updateSessionUser([
                'name'     => $name,
                'username' => $username,
                'email'    => $email
            ]);
            $this->auditModel->logActivity($userId, 'UPDATE_PROFILE', "Administrator {$name} ({$username}) memperbarui data profil akun.");
            SessionHelper::setFlash('success', 'Data profil administrator berhasil diperbarui.');
        } else {
            SessionHelper::setFlash('danger', 'Gagal memperbarui profil. Terjadi kesalahan pada sistem basis data.');
        }

        $this->redirect('admin/profile');
    }

    /**
     * Proses Pembaruan Kata Sandi Admin (POST)
     */
    public function updatePassword(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/profile');
            return;
        }

        if (!CsrfHelper::validateToken($this->getPost('csrf_token'))) {
            SessionHelper::setFlash('danger', 'Token CSRF tidak valid.');
            $this->redirect('admin/profile');
            return;
        }

        $userId          = (int)AuthMiddleware::id();
        $currentPassword = (string)$this->getPost('current_password', '');
        $newPassword     = (string)$this->getPost('new_password', '');
        $confirmPassword = (string)$this->getPost('confirm_password', '');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            SessionHelper::setFlash('warning', 'Semua kolom kata sandi wajib diisi.');
            $this->redirect('admin/profile');
            return;
        }

        if (!$this->userModel->verifyPassword($userId, $currentPassword)) {
            SessionHelper::setFlash('danger', 'Kata sandi saat ini yang Anda masukkan salah.');
            $this->redirect('admin/profile');
            return;
        }

        if (strlen($newPassword) < 6) {
            SessionHelper::setFlash('danger', 'Kata sandi baru minimal harus terdiri dari 6 karakter.');
            $this->redirect('admin/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            SessionHelper::setFlash('danger', 'Konfirmasi kata sandi baru tidak cocok.');
            $this->redirect('admin/profile');
            return;
        }

        $success = $this->userModel->updateUserPassword($userId, $newPassword);

        if ($success) {
            $this->auditModel->logActivity($userId, 'CHANGE_PASSWORD', 'Administrator berhasil memperbarui kata sandi akun.');
            SessionHelper::setFlash('success', 'Kata sandi berhasil diperbarui dengan aman.');
        } else {
            SessionHelper::setFlash('danger', 'Gagal memperbarui kata sandi. Silakan coba kembali.');
        }

        $this->redirect('admin/profile');
    }
}
