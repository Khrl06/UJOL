<?php
use App\Helpers\CsrfHelper;
$participant = array_merge([
    'subject_name' => '',
    'subject_code' => '',
    'class_name' => '-',
    'exam_title' => '',
    'exam_token' => '',
    'token' => '',
    'enable_dual_camera' => 0,
    'enable_ai_proctoring' => 0,
    'status' => 'registered',
    'exam_id' => 0
], $participant ?? []);
?>

<!-- Header Halaman Persiapan -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary rounded-pill px-3 py-1 text-xs font-semibold">
                <i class="bi bi-journal-bookmark me-1"></i><?= e($participant['subject_name']) ?> (<?= e($participant['subject_code']) ?>)
            </span>
            <span class="badge bg-secondary rounded-pill px-2.5 py-1 text-xs">
                Kelas: <?= e($participant['class_name']) ?>
            </span>
            <?php if (!empty($participant['enable_dual_camera'])): ?>
                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 text-xs font-semibold">
                    <i class="bi bi-camera-video-fill me-1"></i> Wajib Dual Camera
                </span>
            <?php endif; ?>
        </div>
        <h3 class="font-bold text-dark mb-0">Pemeriksaan Perangkat & Kamera Proctoring</h3>
        <p class="text-muted text-xs mb-0 mt-0.5">Ujian: <strong><?= e($participant['exam_title']) ?></strong> | Peserta: <?= e($participant['student_name']) ?> (NISN: <?= e($participant['nisn']) ?>)</p>
    </div>
    <div>
        <a href="<?= base_url('student/exams/' . $participant['exam_id']) ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3" onclick="cleanupStreams()">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail Ujian
        </a>
    </div>
</div>

<!-- Banner Instruksi & Persyaratan Pengawasan -->
<div class="card-custom p-4 mb-4 border-start border-4 border-primary">
    <div class="d-flex align-items-start gap-3">
        <div class="p-3 bg-primary-subtle text-primary rounded-3 flex-shrink-0">
            <i class="bi bi-shield-lock-fill fs-3"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="font-bold text-dark mb-1">Standarisasi Pengawasan Ujian Online Berbasis Kamera Ganda</h5>
            <p class="text-muted text-xs mb-2 leading-relaxed">
                Untuk menjamin kejujuran dan validitas asesmen, sistem mewajibkan verifikasi dua sudut pandang kamera sebelum Anda dapat mengakses lembar soal ujian:
            </p>
            <div class="row g-2 text-xs">
                <div class="col-12 col-md-4">
                    <div class="p-2.5 rounded-2 bg-light border">
                        <strong class="text-dark d-block mb-1"><i class="bi bi-person-bounding-box text-primary me-1"></i> Kamera 1 (Utama):</strong>
                        Diarahkan tepat ke wajah dan pandangan mata Anda (Webcam laptop/PC).
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-2.5 rounded-2 bg-light border">
                        <strong class="text-dark d-block mb-1"><i class="bi bi-display text-info me-1"></i> Kamera 2 (Sekunder):</strong>
                        Diarahkan dari samping untuk memantau area meja, tangan, dan keyboard (Webcam USB / HP).
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-2.5 rounded-2 bg-light border">
                        <strong class="text-dark d-block mb-1"><i class="bi bi-mic-fill text-success me-1"></i> Mikrofon (Audio):</strong>
                        Memantau suara lingkungan sekitar ruang ujian untuk mencegah kolaborasi luar.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Notifikasi Dinamis (Dikelola via JS) -->
<div id="deviceAlertContainer" class="mb-4"></div>

<!-- Area Utama Pemeriksaan Perangkat (Grid 2 Kolom) -->
<div class="row g-4 mb-4">
    <!-- KOLOM KAMERA 1 (Kamera Wajah) -->
    <div class="col-12 col-lg-6">
        <div class="card-custom h-100 p-4 border-top border-4 border-primary d-flex flex-column justify-content-between shadow-sm">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h5 class="font-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-person-video text-primary"></i> Kamera 1: Wajah Peserta
                        </h5>
                        <small class="text-muted text-xs">Kamera sudut depan (Primary Face Camera)</small>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-1.5 text-xs font-semibold" id="cam1StatusBadge">
                        <i class="bi bi-hourglass-split me-1"></i> Menunggu Izin
                    </span>
                </div>

                <!-- Live Video Feed Container -->
                <div class="position-relative bg-dark rounded-3 overflow-hidden mb-3 border shadow-inner" style="aspect-ratio: 16/9; min-height: 240px;">
                    <video id="cam1Preview" autoplay playsinline muted class="w-100 h-100" style="object-fit: cover; transform: scaleX(-1);"></video>
                    
                    <!-- Overlay Placeholder saat kamera belum aktif -->
                    <div id="cam1Placeholder" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark text-white p-3 text-center" style="background: rgba(15, 23, 42, 0.95);">
                        <i class="bi bi-camera-video-off fs-1 text-secondary mb-2" id="cam1PlaceholderIcon"></i>
                        <span class="font-semibold text-sm" id="cam1PlaceholderText">Kamera 1 Belum Terhubung</span>
                        <small class="text-secondary text-xs mt-1" id="cam1PlaceholderSub">Izinkan akses browser atau pilih perangkat</small>
                    </div>

                    <!-- Live Indicator Pill -->
                    <div class="position-absolute top-2 start-2 d-none" id="cam1LivePill" style="top: 12px; left: 12px;">
                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 text-xs font-bold d-flex align-items-center gap-1 shadow">
                            <span class="spinner-grow spinner-grow-sm text-light" style="width: 8px; height: 8px;"></span>
                            LIVE CAM 1
                        </span>
                    </div>

                    <!-- Resolution Pill -->
                    <div class="position-absolute bottom-2 end-2 d-none" id="cam1ResPill" style="bottom: 12px; right: 12px;">
                        <span class="badge bg-dark bg-opacity-75 text-light border border-secondary text-xs font-mono" id="cam1ResText">
                            -- x --
                        </span>
                    </div>
                </div>

                <!-- Dropdown Pemilihan Perangkat Kamera 1 -->
                <div class="mb-3">
                    <label class="form-label text-xs font-semibold text-secondary d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-camera-fill me-1 text-primary"></i> Pilih Sumber Kamera 1:</span>
                        <span class="text-xs text-muted" id="cam1DeviceCountText">0 Perangkat</span>
                    </label>
                    <select class="form-select form-select-sm rounded-2 font-medium" id="cam1Select">
                        <option value="">-- Menunggu Enumerasi Perangkat --</option>
                    </select>
                </div>

                <!-- Status Metadata Kamera 1 -->
                <div class="p-3 bg-light rounded-3 border text-xs text-muted">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Izin Browser (Permission):</span>
                        <strong class="font-semibold" id="cam1PermText">Belum Diminta</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Konektivitas Hardware:</span>
                        <strong class="font-semibold text-secondary" id="cam1ConnText">Mendeteksi...</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Fokus Pengawasan:</span>
                        <strong class="text-primary font-semibold">Wajah & Mata Peserta</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KOLOM KAMERA 2 (Kamera Meja/Samping) -->
    <div class="col-12 col-lg-6">
        <div class="card-custom h-100 p-4 border-top border-4 border-info d-flex flex-column justify-content-between shadow-sm">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h5 class="font-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-display text-info"></i> Kamera 2: Meja & Area Kerja
                        </h5>
                        <small class="text-muted text-xs">Kamera sudut samping/perangkat (Desk / Side Camera)</small>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-1.5 text-xs font-semibold" id="cam2StatusBadge">
                        <i class="bi bi-hourglass-split me-1"></i> Menunggu Izin
                    </span>
                </div>

                <!-- Live Video Feed Container -->
                <div class="position-relative bg-dark rounded-3 overflow-hidden mb-3 border shadow-inner" style="aspect-ratio: 16/9; min-height: 240px;">
                    <video id="cam2Preview" autoplay playsinline muted class="w-100 h-100" style="object-fit: cover;"></video>
                    
                    <!-- Overlay Placeholder saat kamera belum aktif -->
                    <div id="cam2Placeholder" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark text-white p-3 text-center" style="background: rgba(15, 23, 42, 0.95);">
                        <i class="bi bi-camera-video-off fs-1 text-secondary mb-2" id="cam2PlaceholderIcon"></i>
                        <span class="font-semibold text-sm" id="cam2PlaceholderText">Kamera 2 Belum Terhubung</span>
                        <small class="text-secondary text-xs mt-1" id="cam2PlaceholderSub">Hubungkan webcam eksternal atau kamera kedua</small>
                    </div>

                    <!-- Live Indicator Pill -->
                    <div class="position-absolute top-2 start-2 d-none" id="cam2LivePill" style="top: 12px; left: 12px;">
                        <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 text-xs font-bold d-flex align-items-center gap-1 shadow">
                            <span class="spinner-grow spinner-grow-sm text-dark" style="width: 8px; height: 8px;"></span>
                            LIVE CAM 2
                        </span>
                    </div>

                    <!-- Resolution Pill -->
                    <div class="position-absolute bottom-2 end-2 d-none" id="cam2ResPill" style="bottom: 12px; right: 12px;">
                        <span class="badge bg-dark bg-opacity-75 text-light border border-secondary text-xs font-mono" id="cam2ResText">
                            -- x --
                        </span>
                    </div>
                </div>

                <!-- Dropdown Pemilihan Perangkat Kamera 2 -->
                <div class="mb-3">
                    <label class="form-label text-xs font-semibold text-secondary d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-camera-fill me-1 text-info"></i> Pilih Sumber Kamera 2:</span>
                        <span class="text-xs text-muted" id="cam2DeviceCountText">0 Perangkat</span>
                    </label>
                    <select class="form-select form-select-sm rounded-2 font-medium" id="cam2Select">
                        <option value="">-- Menunggu Enumerasi Perangkat --</option>
                    </select>
                </div>

                <!-- Status Metadata Kamera 2 -->
                <div class="p-3 bg-light rounded-3 border text-xs text-muted">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Izin Browser (Permission):</span>
                        <strong class="font-semibold" id="cam2PermText">Belum Diminta</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Konektivitas Hardware:</span>
                        <strong class="font-semibold text-secondary" id="cam2ConnText">Mendeteksi...</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Fokus Pengawasan:</span>
                        <strong class="text-info font-semibold">Area Meja, Tangan & Layar</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BARIS PEMERIKSAAN MIKROFON & STATUS HARDWARE -->
<div class="row g-4 mb-4">
    <!-- KARTU MIKROFON -->
    <div class="col-12 col-md-6">
        <div class="card-custom p-4 border-start border-4 border-success shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="font-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-mic-fill text-success fs-5"></i> Pemeriksaan Mikrofon Lingkungan
                </h6>
                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-1 text-xs font-semibold" id="micStatusBadge">
                    Menunggu Izin
                </span>
            </div>
            
            <p class="text-muted text-xs mb-3">
                Mikrofon digunakan untuk memverifikasi tingkat kebisingan di sekitar Anda selama asesmen berlangsung. Bicaralah atau bertepuk tangan untuk menguji deteksi suara:
            </p>

            <!-- Audio Level Meter Visualizer -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center text-xs mb-1">
                    <span class="text-secondary font-semibold">Level Input Audio:</span>
                    <span class="font-mono font-bold text-success" id="micLevelPercentage">0%</span>
                </div>
                <div class="progress rounded-pill bg-light border" style="height: 12px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated transition-all" id="micLevelBar" style="width: 0%; transition: width 0.08s ease;"></div>
                </div>
            </div>

            <div class="p-2.5 bg-light rounded-2 border text-xs d-flex justify-content-between align-items-center text-muted">
                <span>Status Izin Audio: <strong class="text-dark" id="micPermLabel">Memeriksa...</strong></span>
                <span class="badge bg-light text-secondary border" id="micDeviceLabel">Default Audio Input</span>
            </div>
        </div>
    </div>

    <!-- KARTU RANGKUMAN HARDWARE & PERSYARATAN -->
    <div class="col-12 col-md-6">
        <div class="card-custom p-4 border-start border-4 border-warning shadow-sm h-100 d-flex flex-column justify-content-between">
            <div>
                <h6 class="font-bold text-dark mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-warning fs-5"></i> Rangkuman Kesiapan Perangkat
                </h6>
                <p class="text-muted text-xs mb-3">
                    Pastikan kedua kamera dan mikrofon memiliki status hijau (Terhubung) sebelum menekan tombol Lanjut.
                </p>

                <ul class="list-group list-group-flush text-xs mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1.5">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-circle-fill text-muted" id="checklistCam1Icon"></i> Kamera 1 (Wajah)
                        </span>
                        <span class="font-semibold text-muted" id="checklistCam1Status">Belum Siap</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1.5">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-circle-fill text-muted" id="checklistCam2Icon"></i> Kamera 2 (Meja)
                        </span>
                        <span class="font-semibold text-muted" id="checklistCam2Status">Belum Siap</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1.5">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-circle-fill text-muted" id="checklistMicIcon"></i> Mikrofon Lingkungan
                        </span>
                        <span class="font-semibold text-muted" id="checklistMicStatus">Belum Siap</span>
                    </li>
                </ul>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-3 py-2 px-3 flex-grow-1 font-semibold d-flex align-items-center justify-content-center gap-1.5" id="btnTestCamera" onclick="retestDevices()">
                    <i class="bi bi-arrow-clockwise fs-6"></i>
                    <span>Uji Ulang Kamera / Test Camera</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER AKSI: INPUT TOKEN & TOMBOL CONTINUE -->
<div class="card-custom p-4 shadow-sm border-top border-4 border-success">
    <div class="row align-items-center g-3">
        <div class="col-12 col-md-7">
            <h5 class="font-bold text-dark mb-1">
                <i class="bi bi-check2-circle text-success me-1"></i> Siap Melanjutkan ke Ujian?
            </h5>
            <p class="text-muted text-xs mb-0">
                <?php if (!empty($participant['enable_dual_camera'])): ?>
                    Tombol <strong>Lanjut ke Ujian</strong> akan otomatis aktif setelah Kamera 1 dan Kamera 2 berhasil terhubung dengan dua perangkat yang berbeda.
                <?php else: ?>
                    Tombol <strong>Lanjut ke Ujian</strong> akan otomatis aktif setelah Kamera 1 berhasil diverifikasi.
                <?php endif; ?>
            </p>
        </div>

        <div class="col-12 col-md-5">
            <?php if ($participant['status'] === 'ongoing'): ?>
                <!-- Siswa sudah dalam pengerjaan aktif -> Tombol Kembali ke Ruang Ujian -->
                <div class="d-grid">
                    <button type="button" class="btn btn-primary py-3 rounded-3 font-bold fs-6 shadow d-flex align-items-center justify-content-center gap-2" id="btnContinueExam" onclick="proceedToAttempt()">
                        <i class="bi bi-box-arrow-in-right fs-5"></i>
                        <span>Lanjut ke Ruang Ujian (Ongoing)</span>
                    </button>
                </div>
            <?php else: ?>
                <!-- Siswa baru akan memulai -> Form Token & Start Ujian -->
                <form action="<?= base_url('student/exams/' . $participant['exam_id'] . '/start') ?>" method="POST" id="formStartAttempt" onsubmit="return handleStartSubmit(event)">
                    <?= CsrfHelper::tokenField() ?>
                    <div class="mb-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key-fill text-warning"></i></span>
                            <input type="text" name="token" id="inputExamToken" class="form-control font-mono font-bold text-uppercase text-center" 
                                   placeholder="TOKEN UJIAN (Contoh: <?= e($participant['exam_token'] ?? ($participant['token'] ?? '')) ?>)" 
                                   value="<?= e($participant['exam_token'] ?? ($participant['token'] ?? '')) ?>" maxlength="10" required autocomplete="off">
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success py-3 rounded-3 font-bold fs-6 shadow d-flex align-items-center justify-content-center gap-2" id="btnContinueExam" disabled>
                            <i class="bi bi-play-circle-fill fs-5"></i>
                            <span>Lanjut & Mulai Ujian (Continue)</span>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- =========================================================================
     JAVASCRIPT: DUAL CAMERA & MICROPHONE PROCTORING DEVICE CHECK ENGINE
     ========================================================================= -->
<script>
const DEVICE_CHECK_CONFIG = {
    examId: <?= (int)$participant['exam_id'] ?>,
    participantId: <?= (int)$participant['id'] ?>,
    enableDualCamera: <?= !empty($participant['enable_dual_camera']) ? 'true' : 'false' ?>,
    attemptUrl: '<?= base_url('student/exams/' . $participant['exam_id'] . '/attempt') ?>',
    status: '<?= e($participant['status']) ?>'
};

// Global Media State
let mediaState = {
    videoDevices: [],
    cam1Stream: null,
    cam2Stream: null,
    micStream: null,
    audioContext: null,
    audioAnalyser: null,
    audioAnimFrame: null,
    cam1SelectedId: '',
    cam2SelectedId: '',
    cam1Status: 'pending', // 'pending' | 'active' | 'disconnected' | 'denied'
    cam2Status: 'pending',
    micStatus: 'pending'
};

document.addEventListener('DOMContentLoaded', function() {
    initDeviceCheck();

    // Event listener untuk perubahan dropdown kamera
    document.getElementById('cam1Select').addEventListener('change', onCam1Change);
    document.getElementById('cam2Select').addEventListener('change', onCam2Change);

    // Event listener deteksi cabut/colok perangkat (Device Change Event)
    if (navigator.mediaDevices && navigator.mediaDevices.addEventListener) {
        navigator.mediaDevices.addEventListener('devicechange', async () => {
            console.log('[DeviceCheck] Perubahan perangkat terdeteksi oleh browser!');
            showToastNotice('Pembaruan Perangkat Terdeteksi: Memindai ulang koneksi kamera & mikrofon...', 'info');
            await refreshDeviceEnumeration();
        });
    }

    // Tangani pelepasan stream saat halaman ditutup atau ditinggalkan
    window.addEventListener('beforeunload', cleanupStreams);
    window.addEventListener('pagehide', cleanupStreams);
});

/**
 * 1. INISIALISASI PEMERIKSAAN PERANGKAT
 */
async function initDeviceCheck() {
    clearAlerts();

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showAlert('danger', 'Browser Anda tidak mendukung API MediaDevices (WebRTC). Silakan gunakan Google Chrome, Microsoft Edge, atau Mozilla Firefox versi terbaru.');
        setCamStatus(1, 'denied', 'Browser Tidak Mendukung');
        setCamStatus(2, 'denied', 'Browser Tidak Mendukung');
        setMicStatus('denied', 'Tidak Didukung');
        return;
    }

    // Langkah 1: Minta izin akses kamera dan mikrofon
    try {
        updatePlaceholder(1, 'meminta-izin', 'Meminta Izin Kamera 1...', 'Klik Izinkan (Allow) pada browser');
        updatePlaceholder(2, 'meminta-izin', 'Meminta Izin Kamera 2...', 'Klik Izinkan (Allow) pada browser');
        
        // Meminta stream awal untuk membuka label perangkat
        const initialStream = await navigator.mediaDevices.getUserMedia({ 
            video: true, 
            audio: true 
        });

        // Izin didapatkan! Hentikan stream sementara
        initialStream.getTracks().forEach(t => t.stop());

        document.getElementById('cam1PermText').textContent = 'Diizinkan (Granted)';
        document.getElementById('cam2PermText').textContent = 'Diizinkan (Granted)';
        document.getElementById('micPermLabel').textContent = 'Diizinkan (Granted)';
        
    } catch (err) {
        console.warn('[DeviceCheck] Akses media ditolak atau gagal:', err);
        handlePermissionError(err);
        return;
    }

    // Langkah 2: Enumerasi semua perangkat video input & audio input
    await refreshDeviceEnumeration();

    // Langkah 3: Inisialisasi Audio Level Meter
    await initMicrophoneStream();
}

/**
 * 2. ENUMERASI PERANGKAT KAMERA
 */
async function refreshDeviceEnumeration() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        mediaState.videoDevices = devices.filter(d => d.kind === 'videoinput');

        const camCount = mediaState.videoDevices.length;
        console.log(`[DeviceCheck] Ditemukan ${camCount} perangkat kamera:`, mediaState.videoDevices);

        document.getElementById('cam1DeviceCountText').textContent = `${camCount} Perangkat Tersedia`;
        document.getElementById('cam2DeviceCountText').textContent = `${camCount} Perangkat Tersedia`;

        // Populasi Dropdown Kamera 1 & 2
        populateCameraSelects(mediaState.videoDevices);

        // Evaluasi Ketersediaan Kamera Sesuai Spesifikasi Dual Camera
        if (camCount === 0) {
            showAlert('danger', '<strong>Tidak Ada Kamera Terdeteksi:</strong> Komputer Anda tidak memiliki perangkat kamera yang aktif. Pastikan webcam terhubung.');
            setCamStatus(1, 'disconnected', 'Tidak Ditemukan');
            setCamStatus(2, 'disconnected', 'Tidak Ditemukan');
            validateContinueState();
            return;
        }

        if (camCount === 1 && DEVICE_CHECK_CONFIG.enableDualCamera) {
            showAlert('warning', `
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="d-block mb-1">Perangkat Hanya Memiliki 1 Kamera (Ujian Mewajibkan Dual Camera):</strong>
                        Sistem mendeteksi hanya <strong>1 perangkat kamera</strong> pada komputer Anda. Ujian ini mewajibkan 2 sumber kamera independen (Kamera Wajah dan Kamera Meja).
                        <ul class="mb-1 ps-3 mt-1">
                            <li>Sambungkan <strong>Webcam USB eksternal</strong> ke port komputer Anda, atau</li>
                            <li>Gunakan kamera smartphone sebagai webcam sekunder (menggunakan aplikasi seperti DroidCam, Iriun Webcam, dll).</li>
                        </ul>
                        Setelah menghubungkan kamera kedua, klik tombol <strong>Uji Ulang Kamera</strong> di bawah.
                    </div>
                </div>
            `);
        }

        // Tentukan Default Device Selection
        // Jika sebelumnya sudah ada pilihan di sessionStorage
        const savedCam1 = sessionStorage.getItem('smartexam_cam1_device_id');
        const savedCam2 = sessionStorage.getItem('smartexam_cam2_device_id');

        let defaultCam1Id = (savedCam1 && mediaState.videoDevices.some(d => d.deviceId === savedCam1)) 
            ? savedCam1 
            : (mediaState.videoDevices[0]?.deviceId || '');

        let defaultCam2Id = (savedCam2 && mediaState.videoDevices.some(d => d.deviceId === savedCam2))
            ? savedCam2
            : (mediaState.videoDevices.length > 1 ? mediaState.videoDevices[1]?.deviceId : '');

        // Jika default sama dan ada 2 perangkat, pisahkan
        if (defaultCam1Id === defaultCam2Id && mediaState.videoDevices.length > 1) {
            defaultCam2Id = mediaState.videoDevices[1].deviceId;
        }

        document.getElementById('cam1Select').value = defaultCam1Id;
        document.getElementById('cam2Select').value = defaultCam2Id;

        mediaState.cam1SelectedId = defaultCam1Id;
        mediaState.cam2SelectedId = defaultCam2Id;

        // Jalankan Preview untuk masing-masing kamera
        if (defaultCam1Id) {
            await startCameraPreview(1, defaultCam1Id);
        } else {
            setCamStatus(1, 'disconnected', 'Tidak Ada Perangkat');
        }

        if (defaultCam2Id) {
            await startCameraPreview(2, defaultCam2Id);
        } else {
            if (DEVICE_CHECK_CONFIG.enableDualCamera) {
                setCamStatus(2, 'disconnected', 'Butuh Kamera ke-2');
                updatePlaceholder(2, 'butuh-kamera', 'Kamera 2 Belum Tersedia', 'Sambungkan kamera kedua untuk melanjutkan');
            } else {
                setCamStatus(2, 'disconnected', 'Opsional (Tidak Wajib)');
                updatePlaceholder(2, 'opsional', 'Kamera 2 Opsional', 'Ujian ini tidak mewajibkan kamera kedua');
            }
        }

        validateContinueState();

    } catch (err) {
        console.error('[DeviceCheck] Gagal mengenumerasi perangkat:', err);
        showAlert('danger', 'Gagal memindai perangkat input video/audio. Pastikan izin kamera telah diberikan.');
    }
}

/**
 * 3. POPULASI DROPDOWN SELEKSI KAMERA
 */
function populateCameraSelects(devices) {
    const cam1Select = document.getElementById('cam1Select');
    const cam2Select = document.getElementById('cam2Select');

    cam1Select.innerHTML = '';
    cam2Select.innerHTML = '';

    if (devices.length === 0) {
        cam1Select.innerHTML = '<option value="">Tidak ada kamera terdeteksi</option>';
        cam2Select.innerHTML = '<option value="">Tidak ada kamera terdeteksi</option>';
        return;
    }

    devices.forEach((dev, idx) => {
        const label = dev.label || `Kamera ${idx + 1} (${dev.deviceId.slice(0, 8)}...)`;

        const opt1 = document.createElement('option');
        opt1.value = dev.deviceId;
        opt1.textContent = `[Perangkat ${idx + 1}] ${label}`;
        cam1Select.appendChild(opt1);

        const opt2 = document.createElement('option');
        opt2.value = dev.deviceId;
        opt2.textContent = `[Perangkat ${idx + 1}] ${label}`;
        cam2Select.appendChild(opt2);
    });

    if (devices.length === 1 && DEVICE_CHECK_CONFIG.enableDualCamera) {
        const warningOpt = document.createElement('option');
        warningOpt.value = '';
        warningOpt.textContent = '⚠️ Butuh Kamera ke-2 (Hubungkan Webcam Eksternal)';
        cam2Select.appendChild(warningOpt);
    }
}

/**
 * 4. JALANKAN PREVIEW KAMERA (Cam 1 / Cam 2)
 */
async function startCameraPreview(camNumber, deviceId) {
    const videoEl = document.getElementById(`cam${camNumber}Preview`);
    const placeholder = document.getElementById(`cam${camNumber}Placeholder`);
    const livePill = document.getElementById(`cam${camNumber}LivePill`);
    const resPill = document.getElementById(`cam${camNumber}ResPill`);
    const resText = document.getElementById(`cam${camNumber}ResText`);

    // Hentikan stream sebelumnya untuk slot ini
    stopStreamFor(camNumber);

    try {
        const constraints = {
            video: {
                deviceId: { exact: deviceId },
                width: { ideal: 640 },
                height: { ideal: 480 }
            },
            audio: false
        };

        const stream = await navigator.mediaDevices.getUserMedia(constraints);

        if (camNumber === 1) {
            mediaState.cam1Stream = stream;
        } else {
            mediaState.cam2Stream = stream;
        }

        videoEl.srcObject = stream;

        // Tangani track ended (misal kabel USB dicabut)
        const videoTrack = stream.getVideoTracks()[0];
        if (videoTrack) {
            videoTrack.addEventListener('ended', () => {
                console.warn(`[DeviceCheck] Track Kamera ${camNumber} terputus (unplugged / stopped)!`);
                setCamStatus(camNumber, 'disconnected', 'Terputus (Unplugged)');
                updatePlaceholder(camNumber, 'terputus', `Kamera ${camNumber} Terputus!`, 'Kamera dilepas atau tidak dapat diakses');
                showAlert('danger', `<strong>Kamera ${camNumber} Terputus!</strong> Perangkat kamera terlepas dari komputer. Pastikan kabel kamera terhubung kokoh.`);
                validateContinueState();
            });
        }

        // Ketika video mulai memutar
        videoEl.onloadedmetadata = () => {
            placeholder.classList.add('d-none');
            livePill.classList.remove('d-none');
            resPill.classList.remove('d-none');
            resText.textContent = `${videoEl.videoWidth}x${videoEl.videoHeight}`;
            setCamStatus(camNumber, 'active', 'Terhubung (Aktif)');
            validateContinueState();
        };

    } catch (err) {
        console.error(`[DeviceCheck] Gagal mengaktifkan Kamera ${camNumber}:`, err);
        setCamStatus(camNumber, 'disconnected', 'Gagal Terhubung');
        updatePlaceholder(camNumber, 'error', `Kamera ${camNumber} Gagal Dibuka`, err.message || 'Perangkat sedang dipakai aplikasi lain');
        validateContinueState();
    }
}

/**
 * 5. INISIALISASI MIKROFON & AUDIO LEVEL METER
 */
async function initMicrophoneStream() {
    stopMicStream();

    try {
        const micStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
        mediaState.micStream = micStream;

        const audioTrack = micStream.getAudioTracks()[0];
        if (audioTrack) {
            document.getElementById('micDeviceLabel').textContent = audioTrack.label || 'Mikrofon Aktif';
            audioTrack.addEventListener('ended', () => {
                setMicStatus('disconnected', 'Terputus');
                validateContinueState();
            });
        }

        setMicStatus('active', 'Terhubung & Aktif');

        // Setup AudioContext untuk visualizer realtime
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (AudioContextClass) {
            mediaState.audioContext = new AudioContextClass();
            const source = mediaState.audioContext.createMediaStreamSource(micStream);
            const analyser = mediaState.audioContext.createAnalyser();
            analyser.fftSize = 256;
            source.connect(analyser);

            mediaState.audioAnalyser = analyser;
            startAudioMeterLoop();
        }

    } catch (err) {
        console.warn('[DeviceCheck] Gagal mengakses mikrofon:', err);
        setMicStatus('denied', 'Izin Ditolak / Tidak Ada Mic');
    }

    validateContinueState();
}

function startAudioMeterLoop() {
    const analyser = mediaState.audioAnalyser;
    if (!analyser) return;

    const dataArray = new Uint8Array(analyser.frequencyBinCount);
    const meterBar = document.getElementById('micLevelBar');
    const levelText = document.getElementById('micLevelPercentage');

    function updateMeter() {
        if (!mediaState.audioAnalyser) return;
        analyser.getByteFrequencyData(dataArray);
        
        let sum = 0;
        for (let i = 0; i < dataArray.length; i++) {
            sum += dataArray[i];
        }
        const average = sum / dataArray.length;
        const volumePercent = Math.min(100, Math.round((average / 128) * 100));

        if (meterBar) meterBar.style.width = volumePercent + '%';
        if (levelText) levelText.textContent = volumePercent + '%';

        mediaState.audioAnimFrame = requestAnimationFrame(updateMeter);
    }

    updateMeter();
}

/**
 * 6. HANDLER GANTI PILIHAN KAMERA 1 & 2
 */
async function onCam1Change(e) {
    const deviceId = e.target.value;
    mediaState.cam1SelectedId = deviceId;
    
    // Periksa duplikasi perangkat
    if (deviceId && deviceId === mediaState.cam2SelectedId) {
        showAlert('warning', '<strong>Perangkat Sama:</strong> Kamera 1 dan Kamera 2 tidak boleh menggunakan sumber kamera yang sama. Pilih perangkat yang berbeda untuk Kamera 2.');
    } else {
        clearAlerts();
    }

    if (deviceId) {
        await startCameraPreview(1, deviceId);
    } else {
        stopStreamFor(1);
        setCamStatus(1, 'disconnected', 'Tidak Dipilih');
    }
    validateContinueState();
}

async function onCam2Change(e) {
    const deviceId = e.target.value;
    mediaState.cam2SelectedId = deviceId;

    // Periksa duplikasi perangkat
    if (deviceId && deviceId === mediaState.cam1SelectedId) {
        showAlert('warning', '<strong>Perangkat Sama:</strong> Kamera 1 dan Kamera 2 tidak boleh menggunakan sumber kamera yang sama. Pilih perangkat yang berbeda untuk Kamera 2.');
    } else {
        clearAlerts();
    }

    if (deviceId) {
        await startCameraPreview(2, deviceId);
    } else {
        stopStreamFor(2);
        setCamStatus(2, 'disconnected', 'Tidak Dipilih');
    }
    validateContinueState();
}

/**
 * 7. VALIDASI KELAYAKAN STATUS & TOMBOL CONTINUE
 */
function validateContinueState() {
    const isCam1Ready = (mediaState.cam1Status === 'active');
    const isCam2Ready = (mediaState.cam2Status === 'active');
    const isDifferentDevices = (mediaState.cam1SelectedId && mediaState.cam2SelectedId && mediaState.cam1SelectedId !== mediaState.cam2SelectedId);

    // Update Checklist Item
    updateChecklistItem('Cam1', isCam1Ready, isCam1Ready ? 'Aktif & Terhubung' : 'Belum Aktif');
    
    if (DEVICE_CHECK_CONFIG.enableDualCamera) {
        const cam2Label = isCam2Ready 
            ? (isDifferentDevices ? 'Aktif & Terverifikasi' : 'Perangkat Sama dengan Cam 1')
            : 'Belum Aktif / Kurang Kamera';
        updateChecklistItem('Cam2', (isCam2Ready && isDifferentDevices), cam2Label);
    } else {
        updateChecklistItem('Cam2', true, 'Opsional (Tidak Wajib)');
    }

    const isMicReady = (mediaState.micStatus === 'active');
    updateChecklistItem('Mic', isMicReady, isMicReady ? 'Audio Terdeteksi' : 'Periksa Mikrofon');

    // Evaluasi Syarat Kelulusan Device Check
    let canContinue = false;

    if (DEVICE_CHECK_CONFIG.enableDualCamera) {
        // Dual Camera WAJIB: Cam 1 aktif, Cam 2 aktif, dan device ID berbeda
        canContinue = isCam1Ready && isCam2Ready && isDifferentDevices;
    } else {
        // Hanya kamera utama saja yang wajib
        canContinue = isCam1Ready;
    }

    // Update Tombol Continue
    const btnContinue = document.getElementById('btnContinueExam');
    if (btnContinue) {
        btnContinue.disabled = !canContinue;
        if (canContinue) {
            btnContinue.classList.remove('btn-secondary');
            btnContinue.classList.add('btn-success');
        } else {
            btnContinue.classList.add('btn-secondary');
            btnContinue.classList.remove('btn-success');
        }
    }
}

function updateChecklistItem(idSuffix, isReady, statusText) {
    const icon = document.getElementById(`checklist${idSuffix}Icon`);
    const text = document.getElementById(`checklist${idSuffix}Status`);
    if (!icon || !text) return;

    if (isReady) {
        icon.className = 'bi bi-check-circle-fill text-success';
        text.className = 'font-semibold text-success';
    } else {
        icon.className = 'bi bi-x-circle-fill text-danger';
        text.className = 'font-semibold text-danger';
    }
    text.textContent = statusText;
}

/**
 * 8. UPDATE UI STATUS BADGE & PLACEHOLDER
 */
function setCamStatus(camNumber, status, label) {
    if (camNumber === 1) mediaState.cam1Status = status;
    if (camNumber === 2) mediaState.cam2Status = status;

    const badge = document.getElementById(`cam${camNumber}StatusBadge`);
    const connText = document.getElementById(`cam${camNumber}ConnText`);

    if (!badge || !connText) return;

    badge.className = 'badge rounded-pill px-3 py-1.5 text-xs font-semibold';
    if (status === 'active') {
        badge.classList.add('bg-success', 'text-white');
        badge.innerHTML = '<i class="bi bi-check2-circle me-1"></i> ' + label;
        connText.className = 'font-semibold text-success';
        connText.textContent = label;
    } else if (status === 'disconnected') {
        badge.classList.add('bg-danger', 'text-white');
        badge.innerHTML = '<i class="bi bi-x-circle me-1"></i> ' + label;
        connText.className = 'font-semibold text-danger';
        connText.textContent = label;
    } else if (status === 'denied') {
        badge.classList.add('bg-danger', 'text-white');
        badge.innerHTML = '<i class="bi bi-shield-x me-1"></i> ' + label;
        connText.className = 'font-semibold text-danger';
        connText.textContent = label;
    } else {
        badge.classList.add('bg-secondary-subtle', 'text-secondary', 'border');
        badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> ' + label;
        connText.className = 'font-semibold text-secondary';
        connText.textContent = label;
    }
}

function setMicStatus(status, label) {
    mediaState.micStatus = status;
    const badge = document.getElementById('micStatusBadge');
    const permLabel = document.getElementById('micPermLabel');

    if (!badge) return;

    badge.className = 'badge rounded-pill px-3 py-1 text-xs font-semibold';
    if (status === 'active') {
        badge.classList.add('bg-success', 'text-white');
        badge.innerHTML = '<i class="bi bi-check2 me-1"></i> ' + label;
        if (permLabel) permLabel.className = 'text-success font-semibold';
    } else if (status === 'denied') {
        badge.classList.add('bg-danger', 'text-white');
        badge.innerHTML = '<i class="bi bi-x-circle me-1"></i> ' + label;
        if (permLabel) permLabel.className = 'text-danger font-semibold';
    } else {
        badge.classList.add('bg-secondary-subtle', 'text-secondary', 'border');
        badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> ' + label;
        if (permLabel) permLabel.className = 'text-muted font-semibold';
    }
    if (permLabel) permLabel.textContent = label;
}

function updatePlaceholder(camNumber, type, title, sub) {
    const ph = document.getElementById(`cam${camNumber}Placeholder`);
    const phText = document.getElementById(`cam${camNumber}PlaceholderText`);
    const phSub = document.getElementById(`cam${camNumber}PlaceholderSub`);
    const phIcon = document.getElementById(`cam${camNumber}PlaceholderIcon`);
    const livePill = document.getElementById(`cam${camNumber}LivePill`);
    const resPill = document.getElementById(`cam${camNumber}ResPill`);

    if (!ph) return;
    ph.classList.remove('d-none');
    if (livePill) livePill.classList.add('d-none');
    if (resPill) resPill.classList.add('d-none');

    if (phText) phText.textContent = title;
    if (phSub) phSub.textContent = sub;
}

/**
 * 9. NOTIFIKASI & PESAN KESALAHAN
 */
function showAlert(type, html) {
    const container = document.getElementById('deviceAlertContainer');
    if (!container) return;
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show text-xs p-3 shadow-sm rounded-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                ${html}
            </div>
            <button type="button" class="btn-close text-xs" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
}

function clearAlerts() {
    const container = document.getElementById('deviceAlertContainer');
    if (container) container.innerHTML = '';
}

function showToastNotice(msg, type = 'info') {
    console.log(`[Toast] ${type}: ${msg}`);
}

function handlePermissionError(err) {
    const msg = (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')
        ? '<strong>Izin Kamera / Mikrofon Ditolak:</strong> Silakan klik ikon gembok/kamera pada bilah alamat browser Anda dan ubah status izin menjadi <strong>Izinkan (Allow)</strong>, kemudian muat ulang halaman.'
        : `<strong>Gagal Mengakses Media:</strong> ${err.message || 'Perangkat tidak tersedia atau terhalang izin browser.'}`;

    showAlert('danger', msg);
    setCamStatus(1, 'denied', 'Izin Ditolak');
    setCamStatus(2, 'denied', 'Izin Ditolak');
    setMicStatus('denied', 'Izin Ditolak');
    validateContinueState();
}

/**
 * 10. AKSI TOMBOL TEST CAMERA & TRANSISI LANJUT KE UJIAN
 */
async function retestDevices() {
    const btn = document.getElementById('btnTestCamera');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menguji Ulang Kamera...';
    }

    cleanupStreams();
    await initDeviceCheck();

    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise fs-6"></i> <span>Uji Ulang Kamera / Test Camera</span>';
    }
}

/**
 * Simpan ID Perangkat ke Session Storage dan Navigasi
 */
function saveDevicePreferences() {
    if (mediaState.cam1SelectedId) {
        sessionStorage.setItem('smartexam_cam1_device_id', mediaState.cam1SelectedId);
    }
    if (mediaState.cam2SelectedId) {
        sessionStorage.setItem('smartexam_cam2_device_id', mediaState.cam2SelectedId);
    }
    sessionStorage.setItem('smartexam_devices_verified', '1');
    sessionStorage.setItem('smartexam_verified_time', Date.now().toString());
}

function proceedToAttempt() {
    saveDevicePreferences();
    cleanupStreams();
    window.location.href = DEVICE_CHECK_CONFIG.attemptUrl;
}

function handleStartSubmit(e) {
    const isCam1Ready = (mediaState.cam1Status === 'active');
    const isCam2Ready = (mediaState.cam2Status === 'active');
    const isDifferent = (mediaState.cam1SelectedId !== mediaState.cam2SelectedId);

    if (DEVICE_CHECK_CONFIG.enableDualCamera && (!isCam1Ready || !isCam2Ready || !isDifferent)) {
        e.preventDefault();
        showAlert('danger', '<strong>Gagal Melanjutkan:</strong> Ujian ini mewajibkan Kamera 1 dan Kamera 2 aktif menggunakan dua perangkat yang berbeda.');
        return false;
    }

    saveDevicePreferences();
    cleanupStreams();
    return true;
}

/**
 * 11. PEMBERSIHAN MEDIA STREAM (LIFECYCLE MANAGEMENT)
 */
function stopStreamFor(camNumber) {
    if (camNumber === 1 && mediaState.cam1Stream) {
        mediaState.cam1Stream.getTracks().forEach(t => {
            t.stop();
            console.log('[DeviceCheck] Track Cam 1 dihentikan.');
        });
        mediaState.cam1Stream = null;
    } else if (camNumber === 2 && mediaState.cam2Stream) {
        mediaState.cam2Stream.getTracks().forEach(t => {
            t.stop();
            console.log('[DeviceCheck] Track Cam 2 dihentikan.');
        });
        mediaState.cam2Stream = null;
    }
}

function stopMicStream() {
    if (mediaState.micStream) {
        mediaState.micStream.getTracks().forEach(t => t.stop());
        mediaState.micStream = null;
    }
    if (mediaState.audioAnimFrame) {
        cancelAnimationFrame(mediaState.audioAnimFrame);
        mediaState.audioAnimFrame = null;
    }
    if (mediaState.audioContext && mediaState.audioContext.state !== 'closed') {
        try { mediaState.audioContext.close(); } catch (e) {}
        mediaState.audioContext = null;
    }
}

function cleanupStreams() {
    stopStreamFor(1);
    stopStreamFor(2);
    stopMicStream();
    console.log('[DeviceCheck] Seluruh stream media lokal telah dibersihkan.');
}
</script>
