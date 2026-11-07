<?php
use App\Helpers\CsrfHelper;

$profile = $profile ?? [];
$teacher = $teacher ?? [];
$stats   = $stats ?? [];
$banks   = $banks ?? [];
$exams   = $exams ?? [];
$logs    = $logs ?? [];

$name    = $profile['name'] ?? 'Budi Prasetyo, S.Pd., M.Kom.';
$initial = strtoupper(substr($name, 0, 1));
$gender  = $teacher['gender'] ?? ($profile['teacher_gender'] ?? 'L');
$phone   = $teacher['phone'] ?? ($profile['teacher_phone'] ?? '');
$address = $teacher['address'] ?? ($profile['teacher_address'] ?? '');
$nip     = $teacher['nip'] ?? ($profile['nip'] ?? '');
$nuptk   = $teacher['nuptk'] ?? ($profile['nuptk'] ?? '');
?>

<!-- Header & Breadcrumb -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 text-xs">
                <li class="breadcrumb-item"><a href="<?= base_url('teacher/dashboard') ?>" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Dasbor</a></li>
                <li class="breadcrumb-item active text-primary font-semibold" aria-current="page">Profil Pendidik</li>
            </ol>
        </nav>
        <h3 class="font-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-person-badge-fill text-info"></i> Profil Guru Pengajar
        </h3>
        <p class="text-muted text-sm mb-0">Kelola identitas pendidik, kontak WhatsApp resmi, serta pengaturan keamanan akun pengajar.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill font-semibold text-xs">
            <i class="bi bi-award-fill me-1"></i>Staf Pengajar Terverifikasi
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Kartu Identitas & Statistik Guru -->
    <div class="col-lg-4">
        <!-- Kartu Identitas Guru -->
        <div class="card-custom p-4 mb-4 text-center">
            <div class="position-relative d-inline-block mx-auto mb-3">
                <div class="rounded-circle shadow d-flex align-items-center justify-content-center text-white font-bold"
                     style="width: 100px; height: 100px; font-size: 2.5rem; background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); border: 4px solid #e0f2fe;">
                    <?= $initial ?>
                </div>
                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle" title="Status Akun Aktif">
                    <span class="visually-hidden">Online</span>
                </span>
            </div>

            <h5 class="font-bold text-dark mb-1"><?= e($name) ?></h5>
            <div class="text-muted text-sm mb-2">@<?= e($profile['username'] ?? 'guru') ?></div>

            <div class="mb-3 d-flex flex-wrap justify-content-center gap-1">
                <span class="badge badge-role-guru rounded-pill px-3 py-1.5 text-xs shadow-sm">
                    <i class="bi bi-person-badge-fill me-1"></i>GURU PENGAJAR
                </span>
                <?php if (!empty($nip)): ?>
                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 text-xs">
                        NIP: <?= e($nip) ?>
                    </span>
                <?php endif; ?>
            </div>

            <hr class="my-3 opacity-25">

            <div class="text-start">
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-envelope me-1"></i>Email</span>
                    <span class="font-semibold text-dark text-truncate" style="max-width: 170px;" title="<?= e($profile['email'] ?? '-') ?>"><?= e($profile['email'] ?? '-') ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-whatsapp me-1 text-success"></i>WhatsApp</span>
                    <span class="font-semibold text-dark"><?= !empty($phone) ? e($phone) : '<span class="text-muted fst-italic">Belum diatur</span>' ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-gender-ambiguous me-1"></i>Jenis Kelamin</span>
                    <span class="font-semibold text-dark"><?= $gender === 'P' ? 'Perempuan' : 'Laki-laki' ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-card-checklist me-1"></i>NUPTK</span>
                    <span class="font-semibold text-dark"><?= !empty($nuptk) ? e($nuptk) : '-' ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 text-xs">
                    <span class="text-muted"><i class="bi bi-patch-check me-1"></i>Status</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 text-xs font-semibold">Aktif</span>
                </div>
            </div>
        </div>

        <!-- Kartu Statistik Akademik Guru -->
        <div class="card-custom p-4">
            <h6 class="font-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-graph-up text-primary"></i> Statistik Asesmen Guru
            </h6>
            <div class="row g-2 text-center">
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="font-bold text-primary fs-4"><?= (int)($stats['total_banks'] ?? 0) ?></div>
                        <small class="text-muted text-xs font-semibold">Bank Soal</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="font-bold text-success fs-4"><?= (int)($stats['total_exams'] ?? 0) ?></div>
                        <small class="text-muted text-xs font-semibold">Paket Ujian</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="font-bold text-info fs-4"><?= (int)($stats['total_questions'] ?? 0) ?></div>
                        <small class="text-muted text-xs font-semibold">Butir Soal</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="font-bold text-warning fs-4"><?= (int)($stats['total_participants'] ?? 0) ?></div>
                        <small class="text-muted text-xs font-semibold">Peserta Ujian</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Formulir Pembaruan & Aktivitas -->
    <div class="col-lg-8">
        <div class="card-custom border-0 shadow-sm p-4 bg-white rounded-4">
            <!-- Nav Tabs -->
            <ul class="nav nav-pills gap-2 mb-4 p-1 bg-light rounded-3" id="teacherProfileTab" role="tablist">
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link active w-100 rounded-3 py-2 text-xs font-semibold d-flex align-items-center justify-content-center gap-2"
                            id="tab-biodata-btn" data-bs-toggle="pill" data-bs-target="#tab-biodata" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-person-lines-fill fs-6"></i>
                        <span>Biodata Pendidik</span>
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
                            id="tab-activities-btn" data-bs-toggle="pill" data-bs-target="#tab-activities" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-collection-play-fill fs-6"></i>
                        <span>Paket Asesmen Saya</span>
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="teacherProfileTabContent">
                <!-- Tab 1: Biodata Guru -->
                <div class="tab-pane fade show active" id="tab-biodata" role="tabpanel" aria-labelledby="tab-biodata-btn">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="font-bold text-dark mb-1">Informasi Lengkap Pendidik</h6>
                            <p class="text-muted text-xs mb-0">Nomor WhatsApp wajib aktif agar sistem dapat mengirimkan notifikasi pelaksanaan ujian dan hasil penilaian.</p>
                        </div>
                    </div>

                    <form action="<?= base_url('teacher/updateProfile') ?>" method="POST" autocomplete="off">
                        <?= CsrfHelper::tokenField() ?>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="teacherName" class="form-label text-xs font-semibold text-secondary">Nama Lengkap & Gelar Akademik <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="teacherName" name="name" 
                                           value="<?= e($name) ?>" required placeholder="Contoh: Budi Prasetyo, S.Pd., M.Kom.">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="teacherUsername" class="form-label text-xs font-semibold text-secondary">Username Login <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted font-bold">@</span>
                                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="teacherUsername" name="username" 
                                           value="<?= e($profile['username'] ?? '') ?>" required placeholder="Username unik">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="teacherEmail" class="form-label text-xs font-semibold text-secondary">Alamat Email Pendidik <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0 text-sm" id="teacherEmail" name="email" 
                                           value="<?= e($profile['email'] ?? '') ?>" required placeholder="email@sekolah.sch.id">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="teacherNip" class="form-label text-xs font-semibold text-secondary">Nomor Induk Pegawai (NIP)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-card-text"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="teacherNip" name="nip" 
                                           value="<?= e($nip) ?>" placeholder="19xxxxxxxxxxxxxxx">
                                </div>
                                <small class="text-muted text-xs">Kosongkan bila belum memiliki NIP resmi.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="teacherNuptk" class="form-label text-xs font-semibold text-secondary">NUPTK Pendidik</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-award"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="teacherNuptk" name="nuptk" 
                                           value="<?= e($nuptk) ?>" placeholder="16 digit NUPTK">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-xs font-semibold text-secondary">Jenis Kelamin</label>
                                <div class="d-flex align-items-center gap-4 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="genderL" value="L" <?= $gender !== 'P' ? 'checked' : '' ?>>
                                        <label class="form-check-label text-sm" for="genderL">Laki-laki</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="genderP" value="P" <?= $gender === 'P' ? 'checked' : '' ?>>
                                        <label class="form-check-label text-sm" for="genderP">Perempuan</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="teacherPhone" class="form-label text-xs font-semibold text-secondary">Nomor WhatsApp / HP Aktif</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-success"><i class="bi bi-whatsapp"></i></span>
                                    <input type="tel" class="form-control border-start-0 ps-0 text-sm" id="teacherPhone" name="phone" 
                                           value="<?= e($phone) ?>" placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx">
                                </div>
                                <small class="text-muted text-xs">Penting: Diperlukan untuk menerima pesan otomatis sistem via WhatsApp Gateway.</small>
                            </div>

                            <div class="col-12">
                                <label for="teacherAddress" class="form-label text-xs font-semibold text-secondary">Alamat Tempat Tinggal</label>
                                <textarea class="form-control text-sm" id="teacherAddress" name="address" rows="2" placeholder="Alamat domisili pendidik"><?= e($address) ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary rounded-3 px-4 font-semibold text-sm d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-save2-fill"></i>
                                <span>Simpan Biodata Pendidik</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Keamanan & Ganti Sandi -->
                <div class="tab-pane fade" id="tab-password" role="tabpanel" aria-labelledby="tab-password-btn">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="font-bold text-dark mb-1">Ubah Kata Sandi Guru</h6>
                            <p class="text-muted text-xs mb-0">Pastikan Anda menjaga kerahasiaan kata sandi agar paket ujian dan bank soal tetap aman.</p>
                        </div>
                    </div>

                    <form action="<?= base_url('teacher/updatePassword') ?>" method="POST" autocomplete="off">
                        <?= CsrfHelper::tokenField() ?>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="teacher_current_password" class="form-label text-xs font-semibold text-secondary">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0 text-sm" id="teacher_current_password" name="current_password" required placeholder="Masukkan kata sandi lama">
                                    <button class="btn btn-light border border-start-0 text-muted toggle-password-btn" type="button" data-target="teacher_current_password">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="teacher_new_password" class="form-label text-xs font-semibold text-secondary">Kata Sandi Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0 text-sm" id="teacher_new_password" name="new_password" minlength="6" required placeholder="Minimal 6 karakter">
                                    <button class="btn btn-light border border-start-0 text-muted toggle-password-btn" type="button" data-target="teacher_new_password">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="teacher_confirm_password" class="form-label text-xs font-semibold text-secondary">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-check2-circle"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0 text-sm" id="teacher_confirm_password" name="confirm_password" minlength="6" required placeholder="Ketik ulang sandi baru">
                                    <button class="btn btn-light border border-start-0 text-muted toggle-password-btn" type="button" data-target="teacher_confirm_password">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info d-flex align-items-center gap-2 mt-4 rounded-3 py-2 px-3 text-xs mb-0">
                            <i class="bi bi-shield-check fs-5 text-primary"></i>
                            <div>Sistem menggunakan algoritma enkripsi satu arah Bcrypt untuk melindungi kata sandi pengajar.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary rounded-3 px-4 font-semibold text-sm d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-shield-lock-fill"></i>
                                <span>Perbarui Kata Sandi</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 3: Paket Ujian & Bank Soal -->
                <div class="tab-pane fade" id="tab-activities" role="tabpanel" aria-labelledby="tab-activities-btn">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="font-bold text-dark mb-1">Paket Ujian & Bank Soal Terkini</h6>
                            <p class="text-muted text-xs mb-0">Ringkasan aktivitas materi ujian yang Anda buat di sistem SmartExam AI.</p>
                        </div>
                        <a href="<?= base_url('teacher/banks') ?>" class="btn btn-outline-primary btn-sm rounded-3 text-xs font-semibold">
                            <i class="bi bi-folder-plus me-1"></i>Kelola Bank Soal
                        </a>
                    </div>

                    <div class="mb-4">
                        <h6 class="font-bold text-dark text-xs text-uppercase tracking-wider mb-2 text-secondary">5 Bank Soal Terakhir:</h6>
                        <?php if (empty($banks)): ?>
                            <div class="p-3 bg-light rounded-3 text-muted text-xs text-center border">
                                Belum ada bank soal yang dibuat. Klik tombol di atas untuk membuat paket soal baru.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-xs mb-0 border rounded-3 overflow-hidden">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Judul Bank Soal</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Kelas</th>
                                            <th>Total Soal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($banks as $b): ?>
                                            <tr>
                                                <td class="font-semibold text-dark"><?= e($b['title'] ?? '-') ?></td>
                                                <td><span class="badge bg-light text-primary border"><?= e($b['subject_name'] ?? '-') ?></span></td>
                                                <td>Tingkat <?= e($b['grade_level'] ?? '12') ?></td>
                                                <td><span class="font-bold text-dark"><?= (int)($b['total_questions'] ?? 0) ?></span> Butir</td>
                                                <td>
                                                    <a href="<?= base_url('teacher/bankQuestions?bank_id=' . ($b['id'] ?? 0)) ?>" class="btn btn-outline-primary btn-sm py-0.5 px-2 text-xs rounded-2">
                                                        Buka
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h6 class="font-bold text-dark text-xs text-uppercase tracking-wider mb-2 text-secondary">5 Paket Ujian Terakhir:</h6>
                        <?php if (empty($exams)): ?>
                            <div class="p-3 bg-light rounded-3 text-muted text-xs text-center border">
                                Belum ada jadwal ujian yang dipublikasikan.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-xs mb-0 border rounded-3 overflow-hidden">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama Ujian</th>
                                            <th>Tanggal</th>
                                            <th>Durasi</th>
                                            <th>Peserta</th>
                                            <th>Token</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($exams as $ex): ?>
                                            <tr>
                                                <td class="font-semibold text-dark"><?= e($ex['title'] ?? '-') ?></td>
                                                <td class="text-muted"><?= !empty($ex['exam_date']) ? date('d/m/Y', strtotime($ex['exam_date'])) : '-' ?></td>
                                                <td><?= (int)($ex['duration_minutes'] ?? 90) ?> Menit</td>
                                                <td><span class="badge bg-light text-secondary border"><?= (int)($ex['participant_count'] ?? 0) ?> Siswa</span></td>
                                                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle font-mono"><?= e($ex['token'] ?? '-') ?></span></td>
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

