<?php
/**
 * SmartExam AI - Application Configuration
 * Sistem Ujian Online Berbasis AI untuk SMA / Sederajat
 */

// Informasi Aplikasi
define('APP_NAME', 'SmartExam AI');
define('APP_TAGLINE', 'Sistem Ujian Online Cerdas & Terpercaya');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'SmartExam Development Team');
define('APP_ENV', 'development'); // development | production

// Set Timezone default Indonesia Western Time
date_default_timezone_set('Asia/Jakarta');

// Deteksi otomatis Base URL (Mendukung XAMPP subfolder /UJOL maupun PHP Built-in Server)
if (!defined('BASE_URL')) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $protocol = $isHttps ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Deteksi path direktori eksekusi skrip
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = rtrim($scriptDir, '/');
    
    define('BASE_URL', $protocol . $host . $basePath);
}

// Konfigurasi Sesi & Remember Me
define('SESSION_NAME', 'smartexam_session_id');
define('SESSION_LIFETIME', 7200); // 2 Jam
define('REMEMBER_COOKIE_NAME', 'smartexam_remember_token');
define('APP_SECRET_KEY', 'smartexam_ai_super_secret_salt_key_2026');

// Konfigurasi WhatsApp Gateway (Fonnte / Wablas / Twilio / Generic)
define('WA_GATEWAY_ENABLED', true);
define('WA_GATEWAY_PROVIDER', getenv('WA_GATEWAY_PROVIDER') ?: 'fonnte'); // fonnte | wablas | generic | simulation
define('WA_API_TOKEN', getenv('WA_API_TOKEN') ?: 'Uz2XNDo4JQatmBAeKqBf');
define('WA_API_URL', getenv('WA_API_URL') ?: 'https://api.fonnte.com/send');
define('WA_SENDER_NAME', 'SmartExam AI UJOL');

// Path Konstanta
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('PUBLIC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'public');
define('STORAGE_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
