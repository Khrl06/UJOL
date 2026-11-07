<?php
use App\Helpers\CsrfHelper;
$participant = array_merge([
    'subject_name' => '',
    'subject_code' => '',
    'class_name' => '-',
    'exam_title' => '',
    'duration_minutes' => 0,
    'total_questions' => 0,
    'passing_grade' => 75,
    'enable_dual_camera' => 0,
], $participant ?? []);
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary rounded-pill px-3 py-1 text-xs font-semibold">
                <i class="bi bi-journal-bookmark me-1"></i><?= e($participant['subject_name']) ?> (<?= e($participant['subject_code']) ?>)
            </span>
            <span class="badge bg-secondary rounded-pill px-2.5 py-1 text-xs">
                Kelas: <?= e($participant['class_name']) ?>
            </span>
        </div>
        <h3 class="font-bold text-dark mb-0"><?= e($participant['exam_title']) ?></h3>
    </div>
    <div>
        <a href="<?= base_url('student/exams') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Ujian
        </a>
    </div>
</div>

<div class="row g-4 justify-content-center mb-4">
    <!-- Kolom Kiri: Spesifikasi & Instruksi -->
    <div class="col-12 col-lg-7">
        <div class="card-custom p-4 mb-4">
            <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                <i class="bi bi-info-circle-fill text-primary me-2"></i>Spesifikasi Pelaksanaan Ujian
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3 text-center">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-stopwatch fs-3 text-primary d-block mb-1"></i>
                        <span class="text-muted text-xs d-block">Durasi Waktu</span>
                        <strong class="text-dark font-mono"><?= $participant['duration_minutes'] ?> Menit</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-list-check fs-3 text-info d-block mb-1"></i>
                        <span class="text-muted text-xs d-block">Jumlah Soal</span>
                        <strong class="text-dark font-mono"><?= $participant['total_questions'] ?> Butir</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-award fs-3 text-warning d-block mb-1"></i>
                        <span class="text-muted text-xs d-block">Standar KKM</span>
                        <strong class="text-dark font-mono"><?= $participant['passing_grade'] ?></strong>
                    </div>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-shield-check fs-3 text-success d-block mb-1"></i>
                        <span class="text-muted text-xs d-block">Pengawasan</span>
                        <strong class="text-dark text-xs"><?= !empty($participant['enable_dual_camera']) ? 'Dual Camera + AI' : 'AI Aktif' ?></strong>
                    </div>
                </div>
            </div>

            <h6 class="font-bold text-dark mb-2">
                <i class="bi bi-card-checklist text-secondary me-1"></i>Petunjuk & Tata Tertib Peserta:
            </h6>
            <div class="p-3 bg-light rounded-3 border text-xs text-muted mb-0">
                <ol class="mb-0 ps-3 d-flex flex-column gap-1.5">
                    <li>Pastikan perangkat Anda terhubung ke jaringan internet yang stabil dan memiliki daya baterai yang cukup.</li>
                    <?php if (!empty($participant['enable_dual_camera'])): ?>
                        <li class="text-dark font-semibold"><i class="bi bi-camera-video text-warning me-1"></i>Ujian ini mewajibkan <strong>Dual Camera</strong> (Kamera 1 menghadap wajah, Kamera 2 menghadap meja kerja/perangkat) serta mikrofon aktif.</li>
                    <?php endif; ?>
                    <li>Hitung mundur waktu ujian akan otomatis berjalan sejak Anda menekan tombol <strong>Mulai Mengerjakan Ujian</strong>.</li>
                    <li>Jawaban Anda tersimpan secara otomatis (*autosave*) setiap kali Anda memilih atau mengubah opsi jawaban.</li>
                    <li>Gunakan tombol <strong>Ragu-Ragu</strong> untuk menandai soal yang ingin Anda tinjau kembali sebelum submit.</li>
                    <li>Dilarang membuka tab browser lain, berpindah aplikasi, atau melakukan tindakan kecurangan lainnya.</li>
                    <li>Jika waktu pengerjaan habis, lembar jawaban Anda akan otomatis dikumpulkan dan dinilai oleh sistem (*auto-submit*).</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Verifikasi Token & Konfirmasi Mulai -->
    <div class="col-12 col-lg-5">
        <div class="card-custom p-4 border-top border-4 border-success">
            <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                <i class="bi bi-key-fill text-warning me-2"></i>Konfirmasi & Token Masuk
            </h5>

            <?php if (!empty($participant['enable_dual_camera'])): ?>
                <!-- Informasi & Shortcut Gateway Device Check untuk Ujian Dual Camera -->
                <div class="p-3 rounded-3 bg-primary-subtle border border-primary-subtle text-xs text-dark mb-3">
                    <strong class="d-block mb-1 text-primary"><i class="bi bi-camera-video-fill me-1"></i>Pemeriksaan Perangkat Diperlukan:</strong>
                    Ujian ini menerapkan sistem pengawasan kamera ganda. Anda disarankan melakukan uji coba kamera dan mikrofon terlebih dahulu agar tidak terkendala saat waktu ujian berjalan.
                </div>

                <div class="d-grid mb-3">
                    <a href="<?= base_url('student/exams/' . $participant['exam_id'] . '/device-check') ?>" class="btn btn-primary py-2.5 rounded-3 font-bold text-sm shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-camera-video-fill fs-6"></i>
                        <span>Pemeriksaan Perangkat (Device Check)</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="text-center text-xs text-muted mb-3">
                    <span>— ATAU LANGSUNG MASUKKAN TOKEN DI BAWAH —</span>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('student/exams/' . $participant['exam_id'] . '/start') ?>" method="POST" id="formStartExam">
                <?= CsrfHelper::tokenField() ?>

                <div class="mb-3">
                    <label class="form-label text-xs font-semibold text-secondary">Token Ujian (6 Karakter) *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-key-fill text-warning"></i></span>
                        <input type="text" name="token" class="form-control form-control-lg font-mono text-uppercase font-bold tracking-widest text-center" 
                               placeholder="Contoh: 2NJ4XV" maxlength="10" required autofocus autocomplete="off">
                    </div>
                    <small class="text-muted text-xs d-block mt-1">
                        Mintalah token ujian kepada pengawas ruang atau guru mata pelajaran Anda.
                    </small>
                </div>

                <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle text-xs text-dark mb-4">
                    <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>Peringatan Penting:</strong>
                    Setelah menekan tombol di bawah, sesi attempt akan langsung dicatat di server dan durasi waktu tidak dapat dihentikan.
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success py-3 rounded-3 font-bold fs-6 shadow d-flex align-items-center justify-content-center gap-2" onclick="return confirm('Apakah Anda sudah siap memulai pengerjaan ujian sekarang?')">
                        <i class="bi bi-play-circle-fill fs-5"></i>
                        <span>Mulai Mengerjakan Ujian</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
