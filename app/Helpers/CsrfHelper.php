<?php
/**
 * SmartExam AI - CSRF Protection Helper
 * Menghasilkan dan memvalidasi token CSRF untuk mencegah serangan Cross-Site Request Forgery.
 */

namespace App\Helpers;

class CsrfHelper
{
    private const SESSION_KEY = 'csrf_token';

    /**
     * Generate token CSRF baru jika belum ada di sesi
     */
    public static function generateToken(): string
    {
        SessionHelper::start();
        if (!SessionHelper::has(self::SESSION_KEY)) {
            $token = bin2hex(random_bytes(32));
            SessionHelper::set(self::SESSION_KEY, $token);
        }
        return SessionHelper::get(self::SESSION_KEY);
    }

    /**
     * Ambil token yang tersimpan
     */
    public static function getToken(): string
    {
        return self::generateToken();
    }

    /**
     * Validasi token dari request POST / header
     */
    public static function validateToken(?string $token): bool
    {
        SessionHelper::start();
        $storedToken = SessionHelper::get(self::SESSION_KEY);

        if (empty($storedToken) || empty($token)) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    /**
     * Hasilkan elemen input HTML tersembunyi untuk form
     */
    public static function tokenField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
