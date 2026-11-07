<!-- Welcome Banner -->
<div class="welcome-banner" style="background: linear-gradient(135deg, #064e3b 0%, #059669 50%, #10b981 100%);">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge badge-role-siswa rounded-pill px-3 py-1 text-xs">
                    <i class="bi bi-backpack me-1"></i>PESERTA DIDIK
                </span>
                <span class="badge bg-white text-success rounded-pill px-2.5 py-0.5 text-xs font-semibold">
                    <?= e($student['class_name'] ?? 'Siswa') ?>
                </span>
                <span class="text-white-50 text-xs">NISN: <?= e($student['nisn'] ?? '-') ?></span>
            </div>
            <h2 class="font-bold text-white mb-1">Semangat Belajar, <?= e($student['name'] ?? 'Peserta Didik') ?>!</h2>
            <p class="text-white-50 text-sm mb-0">Pastikan perangkat Anda dan koneksi internet siap sebelum memulai sesi ujian online.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('student/exams') ?>" class="btn btn-light btn-sm rounded-3 px-3 py-2 font-semibold text-success shadow-sm">
                <i class="bi bi-journal-check me-1"></i> Buka Daftar Ujian
            </a>
        </div>
    </div>
</div>

<!-- Student Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom card-stat">
            <div>
                <span class="text-muted text-xs text-uppercase font-bold">Ujian Tersedia</span>
                <h3 class="font-bold my-1 text-dark"><?= count($availableExams ?? []) ?></h3>
                <span class="text-primary text-xs font-semibold"><i class="bi bi-calendar-event me-1"></i>Wajib Diikuti</span>
            </div>
            <div class="icon-box icon-box-primary">
                <i class="bi bi-calendar2-check-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom card-stat">
            <div>
                <span class="text-muted text-xs text-uppercase font-bold">Sedang Berlangsung</span>
                <h3 class="font-bold my-1 text-warning"><?= count($ongoingExams ?? []) ?></h3>
                <span class="text-warning text-xs font-semibold"><i class="bi bi-broadcast me-1"></i>Sesi Aktif</span>
            </div>
            <div class="icon-box icon-box-warning">
                <i class="bi bi-stopwatch-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom card-stat">
            <div>
                <span class="text-muted text-xs text-uppercase font-bold">Ujian Selesai</span>
                <h3 class="font-bold my-1 text-dark"><?= count($completedExams ?? []) ?></h3>
                <span class="text-success text-xs font-semibold"><i class="bi bi-check2-circle me-1"></i>Tuntas Dikerjakan</span>
            </div>
            <div class="icon-box icon-box-success">
                <i class="bi bi-clipboard-check-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom card-stat">
            <div>
                <span class="text-muted text-xs text-uppercase font-bold">Rombel Kelas</span>
                <h4 class="font-bold my-1 text-dark"><?= e($student['class_name'] ?? 'XII-MIPA-1') ?></h4>
                <span class="text-info text-xs font-semibold"><i class="bi bi-mortarboard-fill me-1"></i>SMA Aktif</span>
            </div>
            <div class="icon-box icon-box-info">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Ongoing Exam Alert Banner (If Any) -->
<?php if (!empty($ongoingExams)): ?>
    <div class="alert alert-warning border-2 border-warning p-4 rounded-3 mb-4 shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h5 class="font-bold text-dark mb-1">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Anda Memiliki Sesi Ujian yang Sedang Berjalan!
                </h5>
                <p class="text-dark text-xs mb-0">
                    Ujian <strong><?= e($ongoingExams[0]['exam_title']) ?></strong> sedang aktif dan waktu terus berjalan. Silakan lanjutkan pengerjaan Anda.
                </p>
            </div>
            <div>
                <a href="<?= base_url('student/exams/' . $ongoingExams[0]['exam_id'] . '/attempt') ?>" class="btn btn-dark rounded-3 px-4 py-2 font-bold text-warning shadow">
                    Lanjutkan Ujian Sekarang <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Main Section: Scheduled Exams & Technical Checklist -->
<div class="row g-4 mb-4">
    <!-- Scheduled Exams List -->
    <div class="col-12 col-xl-8">
        <div class="card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="font-bold text-dark mb-0">Jadwal Ujian Tersedia</h5>
                    <small class="text-muted text-xs">Paket ujian online yang siap Anda kerjakan</small>
                </div>
                <a href="<?= base_url('student/exams') ?>" class="btn btn-outline-secondary btn-sm rounded-3 px-2.5 text-xs">
                    Lihat Semua
                </a>
            </div>

            <div class="d-flex flex-column gap-3">
                <?php if (!empty($availableExams)): ?>
                    <?php foreach (array_slice($availableExams, 0, 4) as $exam): ?>
                        <div class="border rounded-3 p-3 bg-white hover-shadow transition">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-primary-subtle text-primary font-semibold text-xs"><?= e($exam['subject_name']) ?></span>
                                        <?php if (!empty($exam['enable_ai_proctoring'])): ?>
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle text-xs">
                                                <i class="bi bi-robot text-warning me-1"></i>AI Proctoring
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h6 class="font-bold text-dark mb-1"><?= e($exam['exam_title']) ?></h6>
                                    <div class="text-muted text-xs d-flex flex-wrap align-items-center gap-3">
                                        <span><i class="bi bi-person-badge me-1"></i><?= e($exam['teacher_name']) ?></span>
                                        <span><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($exam['start_date'])) ?></span>
                                        <span><i class="bi bi-clock me-1"></i><?= $exam['duration_minutes'] ?> Menit</span>
                                    </div>
                                </div>
                                <div>
                                    <a href="<?= base_url('student/exams/' . $exam['exam_id']) ?>" class="btn btn-primary btn-sm rounded-3 px-3 py-2 font-semibold">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Ujian
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-check display-6 d-block mb-2 text-muted"></i>
                        Tidak ada ujian baru yang tersedia saat ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Technical Readiness & Requirements for Students -->
    <div class="col-12 col-xl-4">
        <div class="card-custom p-4 mb-4">
            <h6 class="font-bold text-dark mb-3">
                <i class="bi bi-shield-check text-success me-2"></i>Panduan Kesiapan Ujian
            </h6>
            <div class="d-flex flex-column gap-2 text-xs">
                <div class="d-flex align-items-start gap-2 p-2 rounded-3 bg-light border">
                    <i class="bi bi-check-circle-fill text-success mt-0.5"></i>
                    <div>
                        <strong class="d-block text-dark">Kamera & Perangkat</strong>
                        <span class="text-muted">Gunakan laptop/PC dan pastikan browser terupdate.</span>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2 p-2 rounded-3 bg-light border">
                    <i class="bi bi-check-circle-fill text-success mt-0.5"></i>
                    <div>
                        <strong class="d-block text-dark">Koneksi Internet</strong>
                        <span class="text-muted">Gunakan koneksi yang stabil minimal 4G/Wi-Fi.</span>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2 p-2 rounded-3 bg-light border">
                    <i class="bi bi-check-circle-fill text-success mt-0.5"></i>
                    <div>
                        <strong class="d-block text-dark">Autosave Otomatis</strong>
                        <span class="text-muted">Setiap jawaban Anda tersimpan otomatis di server.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Profile Card -->
        <div class="card-custom p-4">
            <h6 class="font-bold text-dark mb-3">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>Identitas Peserta Didik
            </h6>
            <ul class="list-group list-group-flush text-xs">
                <li class="list-group-item d-flex justify-content-between px-0 py-2 border-bottom">
                    <span class="text-muted">Nama Lengkap</span>
                    <span class="font-bold text-dark"><?= e($student['name'] ?? '-') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0 py-2 border-bottom">
                    <span class="text-muted">Kelas / Rombel</span>
                    <span class="font-semibold text-dark"><?= e($student['class_name'] ?? '-') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0 py-2 border-bottom">
                    <span class="text-muted">NISN</span>
                    <span class="font-semibold text-dark"><?= e($student['nisn'] ?? '-') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                    <span class="text-muted">Status Akun</span>
                    <span class="badge bg-success-subtle text-success font-semibold">Terverifikasi Aktif</span>
                </li>
            </ul>
            <div class="mt-3 pt-2 border-top">
                <a href="<?= base_url('student/profile') ?>" class="btn btn-outline-primary btn-sm w-100 rounded-3 text-xs font-semibold d-flex align-items-center justify-content-center gap-1.5">
                    <i class="bi bi-person-lines-fill"></i>
                    <span>Kelola Profil Saya</span>
                </a>
            </div>
        </div>
    </div>
</div>
