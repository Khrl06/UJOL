<?php
/**
 * SmartExam AI - WhatsApp OTP Service
 * Mengelola pembuatan, pengiriman, dan verifikasi kode OTP pendaftaran akun baru via WhatsApp Gateway.
 */

namespace App\Helpers;

use App\Core\Database;
use PDO;

class WhatsAppOtpService
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
     * Kirim pesan WhatsApp berisi kode OTP pendaftaran akun
     */
    public function sendRegistrationOtp(string $phone, string $name, array $registrationData, int $expireMinutes = 15): array
    {
        $cleanPhone = WhatsAppGateway::formatPhoneNumber($phone) ?: $phone;
        $otp = self::generateOtp(6);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expireMinutes} minutes"));

        // Simpan data pendaftaran sementara ke database
        if ($this->db) {
            // Nonaktifkan OTP lama yang belum terpakai untuk nomor ini
            $stmtDeactivate = $this->db->prepare("UPDATE whatsapp_otps SET is_used = 1 WHERE phone = :phone AND is_used = 0");
            $stmtDeactivate->execute([':phone' => $cleanPhone]);

            // Insert OTP baru
            $stmt = $this->db->prepare("
                INSERT INTO whatsapp_otps (phone, otp_code, registration_data, expires_at, is_used, created_at)
                VALUES (:phone, :otp, :data, :expires, 0, NOW())
            ");
            $stmt->execute([
                ':phone'   => $cleanPhone,
                ':otp'     => $otp,
                ':data'    => json_encode($registrationData),
                ':expires' => $expiresAt
            ]);
        }

        // Simpan juga ke Session untuk fallback instan
        SessionHelper::set('pending_registration_otp', [
            'phone'      => $cleanPhone,
            'display_phone' => $phone,
            'otp'        => $otp,
            'expires_at' => strtotime($expiresAt),
            'data'       => $registrationData
        ]);

        // Buat Pesan WhatsApp Resmi & Rapih
        $appName = defined('APP_NAME') ? APP_NAME : 'SmartExam AI';
        $message = "🔐 *KODE VERIFIKASI (OTP) PENDAFTARAN AKUN*\n"
                 . "*{$appName}*\n"
                 . "----------------------------------------\n"
                 . "Halo *{$name}*,\n\n"
                 . "Terima kasih telah mendaftar di sistem {$appName}.\n"
                 . "Berikut adalah 6-digit kode One-Time Password (OTP) Anda untuk verifikasi dan aktivasi akun:\n\n"
                 . "👉 Kode OTP: *{$otp}*\n\n"
                 . "⏳ Kode ini hanya berlaku selama *{$expireMinutes} menit*.\n"
                 . "⚠️ *PENTING:* Jangan berikan kode OTP ini kepada siapa pun, termasuk pihak sekolah atau panitia ujian.\n\n"
                 . "Pesan ini dikirimkan secara otomatis oleh sistem {$appName}.";

        // Kirim via WhatsApp Gateway
        $waResult = WhatsAppGateway::sendMessage($cleanPhone, $message);

        // Catat Log OTP WhatsApp ke storage/logs/whatsapp_otp.log
        $this->logOtpSimulation($cleanPhone, $otp, $expireMinutes, $waResult);

        return [
            'success'   => true,
            'phone'     => $cleanPhone,
            'otp'       => $otp,
            'wa_result' => $waResult,
            'expires_at'=> $expiresAt
        ];
    }

    /**
     * Verifikasi kode OTP pendaftaran WhatsApp
     * 
     * @return array|null Mengembalikan data registrasi jika valid, null jika salah/kedaluwarsa
     */
    public function verifyRegistrationOtp(string $phone, string $otpCode): ?array
    {
        $cleanPhone = WhatsAppGateway::formatPhoneNumber($phone) ?: $phone;
        $cleanOtp   = trim($otpCode);

        // 1. Cek melalui Basis Data
        if ($this->db) {
            $stmt = $this->db->prepare("
                SELECT id, registration_data, expires_at 
                FROM whatsapp_otps 
                WHERE (phone = :phone OR phone = :rawPhone) AND otp_code = :otp AND is_used = 0 AND expires_at >= NOW()
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([
                ':phone'    => $cleanPhone,
                ':rawPhone' => trim($phone),
                ':otp'      => $cleanOtp
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Tandai sudah dipakai
                $update = $this->db->prepare("UPDATE whatsapp_otps SET is_used = 1 WHERE id = :id");
                $update->execute([':id' => $row['id']]);

                // Bersihkan session
                SessionHelper::remove('pending_registration_otp');

                return json_decode($row['registration_data'], true);
            }
        }

        // 2. Fallback Session
        $sessionOtp = SessionHelper::get('pending_registration_otp');
        if ($sessionOtp && ($sessionOtp['phone'] === $cleanPhone || ($sessionOtp['display_phone'] ?? '') === $phone) && $sessionOtp['otp'] === $cleanOtp) {
            if (time() <= $sessionOtp['expires_at']) {
                SessionHelper::remove('pending_registration_otp');
                return $sessionOtp['data'] ?? [];
            }
        }

        return null;
    }

    /**
     * Catat log audit OTP ke file log storage
     */
    private function logOtpSimulation(string $phone, string $otp, int $expireMinutes, array $waResult): void
    {
        $logDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : dirname(dirname(__DIR__)) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/whatsapp_otp.log';
        $provider = $waResult['provider'] ?? 'simulated';
        $status   = ($waResult['success'] ?? false) ? 'SENT' : 'FAILED';
        $logLine  = sprintf(
            "[%s] REGISTRATION OTP -> WA: %s | OTP: %s | Expires in: %dm | Status: %s | Provider: %s\n",
            date('Y-m-d H:i:s'),
            $phone,
            $otp,
            $expireMinutes,
            $status,
            $provider
        );

        @file_put_contents($logFile, $logLine, FILE_APPEND);
    }
}
