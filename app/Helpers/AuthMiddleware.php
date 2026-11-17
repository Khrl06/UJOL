<?php
/**
 * SmartExam AI - Authentication & Role-Based Access Control (RBAC) Guard
 * Memvalidasi otentikasi sesi, proteksi remember me, dan pembatasan hak akses role.
 */

namespace App\Helpers;

use App\Models\AuditLogModel;
use App\Models\UserModel;
use App\Controllers\ErrorController;

class AuthMiddleware
{
    private const SESSION_USER_KEY = 'user_logged_in';

    /**
     * Cek apakah pengguna saat ini sedang login (termasuk auto-login dari Remember Me)
     */
    public static function isLoggedIn(): bool
    {
        SessionHelper::start();

        if (SessionHelper::has(self::SESSION_USER_KEY)) {
            return true;
        }

        // Cek apakah ada cookie Remember Me
        return self::checkRememberMeCookie();
    }

    /**
     * Dapatkan data user yang sedang login
     */
    public static function user(): ?array
    {
        self::isLoggedIn();
        return SessionHelper::get(self::SESSION_USER_KEY, null);
    }

    /**
     * Dapatkan ID user yang sedang login
     */
    public static function id(): ?int
    {
        $user = self::user();
        return isset($user['id']) ? (int)$user['id'] : null;
    }

    /**
     * Dapatkan role user saat ini (admin, guru, siswa)
     */
    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    /**
     * Set data user yang berhasil login ke sesi (dengan proteksi session fixation & remember me)
     */
    public static function login(array $userData, bool $remember = false): void
    {
        SessionHelper::start();

        // 1. Regenerasi Session ID untuk mencegah Session Fixation attack
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // 2. Hapus data sensitif seperti password sebelum disimpan di sesi
        unset($userData['password']);
        SessionHelper::set(self::SESSION_USER_KEY, $userData);

        // 3. Pasang cookie Remember Me jika diminta
        if ($remember && isset($userData['id'])) {
            self::setRememberCookie((int)$userData['id']);
        }
    }

    /**
     * Perbarui data user di sesi secara real-time (setelah edit profil)
     */
    public static function updateSessionUser(array $userData): void
    {
        SessionHelper::start();
        $currentUser = SessionHelper::get(self::SESSION_USER_KEY, []);
        if (is_array($currentUser)) {
            unset($userData['password']);
            $merged = array_merge($currentUser, $userData);
            SessionHelper::set(self::SESSION_USER_KEY, $merged);
        }
    }

    /**
     * Logout pengguna dan bersihkan sesi serta cookie remember me
     */
    public static function logout(): void
    {
        $userId = self::id();

        // Catat jejak audit sebelum sesi dimusnahkan
        if ($userId) {
            $audit = new AuditLogModel();
            $audit->logActivity($userId, 'LOGOUT', 'Pengguna keluar dari sistem.');
        }

        // Bersihkan cookie Remember Me jika ada
        self::clearRememberCookie();

        // Hancurkan seluruh sesi
        SessionHelper::destroy();
    }

    /**
     * Wajibkan pengguna login, jika belum maka redirect ke login
     */
    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            SessionHelper::setFlash('warning', 'Silakan masuk terlebih dahulu untuk mengakses halaman tersebut.');
            redirect('login');
        }
    }

    /**
     * Wajibkan pengguna guest (belum login), jika sudah login maka redirect ke dashboard sesuai role
     */
    public static function requireGuest(): void
    {
        if (self::isLoggedIn()) {
            self::redirectToDashboard();
        }
    }

    /**
     * Wajibkan pengguna memiliki role tertentu.
     * Jika tidak cocok, tolak akses dan tampilkan HTTP 403 Forbidden.
     *
     * @param string|array $allowedRoles Role yang diizinkan (misal: 'admin' atau ['admin', 'guru'])
     */
    public static function requireRole(string|array $allowedRoles): void
    {
        self::requireAuth();

        $currentRole = self::role();
        $allowed = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];

        if (!in_array($currentRole, $allowed, true)) {
            $requiredStr = strtoupper(implode(', ', $allowed));
            $message = "Akses Ditolak! Halaman ini dikhususkan untuk peran [{$requiredStr}]. Peran Anda saat ini adalah [" . strtoupper($currentRole ?? 'GUEST') . "].";
            self::denyAccess($message, $requiredStr);
        }
    }

    /**
     * Menolak akses secara eksplisit, mencatat ke audit log, dan merender HTTP 403
     */
    public static function denyAccess(string $message = 'Akses tidak diizinkan.', ?string $requiredRole = null): void
    {
        $userId = self::id();
        $currentRole = self::role();

        // Catat pelanggaran akses ke audit logs
        $audit = new AuditLogModel();
        $audit->logActivity(
            $userId,
            'ACCESS_DENIED',
            "Percobaan akses tidak sah ke modul [{$requiredRole}] oleh user role [{$currentRole}]. URL: " . ($_SERVER['REQUEST_URI'] ?? '')
        );

        $errorController = new ErrorController();
        $errorController->forbidden($message, $requiredRole);
        exit;
    }

    /**
     * Arahkan ke dashboard sesuai role aktif pengguna
     */
    public static function redirectToDashboard(): void
    {
        $role = self::role();
        match ($role) {
            'admin' => redirect('admin/dashboard'),
            'guru'  => redirect('teacher/dashboard'),
            'siswa' => redirect('student/dashboard'),
            default => redirect('login'),
        };
    }

    /**
     * Buat dan pasang signed cookie Remember Me
     */
    private static function setRememberCookie(int $userId): void
    {
        $secret = defined('APP_SECRET_KEY') ? APP_SECRET_KEY : 'default_secret';
        $signature = hash_hmac('sha256', (string)$userId, $secret);
        $cookieValue = base64_encode($userId . ':' . $signature);

        $lifetime = defined('REMEMBER_COOKIE_LIFETIME') ? REMEMBER_COOKIE_LIFETIME : 2592000;
        $cookieName = defined('REMEMBER_COOKIE_NAME') ? REMEMBER_COOKIE_NAME : 'smartexam_remember_token';

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

        setcookie($cookieName, $cookieValue, [
            'expires'  => time() + $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Validasi cookie Remember Me dan auto-login jika valid
     */
    private static function checkRememberMeCookie(): bool
    {
        $cookieName = defined('REMEMBER_COOKIE_NAME') ? REMEMBER_COOKIE_NAME : 'smartexam_remember_token';
        if (empty($_COOKIE[$cookieName])) {
            return false;
        }

        $decoded = base64_decode($_COOKIE[$cookieName], true);
        if (!$decoded || !str_contains($decoded, ':')) {
            self::clearRememberCookie();
            return false;
        }

        [$userIdStr, $signature] = explode(':', $decoded, 2);
        $userId = (int)$userIdStr;
        if ($userId <= 0) {
            self::clearRememberCookie();
            return false;
        }

        $secret = defined('APP_SECRET_KEY') ? APP_SECRET_KEY : 'default_secret';
        $expectedSignature = hash_hmac('sha256', (string)$userId, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            self::clearRememberCookie();
            return false;
        }

        // Cari user di database
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if ($user && (!isset($user['is_active']) || (int)$user['is_active'] === 1)) {
            // Auto login user
            self::login($user, false);
            return true;
        }

        self::clearRememberCookie();
        return false;
    }

    /**
     * Hapus cookie Remember Me
     */
    private static function clearRememberCookie(): void
    {
        $cookieName = defined('REMEMBER_COOKIE_NAME') ? REMEMBER_COOKIE_NAME : 'smartexam_remember_token';
        if (isset($_COOKIE[$cookieName])) {
            setcookie($cookieName, '', time() - 3600, '/');
            unset($_COOKIE[$cookieName]);
        }
    }
}
