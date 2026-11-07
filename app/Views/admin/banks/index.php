<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Bank Soal Global Sekolah</h3>
        <p class="text-muted text-sm mb-0">Pengawasan terpusat paket bank soal seluruh mata pelajaran yang disusun oleh dewan guru SMA.</p>
    </div>
    <div>
        <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dasbor
        </a>
    </div>
</div>

<!-- Filter & Search Card -->
<div class="card-custom p-3 mb-4">
    <form action="<?= base_url('admin/banks') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0 text-sm" placeholder="Cari judul bank soal atau nama guru..." value="<?= e($search) ?>">
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

        <div class="col-12 col-md-3">
            <select name="teacher_id" class="form-select bg-light text-sm" onchange="this.form.submit()">
                <option value="">-- Semua Guru Penyusun --</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $teacherId == $t['id'] ? 'selected' : '' ?>>
                        <?= e($t['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-secondary btn-sm rounded-3 px-3">Filter</button>
            <?php if (!empty($search) || !empty($subjectId) || !empty($teacherId)): ?>
                <a href="<?= base_url('admin/banks') ?>" class="btn btn-outline-secondary btn-sm rounded-3 ms-1">Reset</a>
            <?php endif; ?>
        </div>

        <div class="col text-md-end text-muted text-xs">
            Total <strong><?= number_format($total) ?></strong> bank soal
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card-custom p-4 mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-xs text-uppercase text-muted">
                    <th>Judul Bank Soal</th>
                    <th>Mata Pelajaran</th>
                    <th>Guru Penyusun</th>
                    <th>Sasaran Kelas</th>
                    <th>Rincian Soal</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (!empty($banks)): ?>
                    <?php foreach ($banks as $b): ?>
                        <tr>
                            <td>
                                <div class="font-bold text-dark mb-0.5"><?= e($b['title']) ?></div>
                                <small class="text-muted text-xs text-truncate d-block" style="max-width: 280px;">
                                    <?= e($b['description'] ?: 'Tidak ada kisi-kisi khusus.') ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtxet text-primary bg-primary-subtle rounded-pill px-2.5 py-1 text-xs font-semibold">
                                    <?= e($b['subject_name']) ?> (<?= e($b['subject_code']) ?>)
                                </span>
                            </td>
                            <td>
                                <div class="font-semibold text-dark"><?= e($b['teacher_name']) ?></div>
                                <small class="text-muted text-xs">NIP: <?= e($b['teacher_nip'] ?: '-') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-mono text-xs">
                                    Kelas <?= e($b['grade_level'] ?? '12') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light border text-dark text-xs py-1 px-2 me-1">
                                    <i class="bi bi-list-check text-primary me-1"></i><?= $b['mc_count'] ?? 0 ?> PG
                                </span>
                                <span class="badge bg-light border text-dark text-xs py-1 px-2">
                                    <i class="bi bi-pencil-square text-info me-1"></i><?= $b['essay_count'] ?? 0 ?> Essay
                                </span>
                            </td>
                            <td>
                                <?php if ((int)($b['is_active'] ?? 1) === 1): ?>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 text-xs">
                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 text-xs">
                                        <i class="bi bi-dash-circle-fill me-1"></i>Draft
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted text-xs font-mono">
                                <?= date('d/m/Y', strtotime($b['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x display-6 text-muted d-block mb-2"></i>
                            Tidak ada bank soal ditemukan.
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
                Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= number_format($total) ?> Paket Soal)
            </small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= base_url('admin/banks?page=' . ($page - 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($subjectId) ? '&subject_id=' . $subjectId : '') . (!empty($teacherId) ? '&teacher_id=' . $teacherId : '')) ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= base_url('admin/banks?page=' . $i . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($subjectId) ? '&subject_id=' . $subjectId : '') . (!empty($teacherId) ? '&teacher_id=' . $teacherId : '')) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= base_url('admin/banks?page=' . ($page + 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($subjectId) ? '&subject_id=' . $subjectId : '') . (!empty($teacherId) ? '&teacher_id=' . $teacherId : '')) ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
