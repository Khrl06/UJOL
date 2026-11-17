<?php
/**
 * SmartExam AI - Standalone Unit Test Runner
 * Menjalankan seluruh test suite unit secara otomatis dengan tampilan warna ANSI.
 * Exit code: 0 jika semua lolos (PASS), 1 jika ada yang gagal (FAIL).
 */

require_once __DIR__ . '/bootstrap.php';

// ANSI Styling
$cGreen  = "\033[32m";
$cRed    = "\033[31m";
$cYellow = "\033[33m";
$cCyan   = "\033[36m";
$cBold   = "\033[1m";
$cDim    = "\033[2m";
$cReset  = "\033[0m";

echo "\n{$cBold}{$cCyan}======================================================================{$cReset}\n";
echo "{$cBold}{$cCyan}   SMARTEXAM AI (UJOL) — AUTOMATED UNIT TEST SUITE (CI/CD)            {$cReset}\n";
echo "{$cBold}{$cCyan}======================================================================{$cReset}\n";
echo "{$cDim}Environment: " . APP_ENV . " | PHP: " . PHP_VERSION . " | OS: " . PHP_OS . "{$cReset}\n\n";

$testDir = __DIR__ . '/Unit';
$testFiles = glob($testDir . '/*Test.php');

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$startTime = microtime(true);

foreach ($testFiles as $file) {
    require_once $file;
    $className = 'Tests\\Unit\\' . basename($file, '.php');

    if (!class_exists($className)) {
        continue;
    }

    $ref = new ReflectionClass($className);
    if ($ref->isAbstract()) {
        continue;
    }

    $methods = array_filter($ref->getMethods(ReflectionMethod::IS_PUBLIC), function ($m) {
        return str_starts_with($m->getName(), 'test');
    });

    echo "{$cBold}• {$className}{$cReset}\n";

    foreach ($methods as $method) {
        $methodName = $method->getName();
        $totalTests++;

        try {
            $ctor = $ref->getConstructor();
            if ($ctor && $ctor->getNumberOfRequiredParameters() > 0) {
                $instance = $ref->newInstance($methodName);
            } else {
                $instance = $ref->newInstance();
            }

            // Jalankan setUp jika ada
            if (method_exists($instance, 'setUp')) {
                $ref->getMethod('setUp')->invoke($instance);
            }

            // Jalankan method test
            $method->invoke($instance);
            $passedTests++;
            echo "   {$cGreen}✓ PASS{$cReset} {$cDim}{$methodName}{$cReset}\n";
        } catch (\Throwable $e) {
            $failedTests++;
            echo "   {$cRed}✗ FAIL{$cReset} {$methodName}\n";
            echo "     {$cYellow}Penyebab: {$e->getMessage()}{$cReset}\n";
            echo "     {$cDim}Lokasi: {$e->getFile()}:{$e->getLine()}{$cReset}\n";
        }
    }
    echo "\n";
}

$duration = round((microtime(true) - $startTime) * 1000, 2);

echo "{$cBold}{$cCyan}----------------------------------------------------------------------{$cReset}\n";
echo "  HASIL AKHIR PENGUJIAN:\n";
echo "  Total Pengujian : {$totalTests}\n";
echo "  Lolos (Passed)  : {$cGreen}{$passedTests}{$cReset}\n";
echo "  Gagal (Failed)  : " . ($failedTests > 0 ? "{$cRed}{$failedTests}{$cReset}" : "0") . "\n";
echo "  Waktu Eksekusi  : {$duration} ms\n";
echo "{$cBold}{$cCyan}======================================================================{$cReset}\n\n";

if ($failedTests > 0) {
    echo "{$cRed}❌ PENGUJIAN GAGAL (Status: FAIL){$cReset}\n\n";
    exit(1);
} else {
    echo "{$cGreen}✅ SELURUH UNIT TEST LOLOS 100% (Status: SUCCESS / PIPELINE HIJAU){$cReset}\n\n";
    exit(0);
}
