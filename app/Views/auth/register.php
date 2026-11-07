<?php
use App\Helpers\CsrfHelper;
$errors = $errors ?? [];
$old = $old ?? [];
$classes = $classes ?? [];
?>

<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-4 shadow-sm mb-3" style="width: 58px; height: 58px; font-size: 1.75rem;">
        <i class="bi bi-person-plus-fill"></i>
    </div>
    <h3 class="font-bold text-dark mb-1">Daftar Akun Baru</h3>
    <p class="text-muted text-sm mb-0">Lengkapi formulir untuk verifikasi pendaftaran akun ujian online</p>
</div>

<form action="<?= base_url('register') ?>" method="POST" novalidate id="registerForm">
    <?= CsrfHelper::tokenField() ?>

    <!-- Pilihan Role Akun (Siswa / Guru) -->
    <div class="mb-3">
        <label class="form-label text-xs font-semibold text-secondary d-block mb-1.5">Mendaftar Sebagai:</label>
        <div class="d-flex gap-2">
            <div class="flex-fill">
                <input type="radio" class="btn-check" name="role" id="roleSiswa" value="siswa" <?= ($old['role'] ?? 'siswa') === 'siswa' ? 'checked' : '' ?> onchange="toggleRoleFields('siswa')">
                <label class="btn btn-outline-primary w-100 py-2 rounded-3 text-xs font-semibold d-flex align-items-center justify-content-center gap-1.5" for="roleSiswa">
                    <i class="bi bi-mortarboard-fill"></i> Peserta Didik (Siswa)
                </label>
            </div>
            <div class="flex-fill">
                <input type="radio" class="btn-check" name="role" id="roleGuru" value="guru" <?= ($old['role'] ?? '') === 'guru' ? 'checked' : '' ?> onchange="toggleRoleFields('guru')">
                <label class="btn btn-outline-primary w-100 py-2 rounded-3 text-xs font-semibold d-flex align-items-center justify-content-center gap-1.5" for="roleGuru">
                    <i class="bi bi-person-video3"></i> Guru Pengajar
                </label>
            </div>
        </div>
    </div>

    <!-- Nama Lengkap -->
    <div class="mb-3">
        <label for="name" class="form-label text-xs font-semibold text-secondary">Nama Lengkap</label>
        <div class="input-group has-validation">
            <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3">
                <i class="bi bi-person"></i>
            </span>
            <input type="text" 
                   class="form-control bg-light border-start-0 rounded-end-3 <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                   id="name" 
                   name="name" 
                   value="<?= e($old['name'] ?? '') ?>"
                   placeholder="Masukkan nama lengkap Anda" 
                   required>
            <?php if (isset($errors['name'])): ?>
                <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['name']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <!-- Username -->
        <div class="col-md-6">
            <label for="username" class="form-label text-xs font-semibold text-secondary">Username</label>
            <div class="input-group has-validation">
                <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3">
                    <i class="bi bi-at"></i>
                </span>
                <input type="text" 
                       class="form-control bg-light border-start-0 rounded-end-3 <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                       id="username" 
                       name="username" 
                       value="<?= e($old['username'] ?? '') ?>"
                       placeholder="Username unik" 
                       required>
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['username']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nomor WhatsApp -->
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="phone" class="form-label text-xs font-semibold text-secondary mb-0">No. WhatsApp</label>
                <span class="badge bg-success-subtle text-success border border-success-subtle text-xs" style="font-size: 0.65rem;">
                    <i class="bi bi-whatsapp me-1"></i>OTP ke WhatsApp
                </span>
            </div>
            <div class="input-group has-validation">
                <span class="input-group-text bg-light text-success border-end-0 rounded-start-3">
                    <i class="bi bi-whatsapp"></i>
                </span>
                <input type="tel" 
                       class="form-control bg-light border-start-0 rounded-end-3 <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                       id="phone" 
                       name="phone" 
                       value="<?= e($old['phone'] ?? '') ?>"
                       placeholder="0812xxxxxxx" 
                       required>
                <?php if (isset($errors['phone'])): ?>
                    <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['phone']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-12 mt-1">
            <small class="text-muted text-xs d-block">
                <i class="bi bi-info-circle text-success me-1"></i>Kode verifikasi OTP 6-digit akan otomatis dikirimkan langsung ke WhatsApp nomor ini.
            </small>
        </div>
    </div>

    <!-- Bagian Khusus Siswa: Kelas & NISN -->
    <div id="siswaFields" style="<?= ($old['role'] ?? 'siswa') === 'guru' ? 'display: none;' : '' ?>">
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label for="class_id" class="form-label text-xs font-semibold text-secondary">Rombel / Kelas</label>
                <select class="form-select bg-light rounded-3 <?= isset($errors['class_id']) ? 'is-invalid' : '' ?>" id="class_id" name="class_id">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ((int)($old['class_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= e($c['class_name']) ?> (<?= e($c['major']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['class_id'])): ?>
                    <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['class_id']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="nisn" class="form-label text-xs font-semibold text-secondary">NISN</label>
                <input type="text" 
                       class="form-control bg-light rounded-3 <?= isset($errors['nisn']) ? 'is-invalid' : '' ?>" 
                       id="nisn" 
                       name="nisn" 
                       value="<?= e($old['nisn'] ?? '') ?>"
                       placeholder="10 digit NISN">
                <?php if (isset($errors['nisn'])): ?>
                    <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['nisn']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bagian Khusus Guru: NIP -->
    <div id="guruFields" style="<?= ($old['role'] ?? 'siswa') === 'guru' ? '' : 'display: none;' ?>">
        <div class="mb-3">
            <label for="nip" class="form-label text-xs font-semibold text-secondary">NIP / NUPTK</label>
            <input type="text" 
                   class="form-control bg-light rounded-3 <?= isset($errors['nip']) ? 'is-invalid' : '' ?>" 
                   id="nip" 
                   name="nip" 
                   value="<?= e($old['nip'] ?? '') ?>"
                   placeholder="Nomor Induk Pegawai">
            <?php if (isset($errors['nip'])): ?>
                <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['nip']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Jenis Kelamin -->
    <div class="mb-3">
        <label class="form-label text-xs font-semibold text-secondary d-block mb-1">Jenis Kelamin</label>
        <div class="d-flex gap-3">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" id="genderL" value="L" <?= ($old['gender'] ?? 'L') === 'L' ? 'checked' : '' ?>>
                <label class="form-check-label text-xs text-secondary font-semibold" for="genderL">Laki-laki</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" id="genderP" value="P" <?= ($old['gender'] ?? '') === 'P' ? 'checked' : '' ?>>
                <label class="form-check-label text-xs text-secondary font-semibold" for="genderP">Perempuan</label>
            </div>
        </div>
    </div>

    <!-- Kata Sandi & Konfirmasi -->
    <div class="row g-2 mb-4">
        <div class="col-md-6">
            <label for="password" class="form-label text-xs font-semibold text-secondary">Kata Sandi</label>
            <div class="input-group has-validation">
                <input type="password" 
                       class="form-control bg-light border-end-0 rounded-start-3 <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                       id="password" 
                       name="password" 
                       placeholder="Minimal 6 karakter" 
                       required>
                <button class="btn btn-light border border-start-0 rounded-end-3 text-muted" type="button" onclick="togglePassVisibility('password')">
                    <i class="bi bi-eye"></i>
                </button>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['password']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <label for="password_confirm" class="form-label text-xs font-semibold text-secondary">Ulangi Sandi</label>
            <div class="input-group has-validation">
                <input type="password" 
                       class="form-control bg-light border-end-0 rounded-start-3 <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" 
                       id="password_confirm" 
                       name="password_confirm" 
                       placeholder="Ketik ulang sandi" 
                       required>
                <button class="btn btn-light border border-start-0 rounded-end-3 text-muted" type="button" onclick="togglePassVisibility('password_confirm')">
                    <i class="bi bi-eye"></i>
                </button>
                <?php if (isset($errors['password_confirm'])): ?>
                    <div class="invalid-feedback text-xs mt-1 d-block"><?= e($errors['password_confirm']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 font-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-whatsapp"></i>
        <span>Lanjutkan & Kirim Kode OTP WhatsApp</span>
    </button>
</form>

<div class="text-center mt-3 pt-3 border-top">
    <p class="text-xs text-secondary mb-0">
        Sudah memiliki akun? 
        <a href="<?= base_url('login') ?>" class="text-primary font-bold text-decoration-none ms-1">
            Masuk ke Akun
        </a>
    </p>
</div>

<script>
function toggleRoleFields(role) {
    const siswaDiv = document.getElementById('siswaFields');
    const guruDiv = document.getElementById('guruFields');
    if (role === 'guru') {
        siswaDiv.style.display = 'none';
        guruDiv.style.display = 'block';
    } else {
        siswaDiv.style.display = 'block';
        guruDiv.style.display = 'none';
    }
}

function togglePassVisibility(id) {
    const input = document.getElementById(id);
    if (input) {
        input.type = (input.type === 'password') ? 'text' : 'password';
    }
}
</script>
