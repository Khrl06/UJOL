<?php
/**
 * SmartExam AI - Public Front Controller Entry Point
 * Semua permintaan web masuk melalui file ini.
 */

// Muat Konfigurasi Aplikasi
require_once __DIR__ . '/../app/Config/Config.php';

// Atur Pelaporan Error Berdasarkan Environment
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Muat Helper Global yang dibutuhkan
require_once APP_PATH . '/Helpers/UrlHelper.php';
require_once APP_PATH . '/Helpers/SanitizeHelper.php';
require_once APP_PATH . '/Helpers/SettingHelper.php';

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

// Global HTTP Security Hardening Headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(self), microphone=(self), geolocation=()');
}

// Mulai Manajemen Sesi dengan Hardened Configuration
\App\Helpers\SessionHelper::start();

// Inisialisasi dan Jalankan Aplikasi (Router & Controller Dispatcher)
$app = new \App\Core\App();

