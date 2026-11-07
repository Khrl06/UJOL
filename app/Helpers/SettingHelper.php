<?php
/**
 * SmartExam AI - System Setting Helper
 */

namespace App\Helpers {

    use App\Models\SettingModel;

    class SettingHelper
    {
        private static ?SettingModel $model = null;

        /**
         * Dapatkan nilai konfigurasi sistem dinamis
         */
        public static function get(string $key, mixed $default = null): mixed
        {
            if (self::$model === null) {
                self::$model = new SettingModel();
            }
            return self::$model->get($key, $default);
        }

        /**
         * Dapatkan sekelompok konfigurasi dalam satu grup
         */
        public static function getGroup(string $group): array
        {
            if (self::$model === null) {
                self::$model = new SettingModel();
            }
            return self::$model->getByGroup($group);
        }
    }
}

namespace {
    if (!function_exists('app_setting')) {
        /**
         * Global shortcut helper untuk membaca konfigurasi sistem dinamis
         */
        function app_setting(string $key, mixed $default = null): mixed
        {
            return \App\Helpers\SettingHelper::get($key, $default);
        }
    }
}
