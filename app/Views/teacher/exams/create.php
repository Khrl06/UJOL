<?php
use App\Helpers\CsrfHelper;
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Buat Paket & Jadwal Ujian Baru</h3>
        <p class="text-muted text-sm mb-0">Tentukan konfigurasi bank soal, jadwal pelaksanaan, alokasi rombel kelas peserta, dan parameter pengawasan AI.</p>
    </div>
    <div>
        <a href="<?= base_url('teacher/exams') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Ujian
        </a>
    </div>
</div>

<?php if (empty($banks)): ?>
    <div class="alert alert-warning border-warning d-flex align-items-center gap-3 mb-4 rounded-3 p-3">
        <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
        <div>
            <h6 class="font-bold mb-1">Anda Belum Memiliki Bank Soal</h6>
            <p class="text-xs mb-2">Paket ujian membutuhkan minimal 1 Bank Soal dengan butir soal (minimal 1 butir soal). Silakan buat Bank Soal terlebih dahulu.</p>
            <a href="<?= base_url('teacher/banks') ?>" class="btn btn-warning btn-sm rounded-2 font-semibold text-dark text-xs">
                <i class="bi bi-plus-circle-fill me-1"></i> Buat Bank Soal Sekarang
            </a>
        </div>
    </div>
<?php endif; ?>

<form action="<?= base_url('teacher/saveExam') ?>" method="POST" id="formCreateExam">
    <?= CsrfHelper::tokenField() ?>

    <div class="row g-4 mb-4">
        <!-- Kolom Kiri: Informasi Dasar & Alokasi Kelas -->
        <div class="col-12 col-lg-7">
            <!-- 1. Informasi Dasar -->
            <div class="card-custom p-4 mb-4">
                <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i>1. Informasi Pokok Ujian
                </h5>

                <div class="mb-3">
                    <label class="form-label text-xs font-semibold text-secondary">Nama / Judul Paket Ujian *</label>
                    <input type="text" name="title" class="form-control text-sm" placeholder="Contoh: Penilaian Akhir Semester (PAS) Gasal Informatika XII" required autofocus>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label text-xs font-semibold text-secondary">Mata Pelajaran *</label>
                        <select name="subject_id" id="subjectSelect" class="form-select text-sm" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-xs font-semibold text-secondary">Bank Soal Sumber *</label>
                        <select name="question_bank_id" id="bankSelect" class="form-select text-sm" required>
                            <option value="">-- Pilih Bank Soal --</option>
                            <?php foreach ($banks as $b): ?>
                                <option value="<?= $b['id'] ?>" data-subject="<?= $b['subject_id'] ?>" data-total="<?= $b['total_questions'] ?>">
                                    <?= e($b['title']) ?> (<?= $b['total_questions'] ?> Soal)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted text-xs d-block mt-1" id="bankWarning">
                            Wajib memilih bank soal dengan minimal 1 butir soal.
                        </small>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label text-xs font-semibold text-secondary">Instruksi & Tata Tertib Pengerjaan</label>
                    <textarea name="description" class="form-control text-sm" rows="3" placeholder="Tuliskan petunjuk teknis ujian (cth: Kerjakan dengan teliti, dilarang membuka tab browser lain, pastikan kamera aktif)..."></textarea>
                </div>
            </div>

            <!-- 2. Alokasi Peserta Didik (Rombel Kelas) -->
            <div class="card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="font-bold text-dark mb-0">
                        <i class="bi bi-people-fill text-success me-2"></i>2. Alokasi Peserta (Pilih Rombel Kelas) *
                    </h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2 text-xs" id="btnSelectAllClasses">
                        Pilih Semua
                    </button>
                </div>

                <p class="text-muted text-xs mb-3">
                    Peserta didik pada kelas yang dicentang akan otomatis terdaftar dalam sesi ujian ini.
                </p>

                <div class="row g-2">
                    <?php foreach ($classes as $c): ?>
                        <div class="col-12 col-sm-6">
                            <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                                <div class="form-check mb-0">
                                    <input class="form-check-input class-checkbox" type="checkbox" name="class_ids[]" value="<?= $c['id'] ?>" id="class_<?= $c['id'] ?>">
                                    <label class="form-check-label font-bold text-sm text-dark cursor-pointer" for="class_<?= $c['id'] ?>">
                                        <?= e($c['class_name']) ?>
                                    </label>
                                    <small class="text-muted text-xs d-block"><?= e($c['major']) ?> (Tingkat <?= e($c['grade_level']) ?>)</small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary rounded-pill text-xs font-semibold">
                                    <?= $c['total_students'] ?? 0 ?> Siswa
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-danger text-xs d-none mt-2" id="classAlert">Wajib memilih minimal satu rombel kelas peserta.</small>
            </div>
        </div>

        <!-- Kolom Kanan: Jadwal, Parameter Asesmen, & Proctoring AI -->
        <div class="col-12 col-lg-5">
            <!-- 3. Jadwal & Durasi Waktu -->
            <div class="card-custom p-4 mb-4">
                <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="bi bi-calendar-check text-warning me-2"></i>3. Jadwal & Waktu Akses
                </h5>

                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label text-xs font-semibold text-secondary">Tanggal Mulai *</label>
                        <input type="date" name="start_date" id="startDate" class="form-control text-sm font-mono" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label text-xs font-semibold text-secondary">Jam Mulai *</label>
                        <input type="time" name="start_time" id="startTime" class="form-control text-sm font-mono" value="08:00" required>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label text-xs font-semibold text-secondary">Tanggal Selesai *</label>
                        <input type="date" name="end_date" id="endDate" class="form-control text-sm font-mono" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label text-xs font-semibold text-secondary">Jam Selesai *</label>
                        <input type="time" name="end_time" id="endTime" class="form-control text-sm font-mono" value="10:00" required>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label text-xs font-semibold text-secondary">Durasi Pengerjaan Peserta (Menit) *</label>
                    <div class="input-group">
                        <input type="number" min="1" name="duration_minutes" class="form-control text-sm font-mono" value="<?= e(app_setting('exam_default_duration', 90)) ?>" required>
                        <span class="input-group-text bg-light text-xs text-muted">Menit</span>
                    </div>
                    <small class="text-muted text-xs mt-1 d-block">Hitung mundur otomatis dimulai saat siswa klik 'Mulai Ujian'.</small>
                </div>
            </div>

            <!-- 4. Aturan Asesmen & KKM -->
            <div class="card-custom p-4 mb-4">
                <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="bi bi-sliders text-info me-2"></i>4. Parameter Asesmen
                </h5>

                <div class="form-check form-switch mb-2.5">
                    <input class="form-check-input" type="checkbox" name="is_random_questions" value="1" id="randomQ" <?= app_setting('exam_random_questions', true) ? 'checked' : '' ?>>
                    <label class="form-check-label text-xs font-semibold text-dark" for="randomQ">
                        Acak Urutan Butir Soal per Siswa
                    </label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_random_options" value="1" id="randomOpt" <?= app_setting('exam_random_options', true) ? 'checked' : '' ?>>
                    <label class="form-check-label text-xs font-semibold text-dark" for="randomOpt">
                        Acak Opsi Pilihan Ganda (A - E)
                    </label>
                </div>

                <div class="row g-2 mb-0">
                    <div class="col-6">
                        <label class="form-label text-xs font-semibold text-secondary">Max Percobaan</label>
                        <input type="number" min="1" name="max_attempts" class="form-control text-sm font-mono" value="1" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-xs font-semibold text-secondary">Standar KKM</label>
                        <input type="number" step="0.5" min="0" max="100" name="passing_grade" class="form-control text-sm font-mono" value="<?= e(app_setting('exam_default_kkm', 75.0)) ?>" required>
                    </div>
                </div>
            </div>

            <!-- 5. Pengawasan AI & Publikasi -->
            <div class="card-custom p-4 mb-4 border-start border-4 border-info">
                <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="bi bi-shield-lock-fill text-danger me-2"></i>5. Fitur Pengawasan & Status
                </h5>

                <div class="form-check form-switch mb-2.5">
                    <input class="form-check-input" type="checkbox" name="enable_dual_camera" value="1" id="dualCam" <?= app_setting('proctoring_default_dual_camera', true) ? 'checked' : '' ?>>
                    <label class="form-check-label text-xs font-bold text-dark" for="dualCam">
                        <i class="bi bi-webcam-fill text-info me-1"></i> Wajibkan Dual Camera (Depan & Samping)
                    </label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="enable_ai_proctoring" value="1" id="aiProc" <?= app_setting('proctoring_default_ai', true) ? 'checked' : '' ?>>
                    <label class="form-check-label text-xs font-bold text-dark" for="aiProc">
                        <i class="bi bi-robot text-warning me-1"></i> Aktifkan AI Proctoring (Deteksi Wajah/Tab)
                    </label>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs font-semibold text-secondary">Status Awal Penerbitan</label>
                    <select name="status" class="form-select text-sm font-semibold">
                        <option value="draft">Simpan sebagai DRAFT (Belum Rilis ke Siswa)</option>
                        <option value="published">Langsung PUBLISH (Tampil di Jadwal Siswa)</option>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" id="btnSubmitExam" class="btn btn-primary py-2.5 rounded-3 font-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Simpan & Jadwalkan Ujian</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tombol Pilih Semua Kelas
    const btnSelectAll = document.getElementById('btnSelectAllClasses');
    const classCheckboxes = document.querySelectorAll('.class-checkbox');
    let allSelected = false;

    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function() {
            allSelected = !allSelected;
            classCheckboxes.forEach(cb => cb.checked = allSelected);
            btnSelectAll.textContent = allSelected ? 'Batalkan Semua' : 'Pilih Semua';
        });
    }

    // 2. Sinkronisasi Mapel & Bank Soal
    const subjectSelect = document.getElementById('subjectSelect');
    const bankSelect = document.getElementById('bankSelect');
    const bankWarning = document.getElementById('bankWarning');
    const btnSubmit = document.getElementById('btnSubmitExam');

    if (bankSelect && subjectSelect) {
        // Saat bank soal dipilih, sesuaikan subject_id secara otomatis
        bankSelect.addEventListener('change', function() {
            const selectedOpt = bankSelect.options[bankSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const subId = selectedOpt.getAttribute('data-subject');
                const totalQ = parseInt(selectedOpt.getAttribute('data-total') || '0', 10);

                if (subId) {
                    subjectSelect.value = subId;
                }

                if (totalQ < 1) {
                    bankWarning.innerHTML = '<span class="text-danger font-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Bank soal ini masih memiliki 0 butir soal. Harap isi soal terlebih dahulu di menu Bank Soal.</span>';
                } else {
                    bankWarning.innerHTML = '<span class="text-success font-semibold"><i class="bi bi-check-circle-fill me-1"></i>Bank soal valid (' + totalQ + ' butir soal).</span>';
                }
            } else {
                bankWarning.textContent = 'Wajib memilih bank soal dengan minimal 1 butir soal.';
            }
        });

        // Saat mapel diubah, saring opsi bank soal
        subjectSelect.addEventListener('change', function() {
            const chosenSub = subjectSelect.value;
            let firstMatch = '';
            for (let i = 1; i < bankSelect.options.length; i++) {
                const opt = bankSelect.options[i];
                const optSub = opt.getAttribute('data-subject');
                if (!chosenSub || optSub === chosenSub) {
                    opt.style.display = '';
                    if (!firstMatch) firstMatch = opt.value;
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) {
                        bankSelect.value = '';
                    }
                }
            }
        });
    }

    // 3. Validasi Form Sebelum Submit
    const form = document.getElementById('formCreateExam');
    if (form) {
        form.addEventListener('submit', function(e) {
            const checkedClasses = document.querySelectorAll('.class-checkbox:checked');
            const classAlert = document.getElementById('classAlert');
            if (checkedClasses.length === 0) {
                e.preventDefault();
                if (classAlert) {
                    classAlert.classList.remove('d-none');
                }
                alert('Peringatan: Silakan centang minimal satu rombel kelas peserta.');
                return false;
            } else if (classAlert) {
                classAlert.classList.add('d-none');
            }

            // Validasi Bank Soal
            if (bankSelect) {
                const opt = bankSelect.options[bankSelect.selectedIndex];
                const total = parseInt(opt ? opt.getAttribute('data-total') || '0' : '0', 10);
                if (total < 1) {
                    e.preventDefault();
                    alert('Bank soal yang dipilih masih kosong (0 soal). Pilih bank soal yang memiliki butir soal.');
                    return false;
                }
            }
        });
    }
});
</script>
