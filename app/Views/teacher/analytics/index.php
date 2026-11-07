<?php
use App\Helpers\CsrfHelper;
$currentUrl = current_url();
$myExams = $myExams ?? [];
$classes = $classes ?? [];
$subjects = $subjects ?? [];
$filters = $filters ?? [];
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
$distribution = $distribution ?? [];
$itemAnalysis = $itemAnalysis ?? [];
$proctoringStats = $proctoringStats ?? [];
?>

<!-- Header Dasbor Analytics -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 text-xs font-bold">
                <i class="bi bi-graph-up-arrow me-1"></i> Evaluasi Akademik & AI
            </span>
            <span class="badge bg-dark rounded-pill px-2.5 py-1 text-xs font-semibold">
                <i class="bi bi-shield-check me-1 text-warning"></i> Proctoring Intelligence
            </span>
        </div>
        <h3 class="font-bold text-dark mb-0">Analytics & Evaluasi Ujian</h3>
        <p class="text-muted text-xs mb-0 mt-0.5">
            Analisis capaian hasil belajar, distribusi nilai, analisis butir soal, dan evaluasi integritas pengawasan AI.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= base_url('teacher/monitoring') ?>" class="btn btn-outline-danger rounded-3 btn-sm px-3 shadow-sm font-semibold">
            <i class="bi bi-broadcast-pin me-1"></i> Live Monitoring
        </a>
        <a href="<?= base_url('teacher/reports') ?>" class="btn btn-primary rounded-3 btn-sm px-3 shadow-sm font-semibold">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Rekapitulasi Laporan
        </a>
    </div>
</div>

<!-- Toolbar Filter Terpadu -->
<div class="card-custom p-3.5 mb-4 shadow-sm">
    <form action="<?= base_url('teacher/analytics') ?>" method="GET" class="row g-2.5 align-items-center">
        <!-- Filter Paket Ujian -->
        <div class="col-12 col-md-3">
            <label class="form-label text-xs font-semibold text-secondary mb-1">Paket Ujian</label>
            <select name="exam_id" class="form-select form-select-sm rounded-2 font-medium">
                <option value="">-- Semua Paket Ujian --</option>
                <?php foreach ($myExams as $ex): ?>
                    <option value="<?= $ex['id'] ?>" <?= (!empty($filters['exam_id']) && (int)$filters['exam_id'] === (int)$ex['id']) ? 'selected' : '' ?>>
                        <?= e($ex['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Kelas -->
        <div class="col-6 col-md-2">
            <label class="form-label text-xs font-semibold text-secondary mb-1">Kelas</label>
            <select name="class_id" class="form-select form-select-sm rounded-2 font-medium">
                <option value="">-- Semua Kelas --</option>
                <?php foreach ($classes as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= (!empty($filters['class_id']) && (int)$filters['class_id'] === (int)$cl['id']) ? 'selected' : '' ?>>
                        <?= e($cl['class_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Mata Pelajaran -->
        <div class="col-6 col-md-2">
            <label class="form-label text-xs font-semibold text-secondary mb-1">Mata Pelajaran</label>
            <select name="subject_id" class="form-select form-select-sm rounded-2 font-medium">
                <option value="">-- Semua Mapel --</option>
                <?php foreach ($subjects as $sb): ?>
                    <option value="<?= $sb['id'] ?>" <?= (!empty($filters['subject_id']) && (int)$filters['subject_id'] === (int)$sb['id']) ? 'selected' : '' ?>>
                        <?= e($sb['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Tanggal Dari -->
        <div class="col-6 col-md-2">
            <label class="form-label text-xs font-semibold text-secondary mb-1">Tanggal Mulai</label>
            <input type="date" name="date_from" class="form-control form-control-sm rounded-2" value="<?= e($filters['date_from'] ?? '') ?>">
        </div>

        <!-- Filter Tanggal Sampai & Tombol Aksi -->
        <div class="col-6 col-md-3 d-flex align-items-end gap-2">
            <div class="flex-grow-1">
                <label class="form-label text-xs font-semibold text-secondary mb-1">Tanggal Akhir</label>
                <input type="date" name="date_to" class="form-control form-control-sm rounded-2" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" title="Terapkan Filter">
                <i class="bi bi-search"></i>
            </button>
            <a href="<?= base_url('teacher/analytics') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-2.5 shadow-sm" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- =========================================================================
     1. KPI STATISTIC CARDS (6 METRIK UTAMA)
     ========================================================================= -->
<div class="row g-3 mb-4">
    <!-- Jumlah Peserta -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-primary h-100 shadow-sm">
            <span class="text-xs text-muted d-block mb-1">Jumlah Peserta</span>
            <strong class="fs-4 font-mono text-dark d-block"><?= (int)$kpi['total_participants'] ?></strong>
            <small class="text-muted text-xs d-block mt-1">
                <span class="text-success font-semibold"><?= (int)$kpi['completed_count'] ?></span> selesai, 
                <span class="text-primary font-semibold"><?= (int)$kpi['ongoing_count'] ?></span> aktif
            </small>
        </div>
    </div>

    <!-- Rata-rata Nilai -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-info h-100 shadow-sm">
            <span class="text-xs text-muted d-block mb-1">Rata-Rata Nilai</span>
            <strong class="fs-4 font-mono text-info d-block"><?= number_format($kpi['avg_score'], 1) ?></strong>
            <small class="text-muted text-xs d-block mt-1">Skala 0 - 100</small>
        </div>
    </div>

    <!-- Nilai Tertinggi -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-success h-100 shadow-sm">
            <span class="text-xs text-muted d-block mb-1">Nilai Tertinggi</span>
            <strong class="fs-4 font-mono text-success d-block"><?= number_format($kpi['max_score'], 1) ?></strong>
            <small class="text-muted text-xs d-block mt-1">Skor puncak siswa</small>
        </div>
    </div>

    <!-- Nilai Terendah -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-danger h-100 shadow-sm">
            <span class="text-xs text-muted d-block mb-1">Nilai Terendah</span>
            <strong class="fs-4 font-mono text-danger d-block"><?= number_format($kpi['min_score'], 1) ?></strong>
            <small class="text-muted text-xs d-block mt-1">Batas minimum capaian</small>
        </div>
    </div>

    <!-- Persentase Kelulusan -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-warning h-100 shadow-sm">
            <span class="text-xs text-muted d-block mb-1">Persentase Lulus</span>
            <strong class="fs-4 font-mono text-warning d-block"><?= $kpi['passing_rate'] ?>%</strong>
            <small class="text-muted text-xs d-block mt-1">
                <span class="text-success font-semibold"><?= (int)$kpi['passed_count'] ?></span> lulus KKM
            </small>
        </div>
    </div>

    <!-- Total Proctoring Events -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom p-3 border-start border-4 border-dark h-100 shadow-sm">
            <span class="text-xs text-muted d-block mb-1">Insiden Proctoring</span>
            <strong class="fs-4 font-mono text-dark d-block"><?= (int)$kpi['total_events'] ?></strong>
            <small class="text-muted text-xs d-block mt-1">Total anomali AI</small>
        </div>
    </div>
</div>

<!-- =========================================================================
     2. DISTRIBUSI NILAI SISWA
     ========================================================================= -->
<div class="card-custom p-4 mb-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="font-bold text-dark mb-0">
                <i class="bi bi-bar-chart-steps text-primary me-2"></i>Distribusi Rentang Nilai Peserta
            </h6>
            <span class="text-muted text-xs">Pengelompokan hasil penilaian berdasarkan 5 bracket capaian mutu akademik</span>
        </div>
        <span class="badge bg-light text-dark border font-mono text-xs">
            Total Dinilai: <?= (int)$distribution['total'] ?> Siswa
        </span>
    </div>

    <div class="row g-3">
        <?php foreach ($distribution['ranges'] as $bracket): ?>
            <div class="col-12 col-md-6 col-xl">
                <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-xs font-bold text-dark"><?= e($bracket['short']) ?></span>
                            <span class="badge bg-<?= $bracket['color'] ?>-subtle text-<?= $bracket['color'] ?> border border-<?= $bracket['color'] ?>-subtle rounded-pill font-mono text-xs">
                                <?= $bracket['count'] ?> Siswa
                            </span>
                        </div>
                        <small class="text-muted text-xs d-block mb-2"><?= e($bracket['label']) ?></small>
                    </div>
                    <div>
                        <div class="progress rounded-pill mb-1" style="height: 8px;">
                            <div class="progress-bar bg-<?= $bracket['color'] ?>" style="width: <?= $bracket['percentage'] ?>%"></div>
                        </div>
                        <span class="font-mono text-xs text-muted d-block text-end"><?= $bracket['percentage'] ?>%</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- =========================================================================
     3. ANALISIS BUTIR SOAL (PALING BANYAK SALAH & PALING BANYAK BENAR)
     ========================================================================= -->
<div class="row g-4 mb-4">
    <!-- Soal Paling Banyak Salah -->
    <div class="col-12 col-lg-6">
        <div class="card-custom p-4 h-100 shadow-sm border-top border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="font-bold text-dark mb-0">
                        <i class="bi bi-x-circle-fill text-danger me-2"></i>Soal Paling Banyak Salah
                    </h6>
                    <span class="text-muted text-xs">Butir soal dengan tingkat kegagalan tertinggi (Prioritas Remedial)</span>
                </div>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 text-xs">Top 5 Sulit</span>
            </div>

            <?php if (empty($itemAnalysis['most_wrong'])): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check-circle fs-2 text-success d-block mb-1"></i>
                    <span class="text-xs">Belum ada data lembar jawaban untuk dianalisis.</span>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($itemAnalysis['most_wrong'] as $idx => $q): ?>
                        <div class="list-group-item px-0 py-2.5 border-bottom">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <span class="badge bg-dark rounded-pill px-2 py-0.5 text-xs font-mono">Soal #<?= $idx + 1 ?> (ID: <?= $q['question_id'] ?>)</span>
                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-0.5 font-mono text-xs">
                                    Salah: <?= $q['wrong_percentage'] ?>% (<?= $q['wrong_count'] ?>/<?= $q['total_answers'] ?>)
                                </span>
                            </div>
                            <p class="text-xs text-dark mb-1 font-medium leading-relaxed"><?= e($q['question_text']) ?></p>
                            <small class="text-muted text-xs">Bank Soal: <em><?= e($q['bank_title']) ?></em> • Tipe: <?= strtoupper($q['question_type']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Soal Paling Banyak Benar -->
    <div class="col-12 col-lg-6">
        <div class="card-custom p-4 h-100 shadow-sm border-top border-4 border-success">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="font-bold text-dark mb-0">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>Soal Paling Banyak Benar
                    </h6>
                    <span class="text-muted text-xs">Butir soal yang paling dikuasai peserta ujian</span>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs">Top 5 Dikuasai</span>
            </div>

            <?php if (empty($itemAnalysis['most_correct'])): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-info-circle fs-2 text-primary d-block mb-1"></i>
                    <span class="text-xs">Belum ada data lembar jawaban untuk dianalisis.</span>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($itemAnalysis['most_correct'] as $idx => $q): ?>
                        <div class="list-group-item px-0 py-2.5 border-bottom">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <span class="badge bg-dark rounded-pill px-2 py-0.5 text-xs font-mono">Soal #<?= $idx + 1 ?> (ID: <?= $q['question_id'] ?>)</span>
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-0.5 font-mono text-xs">
                                    Benar: <?= $q['correct_percentage'] ?>% (<?= $q['correct_count'] ?>/<?= $q['total_answers'] ?>)
                                </span>
                            </div>
                            <p class="text-xs text-dark mb-1 font-medium leading-relaxed"><?= e($q['question_text']) ?></p>
                            <small class="text-muted text-xs">Bank Soal: <em><?= e($q['bank_title']) ?></em> • Tipe: <?= strtoupper($q['question_type']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- =========================================================================
     4. PROCTORING ANALYTICS (BREAKDOWN JENIS, KAMERA, CONFIDENCE, PESERTA)
     ========================================================================= -->
<div class="card-custom p-4 mb-4 shadow-sm border-top border-4 border-dark">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h6 class="font-bold text-dark mb-0">
                <i class="bi bi-cpu-fill text-warning me-2"></i>Proctoring Intelligence & Anomaly Analytics
            </h6>
            <span class="text-muted text-xs">Pemetaan anomali pengawasan AI untuk menjaga objektivitas dan integritas ujian</span>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-3 py-1 text-xs font-bold">
                <i class="bi bi-hourglass-split me-1"></i> Belum Direview: <?= (int)$proctoringStats['unreviewed_count'] ?> Event
            </span>
            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 text-xs font-bold">
                Rata-rata Confidence: <?= $proctoringStats['avg_confidence'] ?>%
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Breakdown Berdasarkan Jenis Anomali -->
        <div class="col-12 col-lg-4">
            <div class="p-3 bg-light rounded-3 border h-100">
                <span class="text-xs font-bold text-dark text-uppercase tracking-wider d-block mb-3">
                    <i class="bi bi-diagram-3-fill text-primary me-1"></i> Berdasarkan Jenis Event
                </span>
                <?php if (empty($proctoringStats['by_type'])): ?>
                    <p class="text-muted text-xs mb-0">Tidak ada event tercatat.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2.5">
                        <?php foreach ($proctoringStats['by_type'] as $t): ?>
                            <div>
                                <div class="d-flex justify-content-between text-xs mb-1 font-semibold">
                                    <span class="text-dark"><?= e($t['type']) ?></span>
                                    <span class="font-mono text-muted"><?= $t['count'] ?> (<?= $t['percentage'] ?>%)</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-dark" style="width: <?= $t['percentage'] ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Breakdown Berdasarkan Kamera & Confidence -->
        <div class="col-12 col-lg-4">
            <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between gap-3">
                <!-- Berdasarkan Kamera -->
                <div>
                    <span class="text-xs font-bold text-dark text-uppercase tracking-wider d-block mb-2">
                        <i class="bi bi-camera-video me-1 text-info"></i> Berdasarkan Sumber Kamera
                    </span>
                    <?php if (empty($proctoringStats['by_camera'])): ?>
                        <p class="text-muted text-xs mb-0">Tidak ada data kamera.</p>
                    <?php else: ?>
                        <?php foreach ($proctoringStats['by_camera'] as $c): ?>
                            <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom text-xs">
                                <span class="text-dark font-medium"><?= e($c['label']) ?></span>
                                <span class="badge bg-white text-dark border font-mono"><?= $c['count'] ?> event (<?= $c['percentage'] ?>%)</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Berdasarkan Confidence -->
                <div>
                    <span class="text-xs font-bold text-dark text-uppercase tracking-wider d-block mb-2">
                        <i class="bi bi-speedometer2 me-1 text-warning"></i> Tingkat Keyakinan AI (Confidence)
                    </span>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($proctoringStats['by_confidence'] as $conf): ?>
                            <div>
                                <div class="d-flex justify-content-between text-xs mb-1 font-semibold">
                                    <span class="text-dark"><?= e($conf['label']) ?></span>
                                    <span class="font-mono text-muted"><?= $conf['count'] ?> event</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-<?= $conf['color'] ?>" style="width: <?= $conf['percentage'] ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard Anomali Berdasarkan Peserta -->
        <div class="col-12 col-lg-4">
            <div class="p-3 bg-light rounded-3 border h-100">
                <span class="text-xs font-bold text-dark text-uppercase tracking-wider d-block mb-2.5">
                    <i class="bi bi-person-exclamation me-1 text-danger"></i> Peserta Paling Banyak Event
                </span>
                <?php if (empty($proctoringStats['by_student'])): ?>
                    <p class="text-muted text-xs mb-0">Tidak ada peserta dengan anomali pengawasan.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0 text-xs">
                            <thead>
                                <tr class="text-muted border-bottom">
                                    <th>Peserta</th>
                                    <th>Kelas</th>
                                    <th class="text-end">Events</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proctoringStats['by_student'] as $st): ?>
                                    <tr class="border-bottom">
                                        <td>
                                            <strong class="d-block text-dark text-truncate" style="max-width: 130px;" title="<?= e($st['student_name']) ?>">
                                                <?= e($st['student_name']) ?>
                                            </strong>
                                            <small class="text-muted font-mono"><?= e($st['nisn']) ?></small>
                                        </td>
                                        <td><span class="badge bg-secondary-subtle text-secondary"><?= e($st['class_name']) ?></span></td>
                                        <td class="text-end">
                                            <span class="badge bg-dark rounded-pill px-2 py-0.5 font-mono">
                                                <?= (int)$st['event_count'] ?> Event
                                            </span>
                                            <?php if ((int)$st['unreviewed_count'] > 0): ?>
                                                <small class="d-block text-warning font-semibold mt-0.5" style="font-size: 10px;">
                                                    <?= $st['unreviewed_count'] ?> review
                                                </small>
                                            <?php endif; ?>
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
</div>
