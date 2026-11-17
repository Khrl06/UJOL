<?php
/**
 * SmartExam AI - Session & Flash Message Helper
 * Mengelola lifecycle sesi dan notifikasi flash message.
 */

namespace App\Helpers;

class SessionHelper
{
    /**
     * Memulai sesi secara aman dengan hardening cookie
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                if (defined('SESSION_NAME')) {
                    @session_name(SESSION_NAME);
                }

                // Security Hardening: Cookie Flags & Strict Mode
                $isSecure = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on')
                    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
                @ini_set('session.use_only_cookies', '1');
                @ini_set('session.use_strict_mode', '1');
                @ini_set('session.cookie_httponly', '1');
                @ini_set('session.cookie_samesite', 'Lax');

                session_set_cookie_params([
                    'lifetime' => 0,
                    'path'     => '/',
                    'domain'   => '',
                    'secure'   => $isSecure,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            @session_start();
        }
    }

    /**
     * Simpan data ke dalam sesi
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Ambil data dari sesi
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Cek apakah key tertentu ada dalam sesi
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Hapus data tertentu dari sesi
     */
    public static function remove(string $key): void
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Hancurkan seluruh sesi (Logout)
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Set Flash Message (Notifikasi sementara satu kali tayang)
     *
     * @param string $type Tipe alert: 'success', 'danger', 'warning', 'info'
     * @param string $message Pesan notifikasi
     */
    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION['flash_messages'][$type][] = $message;
    }

    /**
     * Ambil dan hapus seluruh Flash Messages
     */
    public static function getFlash(?string $type = null): array
    {
        self::start();
        if ($type !== null) {
            $messages = $_SESSION['flash_messages'][$type] ?? [];
            unset($_SESSION['flash_messages'][$type]);
            return $messages;
        }

        $allMessages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $allMessages;
    }

    /**
     * Cek apakah ada flash message
     */
    public static function hasFlash(?string $type = null): bool
    {
        self::start();
        if ($type !== null) {
            return !empty($_SESSION['flash_messages'][$type]);
        }
        return !empty($_SESSION['flash_messages']);
    }
}
