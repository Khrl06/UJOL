<?php
/**
 * SmartExam AI - Scheduled Cron Worker: Exam Reminders
 * Memeriksa jadwal ujian mendatang dan mengirimkan notifikasi H-1 serta 30 menit sebelum ujian dimulai.
 * 
 * Penggunaan CLI:
 *   php cron/notify_reminders.php
 */

require_once __DIR__ . '/../app/Config/Config.php';

// Registrasi Autoloader Standar PSR-4 untuk Namespace App\
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . DIRECTORY_SEPARATOR;

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

echo "============================================================\n";
echo "  SmartExam AI - Scheduled Exam Reminder Dispatcher\n";
echo "  Waktu: " . date('Y-m-d H:i:s') . " WIB\n";
echo "============================================================\n";

try {
    $notifService = new \App\Helpers\NotificationService();
    $result = $notifService->checkAndDispatchUpcomingReminders();

    echo "Status: " . ($result['success'] ? "SUKSES" : "GAGAL") . "\n";
    echo "- Total Ujian Diperiksa : " . ($result['summary']['exams_checked'] ?? 0) . " paket\n";
    echo "- Pengingat H-1 Terkirim: " . ($result['summary']['h1_dispatched'] ?? 0) . " penerima\n";
    echo "- Pengingat 30M Terkirim: " . ($result['summary']['m30_dispatched'] ?? 0) . " penerima\n";
    echo "============================================================\n";
    echo "Selesai.\n";
    exit(0);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
