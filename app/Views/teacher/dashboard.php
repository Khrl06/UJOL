<?php
$currentUser = $user ?? [];
$kpi = array_merge([
    'total_participants' => 0,
    'completed_count'    => 0,
    'ongoing_count'      => 0,
    'avg_score'          => 0.0,
    'max_score'          => 0.0,
    'min_score'          => 0.0,
    'passed_count'       => 0,
    'failed_count'       => 0,
    'passing_rate'       => 0.0,
    'total_events'       => 0
], $kpi ?? []);
$distribution = $distribution ?? ['total' => 0, 'ranges' => []];
$itemAnalysis = $itemAnalysis ?? ['most_wrong' => [], 'most_correct' => []];
$proctoringStats = $proctoringStats ?? [
    'total_events'      => 0,
    'unreviewed_count'  => 0,
    'avg_confidence'    => 0.0,
    'by_type'           => [],
    'by_camera'         => [],
    'by_confidence'     => [],
    'by_student'        => []
];
?>

<!-- Welcome Banner Guru -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card-custom p-4 bg-primary text-white position-relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
            <div class="row align-items-center position-relative" style="z-index: 2;">
                <div class="col-12 col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 text-xs font-bold shadow-sm">
                            <i class="bi bi-person-badge-fill me-1"></i> Panel Pendidik
                        </span>
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-2.5 py-1 text-xs font-semibold">
                            SmartExam AI v<?= APP_VERSION ?>
                        </span>
                    </div>
                    <h3 class="font-bold mb-1">Selamat Datang, <?= e($currentUser['name'] ?? 'Bapak/Ibu Guru') ?>!</h3>
                    <p class="text-white-50 text-sm mb-0">
                        Pantau capaian evaluasi belajar siswa, statistik butir soal, serta pengawasan integritas ujian berbasis AI secara real-time.
                    </p>
                </div>
                <div class="col-12 col-lg-4 mt-3 mt-lg-0 text-lg-end">
                    <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
                        <a href="<?= base_url('teacher/profile') ?>" class="btn btn-light rounded-3 btn-sm px-3 shadow-sm font-semibold text-primary">
                            <i class="bi bi-person-badge-fill me-1"></i> Profil Guru
                        </a>
                        <a href="<?= base_url('teacher/analytics') ?>" class="btn btn-outline-light rounded-3 btn-sm px-3 shadow-sm font-semibold">
                            <i class="bi bi-graph-up-arrow me-1"></i> Analytics Lengkap
                        </a>
                        <a href="<?= base_url('teacher/reports') ?>" class="btn btn-outline-light rounded-3 btn-sm px-3 shadow-sm font-semibold">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Rekap Laporan
                        </a>
                    </div>
                </div>
            </div>
            <div class="position-absolute end-0 top-0 translate-middle-y opacity-10" style="font-size: 15rem; margin-right: -2rem; z-index: 1;">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     1. METRIK KPI ANALYTICS DASHBOARD GURU (6 KARTU STATISTIK UTAMA)
     ========================================================================= -->
<div class="row g-3 mb-4">
    <!-- Jumlah Peserta -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-primary h-100 shadow-sm">
            <span class="text-xs text-muted text-uppercase font-bold d-block mb-1">Jumlah Peserta</span>
            <strong class="fs-4 font-mono text-dark d-block"><?= number_format($kpi['total_participants']) ?></strong>
            <small class="text-muted text-xs d-block mt-1">
                <span class="text-success font-semibold"><?= (int)$kpi['completed_count'] ?></span> selesai, 
                <span class="text-primary font-semibold"><?= (int)$kpi['ongoing_count'] ?></span> aktif
            </small>
        </div>
    </div>

    <!-- Rata-rata Nilai -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-info h-100 shadow-sm">
            <span class="text-xs text-muted text-uppercase font-bold d-block mb-1">Rata-Rata Nilai</span>
            <strong class="fs-4 font-mono text-info d-block"><?= number_format($kpi['avg_score'], 1) ?></strong>
            <small class="text-muted text-xs d-block mt-1">Skala 0 - 100</small>
        </div>
    </div>

    <!-- Nilai Tertinggi -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-success h-100 shadow-sm">
            <span class="text-xs text-muted text-uppercase font-bold d-block mb-1">Nilai Tertinggi</span>
            <strong class="fs-4 font-mono text-success d-block"><?= number_format($kpi['max_score'], 1) ?></strong>
            <small class="text-muted text-xs d-block mt-1">Skor puncak siswa</small>
        </div>
    </div>

    <!-- Nilai Terendah -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-danger h-100 shadow-sm">
            <span class="text-xs text-muted text-uppercase font-bold d-block mb-1">Nilai Terendah</span>
            <strong class="fs-4 font-mono text-danger d-block"><?= number_format($kpi['min_score'], 1) ?></strong>
            <small class="text-muted text-xs d-block mt-1">Batas minimum capaian</small>
        </div>
    </div>

    <!-- Persentase Kelulusan -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-warning h-100 shadow-sm">
            <span class="text-xs text-muted text-uppercase font-bold d-block mb-1">Persentase Lulus</span>
            <strong class="fs-4 font-mono text-warning d-block"><?= $kpi['passing_rate'] ?>%</strong>
            <small class="text-muted text-xs d-block mt-1">
                <span class="text-success font-semibold"><?= (int)$kpi['passed_count'] ?></span> lulus KKM
            </small>
        </div>
    </div>

    <!-- Jumlah Proctoring Event -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-dark h-100 shadow-sm">
            <span class="text-xs text-muted text-uppercase font-bold d-block mb-1">Proctoring Event</span>
            <strong class="fs-4 font-mono text-dark d-block"><?= number_format($kpi['total_events']) ?></strong>
            <small class="text-muted text-xs d-block mt-1">
                <span class="text-danger font-semibold"><?= (int)$proctoringStats['unreviewed_count'] ?></span> perlu review
            </small>
        </div>
    </div>
</div>

<!-- =========================================================================
     2. DISTRIBUSI NILAI & PROCTORING AI OVERVIEW
     ========================================================================= -->
<div class="row g-4 mb-4">
    <!-- Distribusi Nilai Siswa -->
    <div class="col-12 col-xl-6">
        <div class="card-custom p-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="font-bold text-dark mb-0">
                        <i class="bi bi-bar-chart-steps text-primary me-2"></i>Distribusi Nilai Siswa
                    </h6>
                    <small class="text-muted text-xs">Sebaran capaian nilai dari <?= number_format($distribution['total']) ?> peserta ternilai</small>
                </div>
                <a href="<?= base_url('teacher/analytics') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5 text-xs">
                    Detail &rarr;
                </a>
            </div>

            <?php if (empty($distribution['ranges'])): ?>
                <div class="text-center py-4 text-muted text-sm">Belum ada data nilai ujian yang diserahkan.</div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($distribution['ranges'] as $dist): ?>
                        <div>
                            <div class="d-flex justify-content-between text-xs mb-1 font-semibold">
                                <span class="text-dark"><?= e($dist['label']) ?></span>
                                <span class="font-mono text-muted">
                                    <strong><?= $dist['count'] ?></strong> siswa (<?= $dist['percentage'] ?>%)
                                </span>
                            </div>
                            <div class="progress rounded-pill shadow-none" style="height: 9px; background-color: #f1f5f9;">
                                <div class="progress-bar bg-<?= $dist['color'] ?>" 
                                     role="progressbar" 
                                     style="width: <?= $dist['percentage'] ?>%" 
                                     aria-valuenow="<?= $dist['percentage'] ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ringkasan AI Proctoring -->
    <div class="col-12 col-xl-6">
        <div class="card-custom p-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="font-bold text-dark mb-0">
                        <i class="bi bi-shield-lock-fill text-warning me-2"></i>Integritas & AI Proctoring
                    </h6>
                    <small class="text-muted text-xs">Ringkasan deteksi anomali kamera ganda dan sistem</small>
                </div>
                <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2.5 py-1 text-xs">
                    <?= (int)$proctoringStats['unreviewed_count'] ?> Belum Direview
                </span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="p-2.5 bg-light rounded-3 text-center border">
                        <small class="text-muted text-xs d-block">Total Event</small>
                        <strong class="fs-5 font-mono text-dark"><?= number_format($proctoringStats['total_events']) ?></strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2.5 bg-light rounded-3 text-center border">
                        <small class="text-muted text-xs d-block">Avg Confidence</small>
                        <strong class="fs-5 font-mono text-info"><?= $proctoringStats['avg_confidence'] ?>%</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2.5 bg-light rounded-3 text-center border">
                        <small class="text-muted text-xs d-block">Peserta Anomali</small>
                        <strong class="fs-5 font-mono text-success"><?= count($proctoringStats['by_student']) ?> Siswa</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <span class="text-xs font-bold text-secondary text-uppercase">Jenis Anomali Teratas:</span>
                <?php if (empty($proctoringStats['by_type'])): ?>
                    <small class="text-muted text-xs">Tidak ada event pelanggaran tercatat.</small>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-1.5">
                        <?php foreach (array_slice($proctoringStats['by_type'], 0, 4) as $t): ?>
                            <span class="badge bg-light text-dark border px-2.5 py-1.5 text-xs font-medium">
                                <?= e($t['type']) ?>: <strong class="text-danger"><?= $t['count'] ?></strong> (<?= $t['percentage'] ?>%)
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted text-xs">Pengawasan dual cam (wajah & meja kerja)</small>
                <a href="<?= base_url('teacher/monitoring') ?>" class="text-xs text-primary font-semibold text-decoration-none">
                    Buka Live Monitoring &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     3. ANALISIS BUTIR SOAL (SOAL PALING BANYAK SALAH & PALING BANYAK BENAR)
     ========================================================================= -->
<div class="row g-4 mb-4">
    <!-- Soal Paling Banyak Salah -->
    <div class="col-12 col-xl-6">
        <div class="card-custom p-4 h-100 shadow-sm border-top border-3 border-danger">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="font-bold text-danger mb-0">
                        <i class="bi bi-x-circle-fill me-2"></i>Soal Paling Banyak Salah
                    </h6>
                    <small class="text-muted text-xs">Butir soal dengan rasio kegagalan tertinggi (perlu remedial/ulasan)</small>
                </div>
                <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 text-xs font-bold">Top 5</span>
            </div>

            <?php if (empty($itemAnalysis['most_wrong'])): ?>
                <div class="text-center py-4 text-muted text-sm">
                    <i class="bi bi-check2-circle fs-3 d-block text-success mb-1"></i>
                    Belum ada data jawaban salah pada evaluasi aktif.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-xs">
                        <thead class="table-light text-muted">
                            <tr>
                                <th>Konten Soal</th>
                                <th>Bank Soal</th>
                                <th class="text-center">Salah</th>
                                <th class="text-end">Rasio Salah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemAnalysis['most_wrong'] as $idx => $item): ?>
                                <tr>
                                    <td>
                                        <div class="font-semibold text-dark text-truncate" style="max-width: 220px;" title="<?= e($item['question_text']) ?>">
                                            #<?= $idx + 1 ?>. <?= e($item['question_text']) ?>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">
                                            <?= strtoupper($item['question_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted text-truncate d-block" style="max-width: 120px;" title="<?= e($item['bank_title']) ?>">
                                            <?= e($item['bank_title']) ?>
                                        </small>
                                    </td>
                                    <td class="text-center font-mono">
                                        <span class="text-danger font-semibold"><?= $item['wrong_count'] ?></span> / <?= $item['total_answers'] ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-danger rounded-pill px-2 py-1 font-mono">
                                            <?= $item['wrong_percentage'] ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Soal Paling Banyak Benar -->
    <div class="col-12 col-xl-6">
        <div class="card-custom p-4 h-100 shadow-sm border-top border-3 border-success">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="font-bold text-success mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i>Soal Paling Banyak Benar
                    </h6>
                    <small class="text-muted text-xs">Butir soal dengan tingkat penguasaan konsep tertinggi</small>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 text-xs font-bold">Top 5</span>
            </div>

            <?php if (empty($itemAnalysis['most_correct'])): ?>
                <div class="text-center py-4 text-muted text-sm">
                    <i class="bi bi-info-circle fs-3 d-block text-secondary mb-1"></i>
                    Belum ada data jawaban benar pada evaluasi aktif.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-xs">
                        <thead class="table-light text-muted">
                            <tr>
                                <th>Konten Soal</th>
                                <th>Bank Soal</th>
                                <th class="text-center">Benar</th>
                                <th class="text-end">Rasio Penguasaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemAnalysis['most_correct'] as $idx => $item): ?>
                                <tr>
                                    <td>
                                        <div class="font-semibold text-dark text-truncate" style="max-width: 220px;" title="<?= e($item['question_text']) ?>">
                                            #<?= $idx + 1 ?>. <?= e($item['question_text']) ?>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">
                                            <?= strtoupper($item['question_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted text-truncate d-block" style="max-width: 120px;" title="<?= e($item['bank_title']) ?>">
                                            <?= e($item['bank_title']) ?>
                                        </small>
                                    </td>
                                    <td class="text-center font-mono">
                                        <span class="text-success font-semibold"><?= $item['correct_count'] ?></span> / <?= $item['total_answers'] ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success rounded-pill px-2 py-1 font-mono">
                                            <?= $item['correct_percentage'] ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- =========================================================================
     4. JADWAL UJIAN TERJADWAL & AKSI CEPAT
     ========================================================================= -->
<div class="row g-4 mb-4">
    <!-- Upcoming Exams Table -->
    <div class="col-12 col-xl-8">
        <div class="card-custom p-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h6 class="font-bold text-dark mb-0">Paket Ujian yang Dikelola</h6>
                    <small class="text-muted text-xs">Daftar paket asesmen aktif dan terjadwal</small>
                </div>
                <a href="<?= base_url('teacher/exams/create') ?>" class="btn btn-primary btn-sm rounded-3 px-3 shadow-sm font-semibold">
                    <i class="bi bi-plus-circle me-1"></i> Buat Paket Ujian
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-sm">
                    <thead class="table-light text-xs text-uppercase text-muted">
                        <tr>
                            <th>Judul Ujian</th>
                            <th>Mata Pelajaran</th>
                            <th>Jadwal Pelaksanaan</th>
                            <th>Durasi & Soal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs">
                        <?php if (empty($upcomingExams)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada paket ujian yang dibuat.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($upcomingExams, 0, 5) as $exam): ?>
                                <tr>
                                    <td>
                                        <div class="font-semibold text-dark"><?= e($exam['title'] ?? '') ?></div>
                                        <span class="badge bg-light text-secondary border text-xs" style="font-size: 10px;">
                                            <i class="bi bi-webcam-fill text-warning me-1"></i>AI Dual Cam
                                        </span>
                                    </td>
                                    <td><span class="text-secondary font-medium"><?= e($exam['subject_name'] ?? '-') ?></span></td>
                                    <td>
                                        <div><?= e($exam['start_date'] ?? '-') ?></div>
                                        <small class="text-muted"><?= e($exam['end_date'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <div class="font-semibold"><?= (int)($exam['duration_minutes'] ?? 0) ?> Menit</div>
                                        <small class="text-muted"><?= (int)($exam['total_questions'] ?? 0) ?> Butir Soal</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">
                                            <?= strtoupper($exam['status'] ?? 'DRAFT') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-12 col-xl-4">
        <div class="card-custom p-4 mb-4 shadow-sm">
            <h6 class="font-bold text-dark mb-3">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Aksi Cepat Pendidik
            </h6>
            <div class="d-grid gap-2">
                <a href="<?= base_url('teacher/banks') ?>" class="btn btn-light text-start border rounded-3 p-2.5 text-sm d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-folder2-open text-primary me-2"></i>Kelola Bank Soal</span>
                    <i class="bi bi-chevron-right text-xs text-muted"></i>
                </a>
                <a href="<?= base_url('teacher/monitoring') ?>" class="btn btn-light text-start border rounded-3 p-2.5 text-sm d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-broadcast text-danger me-2"></i>Live Monitoring Ujian</span>
                    <i class="bi bi-chevron-right text-xs text-muted"></i>
                </a>
                <a href="<?= base_url('teacher/analytics') ?>" class="btn btn-light text-start border rounded-3 p-2.5 text-sm d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-graph-up text-info me-2"></i>Analisis Capaian Siswa</span>
                    <i class="bi bi-chevron-right text-xs text-muted"></i>
                </a>
                <a href="<?= base_url('teacher/reports') ?>" class="btn btn-light text-start border rounded-3 p-2.5 text-sm d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Unduh Rekap Laporan</span>
                    <i class="bi bi-chevron-right text-xs text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>
