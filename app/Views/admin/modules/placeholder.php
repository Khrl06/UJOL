<?php
$logs = $logs ?? null;
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1"><?= e($moduleTitle ?? 'Modul Sistem') ?></h3>
        <p class="text-muted text-sm mb-0"><?= e($moduleDesc ?? 'Panel konfigurasi modul SmartExam AI.') ?></p>
    </div>
    <div>
        <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dasbor
        </a>
    </div>
</div>

<?php if ($logs !== null): ?>
    <!-- Audit Logs View -->
    <div class="card-custom p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="font-bold text-dark mb-0">Riwayat Jejak Audit Sistem</h5>
            <span class="badge bg-primary rounded-pill text-xs">50 Entri Terbaru</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-xs text-uppercase text-muted">
                        <th>Waktu Kejadian</th>
                        <th>Aksi (Event)</th>
                        <th>Pengguna</th>
                        <th>Deskripsi Aktivitas</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="font-mono text-xs text-muted">
                                <?= date('d M Y H:i:s', strtotime($l['created_at'])) ?>
                            </td>
                            <td>
                                <span class="badge <?= str_contains($l['action'], 'DENIED') ? 'bg-danger' : (str_contains($l['action'], 'CREATE') ? 'bg-success' : 'bg-primary') ?> text-white font-semibold text-xs">
                                    <?= e($l['action']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="font-semibold text-dark"><?= e($l['user_name'] ?? 'Guest / Anonim') ?></div>
                                <small class="text-muted text-xs"><?= e($l['user_email'] ?? '-') ?></small>
                            </td>
                            <td>
                                <span class="text-dark"><?= e($l['description']) ?></span>
                            </td>
                            <td class="font-mono text-xs text-muted"><?= e($l['ip_address'] ?? '127.0.0.1') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <!-- General Placeholder Banner -->
    <div class="card-custom p-5 text-center my-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-light text-primary rounded-circle p-4 mb-3 mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
            <i class="bi <?= e($moduleIcon ?? 'bi-gear-wide-connected') ?>"></i>
        </div>
        <h4 class="font-bold text-dark mb-2"><?= e($moduleTitle ?? 'Modul Dalam Pengembangan') ?></h4>
        <p class="text-muted text-sm mx-auto mb-4" style="max-width: 540px;">
            <?= e($moduleDesc ?? 'Modul ini disiapkan pada menu navigasi admin dan siap dikembangkan pada tahapan fungsional berikutnya.') ?>
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= base_url('admin/teachers') ?>" class="btn btn-outline-primary btn-sm rounded-3 px-3">
                <i class="bi bi-person-badge me-1"></i> Buka Master Guru
            </a>
            <a href="<?= base_url('admin/students') ?>" class="btn btn-outline-success btn-sm rounded-3 px-3">
                <i class="bi bi-mortarboard me-1"></i> Buka Master Siswa
            </a>
            <a href="<?= base_url('admin/classes') ?>" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
                <i class="bi bi-building me-1"></i> Buka Master Kelas
            </a>
        </div>
    </div>
<?php endif; ?>
