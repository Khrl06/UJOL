<?php
/**
 * SmartExam AI - Database Configuration
 * Konfigurasi koneksi database MySQL menggunakan PDO
 */

return [
    'driver'    => 'mysql',
    'host'      => getenv('MYSQLHOST') ?: (getenv('DB_HOST') ?: '127.0.0.1'),
    'port'      => getenv('MYSQLPORT') ?: (getenv('DB_PORT') ?: '3306'),
    'database'  => getenv('MYSQLDATABASE') ?: (getenv('DB_NAME') ?: 'smartexam_db'),
    'username'  => getenv('MYSQLUSER') ?: (getenv('DB_USER') ?: 'root'),
    'password'  => getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : (getenv('DB_PASS') ?: ''),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
