<?php
use App\Helpers\CsrfHelper;

$settingsGrouped = $settingsGrouped ?? [];
$activeTab = $activeTab ?? 'exam';

$examSettings       = $settingsGrouped['exam'] ?? [];
$proctoringSettings = $settingsGrouped['proctoring'] ?? [];
$whatsappSettings   = $settingsGrouped['whatsapp'] ?? [];
$aiSettings         = $settingsGrouped['ai'] ?? [];
$generalSettings    = $settingsGrouped['general'] ?? [];

function get_val($settings, $key, $default = '') {
    return isset($settings[$key]) ? $settings[$key]['setting_value'] : $default;
}

function is_checked($settings, $key, $default = false) {
    if (!isset($settings[$key])) return $default;
    $v = strtolower((string)$settings[$key]['setting_value']);
    return in_array($v, ['1', 'true', 'on', 'yes'], true);
}
?>

<!-- Header Halaman -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill text-xs font-semibold">
                <i class="bi bi-sliders2-vertical me-1"></i>Tahap 3 Selesai
            </span>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill text-xs font-semibold">
                <i class="bi bi-check-circle-fill me-1"></i>Persisten DB & .env
            </span>
        </div>
        <h3 class="font-bold text-dark mb-1">Konfigurasi Parameter Sistem</h3>
        <p class="text-muted text-sm mb-0">Kelola pengaturan durasi ujian, token akses, acak butir soal, AI proctoring, gateway WhatsApp, dan parameter environment aplikasi.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-3 font-semibold d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTestWa">
            <i class="bi bi-whatsapp"></i>
            <span>Tes Kirim WhatsApp</span>
        </button>
    </div>
</div>

<!-- Nav Tabs Modern -->
<div class="card-custom mb-4 border-0 shadow-sm p-2 bg-white rounded-4">
    <ul class="nav nav-pills nav-fill gap-1" id="settingsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2.5 rounded-3 text-xs font-semibold d-flex align-items-center justify-content-center gap-2 <?= $activeTab === 'exam' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= base_url('admin/settings?tab=exam') ?>">
                <i class="bi bi-file-earmark-text-fill fs-6"></i>
                <span>Parameter Ujian</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2.5 rounded-3 text-xs font-semibold d-flex align-items-center justify-content-center gap-2 <?= $activeTab === 'proctoring' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= base_url('admin/settings?tab=proctoring') ?>">
                <i class="bi bi-shield-check fs-6"></i>
                <span>AI Proctoring</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2.5 rounded-3 text-xs font-semibold d-flex align-items-center justify-content-center gap-2 <?= $activeTab === 'whatsapp' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= base_url('admin/settings?tab=whatsapp') ?>">
                <i class="bi bi-whatsapp fs-6 text-success"></i>
                <span>WhatsApp Gateway</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2.5 rounded-3 text-xs font-semibold d-flex align-items-center justify-content-center gap-2 <?= $activeTab === 'ai' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= base_url('admin/settings?tab=ai') ?>">
                <i class="bi bi-robot fs-6 text-warning"></i>
                <span>Integrasi AI & API</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2.5 rounded-3 text-xs font-semibold d-flex align-items-center justify-content-center gap-2 <?= $activeTab === 'general' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= base_url('admin/settings?tab=general') ?>">
                <i class="bi bi-buildings-fill fs-6"></i>
                <span>Profil & Environment</span>
            </a>
        </li>
    </ul>
</div>

<div class="tab-content" id="settingsTabContent">
    <!-- ================================================================= -->
    <!-- TAB 1: PARAMETER UJIAN                                           -->
    <!-- ================================================================= -->
    <div class="tab-pane fade <?= $activeTab === 'exam' ? 'show active' : '' ?>" id="tab-exam">
        <form action="<?= base_url('admin/saveSettings') ?>" method="POST">
            <?= CsrfHelper::tokenField() ?>
            <input type="hidden" name="tab" value="exam">

            <div class="card-custom p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="font-bold text-dark mb-1">
                            <i class="bi bi-sliders text-primary me-2"></i>Parameter Standar Pelaksanaan Ujian
                        </h5>
                        <p class="text-muted text-xs mb-0">Konfigurasi default saat guru membuat paket asesmen baru dan aturan auto-submit sistem.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary font-semibold text-xs px-3 py-1.5 rounded-pill">
                        Exam Engine Rules
                    </span>
                </div>

                <div class="row g-4">
                    <!-- Durasi Default -->
                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Durasi Default Ujian
                        </label>
                        <div class="input-group">
                            <input type="number" name="settings[exam_default_duration]" class="form-control font-mono font-bold" min="1" max="480" value="<?= e(get_val($examSettings, 'exam_default_duration', '90')) ?>" required>
                            <span class="input-group-text bg-light text-muted text-xs font-semibold">Menit</span>
                        </div>
                        <small class="text-muted text-xs d-block mt-1">Lama waktu pengerjaan default saat guru membuat jadwal ujian baru.</small>
                    </div>

                    <!-- KKM Default -->
                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Standar KKM Default
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.5" min="0" max="100" name="settings[exam_default_kkm]" class="form-control font-mono font-bold" value="<?= e(get_val($examSettings, 'exam_default_kkm', '75.0')) ?>" required>
                            <span class="input-group-text bg-light text-muted text-xs font-semibold">Skala 100</span>
                        </div>
                        <small class="text-muted text-xs d-block mt-1">Batas nilai minimal untuk menentukan status kelulusan siswa.</small>
                    </div>

                    <!-- Toleransi Keterlambatan -->
                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Toleransi Keterlambatan Masuk
                        </label>
                        <div class="input-group">
                            <input type="number" name="settings[exam_late_tolerance_minutes]" class="form-control font-mono font-bold" min="0" max="120" value="<?= e(get_val($examSettings, 'exam_late_tolerance_minutes', '15')) ?>" required>
                            <span class="input-group-text bg-light text-muted text-xs font-semibold">Menit</span>
                        </div>
                        <small class="text-muted text-xs d-block mt-1">Batas maksimal keterlambatan siswa dari jam mulai jadwal ujian.</small>
                    </div>

                    <!-- Panjang Token -->
                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Panjang Karakter Token Acak
                        </label>
                        <div class="input-group">
                            <input type="number" name="settings[exam_token_length]" class="form-control font-mono font-bold" min="4" max="12" value="<?= e(get_val($examSettings, 'exam_token_length', '6')) ?>" required>
                            <span class="input-group-text bg-light text-muted text-xs font-semibold">Digit Alfanumerik</span>
                        </div>
                        <small class="text-muted text-xs d-block mt-1">Jumlah digit token akses acak yang dihasilkan saat paket ujian dibuat.</small>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Switches Kebijakan Ujian -->
                <h6 class="font-bold text-dark mb-3">Aturan & Mekanisme Pengacakan</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <label class="font-bold text-sm text-dark mb-0 d-block" for="chkToken">Wajibkan Token Akses Ujian</label>
                                <small class="text-muted text-xs">Siswa wajib memasukkan token sebelum lembar ujian terbuka.</small>
                            </div>
                            <div class="form-check form-switch mb-0 fs-5">
                                <input class="form-check-input" type="checkbox" name="settings[exam_token_enabled]" value="1" id="chkToken" <?= is_checked($examSettings, 'exam_token_enabled', true) ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <label class="font-bold text-sm text-dark mb-0 d-block" for="chkAutoSubmit">Auto-Submit Saat Waktu Habis</label>
                                <small class="text-muted text-xs">Jawaban siswa otomatis tersimpan dan terkunci ketika timer 00:00.</small>
                            </div>
                            <div class="form-check form-switch mb-0 fs-5">
                                <input class="form-check-input" type="checkbox" name="settings[exam_auto_submit_on_timeout]" value="1" id="chkAutoSubmit" <?= is_checked($examSettings, 'exam_auto_submit_on_timeout', true) ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <label class="font-bold text-sm text-dark mb-0 d-block" for="chkRandomQ">Acak Urutan Butir Soal (Default)</label>
                                <small class="text-muted text-xs">Setiap siswa mendapatkan urutan nomor soal yang berbeda secara acak.</small>
                            </div>
                            <div class="form-check form-switch mb-0 fs-5">
                                <input class="form-check-input" type="checkbox" name="settings[exam_random_questions]" value="1" id="chkRandomQ" <?= is_checked($examSettings, 'exam_random_questions', true) ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <label class="font-bold text-sm text-dark mb-0 d-block" for="chkRandomOpt">Acak Pilihan Opsi Jawaban (A - E)</label>
                                <small class="text-muted text-xs">Mengacak urutan abjad pilihan ganda untuk mencegah kecurangan.</small>
                            </div>
                            <div class="form-check form-switch mb-0 fs-5">
                                <input class="form-check-input" type="checkbox" name="settings[exam_random_options]" value="1" id="chkRandomOpt" <?= is_checked($examSettings, 'exam_random_options', true) ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 font-semibold shadow-sm">
                        <i class="bi bi-save2-fill me-1"></i> Simpan Parameter Ujian
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: AI PROCTORING & KEAMANAN                                   -->
    <!-- ================================================================= -->
    <div class="tab-pane fade <?= $activeTab === 'proctoring' ? 'show active' : '' ?>" id="tab-proctoring">
        <form action="<?= base_url('admin/saveSettings') ?>" method="POST">
            <?= CsrfHelper::tokenField() ?>
            <input type="hidden" name="tab" value="proctoring">

            <div class="card-custom p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="font-bold text-dark mb-1">
                            <i class="bi bi-shield-lock-fill text-danger me-2"></i>Konfigurasi AI Proctoring & Pengawasan Ujian
                        </h5>
                        <p class="text-muted text-xs mb-0">Aturan pengawasan dual camera, deteksi keberadaan wajah, mikrofon, dan limit pergantian tab browser.</p>
                    </div>
                    <span class="badge bg-danger-subtle text-danger font-semibold text-xs px-3 py-1.5 rounded-pill">
                        Anti-Cheat Engine
                    </span>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Batas Maksimal Pelanggaran Pindah Tab
                        </label>
                        <div class="input-group">
                            <input type="number" name="settings[proctoring_tab_switch_limit]" class="form-control font-mono font-bold" min="1" max="10" value="<?= e(get_val($proctoringSettings, 'proctoring_tab_switch_limit', '3')) ?>" required>
                            <span class="input-group-text bg-light text-muted text-xs font-semibold">Kali Kesempatan</span>
                        </div>
                        <small class="text-muted text-xs d-block mt-1">Ujian siswa otomatis terkunci jika berpindah tab browser melebihi batas ini.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Toleransi Wajah Tidak Terlihat
                        </label>
                        <div class="input-group">
                            <input type="number" name="settings[proctoring_face_lost_seconds]" class="form-control font-mono font-bold" min="3" max="60" value="<?= e(get_val($proctoringSettings, 'proctoring_face_lost_seconds', '10')) ?>" required>
                            <span class="input-group-text bg-light text-muted text-xs font-semibold">Detik</span>
                        </div>
                        <small class="text-muted text-xs d-block mt-1">Durasi kamera merekam peringatan jika wajah siswa keluar dari frame kamera.</small>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="font-bold text-dark mb-3">Fitur Deteksi Pengawasan Otomatis</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-xs font-bold">
                                        <i class="bi bi-robot me-1"></i>AI Face Tracker
                                    </span>
                                    <div class="form-check form-switch mb-0 fs-5">
                                        <input class="form-check-input" type="checkbox" name="settings[proctoring_default_ai]" value="1" id="chkAiProc" <?= is_checked($proctoringSettings, 'proctoring_default_ai', true) ? 'checked' : '' ?>>
                                    </div>
                                </div>
                                <label class="font-bold text-sm text-dark mb-1 d-block" for="chkAiProc">AI Proctoring Default</label>
                                <small class="text-muted text-xs">Mengaktifkan model AI pengawasan wajah secara default untuk ujian baru.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-info-subtle text-info border border-info-subtle text-xs font-bold">
                                        <i class="bi bi-webcam-fill me-1"></i>Dual Camera
                                    </span>
                                    <div class="form-check form-switch mb-0 fs-5">
                                        <input class="form-check-input" type="checkbox" name="settings[proctoring_default_dual_camera]" value="1" id="chkDualCam" <?= is_checked($proctoringSettings, 'proctoring_default_dual_camera', true) ? 'checked' : '' ?>>
                                    </div>
                                </div>
                                <label class="font-bold text-sm text-dark mb-1 d-block" for="chkDualCam">Wajibkan Dual Camera</label>
                                <small class="text-muted text-xs">Mewajibkan siswa menghubungkan kamera depan laptop dan kamera samping ponsel.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-xs font-bold">
                                        <i class="bi bi-mic-fill me-1"></i>Audio Sensor
                                    </span>
                                    <div class="form-check form-switch mb-0 fs-5">
                                        <input class="form-check-input" type="checkbox" name="settings[proctoring_audio_detection]" value="1" id="chkAudioDet" <?= is_checked($proctoringSettings, 'proctoring_audio_detection', true) ? 'checked' : '' ?>>
                                    </div>
                                </div>
                                <label class="font-bold text-sm text-dark mb-1 d-block" for="chkAudioDet">Deteksi Suara / Kebisingan</label>
                                <small class="text-muted text-xs">Merekam sinyal peringatan jika mikrofon mendeteksi suara percakapan berulang.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 font-semibold shadow-sm">
                        <i class="bi bi-save2-fill me-1"></i> Simpan Parameter Proctoring
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 3: WHATSAPP GATEWAY & OTP                                     -->
    <!-- ================================================================= -->
    <div class="tab-pane fade <?= $activeTab === 'whatsapp' ? 'show active' : '' ?>" id="tab-whatsapp">
        <form action="<?= base_url('admin/saveSettings') ?>" method="POST">
            <?= CsrfHelper::tokenField() ?>
            <input type="hidden" name="tab" value="whatsapp">

            <div class="card-custom p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="font-bold text-dark mb-1">
                            <i class="bi bi-whatsapp text-success me-2"></i>Konfigurasi WhatsApp Gateway & OTP
                        </h5>
                        <p class="text-muted text-xs mb-0">Pengaturan API token, provider gateway (Fonnte/Wablas), pengirim notifikasi, dan masa kedaluwarsa OTP.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success font-semibold text-xs px-3 py-1.5 rounded-pill">
                            WhatsApp Dispatcher
                        </span>
                    </div>
                </div>

                <div class="p-3 border border-success-subtle bg-success-subtle bg-opacity-10 rounded-3 mb-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="font-bold text-success mb-1">
                            <i class="bi bi-power me-1"></i>Status WhatsApp Gateway
                        </h6>
                        <p class="text-muted text-xs mb-0">Jika diaktifkan, semua OTP pendaftaran dan reminder ujian H-1 / 30m akan dikirimkan otomatis.</p>
                    </div>
                    <div class="form-check form-switch mb-0 fs-4">
                        <input class="form-check-input" type="checkbox" name="settings[wa_gateway_enabled]" value="1" id="chkWaEnabled" <?= is_checked($whatsappSettings, 'wa_gateway_enabled', true) ? 'checked' : '' ?>>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Pilihan Provider Gateway
                        </label>
                        <select name="settings[wa_gateway_provider]" class="form-select font-semibold">
                            <?php $currProvider = get_val($whatsappSettings, 'wa_gateway_provider', 'fonnte'); ?>
                            <option value="fonnte" <?= $currProvider === 'fonnte' ? 'selected' : '' ?>>Fonnte (Rekomendasi - https://fonnte.com)</option>
                            <option value="wablas" <?= $currProvider === 'wablas' ? 'selected' : '' ?>>Wablas (https://wablas.com)</option>
                            <option value="generic" <?= $currProvider === 'generic' ? 'selected' : '' ?>>Generic Webhook API</option>
                            <option value="simulation" <?= $currProvider === 'simulation' ? 'selected' : '' ?>>Mode Simulasi (Hanya Catat Log ke Storage)</option>
                        </select>
                        <small class="text-muted text-xs d-block mt-1">Layanan perantara yang mengirimkan pesan ke nomor WhatsApp tujuan.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Nama Pengirim (Header Text)
                        </label>
                        <input type="text" name="settings[wa_sender_name]" class="form-control" value="<?= e(get_val($whatsappSettings, 'wa_sender_name', 'SmartExam AI UJOL')) ?>" required>
                        <small class="text-muted text-xs d-block mt-1">Nama identitas instansi yang dicantumkan di bagian atas pesan WhatsApp.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            API Token / Key WhatsApp Gateway *
                        </label>
                        <div class="input-group">
                            <input type="password" name="settings[wa_api_token]" id="waApiTokenInput" class="form-control font-mono" value="<?= e(get_val($whatsappSettings, 'wa_api_token', '')) ?>" placeholder="Masukkan token API gateway Anda" required>
                            <button class="btn btn-light border" type="button" onclick="toggleSecretVisibility('waApiTokenInput')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted text-xs d-block mt-1">Token rahasia autentikasi dari dashboard Fonnte / Wablas Anda.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            API Endpoint URL
                        </label>
                        <input type="url" name="settings[wa_api_url]" class="form-control font-mono" value="<?= e(get_val($whatsappSettings, 'wa_api_url', 'https://api.fonnte.com/send')) ?>" required>
                        <small class="text-muted text-xs d-block mt-1">Default Fonnte: <code>https://api.fonnte.com/send</code></small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Masa Kedaluwarsa Kode OTP (Menit)
                        </label>
                        <div class="input-group">
                            <input type="number" min="1" max="60" name="settings[wa_otp_expiry_minutes]" class="form-control font-mono font-bold" value="<?= e(get_val($whatsappSettings, 'wa_otp_expiry_minutes', '15')) ?>" required>
                            <span class="input-group-text bg-light text-muted text-xs font-semibold">Menit</span>
                        </div>
                        <small class="text-muted text-xs d-block mt-1">Batas waktu siswa/guru memasukkan kode verifikasi 6-digit WhatsApp.</small>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-success rounded-3 font-semibold text-xs" data-bs-toggle="modal" data-bs-target="#modalTestWa">
                        <i class="bi bi-send-check me-1"></i> Uji Coba Kirim Pesan Sekarang
                    </button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 font-semibold shadow-sm">
                        <i class="bi bi-save2-fill me-1"></i> Simpan Konfigurasi WhatsApp
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 4: INTEGRASI AI & CHATBOT                                     -->
    <!-- ================================================================= -->
    <div class="tab-pane fade <?= $activeTab === 'ai' ? 'show active' : '' ?>" id="tab-ai">
        <form action="<?= base_url('admin/saveSettings') ?>" method="POST" id="formAiSettings">
            <?= CsrfHelper::tokenField() ?>
            <input type="hidden" name="tab" value="ai">

            <div class="card-custom p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="font-bold text-dark mb-1">
                            <i class="bi bi-robot text-warning me-2"></i>Konfigurasi Integrasi AI Asisten & Chatbot Server
                        </h5>
                        <p class="text-muted text-xs mb-0">Bebas gunakan penyedia LLM mana saja (Google Gemini, OpenAI, DeepSeek, Groq, OpenRouter, Claude, Mistral, Ollama, atau Custom API URL).</p>
                    </div>
                    <span class="badge bg-warning-subtle text-warning font-semibold text-xs px-3 py-1.5 rounded-pill">
                        Universal AI Provider
                    </span>
                </div>

                <!-- Quick Selection Badges -->
                <div class="mb-2 p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-xs font-bold text-dark text-uppercase tracking-wider">
                            <i class="bi bi-stars text-warning me-1"></i>Pilih Cepat Provider AI Populer:
                        </span>
                        <span class="text-muted text-xs">Klik untuk auto-set format & endpoint</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2" id="aiQuickChips">
                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('gemini')">
                            <i class="bi bi-google"></i> Google Gemini
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('openai')">
                            <i class="bi bi-cpu"></i> OpenAI (GPT-4o)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('deepseek')">
                            <i class="bi bi-lightning-charge-fill"></i> DeepSeek (V3/R1)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('groq')">
                            <i class="bi bi-speedometer2"></i> Groq Cloud
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('claude')">
                            <i class="bi bi-robot"></i> Anthropic Claude
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('openrouter')">
                            <i class="bi bi-diagram-3-fill"></i> OpenRouter
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('mistral')">
                            <i class="bi bi-wind"></i> Mistral AI
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('together')">
                            <i class="bi bi-share-fill"></i> Together AI
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('xai')">
                            <i class="bi bi-x-diamond-fill"></i> xAI (Grok)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('perplexity')">
                            <i class="bi bi-compass-fill"></i> Perplexity
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('ollama')">
                            <i class="bi bi-hdd-network-fill"></i> Ollama (Lokal)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('lmstudio')">
                            <i class="bi bi-laptop-fill"></i> LM Studio / vLLM
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning py-1 px-2.5 rounded-pill text-xs font-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="selectAiPreset('custom')">
                            <i class="bi bi-sliders"></i> Custom URL
                        </button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Penyedia / Provider AI
                        </label>
                        <?php $currAiProvider = get_val($aiSettings, 'ai_provider', 'gemini'); ?>
                        <select name="settings[ai_provider]" id="aiProviderSelect" class="form-select font-semibold" onchange="onAiProviderChange()">
                            <optgroup label="⭐ Populer Cloud AI (Proprietary)">
                                <option value="gemini" <?= $currAiProvider === 'gemini' ? 'selected' : '' ?>>Google Gemini (Google AI Studio)</option>
                                <option value="openai" <?= $currAiProvider === 'openai' ? 'selected' : '' ?>>OpenAI (ChatGPT / GPT-4o / GPT-4o-mini)</option>
                                <option value="claude" <?= $currAiProvider === 'claude' ? 'selected' : '' ?>>Anthropic Claude (Claude 3.5 Sonnet / Haiku)</option>
                                <option value="xai" <?= $currAiProvider === 'xai' ? 'selected' : '' ?>>xAI (Grok 2 / Grok Beta)</option>
                                <option value="perplexity" <?= $currAiProvider === 'perplexity' ? 'selected' : '' ?>>Perplexity AI (Sonar Online / Reasoner)</option>
                            </optgroup>
                            <optgroup label="⚡ Open-Source & Ultra Fast Cloud API">
                                <option value="deepseek" <?= $currAiProvider === 'deepseek' ? 'selected' : '' ?>>DeepSeek AI (DeepSeek-V3 / DeepSeek-R1)</option>
                                <option value="groq" <?= $currAiProvider === 'groq' ? 'selected' : '' ?>>Groq Cloud (Llama 3.3 70B - Ultra Fast LPU)</option>
                                <option value="openrouter" <?= $currAiProvider === 'openrouter' ? 'selected' : '' ?>>OpenRouter (Multi-Provider AI Aggregator)</option>
                                <option value="mistral" <?= $currAiProvider === 'mistral' ? 'selected' : '' ?>>Mistral AI (Mistral Large / Codestral)</option>
                                <option value="together" <?= $currAiProvider === 'together' ? 'selected' : '' ?>>Together AI (Llama 3 / Qwen / Mixtral)</option>
                            </optgroup>
                            <optgroup label="💻 Self-Hosted & Offline (Server Lokal)">
                                <option value="ollama" <?= $currAiProvider === 'ollama' ? 'selected' : '' ?>>Ollama (Localhost:11434 / REST API)</option>
                                <option value="lmstudio" <?= $currAiProvider === 'lmstudio' ? 'selected' : '' ?>>LM Studio / vLLM (Localhost:1234 / 8000)</option>
                            </optgroup>
                            <optgroup label="🌐 Kustom Bebas (OpenAI Compatible)">
                                <option value="custom" <?= $currAiProvider === 'custom' ? 'selected' : '' ?>>Custom Endpoint / Penyedia Mandiri Lainnya</option>
                            </optgroup>
                        </select>
                        <small class="text-muted text-xs d-block mt-1" id="aiProviderDesc">Platform pemroses penalaran soal & asisten interaktif.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Versi / Nama Model AI
                        </label>
                        <input type="text" name="settings[ai_model_name]" id="aiModelNameInput" list="aiModelSuggestions" class="form-control font-mono" value="<?= e(get_val($aiSettings, 'ai_model_name', 'gemini-1.5-flash')) ?>" required>
                        <datalist id="aiModelSuggestions">
                            <option value="gemini-1.5-flash"></option>
                            <option value="gemini-1.5-pro"></option>
                            <option value="gemini-2.0-flash-exp"></option>
                            <option value="gpt-4o-mini"></option>
                            <option value="gpt-4o"></option>
                            <option value="deepseek-chat"></option>
                            <option value="deepseek-reasoner"></option>
                            <option value="llama-3.3-70b-versatile"></option>
                            <option value="claude-3-5-sonnet-20241022"></option>
                            <option value="mistral-large-latest"></option>
                        </datalist>
                        <small class="text-muted text-xs d-block mt-1">Nama model yang dipanggil pada request API.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            API Base URL / Endpoint Kustom
                        </label>
                        <input type="text" name="settings[ai_base_url]" id="aiBaseUrlInput" class="form-control font-mono" value="<?= e(get_val($aiSettings, 'ai_base_url', '')) ?>" placeholder="Otomatis mengikuti provider (atau isi endpoint kustom Anda)">
                        <small class="text-muted text-xs d-block mt-1">Biarkan kosong untuk URL bawaan resmi, atau isi jika menggunakan proxy/self-hosted.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            AI API Key (Kunci Rahasia)
                        </label>
                        <div class="input-group">
                            <input type="password" name="settings[ai_api_key]" id="aiApiKeyInput" class="form-control font-mono" value="<?= e(get_val($aiSettings, 'ai_api_key', '')) ?>" placeholder="AIzaSy... / sk-...">
                            <button class="btn btn-light border" type="button" onclick="toggleSecretVisibility('aiApiKeyInput')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted text-xs d-block mt-1" id="aiApiKeyHint">Kunci API resmi dari penyedia layanan AI yang Anda pilih.</small>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Endpoint Server Chatbot (Node.js Background Service)
                        </label>
                        <input type="url" name="settings[chatbot_server_url]" class="form-control font-mono" value="<?= e(get_val($aiSettings, 'chatbot_server_url', 'http://localhost:3000')) ?>" required>
                        <small class="text-muted text-xs d-block mt-1">Alamat background service server chatbot interaktif real-time.</small>
                    </div>
                </div>

                <!-- Live Test Result Banner -->
                <div id="aiTestResultBox" class="d-none mt-4"></div>

                <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-warning text-dark font-semibold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" id="btnTestAiConnection" onclick="testAiConnection()">
                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                        <span>Uji Coba Respon AI</span>
                    </button>

                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 font-semibold shadow-sm">
                        <i class="bi bi-save2-fill me-1"></i> Simpan Konfigurasi AI
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 5: PROFIL SEKOLAH & ENVIRONMENT                               -->
    <!-- ================================================================= -->
    <div class="tab-pane fade <?= $activeTab === 'general' ? 'show active' : '' ?>" id="tab-general">
        <form action="<?= base_url('admin/saveSettings') ?>" method="POST">
            <?= CsrfHelper::tokenField() ?>
            <input type="hidden" name="tab" value="general">

            <div class="card-custom p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="font-bold text-dark mb-1">
                            <i class="bi bi-buildings-fill text-primary me-2"></i>Identitas Sekolah & Pengaturan Lingkungan (.env)
                        </h5>
                        <p class="text-muted text-xs mb-0">Nama resmi instansi, tahun ajaran operasional, semester aktif, dan mode sistem.</p>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary font-semibold text-xs px-3 py-1.5 rounded-pill">
                        System Environment
                    </span>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Nama Aplikasi
                        </label>
                        <input type="text" name="settings[app_name]" class="form-control font-bold" value="<?= e(get_val($generalSettings, 'app_name', 'SmartExam AI')) ?>" required>
                        <small class="text-muted text-xs d-block mt-1">Nama branding yang tampil di header dan judul halaman browser.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Nama Sekolah / Institusi Pendidikan
                        </label>
                        <input type="text" name="settings[school_name]" class="form-control font-semibold" value="<?= e(get_val($generalSettings, 'school_name', 'SMA Negeri 1 Prestasi Bangsa')) ?>" required>
                        <small class="text-muted text-xs d-block mt-1">Nama resmi sekolah pada kop lembar jawaban dan kartu ujian.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Tahun Ajaran Aktif
                        </label>
                        <input type="text" name="settings[academic_year]" class="form-control font-mono font-semibold" value="<?= e(get_val($generalSettings, 'academic_year', '2026/2027')) ?>" placeholder="2026/2027" required>
                        <small class="text-muted text-xs d-block mt-1">Periode akademik saat ini.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Semester Aktif
                        </label>
                        <select name="settings[active_semester]" class="form-select font-semibold">
                            <?php $currSemester = get_val($generalSettings, 'active_semester', 'Ganjil'); ?>
                            <option value="Ganjil" <?= $currSemester === 'Ganjil' ? 'selected' : '' ?>>Ganjil (Gasal)</option>
                            <option value="Genap" <?= $currSemester === 'Genap' ? 'selected' : '' ?>>Genap</option>
                        </select>
                        <small class="text-muted text-xs d-block mt-1">Penetapan semester berjalan.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-xs font-bold text-dark text-uppercase tracking-wider">
                            Mode Environment (APP_ENV)
                        </label>
                        <select name="settings[app_env]" class="form-select font-mono font-semibold">
                            <?php $currEnv = get_val($generalSettings, 'app_env', 'development'); ?>
                            <option value="development" <?= $currEnv === 'development' ? 'selected' : '' ?>>development (Mode Pengujian Lokal)</option>
                            <option value="production" <?= $currEnv === 'production' ? 'selected' : '' ?>>production (Mode Operasional Asli)</option>
                        </select>
                        <small class="text-muted text-xs d-block mt-1">Otomatis sinkron ke berkas <code>.env</code>.</small>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 font-semibold shadow-sm">
                        <i class="bi bi-save2-fill me-1"></i> Simpan Profil & Sinkron .env
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL: UJI COBA KONEKSI WHATSAPP GATEWAY                         -->
<!-- ================================================================= -->
<div class="modal fade" id="modalTestWa" tabindex="-1" aria-labelledby="modalTestWaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle" style="width: 36px; height: 36px;">
                        <i class="bi bi-whatsapp fs-5"></i>
                    </div>
                    <h5 class="modal-title font-bold text-dark" id="modalTestWaLabel">Tes Koneksi WhatsApp Gateway</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted text-xs mb-3">
                    Kirimkan satu pesan teks uji coba ke nomor WhatsApp Anda untuk memastikan token API dan gateway Fonnte / Wablas siap digunakan.
                </p>

                <div class="mb-3">
                    <label class="form-label text-xs font-semibold text-secondary">Nomor WhatsApp Tujuan *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-success border-end-0">
                            <i class="bi bi-whatsapp"></i>
                        </span>
                        <input type="tel" id="inputTestPhone" class="form-control border-start-0" placeholder="Contoh: 081234567890 atau 6281234567890">
                    </div>
                </div>

                <div id="testWaResult" class="d-none"></div>
            </div>
            <div class="modal-footer border-top pt-3">
                <button type="button" class="btn btn-light rounded-3 text-xs" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success rounded-3 text-xs font-semibold d-flex align-items-center gap-1.5" id="btnSendTestWa" onclick="sendTestWhatsApp()">
                    <i class="bi bi-send-fill"></i>
                    <span>Kirim Pesan Uji Coba</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSecretVisibility(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

function sendTestWhatsApp() {
    const phone = document.getElementById('inputTestPhone').value.trim();
    const btn = document.getElementById('btnSendTestWa');
    const resultBox = document.getElementById('testWaResult');

    if (!phone) {
        alert('Mohon masukkan nomor WhatsApp tujuan uji coba.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
    resultBox.className = 'd-none';

    const formData = new FormData();
    formData.append('csrf_token', '<?= CsrfHelper::generateToken() ?>');
    formData.append('test_phone', phone);

    fetch('<?= base_url('admin/testWaConnection') ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Kirim Pesan Uji Coba';
        resultBox.classList.remove('d-none');

        if (data.success) {
            resultBox.className = 'alert alert-success py-2.5 px-3 text-xs rounded-3 mb-0';
            resultBox.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> <strong>Berhasil:</strong> ' + (data.message || 'Pesan uji coba WhatsApp berhasil dikirimkan!');
        } else {
            resultBox.className = 'alert alert-danger py-2.5 px-3 text-xs rounded-3 mb-0';
            resultBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Gagal:</strong> ' + (data.message || 'Gagal mengirim pesan.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Kirim Pesan Uji Coba';
        resultBox.classList.remove('d-none');
        resultBox.className = 'alert alert-danger py-2.5 px-3 text-xs rounded-3 mb-0';
        resultBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Error koneksi: ' + err.message;
    });
}

const AI_PROVIDER_PRESETS = {
    gemini: {
        baseUrl: '',
        defaultModel: 'gemini-1.5-flash',
        models: ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-2.0-flash-exp'],
        keyPlaceholder: 'AIzaSy...',
        hint: 'Dapatkan API Key gratis di Google AI Studio (aistudio.google.com).'
    },
    openai: {
        baseUrl: 'https://api.openai.com/v1',
        defaultModel: 'gpt-4o-mini',
        models: ['gpt-4o-mini', 'gpt-4o', 'o1-mini'],
        keyPlaceholder: 'sk-proj-...',
        hint: 'Dapatkan API Key di OpenAI Developer Platform (platform.openai.com).'
    },
    deepseek: {
        baseUrl: 'https://api.deepseek.com/v1',
        defaultModel: 'deepseek-chat',
        models: ['deepseek-chat', 'deepseek-reasoner'],
        keyPlaceholder: 'sk-...',
        hint: 'Dapatkan API Key di DeepSeek Platform (platform.deepseek.com).'
    },
    groq: {
        baseUrl: 'https://api.groq.com/openai/v1',
        defaultModel: 'llama-3.3-70b-versatile',
        models: ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'mixtral-8x7b-32768'],
        keyPlaceholder: 'gsk_...',
        hint: 'Groq Cloud LPU ultra-cepat. Dapatkan di console.groq.com.'
    },
    openrouter: {
        baseUrl: 'https://openrouter.ai/api/v1',
        defaultModel: 'deepseek/deepseek-chat',
        models: ['deepseek/deepseek-chat', 'google/gemini-flash-1.5', 'meta-llama/llama-3.3-70b-instruct'],
        keyPlaceholder: 'sk-or-v1-...',
        hint: 'OpenRouter mendukung ratusan model AI dalam 1 API key. Dapatkan di openrouter.ai.'
    },
    claude: {
        baseUrl: 'https://api.anthropic.com/v1/messages',
        defaultModel: 'claude-3-5-sonnet-20241022',
        models: ['claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022'],
        keyPlaceholder: 'sk-ant-api03-...',
        hint: 'Dapatkan API Key Anthropic Claude di console.anthropic.com.'
    },
    mistral: {
        baseUrl: 'https://api.mistral.ai/v1',
        defaultModel: 'mistral-large-latest',
        models: ['mistral-large-latest', 'mistral-small-latest', 'codestral-latest'],
        keyPlaceholder: '...',
        hint: 'Dapatkan API Key Mistral di console.mistral.ai.'
    },
    together: {
        baseUrl: 'https://api.together.xyz/v1',
        defaultModel: 'meta-llama/Llama-3-70b-chat-hf',
        models: ['meta-llama/Llama-3-70b-chat-hf', 'mistralai/Mixtral-8x7B-Instruct-v0.1'],
        keyPlaceholder: '...',
        hint: 'Dapatkan API Key di api.together.xyz.'
    },
    xai: {
        baseUrl: 'https://api.x.ai/v1',
        defaultModel: 'grok-2-latest',
        models: ['grok-2-latest', 'grok-beta', 'grok-2-vision-latest'],
        keyPlaceholder: 'xai-...',
        hint: 'Dapatkan API Key di x.ai Developer Portal (console.x.ai).'
    },
    perplexity: {
        baseUrl: 'https://api.perplexity.ai',
        defaultModel: 'sonar',
        models: ['sonar', 'sonar-pro', 'sonar-reasoning'],
        keyPlaceholder: 'pplx-...',
        hint: 'Dapatkan API Key di Perplexity API Settings (perplexity.ai/settings/api).'
    },
    ollama: {
        baseUrl: 'http://localhost:11434/v1',
        defaultModel: 'llama3',
        models: ['llama3', 'mistral', 'qwen2.5:7b', 'phi3'],
        keyPlaceholder: 'ollama (opsional/bebas)',
        hint: 'Server Ollama lokal. Pastikan server Ollama aktif di komputer/server Anda.'
    },
    lmstudio: {
        baseUrl: 'http://localhost:1234/v1',
        defaultModel: 'local-model',
        models: ['local-model'],
        keyPlaceholder: 'lm-studio (opsional)',
        hint: 'Pastikan Server Local di LM Studio atau vLLM sudah dijalankan pada port 1234 atau 8000.'
    },
    custom: {
        baseUrl: '',
        defaultModel: '',
        models: [],
        keyPlaceholder: 'API Key kustom...',
        hint: 'Gunakan Base URL dan model sesuai konfigurasi server LLM OpenAI-compatible Anda.'
    }
};

function selectAiPreset(providerKey) {
    const sel = document.getElementById('aiProviderSelect');
    if (!sel) return;
    sel.value = providerKey;
    onAiProviderChange();

    const keyInput = document.getElementById('aiApiKeyInput');
    if (keyInput) {
        keyInput.focus();
    }
}

function onAiProviderChange() {
    const provider = document.getElementById('aiProviderSelect').value;
    const preset = AI_PROVIDER_PRESETS[provider];
    if (!preset) return;

    const baseUrlInput = document.getElementById('aiBaseUrlInput');
    const modelInput = document.getElementById('aiModelNameInput');
    const keyInput = document.getElementById('aiApiKeyInput');
    const hintElem = document.getElementById('aiApiKeyHint');
    const datalist = document.getElementById('aiModelSuggestions');

    if (preset.baseUrl !== undefined) {
        baseUrlInput.value = preset.baseUrl;
    }
    if (preset.defaultModel) {
        modelInput.value = preset.defaultModel;
    }
    if (preset.keyPlaceholder) {
        keyInput.placeholder = preset.keyPlaceholder;
    }
    if (hintElem && preset.hint) {
        hintElem.innerText = preset.hint;
    }

    if (datalist && preset.models) {
        datalist.innerHTML = '';
        preset.models.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m;
            datalist.appendChild(opt);
        });
    }
}

function testAiConnection() {
    const provider = document.getElementById('aiProviderSelect').value;
    const modelName = document.getElementById('aiModelNameInput').value.trim();
    const apiKey = document.getElementById('aiApiKeyInput').value.trim();
    const baseUrl = document.getElementById('aiBaseUrlInput').value.trim();
    const btn = document.getElementById('btnTestAiConnection');
    const resultBox = document.getElementById('aiTestResultBox');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menguji Respon AI...';
    resultBox.className = 'd-none';

    const formData = new FormData();
    formData.append('csrf_token', '<?= CsrfHelper::generateToken() ?>');
    formData.append('provider', provider);
    formData.append('model_name', modelName);
    formData.append('api_key', apiKey);
    formData.append('base_url', baseUrl);

    fetch('<?= base_url('admin/testAiConnection') ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning-charge-fill text-warning me-1"></i> Uji Coba Respon AI';
        resultBox.classList.remove('d-none');

        if (data.success) {
            resultBox.className = 'alert alert-success d-flex align-items-start gap-3 py-3 px-3 rounded-3 shadow-sm border-0';
            resultBox.innerHTML = `
                <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle flex-shrink-0" style="width: 32px; height: 32px;">
                    <i class="bi bi-check-lg fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <strong class="text-success text-sm">Koneksi AI Berhasil Terhubung!</strong>
                        <span class="badge bg-success-subtle text-success border border-success-subtle text-xs px-2.5 py-1 rounded-pill">
                            ${data.provider || provider} &bull; ${data.model || modelName}
                        </span>
                    </div>
                    <div class="p-2.5 bg-white bg-opacity-75 rounded-2 font-mono text-xs text-dark border border-success-subtle mt-1 shadow-2xs">
                        <strong>Respon Model:</strong> "${data.reply || 'OK'}"
                    </div>
                </div>
            `;
        } else {
            resultBox.className = 'alert alert-danger d-flex align-items-start gap-3 py-3 px-3 rounded-3 shadow-sm border-0';
            resultBox.innerHTML = `
                <div class="d-inline-flex align-items-center justify-content-center bg-danger text-white rounded-circle flex-shrink-0" style="width: 32px; height: 32px;">
                    <i class="bi bi-exclamation-triangle-fill fs-6"></i>
                </div>
                <div class="flex-grow-1">
                    <strong class="text-danger text-sm d-block mb-1">Uji Koneksi AI Gagal</strong>
                    <p class="text-xs text-danger mb-0">${data.message || 'Terjadi kesalahan saat menghubungi API penyedia AI.'}</p>
                </div>
            `;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning-charge-fill text-warning me-1"></i> Uji Coba Respon AI';
        resultBox.classList.remove('d-none');
        resultBox.className = 'alert alert-danger d-flex align-items-start gap-2 py-3 px-3 rounded-3 shadow-sm';
        resultBox.innerHTML = '<i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i> <div><strong>Error Network:</strong> ' + err.message + '</div>';
    });
}
</script>
