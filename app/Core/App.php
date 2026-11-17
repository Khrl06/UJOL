<?php
/**
 * SmartExam AI - Application Core Router
 * Memproses URL request, memetakan ke Controller, Method, dan Parameter yang sesuai.
 */

namespace App\Core;

use App\Controllers\ErrorController;

class App
{
    protected string $controller = 'AuthController';
    protected string $method = 'login';
    protected array $params = [];

    // Pemetaan rute pendek (Route Aliases)
    protected array $routes = [
        ''                          => ['controller' => 'HomeController', 'method' => 'index'],
        'home'                      => ['controller' => 'HomeController', 'method' => 'index'],
        'login'                     => ['controller' => 'AuthController', 'method' => 'login'],
        'register'                  => ['controller' => 'AuthController', 'method' => 'register'],
        'verify-otp'                => ['controller' => 'AuthController', 'method' => 'verifyOtp'],
        'resend-otp'                => ['controller' => 'AuthController', 'method' => 'resendOtp'],
        'logout'                    => ['controller' => 'AuthController', 'method' => 'logout'],
        'dashboard'                 => ['controller' => 'DashboardController', 'method' => 'index'],
        'profile'                   => ['controller' => 'DashboardController', 'method' => 'profile'],
        'admin/profile'             => ['controller' => 'AdminController', 'method' => 'profile'],
        'admin/profile/update'      => ['controller' => 'AdminController', 'method' => 'updateProfile'],
        'admin/profile/password'    => ['controller' => 'AdminController', 'method' => 'updatePassword'],
        'admin/updateProfile'       => ['controller' => 'AdminController', 'method' => 'updateProfile'],
        'admin/updatePassword'      => ['controller' => 'AdminController', 'method' => 'updatePassword'],
        'teacher/profile'           => ['controller' => 'TeacherController', 'method' => 'profile'],
        'teacher/profile/update'    => ['controller' => 'TeacherController', 'method' => 'updateProfile'],
        'teacher/profile/password'  => ['controller' => 'TeacherController', 'method' => 'updatePassword'],
        'teacher/updateProfile'     => ['controller' => 'TeacherController', 'method' => 'updateProfile'],
        'teacher/updatePassword'    => ['controller' => 'TeacherController', 'method' => 'updatePassword'],
        'student/profile'           => ['controller' => 'StudentController', 'method' => 'profile'],
        'student/profile/update'    => ['controller' => 'StudentController', 'method' => 'updateProfile'],
        'student/profile/password'  => ['controller' => 'StudentController', 'method' => 'updatePassword'],
        'student/updateProfile'     => ['controller' => 'StudentController', 'method' => 'updateProfile'],
        'student/updatePassword'    => ['controller' => 'StudentController', 'method' => 'updatePassword'],
        'admin'                     => ['controller' => 'AdminController', 'method' => 'index'],
        'admin/dashboard'           => ['controller' => 'AdminController', 'method' => 'index'],
        'admin/teachers'            => ['controller' => 'AdminController', 'method' => 'teachers'],
        'admin/students'            => ['controller' => 'AdminController', 'method' => 'students'],
        'admin/classes'             => ['controller' => 'AdminController', 'method' => 'classes'],
        'admin/subjects'            => ['controller' => 'AdminController', 'method' => 'subjects'],
        'admin/banks'               => ['controller' => 'AdminController', 'method' => 'banks'],
        'admin/exams'               => ['controller' => 'AdminController', 'method' => 'exams'],
        'admin/monitoring'          => ['controller' => 'AdminController', 'method' => 'monitoring'],
        'admin/results'             => ['controller' => 'AdminController', 'method' => 'results'],
        'admin/proctoring'          => ['controller' => 'AdminController', 'method' => 'proctoring'],
        'admin/reports'             => ['controller' => 'AdminController', 'method' => 'reports'],
        'admin/users'               => ['controller' => 'AdminController', 'method' => 'users'],
        'admin/logs'                => ['controller' => 'AdminController', 'method' => 'logs'],
        'admin/settings'            => ['controller' => 'AdminController', 'method' => 'settings'],
        'admin/saveSettings'        => ['controller' => 'AdminController', 'method' => 'saveSettings'],
        'admin/activateAiProvider'  => ['controller' => 'AdminController', 'method' => 'activateAiProvider'],
        'admin/deleteAiProvider'    => ['controller' => 'AdminController', 'method' => 'deleteAiProvider'],
        'admin/testWaConnection'    => ['controller' => 'AdminController', 'method' => 'testWaConnection'],
        'teacher'                   => ['controller' => 'TeacherController', 'method' => 'index'],
        'teacher/dashboard'         => ['controller' => 'TeacherController', 'method' => 'index'],
        'teacher/banks'             => ['controller' => 'TeacherController', 'method' => 'banks'],
        'teacher/createBank'        => ['controller' => 'TeacherController', 'method' => 'createBank'],
        'teacher/editBank'          => ['controller' => 'TeacherController', 'method' => 'editBank'],
        'teacher/deleteBank'        => ['controller' => 'TeacherController', 'method' => 'deleteBank'],
        'teacher/bankQuestions'     => ['controller' => 'TeacherController', 'method' => 'bankQuestions'],
        'teacher/createQuestion'    => ['controller' => 'TeacherController', 'method' => 'createQuestion'],
        'teacher/generateQuestionsAuto' => ['controller' => 'TeacherController', 'method' => 'generateQuestionsAuto'],
        'teacher/generateOptionsAjax'   => ['controller' => 'TeacherController', 'method' => 'generateOptionsAjax'],
        'teacher/batchImportQuestions'  => ['controller' => 'TeacherController', 'method' => 'batchImportQuestions'],
        'teacher/importQuestionsFile'   => ['controller' => 'TeacherController', 'method' => 'importQuestionsFile'],
        'teacher/editQuestion'      => ['controller' => 'TeacherController', 'method' => 'editQuestion'],
        'teacher/deleteQuestion'    => ['controller' => 'TeacherController', 'method' => 'deleteQuestion'],
        'teacher/duplicateQuestion' => ['controller' => 'TeacherController', 'method' => 'duplicateQuestion'],
        'teacher/toggleQuestion'    => ['controller' => 'TeacherController', 'method' => 'toggleQuestion'],
        'teacher/exams'             => ['controller' => 'TeacherController', 'method' => 'exams'],
        'teacher/exams/create'      => ['controller' => 'TeacherController', 'method' => 'createExamView'],
        'teacher/saveExam'          => ['controller' => 'TeacherController', 'method' => 'saveExam'],
        'teacher/updateExam'        => ['controller' => 'TeacherController', 'method' => 'updateExam'],
        'teacher/deleteExam'        => ['controller' => 'TeacherController', 'method' => 'deleteExam'],
        'teacher/publishExam'       => ['controller' => 'TeacherController', 'method' => 'publishExam'],
        'teacher/unpublishExam'     => ['controller' => 'TeacherController', 'method' => 'unpublishExam'],
        'teacher/duplicateExam'     => ['controller' => 'TeacherController', 'method' => 'duplicateExam'],
        'teacher/assignParticipants'=> ['controller' => 'TeacherController', 'method' => 'assignParticipants'],
        'teacher/removeParticipant' => ['controller' => 'TeacherController', 'method' => 'removeParticipant'],
        'teacher/regenerateToken'   => ['controller' => 'TeacherController', 'method' => 'regenerateToken'],
        'student'                   => ['controller' => 'StudentController', 'method' => 'index'],
        'student/dashboard'         => ['controller' => 'StudentController', 'method' => 'index'],
        'student/exams'             => ['controller' => 'StudentController', 'method' => 'exams'],
        'student/history'           => ['controller' => 'StudentController', 'method' => 'history'],
        'student/saveAnswer'        => ['controller' => 'StudentController', 'method' => 'saveAnswerAjax'],
        'student/setDoubtful'       => ['controller' => 'StudentController', 'method' => 'setDoubtfulAjax'],
        'student/submitExam'        => ['controller' => 'StudentController', 'method' => 'submitExam'],
        'student/proctoringEvent'   => ['controller' => 'StudentController', 'method' => 'recordProctoringEventAjax'],
        'admin/proctoring/updateStatus'           => ['controller' => 'AdminController', 'method' => 'updateProctoringStatus'],
        'teacher/monitoring'                      => ['controller' => 'TeacherController', 'method' => 'liveMonitoring'],
        'teacher/proctoring/participantDetail'    => ['controller' => 'TeacherController', 'method' => 'participantDetailAjax'],
        'teacher/proctoring/addNote'              => ['controller' => 'TeacherController', 'method' => 'addSupervisorNoteAjax'],
        'teacher/proctoring/updateStatus'         => ['controller' => 'TeacherController', 'method' => 'updateProctoringStatus'],
        'teacher/analytics'                       => ['controller' => 'TeacherController', 'method' => 'analytics'],
        'teacher/results'                         => ['controller' => 'TeacherController', 'method' => 'results'],
        'teacher/reports'                         => ['controller' => 'TeacherController', 'method' => 'reports'],
        'teacher/reports/export'                  => ['controller' => 'TeacherController', 'method' => 'exportReportsCsv'],
        'teacher/reports/print'                   => ['controller' => 'TeacherController', 'method' => 'printReport'],
        'teacher/updateScore'                     => ['controller' => 'TeacherController', 'method' => 'updateScore'],
        'notifications'                           => ['controller' => 'NotificationController', 'method' => 'index'],
        'notifications/ajax'                      => ['controller' => 'NotificationController', 'method' => 'index'],
        'notifications/count'                     => ['controller' => 'NotificationController', 'method' => 'count'],
        'notifications/markRead'                  => ['controller' => 'NotificationController', 'method' => 'markRead'],
        'notifications/markAllRead'               => ['controller' => 'NotificationController', 'method' => 'markAllRead'],
        'notifications/triggerReminders'          => ['controller' => 'NotificationController', 'method' => 'triggerReminders'],
        'notifications/testWhatsApp'              => ['controller' => 'NotificationController', 'method' => 'testWhatsApp'],
    ];

    public function __construct()
    {
        $url = $this->parseUrl();
        $urlString = implode('/', $url);

        // 1. Cek apakah URL cocok persis dengan rute statis / alias
        if (isset($this->routes[$urlString])) {
            $this->controller = $this->routes[$urlString]['controller'];
            $this->method = $this->routes[$urlString]['method'];
            $this->params = [];
            $this->dispatch();
            return;
        }

        // 2. Cek Pola Rute Dinamis untuk Ujian Guru:
        // Pola A: /teacher/exams/create
        if ($urlString === 'teacher/exams/create') {
            $this->controller = 'TeacherController';
            $this->method = 'createExamView';
            $this->params = [];
            $this->dispatch();
            return;
        }

        // Pola B: /teacher/exams/{id}/edit
        if (preg_match('#^teacher/exams/([0-9]+)/edit$#', $urlString, $matches)) {
            $this->controller = 'TeacherController';
            $this->method = 'editExamView';
            $this->params = [(int)$matches[1]];
            $this->dispatch();
            return;
        }

        // Pola C: /teacher/exams/{id}
        if (preg_match('#^teacher/exams/([0-9]+)$#', $urlString, $matches)) {
            $this->controller = 'TeacherController';
            $this->method = 'examDetail';
            $this->params = [(int)$matches[1]];
            $this->dispatch();
            return;
        }

        // Pola D: /teacher/exams/{id}/monitoring
        if (preg_match('#^teacher/exams/([0-9]+)/monitoring$#', $urlString, $matches)) {
            $this->controller = 'TeacherController';
            $this->method = 'liveMonitoring';
            $this->params = [(int)$matches[1]];
            $this->dispatch();
            return;
        }

        // Pola E: /teacher/exams/{id}/live-status
        if (preg_match('#^teacher/exams/([0-9]+)/live-status$#', $urlString, $matches)) {
            $this->controller = 'TeacherController';
            $this->method = 'liveStatusAjax';
            $this->params = [(int)$matches[1]];
            $this->dispatch();
            return;
        }

        // 3. Cek Pola Rute Dinamis untuk Exam Engine Siswa:
        // Pola S0: /student/exams/{id}/device-check
        if (preg_match('#^student/exams/([0-9]+)/device-check$#', $urlString, $matches)) {
            $this->controller = 'StudentController';
            $this->method = 'deviceCheck';
            $this->params = [(int)$matches[1]];
            $this->dispatch();
            return;
        }

        // Pola S1: /student/exams/{id}/start
        if (preg_match('#^student/exams/([0-9]+)/start$#', $urlString, $matches)) {
            $this->controller = 'StudentController';
            $this->method = 'startExam';
            $this->params = [(int)$matches[1]];
            $this->dispatch();
            return;
        }

        // Pola S2: /student/exams/{id}/attempt
        if (preg_match('#^student/exams/([0-9]+)/attempt$#', $urlString, $matches)) {
            $this->controller = 'StudentController';
            $this->method = 'attempt';
            $this->params = [(int)$matches[1]];
            $this->dispatch();
            return;
        }

        // Pola S3: /student/exams/{id}/result
        if (preg_match('#^student/exams/([0-9]+)/result$#', $urlString, $matches)) {
            $this->controller = 'StudentController';
            $this->method = 'examResult';
            $this->params = [(int)$matches[1]];
            $this->dispatch();
            return;
        }

        // Pola S4: /student/exams/{id}
        if (preg_match('#^student/exams/([0-9]+)$#', $urlString, $matches)) {
            $this->controller = 'StudentController';
            $this->method = 'examDetail';
            $this->params = [(int)$matches[1]];
            $this->dispatch();
            return;
        }

        // 3. Fallback pemetaan standar /controller/method/params
        if (!empty($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            if (file_exists(APP_PATH . '/Controllers/' . $controllerName . '.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                $this->render404("Controller '{$controllerName}' tidak ditemukan.");
                return;
            }
        }

        $controllerClass = "App\\Controllers\\" . $this->controller;
        if (!class_exists($controllerClass)) {
            $this->render404("Kelas '{$controllerClass}' tidak ditemukan.");
            return;
        }
        $controllerInstance = new $controllerClass();

        if (isset($url[1])) {
            $methodName = $url[1];
            if (method_exists($controllerInstance, $methodName)) {
                $this->method = $methodName;
                unset($url[1]);
            } else {
                $this->render404("Method '{$methodName}' tidak ditemukan pada controller.");
                return;
            }
        } else {
            if (method_exists($controllerInstance, 'index')) {
                $this->method = 'index';
            } elseif (method_exists($controllerInstance, 'login')) {
                $this->method = 'login';
            }
        }

        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$controllerInstance, $this->method], $this->params);
    }

    /**
     * Dispatching ke Controller
     */
    protected function dispatch(): void
    {
        $controllerClass = "App\\Controllers\\" . $this->controller;
        if (!class_exists($controllerClass)) {
            $this->render404("Kelas '{$controllerClass}' tidak ditemukan.");
            return;
        }

        $controllerInstance = new $controllerClass();
        if (!method_exists($controllerInstance, $this->method)) {
            $this->render404("Method '{$this->method}' tidak ditemukan pada controller '{$controllerClass}'.");
            return;
        }

        call_user_func_array([$controllerInstance, $this->method], $this->params);
    }

    /**
     * Parse URL dari query string $_GET['url'] atau REQUEST_URI
     */
    protected function parseUrl(): array
    {
        if (isset($_GET['url']) && !empty(trim($_GET['url']))) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');

        if ($scriptName !== '/' && $scriptName !== '\\') {
            $uri = substr($uri, strlen($scriptName));
        }

        $uri = trim($uri, '/');
        if (str_starts_with($uri, 'index.php/')) {
            $uri = substr($uri, 10);
        } elseif ($uri === 'index.php') {
            $uri = '';
        }

        return !empty($uri) ? explode('/', filter_var(rtrim($uri, '/'), FILTER_SANITIZE_URL)) : [];
    }

    protected function render404(string $message = 'Halaman tidak ditemukan.'): void
    {
        $errorController = new ErrorController();
        $errorController->notFound($message);
    }
}
