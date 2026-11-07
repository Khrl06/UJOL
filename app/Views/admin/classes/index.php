<?php
use App\Helpers\CsrfHelper;
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Manajemen Kelas & Rombongan Belajar</h3>
        <p class="text-muted text-sm mb-0">Kelola data tingkatan kelas (X, XI, XII), jurusan, dan alokasi peserta didik per rombel.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-primary rounded-3 btn-sm px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddClass">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kelas Baru
        </button>
    </div>
</div>

<!-- Search Card -->
<div class="card-custom p-3 mb-4">
    <form action="<?= base_url('admin/classes') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0 text-sm" placeholder="Cari nama rombel, jurusan, tahun ajaran..." value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-secondary btn-sm rounded-3 px-3">Cari</button>
            <?php if (!empty($search)): ?>
                <a href="<?= base_url('admin/classes') ?>" class="btn btn-outline-secondary btn-sm rounded-3 ms-1">Reset</a>
            <?php endif; ?>
        </div>
        <div class="col text-md-end text-muted text-xs">
            Total <strong><?= count($classes) ?></strong> rombel kelas terdaftar
        </div>
    </form>
</div>

<!-- Classes Table Card -->
<div class="card-custom p-4 mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-xs text-uppercase text-muted">
                    <th>Nama Rombel</th>
                    <th>Tingkat (Jenjang)</th>
                    <th>Peminatan / Jurusan</th>
                    <th>Tahun Ajaran</th>
                    <th>Jumlah Siswa</th>
                    <th class="text-center" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (!empty($classes)): ?>
                    <?php foreach ($classes as $c): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-3 bg-primary-subtle text-primary font-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="font-bold text-dark"><?= e($c['class_name']) ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill px-2.5 py-1 text-xs">
                                    Kelas <?= e($c['grade_level']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle font-semibold text-xs">
                                    <?= e($c['major']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-dark font-mono text-xs"><?= e($c['academic_year']) ?></span>
                            </td>
                            <td>
                                <?php $sCount = (int)($c['total_students'] ?? 0); ?>
                                <span class="badge <?= $sCount > 0 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?> rounded-pill px-2.5 py-1 text-xs font-semibold">
                                    <i class="bi bi-people-fill me-1"></i><?= $sCount ?> Siswa
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary rounded-2 me-1" data-bs-toggle="modal" data-bs-target="#modalEditClass_<?= $c['id'] ?>" title="Edit Kelas">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-2" data-bs-toggle="modal" data-bs-target="#modalDeleteClass_<?= $c['id'] ?>" title="Hapus Kelas">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Class -->
                        <div class="modal fade" id="modalEditClass_<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom bg-light">
                                        <h5 class="modal-title font-bold text-dark">
                                            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Kelas
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= base_url('admin/editClass') ?>" method="POST">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">

                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label text-xs font-semibold text-secondary">Nama Rombel *</label>
                                                <input type="text" name="class_name" class="form-control text-sm" value="<?= e($c['class_name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-xs font-semibold text-secondary">Tingkat Jenjang *</label>
                                                <select name="grade_level" class="form-select text-sm" required>
                                                    <option value="10" <?= $c['grade_level'] == '10' ? 'selected' : '' ?>>Kelas 10 (Fase E)</option>
                                                    <option value="11" <?= $c['grade_level'] == '11' ? 'selected' : '' ?>>Kelas 11 (Fase F)</option>
                                                    <option value="12" <?= $c['grade_level'] == '12' ? 'selected' : '' ?>>Kelas 12 (Tingkat Akhir)</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-xs font-semibold text-secondary">Peminatan / Jurusan *</label>
                                                <input type="text" name="major" class="form-control text-sm" value="<?= e($c['major']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-xs font-semibold text-secondary">Tahun Ajaran *</label>
                                                <input type="text" name="academic_year" class="form-control text-sm font-mono" value="<?= e($c['academic_year']) ?>" required>
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

                        <!-- Modal Delete Class -->
                        <div class="modal fade" id="modalDeleteClass_<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom bg-danger text-white">
                                        <h5 class="modal-title font-bold">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Kelas
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= base_url('admin/deleteClass') ?>" method="POST">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">

                                        <div class="modal-body p-4 text-start">
                                            <p class="mb-2">Apakah Anda yakin ingin menghapus kelas berikut?</p>
                                            <div class="p-3 bg-light rounded-3 border mb-3">
                                                <div class="font-bold text-dark"><?= e($c['class_name']) ?></div>
                                                <div class="text-xs text-muted">Tingkat <?= e($c['grade_level']) ?> - <?= e($c['major']) ?> (<?= $sCount ?> Siswa)</div>
                                            </div>
                                            <?php if ($sCount > 0): ?>
                                                <div class="alert alert-danger text-xs mb-0 py-2">
                                                    <i class="bi bi-slash-circle-fill me-1"></i> Kelas ini memiliki <strong><?= $sCount ?> siswa</strong>. Penghapusan akan ditolak demi melindungi data siswa.
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning text-xs mb-0 py-2">
                                                    <i class="bi bi-info-circle-fill me-1"></i> Kelas ini kosong dan aman untuk dihapus.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer border-top bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger btn-sm px-3 font-semibold" <?= $sCount > 0 ? 'disabled' : '' ?>>
                                                Ya, Hapus Kelas
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
                            <i class="bi bi-building-x display-6 text-muted d-block mb-2"></i>
                            Tidak ada rombel kelas ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Class -->
<div class="modal fade" id="modalAddClass" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-primary text-white">
                <h5 class="modal-title font-bold">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tambah Rombel Kelas Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/createClass') ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Nama Rombel Kelas *</label>
                        <input type="text" name="class_name" class="form-control text-sm" placeholder="Contoh: XII MIPA 3 atau X E-2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Tingkat Jenjang *</label>
                        <select name="grade_level" class="form-select text-sm" required>
                            <option value="12">Kelas 12 (Tingkat Akhir)</option>
                            <option value="11">Kelas 11 (Fase F)</option>
                            <option value="10">Kelas 10 (Fase E)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Peminatan / Jurusan *</label>
                        <input type="text" name="major" class="form-control text-sm" value="MIPA" placeholder="MIPA, IPS, Bahasa, atau Umum" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Tahun Ajaran *</label>
                        <input type="text" name="academic_year" class="form-control text-sm font-mono" value="2026/2027" required>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-semibold">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>
</div>
