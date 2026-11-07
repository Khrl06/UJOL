<div class="d-flex flex-column align-items-center justify-content-center py-5 text-center">
    <div class="rounded-circle bg-danger-subtle text-danger p-4 mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 96px; height: 96px; font-size: 2.75rem;">
        <i class="bi bi-shield-x"></i>
    </div>
    
    <div class="badge bg-danger text-white px-3 py-1.5 rounded-pill font-semibold text-xs mb-2">
        <i class="bi bi-lock-fill me-1"></i>KODE 403: FORBIDDEN
    </div>

    <h2 class="display-6 font-bold text-dark mb-2">Akses Modul Ditolak</h2>
    
    <div class="card-custom p-4 my-3 text-start mx-auto shadow-sm" style="max-width: 540px; border-left: 4px solid #dc2626;">
        <p class="text-secondary text-sm mb-3">
            <?= htmlspecialchars($message ?? 'Anda tidak memiliki hak otorisasi (privilege) untuk membuka halaman modul ini.') ?>
        </p>
        <div class="d-flex flex-wrap gap-2 text-xs">
            <span class="text-muted">Peran Anda:</span>
            <span class="badge bg-secondary rounded-pill"><?= strtoupper($currentRole ?? 'GUEST') ?></span>
            <?php if (!empty($requiredRole)): ?>
                <span class="text-muted ms-2">Diperlukan:</span>
                <span class="badge bg-danger rounded-pill"><?= strtoupper($requiredRole) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <p class="text-muted text-xs mb-4">
        Percobaan akses lintas peran dicatat ke dalam log audit keamanan sistem untuk menjaga integritas asesmen.
    </p>

    <div class="d-flex gap-2">
        <a href="<?= base_url('dashboard') ?>" class="btn btn-primary rounded-3 px-4 py-2 font-semibold shadow-sm text-sm">
            <i class="bi bi-house-door-fill me-1"></i> Kembali ke Dasbor Saya
        </a>
        <a href="<?= base_url('logout') ?>" class="btn btn-outline-secondary rounded-3 px-3 py-2 text-sm">
            <i class="bi bi-box-arrow-right me-1"></i> Ganti Akun
        </a>
    </div>
</div>
