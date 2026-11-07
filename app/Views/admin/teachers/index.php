<?php
use App\Helpers\CsrfHelper;
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Master Data Guru Pengajar</h3>
        <p class="text-muted text-sm mb-0">Kelola informasi tenaga pendidik, NIP, akun akses login, dan penugasan bank soal.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-primary rounded-3 btn-sm px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddTeacher">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Guru Baru
        </button>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card-custom p-3 mb-4">
    <form action="<?= base_url('admin/teachers') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0 text-sm" placeholder="Cari nama guru, NIP, email, atau username..." value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-secondary btn-sm rounded-3 px-3">Cari</button>
            <?php if (!empty($search)): ?>
                <a href="<?= base_url('admin/teachers') ?>" class="btn btn-outline-secondary btn-sm rounded-3 ms-1">Reset</a>
            <?php endif; ?>
        </div>
        <div class="col text-md-end text-muted text-xs">
            Menampilkan total <strong><?= number_format($total) ?></strong> data guru
        </div>
    </form>
</div>

<!-- Teachers Table Card -->
<div class="card-custom p-4 mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-xs text-uppercase text-muted">
                    <th>Nama & Akun</th>
                    <th>NIP / NUPTK</th>
                    <th>Kontak / Email</th>
                    <th>Gender</th>
                    <th>Bank Soal / Ujian</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (!empty($teachers)): ?>
                    <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-info-subtle text-info font-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                        <?= strtoupper(substr($t['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-dark"><?= e($t['name']) ?></div>
                                        <small class="text-muted text-xs"><i class="bi bi-person me-1"></i><?= e($t['username']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="font-semibold text-dark"><?= e($t['nip'] ?: '-') ?></div>
                                <small class="text-muted text-xs">NUPTK: <?= e($t['nuptk'] ?: '-') ?></small>
                            </td>
                            <td>
                                <div class="text-dark"><?= e($t['email']) ?></div>
                                <small class="text-muted text-xs"><?= e($t['phone'] ?: '-') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    <?= ($t['gender'] === 'L') ? 'Laki-laki' : 'Perempuan' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary rounded-pill me-1 text-xs">
                                    <i class="bi bi-collection me-1"></i><?= $t['total_banks'] ?? 0 ?> Bank
                                </span>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill text-xs">
                                    <i class="bi bi-calendar me-1"></i><?= $t['total_exams'] ?? 0 ?> Ujian
                                </span>
                            </td>
                            <td>
                                <?php if ((int)($t['is_active'] ?? 1) === 1): ?>
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
                                    <button type="button" class="btn btn-outline-primary rounded-2 me-1" data-bs-toggle="modal" data-bs-target="#modalEditTeacher_<?= $t['id'] ?>" title="Edit Data Guru">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-2" data-bs-toggle="modal" data-bs-target="#modalDeleteTeacher_<?= $t['id'] ?>" title="Hapus Guru">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Teacher -->
                        <div class="modal fade" id="modalEditTeacher_<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom bg-light">
                                        <h5 class="modal-title font-bold text-dark">
                                            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Guru
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= base_url('admin/editTeacher') ?>" method="POST">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">

                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Nama Lengkap & Gelar *</label>
                                                    <input type="text" name="name" class="form-control text-sm" value="<?= e($t['name']) ?>" required>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Jenis Kelamin</label>
                                                    <select name="gender" class="form-select text-sm">
                                                        <option value="L" <?= $t['gender'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                        <option value="P" <?= $t['gender'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">NIP (Nomor Induk Pegawai)</label>
                                                    <input type="text" name="nip" class="form-control text-sm" value="<?= e($t['nip'] ?? '') ?>">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">NUPTK</label>
                                                    <input type="text" name="nuptk" class="form-control text-sm" value="<?= e($t['nuptk'] ?? '') ?>">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Username Login *</label>
                                                    <input type="text" name="username" class="form-control text-sm" value="<?= e($t['username']) ?>" required>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Alamat Email *</label>
                                                    <input type="email" name="email" class="form-control text-sm" value="<?= e($t['email']) ?>" required>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Ubah Kata Sandi</label>
                                                    <input type="password" name="password" class="form-control text-sm" placeholder="Kosongkan jika tidak ingin mengubah sandi">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Nomor Telepon / WhatsApp</label>
                                                    <input type="text" name="phone" class="form-control text-sm" value="<?= e($t['phone'] ?? '') ?>">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Status Akun</label>
                                                    <select name="is_active" class="form-select text-sm">
                                                        <option value="1" <?= (int)($t['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Aktif (Dapat Login)</option>
                                                        <option value="0" <?= (int)($t['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Nonaktif (Diblokir)</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label text-xs font-semibold text-secondary">Alamat Domisili</label>
                                                    <input type="text" name="address" class="form-control text-sm" value="<?= e($t['address'] ?? '') ?>">
                                                </div>
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

                        <!-- Modal Delete Teacher -->
                        <div class="modal fade" id="modalDeleteTeacher_<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom bg-danger text-white">
                                        <h5 class="modal-title font-bold">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Guru
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= base_url('admin/deleteTeacher') ?>" method="POST">
                                        <?= CsrfHelper::tokenField() ?>
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">

                                        <div class="modal-body p-4 text-start">
                                            <p class="mb-2">Apakah Anda yakin ingin menghapus data guru berikut?</p>
                                            <div class="p-3 bg-light rounded-3 border mb-3">
                                                <div class="font-bold text-dark"><?= e($t['name']) ?></div>
                                                <div class="text-xs text-muted">NIP: <?= e($t['nip'] ?: '-') ?> | Username: <?= e($t['username']) ?></div>
                                            </div>
                                            <div class="alert alert-warning text-xs mb-0 py-2">
                                                <i class="bi bi-shield-alert me-1"></i> Akun pengguna dan riwayat keterkaitan data akan dihapus secara permanen.
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger btn-sm px-3 font-semibold">Ya, Hapus Data</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-person-x display-6 text-muted d-block mb-2"></i>
                            Tidak ada data guru ditemukan.
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
                Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= number_format($total) ?> Guru)
            </small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= base_url('admin/teachers?page=' . ($page - 1) . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= base_url('admin/teachers?page=' . $i . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= base_url('admin/teachers?page=' . ($page + 1) . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Add Teacher -->
<div class="modal fade" id="modalAddTeacher" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-primary text-white">
                <h5 class="modal-title font-bold">
                    <i class="bi bi-person-plus-fill me-2"></i>Tambah Guru Pengajar Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/createTeacher') ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Nama Lengkap & Gelar *</label>
                            <input type="text" name="name" class="form-control text-sm" placeholder="Contoh: Dra. Hj. Siti Aminah, M.Pd." required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Jenis Kelamin *</label>
                            <select name="gender" class="form-select text-sm" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" name="nip" class="form-control text-sm" placeholder="18 digit angka NIP">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">NUPTK</label>
                            <input type="text" name="nuptk" class="form-control text-sm" placeholder="16 digit angka NUPTK">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Username Akun *</label>
                            <input type="text" name="username" class="form-control text-sm" placeholder="Contoh: siti.aminah" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Alamat Email Resmi *</label>
                            <input type="email" name="email" class="form-control text-sm" placeholder="siti.aminah@sma.sch.id" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Kata Sandi Awal *</label>
                            <input type="password" name="password" class="form-control text-sm" value="guru123" required>
                            <small class="text-muted text-xs">Default: guru123</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Nomor Telepon / WA</label>
                            <input type="text" name="phone" class="form-control text-sm" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-xs font-semibold text-secondary">Alamat Domisili</label>
                            <textarea name="address" class="form-control text-sm" rows="2" placeholder="Alamat tempat tinggal guru..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-semibold">Simpan Guru Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
