<?php
/**
 * SmartExam AI - Tahap 3: Database Migration untuk Sistem Pengaturan Dinamis
 */

require_once __DIR__ . '/../../app/Config/Config.php';
require_once __DIR__ . '/../../app/Core/Database.php';

$db = \App\Core\Database::getInstance();
if (!$db) {
    echo "Gagal menghubungkan ke database.\n";
    exit(1);
}

try {
    // 1. Buat Tabel system_settings
    $sqlTable = "CREATE TABLE IF NOT EXISTS `system_settings` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'exam, proctoring, whatsapp, ai, general',
        `setting_key` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nama kunci parameter unik',
        `setting_value` TEXT NULL COMMENT 'Nilai pengaturan',
        `setting_type` ENUM('string', 'number', 'boolean', 'json') NOT NULL DEFAULT 'string' COMMENT 'Tipe data casting',
        `title` VARCHAR(150) NOT NULL COMMENT 'Judul label konfigurasi manusiawi',
        `description` VARCHAR(255) NULL COMMENT 'Penjelasan fungsi konfigurasi',
        `is_secret` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Masking (seperti API key atau token)',
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_group` (`setting_group`),
        INDEX `idx_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel pengaturan parameter dinamis sistem aplikasi SmartExam AI';";

    $db->exec($sqlTable);
    echo "✓ Tabel system_settings berhasil dipastikan ada.\n";

    // 2. Daftar Nilai Default Parameter Sistem
    $defaultSettings = [
        // GROUP: exam (Parameter Pelaksanaan Ujian)
        [
            'setting_group' => 'exam',
            'setting_key'   => 'exam_default_duration',
            'setting_value' => '90',
            'setting_type'  => 'number',
            'title'         => 'Durasi Default Ujian (Menit)',
            'description'   => 'Lama waktu pengerjaan default saat guru membuat paket ujian baru.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'exam',
            'setting_key'   => 'exam_token_enabled',
            'setting_value' => '1',
            'setting_type'  => 'boolean',
            'title'         => 'Wajibkan Token Ujian',
            'description'   => 'Peserta didik wajib memasukkan token resmi untuk membuka lembar soal.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'exam',
            'setting_key'   => 'exam_token_length',
            'setting_value' => '6',
            'setting_type'  => 'number',
            'title'         => 'Panjang Karakter Token',
            'description'   => 'Panjang digit karakter token alfanumerik otomatis (standar: 6 karakter).',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'exam',
            'setting_key'   => 'exam_random_questions',
            'setting_value' => '1',
            'setting_type'  => 'boolean',
            'title'         => 'Acak Urutan Butir Soal',
            'description'   => 'Nilai default untuk mengacak susunan butir soal untuk setiap siswa.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'exam',
            'setting_key'   => 'exam_random_options',
            'setting_value' => '1',
            'setting_type'  => 'boolean',
            'title'         => 'Acak Pilihan Opsi Ganda (A-E)',
            'description'   => 'Nilai default untuk mengacak opsi jawaban pilihan ganda.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'exam',
            'setting_key'   => 'exam_default_kkm',
            'setting_value' => '75.0',
            'setting_type'  => 'number',
            'title'         => 'Nilai Standar KKM Default',
            'description'   => 'Nilai batas kriteria kelulusan minimal standar asesmen.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'exam',
            'setting_key'   => 'exam_late_tolerance_minutes',
            'setting_value' => '15',
            'setting_type'  => 'number',
            'title'         => 'Toleransi Keterlambatan (Menit)',
            'description'   => 'Batas menit keterlambatan siswa masuk ruang ujian setelah jadwal dimulai.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'exam',
            'setting_key'   => 'exam_auto_submit_on_timeout',
            'setting_value' => '1',
            'setting_type'  => 'boolean',
            'title'         => 'Submit Otomatis Saat Waktu Habis',
            'description'   => 'Lembar jawaban siswa otomatis tersimpan dan terkirim ketika hitung mundur berakhir.',
            'is_secret'     => 0
        ],

        // GROUP: proctoring (AI Proctoring & Keamanan Ujian)
        [
            'setting_group' => 'proctoring',
            'setting_key'   => 'proctoring_default_ai',
            'setting_value' => '1',
            'setting_type'  => 'boolean',
            'title'         => 'Aktifkan AI Proctoring Default',
            'description'   => 'Default menyalakan AI pengawasan wajah dan tab browser.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'proctoring',
            'setting_key'   => 'proctoring_default_dual_camera',
            'setting_value' => '1',
            'setting_type'  => 'boolean',
            'title'         => 'Wajibkan Dual Camera Default',
            'description'   => 'Kamera depan laptop dan kamera samping ponsel siswa.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'proctoring',
            'setting_key'   => 'proctoring_tab_switch_limit',
            'setting_value' => '3',
            'setting_type'  => 'number',
            'title'         => 'Batas Pelanggaran Pindah Tab',
            'description'   => 'Maksimal siswa berganti tab browser sebelum lembar ujian terkunci otomatis.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'proctoring',
            'setting_key'   => 'proctoring_face_lost_seconds',
            'setting_value' => '10',
            'setting_type'  => 'number',
            'title'         => 'Toleransi Wajah Tidak Terdeteksi (Detik)',
            'description'   => 'Durasi deteksi kamera jika wajah siswa menghilang dari pandangan pengawasan.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'proctoring',
            'setting_key'   => 'proctoring_audio_detection',
            'setting_value' => '1',
            'setting_type'  => 'boolean',
            'title'         => 'Deteksi Suara / Kebisingan Ruangan',
            'description'   => 'Mendeteksi suara percakapan mencurigakan selama ujian berlangsung.',
            'is_secret'     => 0
        ],

        // GROUP: whatsapp (Gateway & Notifikasi)
        [
            'setting_group' => 'whatsapp',
            'setting_key'   => 'wa_gateway_enabled',
            'setting_value' => '1',
            'setting_type'  => 'boolean',
            'title'         => 'Status WhatsApp Gateway',
            'description'   => 'Aktifkan pengiriman OTP & notifikasi jadwal ujian otomatis via WhatsApp.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'whatsapp',
            'setting_key'   => 'wa_gateway_provider',
            'setting_value' => 'fonnte',
            'setting_type'  => 'string',
            'title'         => 'Provider WhatsApp',
            'description'   => 'Pilihan layanan gateway: fonnte, wablas, generic, atau simulation.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'whatsapp',
            'setting_key'   => 'wa_api_token',
            'setting_value' => defined('WA_API_TOKEN') ? WA_API_TOKEN : 'Uz2XNDo4JQatmBAeKqBf',
            'setting_type'  => 'string',
            'title'         => 'API Token / Key WhatsApp',
            'description'   => 'Token otorisasi dari akun Fonnte / Wablas Anda.',
            'is_secret'     => 1
        ],
        [
            'setting_group' => 'whatsapp',
            'setting_key'   => 'wa_api_url',
            'setting_value' => defined('WA_API_URL') ? WA_API_URL : 'https://api.fonnte.com/send',
            'setting_type'  => 'string',
            'title'         => 'API Endpoint URL',
            'description'   => 'URL endpoint pengiriman pesan POST.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'whatsapp',
            'setting_key'   => 'wa_sender_name',
            'setting_value' => 'SmartExam AI UJOL',
            'setting_type'  => 'string',
            'title'         => 'Nama Pengirim WhatsApp',
            'description'   => 'Identitas pengirim yang dicantumkan pada header pesan.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'whatsapp',
            'setting_key'   => 'wa_otp_expiry_minutes',
            'setting_value' => '15',
            'setting_type'  => 'number',
            'title'         => 'Masa Kedaluwarsa OTP (Menit)',
            'description'   => 'Batas waktu validitas kode OTP WhatsApp pendaftaran akun.',
            'is_secret'     => 0
        ],

        // GROUP: ai (Integrasi AI & Chatbot)
        [
            'setting_group' => 'ai',
            'setting_key'   => 'ai_provider',
            'setting_value' => 'gemini',
            'setting_type'  => 'string',
            'title'         => 'Provider AI Asisten',
            'description'   => 'Model provider: gemini, openai, atau local-llm.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'ai',
            'setting_key'   => 'ai_api_key',
            'setting_value' => '',
            'setting_type'  => 'string',
            'title'         => 'AI API Key',
            'description'   => 'Kunci API Google Gemini atau OpenAI untuk asisten chatbot & pembuatan soal otomatis.',
            'is_secret'     => 1
        ],
        [
            'setting_group' => 'ai',
            'setting_key'   => 'ai_model_name',
            'setting_value' => 'gemini-1.5-flash',
            'setting_type'  => 'string',
            'title'         => 'Nama Model AI',
            'description'   => 'Versi model yang digunakan (cth: gemini-1.5-flash, gpt-4o-mini).',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'ai',
            'setting_key'   => 'ai_base_url',
            'setting_value' => 'https://generativelanguage.googleapis.com',
            'setting_type'  => 'string',
            'title'         => 'Base URL / Endpoint API',
            'description'   => 'URL Base API endpoint provider AI (opsional untuk OpenAI-compatible/Custom: Groq, DeepSeek, OpenRouter, dll).',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'ai',
            'setting_key'   => 'chatbot_server_url',
            'setting_value' => 'http://localhost:3001',
            'setting_type'  => 'string',
            'title'         => 'URL Server Chatbot Node.js',
            'description'   => 'Endpoint server background chatbot interaktif real-time.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'ai',
            'setting_key'   => 'ai_providers_list',
            'setting_value' => '[]',
            'setting_type'  => 'json',
            'title'         => 'Daftar Provider AI Tersimpan',
            'description'   => 'Daftar penyedia dan model AI yang dikonfigurasikan sistem.',
            'is_secret'     => 0
        ],

        // GROUP: general (Identitas Sekolah & Environment)
        [
            'setting_group' => 'general',
            'setting_key'   => 'app_name',
            'setting_value' => 'SmartExam AI',
            'setting_type'  => 'string',
            'title'         => 'Nama Aplikasi',
            'description'   => 'Nama sistem ujian online yang tampil di header dan footer.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'general',
            'setting_key'   => 'school_name',
            'setting_value' => 'SMA Negeri 1 Prestasi Bangsa',
            'setting_type'  => 'string',
            'title'         => 'Nama Sekolah / Institusi',
            'description'   => 'Nama resmi instansi pendidikan penyelenggara asesmen.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'general',
            'setting_key'   => 'academic_year',
            'setting_value' => '2026/2027',
            'setting_type'  => 'string',
            'title'         => 'Tahun Ajaran Aktif',
            'description'   => 'Periode tahun ajaran yang sedang berjalan.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'general',
            'setting_key'   => 'active_semester',
            'setting_value' => 'Ganjil',
            'setting_type'  => 'string',
            'title'         => 'Semester Aktif',
            'description'   => 'Pilihan: Ganjil atau Genap.',
            'is_secret'     => 0
        ],
        [
            'setting_group' => 'general',
            'setting_key'   => 'app_env',
            'setting_value' => defined('APP_ENV') ? APP_ENV : 'development',
            'setting_type'  => 'string',
            'title'         => 'Environment Aplikasi',
            'description'   => 'Pilihan: development (pengujian) atau production (rilis operasional).',
            'is_secret'     => 0
        ]
    ];

    $stmtInsert = $db->prepare("
        INSERT INTO `system_settings` (`setting_group`, `setting_key`, `setting_value`, `setting_type`, `title`, `description`, `is_secret`)
        VALUES (:group, :key, :val, :type, :title, :desc, :secret)
        ON DUPLICATE KEY UPDATE 
            `title` = VALUES(`title`),
            `description` = VALUES(`description`),
            `setting_type` = VALUES(`setting_type`)
    ");

    $count = 0;
    foreach ($defaultSettings as $s) {
        $stmtInsert->execute([
            ':group'  => $s['setting_group'],
            ':key'    => $s['setting_key'],
            ':val'    => $s['setting_value'],
            ':type'   => $s['setting_type'],
            ':title'  => $s['title'],
            ':desc'   => $s['description'],
            ':secret' => $s['is_secret']
        ]);
        $count++;
    }

    echo "✓ Berhasil menanam {$count} parameter default sistem ke tabel system_settings.\n";

} catch (\Exception $e) {
    echo "Terjadi error migrasi: " . $e->getMessage() . "\n";
    exit(1);
}
