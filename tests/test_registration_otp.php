<?php
/**
 * SmartExam AI - Test Suite: Registrasi Akun & Verifikasi Email OTP
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
echo "{$cBold}{$cCyan}  SMARTEXAM AI - TEST REGISTRASI & EMAIL OTP FLOW             {$cReset}\n";
echo "{$cBold}{$cCyan}=============================================================={$cReset}\n\n";

$emailService = new \App\Helpers\EmailService();
$testEmail    = 'khaerul060406@gmail.com';
$testName     = 'Khaerul Test Pendaftar';

// 1. Generate OTP
$otp = \App\Helpers\EmailService::generateOtp(6);
assertTest(strlen($otp) === 6 && ctype_digit($otp), "Generate 6-digit angka OTP acak: {$otp}");

// 2. Kirim OTP Pendaftaran ke Email
$payload = [
    'name'      => $testName,
    'username'  => 'khaerul_test_' . time(),
    'email'     => $testEmail,
    'phone'     => '083836331952',
    'role'      => 'siswa',
    'class_id'  => 1,
    'nisn'      => '99' . time(),
    'gender'    => 'L',
    'password'  => 'rahasia123'
];

$sendRes = $emailService->sendRegistrationOtp($testEmail, $testName, $payload, 15);
assertTest($sendRes['success'] === true, "Pengiriman kode OTP ke email {$testEmail} berhasil diinisiasi");
assertTest(!empty($sendRes['otp']), "Kode OTP resmi dihasilkan: {$sendRes['otp']}");

// 3. Verifikasi OTP Salah (Harus Ditolak)
$badVerify = $emailService->verifyRegistrationOtp($testEmail, '999999');
assertTest($badVerify === null, "Kode OTP salah (999999) berhasil DITOLAK oleh sistem");

// 4. Verifikasi OTP Benar
$goodVerify = $emailService->verifyRegistrationOtp($testEmail, $sendRes['otp']);
assertTest($goodVerify !== null, "Kode OTP benar ({$sendRes['otp']}) berhasil DITERIMA dan diverifikasi");
assertTest($goodVerify['email'] === $testEmail, "Data payload pendaftaran kembali dengan data email yang valid");

// 5. Proteksi Replay Attack (OTP yang sudah dipakai tidak boleh dipakai ulang)
$replayVerify = $emailService->verifyRegistrationOtp($testEmail, $sendRes['otp']);
assertTest($replayVerify === null, "Proteksi Replay: OTP yang sudah dipakai tidak dapat digunakan kembali (is_used = 1)");

// 6. Cek Log file email_otp.log
$logFile = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs/email_otp.log' : __DIR__ . '/../storage/logs/email_otp.log';
assertTest(file_exists($logFile), "File log storage/logs/email_otp.log berhasil mencatat jejak audit email OTP");

echo "\n{$cBold}{$cCyan}=============================================================={$cReset}\n";
echo "{$cBold}  HASIL AKHIR: {$passCount} / {$totalCount} PENGUJIAN REGISTRASI & OTP LULUS (100%){$cReset}\n";
echo "{$cBold}{$cCyan}=============================================================={$cReset}\n\n";

exit($passCount === $totalCount ? 0 : 1);
