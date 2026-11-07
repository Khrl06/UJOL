<?php
use App\Helpers\CsrfHelper;
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="font-bold text-dark mb-1">Edit Pengaturan Paket Ujian</h3>
        <p class="text-muted text-sm mb-0">Sesuaikan jadwal pelaksanaan, parameter asesmen, dan pengaturan keamanan ujian.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('teacher/exams/' . $exam['id']) ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
        </a>
    </div>
</div>

<form action="<?= base_url('teacher/updateExam') ?>" method="POST">
    <?= CsrfHelper::tokenField() ?>
    <input type="hidden" name="id" value="<?= $exam['id'] ?>">

    <div class="row g-4 mb-4">
        <!-- Kolom Kiri: Informasi Pokok & Bank Soal -->
        <div class="col-12 col-lg-7">
            <div class="card-custom p-4 mb-4">
                <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="bi bi-pencil-square text-primary me-2"></i>1. Informasi Pokok Ujian
                </h5>

                <div class="mb-3">
                    <label class="form-label text-xs font-semibold text-secondary">Nama / Judul Paket Ujian *</label>
                    <input type="text" name="title" class="form-control text-sm" value="<?= e($exam['title']) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label text-xs font-semibold text-secondary">Mata Pelajaran *</label>
                        <select name="subject_id" class="form-select text-sm" required>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $exam['subject_id'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= e($s['name']) ?> (<?= e($s['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-xs font-semibold text-secondary">Bank Soal Sumber *</label>
                        <select name="question_bank_id" class="form-select text-sm" required>
                            <?php foreach ($banks as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $exam['question_bank_id'] == $b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['title']) ?> (<?= $b['total_questions'] ?> Soal)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label text-xs font-semibold text-secondary">Instruksi & Tata Tertib Pengerjaan</label>
                    <textarea name="description" class="form-control text-sm" rows="4"><?= e($exam['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Jadwal, Aturan, & Proctoring -->
        <div class="col-12 col-lg-5">
            <!-- 2. Jadwal & Waktu -->
            <div class="card-custom p-4 mb-4">
                <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="bi bi-calendar-check text-warning me-2"></i>2. Jadwal & Waktu Akses
                </h5>

                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label text-xs font-semibold text-secondary">Tanggal Mulai *</label>
                        <input type="date" name="start_date" class="form-control text-sm font-mono" value="<?= $exam['start_date'] ?>" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label text-xs font-semibold text-secondary">Jam Mulai *</label>
                        <input type="time" name="start_time" class="form-control text-sm font-mono" value="<?= substr($exam['start_time'], 0, 5) ?>" required>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label text-xs font-semibold text-secondary">Tanggal Selesai *</label>
                        <input type="date" name="end_date" class="form-control text-sm font-mono" value="<?= $exam['end_date'] ?>" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label text-xs font-semibold text-secondary">Jam Selesai *</label>
                        <input type="time" name="end_time" class="form-control text-sm font-mono" value="<?= substr($exam['end_time'], 0, 5) ?>" required>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label text-xs font-semibold text-secondary">Durasi Pengerjaan (Menit) *</label>
                    <div class="input-group">
                        <input type="number" min="1" name="duration_minutes" class="form-control text-sm font-mono" value="<?= $exam['duration_minutes'] ?>" required>
                        <span class="input-group-text bg-light text-xs text-muted">Menit</span>
                    </div>
                </div>
            </div>

            <!-- 3. Aturan & Status -->
            <div class="card-custom p-4 mb-4">
                <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="bi bi-sliders text-info me-2"></i>3. Parameter & Status
                </h5>

                <div class="form-check form-switch mb-2.5">
                    <input class="form-check-input" type="checkbox" name="is_random_questions" value="1" id="randomQ" <?= !empty($exam['is_random_questions']) ? 'checked' : '' ?>>
                    <label class="form-check-label text-xs font-semibold text-dark" for="randomQ">Acak Urutan Soal</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_random_options" value="1" id="randomOpt" <?= !empty($exam['is_random_options']) ? 'checked' : '' ?>>
                    <label class="form-check-label text-xs font-semibold text-dark" for="randomOpt">Acak Opsi Pilihan Ganda (A-E)</label>
                </div>

                <div class="form-check form-switch mb-2.5">
                    <input class="form-check-input" type="checkbox" name="enable_dual_camera" value="1" id="dualCam" <?= !empty($exam['enable_dual_camera']) ? 'checked' : '' ?>>
                    <label class="form-check-label text-xs font-semibold text-dark" for="dualCam">Dual Camera (Depan & Samping)</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="enable_ai_proctoring" value="1" id="aiProc" <?= !empty($exam['enable_ai_proctoring']) ? 'checked' : '' ?>>
                    <label class="form-check-label text-xs font-semibold text-dark" for="aiProc">AI Proctoring (Deteksi Pelanggaran)</label>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label text-xs font-semibold text-secondary">Max Percobaan</label>
                        <input type="number" min="1" name="max_attempts" class="form-control text-sm font-mono" value="<?= $exam['max_attempts'] ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-xs font-semibold text-secondary">Standar KKM</label>
                        <input type="number" step="0.5" name="passing_grade" class="form-control text-sm font-mono" value="<?= $exam['passing_grade'] ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-xs font-semibold text-secondary">Status Siklus Ujian</label>
                    <select name="status" class="form-select text-sm font-bold">
                        <option value="draft" <?= $exam['status'] === 'draft' ? 'selected' : '' ?>>DRAFT (Belum Rilis)</option>
                        <option value="published" <?= $exam['status'] === 'published' ? 'selected' : '' ?>>PUBLISHED (Terjadwal)</option>
                        <option value="ongoing" <?= $exam['status'] === 'ongoing' ? 'selected' : '' ?>>ONGOING (Sedang Berlangsung)</option>
                        <option value="completed" <?= $exam['status'] === 'completed' ? 'selected' : '' ?>>COMPLETED (Telah Selesai)</option>
                        <option value="cancelled" <?= $exam['status'] === 'cancelled' ? 'selected' : '' ?>>CANCELLED (Dibatalkan)</option>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary py-2.5 rounded-3 font-semibold shadow-sm">
                        <i class="bi bi-save-fill me-1"></i> Simpan Perubahan Ujian
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
