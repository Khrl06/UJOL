<?php
/**
 * SmartExam AI - Database Wrapper (Singleton)
 * Mengelola koneksi PDO MySQL secara terpusat dan aman.
 */

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static bool $connected = false;
    private static ?string $errorMessage = null;

    /**
     * Dapatkan instance PDO (Singleton)
     */
    public static function getInstance(): ?PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Buat koneksi PDO ke MySQL
     */
    private static function connect(): void
    {
        $configFile = APP_PATH . '/Config/Database.php';
        if (!file_exists($configFile)) {
            self::$errorMessage = "File konfigurasi database tidak ditemukan.";
            return;
        }

        $config = require $configFile;

        $dsn = sprintf(
            "%s:host=%s;port=%s;dbname=%s;charset=%s",
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            self::$instance = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options'] ?? []
            );
            self::$connected = true;
            self::$errorMessage = null;
        } catch (PDOException $e) {
            self::$instance = null;
            self::$connected = false;
            self::$errorMessage = $e->getMessage();
        }
    }

    /**
     * Cek apakah koneksi database berhasil
     */
    public static function isConnected(): bool
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$connected;
    }

    /**
     * Dapatkan pesan error koneksi terakhir jika ada
     */
    public static function getLastError(): ?string
    {
        return self::$errorMessage;
    }
}
