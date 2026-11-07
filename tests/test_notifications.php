<?php
/**
 * SmartExam AI - Comprehensive Test Suite: Tahap 2 Fitur Notifikasi
 * Menguji:
 * 1. WhatsAppGateway Formatting & Provider Simulation
 * 2. Multi-Channel In-App & WhatsApp Notification Dispatch
 * 3. Notifikasi Pengingat Jadwal Ujian H-1 (Dispatched & Idempotent/No Spam)
 * 4. Notifikasi Pengingat Jadwal Ujian 30 Menit (Token Ujian & Idempotent)
 * 5. Notifikasi Hasil & Nilai Ujian Resmi Dirilis (Skor, KKM, Status Kelulusan)
 * 6. Scheduler Engine (checkAndDispatchUpcomingReminders)
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
echo "{$cBold}{$cCyan}  SMARTEXAM AI (UJOL) - NOTIFICATION & WHATSAPP TEST SUITE   {$cReset}\n";
echo "{$cBold}{$cCyan}=============================================================={$cReset}\n\n";

$notifService = new \App\Helpers\NotificationService();
$notifModel   = new \App\Models\NotificationModel();

// =========================================================================
// 1. PENGUJIAN WHATSAPP GATEWAY FORMATTING & PROVIDER
// =========================================================================
echo "{$cBold}[PENGUJIAN 1]: WhatsApp Gateway Formatter & Template{$cReset}\n";

$fmt1 = \App\Helpers\WhatsAppGateway::formatPhoneNumber('08123456789');
assertTest($fmt1 === '628123456789', "Format nomor HP '0812...' dikonversi ke '62812...'");

$fmt2 = \App\Helpers\WhatsAppGateway::formatPhoneNumber('+62 821-8889-9001');
assertTest($fmt2 === '6282188899001', "Karakter spasi, strip, dan simbol plus dibersihkan dengan benar");

// Template H-1
$tplH1 = \App\Helpers\WhatsAppGateway::templateExamReminderH1('Ahmad Fauzi', [
    'title'          => 'Ujian PAS Informatika XII',
    'subject_name'   => 'Informatika',
    'formatted_date' => '24-10-2026',
    'start_time'     => '08:00',
    'end_time'       => '09:30',
    'duration_minutes' => 90
]);
assertTest(str_contains($tplH1, 'PENGINGAT JADWAL UJIAN (H-1)'), "Template WhatsApp H-1 memuat header resmi pengingat");
assertTest(str_contains($tplH1, 'Ujian PAS Informatika XII'), "Template memuat nama paket ujian yang akurat");

// Template 30 Menit
$tpl30M = \App\Helpers\WhatsAppGateway::templateExamReminder30M('Ahmad Fauzi', [
    'title'        => 'Ujian PAS Informatika XII',
    'subject_name' => 'Informatika',
    'start_time'   => '08:00',
    'token'        => 'INF999'
]);
assertTest(str_contains($tpl30M, '30 MENIT LAGI'), "Template 30 Menit memuat peringatan hitung mundur");
assertTest(str_contains($tpl30M, 'INF999'), "Template 30 Menit menyertakan token masuk ujian");

// Template Rilis Nilai
$tplScore = \App\Helpers\WhatsAppGateway::templateScoreReleased('Ahmad Fauzi', 'Ujian PAS Informatika XII', 'Informatika', 85.50, 75.00, true, 'Sangat memuaskan');
assertTest(str_contains($tplScore, 'PENGUMUMAN HASIL & NILAI UJIAN'), "Template Nilai memuat judul pengumuman hasil");
assertTest(str_contains($tplScore, '85.50') && str_contains($tplScore, 'LULUS KKM'), "Template Nilai memuat skor dan status kelulusan KKM");

// Pengiriman Pesan WhatsApp (Simulation / Live)
$sendWa = \App\Helpers\WhatsAppGateway::sendMessage('082188899001', "Uji Coba Pengiriman Pesan");
assertTest($sendWa['success'] === true, "WhatsApp Gateway berhasil mengeksekusi pengiriman pesan");

// =========================================================================
// 2. PENGUJIAN IN-APP NOTIFICATION & DATABASE LOG
// =========================================================================
echo "\n{$cBold}[PENGUJIAN 2]: In-App Notification & Database Record{$cReset}\n";

$targetUserId = 3; // Ahmad Fauzi
$beforeUnread = $notifModel->countUnread($targetUserId);

$sendRes = $notifService->send(
    $targetUserId,
    'Uji Coba Sistem Notifikasi',
    'Pemberitahuan otomatis berhasil dijalankan pada sistem UJOL.',
    null,
    ['in_app', 'whatsapp'],
    'info',
    'bi-bell-fill',
    'student/dashboard'
);
assertTest($sendRes['in_app'] === true, "Notifikasi In-App berhasil disimpan ke tabel database");

$afterUnread = $notifModel->countUnread($targetUserId);
assertTest($afterUnread === ($beforeUnread + 1), "Badge unread notifikasi bertambah secara presisi (+1)");

// =========================================================================
// 3. PENGUJIAN PENGINGAT JADWAL UJIAN H-1 & IDEMPOTENSI (ANTI-SPAM)
// =========================================================================
echo "\n{$cBold}[PENGUJIAN 3]: Pengingat Jadwal Ujian H-1 & Proteksi Idempotensi{$cReset}\n";

$examId = 3; // Ujian Simulasi Asesmen Nasional 2026

// Reset reminder log untuk ujian ini agar pengujian bersih
$db = \App\Core\Database::getInstance();
if ($db) {
    $db->prepare("DELETE FROM exam_reminders WHERE exam_id = :id")->execute([':id' => $examId]);
}

$h1Res1 = $notifService->sendExamReminderH1($examId);
assertTest($h1Res1['success'] === true, "Pengiriman pengingat jadwal ujian H-1 berhasil dijalankan");
assertTest($h1Res1['sent'] > 0, "Pengingat H-1 sukses dikirimkan ke {$h1Res1['sent']} peserta didik");

// Panggilan kedua: HARUS dicegah oleh idempotency filter agar tidak ada spam
$h1Res2 = $notifService->sendExamReminderH1($examId);
assertTest($h1Res2['sent'] === 0, "Proteksi Anti-Spam: Pemanggilan ulang H-1 tidak mengirimkan duplikat pesan (sent = 0)");
assertTest($h1Res2['skipped'] > 0, "Peserta yang sudah menerima berhasil dilewati (skipped = {$h1Res2['skipped']})");

// =========================================================================
// 4. PENGUJIAN PENGINGAT JADWAL UJIAN 30 MENIT
// =========================================================================
echo "\n{$cBold}[PENGUJIAN 4]: Pengingat Jadwal Ujian 30 Menit Sebelum Mulai{$cReset}\n";

$m30Res1 = $notifService->sendExamReminder30M($examId);
assertTest($m30Res1['success'] === true, "Pengiriman pengingat 30 menit sebelum ujian berhasil dijalankan");
assertTest($m30Res1['sent'] > 0, "Token ujian & instruksi 30M sukses dikirimkan ke {$m30Res1['sent']} peserta");

// Panggilan kedua: HARUS dicegah oleh idempotency filter
$m30Res2 = $notifService->sendExamReminder30M($examId);
assertTest($m30Res2['sent'] === 0, "Proteksi Anti-Spam: Pemanggilan ulang 30M tidak mengirimkan duplikat pesan");

// =========================================================================
// 5. PENGUJIAN NOTIFIKASI HASIL & NILAI UJIAN DIRILIS
// =========================================================================
echo "\n{$cBold}[PENGUJIAN 5]: Notifikasi Rilis Nilai Ujian ke Siswa{$cReset}\n";

// Ambil ID peserta nyata di database (Ahmad Fauzi, participant_id: 3)
$partId = 3;
$scoreRes = $notifService->sendScoreReleasedNotification(
    $partId,
    92.50,
    75.00,
    true,
    'Kerja bagus! Nilai ujian Anda di atas rata-rata kelas.'
);
assertTest($scoreRes['success'] === true, "Notifikasi nilai ujian resmi dirilis berhasil dikirimkan ke peserta");
assertTest($scoreRes['channels']['in_app'] === true, "Notifikasi nilai tersimpan di akun In-App siswa");

// =========================================================================
// 6. PENGUJIAN OTOMATISASI SCHEDULER ENGINE
// =========================================================================
echo "\n{$cBold}[PENGUJIAN 6]: Mesin Scheduler Pengecekan Pengingat Otomatis{$cReset}\n";

$schedRes = $notifService->checkAndDispatchUpcomingReminders();
assertTest($schedRes['success'] === true, "Mesin scheduler berhasil berjalan tanpa kendala");
assertTest(isset($schedRes['summary']['exams_checked']), "Summary data scheduler terekam lengkap");

echo "\n{$cBold}{$cCyan}=============================================================={$cReset}\n";
echo "{$cBold}  HASIL AKHIR: {$passCount} / {$totalCount} PENGUJIAN TAHAP 2 BERHASIL (" . round(($passCount / $totalCount) * 100) . "%){$cReset}\n";
echo "{$cBold}{$cCyan}=============================================================={$cReset}\n\n";

exit($passCount === $totalCount ? 0 : 1);
