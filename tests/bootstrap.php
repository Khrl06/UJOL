<?php
/**
 * SmartExam AI - Test Bootstrap File
 * Menyiapkan environment pengujian mandiri tanpa ketergantungan server web aktif.
 */

// Muat konfigurasi global aplikasi terlebih dahulu
require_once dirname(__DIR__) . '/app/Config/Config.php';

// Load Composer autoloader jika ada
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

// Fallback autoloader PSR-4 untuk App\ dan Tests\
spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    } elseif (strncmp('Tests\\', $class, 6) === 0) {
        $file = ROOT_PATH . '/tests/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Muat Helper Global
require_once APP_PATH . '/Helpers/UrlHelper.php';
require_once APP_PATH . '/Helpers/SanitizeHelper.php';
require_once APP_PATH . '/Helpers/CsrfHelper.php';
require_once APP_PATH . '/Helpers/SettingHelper.php';
require_once APP_PATH . '/Helpers/SessionHelper.php';
