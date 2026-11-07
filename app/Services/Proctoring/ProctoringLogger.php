<?php
/**
 * SmartExam AI - Proctoring Logger
 * Menangani logging aktivitas deteksi AI, kegagalan provider, dan jejak privasi.
 */

namespace App\Services\Proctoring;

class ProctoringLogger
{
    protected string $logPath;

    public function __construct(?string $customPath = null)
    {
        $logDir = defined('APP_PATH') ? APP_PATH . DIRECTORY_SEPARATOR . 'logs' : __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $this->logPath = $customPath ?? ($logDir . DIRECTORY_SEPARATOR . 'proctoring.log');
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $time = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
        $entry = sprintf("[%s] [%s] %s%s%s", $time, strtoupper($level), $message, $contextStr, PHP_EOL);
        @file_put_contents($this->logPath, $entry, FILE_APPEND | LOCK_EX);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Dapatkan log terbaru untuk inspeksi admin
     */
    public function getRecentLogs(int $limit = 50): array
    {
        if (!file_exists($this->logPath)) {
            return [];
        }

        $lines = file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) return [];

        return array_slice(array_reverse($lines), 0, $limit);
    }
}
