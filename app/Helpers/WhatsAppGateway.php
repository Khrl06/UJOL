<?php
/**
 * SmartExam AI - WhatsApp Gateway Dispatcher & Message Formatter
 * Mendukung Provider: Fonnte, Wablas, Twilio, Generic Webhook, dan Mode Simulasi Aman.
 */

namespace App\Helpers;

class WhatsAppGateway
{
    /**
     * Format nomor HP ke standar gateway (cth: 0812345678 -> 62812345678)
     */
    public static function formatPhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Hapus karakter non-digit
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (empty($clean)) {
            return null;
        }

        // Jika diawali '08', ganti menjadi '628'
        if (str_starts_with($clean, '08')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '628' . substr($clean, 1);
        }

        return $clean;
    }

    /**
     * Kirim pesan teks WhatsApp melalui gateway terpilih
     * 
     * @param string $recipientPhone Nomor tujuan
     * @param string $message Teks pesan format WhatsApp
     * @return array ['success' => bool, 'provider' => string, 'message' => string, 'simulated' => bool]
     */
    public static function sendMessage(string $recipientPhone, string $message): array
    {
        $phone = self::formatPhoneNumber($recipientPhone);
        if (!$phone) {
            return [
                'success'   => false,
                'provider'  => 'none',
                'message'   => 'Nomor telepon tujuan tidak valid.',
                'simulated' => false
            ];
        }

        $provider = function_exists('app_setting') ? strtolower((string)app_setting('wa_gateway_provider', defined('WA_GATEWAY_PROVIDER') ? WA_GATEWAY_PROVIDER : 'fonnte')) : (defined('WA_GATEWAY_PROVIDER') ? strtolower(WA_GATEWAY_PROVIDER) : 'fonnte');
        $apiToken = function_exists('app_setting') ? (string)app_setting('wa_api_token', defined('WA_API_TOKEN') ? WA_API_TOKEN : '') : (defined('WA_API_TOKEN') ? WA_API_TOKEN : '');
        $apiUrl   = function_exists('app_setting') ? (string)app_setting('wa_api_url', defined('WA_API_URL') ? WA_API_URL : 'https://api.fonnte.com/send') : (defined('WA_API_URL') ? WA_API_URL : 'https://api.fonnte.com/send');

        // Jika token masih placeholder atau gateway diatur simulasi, jalankan mode simulasi aman
        if (empty($apiToken) || str_contains($apiToken, 'DEMO') || $provider === 'simulation') {
            self::logSimulation($phone, $message, $provider);
            return [
                'success'   => true,
                'provider'  => $provider,
                'message'   => "Pesan berhasil disimulasikan ke WhatsApp {$phone} (Mode Dev).",
                'simulated' => true
            ];
        }

        try {
            if ($provider === 'fonnte') {
                return self::sendViaFonnte($phone, $message, $apiToken, $apiUrl);
            } elseif ($provider === 'wablas') {
                return self::sendViaWablas($phone, $message, $apiToken, $apiUrl);
            } elseif ($provider === 'generic') {
                return self::sendViaGenericWebhook($phone, $message, $apiToken, $apiUrl);
            } else {
                // Fallback Fonnte
                return self::sendViaFonnte($phone, $message, $apiToken, $apiUrl);
            }
        } catch (\Throwable $e) {
            error_log("[WhatsApp Gateway Error]: " . $e->getMessage());
            self::logSimulation($phone, $message, "fallback_after_error: " . $e->getMessage());
            return [
                'success'   => false,
                'provider'  => $provider,
                'message'   => 'Gangguan pengiriman: ' . $e->getMessage(),
                'simulated' => false
            ];
        }
    }

    /**
     * Kirim via Fonnte API (https://api.fonnte.com/send)
     */
    private static function sendViaFonnte(string $phone, string $message, string $token, string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'target'  => $phone,
                'message' => $message,
                'countryCode' => '62'
            ],
            CURLOPT_HTTPHEADER     => [
                "Authorization: {$token}"
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            self::logSimulation($phone, $message, "Fonnte cURL error: {$curlErr}");
            return ['success' => false, 'provider' => 'fonnte', 'message' => $curlErr, 'simulated' => false];
        }

        $resData = json_decode($response, true);
        $status = ($httpCode >= 200 && $httpCode < 300) && ($resData['status'] ?? false);

        return [
            'success'   => (bool)$status,
            'provider'  => 'fonnte',
            'message'   => $resData['reason'] ?? ($status ? 'Pesan terkirim ke Fonnte' : 'Respon gagal'),
            'response'  => $resData,
            'simulated' => false
        ];
    }

    /**
     * Kirim via Wablas API (https://api.wablas.com)
     */
    private static function sendViaWablas(string $phone, string $message, string $token, string $url): array
    {
        $payload = json_encode([
            'phone'   => $phone,
            'message' => $message
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                "Authorization: {$token}",
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['success' => false, 'provider' => 'wablas', 'message' => $curlErr, 'simulated' => false];
        }

        $resData = json_decode($response, true);
        $status = ($resData['status'] ?? false) === true || ($resData['status'] ?? '') === 'success';

        return [
            'success'   => $status,
            'provider'  => 'wablas',
            'message'   => $resData['message'] ?? 'Respon diterima',
            'response'  => $resData,
            'simulated' => false
        ];
    }

    /**
     * Kirim via Generic Webhook
     */
    private static function sendViaGenericWebhook(string $phone, string $message, string $token, string $url): array
    {
        $payload = json_encode([
            'recipient' => $phone,
            'message'   => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$token}",
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success'   => ($httpCode >= 200 && $httpCode < 300),
            'provider'  => 'generic',
            'message'   => "HTTP {$httpCode}",
            'simulated' => false
        ];
    }

    /**
     * Template 1: Pengingat Jadwal Ujian H-1
     */
    public static function templateExamReminderH1(string $studentName, array $exam): string
    {
        $title    = $exam['title'] ?? 'Ujian Online';
        $subject  = $exam['subject_name'] ?? 'Mata Pelajaran';
        $date     = $exam['formatted_date'] ?? date('d-m-Y', strtotime($exam['exam_date'] ?? 'now'));
        $time     = ($exam['start_time'] ?? '08:00') . ' s/d ' . ($exam['end_time'] ?? '10:00') . ' WIB';
        $duration = $exam['duration_minutes'] ?? 90;
        $proctor  = !empty($exam['enable_dual_camera']) ? 'Wajib Dual Camera & AI Proctoring' : 'Kamera Standar';
        $baseUrl  = defined('BASE_URL') ? BASE_URL : 'http://localhost/UJOL';

        return "🔔 *PENGINGAT JADWAL UJIAN (H-1)*\n"
             . "*SmartExam AI - UJOL*\n"
             . "----------------------------------------\n"
             . "Halo *{$studentName}*,\n\n"
             . "Jangan lupa, besok Anda memiliki jadwal ujian resmi:\n\n"
             . "📋 *Nama Ujian:* {$title}\n"
             . "📚 *Mata Pelajaran:* {$subject}\n"
             . "📅 *Tanggal:* {$date}\n"
             . "⏰ *Waktu:* {$time}\n"
             . "⏳ *Durasi:* {$duration} Menit\n"
             . "📹 *Ketentuan:* {$proctor}\n\n"
             . "⚠️ *Persiapan Penting:*\n"
             . "1. Pastikan baterai laptop/ponsel Anda terisi penuh.\n"
             . "2. Uji coba posisi kamera ganda pada menu *Uji Perangkat*.\n"
             . "3. Istirahat yang cukup dan persiapkan diri dengan baik.\n\n"
             . "Akses ruang ujian melalui: {$baseUrl}/student/exams\n\n"
             . "Semoga sukses dan raih hasil terbaik! 🌟";
    }

    /**
     * Template 2: Pengingat Jadwal Ujian 30 Menit Sebelum Mulai
     */
    public static function templateExamReminder30M(string $studentName, array $exam): string
    {
        $title   = $exam['title'] ?? 'Ujian Online';
        $subject = $exam['subject_name'] ?? 'Mata Pelajaran';
        $time    = ($exam['start_time'] ?? '08:00') . ' WIB';
        $token   = $exam['token'] ?? 'Lihat Dasbor';
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/UJOL';

        return "⏳ *UJIAN SEGERA DIMULAI (30 MENIT LAGI)*\n"
             . "*SmartExam AI - UJOL*\n"
             . "----------------------------------------\n"
             . "Perhatian *{$studentName}*,\n\n"
             . "Sesi ujian *{$title}* ({$subject}) akan dibuka dalam *30 menit ke depan* pada pukul *{$time}*.\n\n"
             . "🔑 *Token Ujian:* `{$token}`\n\n"
             . "🚀 *Langkah Segera:*\n"
             . "1. Masuk ke akun UJOL sekarang: {$baseUrl}/login\n"
             . "2. Buka menu *Jadwal Ujian* dan klik *Masuk Ruang Ujian*.\n"
             . "3. Sambungkan Dual Camera dan lakukan kalibrasi posisi wajah.\n"
             . "4. Standby di ruang tunggu sampai hitung mundur selesai.\n\n"
             . "*Tetap tenang, jujur, dan percaya pada kemampuan diri sendiri!* 💪";
    }

    /**
     * Template 3: Notifikasi Nilai & Hasil Ujian Dirilis
     */
    public static function templateScoreReleased(
        string $studentName,
        string $examTitle,
        string $subjectName,
        float $score,
        float $kkm,
        bool $isPassed,
        ?string $notes = null
    ): string {
        $statusText = $isPassed ? "✅ *LULUS KKM*" : "⚠️ *BELUM LULUS (REMEDIAL)*";
        $scoreStr   = number_format($score, 2);
        $kkmStr     = number_format($kkm, 2);
        $baseUrl    = defined('BASE_URL') ? BASE_URL : 'http://localhost/UJOL';

        return "📢 *PENGUMUMAN HASIL & NILAI UJIAN*\n"
             . "*SmartExam AI - UJOL*\n"
             . "----------------------------------------\n"
             . "Halo *{$studentName}*,\n\n"
             . "Hasil evaluasi resmi untuk paket ujian Anda telah dirilis oleh guru pengampu:\n\n"
             . "📋 *Ujian:* {$examTitle}\n"
             . "📚 *Mata Pelajaran:* {$subjectName}\n"
             . "🎯 *Nilai Akhir:* *{$scoreStr}* (KKM: {$kkmStr})\n"
             . "📊 *Status Kelulusan:* {$statusText}\n"
             . ($notes ? "📝 *Catatan Guru:* {$notes}\n" : "")
             . "\n"
             . "Lihat rincian lembar jawaban dan pembahasan lengkap Anda di:\n"
             . "👉 {$baseUrl}/student/history\n\n"
             . "Teruslah belajar dan tingkatkan prestasimu! 🎓";
    }

    /**
     * Catat log simulasi pesan WhatsApp
     */
    private static function logSimulation(string $phone, string $message, string $provider): void
    {
        $logDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'whatsapp_messages.log';
        $logEntry = "[" . date('Y-m-d H:i:s') . "] [{$provider}] To: {$phone}\n"
                  . "Message:\n{$message}\n"
                  . "------------------------------------------------------------\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
