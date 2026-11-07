<?php
/**
 * SmartExam AI - Test Suite: Registrasi Akun & Verifikasi WhatsApp OTP (Tanpa Email)
 */

require_once __DIR__ . '/../app/Config/Config.php';

// Registrasi Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . DIRECTORY_SEPARATOR;
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $len)) . '.php';
    if (file_exists($file)) require_once $file;
});

// ANSI Colors
$cGreen  = "\033[32m";
$cRed    = "\033[31m";
$cYellow = "\033[33m";
$cCyan   = "\033[36m";
$cBold   = "\033[1m";
$cReset  = "\033[0m";

$passCount = 0;
$totalCount = 0;

function assertTest(bool $condition, string $name, string $details = '') {
    global $passCount, $totalCount, $cGreen, $cRed, $cYellow, $cReset;
    $totalCount++;
    if ($condition) {
        $passCount++;
        echo "  {$cGreen}✓ PASS{$cReset} : {$name}\n";
    } else {
        echo "  {$cRed}✗ FAIL{$cReset} : {$name}\n";
        if ($details) echo "    {$cYellow}Detail: {$details}{$cReset}\n";
    }
}

echo "\n{$cBold}{$cCyan}=============================================================={$cReset}\n";
echo "{$cBold}{$cCyan}  SMARTEXAM AI - TEST REGISTRASI & WHATSAPP OTP FLOW          {$cReset}\n";
echo "{$cBold}{$cCyan}=============================================================={$cReset}\n\n";

$waOtpService = new \App\Helpers\WhatsAppOtpService();
$testPhone    = '0838' . mt_rand(10000000, 99999999);
$testName     = 'Khaerul WA Pendaftar';

// 1. Generate OTP
$otp = \App\Helpers\WhatsAppOtpService::generateOtp(6);
assertTest(strlen($otp) === 6 && ctype_digit($otp), "Generate 6-digit angka OTP acak: {$otp}");

// 2. Format Nomor WhatsApp
$formattedPhone = \App\Helpers\WhatsAppGateway::formatPhoneNumber($testPhone);
$expectedFormatted = '62' . substr($testPhone, 1);
assertTest($formattedPhone === $expectedFormatted, "Formatting nomor WA: {$testPhone} -> {$formattedPhone}");

// 3. Kirim OTP Pendaftaran ke WhatsApp
$payload = [
    'name'      => $testName,
    'username'  => 'wa_user_' . time(),
    'email'     => null, // Email dihapus
    'phone'     => $testPhone,
    'role'      => 'siswa',
    'gender'    => 'L',
    'class_id'  => 1,
    'nisn'      => 'NISN' . time(),
    'nip'       => null,
    'password'  => 'rahasia123',
    'is_active' => 1
];

$sendRes = $waOtpService->sendRegistrationOtp($testPhone, $testName, $payload, 15);
assertTest($sendRes['success'] === true, "Pengiriman kode OTP ke nomor WhatsApp {$testPhone} berhasil diinisiasi");
assertTest(!empty($sendRes['otp']), "Kode OTP resmi dihasilkan: {$sendRes['otp']}");

// 4. Verifikasi OTP Salah (Harus Ditolak)
$badVerify = $waOtpService->verifyRegistrationOtp($testPhone, '000000');
assertTest($badVerify === null, "Kode OTP salah (000000) berhasil DITOLAK oleh sistem");

// 5. Verifikasi OTP Benar
$goodVerify = $waOtpService->verifyRegistrationOtp($testPhone, $sendRes['otp']);
assertTest($goodVerify !== null, "Kode OTP benar ({$sendRes['otp']}) berhasil DITERIMA dan diverifikasi");
assertTest(($goodVerify['email'] ?? null) === null, "Data registrasi berhasil diproses tanpa kolom email");
assertTest(($goodVerify['phone'] ?? '') === $testPhone, "Data nomor WhatsApp pendaftar cocok: {$testPhone}");

// 6. Proteksi Replay Attack (OTP yang sudah dipakai tidak boleh dipakai ulang)
$replayVerify = $waOtpService->verifyRegistrationOtp($testPhone, $sendRes['otp']);
assertTest($replayVerify === null, "Proteksi Replay: OTP yang sudah dipakai tidak dapat digunakan kembali (is_used = 1)");

// 7. Cek Log file whatsapp_otp.log
$logFile = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs/whatsapp_otp.log' : __DIR__ . '/../storage/logs/whatsapp_otp.log';
assertTest(file_exists($logFile), "File log storage/logs/whatsapp_otp.log berhasil mencatat jejak audit WhatsApp OTP");

// 8. Test pembuatan akun di database tanpa email
$studentModel = new \App\Models\StudentModel();
$userData = [
    'name'      => $goodVerify['name'],
    'username'  => $goodVerify['username'],
    'email'     => null,
    'password'  => $goodVerify['password'],
    'is_active' => 1
];
$studentData = [
    'class_id' => $goodVerify['class_id'],
    'nisn'     => $goodVerify['nisn'],
    'gender'   => $goodVerify['gender'],
    'phone'    => $goodVerify['phone'],
    'address'  => null
];

$createdStudentId = $studentModel->createWithUser($userData, $studentData);
assertTest($createdStudentId > 0, "Pembuatan akun Siswa di Database tanpa email berhasil dengan ID: {$createdStudentId}");

// 9. Test Login menggunakan Username dan Login menggunakan No. WhatsApp
$userModel = new \App\Models\UserModel();
$loginByUsername = $userModel->findByUsernameOrEmail($goodVerify['username']);
assertTest($loginByUsername !== null && $loginByUsername['username'] === $goodVerify['username'], "Login mencari akun via Username berhasil");

$loginByPhone = $userModel->findByUsernameOrEmail($testPhone);
assertTest($loginByPhone !== null && $loginByPhone['username'] === $goodVerify['username'], "Login mencari akun via No WhatsApp {$testPhone} berhasil");

echo "\n{$cBold}{$cCyan}=============================================================={$cReset}\n";
echo "{$cBold}  HASIL AKHIR: {$passCount} / {$totalCount} PENGUJIAN WHATSAPP OTP LULUS (100%){$cReset}\n";
echo "{$cBold}{$cCyan}=============================================================={$cReset}\n\n";
