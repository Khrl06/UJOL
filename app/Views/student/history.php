<?php
$stats = array_merge([
    'total_completed' => 0,
    'total_passed' => 0,
    'total_remedial' => 0,
    'average_score' => 0,
    'highest_score' => 0,
    'lowest_score' => 0,
], $stats ?? []);
$completedExams = $completedExams ?? [];
$student = $student ?? [];
?>
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 text-xs font-semibold">
                <i class="bi bi-mortarboard-fill me-1"></i><?= e($student['class_name'] ?? 'Peserta Didik') ?>
            </span>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 text-xs font-mono">
                NISN: <?= e($student['nisn'] ?? '-') ?>
            </span>
        </div>
        <h3 class="font-bold text-dark mb-0">Riwayat Asesmen & Transkrip Nilai</h3>
        <p class="text-muted text-sm mb-0">Pantau rekapitulasi capaian belajar, nilai evaluasi ujian, serta status ketuntasan KKM Anda.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" onclick="window.print()" class="btn btn-outline-secondary rounded-3 btn-sm px-3 d-flex align-items-center gap-1.5 shadow-sm">
            <i class="bi bi-printer-fill"></i>
            <span>Cetak Transkrip</span>
        </button>
        <a href="<?= base_url('student/exams') ?>" class="btn btn-primary rounded-3 btn-sm px-3 font-semibold d-flex align-items-center gap-1.5 shadow-sm">
            <i class="bi bi-calendar-check"></i>
            <span>Jadwal Ujian Saya</span>
        </a>
    </div>
</div>

<!-- 4 Summary Metric Cards -->
<div class="row g-3 mb-4">
    <!-- 1. Total Ujian Selesai -->
    <div class="col-6 col-lg-3">
        <div class="card-custom p-3.5 h-100 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-xs text-muted font-semibold text-uppercase tracking-wider">Ujian Selesai</span>
                <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-check2-all fs-5"></i>
                </div>
            </div>
            <div class="display-6 font-mono font-bold text-dark mb-1">
                <?= $stats['total_completed'] ?>
            </div>
            <span class="text-muted text-xs">Paket ujian telah dikerjakan</span>
        </div>
    </div>

    <!-- 2. Lulus KKM -->
    <div class="col-6 col-lg-3">
        <div class="card-custom p-3.5 h-100 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-xs text-muted font-semibold text-uppercase tracking-wider">Lulus KKM</span>
                <div class="bg-success-subtle text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-patch-check-fill fs-5"></i>
                </div>
            </div>
            <div class="display-6 font-mono font-bold text-success mb-1">
                <?= $stats['total_passed'] ?>
            </div>
            <span class="text-xs text-muted">
                <?php $passPct = $stats['total_completed'] > 0 ? round(($stats['total_passed'] / $stats['total_completed']) * 100) : 0; ?>
                Tingkat Kelulusan: <strong class="text-success"><?= $passPct ?>%</strong>
            </span>
        </div>
    </div>

    <!-- 3. Remedial -->
    <div class="col-6 col-lg-3">
        <div class="card-custom p-3.5 h-100 border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-xs text-muted font-semibold text-uppercase tracking-wider">Perlu Remedial</span>
                <div class="bg-warning-subtle text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
            </div>
            <div class="display-6 font-mono font-bold text-warning mb-1">
                <?= $stats['total_remedial'] ?>
            </div>
            <span class="text-muted text-xs">Di bawah batas standar KKM</span>
        </div>
    </div>

    <!-- 4. Rata-rata Nilai -->
    <div class="col-6 col-lg-3">
        <div class="card-custom p-3.5 h-100 border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-xs text-muted font-semibold text-uppercase tracking-wider">Rata-Rata Nilai</span>
                <div class="bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-award-fill fs-5"></i>
                </div>
            </div>
            <div class="display-6 font-mono font-bold text-info mb-1">
                <?= number_format((float)$stats['average_score'], 1) ?>
            </div>
            <span class="text-muted text-xs">
                Tertinggi: <strong class="text-dark font-mono"><?= number_format((float)$stats['highest_score'], 1) ?></strong>
            </span>
        </div>
    </div>
</div>

<!-- Main Card: Filter & Table -->
<div class="card-custom p-4 mb-4">
    <!-- Filter Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 pb-3 border-bottom">
        <!-- Status Filter Pills -->
        <div class="d-flex gap-2" id="filterStatusPills">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 text-xs font-semibold filter-btn active" data-filter="all">
                Semua Ujian (<?= $stats['total_completed'] ?>)
            </button>
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 text-xs font-semibold filter-btn" data-filter="passed">
                <i class="bi bi-check-circle me-1"></i> Lulus (<?= $stats['total_passed'] ?>)
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 text-xs font-semibold filter-btn" data-filter="remedial">
                <i class="bi bi-exclamation-circle me-1"></i> Remedial (<?= $stats['total_remedial'] ?>)
            </button>
        </div>

        <!-- Live Search Box -->
        <div class="position-relative" style="min-width: 260px;">
            <i class="bi bi-search position-absolute text-muted" style="top: 50%; left: 12px; transform: translateY(-50%); font-size: 0.85rem;"></i>
            <input type="text" id="searchInput" class="form-control form-control-sm text-xs ps-5 rounded-pill" placeholder="Cari nama ujian, mapel, guru...">
        </div>
    </div>

    <!-- Table of Results -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="historyTable">
            <thead class="table-light text-xs text-uppercase text-muted">
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 32%;">Nama Paket Ujian</th>
                    <th style="width: 18%;">Mata Pelajaran</th>
                    <th style="width: 15%;">Waktu Selesai</th>
                    <th style="width: 10%;" class="text-center">KKM</th>
                    <th style="width: 10%;" class="text-center">Nilai</th>
                    <th style="width: 10%;" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (!empty($completedExams)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($completedExams as $e): ?>
                        <?php 
                            $isPassed = (int)($e['is_passed'] ?? 0) === 1; 
                            $scoreVal = (float)($e['final_score'] ?? 0.0);
                            $passingGrade = (float)($e['passing_grade'] ?? 75.0);
                        ?>
                        <tr class="history-row" data-status="<?= $isPassed ? 'passed' : 'remedial' ?>" data-search="<?= strtolower(e($e['exam_title'] . ' ' . $e['subject_name'] . ' ' . $e['teacher_name'])) ?>">
                            <td class="text-muted font-mono text-xs"><?= $no++ ?></td>
                            <td>
                                <div class="font-bold text-dark"><?= e($e['exam_title']) ?></div>
                                <div class="text-muted text-xs d-flex align-items-center gap-1 mt-0.5">
                                    <i class="bi bi-person text-secondary"></i>
                                    <span>Guru: <?= e($e['teacher_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 text-xs font-semibold">
                                    <i class="bi bi-journal-bookmark me-1"></i><?= e($e['subject_name']) ?>
                                </span>
                                <?php if (!empty($e['subject_code'])): ?>
                                    <small class="text-muted text-xs d-block mt-0.5"><?= e($e['subject_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted text-xs font-mono">
                                <div class="text-dark">
                                    <?= !empty($e['submit_time']) ? date('d M Y', strtotime($e['submit_time'])) : '-' ?>
                                </div>
                                <div class="text-muted text-xs">
                                    <i class="bi bi-clock me-1"></i><?= !empty($e['submit_time']) ? date('H:i', strtotime($e['submit_time'])) . ' WIB' : '-' ?>
                                </div>
                            </td>
                            <td class="text-center font-mono text-xs text-muted font-semibold">
                                <?= number_format($passingGrade, 1) ?>
                            </td>
                            <td class="text-center">
                                <div class="font-mono font-bold fs-6 <?= $isPassed ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($scoreVal, 1) ?>
                                </div>
                                <div>
                                    <?php if ($isPassed): ?>
                                        <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                            <i class="bi bi-check-circle-fill me-0.5"></i> LULUS
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                            <i class="bi bi-exclamation-triangle-fill me-0.5"></i> REMEDIAL
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('student/exams/' . $e['exam_id'] . '/result') ?>" class="btn btn-outline-primary btn-sm rounded-3 px-2.5 py-1 text-xs font-semibold d-inline-flex align-items-center gap-1 shadow-sm">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Hasil</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="emptyRowPermanent">
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-clipboard2-x display-5 text-muted d-block mb-3"></i>
                                <h5 class="font-bold text-dark mb-1">Belum Ada Riwayat Ujian</h5>
                                <p class="text-muted text-sm mb-3">Anda belum memiliki sesi ujian yang telah diselesaikan. Silakan cek ujian yang sedang aktif di jadwal Anda.</p>
                                <a href="<?= base_url('student/exams') ?>" class="btn btn-primary btn-sm rounded-3 px-4 font-semibold shadow-sm">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Buka Jadwal Ujian
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr id="noFilterMatchRow" class="d-none">
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-search display-6 text-muted d-block mb-2"></i>
                        Tidak ada riwayat ujian yang sesuai dengan kriteria pencarian / filter.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const rows = document.querySelectorAll('.history-row');
    const noMatchRow = document.getElementById('noFilterMatchRow');

    let currentFilter = 'all';
    let currentSearch = '';

    function applyFilters() {
        let visibleCount = 0;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowSearch = row.getAttribute('data-search') || '';

            const matchesStatus = (currentFilter === 'all') || (rowStatus === currentFilter);
            const matchesSearch = !currentSearch || rowSearch.includes(currentSearch);

            if (matchesStatus && matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (noMatchRow) {
            if (visibleCount === 0 && rows.length > 0) {
                noMatchRow.classList.remove('d-none');
            } else {
                noMatchRow.classList.add('d-none');
            }
        }
    }

    // Filter Buttons Click
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => {
                b.classList.remove('active', 'btn-primary', 'btn-success', 'btn-warning');
                if (b.getAttribute('data-filter') === 'all') b.classList.add('btn-outline-primary');
                else if (b.getAttribute('data-filter') === 'passed') b.classList.add('btn-outline-success');
                else if (b.getAttribute('data-filter') === 'remedial') b.classList.add('btn-outline-warning');
            });

            this.classList.add('active');
            const filterType = this.getAttribute('data-filter');
            if (filterType === 'all') {
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
            } else if (filterType === 'passed') {
                this.classList.remove('btn-outline-success');
                this.classList.add('btn-success');
            } else if (filterType === 'remedial') {
                this.classList.remove('btn-outline-warning');
                this.classList.add('btn-warning');
            }

            currentFilter = filterType;
            applyFilters();
        });
    });

    // Search Input
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearch = this.value.trim().toLowerCase();
            applyFilters();
        });
    }
});
</script>

<style>
@media print {
    #sidebar, .navbar-custom, #filterStatusPills, #searchInput, .btn {
        display: none !important;
    }
    .card-custom {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    body {
        background-color: #fff !important;
        color: #000 !important;
    }
}
</style>
