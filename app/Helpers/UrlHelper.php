<?php
/**
 * SmartExam AI - URL Helper Functions
 * Fungsi global untuk mempermudah resolusi URL, aset, dan redirect.
 */

if (!function_exists('base_url')) {
    /**
     * Dapatkan Base URL aplikasi dengan path tambahan
     */
    function base_url(string $path = ''): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $path = ltrim($path, '/');
        return empty($path) ? $base : $base . '/' . $path;
    }
}

if (!function_exists('asset')) {
    /**
     * Dapatkan path lengkap file asset (CSS, JS, Images)
     */
    function asset(string $path = ''): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $cleanPath = ltrim($path, '/');

        // Jika BASE_URL sudah berakhiran /public atau document root diarahkan ke public
        if (str_ends_with($base, '/public') || (isset($_SERVER['DOCUMENT_ROOT']) && realpath($_SERVER['DOCUMENT_ROOT']) === realpath(PUBLIC_PATH))) {
            return $base . '/assets/' . $cleanPath;
        }

        // Jika diakses melalui subfolder XAMPP /UJOL
        return $base . '/public/assets/' . $cleanPath;
    }
}

if (!function_exists('redirect')) {
    /**
     * Lakukan redirect ke URL atau rute tertentu
     */
    function redirect(string $path): void
    {
        $url = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            ? $path
            : base_url($path);

        header("Location: " . $url);
        exit;
    }
}

if (!function_exists('current_url')) {
    /**
     * Dapatkan URL halaman saat ini
     */
    function current_url(): string
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
        $protocol = $isHttps ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return $protocol . $host . $uri;
    }
}

if (!function_exists('is_active_nav')) {
    /**
     * Cek apakah segmen path saat ini aktif untuk menandai menu navigasi
     */
    function is_active_nav(string $path): string
    {
        $url = $_GET['url'] ?? '';
        if (empty($url)) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            if ($scriptDir !== '/' && $scriptDir !== '\\') {
                $uri = substr($uri, strlen($scriptDir));
            }
            $url = trim($uri, '/');
            if (str_starts_with($url, 'index.php/')) {
                $url = substr($url, 10);
            } elseif ($url === 'index.php') {
                $url = '';
            }
        }

        $url = trim(explode('?', $url)[0], '/');
        $path = trim($path, '/');

        // Penanganan dasbor utama
        if ($path === 'student' || $path === 'student/dashboard') {
            return ($url === 'student' || $url === 'student/dashboard') ? 'active' : '';
        }
        if ($path === 'teacher' || $path === 'teacher/dashboard') {
            return ($url === 'teacher' || $url === 'teacher/dashboard') ? 'active' : '';
        }
        if ($path === 'admin' || $path === 'admin/dashboard') {
            return ($url === 'admin' || $url === 'admin/dashboard') ? 'active' : '';
        }

        return ($url === $path || str_starts_with($url, $path . '/')) ? 'active' : '';
    }
}

if (!function_exists('e')) {
    /**
     * Escape string untuk ditampilkan secara aman pada HTML
     */
    function e(?string $string): string
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}
