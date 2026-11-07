<?php
/**
 * SmartExam AI - Email & OTP Service
 * Mengelola pembuatan, pengiriman, dan verifikasi kode OTP pendaftaran akun baru melalui Email.
 */

namespace App\Helpers;

use App\Core\Database;
use PDO;

class EmailService
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate 6-digit angka OTP acak
     */
    public static function generateOtp(int $length = 6): string
    {
        $min = (int)pow(10, $length - 1);
        $max = (int)pow(10, $length) - 1;
        return (string)random_int($min, $max);
    }

    /**
     * Kirim email HTML berisi kode OTP pendaftaran
     */
    public function sendRegistrationOtp(string $email, string $name, array $registrationData, int $expireMinutes = 15): array
    {
        $otp = self::generateOtp(6);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expireMinutes} minutes"));

        // Simpan data pendaftaran sementara ke database
        if ($this->db) {
            // Nonaktifkan OTP lama yang belum terpakai untuk email ini
            $stmtDeactivate = $this->db->prepare("UPDATE email_otps SET is_used = 1 WHERE email = :email AND is_used = 0");
            $stmtDeactivate->execute([':email' => $email]);

            // Insert OTP baru
            $stmt = $this->db->prepare("
                INSERT INTO email_otps (email, otp_code, registration_data, expires_at, is_used, created_at)
                VALUES (:email, :otp, :data, :expires, 0, NOW())
            ");
            $stmt->execute([
                ':email'   => $email,
                ':otp'     => $otp,
                ':data'    => json_encode($registrationData),
                ':expires' => $expiresAt
            ]);
        }

        // Simpan juga ke Session untuk fallback cepat
        SessionHelper::set('pending_registration_otp', [
            'email'      => $email,
            'otp'        => $otp,
            'expires_at' => strtotime($expiresAt),
            'data'       => $registrationData
        ]);

        // Buat Template Email HTML Responsif & Estetis
        $htmlContent = $this->getOtpEmailTemplate($name, $otp, $expireMinutes);
        $subject = "Kode Verifikasi Pendaftaran Akun - " . (defined('APP_NAME') ? APP_NAME : 'SmartExam AI');

        // Kirim Email via mail()
        $sent = $this->dispatchMail($email, $subject, $htmlContent);

        // Catat Log OTP ke storage/logs/email_otp.log (untuk audit & kemudahan development/testing)
        $this->logOtpSimulation($email, $otp, $expireMinutes);

        return [
            'success'   => true,
            'email'     => $email,
            'otp'       => $otp,
            'mail_sent' => $sent,
            'expires_at'=> $expiresAt
        ];
    }

    /**
     * Verifikasi kode OTP pendaftaran
     * 
     * @return array|null Mengembalikan data registrasi jika valid, null jika salah/kedaluwarsa
     */
    public function verifyRegistrationOtp(string $email, string $otpCode): ?array
    {
        $cleanOtp = trim($otpCode);

        // 1. Cek melalui Basis Data
        if ($this->db) {
            $stmt = $this->db->prepare("
                SELECT id, registration_data, expires_at 
                FROM email_otps 
                WHERE email = :email AND otp_code = :otp AND is_used = 0 AND expires_at >= NOW()
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([
                ':email' => $email,
                ':otp'   => $cleanOtp
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Tandai sudah dipakai
                $update = $this->db->prepare("UPDATE email_otps SET is_used = 1 WHERE id = :id");
                $update->execute([':id' => $row['id']]);

                // Bersihkan session
                SessionHelper::remove('pending_registration_otp');

                return json_decode($row['registration_data'], true);
            }
        }

        // 2. Fallback Session
        $sessionOtp = SessionHelper::get('pending_registration_otp');
        if ($sessionOtp && $sessionOtp['email'] === $email && $sessionOtp['otp'] === $cleanOtp) {
            if (time() <= $sessionOtp['expires_at']) {
                SessionHelper::remove('pending_registration_otp');
                return $sessionOtp['data'] ?? [];
            }
        }

        return null;
    }

    /**
     * Eksekusi pengiriman email standar PHP
     */
    private function dispatchMail(string $to, string $subject, string $htmlBody): bool
    {
        $fromEmail = "noreply@smartexam.sch.id";
        $fromName  = defined('APP_NAME') ? APP_NAME : 'SmartExam AI';

        $headers   = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = "From: {$fromName} <{$fromEmail}>";
        $headers[] = "Reply-To: {$fromEmail}";
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        // Menggunakan error suppression agar tidak fatal error jika local sendmail belum dikonfigurasi
        return @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    }

    /**
     * Template Email HTML Elegan & Modern
     */
    private function getOtpEmailTemplate(string $name, string $otp, int $expireMinutes): string
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'SmartExam AI';

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Pendaftaran Akun</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6fb; margin: 0; padding: 20px; color: #1e293b; }
        .email-container { max-width: 540px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .email-header { background: linear-gradient(135deg, #2563eb, #1d4ed8); padding: 32px 24px; text-align: center; color: #ffffff; }
        .email-header h1 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
        .email-header p { margin: 6px 0 0; font-size: 13px; opacity: 0.9; }
        .email-body { padding: 32px 28px; text-align: center; }
        .otp-box { background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 18px 24px; margin: 24px 0; display: inline-block; }
        .otp-code { font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #1d4ed8; font-family: 'Courier New', Courier, monospace; margin: 0; }
        .alert-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px; font-size: 12px; text-align: left; color: #92400e; margin-top: 20px; }
        .email-footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{$appName}</h1>
            <p>Portal Sistem Ujian Online Terintegrasi</p>
        </div>
        <div class="email-body">
            <h2 style="font-size: 18px; color: #0f172a; margin-top: 0;">Verifikasi Akun Baru</h2>
            <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                Halo <strong>{$name}</strong>,<br>
                Terima kasih telah mendaftar di <strong>{$appName}</strong>. Silakan gunakan kode verifikasi OTP berikut untuk menyelesaikan aktivasi akun Anda:
            </p>
            
            <div class="otp-box">
                <div class="otp-code">{$otp}</div>
            </div>

            <p style="font-size: 13px; color: #64748b; margin: 0;">
                Kode ini berlaku selama <strong>{$expireMinutes} menit</strong> ke depan.
            </p>

            <div class="alert-box">
                ⚠️ <strong>PENTING:</strong> Jangan berikan kode OTP ini kepada siapapun demi keamanan akun Anda. Petugas sekolah atau proktor tidak pernah meminta kode OTP rahasia ini.
            </div>
        </div>
        <div class="email-footer">
            &copy; 2026 {$appName}. Sistem Asesmen Ujian Berbasis Komputer & AI.<br>
            Email otomatis, mohon untuk tidak membalas pesan ini.
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Catat log simulasi pengiriman OTP
     */
    private function logOtpSimulation(string $email, string $otp, int $expireMinutes): void
    {
        $logDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : dirname(dirname(__DIR__)) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/email_otp.log';
        $logEntry = "[" . date('Y-m-d H:i:s') . "] REGISTRATION OTP -> Email: {$email} | OTP: {$otp} | Expires in: {$expireMinutes}m\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
