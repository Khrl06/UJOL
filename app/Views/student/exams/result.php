<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary rounded-pill px-3 py-1 text-xs font-semibold">
                <i class="bi bi-journal-bookmark me-1"></i><?= e($participant['subject_name']) ?> (<?= e($participant['subject_code']) ?>)
            </span>
            <span class="badge bg-success rounded-pill px-2.5 py-1 text-xs font-semibold">
                <i class="bi bi-check-circle-fill me-1"></i> Ujian Telah Selesai
            </span>
        </div>
        <h3 class="font-bold text-dark mb-0">Hasil & Lembar Evaluasi Asesmen</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('student/exams') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-journal-text me-1"></i> Daftar Ujian
        </a>
        <a href="<?= base_url('student/dashboard') ?>" class="btn btn-primary rounded-3 btn-sm px-3 font-semibold shadow-sm">
            <i class="bi bi-house-door-fill me-1"></i> Dasbor Siswa
        </a>
    </div>
</div>

<div class="row g-4 justify-content-center mb-4">
    <!-- Score & Graduation Card -->
    <div class="col-12 col-md-5 col-xl-4">
        <?php
            $isPassed = (int)($result['is_passed'] ?? 0) === 1;
            $scoreVal = (float)($result['final_score'] ?? $participant['score'] ?? 0.0);
            $passingGrade = (float)($participant['passing_grade'] ?? 75.00);
        ?>
        <div class="card-custom p-4 text-center h-100 d-flex flex-column justify-content-between border-top border-4 <?= $isPassed ? 'border-success' : 'border-warning' ?>">
            <div>
                <span class="text-xs font-bold text-secondary text-uppercase tracking-wider d-block mb-3">
                    Perolehan Nilai Akhir
                </span>

                <!-- Big Circular Score Display -->
                <div class="display-3 font-mono font-bold mb-2 <?= $isPassed ? 'text-success' : 'text-danger' ?>">
                    <?= number_format($scoreVal, 1) ?>
                </div>

                <div class="mb-3">
                    <?php if ($isPassed): ?>
                        <span class="badge bg-success rounded-pill px-4 py-2 text-sm font-bold shadow-sm">
                            <i class="bi bi-patch-check-fill me-1"></i> LULUS KKM
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark rounded-pill px-4 py-2 text-sm font-bold shadow-sm">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> REMEDIAL (Di Bawah KKM)
                        </span>
                    <?php endif; ?>
                </div>

                <p class="text-muted text-xs mb-0">
                    Standar Ketuntasan Minimal (KKM): <strong><?= number_format($passingGrade, 1) ?> Poin</strong>
                </p>
            </div>

            <div class="pt-3 mt-4 border-top text-xs text-muted">
                <i class="bi bi-clock-history me-1"></i> Diselesaikan pada <?= !empty($participant['submit_time']) ? date('d M Y, H:i', strtotime($participant['submit_time'])) : date('d M Y, H:i') ?> WIB
            </div>
        </div>
    </div>

    <!-- Detailed Statistics & Notes Card -->
    <div class="col-12 col-md-7 col-xl-8">
        <div class="card-custom p-4 h-100">
            <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                <i class="bi bi-bar-chart-fill text-primary me-2"></i>Rincian Hasil Pengerjaan
            </h5>

            <!-- Meta Exam Info -->
            <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="font-bold text-dark mb-1"><?= e($participant['exam_title']) ?></h6>
                <div class="row g-2 text-xs text-muted">
                    <div class="col-12 col-sm-6">
                        Nama Siswa: <strong class="text-dark"><?= e($participant['student_name']) ?></strong> (<?= e($participant['nisn']) ?>)
                    </div>
                    <div class="col-12 col-sm-6">
                        Rombel Kelas: <strong class="text-dark"><?= e($participant['class_name']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-3 mb-4 text-center">
                <div class="col-6 col-sm-3">
                    <div class="p-3 rounded-3 bg-light border">
                        <span class="text-xs text-muted d-block mb-1">Total Soal</span>
                        <strong class="fs-4 font-mono text-dark"><?= $result['total_questions'] ?? $participant['total_questions'] ?></strong>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle">
                        <span class="text-xs text-success font-semibold d-block mb-1">Jawaban Benar</span>
                        <strong class="fs-4 font-mono text-success"><?= $result['correct_answers'] ?? 0 ?></strong>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="p-3 rounded-3 bg-danger-subtle border border-danger-subtle">
                        <span class="text-xs text-danger font-semibold d-block mb-1">Jawaban Salah</span>
                        <strong class="fs-4 font-mono text-danger"><?= $result['wrong_answers'] ?? 0 ?></strong>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="p-3 rounded-3 bg-secondary-subtle border border-secondary-subtle">
                        <span class="text-xs text-secondary font-semibold d-block mb-1">Tidak Dijawab</span>
                        <strong class="fs-4 font-mono text-secondary"><?= $result['unattempted'] ?? 0 ?></strong>
                    </div>
                </div>
            </div>

            <!-- Notes & Advice -->
            <?php if (!empty($result['notes'])): ?>
                <div class="p-3 rounded-3 bg-info-subtle border border-info-subtle text-xs text-dark">
                    <strong class="text-info d-block mb-1"><i class="bi bi-info-circle-fill me-1"></i>Catatan Evaluasi:</strong>
                    <?= nl2br(e($result['notes'])) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Pembersihan Total Lifecycle Media Kamera & Mikrofon -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Bersihkan sessionStorage preferensi kamera ujian
    sessionStorage.removeItem('smartexam_cam1_device_id');
    sessionStorage.removeItem('smartexam_cam2_device_id');
    sessionStorage.removeItem('smartexam_devices_verified');

    // 2. Ambil dan hentikan paksa seluruh media track jika masih ada stream aktif
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(stream => {
                stream.getTracks().forEach(track => {
                    track.stop();
                    console.log('[Result] Track kamera/audio telah dihentikan secara tuntas.');
                });
            })
            .catch(() => {
                // Diabaikan jika kamera memang sudah off
            });
    }
});
</script>

