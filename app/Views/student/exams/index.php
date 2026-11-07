<?php
$activeTab = $activeTab ?? 'available';
$available = $available ?? [];
$ongoing = $ongoing ?? [];
$completed = $completed ?? [];
?>
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Jadwal & Riwayat Ujian Saya</h3>
        <p class="text-muted text-sm mb-0">Lihat paket ujian yang tersedia, lanjutkan pengerjaan yang sedang aktif, atau tinjau nilai evaluasi Anda.</p>
    </div>
    <div>
        <a href="<?= base_url('student/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Dasbor
        </a>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 gap-2" id="examTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= $activeTab === 'available' ? 'active' : '' ?> rounded-3 text-sm font-semibold px-4" 
           href="<?= base_url('student/exams?tab=available') ?>">
            <i class="bi bi-journal-check me-1"></i> Ujian Tersedia (<?= count($available) ?>)
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= $activeTab === 'ongoing' ? 'active bg-warning text-dark' : '' ?> rounded-3 text-sm font-semibold px-4" 
           href="<?= base_url('student/exams?tab=ongoing') ?>">
            <i class="bi bi-stopwatch-fill me-1"></i> Sedang Berlangsung (<?= count($ongoing) ?>)
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= $activeTab === 'completed' ? 'active' : '' ?> rounded-3 text-sm font-semibold px-4" 
           href="<?= base_url('student/exams?tab=completed') ?>">
            <i class="bi bi-clipboard-check-fill me-1"></i> Riwayat & Nilai (<?= count($completed) ?>)
        </a>
    </li>
</ul>

<!-- Tab 1: Ujian Tersedia -->
<?php if ($activeTab === 'available'): ?>
    <div class="row g-3 mb-4">
        <?php if (!empty($available)): ?>
            <?php foreach ($available as $e): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card-custom p-4 h-100 d-flex flex-column justify-content-between border-top border-4 border-primary">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary-subtle text-primary font-semibold text-xs rounded-pill px-2.5 py-1">
                                    <i class="bi bi-journal-bookmark me-1"></i><?= e($e['subject_name']) ?>
                                </span>
                                <span class="badge bg-success-subtle text-success rounded-pill text-xs">
                                    <i class="bi bi-clock me-1"></i><?= $e['duration_minutes'] ?> Menit
                                </span>
                            </div>

                            <h5 class="font-bold text-dark mb-1">
                                <a href="<?= base_url('student/exams/' . $e['exam_id']) ?>" class="text-dark text-decoration-none hover-primary">
                                    <?= e($e['exam_title']) ?>
                                </a>
                            </h5>

                            <p class="text-muted text-xs mb-3 line-clamp-2" style="min-height: 32px;">
                                <?= e($e['exam_description'] ?: 'Tidak ada instruksi khusus. Siapkan koneksi internet dan kamera.') ?>
                            </p>

                            <div class="p-2.5 rounded-3 bg-light border text-xs mb-3">
                                <div class="text-muted mb-0.5">Pengampu: <strong><?= e($e['teacher_name']) ?></strong></div>
                                <div class="text-dark">Batas: <?= date('d M Y', strtotime($e['end_date'])) ?> <?= substr($e['end_time'], 0, 5) ?> WIB</div>
                            </div>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="text-muted text-xs">KKM: <strong><?= $e['passing_grade'] ?></strong></span>
                            <a href="<?= base_url('student/exams/' . $e['exam_id']) ?>" class="btn btn-primary btn-sm rounded-3 px-3 font-semibold text-xs">
                                Masuk Ujian <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card-custom p-5 text-center">
                    <i class="bi bi-calendar-check display-6 text-muted d-block mb-3"></i>
                    <h5 class="font-bold text-dark mb-1">Tidak Ada Ujian Baru</h5>
                    <p class="text-muted text-sm mb-0">Semua paket ujian telah Anda selesaikan atau belum ada jadwal ujian baru yang dipublikasikan oleh guru.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Tab 2: Sedang Berlangsung -->
<?php if ($activeTab === 'ongoing'): ?>
    <div class="row g-3 mb-4">
        <?php if (!empty($ongoing)): ?>
            <?php foreach ($ongoing as $e): ?>
                <div class="col-12 col-md-6">
                    <div class="card-custom p-4 border-top border-4 border-warning bg-warning-subtle">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-warning text-dark font-bold text-xs rounded-pill px-3 py-1">
                                <i class="bi bi-broadcast me-1"></i> SEDANG BERLANGSUNG
                            </span>
                            <span class="badge bg-dark font-mono text-xs">
                                Durasi: <?= $e['duration_minutes'] ?> Menit
                            </span>
                        </div>

                        <h4 class="font-bold text-dark mb-1"><?= e($e['exam_title']) ?></h4>
                        <p class="text-dark text-xs mb-3">
                            Mata Pelajaran: <strong><?= e($e['subject_name']) ?></strong> | Pengampu: <?= e($e['teacher_name']) ?>
                        </p>

                        <div class="alert alert-warning text-xs mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Anda memiliki sesi ujian aktif yang belum dikumpulkan. Waktu terus berjalan!
                        </div>

                        <a href="<?= base_url('student/exams/' . $e['exam_id'] . '/attempt') ?>" class="btn btn-dark w-100 py-2.5 rounded-3 font-semibold text-warning d-flex justify-content-center align-items-center gap-2">
                            <i class="bi bi-arrow-right-circle-fill"></i>
                            <span>Lanjutkan Pengerjaan Ujian Sekarang</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card-custom p-5 text-center">
                    <i class="bi bi-check-circle display-6 text-success d-block mb-3"></i>
                    <h5 class="font-bold text-dark mb-1">Tidak Ada Ujian yang Sedang Berjalan</h5>
                    <p class="text-muted text-sm mb-0">Anda tidak memiliki sesi ujian aktif yang tertunda saat ini.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Tab 3: Riwayat Ujian -->
<?php if ($activeTab === 'completed'): ?>
    <div class="card-custom p-4 mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase text-muted">
                    <tr>
                        <th>Nama Paket Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th>Tanggal Selesai</th>
                        <th>Standar KKM</th>
                        <th>Nilai Perolehan</th>
                        <th>Status Kelulusan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (!empty($completed)): ?>
                        <?php foreach ($completed as $e): ?>
                            <tr>
                                <td>
                                    <div class="font-bold text-dark"><?= e($e['exam_title']) ?></div>
                                    <small class="text-muted text-xs">Guru: <?= e($e['teacher_name']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 text-xs">
                                        <?= e($e['subject_name']) ?>
                                    </span>
                                </td>
                                <td class="text-muted text-xs font-mono">
                                    <?= !empty($e['submit_time']) ? date('d/m/Y H:i', strtotime($e['submit_time'])) : '-' ?>
                                </td>
                                <td class="font-mono text-xs text-muted"><?= $e['passing_grade'] ?></td>
                                <td>
                                    <?php if ($e['final_score'] !== null): ?>
                                        <span class="font-mono font-bold fs-6 <?= (float)$e['final_score'] >= (float)$e['passing_grade'] ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format((float)$e['final_score'], 1) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary text-xs">Menunggu Penilaian</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int)($e['is_passed'] ?? 0) === 1): ?>
                                        <span class="badge bg-success text-white rounded-pill px-3 py-1 text-xs font-semibold">
                                            <i class="bi bi-check-circle-fill me-1"></i> LULUS KKM
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1 text-xs font-semibold">
                                            <i class="bi bi-exclamation-circle-fill me-1"></i> REMEDIAL
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('student/exams/' . $e['exam_id'] . '/result') ?>" class="btn btn-outline-primary btn-sm rounded-2 px-3 text-xs font-semibold">
                                        <i class="bi bi-file-text me-1"></i> Lihat Hasil
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x display-6 text-muted d-block mb-2"></i>
                                Belum ada riwayat ujian yang diselesaikan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
