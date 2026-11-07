<?php
/**
 * SmartExam AI - Sanitization & Security Helper
 * Sanitasi data input dan proteksi Cross-Site Scripting (XSS).
 */

namespace App\Helpers;

class SanitizeHelper
{
    /**
     * Sanitasi data input string atau array
     */
    public static function clean(mixed $data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::clean($value);
            }
            return $data;
        }

        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }

        return $data;
    }

    /**
     * Sanitasi khusus email
     */
    public static function email(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
}


