<?php
use App\Helpers\CsrfHelper;
$remainingSeconds = $remainingSeconds ?? 3600;
$totalSeconds = $totalSeconds ?? 3600;
$questions = $questions ?? [];
$savedAnswers = $savedAnswers ?? [];
$participant = $participant ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Ruang Ujian - ' . APP_NAME) ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <style>
        html, body {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
            background-color: #f1f5f9;
            user-select: none !important;
            -webkit-user-select: none !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .exam-header {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            width: 100% !important;
        }
        .timer-box {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.25rem;
            font-weight: 700;
            padding: 0.35rem 1rem;
            border-radius: 0.5rem;
            background: #0f172a;
            color: #38bdf8;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .timer-box.warning-time {
            background: #ca8a04;
            color: #ffffff;
            animation: pulse 1s infinite alternate;
        }
        .timer-box.danger-time {
            background: #dc2626;
            color: #ffffff;
            animation: pulse 0.6s infinite alternate;
        }
        @keyframes pulse {
            from { transform: scale(1); }
            to { transform: scale(1.04); }
        }
        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }
        .option-card {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            font-size: 1rem;
            line-height: 1.5;
        }
        .option-card:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }
        .option-card.selected {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 1px #2563eb;
        }
        .option-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #334155;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .option-card.selected .option-badge {
            background: #2563eb;
            color: #ffffff;
        }
        .palette-btn {
            width: 44px;
            height: 44px;
            border-radius: 0.5rem;
            font-weight: 700;
            font-size: 0.875rem;
            font-family: 'JetBrains Mono', monospace;
            border: 2px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }
        .palette-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
        }
        .palette-btn.active-q {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
            font-weight: 800;
        }
        .palette-btn.answered {
            background: #16a34a;
            border-color: #16a34a;
            color: #ffffff;
        }
        .palette-btn.doubtful {
            background: #ea580c;
            border-color: #ea580c;
            color: #ffffff;
        }
        .palette-btn.doubtful::after {
            content: '';
            position: absolute;
            top: 2px;
            right: 2px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #facc15;
        }
        .doubtful-btn.active {
            background: #ea580c !important;
            border-color: #ea580c !important;
            color: #ffffff !important;
        }
        /* Lockdown Security Overlays */
        .exam-lockdown-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999999;
            background: rgba(15, 23, 42, 0.97);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .animate-alarm-pulse {
            animation: alarmPulse 1s infinite alternate;
        }
        @keyframes alarmPulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            100% { transform: scale(1.08); box-shadow: 0 0 25px 10px rgba(239, 68, 68, 0); }
        }
    </style>
</head>
<body>

<!-- OVERLAY 1: POPUP INISIALISASI MODE LAYAR PENUH (SECURE EXAM) -->
<div id="lockdownInitModal" class="exam-lockdown-overlay">
    <div class="card p-4 p-md-5 text-center shadow-lg border-0 rounded-4" style="max-width: 520px; background: #ffffff;">
        <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 76px; height: 76px;">
            <i class="bi bi-shield-lock-fill fs-1"></i>
        </div>
        <h4 class="font-bold text-dark mb-2">Mode Ujian Aman Aktif</h4>
        <p class="text-muted text-sm mb-3">
            Untuk menjaga integritas dan ketertiban asesmen, ujian ini wajib dijalankan dalam mode <strong>Layar Penuh (Fullscreen)</strong>.
        </p>
        <div class="alert alert-danger text-start text-xs p-3 mb-4 rounded-3">
            <div class="font-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> ATURAN KETAT ASESMEN:</div>
            <ul class="mb-0 ps-3">
                <li>Dilarang membuka tab baru atau browser lain.</li>
                <li>Dilarang beralih ke aplikasi lain (Alt+Tab, split screen, minimize).</li>
                <li>Meninggalkan layar ujian akan tercatat sebagai pelanggaran otomatis (maksimal 3 kali).</li>
            </ul>
        </div>
        <button type="button" class="btn btn-primary btn-lg rounded-pill font-bold px-4 py-2.5 shadow" id="btnEnterFullscreen">
            <i class="bi bi-arrows-fullscreen me-2"></i> Masuk Layar Penuh & Mulai Ujian
        </button>
    </div>
</div>

<!-- OVERLAY 2: LOCK SCREEN MERAH SAAT TERDETEKSI BUKA TAB BARU / KELUAR FULLSCREEN -->
<div id="violationOverlay" class="exam-lockdown-overlay d-none">
    <div class="card p-4 p-md-5 text-center shadow-lg border border-danger rounded-4" style="max-width: 540px; background: #1e293b; color: #ffffff;">
        <div class="rounded-circle bg-danger bg-opacity-20 text-danger mx-auto mb-3 d-flex align-items-center justify-content-center animate-alarm-pulse" style="width: 80px; height: 80px;">
            <i class="bi bi-exclamation-octagon-fill fs-1"></i>
        </div>
        <h3 class="font-bold text-danger mb-2">AKSES UJIAN DIBLOKIR</h3>
        <h6 class="text-warning font-semibold mb-3">Terdeteksi Membuka Tab Baru atau Berpindah Aplikasi!</h6>
        <p class="text-light opacity-75 text-sm mb-3">
            Anda terdeteksi meninggalkan jendela ujian aktif. Insiden ini dicatat secara otomatis oleh sistem AI Proctoring dan Pengawas Ujian.
        </p>
        <div class="p-3 bg-dark rounded-3 border border-secondary mb-3">
            <div class="d-flex justify-content-around text-center">
                <div>
                    <span class="text-muted text-xs d-block">Pelanggaran Tercatat</span>
                    <span class="fs-3 font-bold text-danger font-mono" id="violationCountBadge">1</span>
                </div>
                <div class="border-end border-secondary"></div>
                <div>
                    <span class="text-muted text-xs d-block">Batas Maksimal</span>
                    <span class="fs-3 font-bold text-warning font-mono">3</span>
                </div>
            </div>
        </div>
        <p class="text-danger text-xs font-bold mb-4" id="violationNoticeText">
            ⚠️ PERINGATAN: Jika mencapai 3 pelanggaran, lembar jawaban akan OTOMATIS DIKUMPULKAN & DIDISKUALIFIKASI!
        </p>
        <button type="button" class="btn btn-danger btn-lg rounded-pill font-bold px-4 py-2.5 shadow" id="btnResumeFullscreen">
            <i class="bi bi-shield-check me-2"></i> Saya Mengerti, Kembali ke Ujian
        </button>
    </div>
</div>

<!-- 1. STICKY TOP HEADER BAR -->
<header class="exam-header py-2.5 px-3 px-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <!-- Logo & Exam Meta -->
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-primary fs-3"></i>
                <div>
                    <h6 class="font-bold text-dark mb-0 line-clamp-1"><?= e($participant['exam_title']) ?></h6>
                    <small class="text-muted text-xs">
                        <?= e($participant['subject_name']) ?> | Siswa: <strong><?= e($participant['student_name']) ?></strong> (<?= e($participant['class_name']) ?>)
                    </small>
                </div>
            </div>
        </div>

        <!-- Center: Countdown Timer -->
        <div class="d-flex align-items-center gap-3">
            <div class="timer-box" id="timerBox" title="Sisa Waktu Pengerjaan">
                <i class="bi bi-stopwatch-fill"></i>
                <span id="countdownDisplay">--:--:--</span>
            </div>
            <!-- Autosave Indicator -->
            <div class="text-xs text-muted d-none d-md-flex align-items-center gap-1" id="autosaveStatus">
                <i class="bi bi-cloud-check text-success fs-6"></i>
                <span>Tersimpan</span>
            </div>
        </div>

        <!-- Right: Submit Button -->
        <div>
            <button type="button" class="btn btn-danger btn-sm rounded-3 px-3.5 py-1.5 font-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSubmitExam">
                <i class="bi bi-check2-circle me-1"></i> Kumpulkan Ujian
            </button>
        </div>
    </div>
</header>

<!-- 2. MAIN EXAM ENGINE WORKSPACE -->
<main class="container-fluid py-4 px-3 px-md-4">
    <div class="row g-4">
        <!-- LEFT COLUMN: QUESTION CARD -->
        <div class="col-12 col-lg-8 col-xl-9">
            <div class="card-custom p-4 p-md-5 mb-4 shadow-sm" style="min-height: 480px;">
                <!-- Question Top Header -->
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark fs-6 rounded-pill px-3 py-1.5 font-bold" id="qNumberBadge">
                            Soal No. 1 dari <?= count($questions) ?>
                        </span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 text-xs font-semibold" id="qTypeBadge">
                            Pilihan Ganda
                        </span>
                        <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 text-xs" id="qWeightBadge">
                            Bobot: 1.0 Poin
                        </span>
                    </div>

                    <!-- Bookmark / Doubtful Toggle Button -->
                    <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 text-xs font-semibold doubtful-btn" id="btnToggleDoubtful">
                        <i class="bi bi-bookmark-fill me-1"></i> <span id="doubtfulBtnText">Ragu-Ragu</span>
                    </button>
                </div>

                <!-- Dynamic Question Container (Rendered via JS) -->
                <div id="questionTextContainer" class="font-medium text-dark fs-6 mb-4 leading-relaxed">
                    <!-- Teks pertanyaan akan dimuat di sini -->
                </div>

                <!-- Dynamic Options Container -->
                <div id="optionsContainer" class="mb-4">
                    <!-- Pilihan A-E atau Textarea Essay -->
                </div>
            </div>

            <!-- Bottom Navigation Bar -->
            <div class="card-custom p-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-4 font-semibold text-sm" id="btnPrev">
                    <i class="bi bi-arrow-left me-1"></i> Soal Sebelumnya
                </button>

                <div class="d-flex align-items-center gap-2">
                    <span class="text-xs text-muted d-none d-sm-inline">Gunakan tombol panah keyboard atau klik palet nomor</span>
                </div>

                <button type="button" class="btn btn-primary rounded-3 px-4 font-semibold text-sm" id="btnNext">
                    Soal Berikutnya <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- RIGHT COLUMN: QUESTION PALETTE -->
        <div class="col-12 col-lg-4 col-xl-3">
            <div class="card-custom p-4 shadow-sm" style="position: sticky; top: 80px;">
                <h6 class="font-bold text-dark mb-2 pb-2 border-bottom d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Palet Nomor Soal</span>
                    <span class="badge bg-light text-dark border font-mono text-xs"><?= count($questions) ?> Butir</span>
                </h6>

                <!-- Palette Legend -->
                <div class="d-flex flex-wrap gap-2 text-xs text-muted mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-1">
                        <span class="d-inline-block rounded-circle bg-success" style="width: 10px; height: 10px;"></span>
                        <span>Dijawab</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="d-inline-block rounded-circle bg-warning" style="width: 10px; height: 10px;"></span>
                        <span>Ragu-ragu</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="d-inline-block rounded-circle bg-secondary-subtle border" style="width: 10px; height: 10px;"></span>
                        <span>Belum</span>
                    </div>
                </div>

                <!-- Palette Buttons Grid -->
                <div class="d-flex flex-wrap gap-2 mb-4 justify-content-start overflow-auto" style="max-height: 280px;" id="paletteGrid">
                    <?php foreach ($questions as $idx => $q): ?>
                        <button type="button" class="palette-btn" id="pal_btn_<?= $idx ?>" onclick="jumpToQuestion(<?= $idx ?>)">
                            <?= $idx + 1 ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Quick Summary Stats -->
                <div class="p-3 bg-light rounded-3 border text-xs mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Sudah Dijawab:</span>
                        <strong class="text-success font-mono" id="statAnswered">0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Ragu-ragu:</span>
                        <strong class="text-warning font-mono" id="statDoubtful">0</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Belum Dijawab:</span>
                        <strong class="text-danger font-mono" id="statUnanswered"><?= count($questions) ?></strong>
                    </div>
                </div>

                <?php if (!empty($participant['enable_dual_camera'])): ?>
                <!-- Dual Camera Proctoring Monitor Widget -->
                <div class="p-3 bg-dark text-white rounded-3 shadow-sm border border-secondary mb-3" id="proctoringWidget">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary">
                        <span class="text-xs font-bold d-flex align-items-center gap-1.5 text-warning">
                            <span class="spinner-grow spinner-grow-sm text-danger" style="width: 8px; height: 8px;"></span>
                            DUAL PROCTORING
                        </span>
                        <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 10px;" id="proctoringGlobalBadge">
                            AKTIF
                        </span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="position-relative bg-black rounded-2 overflow-hidden border border-secondary" style="aspect-ratio: 4/3;">
                                <video id="attemptCam1Video" autoplay playsinline muted class="w-100 h-100" style="object-fit: cover; transform: scaleX(-1);"></video>
                                <span class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-center text-white py-0.5" style="font-size: 9px;">Cam 1 (Wajah)</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="position-relative bg-black rounded-2 overflow-hidden border border-secondary" style="aspect-ratio: 4/3;">
                                <video id="attemptCam2Video" autoplay playsinline muted class="w-100 h-100" style="object-fit: cover;"></video>
                                <span class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-center text-white py-0.5" style="font-size: 9px;">Cam 2 (Meja)</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 text-center" style="font-size: 10px; color: #94a3b8;">
                        <i class="bi bi-shield-check text-success me-1"></i> Pengawasan Kamera & Audio Berjalan
                    </div>
                </div>
                <?php endif; ?>

                <div class="d-grid">
                    <button type="button" class="btn btn-outline-danger btn-sm py-2 rounded-3 font-bold" data-bs-toggle="modal" data-bs-target="#modalSubmitExam">
                        <i class="bi bi-send-check-fill me-1"></i> Selesai & Kumpulkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- 3. SUBMIT CONFIRMATION MODAL -->
<div class="modal fade" id="modalSubmitExam" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-danger text-white">
                <h5 class="modal-title font-bold">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Pengumpulan Ujian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('student/submitExam') ?>" method="POST" id="formSubmitExam">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">
                <input type="hidden" name="exam_id" value="<?= $participant['exam_id'] ?>">

                <div class="modal-body p-4 text-start">
                    <p class="mb-3">Apakah Anda yakin ingin mengakhiri dan mengumpulkan lembar jawaban ujian ini sekarang?</p>

                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row text-center g-2 text-xs">
                            <div class="col-4">
                                <span class="text-muted d-block">Total Soal</span>
                                <strong class="fs-6 font-mono text-dark"><?= count($questions) ?></strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block">Dijawab</span>
                                <strong class="fs-6 font-mono text-success" id="modalStatAnswered">0</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block">Belum / Ragu</span>
                                <strong class="fs-6 font-mono text-danger" id="modalStatRemaining">0</strong>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning text-xs mb-0 py-2" id="unansweredWarningAlert">
                        <i class="bi bi-info-circle-fill me-1"></i> Setelah dikumpulkan, Anda tidak dapat mengubah jawaban lagi.
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Lanjutkan Mengerjakan</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4 font-bold" id="btnConfirmSubmit">Ya, Kumpulkan Ujian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- =========================================================================
     4. EXAM ENGINE JAVASCRIPT
     ========================================================================= -->
<script>
const EXAM_CONFIG = {
    participantId: <?= (int)$participant['id'] ?>,
    examId: <?= (int)$participant['exam_id'] ?>,
    enableDualCamera: <?= !empty($participant['enable_dual_camera']) ? 'true' : 'false' ?>,
    enableAiProctoring: <?= !empty($participant['enable_ai_proctoring']) ? 'true' : 'false' ?>,
    remainingSeconds: <?= (int)$remainingSeconds ?>,
    csrfToken: '<?= CsrfHelper::generateToken() ?>',
    saveAnswerUrl: '<?= base_url('student/saveAnswer') ?>',
    setDoubtfulUrl: '<?= base_url('student/setDoubtful') ?>',
    submitExamUrl: '<?= base_url('student/submitExam') ?>',
    proctoringEventUrl: '<?= base_url('student/proctoringEvent') ?>'
};

const QUESTIONS = <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>;
const INITIAL_ANSWERS = <?= json_encode($savedAnswers, JSON_UNESCAPED_UNICODE) ?>;

// State Manajemen
let currentIndex = 0;
let userAnswers = {}; // { qId: { answer: 'A', doubtful: 0 } }
let remainingTime = EXAM_CONFIG.remainingSeconds;
let timerInterval = null;

// Inisialisasi Data Awal dari Database / LocalStorage
QUESTIONS.forEach(q => {
    const qId = q.id;
    const initial = INITIAL_ANSWERS[qId] || {};
    userAnswers[qId] = {
        answer: initial.student_answer !== undefined ? initial.student_answer : null,
        doubtful: initial.is_doubtful ? parseInt(initial.is_doubtful) : 0
    };
});

document.addEventListener('DOMContentLoaded', function() {
    initTimer();
    renderCurrentQuestion();
    updatePaletteStatus();
    attachEventListeners();
});

// 1. TIMER ENGINE
function initTimer() {
    updateTimerDisplay();
    timerInterval = setInterval(() => {
        remainingTime--;
        updateTimerDisplay();

        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            triggerAutoSubmit();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const timerBox = document.getElementById('timerBox');
    const display = document.getElementById('countdownDisplay');

    const hours = Math.floor(remainingTime / 3600);
    const minutes = Math.floor((remainingTime % 3600) / 60);
    const seconds = remainingTime % 60;

    const formatted = 
        String(hours).padStart(2, '0') + ':' + 
        String(minutes).padStart(2, '0') + ':' + 
        String(seconds).padStart(2, '0');

    if (display) display.textContent = formatted;

    if (timerBox) {
        if (remainingTime <= 60) {
            timerBox.className = 'timer-box danger-time';
        } else if (remainingTime <= 300) {
            timerBox.className = 'timer-box warning-time';
        } else {
            timerBox.className = 'timer-box';
        }
    }
}

function triggerAutoSubmit() {
    alert('Waktu ujian telah habis! Sistem akan otomatis mengumpulkan lembar jawaban Anda.');
    stopAllProctoringStreams();
    document.getElementById('formSubmitExam').submit();
}

// 2. RENDER QUESTION
function renderCurrentQuestion() {
    const q = QUESTIONS[currentIndex];
    if (!q) return;

    // Header Badges
    document.getElementById('qNumberBadge').textContent = `Soal No. ${currentIndex + 1} dari ${QUESTIONS.length}`;
    document.getElementById('qTypeBadge').textContent = q.question_type === 'multiple_choice' ? 'Pilihan Ganda' : 'Essay / Uraian';
    document.getElementById('qWeightBadge').textContent = `Bobot: ${parseFloat(q.score_weight).toFixed(1)} Poin`;

    // Doubtful Button State
    const isDoubtful = userAnswers[q.id].doubtful === 1;
    const btnDoubtful = document.getElementById('btnToggleDoubtful');
    const textDoubtful = document.getElementById('doubtfulBtnText');
    if (isDoubtful) {
        btnDoubtful.classList.add('active');
        textDoubtful.textContent = 'Ditandai Ragu';
    } else {
        btnDoubtful.classList.remove('active');
        textDoubtful.textContent = 'Ragu-Ragu';
    }

    // Question Text
    document.getElementById('questionTextContainer').innerHTML = q.question_text.replace(/\n/g, '<br>');

    // Options Container
    const optionsContainer = document.getElementById('optionsContainer');
    optionsContainer.innerHTML = '';

    const currentAnswer = userAnswers[q.id].answer;

    if (q.question_type === 'multiple_choice') {
        const options = q.options_decoded || {};
        const optKeys = ['A', 'B', 'C', 'D', 'E'];

        const listDiv = document.createElement('div');
        listDiv.className = 'd-flex flex-column gap-2.5';

        optKeys.forEach(key => {
            if (options[key]) {
                const isSelected = currentAnswer === key;
                const card = document.createElement('div');
                card.className = `option-card ${isSelected ? 'selected' : ''}`;
                card.onclick = () => selectOption(q.id, key);

                card.innerHTML = `
                    <div class="option-badge">${key}</div>
                    <div class="flex-grow-1 text-sm text-dark font-medium pt-0.5">${options[key]}</div>
                `;
                listDiv.appendChild(card);
            }
        });
        optionsContainer.appendChild(listDiv);
    } else {
        // Essay Box
        const essayWrapper = document.createElement('div');
        essayWrapper.innerHTML = `
            <label class="form-label text-xs font-bold text-secondary mb-1">Tuliskan Lembar Jawaban Essay Anda:</label>
            <textarea class="form-control text-sm font-sans" rows="5" placeholder="Ketikkan jawaban Anda di sini..." id="essayInput_${q.id}">${currentAnswer || ''}</textarea>
            <small class="text-muted text-xs d-block mt-1">Jawaban tersimpan otomatis saat Anda mengetik.</small>
        `;
        optionsContainer.appendChild(essayWrapper);

        const essayInput = document.getElementById(`essayInput_${q.id}`);
        let debounceTimeout = null;
        essayInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                saveAnswer(q.id, essayInput.value);
            }, 600);
        });
    }

    // Navigation Buttons State
    document.getElementById('btnPrev').disabled = (currentIndex === 0);
    document.getElementById('btnNext').disabled = (currentIndex === QUESTIONS.length - 1);

    updatePaletteActiveState();
}

// 3. SELEKSI JAWABAN & AUTOSAVE
function selectOption(questionId, optionKey) {
    userAnswers[questionId].answer = optionKey;
    renderCurrentQuestion();
    updatePaletteStatus();
    saveAnswer(questionId, optionKey);
}

function toggleDoubtful() {
    const q = QUESTIONS[currentIndex];
    const newStatus = userAnswers[q.id].doubtful === 1 ? 0 : 1;
    userAnswers[q.id].doubtful = newStatus;

    renderCurrentQuestion();
    updatePaletteStatus();
    saveAnswer(q.id, userAnswers[q.id].answer, newStatus);
}

function saveAnswer(questionId, answerValue, doubtfulStatus = null) {
    const isDoubtful = doubtfulStatus !== null ? doubtfulStatus : userAnswers[questionId].doubtful;
    showSavingIndicator(true);

    const formData = new FormData();
    formData.append('participant_id', EXAM_CONFIG.participantId);
    formData.append('question_id', questionId);
    formData.append('answer', answerValue !== null ? answerValue : '');
    formData.append('is_doubtful', isDoubtful);
    formData.append('csrf_token', EXAM_CONFIG.csrfToken);

    fetch(EXAM_CONFIG.saveAnswerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showSavingIndicator(false);
        if (data.expired) {
            triggerAutoSubmit();
        }
    })
    .catch(err => {
        showSavingIndicator(false, true);
        console.warn('Autosave server offline, tersimpan di memori browser:', err);
    });
}

function showSavingIndicator(isSaving, isError = false) {
    const statusEl = document.getElementById('autosaveStatus');
    if (!statusEl) return;

    if (isSaving) {
        statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span> Menyimpan...';
    } else if (isError) {
        statusEl.innerHTML = '<i class="bi bi-cloud-slash text-warning"></i> Offline Cache';
    } else {
        statusEl.innerHTML = '<i class="bi bi-cloud-check text-success fs-6"></i> <span>Tersimpan</span>';
    }
}

// 4. PALETTE NAVIGATION
function jumpToQuestion(index) {
    if (index >= 0 && index < QUESTIONS.length) {
        currentIndex = index;
        renderCurrentQuestion();
    }
}

function updatePaletteActiveState() {
    QUESTIONS.forEach((q, idx) => {
        const btn = document.getElementById(`pal_btn_${idx}`);
        if (btn) {
            if (idx === currentIndex) {
                btn.classList.add('active-q');
            } else {
                btn.classList.remove('active-q');
            }
        }
    });
}

function updatePaletteStatus() {
    let answeredCount = 0;
    let doubtfulCount = 0;

    QUESTIONS.forEach((q, idx) => {
        const btn = document.getElementById(`pal_btn_${idx}`);
        const state = userAnswers[q.id];

        if (btn) {
            btn.className = 'palette-btn';
            if (idx === currentIndex) btn.classList.add('active-q');

            const hasAnswer = state.answer !== null && state.answer !== '';
            if (state.doubtful === 1) {
                btn.classList.add('doubtful');
                doubtfulCount++;
            } else if (hasAnswer) {
                btn.classList.add('answered');
                answeredCount++;
            }
        }
    });

    const unansweredCount = QUESTIONS.length - answeredCount;

    document.getElementById('statAnswered').textContent = answeredCount;
    document.getElementById('statDoubtful').textContent = doubtfulCount;
    document.getElementById('statUnanswered').textContent = unansweredCount;

    // Modal stats
    document.getElementById('modalStatAnswered').textContent = answeredCount;
    document.getElementById('modalStatRemaining').textContent = unansweredCount + doubtfulCount;
}

// 5. EVENT LISTENERS & EXAM LOCKDOWN SECURITY SUITE
let violationCount = parseInt(sessionStorage.getItem('smartexam_violations_' + EXAM_CONFIG.participantId) || '0', 10);
const MAX_VIOLATIONS = 3;
let isExamSubmitted = false;
let isViolationLocked = false;

// Audio Synthesizer Beep Alarm
function playSecurityAlarm() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(320, ctx.currentTime + 0.4);
        gain.gain.setValueAtTime(0.4, ctx.currentTime);
        gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 0.4);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.4);
    } catch(e) {}
}

function requestExamFullscreen() {
    const el = document.documentElement;
    if (el.requestFullscreen) {
        el.requestFullscreen().catch(() => {});
    } else if (el.webkitRequestFullscreen) {
        el.webkitRequestFullscreen();
    } else if (el.msRequestFullscreen) {
        el.msRequestFullscreen();
    }
}

function recordViolation(type, description) {
    if (isExamSubmitted) return;

    violationCount++;
    sessionStorage.setItem('smartexam_violations_' + EXAM_CONFIG.participantId, violationCount);
    isViolationLocked = true;

    playSecurityAlarm();

    const overlay = document.getElementById('violationOverlay');
    const countBadge = document.getElementById('violationCountBadge');
    const noticeText = document.getElementById('violationNoticeText');
    const initModal = document.getElementById('lockdownInitModal');

    if (initModal) initModal.classList.add('d-none');
    if (countBadge) countBadge.textContent = violationCount;
    if (overlay) overlay.classList.remove('d-none');

    // Laporkan ke backend proctoring
    reportProctoringEvent({
        event_type: 'BROWSER_ACTIVITY_EVENT',
        camera: 'system',
        confidence_score: 99.00,
        duration: 1,
        severity: 'high',
        browser_event: type,
        browser_event_desc: `[PELANGGARAN ${violationCount}/${MAX_VIOLATIONS}] ${description}`
    });

    if (violationCount >= MAX_VIOLATIONS) {
        if (noticeText) {
            noticeText.innerHTML = '🚨 <strong>BATAS MAKSIMAL PELANGGARAN TERCAPAI (3/3)!</strong><br>Sistem mendiskualifikasi dan mengumpulkan lembar jawaban Anda secara otomatis.';
        }
        const resumeBtn = document.getElementById('btnResumeFullscreen');
        if (resumeBtn) {
            resumeBtn.disabled = true;
            resumeBtn.textContent = 'Mengumpulkan Jawaban Otomatis...';
        }

        setTimeout(() => {
            isExamSubmitted = true;
            stopAllProctoringStreams();
            const form = document.getElementById('formSubmitExam');
            if (form) form.submit();
        }, 2200);
    }
}

function attachEventListeners() {
    // Tombol Masuk Fullscreen Pertama Kali
    const btnEnterFs = document.getElementById('btnEnterFullscreen');
    if (btnEnterFs) {
        btnEnterFs.addEventListener('click', () => {
            requestExamFullscreen();
            const initModal = document.getElementById('lockdownInitModal');
            if (initModal) initModal.classList.add('d-none');
        });
    }

    // Tombol Kembali ke Layar Penuh dari Overlay Pelanggaran
    const btnResumeFs = document.getElementById('btnResumeFullscreen');
    if (btnResumeFs) {
        btnResumeFs.addEventListener('click', () => {
            requestExamFullscreen();
            const overlay = document.getElementById('violationOverlay');
            if (overlay) overlay.classList.add('d-none');
            setTimeout(() => { isViolationLocked = false; }, 800);
        });
    }

    // Deteksi Keluar dari Mode Fullscreen
    document.addEventListener('fullscreenchange', () => {
        const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement);
        if (!isFs && !isExamSubmitted) {
            const submitModal = document.getElementById('modalSubmitExam');
            const isSubmitOpen = submitModal && submitModal.classList.contains('show');
            if (!isSubmitOpen) {
                recordViolation('fullscreen_exit', 'Peserta keluar dari mode Layar Penuh (Fullscreen).');
            }
        }
    });

    // Deteksi Berpindah Tab Browser / Minimize
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden' && !isExamSubmitted) {
            recordViolation('tab_switch', 'Peserta terdeteksi membuka tab baru atau meminimalkan browser.');
        }
    });

    // Deteksi Hilang Fokus Jendela (Window Blur / Membuka Aplikasi Lain)
    window.addEventListener('blur', () => {
        if (!isExamSubmitted && !isViolationLocked) {
            recordViolation('window_blur', 'Fokus jendela ujian hilang (kemungkinan membuka aplikasi lain atau tab baru).');
        }
    });

    // Blokir Klik Kanan (Context Menu)
    document.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        return false;
    });

    // Blokir Seleksi & Copy Paste Teks
    document.addEventListener('selectstart', (e) => {
        if (!['input', 'textarea'].includes(e.target.tagName.toLowerCase())) {
            e.preventDefault();
        }
    });
    document.addEventListener('copy', (e) => {
        if (!['input', 'textarea'].includes(e.target.tagName.toLowerCase())) {
            e.preventDefault();
        }
    });
    document.addEventListener('cut', (e) => {
        if (!['input', 'textarea'].includes(e.target.tagName.toLowerCase())) {
            e.preventDefault();
        }
    });
    document.addEventListener('paste', (e) => {
        if (e.target.tagName.toLowerCase() !== 'textarea') {
            e.preventDefault();
        }
    });

    // Blokir Shortcut Berbahaya & Navigasi Keyboard
    document.addEventListener('keydown', function(e) {
        const key = e.key.toLowerCase();

        // 1. Cegah Shortcut Membuka Tab / Window Baru & Developer Tools
        if (e.ctrlKey && ['t', 'n', 'w', 'r', 'p', 's', 'u'].includes(key)) {
            e.preventDefault();
            e.stopPropagation();
            recordViolation('shortcut_blocked', `Percobaan penekanan tombol kombinasi Ctrl + ${key.toUpperCase()}`);
            return false;
        }

        if (e.ctrlKey && e.shiftKey && ['i', 'j', 'c', 'n', 't'].includes(key)) {
            e.preventDefault();
            e.stopPropagation();
            recordViolation('devtools_blocked', 'Percobaan membuka Developer Tools / Inspect Element.');
            return false;
        }

        if (['f5', 'f11', 'f12'].includes(key)) {
            e.preventDefault();
            e.stopPropagation();
            recordViolation('function_key_blocked', `Percobaan penekanan tombol fungsi ${key.toUpperCase()}`);
            return false;
        }

        // 2. Navigasi Soal Keyboard Biasa (hanya jika di luar input/textarea)
        if (['input', 'textarea'].includes(document.activeElement.tagName.toLowerCase())) {
            return;
        }

        if (e.key === 'ArrowLeft') {
            if (currentIndex > 0) { currentIndex--; renderCurrentQuestion(); }
        } else if (e.key === 'ArrowRight') {
            if (currentIndex < QUESTIONS.length - 1) { currentIndex++; renderCurrentQuestion(); }
        } else if (['a', 'b', 'c', 'd', 'e', 'A', 'B', 'C', 'D', 'E'].includes(e.key)) {
            const q = QUESTIONS[currentIndex];
            if (q && q.question_type === 'multiple_choice') {
                selectOption(q.id, e.key.toUpperCase());
            }
        }
    });

    // Tombol Navigasi Prev / Next
    document.getElementById('btnPrev').addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            renderCurrentQuestion();
        }
    });

    document.getElementById('btnNext').addEventListener('click', () => {
        if (currentIndex < QUESTIONS.length - 1) {
            currentIndex++;
            renderCurrentQuestion();
        }
    });

    document.getElementById('btnToggleDoubtful').addEventListener('click', toggleDoubtful);

    // Inisialisasi Aliran Video Proctoring Jika Diaktifkan
    if (EXAM_CONFIG.enableDualCamera) {
        initAttemptProctoring();
    }

    // Pastikan Kamera Diberhentikan Total Saat Ujian Dikumpulkan
    const submitForm = document.getElementById('formSubmitExam');
    if (submitForm) {
        submitForm.addEventListener('submit', function() {
            isExamSubmitted = true;
            stopAllProctoringStreams();
        });
    }

    window.addEventListener('beforeunload', (e) => {
        if (!isExamSubmitted) {
            stopAllProctoringStreams();
            e.preventDefault();
            e.returnValue = 'Ujian sedang berlangsung! Jangan tutup halaman ini.';
        }
    });
    window.addEventListener('pagehide', stopAllProctoringStreams);
}

// 6. PROCTORING LIFECYCLE & EDGE AI EVENT DETECTION
let proctoringStreams = [];
let proctoringInterval = null;
let proctoringCooldowns = {};
let faceMissingDuration = 0;
let darkFrameDuration = 0;

async function initAttemptProctoring() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;

    const cam1Id = sessionStorage.getItem('smartexam_cam1_device_id');
    const cam2Id = sessionStorage.getItem('smartexam_cam2_device_id');

    // 1. Hubungkan Kamera 1 (Wajah)
    try {
        const constraints1 = cam1Id 
            ? { video: { deviceId: { exact: cam1Id }, width: { ideal: 320 }, height: { ideal: 240 } }, audio: false }
            : { video: true, audio: false };
        const stream1 = await navigator.mediaDevices.getUserMedia(constraints1);
        proctoringStreams.push(stream1);
        const v1 = document.getElementById('attemptCam1Video');
        if (v1) {
            v1.srcObject = stream1;
            // Deteksi CAMERA_DISCONNECTED pada Cam 1
            const t1 = stream1.getVideoTracks()[0];
            if (t1) {
                t1.addEventListener('ended', () => {
                    reportProctoringEvent({
                        event_type: 'CAMERA_DISCONNECTED',
                        camera: 'cam1',
                        confidence_score: 100.00,
                        duration: 1,
                        severity: 'high',
                        description: 'Aliran perangkat Kamera 1 (Wajah) terputus atau dicabut.'
                    });
                });
            }
        }
    } catch (err) {
        console.warn('[Attempt Proctoring] Gagal memuat Cam 1:', err);
    }

    // 2. Hubungkan Kamera 2 (Meja)
    try {
        if (cam2Id && cam2Id !== cam1Id) {
            const stream2 = await navigator.mediaDevices.getUserMedia({
                video: { deviceId: { exact: cam2Id }, width: { ideal: 320 }, height: { ideal: 240 } },
                audio: false
            });
            proctoringStreams.push(stream2);
            const v2 = document.getElementById('attemptCam2Video');
            if (v2) {
                v2.srcObject = stream2;
                // Deteksi CAMERA_DISCONNECTED pada Cam 2
                const t2 = stream2.getVideoTracks()[0];
                if (t2) {
                    t2.addEventListener('ended', () => {
                        reportProctoringEvent({
                            event_type: 'CAMERA_DISCONNECTED',
                            camera: 'cam2',
                            confidence_score: 100.00,
                            duration: 1,
                            severity: 'high',
                            description: 'Aliran perangkat Kamera 2 (Meja) terputus atau dicabut.'
                        });
                    });
                }
            }
        }
    } catch (err) {
        console.warn('[Attempt Proctoring] Gagal memuat Cam 2:', err);
    }



    // 4. Mulai Edge Visual Sampling Loop (setiap 3 detik untuk efisiensi CPU)
    if (EXAM_CONFIG.enableAiProctoring) {
        startEdgeVisionLoop();
    }
}

/**
 * Sampling visual kanvas ringan untuk mendeteksi CAMERA_BLOCKED, FACE_MISSING, dll.
 */
function startEdgeVisionLoop() {
    const v1 = document.getElementById('attemptCam1Video');
    if (!v1) return;

    const offscreenCanvas = document.createElement('canvas');
    offscreenCanvas.width = 320;
    offscreenCanvas.height = 240;
    const ctx = offscreenCanvas.getContext('2d', { willReadFrequently: true });

    proctoringInterval = setInterval(() => {
        if (!v1.videoWidth || v1.paused || v1.ended) return;

        try {
            ctx.drawImage(v1, 0, 0, 320, 240);
            const frame = ctx.getImageData(0, 0, 320, 240);
            const data = frame.data;

            // Hitung rata-rata luminansi dan variansi
            let totalLum = 0;
            const step = 8; // sampling step untuk kecepatan
            let count = 0;

            for (let i = 0; i < data.length; i += 4 * step) {
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
                totalLum += (0.299 * r + 0.587 * g + 0.114 * b);
                count++;
            }

            const avgLum = totalLum / count;

            // 1. Uji CAMERA_BLOCKED (Lensa gelap atau tertutup)
            if (avgLum < 12.0) {
                darkFrameDuration += 3;
                if (darkFrameDuration >= 6) {
                    const snapshot = offscreenCanvas.toDataURL('image/jpeg', 0.6);
                    reportProctoringEvent({
                        event_type: 'CAMERA_BLOCKED',
                        camera: 'cam1',
                        confidence_score: 96.00,
                        duration: darkFrameDuration,
                        severity: 'high',
                        luminance: Math.round(avgLum),
                        description: `Lensa kamera utama terhalang atau sangat gelap (Luminansi: ${Math.round(avgLum)} lux).`,
                        snapshot_base64: snapshot
                    });
                }
            } else {
                darkFrameDuration = 0;
            }

        } catch (e) {
            console.warn('[EdgeVision] Frame read error:', e);
        }
    }, 3000);
}

/**
 * Kirim laporan anomali ke server (Privacy-by-Design & Debounced)
 */
function reportProctoringEvent(payload) {
    const eventType = payload.event_type;
    const now = Date.now();

    // Debounce: cegah spam pelaporan event yang sama berturut-turut dalam 10 detik
    if (proctoringCooldowns[eventType] && (now - proctoringCooldowns[eventType] < 10000)) {
        return;
    }
    proctoringCooldowns[eventType] = now;

    console.log(`[Proctoring Event] Melaporkan insiden: ${eventType}`, payload);

    const formData = new FormData();
    formData.append('csrf_token', EXAM_CONFIG.csrfToken);
    formData.append('participant_id', EXAM_CONFIG.participantId);
    formData.append('event_type', payload.event_type);
    formData.append('camera', payload.camera || 'cam1');
    formData.append('confidence_score', payload.confidence_score || 85.00);
    formData.append('duration', payload.duration || 1);
    formData.append('severity', payload.severity || 'medium');
    formData.append('description', payload.description || '');

    if (payload.snapshot_base64) {
        formData.append('snapshot_base64', payload.snapshot_base64);
    }
    if (payload.browser_event) {
        formData.append('browser_event', payload.browser_event);
        formData.append('browser_event_desc', payload.browser_event_desc || '');
    }

    fetch(EXAM_CONFIG.proctoringEventUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        console.log('[Proctoring Event] Berhasil dicatat untuk review guru/admin:', data);
    })
    .catch(err => {
        console.warn('[Proctoring Event] Gagal mengirim event:', err);
    });
}

function stopAllProctoringStreams() {
    if (proctoringInterval) {
        clearInterval(proctoringInterval);
        proctoringInterval = null;
    }

    if (proctoringStreams && proctoringStreams.length > 0) {
        proctoringStreams.forEach(stream => {
            if (stream && stream.getTracks) {
                stream.getTracks().forEach(track => {
                    track.stop();
                    console.log('[Attempt] Media track proctoring dihentikan secara permanen.');
                });
            }
        });
        proctoringStreams = [];
    }
}
</script>

</body>
</html>
