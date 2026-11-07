<?php
/**
 * Test Suite: Fitur User Profil di Setiap Aktor (Admin, Guru, Murid)
 * SmartExam AI
 */

require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Helpers/UrlHelper.php';
require_once __DIR__ . '/../app/Helpers/SanitizeHelper.php';
require_once __DIR__ . '/../app/Helpers/SettingHelper.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . DIRECTORY_SEPARATOR;
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    if (file_exists($file)) require_once $file;
});

use App\Helpers\SessionHelper;
use App\Helpers\AuthMiddleware;
use App\Models\UserModel;
use App\Models\TeacherModel;
use App\Models\StudentModel;

SessionHelper::start();

echo "======================================================\n";
echo " TEST SUITE: USER PROFIL (ADMIN / GURU / MURID)\n";
echo "======================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $desc, bool $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] " . $desc . "\n";
        $passCount++;
    } else {
        echo " [FAIL] " . $desc . "\n";
        $failCount++;
    }
}

// 1. Test UserModel methods
echo "--- 1. Testing UserModel Profile Methods ---\n";
$userModel = new UserModel();

// Test getUserProfile for Admin (ID 1)
$adminProfile = $userModel->getUserProfile(1);
assertTest("UserModel::getUserProfile(1) returns admin data", $adminProfile !== null && ($adminProfile['role'] ?? '') === 'admin');

// Test getUserProfile for Guru (ID 2)
$guruProfile = $userModel->getUserProfile(2);
assertTest("UserModel::getUserProfile(2) returns guru data", $guruProfile !== null && ($guruProfile['role'] ?? '') === 'guru');

// Test getUserProfile for Murid (ID 3)
$siswaProfile = $userModel->getUserProfile(3);
assertTest("UserModel::getUserProfile(3) returns siswa data", $siswaProfile !== null && ($siswaProfile['role'] ?? '') === 'siswa');

// Test verifyPassword
$verifyAdmin = $userModel->verifyPassword(1, 'admin123');
assertTest("UserModel::verifyPassword(1, 'admin123') is true", $verifyAdmin === true);

$verifyFalse = $userModel->verifyPassword(1, 'wrongpassword');
assertTest("UserModel::verifyPassword(1, 'wrongpassword') is false", $verifyFalse === false);

// 2. Test TeacherModel stats
echo "\n--- 2. Testing TeacherModel Stats ---\n";
$teacherModel = new TeacherModel();
$teacherStats = $teacherModel->getStats(1);
assertTest("TeacherModel::getStats returns array with keys", is_array($teacherStats) && isset($teacherStats['total_banks']) && isset($teacherStats['total_exams']));

// 3. Test StudentModel stats
echo "\n--- 3. Testing StudentModel Stats ---\n";
$studentModel = new StudentModel();
$studentStats = $studentModel->getStats(1);
assertTest("StudentModel::getStats returns array with keys", is_array($studentStats) && isset($studentStats['total_exams']) && isset($studentStats['completed_exams']) && isset($studentStats['avg_score']));

// 4. Test View Files Exist
echo "\n--- 4. Testing View Files Existence ---\n";
$adminViewFile = APP_PATH . '/Views/admin/profile/index.php';
$teacherViewFile = APP_PATH . '/Views/teacher/profile/index.php';
$studentViewFile = APP_PATH . '/Views/student/profile/index.php';

assertTest("Admin Profile View file exists", file_exists($adminViewFile));
assertTest("Teacher Profile View file exists", file_exists($teacherViewFile));
assertTest("Student Profile View file exists", file_exists($studentViewFile));

// 5. Test View Rendering without Errors (Buffer Check)
echo "\n--- 5. Testing View File Rendering ---\n";

// Render Admin View in buffer
ob_start();
try {
    $profile = $adminProfile;
    $logs = $userModel->getAuditLogs(1, 5);
    include $adminViewFile;
    $adminHtml = ob_get_clean();
    $adminRenderOk = strpos($adminHtml, 'Profil Administrator') !== false;
} catch (\Throwable $e) {
    ob_end_clean();
    $adminRenderOk = false;
    echo " Admin View Error: " . $e->getMessage() . "\n";
}
assertTest("Admin Profile View renders successfully with title", $adminRenderOk);

// Render Teacher View in buffer
ob_start();
try {
    $profile = $guruProfile;
    $teacher = [
        'id' => 1,
        'nip' => '198904122015031002',
        'nuptk' => '9876543210123456',
        'gender' => 'L',
        'phone' => '081234567890',
        'address' => 'Jl. Pendidikan No. 45'
    ];
    $stats = $teacherStats;
    $banks = [];
    $exams = [];
    $logs = [];
    include $teacherViewFile;
    $teacherHtml = ob_get_clean();
    $teacherRenderOk = strpos($teacherHtml, 'Profil Guru Pengajar') !== false;
} catch (\Throwable $e) {
    ob_end_clean();
    $teacherRenderOk = false;
    echo " Teacher View Error: " . $e->getMessage() . "\n";
}
assertTest("Teacher Profile View renders successfully with title", $teacherRenderOk);

// Render Student View in buffer
ob_start();
try {
    $student = [
        'id' => 1,
        'name' => 'Ahmad Fauzi Pratama',
        'username' => 'siswa',
        'email' => 'fauzi.siswa@smartexam.sch.id',
        'nisn' => '0068192341',
        'class_name' => 'XII MIPA 1',
        'gender' => 'L',
        'phone' => '089876543210',
        'address' => 'Jl. Anggrek No. 12',
        'grade_level' => '12',
        'major' => 'MIPA',
        'academic_year' => '2026/2027'
    ];
    $stats = $studentStats;
    $participations = [];
    $logs = [];
    include $studentViewFile;
    $studentHtml = ob_get_clean();
    $studentRenderOk = strpos($studentHtml, 'Profil Peserta Didik') !== false;
} catch (\Throwable $e) {
    ob_end_clean();
    $studentRenderOk = false;
    echo " Student View Error: " . $e->getMessage() . "\n";
}
assertTest("Student Profile View renders successfully with title", $studentRenderOk);

// 6. Test App Routing Table
echo "\n--- 6. Testing App Routing Table ---\n";
$appClass = new \ReflectionClass(\App\Core\App::class);
$routesProp = $appClass->getProperty('routes');
$routesProp->setAccessible(true);
$defaultProps = $appClass->getDefaultProperties();
$routes = $defaultProps['routes'];
assertTest("Route 'profile' is registered to DashboardController@profile", isset($routes['profile']) && $routes['profile']['controller'] === 'DashboardController');
assertTest("Route 'admin/profile' is registered to AdminController@profile", isset($routes['admin/profile']) && $routes['admin/profile']['controller'] === 'AdminController');
assertTest("Route 'admin/updateProfile' is registered to AdminController@updateProfile", isset($routes['admin/updateProfile']) && $routes['admin/updateProfile']['controller'] === 'AdminController');
assertTest("Route 'admin/updatePassword' is registered to AdminController@updatePassword", isset($routes['admin/updatePassword']) && $routes['admin/updatePassword']['controller'] === 'AdminController');
assertTest("Route 'teacher/profile' is registered to TeacherController@profile", isset($routes['teacher/profile']) && $routes['teacher/profile']['controller'] === 'TeacherController');
assertTest("Route 'teacher/updateProfile' is registered to TeacherController@updateProfile", isset($routes['teacher/updateProfile']) && $routes['teacher/updateProfile']['controller'] === 'TeacherController');
assertTest("Route 'teacher/updatePassword' is registered to TeacherController@updatePassword", isset($routes['teacher/updatePassword']) && $routes['teacher/updatePassword']['controller'] === 'TeacherController');
assertTest("Route 'student/profile' is registered to StudentController@profile", isset($routes['student/profile']) && $routes['student/profile']['controller'] === 'StudentController');
assertTest("Route 'student/updateProfile' is registered to StudentController@updateProfile", isset($routes['student/updateProfile']) && $routes['student/updateProfile']['controller'] === 'StudentController');
assertTest("Route 'student/updatePassword' is registered to StudentController@updatePassword", isset($routes['student/updatePassword']) && $routes['student/updatePassword']['controller'] === 'StudentController');

// 7. Test AuthMiddleware::updateSessionUser
echo "\n--- 7. Testing AuthMiddleware::updateSessionUser ---\n";
AuthMiddleware::login([
    'id' => 1,
    'name' => 'Nama Lama',
    'username' => 'admin_lama',
    'email' => 'old@mail.com',
    'role' => 'admin'
]);
AuthMiddleware::updateSessionUser([
    'name' => 'Nama Baru',
    'email' => 'new@mail.com'
]);
$updatedUser = AuthMiddleware::user();
assertTest("AuthMiddleware::updateSessionUser updates session name and email", 
    $updatedUser['name'] === 'Nama Baru' && 
    $updatedUser['email'] === 'new@mail.com' && 
    $updatedUser['username'] === 'admin_lama'
);

echo "\n======================================================\n";
echo " TEST SUMMARY: {$passCount} PASSED, {$failCount} FAILED\n";
echo "======================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
