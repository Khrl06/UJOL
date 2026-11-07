<?php
use App\Helpers\CsrfHelper;
$errors = $errors ?? [];
$oldUsername = $oldUsername ?? '';
$isDbConnected = $isDbConnected ?? true;
?>

<!-- Brand Header -->
<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-4 shadow-sm mb-3" style="width: 58px; height: 58px; font-size: 1.75rem;">
        <i class="bi bi-shield-shaded"></i>
    </div>
    <h3 class="font-bold text-dark mb-1"><?= APP_NAME ?></h3>
    <p class="text-muted text-sm mb-2"><?= APP_TAGLINE ?></p>
    <div class="d-flex align-items-center justify-content-center gap-2">
        <span class="badge bg-light text-primary border font-semibold text-xs">SMA / SMK / MA</span>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-xs" title="Sistem Ujian Online Terintegrasi">
            <i class="bi bi-shield-check me-1"></i>Portal Resmi
        </span>
    </div>
</div>

<!-- Login Form -->
<form action="<?= base_url('login') ?>" method="POST" class="mb-3" novalidate>
    <?= CsrfHelper::tokenField() ?>

    <div class="mb-3">
        <label for="username" class="form-label text-sm font-semibold text-secondary">Username atau No. WhatsApp</label>
        <div class="input-group has-validation">
            <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3">
                <i class="bi bi-person-circle"></i>
            </span>
            <input type="text" 
                   class="form-control bg-light border-start-0 rounded-end-3 <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                   id="username" 
                   name="username" 
                   value="<?= e($oldUsername) ?>"
                   placeholder="Masukkan username atau nomor WhatsApp" 
                   required 
                   autofocus>
            <?php if (isset($errors['username'])): ?>
                <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['username']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label text-sm font-semibold text-secondary mb-0">Kata Sandi</label>
            <a href="#" onclick="alert('Untuk bantuan reset sandi silakan hubungi tim proktor/administrator sekolah.'); return false;" class="text-xs text-primary text-decoration-none font-semibold">Lupa sandi?</a>
        </div>
        <div class="input-group has-validation">
            <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3">
                <i class="bi bi-lock"></i>
            </span>
            <input type="password" 
                   class="form-control bg-light border-start-0 border-end-0 <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                   id="password" 
                   name="password" 
                   placeholder="Masukkan kata sandi" 
                   required>
            <button class="btn btn-light border border-start-0 rounded-end-3 text-muted" type="button" id="togglePassword">
                <i class="bi bi-eye"></i>
            </button>
            <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['password']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Remember Me Checkbox -->
    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember_me" value="1" id="rememberMe">
        <label class="form-check-label text-xs text-secondary font-semibold" for="rememberMe">
            Ingat saya di perangkat ini (30 hari)
        </label>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 font-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
        <span>Masuk ke Akun</span>
        <i class="bi bi-arrow-right"></i>
    </button>
</form>

<div class="text-center mt-3 mb-1">
    <p class="text-xs text-secondary mb-0">
        Belum memiliki akun? 
        <a href="<?= base_url('register') ?>" class="text-primary font-bold text-decoration-none ms-1">
            Daftar Akun Baru
        </a>
    </p>
</div>

<div class="text-center mt-4 pt-3 border-top">
    <p class="text-xs text-muted mb-0">
        &copy; <?= date('Y') ?> <?= APP_NAME ?>. Sistem Asesmen Ujian Berbasis Komputer & AI.
    </p>
</div>
