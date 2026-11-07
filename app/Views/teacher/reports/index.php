<?php
use App\Helpers\CsrfHelper;
$reportType = $reportType ?? 'rekap_nilai';
$filters = $filters ?? [];
$queryString = http_build_query(array_merge($filters, ['type' => $reportType]));
$reportData = $reportData ?? [];
$myExams = $myExams ?? [];
$classes = $classes ?? [];
$subjects = $subjects ?? [];
?>

<!-- Header Laporan & Rekapitulasi -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 text-xs font-bold">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Modul Pelaporan Terpadu
            </span>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs font-semibold">
                <i class="bi bi-check2-all me-1"></i> Data Real-Time
            </span>
        </div>
        <h3 class="font-bold text-dark mb-0">Laporan & Rekapitulasi Ujian</h3>
        <p class="text-muted text-xs mb-0 mt-0.5">
            Unduh rekapitulasi nilai, presensi sesi peserta, evaluasi paket ujian, dan log insiden pengawasan proctoring.
        </p>
    </div>

    <!-- Tombol Aksi Ekspor: CSV & Cetak PDF -->
    <div class="d-flex gap-2 flex-wrap">
        <!-- Tombol Unduh CSV -->
        <a href="<?= base_url('teacher/reports/export?' . $queryString) ?>" class="btn btn-success rounded-3 btn-sm px-3 shadow-sm font-semibold d-inline-flex align-items-center gap-1.5">
            <i class="bi bi-filetype-csv fs-6"></i>
            <span>Ekspor CSV (Excel)</span>
        </a>

        <!-- Tombol Cetak Dokumen / PDF -->
        <a href="<?= base_url('teacher/reports/print?' . $queryString) ?>" target="_blank" class="btn btn-outline-dark rounded-3 btn-sm px-3 shadow-sm font-semibold d-inline-flex align-items-center gap-1.5">
            <i class="bi bi-printer-fill fs-6"></i>
            <span>Cetak Dokumen / PDF</span>
        </a>
    </div>
</div>

<!-- Navigasi Tab Laporan -->
<ul class="nav nav-pills nav-fill card-custom p-1.5 mb-4 shadow-sm bg-light border">
    <li class="nav-item">
        <a class="nav-link rounded-3 text-xs font-bold py-2 <?= ($reportType === 'rekap_nilai') ? 'active bg-primary shadow-sm text-white' : 'text-secondary' ?>" 
           href="<?= base_url('teacher/reports?' . http_build_query(array_merge($filters, ['type' => 'rekap_nilai']))) ?>">
            <i class="bi bi-award-fill me-1.5"></i> Rekap Nilai Siswa
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-3 text-xs font-bold py-2 <?= ($reportType === 'rekap_peserta') ? 'active bg-primary shadow-sm text-white' : 'text-secondary' ?>" 
           href="<?= base_url('teacher/reports?' . http_build_query(array_merge($filters, ['type' => 'rekap_peserta']))) ?>">
            <i class="bi bi-people-fill me-1.5"></i> Rekap Peserta & Sesi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-3 text-xs font-bold py-2 <?= ($reportType === 'rekap_ujian') ? 'active bg-primary shadow-sm text-white' : 'text-secondary' ?>" 
           href="<?= base_url('teacher/reports?' . http_build_query(array_merge($filters, ['type' => 'rekap_ujian']))) ?>">
            <i class="bi bi-journal-check me-1.5"></i> Rekap Paket Ujian
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-3 text-xs font-bold py-2 <?= ($reportType === 'rekap_proctoring') ? 'active bg-primary shadow-sm text-white' : 'text-secondary' ?>" 
           href="<?= base_url('teacher/reports?' . http_build_query(array_merge($filters, ['type' => 'rekap_proctoring']))) ?>">
            <i class="bi bi-camera-video-fill me-1.5"></i> Rekap Proctoring AI
        </a>
    </li>
</ul>

<!-- Toolbar Filter Laporan -->
<div class="card-custom p-3.5 mb-4 shadow-sm">
    <form action="<?= base_url('teacher/reports') ?>" method="GET" class="row g-2.5 align-items-center">
        <input type="hidden" name="type" value="<?= e($reportType) ?>">

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
            <a href="<?= base_url('teacher/reports?type=' . $reportType) ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-2.5 shadow-sm" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- =========================================================================
     TABEL DATA REKAPITULASI
     ========================================================================= -->
<div class="card-custom shadow-sm overflow-hidden border">
    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
        <span class="text-xs font-bold text-dark text-uppercase tracking-wider">
            <?php
                echo match ($reportType) {
                    'rekap_nilai'      => 'Tabel Rekapitulasi Nilai Peserta',
                    'rekap_peserta'    => 'Tabel Rekapitulasi Kehadiran & Sesi Peserta',
                    'rekap_ujian'      => 'Tabel Rekapitulasi Paket Ujian',
                    'rekap_proctoring' => 'Tabel Rekapitulasi Insiden AI Proctoring',
                    default            => 'Tabel Laporan'
                };
            ?>
        </span>
        <span class="badge bg-white text-dark border font-mono text-xs">
            Total Data: <?= count($reportData) ?> Baris
        </span>
    </div>

    <?php if (empty($reportData)): ?>
        <div class="p-5 text-center text-muted">
            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
            <strong class="d-block text-sm">Tidak Ada Data Ditemukan</strong>
            <span class="text-xs">Ubah parameter filter di atas atau pastikan siswa telah mengikuti ujian.</span>
        </div>
    <?php else: ?>

        <!-- 1. TABEL REKAP NILAI -->
        <?php if ($reportType === 'rekap_nilai'): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-xs">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Paket Ujian</th>
                            <th class="text-center">Total Soal</th>
                            <th class="text-center text-success">Benar</th>
                            <th class="text-center text-danger">Salah</th>
                            <th class="text-center text-secondary">Kosong</th>
                            <th class="text-end">Nilai Akhir</th>
                            <th class="text-center">KKM</th>
                            <th class="text-center">Status Kelulusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $idx => $r): ?>
                            <tr>
                                <td class="font-mono text-muted"><?= is_numeric($idx) ? $idx + 1 : 1 ?></td>
                                <td class="font-mono"><?= e($r['nisn']) ?></td>
                                <td><strong class="text-dark"><?= e($r['student_name']) ?></strong></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= e($r['class_name']) ?></span></td>
                                <td><?= e($r['exam_title']) ?></td>
                                <td class="text-center font-mono"><?= (int)$r['total_questions'] ?></td>
                                <td class="text-center font-mono font-bold text-success"><?= (int)$r['correct_answers'] ?></td>
                                <td class="text-center font-mono text-danger"><?= (int)$r['wrong_answers'] ?></td>
                                <td class="text-center font-mono text-muted"><?= (int)$r['unattempted'] ?></td>
                                <td class="text-end">
                                    <strong class="fs-6 font-mono <?= ($r['passing_status'] === 'LULUS') ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format((float)$r['final_score'], 1) ?>
                                    </strong>
                                </td>
                                <td class="text-center font-mono"><?= number_format((float)$r['passing_grade'], 1) ?></td>
                                <td class="text-center">
                                    <?php if ($r['passing_status'] === 'LULUS'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 font-bold">LULUS</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 font-bold">TIDAK LULUS</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <!-- 2. TABEL REKAP PESERTA -->
        <?php elseif ($reportType === 'rekap_peserta'): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-xs">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Paket Ujian</th>
                            <th>Status Sesi</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Selesai</th>
                            <th class="text-center">Durasi Pengerjaan</th>
                            <th>Alamat IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $idx => $r): ?>
                            <tr>
                                <td class="font-mono text-muted"><?= is_numeric($idx) ? $idx + 1 : 1 ?></td>
                                <td class="font-mono"><?= e($r['nisn']) ?></td>
                                <td><strong class="text-dark"><?= e($r['student_name']) ?></strong></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= e($r['class_name']) ?></span></td>
                                <td><?= e($r['exam_title']) ?></td>
                                <td>
                                    <?php if ($r['attempt_status'] === 'ongoing'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 font-semibold">Ongoing</span>
                                    <?php elseif ($r['attempt_status'] === 'submitted' || $r['attempt_status'] === 'graded'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5 font-semibold">Submitted</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border rounded-pill px-2.5 py-0.5">Registered</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-mono"><?= $r['start_time'] ? date('d/m/Y H:i:s', strtotime($r['start_time'])) : '-' ?></td>
                                <td class="font-mono"><?= $r['submit_time'] ? date('d/m/Y H:i:s', strtotime($r['submit_time'])) : '-' ?></td>
                                <td class="text-center font-mono font-semibold"><?= $r['duration_spent_minutes'] !== null ? $r['duration_spent_minutes'] . ' Menit' : '-' ?></td>
                                <td class="font-mono text-muted"><?= e($r['ip_address'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <!-- 3. TABEL REKAP UJIAN -->
        <?php elseif ($reportType === 'rekap_ujian'): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-xs">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th>ID</th>
                            <th>Judul Paket Ujian</th>
                            <th>Mata Pelajaran</th>
                            <th class="text-center">Total Soal</th>
                            <th class="text-center">KKM</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-center">Total Peserta</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-end">Rata-Rata Nilai</th>
                            <th class="text-center">Kelulusan (%)</th>
                            <th class="text-center">Status Publikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $idx => $r): ?>
                            <tr>
                                <td class="font-mono text-muted"><?= is_numeric($idx) ? $idx + 1 : 1 ?></td>
                                <td class="font-mono"><?= $r['exam_id'] ?></td>
                                <td><strong class="text-dark"><?= e($r['exam_title']) ?></strong></td>
                                <td><?= e($r['subject_name']) ?></td>
                                <td class="text-center font-mono"><?= (int)$r['total_questions'] ?></td>
                                <td class="text-center font-mono"><?= number_format((float)$r['passing_grade'], 1) ?></td>
                                <td class="text-center font-mono"><?= (int)$r['duration_minutes'] ?> Mnt</td>
                                <td class="text-center font-mono font-bold"><?= (int)$r['total_participants'] ?></td>
                                <td class="text-center font-mono text-success"><?= (int)$r['completed_count'] ?></td>
                                <td class="text-end font-mono font-bold text-primary"><?= number_format((float)$r['avg_score'], 1) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill px-2 py-0.5 font-mono font-bold">
                                        <?= $r['passing_percentage'] ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= ($r['publish_status'] === 'published') ? 'bg-success text-white' : 'bg-secondary text-white' ?> rounded-pill px-2.5 py-0.5">
                                        <?= strtoupper($r['publish_status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <!-- 4. TABEL REKAP PROCTORING -->
        <?php elseif ($reportType === 'rekap_proctoring'): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-xs">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th>Waktu Insiden</th>
                            <th>Nama Siswa & NISN</th>
                            <th>Kelas</th>
                            <th>Paket Ujian</th>
                            <th>Jenis Anomali</th>
                            <th class="text-center">Kamera</th>
                            <th class="text-center">Confidence AI</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-center">Status Review</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $idx => $r): ?>
                            <?php
                                $statusBadge = 'bg-secondary text-white';
                                if ($r['review_status'] === 'REVIEW') $statusBadge = 'bg-warning text-dark font-bold border border-warning';
                                elseif ($r['review_status'] === 'REVIEWED') $statusBadge = 'bg-danger text-white font-bold';
                                elseif ($r['review_status'] === 'DISMISSED') $statusBadge = 'bg-secondary text-white';
                            ?>
                            <tr>
                                <td class="font-mono text-muted"><?= is_numeric($idx) ? $idx + 1 : 1 ?></td>
                                <td class="font-mono"><?= date('d/m/Y H:i:s', strtotime($r['event_time'])) ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= e($r['student_name']) ?></strong>
                                    <small class="text-muted font-mono"><?= e($r['nisn']) ?></small>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= e($r['class_name']) ?></span></td>
                                <td><?= e($r['exam_title']) ?></td>
                                <td><span class="badge bg-dark rounded-pill px-2 py-0.5"><?= e($r['event_type']) ?></span></td>
                                <td class="text-center"><span class="badge bg-light text-dark border"><?= e($r['camera']) ?></span></td>
                                <td class="text-center font-mono font-bold text-danger"><?= number_format((float)$r['confidence_score'], 1) ?>%</td>
                                <td class="text-center font-mono"><?= (int)$r['duration'] ?> dtk</td>
                                <td class="text-center">
                                    <span class="badge <?= $statusBadge ?> rounded-pill px-2.5 py-0.5"><?= $r['review_status'] ?></span>
                                </td>
                                <td><small class="text-muted"><?= e($r['description'] ?? '-') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
