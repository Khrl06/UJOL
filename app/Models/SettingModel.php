<?php
/**
 * SmartExam AI - System Setting Model
 * Mengelola parameter dinamis sistem (Ujian, AI Proctoring, WhatsApp, AI Keys, Profil Sekolah).
 */

namespace App\Models;

use App\Core\Model;
use PDO;

class SettingModel extends Model
{
    protected string $table = 'system_settings';
    protected string $primaryKey = 'id';

    private static ?array $cachedSettings = null;

    /**
     * Dapatkan semua pengaturan yang dikelompokkan berdasarkan group
     */
    public function getAllGrouped(): array
    {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY `setting_group` ASC, `id` ASC";
        $rows = $this->fetchAll($sql);

        $grouped = [];
        foreach ($rows as $row) {
            $group = $row['setting_group'];
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $row['cast_value'] = $this->castValue($row['setting_value'], $row['setting_type']);
            $grouped[$group][$row['setting_key']] = $row;
        }

        return $grouped;
    }

    /**
     * Dapatkan nilai sebuah konfigurasi berdasarkan key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (self::$cachedSettings === null) {
            $this->loadCache();
        }

        return self::$cachedSettings[$key] ?? $default;
    }

    /**
     * Dapatkan semua setting dalam satu grup sebagai associative array key => cast_value
     */
    public function getByGroup(string $group): array
    {
        $sql = "SELECT setting_key, setting_value, setting_type FROM `{$this->table}` WHERE `setting_group` = :g";
        $rows = $this->fetchAll($sql, [':g' => $group]);

        $result = [];
        foreach ($rows as $r) {
            $result[$r['setting_key']] = $this->castValue($r['setting_value'], $r['setting_type']);
        }
        return $result;
    }

    /**
     * Update sekelompok setting sekaligus dalam satu transaksi aman
     */
    public function updateBatch(array $keyValuePairs): bool
    {
        if (!$this->isDbConnected()) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE `{$this->table}` 
                SET `setting_value` = :val, `updated_at` = NOW() 
                WHERE `setting_key` = :key
            ");

            foreach ($keyValuePairs as $key => $value) {
                // Konversi boolean / array ke string untuk disimpan
                $strValue = is_bool($value) ? ($value ? '1' : '0') : (is_array($value) ? json_encode($value) : (string)$value);

                $stmt->execute([
                    ':val' => $strValue,
                    ':key' => $key
                ]);
            }

            $this->db->commit();

            // Refresh memory cache
            $this->loadCache();

            // Sinkronkan ke file .env
            $this->syncToEnvFile($keyValuePairs);

            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("[SettingModel Update Error]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Casting tipe data
     */
    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'number'  => is_numeric($value) ? (str_contains($value, '.') ? (float)$value : (int)$value) : 0,
            'boolean' => in_array(strtolower((string)$value), ['1', 'true', 'yes', 'on'], true),
            'json'    => json_decode($value, true) ?: [],
            default   => $value
        };
    }

    /**
     * Muat semua pengaturan ke memory cache lokal
     */
    private function loadCache(): void
    {
        self::$cachedSettings = [];
        if (!$this->isDbConnected()) {
            return;
        }

        try {
            $sql = "SELECT setting_key, setting_value, setting_type FROM `{$this->table}`";
            $rows = $this->fetchAll($sql);
            foreach ($rows as $r) {
                self::$cachedSettings[$r['setting_key']] = $this->castValue($r['setting_value'], $r['setting_type']);
            }
        } catch (\Throwable $e) {
            // Ignore jika tabel belum siap
        }
    }

    /**
     * Sinkronkan parameter terpilih ke file .env di root project
     */
    private function syncToEnvFile(array $settings): void
    {
        $envPath = defined('ROOT_PATH') ? ROOT_PATH . DIRECTORY_SEPARATOR . '.env' : dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . '.env';

        // Mapping setting key ke ENV key
        $map = [
            'app_name'            => 'APP_NAME',
            'app_env'             => 'APP_ENV',
            'school_name'         => 'SCHOOL_NAME',
            'wa_gateway_provider' => 'WA_GATEWAY_PROVIDER',
            'wa_api_token'        => 'WA_API_TOKEN',
            'wa_api_url'          => 'WA_API_URL',
            'ai_provider'         => 'AI_PROVIDER',
            'ai_api_key'          => 'AI_API_KEY',
            'ai_model_name'       => 'AI_MODEL_NAME',
            'ai_base_url'         => 'AI_BASE_URL',
            'chatbot_server_url'  => 'CHATBOT_SERVER_URL',
        ];

        $envContent = file_exists($envPath) ? file_get_contents($envPath) : "";

        foreach ($settings as $k => $v) {
            if (isset($map[$k])) {
                $envKey = $map[$k];
                $valStr = (string)$v;
                // Quote jika mengandung spasi
                if (str_contains($valStr, ' ')) {
                    $valStr = '"' . addcslashes($valStr, '"') . '"';
                }

                $pattern = "/^{$envKey}=.*/m";
                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, "{$envKey}={$valStr}", $envContent);
                } else {
                    $envContent .= "\n{$envKey}={$valStr}";
                }
            }
        }

        @file_put_contents($envPath, trim($envContent) . "\n");
    }
}
