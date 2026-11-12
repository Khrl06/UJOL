-- ============================================================================
-- SmartExam AI - Skema Basis Data Relasional Lengkap
-- Sistem Ujian Online Berbasis AI untuk SMA / Sederajat
-- 13 Tabel Utama + Relasi Foreign Key + Seeder Data Dummy Development
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `smartexam_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `smartexam_db`;

-- Nonaktifkan pengecekan foreign key sementara saat dropping tabel
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `proctoring_events`;
DROP TABLE IF EXISTS `results`;
DROP TABLE IF EXISTS `answers`;
DROP TABLE IF EXISTS `exam_participants`;
DROP TABLE IF EXISTS `exams`;
DROP TABLE IF EXISTS `questions`;
DROP TABLE IF EXISTS `question_banks`;
DROP TABLE IF EXISTS `subjects`;
DROP TABLE IF EXISTS `teachers`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. TABEL: users (Akun Pengguna)
-- ============================================================================
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama lengkap pengguna',
    `username` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Username unik login',
    `email` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Alamat email pengguna (opsional)',
    `password` VARCHAR(255) NOT NULL COMMENT 'Hash sandi bcrypt',
    `role` ENUM('admin', 'guru', 'siswa') NOT NULL DEFAULT 'siswa' COMMENT 'Peran pengguna dalam sistem',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Aktif, 0 = Nonaktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_username` (`username`),
    INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel akun pengguna sistem';

-- ============================================================================
-- 2. TABEL: classes (Rombongan Belajar / Kelas SMA)
-- ============================================================================
CREATE TABLE `classes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `class_name` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nama rombel, misal: XII MIPA 1',
    `grade_level` ENUM('10', '11', '12') NOT NULL COMMENT 'Tingkat jenjang kelas SMA',
    `major` VARCHAR(50) NOT NULL DEFAULT 'MIPA' COMMENT 'Jurusan: MIPA, IPS, BAHASA, UMUM',
    `academic_year` VARCHAR(20) NOT NULL DEFAULT '2026/2027' COMMENT 'Tahun ajaran aktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_classes_grade` (`grade_level`),
    INDEX `idx_classes_academic_year` (`academic_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel kelas dan rombongan belajar';

-- ============================================================================
-- 3. TABEL: students (Biodata Peserta Didik)
-- ============================================================================
CREATE TABLE `students` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE COMMENT 'Relasi 1:1 ke tabel users',
    `class_id` INT UNSIGNED NOT NULL COMMENT 'Relasi N:1 ke tabel classes',
    `nisn` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Nomor Induk Siswa Nasional',
    `gender` ENUM('L', 'P') NOT NULL DEFAULT 'L' COMMENT 'Laki-laki / Perempuan',
    `phone` VARCHAR(20) NULL COMMENT 'Nomor WhatsApp / telepon siswa',
    `address` TEXT NULL COMMENT 'Alamat tempat tinggal',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_students_class` (`class_id`),
    INDEX `idx_students_nisn` (`nisn`),
    CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_students_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel data profil peserta didik';

-- ============================================================================
-- 4. TABEL: teachers (Biodata Guru Pengajar)
-- ============================================================================
CREATE TABLE `teachers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE COMMENT 'Relasi 1:1 ke tabel users',
    `nip` VARCHAR(30) NULL UNIQUE COMMENT 'Nomor Induk Pegawai',
    `nuptk` VARCHAR(30) NULL COMMENT 'Nomor Unik Pendidik dan Tenaga Kependidikan',
    `gender` ENUM('L', 'P') NOT NULL DEFAULT 'L' COMMENT 'Laki-laki / Perempuan',
    `phone` VARCHAR(20) NULL COMMENT 'Nomor WhatsApp / telepon guru',
    `address` TEXT NULL COMMENT 'Alamat tempat tinggal guru',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_teachers_nip` (`nip`),
    CONSTRAINT `fk_teachers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel data profil guru pengajar';

-- ============================================================================
-- 5. TABEL: subjects (Mata Pelajaran SMA)
-- ============================================================================
CREATE TABLE `subjects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Kode mapel, misal: INF-12, MAT-12',
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama mata pelajaran',
    `description` TEXT NULL COMMENT 'Deskripsi silabus / materi pokok',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_subjects_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel mata pelajaran';

-- ============================================================================
-- 6. TABEL: question_banks (Bank Soal)
-- ============================================================================
CREATE TABLE `question_banks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `teacher_id` INT UNSIGNED NOT NULL COMMENT 'Guru pembuat bank soal',
    `subject_id` INT UNSIGNED NOT NULL COMMENT 'Mata pelajaran terkait',
    `grade_level` VARCHAR(10) NOT NULL DEFAULT '12' COMMENT 'Tingkat kelas sasaran, cth: 10, 11, 12',
    `title` VARCHAR(150) NOT NULL COMMENT 'Judul bank soal',
    `description` TEXT NULL COMMENT 'Deskripsi atau kisi-kisi bank soal',
    `total_questions` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah butir soal di dalam paket',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status bank soal: 1=Aktif, 0=Nonaktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_question_banks_teacher` (`teacher_id`),
    INDEX `idx_question_banks_subject` (`subject_id`),
    CONSTRAINT `fk_question_banks_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_question_banks_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel bank soal guru';

-- ============================================================================
-- 7. TABEL: questions (Butir Soal)
-- ============================================================================
CREATE TABLE `questions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `question_bank_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke bank soal induk',
    `question_text` TEXT NOT NULL COMMENT 'Isi teks pertanyaan (bisa HTML atau plain)',
    `question_type` ENUM('multiple_choice', 'essay', 'true_false') NOT NULL DEFAULT 'multiple_choice' COMMENT 'Tipe bentuk soal',
    `options` JSON NULL COMMENT 'Pilihan jawaban JSON, cth: {"A":"...","B":"...","C":"...","D":"...","E":"..."}',
    `correct_answer` TEXT NOT NULL COMMENT 'Kunci jawaban benar (misal: "A" atau teks kunci/pedoman)',
    `score_weight` DECIMAL(5, 2) NOT NULL DEFAULT 1.00 COMMENT 'Bobot nilai butir soal ini',
    `difficulty` ENUM('mudah', 'sedang', 'sulit') NOT NULL DEFAULT 'sedang' COMMENT 'Tingkat kesulitan soal',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status soal: 1=Aktif, 0=Nonaktif',
    `explanation` TEXT NULL COMMENT 'Pembahasan soal',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_questions_bank` (`question_bank_id`),
    INDEX `idx_questions_type` (`question_type`),
    INDEX `idx_questions_diff` (`difficulty`),
    CONSTRAINT `fk_questions_bank` FOREIGN KEY (`question_bank_id`) REFERENCES `question_banks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel butir soal ujian';

-- ============================================================================
-- 8. TABEL: exams (Paket / Jadwal Pelaksanaan Ujian)
-- ============================================================================
CREATE TABLE `exams` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `teacher_id` INT UNSIGNED NOT NULL COMMENT 'Guru penanggung jawab / pembuat ujian',
    `subject_id` INT UNSIGNED NOT NULL COMMENT 'Mata pelajaran yang diujikan',
    `question_bank_id` INT UNSIGNED NULL COMMENT 'Bank soal referensi yang digunakan',
    `title` VARCHAR(150) NOT NULL COMMENT 'Judul ujian, misal: PAS Gasal Informatika XII',
    `description` TEXT NULL COMMENT 'Instruksi dan tata tertib ujian',
    `start_date` DATE NOT NULL COMMENT 'Tanggal mulai akses ujian',
    `end_date` DATE NOT NULL COMMENT 'Tanggal berakhir akses ujian',
    `exam_date` DATE NOT NULL COMMENT 'Tanggal pelaksanaan utama',
    `start_time` TIME NOT NULL COMMENT 'Waktu mulai ujian (WIB)',
    `end_time` TIME NOT NULL COMMENT 'Batas akhir akses ujian (WIB)',
    `duration_minutes` INT UNSIGNED NOT NULL DEFAULT 90 COMMENT 'Durasi pengerjaan dalam menit',
    `total_questions` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah butir soal yang diujikan',
    `is_random_questions` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Acak urutan soal, 0=Sesuai urutan bank',
    `is_random_options` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Acak opsi pilihan ganda A-E, 0=Tetap',
    `max_attempts` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Batas maksimal pengulangan ujian',
    `passing_grade` DECIMAL(5, 2) NOT NULL DEFAULT 75.00 COMMENT 'Kriteria Ketuntasan Minimal (KKM)',
    `enable_dual_camera` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Wajibkan kamera ganda, 0=Kamera utama saja',
    `enable_ai_proctoring` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Aktifkan pengawasan AI, 0=Nonaktif',
    `token` VARCHAR(20) NOT NULL COMMENT 'Token unik masuk ujian',
    `status` ENUM('draft', 'published', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'draft' COMMENT 'Status siklus ujian',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_exams_teacher` (`teacher_id`),
    INDEX `idx_exams_subject` (`subject_id`),
    INDEX `idx_exams_status` (`status`),
    INDEX `idx_exams_date` (`exam_date`),
    CONSTRAINT `fk_exams_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exams_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exams_bank` FOREIGN KEY (`question_bank_id`) REFERENCES `question_banks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel jadwal dan paket pelaksanaan ujian';

-- ============================================================================
-- 9. TABEL: exam_participants (Peserta Sesi Ujian)
-- ============================================================================
CREATE TABLE `exam_participants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `exam_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke paket ujian',
    `student_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke siswa peserta ujian',
    `start_time` DATETIME NULL COMMENT 'Waktu presisi siswa mulai mengerjakan',
    `submit_time` DATETIME NULL COMMENT 'Waktu presisi siswa mengumpulkan ujian',
    `status` ENUM('registered', 'ongoing', 'submitted', 'graded') NOT NULL DEFAULT 'registered' COMMENT 'Status peserta saat ujian',
    `score` DECIMAL(5, 2) NULL COMMENT 'Nilai total yang diraih siswa',
    `ip_address` VARCHAR(45) NULL COMMENT 'Alamat IP koneksi peserta',
    `user_agent` TEXT NULL COMMENT 'Informasi browser & OS peserta',
    `question_order` TEXT NULL COMMENT 'Urutan ID butir soal yang diujikan (JSON)',
    `supervisor_notes` TEXT NULL COMMENT 'Catatan evaluasi pengawas ujian terhadap peserta',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_exam_student_unique` (`exam_id`, `student_id`),
    INDEX `idx_participants_status` (`status`),
    CONSTRAINT `fk_participants_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_participants_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel partisipasi peserta dalam ujian';

-- ============================================================================
-- 10. TABEL: answers (Lembar Jawaban Siswa)
-- ============================================================================
CREATE TABLE `answers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `exam_participant_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke peserta ujian',
    `question_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke butir soal yang dijawab',
    `student_answer` TEXT NULL COMMENT 'Jawaban pilihan/esai dari siswa',
    `is_correct` TINYINT(1) NULL COMMENT '1 = Benar, 0 = Salah, NULL = Belum dikoreksi',
    `score_obtained` DECIMAL(5, 2) NOT NULL DEFAULT 0.00 COMMENT 'Skor yang diperoleh untuk nomor ini',
    `is_doubtful` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Ditandai ragu-ragu, 0 = Yakin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_participant_question_unique` (`exam_participant_id`, `question_id`),
    INDEX `idx_answers_correct` (`is_correct`),
    CONSTRAINT `fk_answers_participant` FOREIGN KEY (`exam_participant_id`) REFERENCES `exam_participants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel lembar jawaban peserta per butir soal';

-- ============================================================================
-- 11. TABEL: results (Rekapitulasi Nilai & Hasil Ujian)
-- ============================================================================
CREATE TABLE `results` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `exam_participant_id` INT UNSIGNED NOT NULL UNIQUE COMMENT 'Relasi 1:1 ke peserta ujian',
    `total_questions` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total butir soal yang diujikan',
    `correct_answers` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah butir soal dijawab benar',
    `wrong_answers` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah butir soal dijawab salah',
    `unattempted` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah butir soal tidak dijawab',
    `final_score` DECIMAL(5, 2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai akhir skala 0-100',
    `is_passed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Lulus KKM, 0 = Remedial',
    `notes` TEXT NULL COMMENT 'Catatan evaluasi guru',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_results_score` (`final_score`),
    INDEX `idx_results_passed` (`is_passed`),
    CONSTRAINT `fk_results_participant` FOREIGN KEY (`exam_participant_id`) REFERENCES `exam_participants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel rekapitulasi hasil dan nilai akhir ujian';

-- ============================================================================
-- 12. TABEL: proctoring_events (Pencatatan Insiden Pengawasan)
-- ============================================================================
CREATE TABLE `proctoring_events` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `exam_participant_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke peserta ujian yang diawasi',
    `camera` VARCHAR(50) NOT NULL DEFAULT 'cam1' COMMENT 'cam1, cam2, system',
    `event_type` VARCHAR(50) NOT NULL COMMENT 'FACE_MISSING, MULTIPLE_FACE, FACE_TOO_FAR, CAMERA_BLOCKED, CAMERA_DISCONNECTED, SUSPICIOUS_OBJECT, BROWSER_ACTIVITY_EVENT',
    `confidence_score` DECIMAL(5, 2) NOT NULL DEFAULT 0.00 COMMENT 'Tingkat keyakinan AI 0-100 persen',
    `duration` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Durasi anomali berlangsung dalam detik',
    `status` ENUM('NORMAL', 'REVIEW', 'REVIEWED', 'DISMISSED') NOT NULL DEFAULT 'REVIEW' COMMENT 'Status verifikasi manusia (Guru/Admin)',
    `severity` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'low' COMMENT 'Tingkat keparahan anomali',
    `description` TEXT NULL COMMENT 'Keterangan detail log anomali',
    `evidence_reference` VARCHAR(255) NULL COMMENT 'Path URL/referensi file bukti rekaman/snapshot kamera saat kejadian',
    `snapshot_url` VARCHAR(255) NULL COMMENT 'Alias path URL foto bukti rekaman kamera saat kejadian',
    `event_time` DATETIME NOT NULL COMMENT 'Waktu tepat terjadinya event',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_proctoring_participant` (`exam_participant_id`),
    INDEX `idx_proctoring_type` (`event_type`),
    INDEX `idx_proctoring_status` (`status`),
    INDEX `idx_proctoring_camera` (`camera`),
    INDEX `idx_proctoring_severity` (`severity`),
    CONSTRAINT `fk_proctoring_participant` FOREIGN KEY (`exam_participant_id`) REFERENCES `exam_participants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel log insiden pengawasan ujian berbasis AI';

-- ============================================================================
-- 13. TABEL: audit_logs (Jejak Audit Aktivitas Pengguna)
-- ============================================================================
CREATE TABLE `audit_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL COMMENT 'User pelaku aktivitas (NULL jika guest/system)',
    `action` VARCHAR(50) NOT NULL COMMENT 'LOGIN, LOGOUT, CREATE_EXAM, SUBMIT_EXAM, UPDATE_SCORE, dsb.',
    `description` TEXT NULL COMMENT 'Deskripsi detail aksi yang dilakukan',
    `ip_address` VARCHAR(45) NULL COMMENT 'Alamat IP pelaku',
    `user_agent` TEXT NULL COMMENT 'Browser/perangkat pelaku',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_time` (`created_at`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel jejak audit aktivitas pengguna dan sistem';

-- ============================================================================
-- 14. TABEL: notifications (Notifikasi In-App & Multi-Channel)
-- ============================================================================
CREATE TABLE `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'Penerima notifikasi',
    `title` VARCHAR(150) NOT NULL COMMENT 'Judul pemberitahuan',
    `message` TEXT NOT NULL COMMENT 'Isi detail pesan notifikasi',
    `type` ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info' COMMENT 'Tipe styling notifikasi',
    `icon` VARCHAR(50) NOT NULL DEFAULT 'bi-bell' COMMENT 'Ikon Bootstrap Icons',
    `link` VARCHAR(255) NULL COMMENT 'URL redirect saat notifikasi diklik',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = Belum dibaca, 1 = Sudah dibaca',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_notifications_user` (`user_id`),
    INDEX `idx_notifications_read` (`is_read`),
    INDEX `idx_notifications_created` (`created_at`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel pemberitahuan in-app dan status keterbacaan';

-- ============================================================================
-- 15. TABEL: exam_reminders (Log Pengiriman Pengingat Ujian & WhatsApp)
-- ============================================================================
CREATE TABLE `exam_reminders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `exam_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke ujian yang diingatkan',
    `user_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke siswa/guru penerima',
    `reminder_type` ENUM('h_minus_1', 'm_minus_30', 'exam_started', 'score_released') NOT NULL COMMENT 'Jenis pengingat',
    `channel` VARCHAR(50) NOT NULL DEFAULT 'all' COMMENT 'whatsapp, in_app, all',
    `status` ENUM('sent', 'failed', 'simulated') NOT NULL DEFAULT 'sent' COMMENT 'Status pengiriman',
    `recipient_phone` VARCHAR(30) NULL COMMENT 'Nomor WhatsApp tujuan',
    `message_preview` TEXT NULL COMMENT 'Isi teks pesan yang dikirimkan',
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_exam_user_reminder` (`exam_id`, `user_id`, `reminder_type`),
    INDEX `idx_reminder_exam` (`exam_id`),
    INDEX `idx_reminder_user` (`user_id`),
    CONSTRAINT `fk_reminders_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reminders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel log pencegahan duplikasi pengingat ujian dan status WhatsApp/In-App';

-- ============================================================================
-- 16. TABEL: whatsapp_otps (Verifikasi OTP Pendaftaran Akun via WhatsApp)
-- ============================================================================
CREATE TABLE `whatsapp_otps` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `phone` VARCHAR(30) NOT NULL COMMENT 'Nomor WhatsApp pendaftar',
    `otp_code` VARCHAR(10) NOT NULL COMMENT 'Kode OTP 6-digit',
    `registration_data` TEXT NOT NULL COMMENT 'Data pendaftaran akun dalam format JSON aman',
    `expires_at` DATETIME NOT NULL COMMENT 'Waktu kedaluwarsa OTP',
    `is_used` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Sudah diverifikasi, 0 = Belum',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_phone_otp` (`phone`, `otp_code`),
    INDEX `idx_phone_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel verifikasi kode OTP pendaftaran akun baru via WhatsApp';

-- 17. TABEL: email_otps (Legacy Fallback Verifikasi OTP via Email)
-- ============================================================================
CREATE TABLE `email_otps` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(100) NOT NULL COMMENT 'Alamat email pendaftar',
    `otp_code` VARCHAR(10) NOT NULL COMMENT 'Kode OTP 6-digit',
    `registration_data` TEXT NOT NULL COMMENT 'Data pendaftaran akun dalam format JSON aman',
    `expires_at` DATETIME NOT NULL COMMENT 'Waktu kedaluwarsa OTP',
    `is_used` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Sudah diverifikasi, 0 = Belum',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_otp` (`email`, `otp_code`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel verifikasi kode OTP pendaftaran akun baru via email';

-- ============================================================================
-- 18. TABEL: system_settings (Pengaturan Parameter Dinamis Sistem)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `system_settings` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel pengaturan parameter dinamis sistem aplikasi SmartExam AI';

-- ============================================================================
-- SEEDER DATA DUMMY DEVELOPMENT (SMA LENGKAP)
-- Semua password dienkripsi menggunakan bcrypt:
--   admin  : admin123
--   guru   : guru123
--   siswa  : siswa123
--   guru2  : guru123
--   siswa2 : siswa123
-- ============================================================================

-- 1. SEEDER: users
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `is_active`, `created_at`) VALUES
(1, 'Administrator Utama', 'admin', 'admin@smartexam.sch.id', '$2y$10$Gin1PFRH33WhBmBVNl.eTOqukoRv0J5pxT0OpWeK8s.H8lhkEESl.', 'admin', 1, NOW()),
(2, 'Budi Prasetyo, S.Pd., M.Kom.', 'guru', 'budi.guru@smartexam.sch.id', '$2y$10$fAkR5UVZzxltcA24ezE4SOB0xIln1kfYYHWuqzQFLevL.nwrUUJkK', 'guru', 1, NOW()),
(3, 'Ahmad Fauzi Pratama', 'siswa', 'fauzi.siswa@smartexam.sch.id', '$2y$10$.f5u.pk80pMoOIub9BAsj.4JqxUBDbfkSfVwjSZf5yesYmfXuX92e', 'siswa', 1, NOW()),
(4, 'Dewi Ratnasari, S.Pd.', 'dewi.guru', 'dewi.guru@smartexam.sch.id', '$2y$10$fAkR5UVZzxltcA24ezE4SOB0xIln1kfYYHWuqzQFLevL.nwrUUJkK', 'guru', 1, NOW()),
(5, 'Siti Nurhaliza Putri', 'siti.siswa', 'siti.siswa@smartexam.sch.id', '$2y$10$.f5u.pk80pMoOIub9BAsj.4JqxUBDbfkSfVwjSZf5yesYmfXuX92e', 'siswa', 1, NOW());

-- 2. SEEDER: classes
INSERT INTO `classes` (`id`, `class_name`, `grade_level`, `major`, `academic_year`, `created_at`) VALUES
(1, 'XII MIPA 1', '12', 'MIPA', '2026/2027', NOW()),
(2, 'XII MIPA 2', '12', 'MIPA', '2026/2027', NOW()),
(3, 'XI MIPA 3', '11', 'MIPA', '2026/2027', NOW()),
(4, 'X E-1', '10', 'UMUM', '2026/2027', NOW());

-- 3. SEEDER: teachers
INSERT INTO `teachers` (`id`, `user_id`, `nip`, `nuptk`, `gender`, `phone`, `address`, `created_at`) VALUES
(1, 2, '198904122015031002', '3456789012345678', 'L', '081234567890', 'Jl. Merdeka No. 45, Jakarta Selatan', NOW()),
(2, 4, '199208242018022003', '9876543210987654', 'P', '081398765432', 'Jl. Sudirman No. 12, Jakarta Pusat', NOW());

-- 4. SEEDER: students
INSERT INTO `students` (`id`, `user_id`, `class_id`, `nisn`, `gender`, `phone`, `address`, `created_at`) VALUES
(1, 3, 1, '0068192341', 'L', '082155566778', 'Jl. Tebet Raya No. 8, Jakarta Selatan', NOW()),
(2, 5, 1, '0068192342', 'P', '082188899001', 'Jl. Mampang Prapatan No. 20, Jakarta Selatan', NOW());

-- 5. SEEDER: subjects
INSERT INTO `subjects` (`id`, `code`, `name`, `description`, `created_at`) VALUES
(1, 'INF-12', 'Informatika', 'Algoritma, Pemrograman, Struktur Data, dan Etika Digital', NOW()),
(2, 'MAT-12', 'Matematika Peminatan', 'Kalkulus, Vektor, dan Geometri Ruang Dimensi Tiga', NOW()),
(3, 'FIS-12', 'Fisika', 'Mekanika Kuantum, Relativitas, dan Induksi Elektromagnetik', NOW()),
(4, 'IND-12', 'Bahasa Indonesia', 'Karya Tulis Ilmiah, Resensi Buku, dan Debat Akademik', NOW());

-- 6. SEEDER: question_banks
INSERT INTO `question_banks` (`id`, `teacher_id`, `subject_id`, `title`, `description`, `total_questions`, `created_at`) VALUES
(1, 1, 1, 'Bank Soal PAS Informatika XII - Gasal', 'Kumpulan soal persiapan Penilaian Akhir Semester gasal kelas XII', 3, NOW()),
(2, 2, 2, 'Bank Soal Kuis Vektor XII MIPA', 'Kumpulan soal latihan vektor ruang dan aljabar linier dasar', 2, NOW());

-- 7. SEEDER: questions
INSERT INTO `questions` (`id`, `question_bank_id`, `question_text`, `question_type`, `options`, `correct_answer`, `score_weight`, `explanation`, `created_at`) VALUES
(1, 1, 'Dalam analisis algoritma, manakah notasi yang digunakan untuk menyatakan kompleksitas waktu terburuk (worst-case)?', 'multiple_choice', '{"A":"Big O Notation (O)","B":"Big Omega Notation (Ω)","C":"Big Theta Notation (θ)","D":"Little o Notation (o)","E":"Small Omega Notation (ω)"}', 'A', 25.00, 'Big-O notation merepresentasikan batas atas (upper bound) yang mencerminkan performa pada kasus terburuk.', NOW()),
(2, 1, 'Struktur data yang mengadopsi prinsip LIFO (Last In First Out) adalah...', 'multiple_choice', '{"A":"Queue (Antrean)","B":"Stack (Tumpukan)","C":"Linked List (Daftar Berkait)","D":"Binary Tree (Pohon Biner)","E":"Graph (Graf)"}', 'B', 25.00, 'Stack beroperasi dengan prinsip LIFO, di mana elemen terakhir yang dimasukkan adalah yang pertama dikeluarkan.', NOW()),
(3, 1, 'Jelaskan perbedaan mendasar antara pencarian linier (Linear Search) dan pencarian biner (Binary Search) beserta syarat penggunaannya!', 'essay', NULL, 'Pencarian linier memeriksa setiap elemen satu per satu tanpa syarat urut. Pencarian biner membagi data menjadi dua bagian dan mensyaratkan kumpulan data telah terurut.', 50.00, 'Kunci penilaian: penjelasan syarat data terurut dan efisiensi O(log n) vs O(n).', NOW()),
(4, 2, 'Diberikan vektor a = 2i + 3j - k dan vektor b = 4i - j + 2k. Nilai hasil kali titik (dot product) a · b adalah...', 'multiple_choice', '{"A":"3","B":"5","C":"7","D":"9","E":"11"}', 'A', 50.00, 'a · b = (2*4) + (3*-1) + (-1*2) = 8 - 3 - 2 = 3.', NOW()),
(5, 2, 'Apakah dua vektor yang memiliki hasil kali titik bernilai nol (0) saling tegak lurus (ortogonal)?', 'true_false', '{"A":"Benar","B":"Salah"}', 'A', 50.00, 'Dua vektor tak nol ortogonal jika dan hanya jika dot product keduanya sama dengan 0.', NOW());

-- 8. SEEDER: exams
INSERT INTO `exams` (`id`, `teacher_id`, `subject_id`, `question_bank_id`, `title`, `description`, `start_date`, `end_date`, `exam_date`, `start_time`, `end_time`, `duration_minutes`, `token`, `status`, `created_at`) VALUES
(1, 1, 1, 1, 'Penilaian Akhir Semester - Informatika XII', 'Ujian PAS Gasal. Wajib mengaktifkan pengawasan dual camera dan tidak berpindah tab.', CURDATE(), CURDATE(), CURDATE(), '08:00:00', '10:00:00', 90, 'INF2026', 'ongoing', NOW()),
(2, 2, 2, 2, 'Kuis Harian - Vektor Dimensi Tiga', 'Kuis bab vektor untuk menguji pemahaman konsep dot product dan proyeksi.', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:30:00', 60, 'VEK2026', 'published', NOW());

-- 9. SEEDER: exam_participants
INSERT INTO `exam_participants` (`id`, `exam_id`, `student_id`, `start_time`, `submit_time`, `status`, `score`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 1, NOW() - INTERVAL 45 MINUTE, NOW() - INTERVAL 5 MINUTE, 'submitted', 85.00, '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/128.0', NOW()),
(2, 1, 2, NOW() - INTERVAL 30 MINUTE, NULL, 'ongoing', NULL, '192.168.1.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/128.0', NOW());

-- 10. SEEDER: answers
INSERT INTO `answers` (`id`, `exam_participant_id`, `question_id`, `student_answer`, `is_correct`, `score_obtained`, `created_at`) VALUES
(1, 1, 1, 'A', 1, 25.00, NOW()),
(2, 1, 2, 'B', 1, 25.00, NOW()),
(3, 1, 3, 'Linear search mencari satu per satu pada array acak. Binary search mensyaratkan array terurut dan mencari dengan membagi dua secara logaritmik.', 1, 35.00, NOW());

-- 11. SEEDER: results
INSERT INTO `results` (`id`, `exam_participant_id`, `total_questions`, `correct_answers`, `wrong_answers`, `unattempted`, `final_score`, `is_passed`, `notes`, `created_at`) VALUES
(1, 1, 3, 3, 0, 0, 85.00, 1, 'Pemahaman teori algoritma sangat baik. Pertahankan prestasinya!', NOW());

-- 12. SEEDER: proctoring_events
INSERT INTO `proctoring_events` (`id`, `exam_participant_id`, `event_type`, `severity`, `description`, `snapshot_url`, `event_time`, `created_at`) VALUES
(1, 1, 'tab_switch', 'low', 'Peserta terdeteksi memindahkan fokus tab browser selama 3 detik.', 'assets/proctoring/snapshots/snap_p1_01.jpg', NOW() - INTERVAL 35 MINUTE, NOW()),
(2, 2, 'look_away', 'medium', 'Arah pandangan mata berpaling ke luar area layar selama lebih dari 5 detik.', 'assets/proctoring/snapshots/snap_p2_01.jpg', NOW() - INTERVAL 15 MINUTE, NOW());

-- 13. SEEDER: audit_logs
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'LOGIN', 'Admin login ke sistem kendali pusat.', '127.0.0.1', 'SmartExam Admin Console', NOW() - INTERVAL 2 HOUR),
(2, 2, 'CREATE_EXAM', 'Guru Budi membuat paket ujian Penilaian Akhir Semester - Informatika XII.', '192.168.1.50', 'Mozilla/5.0 (Windows NT 10.0)', NOW() - INTERVAL 1 HOUR),
(3, 3, 'START_EXAM', 'Siswa Ahmad Fauzi memulai pengerjaan ujian PAS Informatika XII.', '192.168.1.105', 'Mozilla/5.0 Chrome/128.0', NOW() - INTERVAL 45 MINUTE),
(4, 3, 'SUBMIT_EXAM', 'Siswa Ahmad Fauzi selesai dan mengumpulkan lembar jawaban.', '192.168.1.105', 'Mozilla/5.0 Chrome/128.0', NOW() - INTERVAL 5 MINUTE);
