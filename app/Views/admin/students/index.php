<?php
use App\Helpers\CsrfHelper;
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Master Data Peserta Didik</h3>
        <p class="text-muted text-sm mb-0">Kelola informasi peserta didik, NISN, penempatan rombongan belajar (kelas), dan akun login asesmen.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-success rounded-3 btn-sm px-3 font-semibold shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#modalAddStudent">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Siswa Baru
        </button>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card-custom p-3 mb-4">
    <form action="<?= base_url('admin/students') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-5 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0 text-sm" placeholder="Cari nama, NISN, email, username..." value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <select name="class_id" class="form-select bg-light text-sm" onchange="this.form.submit()">
                <option value="">-- Semua Rombel Kelas --</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['class_name']) ?> (<?= e($c['major']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-secondary btn-sm rounded-3 px-3">Filter</button>
            <?php if (!empty($search) || !empty($classId)): ?>
                <a href="<?= base_url('admin/students') ?>" class="btn btn-outline-secondary btn-sm rounded-3 ms-1">Reset</a>
            <?php endif; ?>
        </div>
        <div class="col text-md-end text-muted text-xs">
            Menampilkan total <strong><?= number_format($total) ?></strong> peserta didik
        </div>
    </form>
</div>

<!-- Students Table Card -->
<div class="card-custom p-4 mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-xs text-uppercase text-muted">
                    <th>Nama & Akun</th>
                    <th>NISN</th>
                    <th>Kelas / Rombel</th>
                    <th>Kontak / Email</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-success-subtle text-success font-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                        <?= strtoupper(substr($s['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-dark"><?= e($s['name']) ?></div>
                                        <small class="text-muted text-xs"><i class="bi bi-person me-1"></i><?= e($s['username']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-mono text-xs">
                                    <?= e($s['nisn']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 text-xs font-semibold">
                                    <i class="bi bi-building me-1"></i><?= e($s['class_name'] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-dark"><?= e($s['email']) ?></div>
                                <small class="text-muted text-xs"><?= e($s['phone'] ?: '-') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    <?= ($s['gender'] === 'L') ? 'Laki-laki' : 'Perempuan' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ((int)($s['is_active'] ?? 1) === 1): ?>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 text-xs">
                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 text-xs">
                                        <i class="bi bi-x-circle-fill me-1"></i>Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary rounded-2 me-1" data-bs-toggle="modal" data-bs-target="#modalEditStudent_<?= $s['id'] ?>" title="Edit Siswa">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-2" data-bs-toggle="modal" data-bs-target="#modalDeleteStudent_<?= $s['id'] ?>" title="Hapus Siswa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Student -->
                        <div class="modal fade" id="modalEditStudent_<?= $s['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom bg-light">
                                        <h5 class="modal-title font-bold text-dark">
                                            <i class="bi bi-pencil-square text-success me-2"></i>Edit Data Peserta Didik
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= base_url('admin/editStudent') ?>" method="POST">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">

                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Nama Lengkap Siswa *</label>
                                                    <input type="text" name="name" class="form-control text-sm" value="<?= e($s['name']) ?>" required>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">NISN (10 Digit) *</label>
                                                    <input type="text" name="nisn" class="form-control text-sm font-mono" value="<?= e($s['nisn']) ?>" required>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Kelas / Rombel *</label>
                                                    <select name="class_id" class="form-select text-sm" required>
                                                        <?php foreach ($classes as $c): ?>
                                                            <option value="<?= $c['id'] ?>" <?= $s['class_id'] == $c['id'] ? 'selected' : '' ?>>
                                                                <?= e($c['class_name']) ?> (<?= e($c['major']) ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Jenis Kelamin</label>
                                                    <select name="gender" class="form-select text-sm">
                                                        <option value="L" <?= $s['gender'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                        <option value="P" <?= $s['gender'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Username Login *</label>
                                                    <input type="text" name="username" class="form-control text-sm" value="<?= e($s['username']) ?>" required>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Alamat Email *</label>
                                                    <input type="email" name="email" class="form-control text-sm" value="<?= e($s['email']) ?>" required>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Ubah Sandi</label>
                                                    <input type="password" name="password" class="form-control text-sm" placeholder="Kosongkan jika tidak diubah">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Nomor Telepon / WA</label>
                                                    <input type="text" name="phone" class="form-control text-sm" value="<?= e($s['phone'] ?? '') ?>">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Status Akun</label>
                                                    <select name="is_active" class="form-select text-sm">
                                                        <option value="1" <?= (int)($s['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Aktif (Dapat Ujian)</option>
                                                        <option value="0" <?= (int)($s['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Nonaktif (Ditangguhkan)</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Alamat Domisili</label>
                                                    <input type="text" name="address" class="form-control text-sm" value="<?= e($s['address'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success text-white btn-sm px-3 font-semibold">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Delete Student -->
                        <div class="modal fade" id="modalDeleteStudent_<?= $s['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom bg-danger text-white">
                                        <h5 class="modal-title font-bold">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Siswa
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= base_url('admin/deleteStudent') ?>" method="POST">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">

                                        <div class="modal-body p-4 text-start">
                                            <p class="mb-2">Apakah Anda yakin ingin menghapus data peserta didik berikut?</p>
                                            <div class="p-3 bg-light rounded-3 border mb-3">
                                                <div class="font-bold text-dark"><?= e($s['name']) ?></div>
                                                <div class="text-xs text-muted">NISN: <?= e($s['nisn']) ?> | Kelas: <?= e($s['class_name']) ?></div>
                                            </div>
                                            <div class="alert alert-warning text-xs mb-0 py-2">
                                                <i class="bi bi-shield-alert me-1"></i> Seluruh riwayat asesmen, jawaban ujian, dan akun login siswa ini akan dihapus permanen.
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger btn-sm px-3 font-semibold">Ya, Hapus Siswa</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-backpack4 display-6 text-muted d-block mb-2"></i>
                            Tidak ada data peserta didik ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
            <small class="text-muted text-xs">
                Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= number_format($total) ?> Siswa)
            </small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= base_url('admin/students?page=' . ($page - 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($classId) ? '&class_id=' . $classId : '')) ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= base_url('admin/students?page=' . $i . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($classId) ? '&class_id=' . $classId : '')) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= base_url('admin/students?page=' . ($page + 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($classId) ? '&class_id=' . $classId : '')) ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Add Student -->
<div class="modal fade" id="modalAddStudent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-success text-white">
                <h5 class="modal-title font-bold">
                    <i class="bi bi-mortarboard-fill me-2"></i>Tambah Peserta Didik Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/createStudent') ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Nama Lengkap Siswa *</label>
                            <input type="text" name="name" class="form-control text-sm" placeholder="Contoh: Muhammad Ilham Pratama" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">NISN (10 Digit Angka) *</label>
                            <input type="text" name="nisn" class="form-control text-sm font-mono" placeholder="00xxxxxxxx" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Kelas / Rombongan Belajar *</label>
                            <select name="class_id" class="form-select text-sm" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['class_name']) ?> (<?= e($c['major']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Jenis Kelamin *</label>
                            <select name="gender" class="form-select text-sm" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Username Akun *</label>
                            <input type="text" name="username" class="form-control text-sm" placeholder="ilham.siswa" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Alamat Email *</label>
                            <input type="email" name="email" class="form-control text-sm" placeholder="ilham@siswa.sch.id" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Kata Sandi Awal *</label>
                            <input type="password" name="password" class="form-control text-sm" value="siswa123" required>
                            <small class="text-muted text-xs">Default: siswa123</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Nomor Telepon / WA</label>
                            <input type="text" name="phone" class="form-control text-sm" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-xs font-semibold text-secondary">Alamat Tempat Tinggal</label>
                            <textarea name="address" class="form-control text-sm" rows="2" placeholder="Alamat siswa..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-white btn-sm px-4 font-semibold">Simpan Siswa Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
