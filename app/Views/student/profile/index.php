<?php
use App\Helpers\CsrfHelper;

$student        = $student ?? [];
$stats          = $stats ?? [];
$participations = $participations ?? [];
$logs           = $logs ?? [];

$name           = $student['name'] ?? 'Ahmad Fauzi Pratama';
$initial        = strtoupper(substr($name, 0, 1));
$nisn           = $student['nisn'] ?? ($student['nisn_nip'] ?? '-');
$className      = $student['class_name'] ?? 'XII MIPA 1';
$gender         = $student['gender'] ?? ($student['student_gender'] ?? 'L');
$phone          = $student['phone'] ?? ($student['student_phone'] ?? '');
$address        = $student['address'] ?? ($student['student_address'] ?? '');
$gradeLevel     = $student['grade_level'] ?? '12';
$major          = $student['major'] ?? 'MIPA';
$academicYear   = $student['academic_year'] ?? '2026/2027';
?>

<!-- Header & Breadcrumb -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 text-xs">
                <li class="breadcrumb-item"><a href="<?= base_url('student/dashboard') ?>" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Dasbor</a></li>
                <li class="breadcrumb-item active text-primary font-semibold" aria-current="page">Profil Siswa</li>
            </ol>
        </nav>
        <h3 class="font-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-mortarboard-fill text-success"></i> Profil Peserta Didik
        </h3>
        <p class="text-muted text-sm mb-0">Informasi identitas kesiswaan, rombel belajar aktif, kontak WhatsApp terverifikasi, dan kredensial akses pengerjaan ujian.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill font-semibold text-xs">
            <i class="bi bi-check2-circle me-1"></i>Peserta Didik Aktif <?= e($academicYear) ?>
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Kartu Identitas & Statistik Siswa -->
    <div class="col-lg-4">
        <!-- Kartu Identitas Peserta Didik -->
        <div class="card-custom p-4 mb-4 text-center">
            <div class="position-relative d-inline-block mx-auto mb-3">
                <div class="rounded-circle shadow d-flex align-items-center justify-content-center text-white font-bold"
                     style="width: 100px; height: 100px; font-size: 2.5rem; background: linear-gradient(135deg, #10b981 0%, #047857 100%); border: 4px solid #d1fae5;">
                    <?= $initial ?>
                </div>
                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle" title="Siswa Aktif">
                    <span class="visually-hidden">Online</span>
                </span>
            </div>

            <h5 class="font-bold text-dark mb-1"><?= e($name) ?></h5>
            <div class="text-muted text-sm mb-2">@<?= e($student['username'] ?? 'siswa') ?></div>

            <div class="mb-3 d-flex flex-wrap justify-content-center gap-1">
                <span class="badge badge-role-siswa rounded-pill px-3 py-1.5 text-xs shadow-sm">
                    <i class="bi bi-mortarboard-fill me-1"></i>PESERTA DIDIK
                </span>
                <span class="badge bg-light text-primary border rounded-pill px-2.5 py-1 text-xs font-semibold">
                    <i class="bi bi-building me-1"></i><?= e($className) ?>
                </span>
            </div>

            <hr class="my-3 opacity-25">

            <div class="text-start">
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-card-text me-1"></i>NISN</span>
                    <span class="font-bold text-dark font-mono"><?= e($nisn) ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-layers me-1"></i>Tingkat & Jurusan</span>
                    <span class="font-semibold text-dark">Kelas <?= e($gradeLevel) ?> - <?= e($major) ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-envelope me-1"></i>Alamat Email</span>
                    <span class="font-semibold text-dark text-truncate" style="max-width: 165px;" title="<?= e($student['email'] ?? '-') ?>"><?= e($student['email'] ?? '-') ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-whatsapp me-1 text-success"></i>WhatsApp</span>
                    <span class="font-semibold text-dark"><?= !empty($phone) ? e($phone) : '<span class="text-muted fst-italic">Belum diatur</span>' ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom text-xs">
                    <span class="text-muted"><i class="bi bi-gender-ambiguous me-1"></i>Jenis Kelamin</span>
                    <span class="font-semibold text-dark"><?= $gender === 'P' ? 'Perempuan' : 'Laki-laki' ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 text-xs">
                    <span class="text-muted"><i class="bi bi-calendar3 me-1"></i>Tahun Ajaran</span>
                    <span class="badge bg-light text-secondary border font-semibold"><?= e($academicYear) ?></span>
                </div>
            </div>
        </div>

        <!-- Kartu Ringkasan Hasil Asesmen Siswa -->
        <div class="card-custom p-4">
            <h6 class="font-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-award-fill text-warning"></i> Ringkasan Asesmen Anda
            </h6>
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="p-2.5 bg-light rounded-3 border">
                        <div class="font-bold text-primary fs-5"><?= (int)($stats['total_exams'] ?? 0) ?></div>
                        <small class="text-muted text-xs">Total Ujian</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2.5 bg-light rounded-3 border">
                        <div class="font-bold text-success fs-5"><?= (int)($stats['completed_exams'] ?? 0) ?></div>
                        <small class="text-muted text-xs">Selesai</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2.5 bg-light rounded-3 border">
                        <div class="font-bold text-warning fs-5"><?= number_format((float)($stats['avg_score'] ?? 0), 1) ?></div>
                        <small class="text-muted text-xs">Rata-rata</small>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <a href="<?= base_url('student/history') ?>" class="btn btn-outline-primary btn-sm w-100 rounded-3 text-xs font-semibold">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i>Lihat Rapor Riwayat Lengkap
                </a>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Formulir Pembaruan & Riwayat Ujian -->
    <div class="col-lg-8">
        <div class="card-custom border-0 shadow-sm p-4 bg-white rounded-4">
            <!-- Nav Tabs -->
            <ul class="nav nav-pills gap-2 mb-4 p-1 bg-light rounded-3" id="studentProfileTab" role="tablist">
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link active w-100 rounded-3 py-2 text-xs font-semibold d-flex align-items-center justify-content-center gap-2"
                            id="tab-biodata-btn" data-bs-toggle="pill" data-bs-target="#tab-biodata" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-person-lines-fill fs-6"></i>
                        <span>Biodata Siswa</span>
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
                            id="tab-history-btn" data-bs-toggle="pill" data-bs-target="#tab-history" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-clock-history fs-6"></i>
                        <span>Ujian Terkini</span>
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="studentProfileTabContent">
                <!-- Tab 1: Biodata Siswa -->
                <div class="tab-pane fade show active" id="tab-biodata" role="tabpanel" aria-labelledby="tab-biodata-btn">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="font-bold text-dark mb-1">Informasi Akun & Data Peserta Didik</h6>
                            <p class="text-muted text-xs mb-0">Pastikan nomor WhatsApp aktif untuk menerima token ujian dan notifikasi jadwal asesmen secara real-time.</p>
                        </div>
                    </div>

                    <form action="<?= base_url('student/updateProfile') ?>" method="POST" autocomplete="off">
                        <?= CsrfHelper::tokenField() ?>

                        <div class="row g-3">
                            <!-- Kolom Terkunci (Akademik Sekolah) -->
                            <div class="col-md-6">
                                <label class="form-label text-xs font-semibold text-secondary d-flex align-items-center gap-1">
                                    <span>NISN (Nomor Induk Siswa Nasional)</span>
                                    <i class="bi bi-lock-fill text-muted" title="Dikelola oleh Administrator Sekolah"></i>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-card-text"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0 ps-0 text-sm font-mono font-semibold" value="<?= e($nisn) ?>" readonly disabled>
                                </div>
                                <small class="text-muted text-xs">Data akademik dikunci oleh pihak sekolah.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-xs font-semibold text-secondary d-flex align-items-center gap-1">
                                    <span>Kelas & Rombongan Belajar</span>
                                    <i class="bi bi-lock-fill text-muted" title="Dikelola oleh Administrator Sekolah"></i>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-building"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0 ps-0 text-sm font-semibold" value="<?= e($className) ?> (<?= e($major) ?>)" readonly disabled>
                                </div>
                                <small class="text-muted text-xs">Hubungi admin kurikulum jika terjadi kekeliruan rombel.</small>
                            </div>

                            <!-- Kolom Dapat Diedit -->
                            <div class="col-md-12">
                                <label for="studentName" class="form-label text-xs font-semibold text-secondary">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="studentName" name="name" 
                                           value="<?= e($name) ?>" required placeholder="Nama lengkap sesuai ijazah">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="studentUsername" class="form-label text-xs font-semibold text-secondary">Username Login <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted font-bold">@</span>
                                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="studentUsername" name="username" 
                                           value="<?= e($student['username'] ?? '') ?>" required placeholder="Username unik">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="studentEmail" class="form-label text-xs font-semibold text-secondary">Alamat Email Siswa <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0 text-sm" id="studentEmail" name="email" 
                                           value="<?= e($student['email'] ?? '') ?>" required placeholder="siswa@smartexam.sch.id">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-xs font-semibold text-secondary">Jenis Kelamin</label>
                                <div class="d-flex align-items-center gap-4 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="sGenderL" value="L" <?= $gender !== 'P' ? 'checked' : '' ?>>
                                        <label class="form-check-label text-sm" for="sGenderL">Laki-laki</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="sGenderP" value="P" <?= $gender === 'P' ? 'checked' : '' ?>>
                                        <label class="form-check-label text-sm" for="sGenderP">Perempuan</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="studentPhone" class="form-label text-xs font-semibold text-secondary">Nomor WhatsApp Siswa / Wali</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-success"><i class="bi bi-whatsapp"></i></span>
                                    <input type="tel" class="form-control border-start-0 ps-0 text-sm" id="studentPhone" name="phone" 
                                           value="<?= e($phone) ?>" placeholder="Contoh: 081234567890">
                                </div>
                                <small class="text-muted text-xs">Penting: Digunakan untuk menerima token ujian & OTP via WhatsApp.</small>
                            </div>

                            <div class="col-12">
                                <label for="studentAddress" class="form-label text-xs font-semibold text-secondary">Alamat Tempat Tinggal</label>
                                <textarea class="form-control text-sm" id="studentAddress" name="address" rows="2" placeholder="Alamat domisili lengkap"><?= e($address) ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary rounded-3 px-4 font-semibold text-sm d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-save2-fill"></i>
                                <span>Simpan Perubahan Biodata</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Keamanan & Ganti Sandi -->
                <div class="tab-pane fade" id="tab-password" role="tabpanel" aria-labelledby="tab-password-btn">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="font-bold text-dark mb-1">Perbarui Kata Sandi Ujian Anda</h6>
                            <p class="text-muted text-xs mb-0">Lindungi akun siswa Anda agar orang lain tidak dapat mengerjakan ujian atas nama Anda.</p>
                        </div>
                    </div>

                    <form action="<?= base_url('student/updatePassword') ?>" method="POST" autocomplete="off">
                        <?= CsrfHelper::tokenField() ?>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="student_current_password" class="form-label text-xs font-semibold text-secondary">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0 text-sm" id="student_current_password" name="current_password" required placeholder="Masukkan sandi lama Anda">
                                    <button class="btn btn-light border border-start-0 text-muted toggle-password-btn" type="button" data-target="student_current_password">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="student_new_password" class="form-label text-xs font-semibold text-secondary">Kata Sandi Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0 text-sm" id="student_new_password" name="new_password" minlength="6" required placeholder="Minimal 6 karakter">
                                    <button class="btn btn-light border border-start-0 text-muted toggle-password-btn" type="button" data-target="student_new_password">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                                <small class="text-muted text-xs">Minimal 6 karakter.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="student_confirm_password" class="form-label text-xs font-semibold text-secondary">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-check2-circle"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0 text-sm" id="student_confirm_password" name="confirm_password" minlength="6" required placeholder="Ulangi sandi baru">
                                    <button class="btn btn-light border border-start-0 text-muted toggle-password-btn" type="button" data-target="student_confirm_password">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning d-flex align-items-center gap-2 mt-4 rounded-3 py-2 px-3 text-xs mb-0">
                            <i class="bi bi-info-circle-fill fs-5 text-warning"></i>
                            <div>Ingat baik-baik kata sandi baru Anda untuk membuka ruang ujian selanjutnya.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary rounded-3 px-4 font-semibold text-sm d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-shield-lock-fill"></i>
                                <span>Perbarui Kata Sandi</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 3: Ujian Terkini -->
                <div class="tab-pane fade" id="tab-history" role="tabpanel" aria-labelledby="tab-history-btn">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="font-bold text-dark mb-1">Riwayat Asesmen & Ujian Terkini</h6>
                            <p class="text-muted text-xs mb-0">Daftar pengerjaan ujian terbaru beserta perolehan nilai hasil evaluasi.</p>
                        </div>
                        <a href="<?= base_url('student/history') ?>" class="btn btn-outline-primary btn-sm rounded-3 text-xs font-semibold">
                            <i class="bi bi-arrow-right-circle me-1"></i>Seluruh Riwayat
                        </a>
                    </div>

                    <?php if (empty($participations)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <div class="font-semibold text-sm">Belum ada riwayat pengerjaan ujian</div>
                            <small class="text-xs">Jadwal ujian aktif dapat Anda akses pada menu Jadwal Ujian Saya.</small>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-xs mb-0 border rounded-3 overflow-hidden">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Ujian</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Guru Penguji</th>
                                        <th>Tanggal</th>
                                        <th>Nilai Akhir</th>
                                        <th>Status Kelulusan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participations as $p): ?>
                                        <?php
                                            $score = isset($p['final_score']) && $p['final_score'] !== null ? (float)$p['final_score'] : null;
                                            $isPassed = !empty($p['is_passed']);
                                        ?>
                                        <tr>
                                            <td class="font-semibold text-dark"><?= e($p['exam_title'] ?? '-') ?></td>
                                            <td><span class="badge bg-light text-primary border"><?= e($p['subject_name'] ?? '-') ?></span></td>
                                            <td class="text-muted"><?= e($p['teacher_name'] ?? '-') ?></td>
                                            <td class="text-muted"><?= !empty($p['exam_date']) ? date('d/m/Y', strtotime($p['exam_date'])) : '-' ?></td>
                                            <td>
                                                <?php if ($score !== null): ?>
                                                    <span class="badge <?= $score >= 75 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' ?> font-bold px-2 py-1 fs-6">
                                                        <?= number_format($score, 1) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">Belum dinilai</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($score !== null): ?>
                                                    <?php if ($isPassed): ?>
                                                        <span class="badge bg-success rounded-pill px-2.5 py-1 text-xs">
                                                            <i class="bi bi-check-circle-fill me-1"></i>Lulus KKM
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger rounded-pill px-2.5 py-1 text-xs">
                                                            <i class="bi bi-x-circle-fill me-1"></i>Remedial
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary rounded-pill px-2 py-0.5 text-xs">Proses</span>
                                                <?php endif; ?>
                                            </td>
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

