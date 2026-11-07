<?php
use App\Helpers\CsrfHelper;
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Manajemen Mata Pelajaran</h3>
        <p class="text-muted text-sm mb-0">Kelola kurikulum mata pelajaran SMA, kode mapel unik, dan keterhubungan dengan bank soal serta jadwal asesmen.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-warning text-dark rounded-3 btn-sm px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddSubject">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Mapel Baru
        </button>
    </div>
</div>

<!-- Search Card -->
<div class="card-custom p-3 mb-4">
    <form action="<?= base_url('admin/subjects') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0 text-sm" placeholder="Cari nama mapel, kode, deskripsi..." value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-secondary btn-sm rounded-3 px-3">Cari</button>
            <?php if (!empty($search)): ?>
                <a href="<?= base_url('admin/subjects') ?>" class="btn btn-outline-secondary btn-sm rounded-3 ms-1">Reset</a>
            <?php endif; ?>
        </div>
        <div class="col text-md-end text-muted text-xs">
            Total <strong><?= count($subjects) ?></strong> mata pelajaran terdaftar
        </div>
    </form>
</div>

<!-- Subjects Table Card -->
<div class="card-custom p-4 mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-xs text-uppercase text-muted">
                    <th>Kode Mapel</th>
                    <th>Nama Mata Pelajaran</th>
                    <th>Deskripsi / Silabus</th>
                    <th>Bank Soal Terhubung</th>
                    <th>Ujian Terhubung</th>
                    <th class="text-center" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (!empty($subjects)): ?>
                    <?php foreach ($subjects as $sub): ?>
                        <tr>
                            <td>
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-mono text-xs px-2.5 py-1 font-bold">
                                    <?= e($sub['code']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="font-bold text-dark"><?= e($sub['name']) ?></div>
                            </td>
                            <td>
                                <span class="text-muted text-xs"><?= e($sub['description'] ?: 'Tidak ada keterangan') ?></span>
                            </td>
                            <td>
                                <?php $bCount = (int)($sub['question_bank_count'] ?? 0); ?>
                                <span class="badge <?= $bCount > 0 ? 'bg-primary-subtle text-primary' : 'bg-light text-muted border' ?> rounded-pill px-2.5 py-1 text-xs">
                                    <i class="bi bi-collection me-1"></i><?= $bCount ?> Bank
                                </span>
                            </td>
                            <td>
                                <?php $eCount = (int)($sub['exam_count'] ?? 0); ?>
                                <span class="badge <?= $eCount > 0 ? 'bg-info-subtle text-info' : 'bg-light text-muted border' ?> rounded-pill px-2.5 py-1 text-xs">
                                    <i class="bi bi-calendar-check me-1"></i><?= $eCount ?> Ujian
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary rounded-2 me-1" data-bs-toggle="modal" data-bs-target="#modalEditSubject_<?= $sub['id'] ?>" title="Edit Mapel">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-2" data-bs-toggle="modal" data-bs-target="#modalDeleteSubject_<?= $sub['id'] ?>" title="Hapus Mapel">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Subject -->
                        <div class="modal fade" id="modalEditSubject_<?= $sub['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom bg-light">
                                        <h5 class="modal-title font-bold text-dark">
                                            <i class="bi bi-pencil-square text-warning me-2"></i>Edit Mata Pelajaran
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= base_url('admin/editSubject') ?>" method="POST">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $sub['id'] ?>">

                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label text-xs font-semibold text-secondary">Kode Mapel (Unik) *</label>
                                                <input type="text" name="code" class="form-control text-sm font-mono" value="<?= e($sub['code']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-xs font-semibold text-secondary">Nama Mata Pelajaran *</label>
                                                <input type="text" name="name" class="form-control text-sm" value="<?= e($sub['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-xs font-semibold text-secondary">Deskripsi / Silabus Singkat</label>
                                                <textarea name="description" class="form-control text-sm" rows="3"><?= e($sub['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-warning text-dark btn-sm px-3 font-semibold">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Delete Subject -->
                        <div class="modal fade" id="modalDeleteSubject_<?= $sub['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom bg-danger text-white">
                                        <h5 class="modal-title font-bold">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Mata Pelajaran
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= base_url('admin/deleteSubject') ?>" method="POST">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $sub['id'] ?>">

                                        <div class="modal-body p-4 text-start">
                                            <p class="mb-2">Apakah Anda yakin ingin menghapus mata pelajaran berikut?</p>
                                            <div class="p-3 bg-light rounded-3 border mb-3">
                                                <div class="font-bold text-dark"><?= e($sub['name']) ?> (<?= e($sub['code']) ?>)</div>
                                                <div class="text-xs text-muted">Keterhubungan: <?= $bCount ?> Bank Soal, <?= $eCount ?> Ujian</div>
                                            </div>
                                            <?php if ($bCount > 0 || $eCount > 0): ?>
                                                <div class="alert alert-danger text-xs mb-0 py-2">
                                                    <i class="bi bi-slash-circle-fill me-1"></i> Mata pelajaran ini masih memiliki bank soal atau jadwal ujian aktif. Penghapusan akan ditolak.
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning text-xs mb-0 py-2">
                                                    <i class="bi bi-info-circle-fill me-1"></i> Mata pelajaran ini tidak memiliki relasi data aktif dan aman untuk dihapus.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer border-top bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger btn-sm px-3 font-semibold" <?= ($bCount > 0 || $eCount > 0) ? 'disabled' : '' ?>>
                                                Ya, Hapus Mapel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x display-6 text-muted d-block mb-2"></i>
                            Tidak ada data mata pelajaran ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Subject -->
<div class="modal fade" id="modalAddSubject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-warning text-dark">
                <h5 class="modal-title font-bold">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tambah Mata Pelajaran Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/createSubject') ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Kode Mata Pelajaran (Unik) *</label>
                        <input type="text" name="code" class="form-control text-sm font-mono" placeholder="Contoh: BIO-12 atau KIM-11" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Nama Mata Pelajaran *</label>
                        <input type="text" name="name" class="form-control text-sm" placeholder="Contoh: Biologi Peminatan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Deskripsi / Silabus Singkat</label>
                        <textarea name="description" class="form-control text-sm" rows="3" placeholder="Deskripsi materi mata pelajaran..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark btn-sm px-4 font-semibold">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>
