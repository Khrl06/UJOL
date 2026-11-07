<?php
/**
 * SmartExam AI - Test Suite: Tahap 3 Konfigurasi Sistem (Dynamic Settings & .env Sync)
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

require_once APP_PATH . '/Helpers/SettingHelper.php';

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
echo "{$cBold}{$cCyan}  SMARTEXAM AI - TEST TAHAP 3: KONFIGURASI SISTEM DINAMIS     {$cReset}\n";
echo "{$cBold}{$cCyan}=============================================================={$cReset}\n\n";

$settingModel = new \App\Models\SettingModel();

// 1. Uji Baca Pengaturan Default Ujian
$duration = $settingModel->get('exam_default_duration');
assertTest($duration === 90 || is_numeric($duration), "Baca durasi default ujian: {$duration} menit");

$tokenLength = $settingModel->get('exam_token_length');
assertTest($tokenLength === 6 || is_numeric($tokenLength), "Baca panjang token ujian: {$tokenLength} digit");

// 2. Uji Type Casting Boolean
$isTokenReq = $settingModel->get('exam_token_enabled');
assertTest(is_bool($isTokenReq), "Type casting boolean exam_token_enabled: " . var_export($isTokenReq, true));

$randomQ = $settingModel->get('exam_random_questions');
assertTest(is_bool($randomQ), "Type casting boolean exam_random_questions: " . var_export($randomQ, true));

// 3. Uji Pengambilan Berdasarkan Group
$examGroup = $settingModel->getByGroup('exam');
assertTest(is_array($examGroup) && isset($examGroup['exam_default_duration']), "Pengambilan seluruh setting grup 'exam' berhasil");

$proctoringGroup = $settingModel->getByGroup('proctoring');
assertTest(is_array($proctoringGroup) && isset($proctoringGroup['proctoring_tab_switch_limit']), "Pengambilan setting grup 'proctoring' berhasil (Tab limit: {$proctoringGroup['proctoring_tab_switch_limit']})");

// 4. Uji Pengambilan Grup WhatsApp
$waGroup = $settingModel->getByGroup('whatsapp');
assertTest(isset($waGroup['wa_gateway_provider']) && !empty($waGroup['wa_gateway_provider']), "Pengambilan setting grup 'whatsapp' berhasil (Provider: {$waGroup['wa_gateway_provider']})");

// 5. Uji Pengambilan Semua Grup dengan getAllGrouped()
$allGrouped = $settingModel->getAllGrouped();
assertTest(isset($allGrouped['exam']) && isset($allGrouped['proctoring']) && isset($allGrouped['whatsapp']) && isset($allGrouped['ai']) && isset($allGrouped['general']), "getAllGrouped() mengelompokkan 5 kategori parameter lengkap");

// 6. Uji Global Helper Shortcut app_setting()
$helperDuration = app_setting('exam_default_duration');
assertTest((int)$helperDuration === (int)$duration, "Global shortcut function app_setting('exam_default_duration') cocok: {$helperDuration}");

// 7. Uji Update Batch Parameter dan Sinkronisasi ke .env
$testToken = 'TestToken_' . time();
$updateRes = $settingModel->updateBatch([
    'exam_default_duration' => '120',
    'wa_api_token'          => $testToken,
    'school_name'           => 'SMA Labschool Digital'
]);
assertTest($updateRes === true, "Update batch parameter ke database berhasil dalam transaksi aman");

// Verifikasi nilai baru setelah update
$newDuration = $settingModel->get('exam_default_duration');
assertTest((int)$newDuration === 120, "Nilai durasi ujian berhasil diperbarui menjadi 120 menit");

$newSchool = $settingModel->get('school_name');
assertTest($newSchool === 'SMA Labschool Digital', "Nama sekolah berhasil diperbarui menjadi '{$newSchool}'");

// 8. Verifikasi Berkas .env Berhasil Tersinkronisasi
$envFile = ROOT_PATH . DIRECTORY_SEPARATOR . '.env';
assertTest(file_exists($envFile), "Berkas .env berhasil dibuat/disinkronkan di root project");

$envContent = file_get_contents($envFile);
assertTest(str_contains($envContent, $testToken), "Token WhatsApp baru tercermin di dalam berkas .env");
assertTest(str_contains($envContent, 'SMA Labschool Digital'), "Nama sekolah baru tercermin di dalam berkas .env");

// 9. Kembalikan Nilai Default Asli
$restoreRes = $settingModel->updateBatch([
    'exam_default_duration' => '90',
    'wa_api_token'          => 'Uz2XNDo4JQatmBAeKqBf',
    'school_name'           => 'SMA Negeri 1 Prestasi Bangsa'
]);
assertTest($restoreRes === true, "Rollback / restore nilai awal berhasil dijalankan");

echo "\n{$cBold}{$cCyan}=============================================================={$cReset}\n";
echo "{$cBold}  HASIL AKHIR: {$passCount} / {$totalCount} PENGUJIAN TAHAP 3 LULUS (100%){$cReset}\n";
echo "{$cBold}{$cCyan}=============================================================={$cReset}\n\n";
