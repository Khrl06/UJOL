
<div class="d-flex flex-column align-items-center justify-content-center py-5 text-center">
    <div class="rounded-circle bg-danger-subtle text-danger p-4 mb-3 d-flex align-items-center justify-content-center" style="width: 90px; height: 90px; font-size: 2.5rem;">
        <i class="bi bi-exclamation-octagon"></i>
    </div>
    <h1 class="display-4 font-bold text-dark mb-2">404</h1>
    <h4 class="font-bold text-secondary mb-2">Halaman Tidak Ditemukan</h4>
    <p class="text-muted text-sm max-w-md mb-4" style="max-width: 480px;">
        <?= htmlspecialchars($message ?? 'Halaman yang Anda tuju tidak tersedia atau telah dipindahkan.') ?>
    </p>
    <a href="<?= base_url() ?>" class="btn btn-primary rounded-3 px-4 py-2 font-semibold shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
    </a>
</div>
