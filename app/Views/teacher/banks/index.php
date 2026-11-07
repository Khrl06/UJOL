<?php
use App\Helpers\CsrfHelper;
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Bank Soal Saya</h3>
        <p class="text-muted text-sm mb-0">Kelola paket bank soal mandiri Anda berdasarkan mata pelajaran, tingkatan kelas, dan kisi-kisi asesmen.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('teacher/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-primary rounded-3 btn-sm px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddBank">
            <i class="bi bi-plus-circle-fill me-1"></i> Buat Bank Soal Baru
        </button>
    </div>
</div>

<!-- Search & Subject Filter Card -->
<div class="card-custom p-3 mb-4">
    <form action="<?= base_url('teacher/banks') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-5 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0 text-sm" placeholder="Cari judul bank soal atau kisi-kisi..." value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <select name="subject_id" class="form-select bg-light text-sm" onchange="this.form.submit()">
                <option value="">-- Semua Mata Pelajaran --</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $subjectId == $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['name']) ?> (<?= e($s['code']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-secondary btn-sm rounded-3 px-3">Filter</button>
            <?php if (!empty($search) || !empty($subjectId)): ?>
                <a href="<?= base_url('teacher/banks') ?>" class="btn btn-outline-secondary btn-sm rounded-3 ms-1">Reset</a>
            <?php endif; ?>
        </div>
        <div class="col text-md-end text-muted text-xs">
            Total <strong><?= number_format($total) ?></strong> paket bank soal
        </div>
    </form>
</div>

<!-- Bank Soal Grid / Cards -->
<div class="row g-3 mb-4">
    <?php if (!empty($banks)): ?>
        <?php foreach ($banks as $b): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card-custom p-4 h-100 d-flex flex-column justify-content-between border-top border-4 border-primary">
                    <div>
                        <!-- Subject & Status Badge -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary font-semibold text-xs rounded-pill px-2.5 py-1">
                                <i class="bi bi-journal-bookmark me-1"></i><?= e($b['subject_name']) ?>
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-light text-secondary border font-mono text-xs">
                                    Kelas <?= e($b['grade_level'] ?? '12') ?>
                                </span>
                                <?php if ((int)($b['is_active'] ?? 1) === 1): ?>
                                    <span class="badge bg-success-subtle text-success rounded-pill text-xs">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill text-xs">Nonaktif</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Title -->
                        <h5 class="font-bold text-dark mb-2">
                            <a href="<?= base_url('teacher/bankQuestions?bank_id=' . $b['id']) ?>" class="text-dark text-decoration-none hover-primary">
                                <?= e($b['title']) ?>
                            </a>
                        </h5>

                        <!-- Description -->
                        <p class="text-muted text-xs mb-3 line-clamp-2" style="min-height: 32px;">
                            <?= e($b['description'] ?: 'Tidak ada instruksi atau kisi-kisi khusus untuk bank soal ini.') ?>
                        </p>

                        <!-- Question Stats -->
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-light border text-dark text-xs py-1.5 px-2 font-semibold">
                                <i class="bi bi-list-check text-primary me-1"></i><?= $b['mc_count'] ?? 0 ?> Pilihan Ganda
                            </span>
                            <span class="badge bg-light border text-dark text-xs py-1.5 px-2 font-semibold">
                                <i class="bi bi-pencil-square text-info me-1"></i><?= $b['essay_count'] ?? 0 ?> Essay
                            </span>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted text-xs">
                            <i class="bi bi-clock me-1"></i><?= date('d M Y', strtotime($b['created_at'])) ?>
                        </small>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" data-bs-toggle="modal" data-bs-target="#modalEditBank_<?= $b['id'] ?>" title="Edit Bank Soal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" data-bs-toggle="modal" data-bs-target="#modalDeleteBank_<?= $b['id'] ?>" title="Hapus Bank Soal">
                                <i class="bi bi-trash"></i>
                            </button>
                            <a href="<?= base_url('teacher/bankQuestions?bank_id=' . $b['id']) ?>" class="btn btn-primary btn-sm rounded-2 py-1 px-3 font-semibold text-xs">
                                Kelola Soal <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Edit Bank -->
            <div class="modal fade" id="modalEditBank_<?= $b['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom bg-light">
                            <h5 class="modal-title font-bold text-dark">
                                <i class="bi bi-pencil-square text-primary me-2"></i>Edit Informasi Bank Soal
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="<?= base_url('teacher/editBank') ?>" method="POST">
                            <?= CsrfHelper::tokenField() ?>
                            <input type="hidden" name="id" value="<?= $b['id'] ?>">

                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label text-xs font-semibold text-secondary">Judul Bank Soal *</label>
                                    <input type="text" name="title" class="form-control text-sm" value="<?= e($b['title']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-xs font-semibold text-secondary">Mata Pelajaran *</label>
                                    <select name="subject_id" class="form-select text-sm" required>
                                        <?php foreach ($subjects as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= $b['subject_id'] == $s['id'] ? 'selected' : '' ?>>
                                                <?= e($s['name']) ?> (<?= e($s['code']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-xs font-semibold text-secondary">Tingkat Kelas Sasaran</label>
                                    <select name="grade_level" class="form-select text-sm">
                                        <option value="12" <?= ($b['grade_level'] ?? '12') == '12' ? 'selected' : '' ?>>Kelas 12 (Tingkat Akhir)</option>
                                        <option value="11" <?= ($b['grade_level'] ?? '12') == '11' ? 'selected' : '' ?>>Kelas 11 (Fase F)</option>
                                        <option value="10" <?= ($b['grade_level'] ?? '12') == '10' ? 'selected' : '' ?>>Kelas 10 (Fase E)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-xs font-semibold text-secondary">Status Bank Soal</label>
                                    <select name="is_active" class="form-select text-sm">
                                        <option value="1" <?= (int)($b['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Aktif (Siap Digunakan Ujian)</option>
                                        <option value="0" <?= (int)($b['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Nonaktif / Draft</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-xs font-semibold text-secondary">Deskripsi / Kisi-kisi Soal</label>
                                    <textarea name="description" class="form-control text-sm" rows="3"><?= e($b['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top bg-light">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3 font-semibold">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Delete Bank -->
            <div class="modal fade" id="modalDeleteBank_<?= $b['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom bg-danger text-white">
                            <h5 class="modal-title font-bold">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Bank Soal
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="<?= base_url('teacher/deleteBank') ?>" method="POST">
                            <?= CsrfHelper::tokenField() ?>
                            <input type="hidden" name="id" value="<?= $b['id'] ?>">

                            <div class="modal-body p-4 text-start">
                                <p class="mb-2">Apakah Anda yakin ingin menghapus bank soal berikut?</p>
                                <div class="p-3 bg-light rounded-3 border mb-3">
                                    <div class="font-bold text-dark"><?= e($b['title']) ?></div>
                                    <div class="text-xs text-muted">Mata Pelajaran: <?= e($b['subject_name']) ?> | Total: <?= $b['total_questions'] ?> Butir Soal</div>
                                </div>
                                <div class="alert alert-warning text-xs mb-0 py-2">
                                    <i class="bi bi-shield-alert me-1"></i> Seluruh butir soal di dalam paket ini akan dihapus secara permanen.
                                </div>
                            </div>
                            <div class="modal-footer border-top bg-light">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger btn-sm px-3 font-semibold">Ya, Hapus Bank Soal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card-custom p-5 text-center">
                <i class="bi bi-folder-x display-6 text-muted d-block mb-3"></i>
                <h5 class="font-bold text-dark mb-1">Belum Ada Bank Soal</h5>
                <p class="text-muted text-sm mb-4">Anda belum memiliki bank soal atau tidak ada hasil yang cocok dengan filter pencarian.</p>
                <button type="button" class="btn btn-primary rounded-3 btn-sm px-4 font-semibold" data-bs-toggle="modal" data-bs-target="#modalAddBank">
                    <i class="bi bi-plus-circle-fill me-1"></i> Buat Bank Soal Sekarang
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <small class="text-muted text-xs">
            Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= number_format($total) ?> Paket Soal)
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= base_url('teacher/banks?page=' . ($page - 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($subjectId) ? '&subject_id=' . $subjectId : '')) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('teacher/banks?page=' . $i . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($subjectId) ? '&subject_id=' . $subjectId : '')) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= base_url('teacher/banks?page=' . ($page + 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($subjectId) ? '&subject_id=' . $subjectId : '')) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>

<!-- Modal Add Bank Soal -->
<div class="modal fade" id="modalAddBank" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-primary text-white">
                <h5 class="modal-title font-bold">
                    <i class="bi bi-plus-circle-fill me-2"></i>Buat Bank Soal Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('teacher/createBank') ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Judul Bank Soal *</label>
                        <input type="text" name="title" class="form-control text-sm" placeholder="Contoh: Bank Soal PAS Gasal Informatika 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Mata Pelajaran *</label>
                        <select name="subject_id" class="form-select text-sm" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Tingkat Jenjang Kelas</label>
                        <select name="grade_level" class="form-select text-sm">
                            <option value="12">Kelas 12 (Tingkat Akhir)</option>
                            <option value="11">Kelas 11 (Fase F)</option>
                            <option value="10">Kelas 10 (Fase E)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Status Keaktifan</label>
                        <select name="is_active" class="form-select text-sm">
                            <option value="1">Aktif (Dapat Digunakan untuk Ujian)</option>
                            <option value="0">Nonaktif / Draft</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Deskripsi / Kisi-kisi</label>
                        <textarea name="description" class="form-control text-sm" rows="3" placeholder="Tuliskan materi lingkup asesmen..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-semibold">Simpan & Kelola Soal</button>
                </div>
            </form>
        </div>
    </div>
</div>
