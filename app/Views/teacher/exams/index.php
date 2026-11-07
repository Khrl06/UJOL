<?php
use App\Helpers\CsrfHelper;
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Manajemen Paket & Jadwal Ujian</h3>
        <p class="text-muted text-sm mb-0">Kelola paket pelaksanaan ujian, token akses, jadwal rilis, alokasi peserta kelas, dan konfigurasi AI proctoring.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('teacher/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Dasbor
        </a>
        <a href="<?= base_url('teacher/exams/create') ?>" class="btn btn-primary rounded-3 btn-sm px-3 font-semibold shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> Buat Ujian Baru
        </a>
    </div>
</div>

<!-- Search & Status Filter Card -->
<div class="card-custom p-3 mb-4">
    <form action="<?= base_url('teacher/exams') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0 text-sm" placeholder="Cari nama ujian, token, deskripsi..." value="<?= e($search) ?>">
            </div>
        </div>

        <div class="col-12 col-md-3">
            <select name="subject_id" class="form-select bg-light text-sm" onchange="this.form.submit()">
                <option value="">-- Semua Mata Pelajaran --</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $subjectId == $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['name']) ?> (<?= e($s['code']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-5">
            <div class="btn-group w-100 overflow-auto" role="group">
                <a href="<?= base_url('teacher/exams?subject_id=' . ($subjectId ?? '') . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                   class="btn btn-sm <?= empty($status) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Semua
                </a>
                <a href="<?= base_url('teacher/exams?status=draft' . ($subjectId ? '&subject_id=' . $subjectId : '') . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                   class="btn btn-sm <?= $status === 'draft' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                    DRAFT
                </a>
                <a href="<?= base_url('teacher/exams?status=published' . ($subjectId ? '&subject_id=' . $subjectId : '') . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                   class="btn btn-sm <?= $status === 'published' ? 'btn-success' : 'btn-outline-success' ?>">
                    PUBLISHED
                </a>
                <a href="<?= base_url('teacher/exams?status=ongoing' . ($subjectId ? '&subject_id=' . $subjectId : '') . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                   class="btn btn-sm <?= $status === 'ongoing' ? 'btn-danger' : 'btn-outline-danger' ?>">
                    ONGOING
                </a>
                <a href="<?= base_url('teacher/exams?status=completed' . ($subjectId ? '&subject_id=' . $subjectId : '') . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                   class="btn btn-sm <?= $status === 'completed' ? 'btn-info text-dark' : 'btn-outline-info' ?>">
                    COMPLETED
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Exam Cards Grid -->
<div class="row g-3 mb-4">
    <?php if (!empty($exams)): ?>
        <?php foreach ($exams as $e): ?>
            <?php
                $statusUpper = strtoupper($e['status']);
                $badgeStatus = match($e['status']) {
                    'published' => 'bg-success text-white',
                    'ongoing'   => 'bg-danger text-white',
                    'completed' => 'bg-info text-dark',
                    'cancelled' => 'bg-dark text-white',
                    default     => 'bg-secondary text-white'
                };
            ?>
            <div class="col-12 col-lg-6">
                <div class="card-custom p-4 h-100 d-flex flex-column justify-content-between border-top border-4 <?= ($e['status'] === 'published' || $e['status'] === 'ongoing') ? 'border-success' : 'border-secondary' ?>">
                    <div>
                        <!-- Header Badges -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary font-semibold text-xs rounded-pill px-2.5 py-1">
                                <i class="bi bi-journal-bookmark me-1"></i><?= e($e['subject_name']) ?>
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-dark font-mono text-xs px-2.5 py-1" title="Token Ujian">
                                    <i class="bi bi-key-fill text-warning me-1"></i><?= e($e['token']) ?>
                                </span>
                                <span class="badge <?= $badgeStatus ?> rounded-pill px-2.5 py-1 text-xs font-semibold">
                                    <?= $statusUpper ?>
                                </span>
                            </div>
                        </div>

                        <!-- Exam Title -->
                        <h5 class="font-bold text-dark mb-1">
                            <a href="<?= base_url('teacher/exams/' . $e['id']) ?>" class="text-dark text-decoration-none hover-primary">
                                <?= e($e['title']) ?>
                            </a>
                        </h5>

                        <p class="text-muted text-xs mb-3 line-clamp-2" style="min-height: 32px;">
                            <?= e($e['description'] ?: 'Tidak ada instruksi khusus untuk paket ujian ini.') ?>
                        </p>

                        <!-- Exam Meta Schedule & Specs -->
                        <div class="row g-2 mb-3 text-xs">
                            <div class="col-12 col-sm-6">
                                <div class="p-2 rounded bg-light border">
                                    <div class="text-muted mb-0.5"><i class="bi bi-calendar-event me-1"></i>Jadwal Pelaksanaan:</div>
                                    <strong class="text-dark">
                                        <?= date('d/m/Y', strtotime($e['start_date'])) ?> s/d <?= date('d/m/Y', strtotime($e['end_date'])) ?>
                                    </strong>
                                    <div class="text-muted mt-0.5">Pukul <?= substr($e['start_time'], 0, 5) ?> - <?= substr($e['end_time'], 0, 5) ?> WIB</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="p-2 rounded bg-light border">
                                    <div class="text-muted mb-0.5"><i class="bi bi-stopwatch me-1"></i>Durasi & Soal:</div>
                                    <strong class="text-dark"><?= $e['duration_minutes'] ?> Menit</strong> | <?= $e['total_questions'] ?> Butir Soal
                                    <div class="text-success font-semibold mt-0.5"><i class="bi bi-people-fill me-1"></i><?= $e['total_participants'] ?? 0 ?> Peserta Terdaftar</div>
                                </div>
                            </div>
                        </div>

                        <!-- Proctoring Features Badge -->
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <?php if (!empty($e['enable_dual_camera'])): ?>
                                <span class="badge bg-info-subtle text-info border border-info-subtle text-xs">
                                    <i class="bi bi-webcam-fill me-1"></i>Dual Camera Aktif
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($e['enable_ai_proctoring'])): ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-xs">
                                    <i class="bi bi-robot me-1"></i>AI Proctoring
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($e['is_random_questions'])): ?>
                                <span class="badge bg-light text-secondary border text-xs">Acak Soal</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex gap-1">
                            <!-- Detail Button -->
                            <a href="<?= base_url('teacher/exams/' . $e['id']) ?>" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2.5 font-semibold text-xs">
                                <i class="bi bi-eye me-1"></i>Detail & Peserta
                            </a>

                            <!-- Edit Button -->
                            <a href="<?= base_url('teacher/exams/' . $e['id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2 text-xs" title="Edit Pengaturan Ujian">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <!-- Duplicate Button -->
                            <form action="<?= base_url('teacher/duplicateExam') ?>" method="POST" class="d-inline">
                                <?= CsrfHelper::tokenField() ?>
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2 text-xs" title="Duplikasi Ujian 1-Klik">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </form>

                            <!-- Delete Button -->
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2 text-xs" data-bs-toggle="modal" data-bs-target="#modalDeleteExam_<?= $e['id'] ?>" title="Hapus Ujian">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <!-- Publish / Unpublish Toggle -->
                        <?php if ($e['status'] === 'draft'): ?>
                            <form action="<?= base_url('teacher/publishExam') ?>" method="POST" class="d-inline">
                                <?= CsrfHelper::tokenField() ?>
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm rounded-2 py-1 px-3 font-semibold text-xs">
                                    <i class="bi bi-send-check-fill me-1"></i>Publish Ujian
                                </button>
                            </form>
                        <?php elseif ($e['status'] === 'published'): ?>
                            <form action="<?= base_url('teacher/unpublishExam') ?>" method="POST" class="d-inline">
                                <?= CsrfHelper::tokenField() ?>
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn btn-outline-warning btn-sm rounded-2 py-1 px-3 font-semibold text-xs text-dark" title="Kembalikan ke status draft">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Unpublish
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Modal Delete Exam -->
            <div class="modal fade" id="modalDeleteExam_<?= $e['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom bg-danger text-white">
                            <h5 class="modal-title font-bold">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Ujian
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="<?= base_url('teacher/deleteExam') ?>" method="POST">
                            <?= CsrfHelper::tokenField() ?>
                            <input type="hidden" name="id" value="<?= $e['id'] ?>">

                            <div class="modal-body p-4 text-start">
                                <p class="mb-2">Apakah Anda yakin ingin menghapus paket ujian berikut?</p>
                                <div class="p-3 bg-light rounded-3 border mb-3">
                                    <div class="font-bold text-dark"><?= e($e['title']) ?></div>
                                    <div class="text-xs text-muted">Mata Pelajaran: <?= e($e['subject_name']) ?> | Token: <?= e($e['token']) ?></div>
                                </div>
                                <div class="alert alert-warning text-xs mb-0 py-2">
                                    <i class="bi bi-shield-alert me-1"></i> Seluruh data alokasi peserta dan riwayat terkait ujian ini akan dihapus permanen.
                                </div>
                            </div>
                            <div class="modal-footer border-top bg-light">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger btn-sm px-3 font-semibold">Ya, Hapus Ujian</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card-custom p-5 text-center">
                <i class="bi bi-calendar-x display-6 text-muted d-block mb-3"></i>
                <h5 class="font-bold text-dark mb-1">Belum Ada Jadwal Ujian</h5>
                <p class="text-muted text-sm mb-4">Anda belum memiliki paket ujian atau tidak ada data yang cocok dengan kriteria filter.</p>
                <a href="<?= base_url('teacher/exams/create') ?>" class="btn btn-primary rounded-3 btn-sm px-4 font-semibold">
                    <i class="bi bi-plus-circle-fill me-1"></i> Buat Paket Ujian Sekarang
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <small class="text-muted text-xs">
            Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= number_format($total) ?> Paket Ujian)
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= base_url('teacher/exams?page=' . ($page - 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($status) ? '&status=' . $status : '') . (!empty($subjectId) ? '&subject_id=' . $subjectId : '')) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('teacher/exams?page=' . $i . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($status) ? '&status=' . $status : '') . (!empty($subjectId) ? '&subject_id=' . $subjectId : '')) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= base_url('teacher/exams?page=' . ($page + 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($status) ? '&status=' . $status : '') . (!empty($subjectId) ? '&subject_id=' . $subjectId : '')) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>
