<?php
use App\Helpers\CsrfHelper;

$profile = $profile ?? [];
$logs    = $logs ?? [];
$role    = $profile['role'] ?? 'admin';
$name    = $profile['name'] ?? 'Administrator';
$initial = strtoupper(substr($name, 0, 1));
?>

<!-- Header & Breadcrumb -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 text-xs">
                <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Dasbor</a></li>
                <li class="breadcrumb-item active text-primary font-semibold" aria-current="page">Profil Administrator</li>
            </ol>
        </nav>
        <h3 class="font-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-person-badge-fill text-danger"></i> Profil Administrator Sistem
        </h3>
        <p class="text-muted text-sm mb-0">Kelola identitas akun super user, kredensial login, dan pantau log keamanan sistem SmartExam AI.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill font-semibold text-xs">
            <i class="bi bi-shield-shaded me-1"></i>Hak Akses Tertinggi (Super User)
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Kartu Identitas & Keamanan -->
    <div class="col-lg-4">
        <!-- Kartu Identitas Administrator -->
        <div class="card-custom p-4 mb-4 text-center">
            <div class="position-relative d-inline-block mx-auto mb-3">
                <div class="rounded-circle shadow d-flex align-items-center justify-content-center text-white font-bold"
                     style="width: 100px; height: 100px; font-size: 2.5rem; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border: 4px solid #fee2e2;">
                    <?= $initial ?>
                </div>
                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle" title="Status Akun Aktif">
                    <span class="visually-hidden">Online</span>
                </span>
            </div>

            <h5 class="font-bold text-dark mb-1"><?= e($profile['name'] ?? 'Admin') ?></h5>
            <div class="text-muted text-sm mb-2">@<?= e($profile['username'] ?? 'admin') ?></div>

            <div class="mb-3">
                <span class="badge badge-role-admin rounded-pill px-3 py-1.5 text-xs shadow-sm">
                    <i class="bi bi-shield-lock-fill me-1"></i>SUPER USER / ADMIN
                </span>
            </div>

            <hr class="my-3 opacity-25">

            <div class="text-start">
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-hash me-1"></i>ID Akun</span>
                    <span class="font-semibold text-dark">#<?= (int)($profile['id'] ?? 1) ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-envelope me-1"></i>Alamat Email</span>
                    <span class="font-semibold text-dark text-truncate" style="max-width: 170px;" title="<?= e($profile['email'] ?? '-') ?>"><?= e($profile['email'] ?? '-') ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-patch-check me-1"></i>Status Akun</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 text-xs font-semibold">Aktif</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-calendar-check me-1"></i>Terdaftar</span>
                    <span class="font-semibold text-dark"><?= !empty($profile['created_at']) ? date('d M Y, H:i', strtotime($profile['created_at'])) . ' WIB' : '01 Agu 2026' ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 text-xs">
                    <span class="text-muted"><i class="bi bi-clock-history me-1"></i>Pembaruan</span>
                    <span class="font-semibold text-dark"><?= !empty($profile['updated_at']) ? date('d M Y, H:i', strtotime($profile['updated_at'])) . ' WIB' : 'Terkini' ?></span>
                </div>
            </div>
        </div>

        <!-- Kartu Parameter Keamanan Sesi -->
        <div class="card-custom p-4">
            <h6 class="font-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success"></i> Status Keamanan Sistem
            </h6>
            <div class="p-3 bg-light rounded-3 mb-3 border">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-lock-fill text-primary"></i>
                    <span class="font-semibold text-xs text-dark">Enkripsi Kata Sandi</span>
                </div>
                <span class="text-xs text-muted">Standar Industri Bcrypt (Blowfish Cost 10)</span>
            </div>

            <div class="p-3 bg-light rounded-3 mb-3 border">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-key-fill text-warning"></i>
                    <span class="font-semibold text-xs text-dark">Proteksi CSRF & XSS</span>
                </div>
                <span class="text-xs text-muted">Token Sinkronisasi Sesi & HTTP Only Cookies</span>
            </div>

            <div class="p-3 bg-light rounded-3 border">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-broadcast text-info"></i>
                    <span class="font-semibold text-xs text-dark">Alamat IP Klien Anda</span>
                </div>
                <span class="text-xs font-mono text-dark font-semibold"><?= e($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') ?></span>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Formulir Pembaruan & Audit Log -->
    <div class="col-lg-8">
        <div class="card-custom border-0 shadow-sm p-4 bg-white rounded-4">
            <!-- Nav Tabs -->
            <ul class="nav nav-pills gap-2 mb-4 p-1 bg-light rounded-3" id="adminProfileTab" role="tablist">
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link active w-100 rounded-3 py-2 text-xs font-semibold d-flex align-items-center justify-content-center gap-2"
                            id="tab-biodata-btn" data-bs-toggle="pill" data-bs-target="#tab-biodata" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-person-fill fs-6"></i>
                        <span>Informasi Profil</span>
                    </button>
                </li>
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link w-100 rounded-3 py-2 text-xs font-semibold d-flex align-items-center justify-content-center gap-2"
                            id="tab-password-btn" data-bs-toggle="pill" data-bs-target="#tab-password" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-shield-lock-fill fs-6"></i>
                        <span>Keamanan & Sandi</span>
                    </button>
                </li>
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link w-100 rounded-3 py-2 text-xs font-semibold d-flex align-items-center justify-content-center gap-2"
                            id="tab-logs-btn" data-bs-toggle="pill" data-bs-target="#tab-logs" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-journal-text fs-6"></i>
                        <span>Jejak Audit</span>
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="adminProfileTabContent">
                <!-- Tab 1: Informasi Profil Akun -->
                <div class="tab-pane fade show active" id="tab-biodata" role="tabpanel" aria-labelledby="tab-biodata-btn">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="font-bold text-dark mb-1">Edit Biodata & Akun Administrator</h6>
                            <p class="text-muted text-xs mb-0">Pastikan alamat email dan username selalu valid untuk keperluan pemulihan dan administrasi.</p>
                        </div>
                    </div>

                    <form action="<?= base_url('admin/updateProfile') ?>" method="POST" autocomplete="off">
                        <?= CsrfHelper::tokenField() ?>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="adminName" class="form-label text-xs font-semibold text-secondary">Nama Lengkap Administrator <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="adminName" name="name" 
                                           value="<?= e($profile['name'] ?? '') ?>" required placeholder="Masukkan nama lengkap beserta gelar jika ada">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="adminUsername" class="form-label text-xs font-semibold text-secondary">Username Login <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted font-bold">@</span>
                                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="adminUsername" name="username" 
                                           value="<?= e($profile['username'] ?? '') ?>" required placeholder="Username unik login">
                                </div>
                                <small class="text-muted text-xs">Gunakan huruf kecil, angka, atau tanda titik tanpa spasi.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="adminEmail" class="form-label text-xs font-semibold text-secondary">Alamat Email Resmi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0 text-sm" id="adminEmail" name="email" 
                                           value="<?= e($profile['email'] ?? '') ?>" required placeholder="contoh@smartexam.sch.id">
                                </div>
                                <small class="text-muted text-xs">Digunakan untuk menerima notifikasi sistem & laporan insiden.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-xs font-semibold text-secondary">Peran Otoritas</label>
                                <input type="text" class="form-control bg-light text-sm" value="Super Administrator (Tingkat Sekolah)" readonly disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-xs font-semibold text-secondary">Status Keanggotaan</label>
                                <input type="text" class="form-control bg-light text-sm" value="Aktif (Terverifikasi)" readonly disabled>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary rounded-3 px-4 font-semibold text-sm d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-save2-fill"></i>
                                <span>Simpan Perubahan Profil</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Keamanan & Ganti Kata Sandi -->
                <div class="tab-pane fade" id="tab-password" role="tabpanel" aria-labelledby="tab-password-btn">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="font-bold text-dark mb-1">Perbarui Kata Sandi Akun</h6>
                            <p class="text-muted text-xs mb-0">Untuk menjaga keamanan tingkat tinggi, gunakan kombinasi minimal 6 karakter dengan huruf dan angka.</p>
                        </div>
                    </div>

                    <form action="<?= base_url('admin/updatePassword') ?>" method="POST" autocomplete="off">
                        <?= CsrfHelper::tokenField() ?>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="current_password" class="form-label text-xs font-semibold text-secondary">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0 text-sm" id="current_password" name="current_password" required placeholder="Masukkan kata sandi lama Anda">
                                    <button class="btn btn-light border border-start-0 text-muted toggle-password-btn" type="button" data-target="current_password">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="new_password" class="form-label text-xs font-semibold text-secondary">Kata Sandi Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0 text-sm" id="new_password" name="new_password" minlength="6" required placeholder="Minimal 6 karakter">
                                    <button class="btn btn-light border border-start-0 text-muted toggle-password-btn" type="button" data-target="new_password">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                                <small class="text-muted text-xs">Panjang sandi minimal 6 karakter.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label text-xs font-semibold text-secondary">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-check2-circle"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0 text-sm" id="confirm_password" name="confirm_password" minlength="6" required placeholder="Ulangi kata sandi baru">
                                    <button class="btn btn-light border border-start-0 text-muted toggle-password-btn" type="button" data-target="confirm_password">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                                <small class="text-muted text-xs">Ketik ulang persis dengan sandi baru.</small>
                            </div>
                        </div>

                        <div class="alert alert-warning d-flex align-items-center gap-2 mt-4 rounded-3 py-2 px-3 text-xs mb-0">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                            <div>Setelah kata sandi diganti, Anda tetap dapat melanjutkan sesi ini namun wajib mengingat sandi baru saat login kembali.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-danger rounded-3 px-4 font-semibold text-sm d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-shield-lock-fill"></i>
                                <span>Perbarui Kata Sandi</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 3: Jejak Audit Log Aktivitas -->
                <div class="tab-pane fade" id="tab-logs" role="tabpanel" aria-labelledby="tab-logs-btn">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="font-bold text-dark mb-1">Jejak Aktivitas Keamanan Akun</h6>
                            <p class="text-muted text-xs mb-0">Catatan riwayat aksi terkini yang dilakukan oleh akun administrator Anda.</p>
                        </div>
                        <span class="badge bg-light text-secondary border text-xs">Total: <?= count($logs) ?> Entri</span>
                    </div>

                    <?php if (empty($logs)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <div class="font-semibold text-sm">Belum ada riwayat jejak audit</div>
                            <small class="text-xs">Aktivitas penting sistem akan tercatat otomatis di sini.</small>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-xs mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Aksi</th>
                                        <th>Keterangan</th>
                                        <th>Alamat IP</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <?php
                                            $action = strtoupper($log['action'] ?? 'ACTIVITY');
                                            $badgeClass = match(true) {
                                                str_contains($action, 'LOGIN') => 'bg-success-subtle text-success border border-success-subtle',
                                                str_contains($action, 'PASSWORD') => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                str_contains($action, 'PROFILE') => 'bg-primary-subtle text-primary border border-primary-subtle',
                                                str_contains($action, 'SETTING') => 'bg-warning-subtle text-warning border border-warning-subtle',
                                                default => 'bg-light text-secondary border'
                                            };
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge <?= $badgeClass ?> font-semibold px-2 py-1 rounded-pill">
                                                    <?= e($action) ?>
                                                </span>
                                            </td>
                                            <td class="text-dark font-semibold"><?= e($log['description'] ?? '-') ?></td>
                                            <td class="text-muted font-mono"><?= e($log['ip_address'] ?? '127.0.0.1') ?></td>
                                            <td class="text-muted whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($log['created_at'] ?? 'now')) ?> WIB</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/Hide password toggles
    document.querySelectorAll('.toggle-password-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (!input) return;

            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            var icon = this.querySelector('i');
            if (icon) {
                icon.className = isPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
            }
        });
    });
});
</script>

