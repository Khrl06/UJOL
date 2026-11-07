<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge badge-role-admin rounded-pill px-3 py-1 text-xs">
                    <i class="bi bi-shield-lock-fill me-1"></i>ADMINISTRATOR UTAMA
                </span>
                <span class="text-white-50 text-xs">ID Sesi: <?= session_id() ?></span>
            </div>
            <h2 class="font-bold text-white mb-1">Halo, <?= e($user['name'] ?? 'Administrator') ?>!</h2>
            <p class="text-white-50 text-sm mb-0">Selamat datang di Panel Kendali Pusat SmartExam AI. Seluruh sistem berjalan dengan normal.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('admin/profile') ?>" class="btn btn-light btn-sm rounded-3 px-3 py-2 text-danger font-semibold">
                <i class="bi bi-person-badge-fill me-1"></i> Profil Saya
            </a>
            <a href="<?= base_url('admin/teachers') ?>" class="btn btn-outline-light btn-sm rounded-3 px-3 py-2 font-semibold">
                <i class="bi bi-person-plus me-1"></i> Tambah Guru
            </a>
            <a href="<?= base_url('admin/students') ?>" class="btn btn-outline-light btn-sm rounded-3 px-3 py-2 font-semibold">
                <i class="bi bi-mortarboard me-1"></i> Tambah Siswa
            </a>
        </div>
    </div>
</div>

<!-- =========================================================================
     8 KARTU STATISTIK REAL-TIME
     ========================================================================= -->
<div class="row g-3 mb-4">
    <!-- 1. Total Guru -->
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= base_url('admin/teachers') ?>" class="text-decoration-none">
            <div class="card-custom card-stat h-100">
                <div>
                    <span class="text-muted text-xs text-uppercase font-bold">Total Guru</span>
                    <h3 class="font-bold my-1 text-dark"><?= number_format($stats['total_teachers'] ?? 0) ?></h3>
                    <span class="text-info text-xs font-semibold"><i class="bi bi-arrow-right-circle me-1"></i>Tenaga Pendidik</span>
                </div>
                <div class="icon-box icon-box-info">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- 2. Total Peserta Didik -->
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= base_url('admin/students') ?>" class="text-decoration-none">
            <div class="card-custom card-stat h-100">
                <div>
                    <span class="text-muted text-xs text-uppercase font-bold">Total Peserta Didik</span>
                    <h3 class="font-bold my-1 text-dark"><?= number_format($stats['total_students'] ?? 0) ?></h3>
                    <span class="text-success text-xs font-semibold"><i class="bi bi-arrow-right-circle me-1"></i>Siswa Terdaftar</span>
                </div>
                <div class="icon-box icon-box-success">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- 3. Total Kelas -->
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= base_url('admin/classes') ?>" class="text-decoration-none">
            <div class="card-custom card-stat h-100">
                <div>
                    <span class="text-muted text-xs text-uppercase font-bold">Total Kelas</span>
                    <h3 class="font-bold my-1 text-dark"><?= number_format($stats['total_classes'] ?? 0) ?></h3>
                    <span class="text-primary text-xs font-semibold"><i class="bi bi-arrow-right-circle me-1"></i>Rombel Aktif</span>
                </div>
                <div class="icon-box icon-box-primary">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- 4. Total Mata Pelajaran -->
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= base_url('admin/subjects') ?>" class="text-decoration-none">
            <div class="card-custom card-stat h-100">
                <div>
                    <span class="text-muted text-xs text-uppercase font-bold">Total Mata Pelajaran</span>
                    <h3 class="font-bold my-1 text-dark"><?= number_format($stats['total_subjects'] ?? 0) ?></h3>
                    <span class="text-warning text-xs font-semibold"><i class="bi bi-arrow-right-circle me-1"></i>Kurikulum SMA</span>
                </div>
                <div class="icon-box icon-box-warning">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- 5. Total Bank Soal -->
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= base_url('admin/banks') ?>" class="text-decoration-none">
            <div class="card-custom card-stat h-100">
                <div>
                    <span class="text-muted text-xs text-uppercase font-bold">Total Bank Soal</span>
                    <h3 class="font-bold my-1 text-dark"><?= number_format($stats['total_question_banks'] ?? 0) ?></h3>
                    <span class="text-secondary text-xs font-semibold"><i class="bi bi-collection me-1"></i>Paket Soal Guru</span>
                </div>
                <div class="icon-box" style="background-color: #f1f5f9; color: #475569;">
                    <i class="bi bi-collection-fill"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- 6. Total Ujian -->
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= base_url('admin/exams') ?>" class="text-decoration-none">
            <div class="card-custom card-stat h-100">
                <div>
                    <span class="text-muted text-xs text-uppercase font-bold">Total Ujian</span>
                    <h3 class="font-bold my-1 text-dark"><?= number_format($stats['total_exams'] ?? 0) ?></h3>
                    <span class="text-secondary text-xs font-semibold"><i class="bi bi-calendar2-range me-1"></i>Semua Periode</span>
                </div>
                <div class="icon-box" style="background-color: #f3e8ff; color: #9333ea;">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- 7. Ujian Aktif -->
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= base_url('admin/monitoring') ?>" class="text-decoration-none">
            <div class="card-custom card-stat h-100">
                <div>
                    <span class="text-muted text-xs text-uppercase font-bold">Ujian Aktif</span>
                    <h3 class="font-bold my-1 text-dark"><?= number_format($stats['active_exams'] ?? 0) ?></h3>
                    <span class="text-danger text-xs font-semibold"><i class="bi bi-broadcast me-1"></i>Sedang Berjalan</span>
                </div>
                <div class="icon-box" style="background-color: #fee2e2; color: #dc2626;">
                    <i class="bi bi-broadcast-pin"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- 8. Peserta Sedang Ujian -->
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= base_url('admin/monitoring') ?>" class="text-decoration-none">
            <div class="card-custom card-stat h-100">
                <div>
                    <span class="text-muted text-xs text-uppercase font-bold">Peserta Sedang Ujian</span>
                    <h3 class="font-bold my-1 text-dark"><?= number_format($stats['active_exam_students'] ?? 0) ?></h3>
                    <span class="text-success text-xs font-semibold"><i class="bi bi-webcam-fill me-1"></i>Dual Cam Aktif</span>
                </div>
                <div class="icon-box" style="background-color: #dcfce7; color: #16a34a;">
                    <i class="bi bi-laptop"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- =========================================================================
     MAIN CONTENT SECTION: User Table & System / Audit Log
     ========================================================================= -->
<div class="row g-4 mb-4">
    <!-- User Management Preview -->
    <div class="col-12 col-xl-8">
        <div class="card-custom p-4">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                <div>
                    <h5 class="font-bold text-dark mb-0">Daftar Akun Pengguna Terdaftar</h5>
                    <small class="text-muted text-xs">Akun yang memiliki akses autentikasi ke dalam sistem</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('admin/teachers') ?>" class="btn btn-outline-info btn-sm rounded-3">
                        <i class="bi bi-person-video3 me-1"></i> Kelola Guru
                    </a>
                    <a href="<?= base_url('admin/students') ?>" class="btn btn-outline-success btn-sm rounded-3">
                        <i class="bi bi-backpack me-1"></i> Kelola Siswa
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-xs text-uppercase text-muted">
                            <th>Nama Lengkap</th>
                            <th>Username / Email</th>
                            <th>NIP / NISN</th>
                            <th>Peran (Role)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($userList as $u): ?>
                            <?php
                                $badgeRole = match($u['role'] ?? '') {
                                    'admin' => 'badge-role-admin',
                                    'guru'  => 'badge-role-guru',
                                    'siswa' => 'badge-role-siswa',
                                    default => 'bg-secondary'
                                };
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light border text-primary font-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-dark"><?= e($u['name']) ?></div>
                                            <small class="text-muted text-xs"><?= e($u['class_name'] ?? '-') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-semibold text-dark"><?= e($u['username']) ?></div>
                                    <small class="text-muted text-xs"><?= e($u['email']) ?></small>
                                </td>
                                <td><?= e($u['nisn_nip'] ?? '-') ?></td>
                                <td>
                                    <span class="badge <?= $badgeRole ?> rounded-pill px-2.5 py-1 text-xs">
                                        <?= strtoupper($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ((int)($u['is_active'] ?? 1) === 1): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 text-xs">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 text-xs">
                                            <i class="bi bi-dash-circle-fill me-1" style="font-size: 6px;"></i>Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Status & Recent Audit Logs -->
    <div class="col-12 col-xl-4">
        <!-- Status Server -->
        <div class="card-custom p-4 mb-4">
            <h5 class="font-bold text-dark mb-3">
                <i class="bi bi-hdd-network text-primary me-2"></i>Status Server & Sistem
            </h5>
            <ul class="list-group list-group-flush text-sm">
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                    <span class="text-muted">Arsitektur</span>
                    <span class="badge bg-primary-subtle text-primary font-semibold">MVC Pure PHP</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                    <span class="text-muted">Versi PHP</span>
                    <span class="font-semibold text-dark"><?= $systemInfo['php_version'] ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                    <span class="text-muted">Database MySQL</span>
                    <span class="badge bg-success-subtle text-success font-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i>smartexam_db Aktif
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                    <span class="text-muted">Framework UI</span>
                    <span class="font-semibold text-dark">Bootstrap 5.3.3</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">Keamanan RBAC</span>
                    <span class="badge bg-success font-semibold">Aktif & Terproteksi</span>
                </li>
            </ul>
        </div>

        <!-- Recent Audit Log Widget -->
        <div class="card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="font-bold text-dark mb-0">
                    <i class="bi bi-shield-check text-warning me-1"></i>Log Keamanan Terbaru
                </h6>
                <a href="<?= base_url('admin/logs') ?>" class="text-xs text-primary font-semibold text-decoration-none">Lihat Semua</a>
            </div>
            <div class="d-flex flex-column gap-2 text-xs">
                <?php if (!empty($recentLogs)): ?>
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="p-2 rounded-3 bg-light border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge <?= str_contains($log['action'], 'DENIED') ? 'bg-danger' : 'bg-primary' ?> text-white font-semibold" style="font-size: 0.65rem;">
                                    <?= e($log['action']) ?>
                                </span>
                                <span class="text-muted" style="font-size: 0.65rem;"><?= date('H:i d/m', strtotime($log['created_at'])) ?></span>
                            </div>
                            <div class="text-dark font-semibold text-truncate"><?= e($log['description']) ?></div>
                            <small class="text-muted"><?= e($log['user_name'] ?? 'System') ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">Belum ada aktivitas audit.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
