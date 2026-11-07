<?php
use App\Helpers\CsrfHelper;
$phone = $phone ?? '';
$error = $error ?? '';
$devOtp = $devOtp ?? null;
?>

<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle shadow-sm mb-3" style="width: 64px; height: 64px; font-size: 2rem;">
        <i class="bi bi-whatsapp"></i>
    </div>
    <h3 class="font-bold text-dark mb-1">Verifikasi WhatsApp Anda</h3>
    <p class="text-muted text-sm mb-2">Masukkan 6-digit kode verifikasi OTP yang telah kami kirimkan via WhatsApp ke nomor:</p>
    <div class="d-inline-block bg-light border border-success-subtle rounded-pill px-3 py-1 font-semibold text-xs text-success mb-2">
        <i class="bi bi-whatsapp me-1"></i><?= e($phone) ?>
    </div>
</div>

<!-- OTP Submission Form -->
<form action="<?= base_url('verify-otp') ?>" method="POST" class="mb-3" id="otpForm" novalidate>
    <?= CsrfHelper::tokenField() ?>
    <input type="hidden" name="phone" value="<?= e($phone) ?>">

    <div class="mb-4">
        <label for="otp_code" class="form-label text-xs font-semibold text-secondary d-block text-center mb-2">
            6-DIGIT KODE OTP WHATSAPP
        </label>
        <div class="d-flex justify-content-center">
            <input type="text" 
                   class="form-control text-center bg-light border-2 rounded-3 font-mono <?= !empty($error) ? 'is-invalid border-danger' : 'border-success-subtle' ?>" 
                   id="otp_code" 
                   name="otp_code" 
                   maxlength="6" 
                   pattern="[0-9]{6}" 
                   inputmode="numeric" 
                   autocomplete="one-time-code"
                   placeholder="------" 
                   style="font-size: 2rem; font-weight: 800; letter-spacing: 0.6rem; max-width: 280px; padding: 0.5rem;"
                   required 
                   autofocus>
        </div>
        <?php if (!empty($error)): ?>
            <div class="text-danger text-center text-xs mt-2 d-block font-semibold">
                <i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?>
            </div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 font-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-shield-fill-check"></i>
        <span>Verifikasi & Aktifkan Akun</span>
    </button>
</form>

<!-- Resend OTP Section -->
<div class="text-center pt-2">
    <form action="<?= base_url('resend-otp') ?>" method="POST" id="resendForm" class="d-inline">
        <?= CsrfHelper::tokenField() ?>
        <input type="hidden" name="phone" value="<?= e($phone) ?>">
        <p class="text-xs text-muted mb-1">
            Tidak menerima kode di WhatsApp? 
            <button type="submit" id="btnResend" class="btn btn-link text-success p-0 text-xs font-bold text-decoration-none">
                Kirim Ulang Kode OTP
            </button>
        </p>
    </form>
    <div class="mt-2">
        <a href="<?= base_url('register') ?>" class="text-xs text-secondary text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Ganti Nomor WhatsApp / Kembali
        </a>
    </div>
</div>

<script>
// Auto format digit input
document.getElementById('otp_code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length === 6) {
        document.getElementById('otpForm').submit();
    }
});
</script>
