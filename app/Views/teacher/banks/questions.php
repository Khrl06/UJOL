<?php
use App\Helpers\CsrfHelper;
use App\Models\QuestionModel;
$totalMaxScore = $totalMaxScore ?? 0.0;
$bank = $bank ?? [];
$questions = $questions ?? [];
$total = $total ?? count($questions);
$page = $page ?? 1;
$perPage = $perPage ?? 10;
$totalPages = $totalPages ?? 1;
$search = $search ?? '';
$difficulty = $difficulty ?? '';
?>

<!-- Bank Header Card -->
<div class="card-custom p-4 mb-4 border-start border-4 border-primary">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge bg-primary rounded-pill px-3 py-1 text-xs font-semibold">
                    <i class="bi bi-journal-bookmark me-1"></i><?= e($bank['subject_name']) ?> (<?= e($bank['subject_code']) ?>)
                </span>
                <span class="badge bg-secondary rounded-pill px-2.5 py-1 text-xs">
                    Tingkat Kelas <?= e($bank['grade_level'] ?? '12') ?>
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs font-semibold">
                    Total Bobot: <?= number_format($totalMaxScore, 1) ?> Poin
                </span>
            </div>
            <h3 class="font-bold text-dark mb-1"><?= e($bank['title']) ?></h3>
            <p class="text-muted text-xs mb-0"><?= e($bank['description'] ?: 'Tidak ada kisi-kisi atau instruksi khusus.') ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('teacher/banks') ?>" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Bank
            </a>
            <button type="button" class="btn btn-success text-white btn-sm rounded-3 px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAutoGenerateSoal">
                <i class="bi bi-robot me-1"></i> ⚡ Generator Soal Otomatis
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-3 px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBatchImport">
                <i class="bi bi-clipboard2-plus me-1"></i> Tempel Cepat (Auto-Parse)
            </button>
            <button type="button" class="btn btn-primary btn-sm rounded-3 px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddMC">
                <i class="bi bi-list-check me-1"></i> + Pilihan Ganda (A-E)
            </button>
            <button type="button" class="btn btn-info text-dark btn-sm rounded-3 px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddEssay">
                <i class="bi bi-pencil-square me-1"></i> + Soal Essay
            </button>
        </div>
    </div>
</div>

<!-- Search & Difficulty Filter Card -->
<div class="card-custom p-3 mb-4">
    <form action="<?= base_url('teacher/bankQuestions') ?>" method="GET" class="row g-2 align-items-center">
        <input type="hidden" name="bank_id" value="<?= $bank['id'] ?>">

        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0 text-sm" placeholder="Cari isi pertanyaan, opsi, atau pembahasan..." value="<?= e($search) ?>">
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="btn-group w-100" role="group">
                <a href="<?= base_url('teacher/bankQuestions?bank_id=' . $bank['id'] . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                   class="btn btn-sm <?= empty($difficulty) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Semua
                </a>
                <a href="<?= base_url('teacher/bankQuestions?bank_id=' . $bank['id'] . '&difficulty=mudah' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                   class="btn btn-sm <?= $difficulty === 'mudah' ? 'btn-success' : 'btn-outline-success' ?>">
                    Mudah
                </a>
                <a href="<?= base_url('teacher/bankQuestions?bank_id=' . $bank['id'] . '&difficulty=sedang' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                   class="btn btn-sm <?= $difficulty === 'sedang' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>">
                    Sedang
                </a>
                <a href="<?= base_url('teacher/bankQuestions?bank_id=' . $bank['id'] . '&difficulty=sulit' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                   class="btn btn-sm <?= $difficulty === 'sulit' ? 'btn-danger' : 'btn-outline-danger' ?>">
                    Sulit
                </a>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-secondary btn-sm rounded-3 px-3">Filter</button>
            <?php if (!empty($search) || !empty($difficulty)): ?>
                <a href="<?= base_url('teacher/bankQuestions?bank_id=' . $bank['id']) ?>" class="btn btn-outline-secondary btn-sm rounded-3 ms-1">Reset</a>
            <?php endif; ?>
        </div>

        <div class="col text-md-end text-muted text-xs">
            Menampilkan <strong><?= count($questions) ?></strong> dari <strong><?= number_format($total) ?></strong> butir soal
        </div>
    </form>
</div>

<!-- Questions Cards List -->
<div class="d-flex flex-column gap-3 mb-4">
    <?php if (!empty($questions)): ?>
        <?php $questionNum = ($page - 1) * $perPage + 1; ?>
        <?php foreach ($questions as $q): ?>
            <?php 
                $options = QuestionModel::decodeOptions($q['options']); 
                $diffClass = match($q['difficulty'] ?? 'sedang') {
                    'mudah'  => 'bg-success text-white',
                    'sulit'  => 'bg-danger text-white',
                    default  => 'bg-warning text-dark'
                };
            ?>
            <div class="card-custom p-4 <?= ((int)($q['is_active'] ?? 1) === 0) ? 'opacity-75 bg-light' : '' ?>">
                <!-- Question Top Meta -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark rounded-pill px-3 py-1.5 font-bold text-xs">
                            No. <?= $questionNum++ ?>
                        </span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 text-xs font-semibold">
                            <?= $q['question_type'] === 'multiple_choice' ? 'Pilihan Ganda (A-E)' : 'Essay / Uraian' ?>
                        </span>
                        <span class="badge <?= $diffClass ?> rounded-pill px-2.5 py-1 text-xs text-uppercase font-semibold">
                            <?= e($q['difficulty'] ?? 'sedang') ?>
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 text-xs">
                            Bobot: <?= number_format($q['score_weight'], 1) ?> Poin
                        </span>
                    </div>

                    <!-- Actions Top Right -->
                    <div class="d-flex align-items-center gap-1">
                        <!-- Toggle Status Button -->
                        <form action="<?= base_url('teacher/toggleQuestion') ?>" method="POST" class="d-inline">
                            <?= CsrfHelper::tokenField() ?>
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <input type="hidden" name="question_bank_id" value="<?= $bank['id'] ?>">
                            <?php if ((int)($q['is_active'] ?? 1) === 1): ?>
                                <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 text-xs rounded-pill" title="Klik untuk Nonaktifkan">
                                    <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs rounded-pill" title="Klik untuk Aktifkan">
                                    <i class="bi bi-slash-circle me-1"></i>Nonaktif
                                </button>
                            <?php endif; ?>
                        </form>

                        <!-- Preview Button -->
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" data-bs-toggle="modal" data-bs-target="#modalPreview_<?= $q['id'] ?>" title="Preview Tampilan Soal">
                            <i class="bi bi-eye"></i> Preview
                        </button>

                        <!-- Duplicate Button -->
                        <form action="<?= base_url('teacher/duplicateQuestion') ?>" method="POST" class="d-inline">
                            <?= CsrfHelper::tokenField() ?>
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <input type="hidden" name="question_bank_id" value="<?= $bank['id'] ?>">
                            <button type="submit" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Duplikasi / Salin Soal 1-Klik">
                                <i class="bi bi-copy"></i> Duplikat
                            </button>
                        </form>

                        <!-- Edit Button -->
                        <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-2 py-1 px-2" data-bs-toggle="modal" data-bs-target="#modalEdit_<?= $q['id'] ?>" title="Edit Butir Soal">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <!-- Delete Button -->
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" data-bs-toggle="modal" data-bs-target="#modalDelete_<?= $q['id'] ?>" title="Hapus Soal">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- Question Text -->
                <div class="text-dark font-medium text-sm mb-3">
                    <?= nl2br(e($q['question_text'])) ?>
                </div>

                <!-- Options (If Multiple Choice) -->
                <?php if ($q['question_type'] === 'multiple_choice' && !empty($options)): ?>
                    <div class="row g-2 mb-3">
                        <?php foreach (['A', 'B', 'C', 'D', 'E'] as $optKey): ?>
                            <?php if (isset($options[$optKey])): ?>
                                <?php $isCorrect = (strtoupper($q['correct_answer']) === $optKey); ?>
                                <div class="col-12 col-md-6">
                                    <div class="p-2.5 rounded-3 border d-flex align-items-start gap-2 <?= $isCorrect ? 'bg-success-subtle border-success text-success font-semibold' : 'bg-light text-dark' ?>">
                                        <span class="badge <?= $isCorrect ? 'bg-success text-white' : 'bg-secondary text-white' ?> rounded-circle" style="width: 22px; height: 22px; line-height: 15px;">
                                            <?= $optKey ?>
                                        </span>
                                        <div class="text-sm flex-grow-1">
                                            <?= e($options[$optKey]) ?>
                                            <?php if ($isCorrect): ?>
                                                <i class="bi bi-check-circle-fill ms-1 text-success" title="Kunci Jawaban Benar"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Essay Rubric -->
                    <div class="p-3 bg-light border rounded-3 mb-3">
                        <span class="text-xs font-bold text-secondary text-uppercase d-block mb-1">
                            <i class="bi bi-bookmark-check-fill text-info me-1"></i>Pedoman Jawaban / Rubrik Penilaian:
                        </span>
                        <div class="text-sm text-dark font-mono bg-white p-2 rounded border">
                            <?= nl2br(e($q['correct_answer'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Explanation / Pembahasan -->
                <?php if (!empty($q['explanation'])): ?>
                    <div class="p-2.5 rounded-3 bg-info-subtle border border-info-subtle text-xs text-dark">
                        <strong class="text-info d-block mb-0.5"><i class="bi bi-lightbulb-fill me-1"></i>Pembahasan Soal:</strong>
                        <?= nl2br(e($q['explanation'])) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal Preview Soal -->
            <div class="modal fade" id="modalPreview_<?= $q['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom bg-primary text-white">
                            <h5 class="modal-title font-bold">
                                <i class="bi bi-eye-fill me-2"></i>Preview Simulasi Soal Ujian
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="badge bg-secondary text-xs">Mata Pelajaran: <?= e($bank['subject_name']) ?></span>
                                <span class="text-xs text-muted">Tipe: <?= strtoupper($q['question_type']) ?> | Bobot: <?= $q['score_weight'] ?> Poin</span>
                            </div>

                            <div class="lead font-medium text-dark mb-4 text-sm">
                                <?= nl2br(e($q['question_text'])) ?>
                            </div>

                            <?php if ($q['question_type'] === 'multiple_choice' && !empty($options)): ?>
                                <div class="list-group mb-4">
                                    <?php foreach (['A', 'B', 'C', 'D', 'E'] as $k): ?>
                                        <?php if (isset($options[$k])): ?>
                                            <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2.5">
                                                <input class="form-check-input me-1" type="radio" name="preview_choice_<?= $q['id'] ?>" value="<?= $k ?>">
                                                <span class="font-bold text-dark"><?= $k ?>.</span>
                                                <span class="text-dark text-sm"><?= e($options[$k]) ?></span>
                                            </label>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="mb-4">
                                    <textarea class="form-control text-sm" rows="4" placeholder="Lembar jawaban peserta didik (simulasi)..." disabled></textarea>
                                </div>
                            <?php endif; ?>

                            <!-- Teacher Answer Key Dropdown Panel -->
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="font-bold text-xs text-uppercase text-secondary mb-1">
                                    <i class="bi bi-key-fill text-warning me-1"></i>Kunci & Pembahasan Guru:
                                </div>
                                <div class="text-xs text-dark mb-1">
                                    <strong>Kunci:</strong> <span class="badge bg-success font-mono"><?= e($q['correct_answer']) ?></span>
                                </div>
                                <?php if (!empty($q['explanation'])): ?>
                                    <div class="text-xs text-muted">
                                        <strong>Pembahasan:</strong> <?= e($q['explanation']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup Preview</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Edit Soal -->
            <div class="modal fade" id="modalEdit_<?= $q['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom bg-warning text-dark">
                            <h5 class="modal-title font-bold">
                                <i class="bi bi-pencil-square me-2"></i>Edit Butir Soal
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="<?= base_url('teacher/editQuestion') ?>" method="POST">
                            <?= CsrfHelper::tokenField() ?>
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <input type="hidden" name="question_bank_id" value="<?= $bank['id'] ?>">
                            <input type="hidden" name="question_type" value="<?= $q['question_type'] ?>">

                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label text-xs font-semibold text-secondary">Isi Teks Pertanyaan *</label>
                                    <textarea name="question_text" class="form-control text-sm" rows="3" required><?= e($q['question_text']) ?></textarea>
                                </div>

                                <?php if ($q['question_type'] === 'multiple_choice'): ?>
                                    <div class="row g-2 mb-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-xs font-semibold text-secondary">Pilihan A *</label>
                                            <input type="text" name="option_a" class="form-control text-sm" value="<?= e($options['A'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-xs font-semibold text-secondary">Pilihan B *</label>
                                            <input type="text" name="option_b" class="form-control text-sm" value="<?= e($options['B'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-xs font-semibold text-secondary">Pilihan C *</label>
                                            <input type="text" name="option_c" class="form-control text-sm" value="<?= e($options['C'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-xs font-semibold text-secondary">Pilihan D *</label>
                                            <input type="text" name="option_d" class="form-control text-sm" value="<?= e($options['D'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-xs font-semibold text-secondary">Pilihan E (Opsional)</label>
                                            <input type="text" name="option_e" class="form-control text-sm" value="<?= e($options['E'] ?? '') ?>">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-xs font-semibold text-secondary">Kunci Jawaban Benar *</label>
                                            <select name="correct_answer" class="form-select text-sm" required>
                                                <?php foreach (['A', 'B', 'C', 'D', 'E'] as $k): ?>
                                                    <option value="<?= $k ?>" <?= strtoupper($q['correct_answer']) === $k ? 'selected' : '' ?>>Pilihan <?= $k ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-semibold text-secondary">Pedoman Jawaban / Rubrik Penilaian *</label>
                                        <textarea name="rubric" class="form-control text-sm" rows="3" required><?= e($q['correct_answer']) ?></textarea>
                                    </div>
                                <?php endif; ?>

                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label text-xs font-semibold text-secondary">Bobot Nilai</label>
                                        <input type="number" step="0.5" name="score_weight" class="form-control text-sm font-mono" value="<?= $q['score_weight'] ?>" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label text-xs font-semibold text-secondary">Tingkat Kesulitan</label>
                                        <select name="difficulty" class="form-select text-sm">
                                            <option value="mudah" <?= ($q['difficulty'] ?? '') === 'mudah' ? 'selected' : '' ?>>Mudah</option>
                                            <option value="sedang" <?= ($q['difficulty'] ?? '') === 'sedang' ? 'selected' : '' ?>>Sedang</option>
                                            <option value="sulit" <?= ($q['difficulty'] ?? '') === 'sulit' ? 'selected' : '' ?>>Sulit</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label text-xs font-semibold text-secondary">Status Keaktifan</label>
                                        <select name="is_active" class="form-select text-sm">
                                            <option value="1" <?= ((int)($q['is_active'] ?? 1) === 1) ? 'selected' : '' ?>>Aktif</option>
                                            <option value="0" <?= ((int)($q['is_active'] ?? 1) === 0) ? 'selected' : '' ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-xs font-semibold text-secondary">Pembahasan Soal</label>
                                    <textarea name="explanation" class="form-control text-sm" rows="2" placeholder="Penjelasan cara menjawab yang benar..."><?= e($q['explanation'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top bg-light">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-warning text-dark btn-sm px-3 font-semibold">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Delete Soal -->
            <div class="modal fade" id="modalDelete_<?= $q['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom bg-danger text-white">
                            <h5 class="modal-title font-bold">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Soal
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="<?= base_url('teacher/deleteQuestion') ?>" method="POST">
                            <?= CsrfHelper::tokenField() ?>
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <input type="hidden" name="question_bank_id" value="<?= $bank['id'] ?>">

                            <div class="modal-body p-4 text-start">
                                <p class="mb-2">Apakah Anda yakin ingin menghapus butir soal berikut?</p>
                                <div class="p-3 bg-light rounded-3 border text-xs text-dark mb-0">
                                    <?= e(substr($q['question_text'], 0, 150)) ?>...
                                </div>
                            </div>
                            <div class="modal-footer border-top bg-light">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger btn-sm px-3 font-semibold">Ya, Hapus Butir Soal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="card-custom p-5 text-center">
            <i class="bi bi-inbox display-6 text-muted d-block mb-3"></i>
            <h5 class="font-bold text-dark mb-1">Belum Ada Butir Soal</h5>
            <p class="text-muted text-sm mb-4">Gunakan Generator Otomatis untuk membuat butir soal dan opsi PG secara instan tanpa perlu mengetik manual.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <button type="button" class="btn btn-success text-white rounded-3 btn-sm px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAutoGenerateSoal">
                    <i class="bi bi-robot me-1"></i> ⚡ Generator Soal Otomatis
                </button>
                <button type="button" class="btn btn-outline-primary rounded-3 btn-sm px-3 font-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBatchImport">
                    <i class="bi bi-clipboard2-plus me-1"></i> Tempel Cepat (Auto-Parse)
                </button>
                <button type="button" class="btn btn-primary rounded-3 btn-sm px-3 font-semibold" data-bs-toggle="modal" data-bs-target="#modalAddMC">
                    <i class="bi bi-list-check me-1"></i> Tambah Pilihan Ganda
                </button>
                <button type="button" class="btn btn-info text-dark rounded-3 btn-sm px-3 font-semibold" data-bs-toggle="modal" data-bs-target="#modalAddEssay">
                    <i class="bi bi-pencil-square me-1"></i> Tambah Essay
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <small class="text-muted text-xs">
            Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= number_format($total) ?> Butir Soal)
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= base_url('teacher/bankQuestions?bank_id=' . $bank['id'] . '&page=' . ($page - 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($difficulty) ? '&difficulty=' . $difficulty : '')) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('teacher/bankQuestions?bank_id=' . $bank['id'] . '&page=' . $i . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($difficulty) ? '&difficulty=' . $difficulty : '')) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= base_url('teacher/bankQuestions?bank_id=' . $bank['id'] . '&page=' . ($page + 1) . (!empty($search) ? '&q=' . urlencode($search) : '') . (!empty($difficulty) ? '&difficulty=' . $difficulty : '')) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>

<!-- =========================================================================
     MODAL TAMBAH PILIHAN GANDA (A-E)
     ========================================================================= -->
<div class="modal fade" id="modalAddMC" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-primary text-white">
                <h5 class="modal-title font-bold">
                    <i class="bi bi-list-check me-2"></i>Tambah Soal Pilihan Ganda (A - E)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('teacher/createQuestion') ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="question_bank_id" value="<?= $bank['id'] ?>">
                <input type="hidden" name="question_type" value="multiple_choice">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-xs font-semibold text-secondary mb-0">Isi Teks Pertanyaan *</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-xs text-decoration-none" id="btnSampleQuestionPrompt">
                                <i class="bi bi-lightbulb me-1"></i>Contoh Ide Soal
                            </button>
                        </div>
                        <textarea name="question_text" id="addMcQuestionText" class="form-control text-sm" rows="3" placeholder="Tuliskan pertanyaan soal di sini..." required></textarea>
                    </div>

                    <!-- Otomasi Opsi Pilihan Ganda Banner & Tombol AI -->
                    <div class="p-3 mb-3 rounded-3 border bg-light-subtle d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-primary-subtle">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary rounded-pill px-2.5 py-0.5 text-xs font-bold">
                                    <i class="bi bi-magic me-1"></i>Otomasi Pengisian Opsi PG
                                </span>
                                <span class="text-xs text-dark font-semibold">Tidak Perlu Ketik Manual Pilihan A - E!</span>
                            </div>
                            <p class="text-muted text-xs mb-0">Klik tombol di sebelah kanan untuk mengisi opsi A, B, C, D, E, kunci jawaban, dan pembahasan secara otomatis.</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 font-bold shadow-sm flex-shrink-0" id="btnAutoGenerateOptions">
                            <i class="bi bi-stars text-warning me-1"></i> ✨ Generate Opsi & Kunci Otomatis
                        </button>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Pilihan Jawaban A *</label>
                            <input type="text" name="option_a" class="form-control text-sm" placeholder="Teks opsi A..." required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Pilihan Jawaban B *</label>
                            <input type="text" name="option_b" class="form-control text-sm" placeholder="Teks opsi B..." required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Pilihan Jawaban C *</label>
                            <input type="text" name="option_c" class="form-control text-sm" placeholder="Teks opsi C..." required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Pilihan Jawaban D *</label>
                            <input type="text" name="option_d" class="form-control text-sm" placeholder="Teks opsi D..." required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Pilihan Jawaban E (SMA Standard)</label>
                            <input type="text" name="option_e" class="form-control text-sm" placeholder="Teks opsi E...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-xs font-semibold text-secondary">Kunci Jawaban Benar *</label>
                            <select name="correct_answer" class="form-select text-sm font-bold text-success" required>
                                <option value="A">Pilihan A</option>
                                <option value="B">Pilihan B</option>
                                <option value="C">Pilihan C</option>
                                <option value="D">Pilihan D</option>
                                <option value="E">Pilihan E</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-xs font-semibold text-secondary">Bobot Nilai *</label>
                            <input type="number" step="0.5" name="score_weight" class="form-control text-sm font-mono" value="2.0" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-xs font-semibold text-secondary">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select text-sm">
                                <option value="mudah">Mudah</option>
                                <option value="sedang" selected>Sedang</option>
                                <option value="sulit">Sulit</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-xs font-semibold text-secondary">Status Soal</label>
                            <select name="is_active" class="form-select text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif / Draft</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Pembahasan Soal</label>
                        <textarea name="explanation" class="form-control text-sm" rows="2" placeholder="Uraian pembahasan kunci jawaban untuk evaluasi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-semibold">Simpan Soal PG</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL TAMBAH SOAL ESSAY
     ========================================================================= -->
<div class="modal fade" id="modalAddEssay" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-info text-dark">
                <h5 class="modal-title font-bold">
                    <i class="bi bi-pencil-square me-2"></i>Tambah Soal Essay / Uraian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('teacher/createQuestion') ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="question_bank_id" value="<?= $bank['id'] ?>">
                <input type="hidden" name="question_type" value="essay">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Isi Teks Pertanyaan Essay *</label>
                        <textarea name="question_text" class="form-control text-sm" rows="3" placeholder="Tuliskan soal essay / uraian di sini..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Pedoman Jawaban / Rubrik Penilaian *</label>
                        <textarea name="rubric" class="form-control text-sm" rows="3" placeholder="Tuliskan poin-poin kata kunci dan kriteria penskoran yang diharapkan..." required></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-xs font-semibold text-secondary">Bobot Nilai Maksimal *</label>
                            <input type="number" step="0.5" name="score_weight" class="form-control text-sm font-mono" value="10.0" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-xs font-semibold text-secondary">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select text-sm">
                                <option value="mudah">Mudah</option>
                                <option value="sedang" selected>Sedang</option>
                                <option value="sulit">Sulit</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-xs font-semibold text-secondary">Status Soal</label>
                            <select name="is_active" class="form-select text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif / Draft</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Pembahasan Soal</label>
                        <textarea name="explanation" class="form-control text-sm" rows="2" placeholder="Uraian pembahasan lengkap soal essay..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-dark btn-sm px-4 font-semibold">Simpan Soal Essay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL GENERATOR SOAL OTOMATIS (AI / SMART ENGINE)
     ========================================================================= -->
<div class="modal fade" id="modalAutoGenerateSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-gradient bg-success text-white">
                <div>
                    <h5 class="modal-title font-bold mb-0">
                        <i class="bi bi-robot me-2"></i>Generator Butir Soal & Pilihan Ganda Otomatis
                    </h5>
                    <small class="text-white-50 text-xs">Membuat butir soal, opsi A-E, kunci jawaban, dan pembahasan tanpa ketik manual</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('teacher/generateQuestionsAuto') ?>" method="POST" id="formAutoGenerate">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="question_bank_id" value="<?= $bank['id'] ?>">

                <div class="modal-body p-4">
                    <!-- Info Alert -->
                    <div class="alert alert-success-subtle border-success-subtle text-dark rounded-3 p-3 mb-4 text-xs d-flex align-items-start gap-2.5">
                        <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <strong>Otomatisasi Penuh (Zero Manual PG):</strong>
                            <div class="text-muted mt-0.5">
                                Sistem akan otomatis menghasilkan butir-butir soal pilihan ganda berstandar kurikulum SMA tingkat kelas <?= e($bank['grade_level'] ?? '12') ?> untuk mata pelajaran <strong><?= e($bank['subject_name']) ?></strong>. Setiap soal otomatis dilengkapi 5 opsi (A–E), kunci jawaban tervalidasi, dan uraian pembahasan ilmiah.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-secondary">Topik Bahasan / Kisi-Kisi Spesifik (Opsional)</label>
                        <input type="text" name="topic" id="inputAutoTopic" class="form-control text-sm" placeholder="Contoh: Algoritma dan Struktur Data, atau biarkan kosong untuk topik umum mapel ini" value="">
                        
                        <!-- Rekomendasi Topik Cepat -->
                        <div class="d-flex flex-wrap gap-1.5 mt-2 align-items-center">
                            <span class="text-xs text-muted me-1">Pilih Cepat Topik:</span>
                            <?php if (stripos($bank['subject_name'], 'Informatika') !== false): ?>
                                <button type="button" class="badge bg-light text-dark border btn-quick-topic" data-topic="Algoritma dan Pemrograman">Algoritma & Coding</button>
                                <button type="button" class="badge bg-light text-dark border btn-quick-topic" data-topic="Jaringan Komputer dan Internet">Jaringan & OSI Layer</button>
                                <button type="button" class="badge bg-light text-dark border btn-quick-topic" data-topic="Keamanan Siber dan Basis Data">Cybersecurity & Database</button>
                            <?php elseif (stripos($bank['subject_name'], 'Matematika') !== false): ?>
                                <button type="button" class="badge bg-light text-dark border btn-quick-topic" data-topic="Aljabar dan Fungsi">Aljabar & Fungsi</button>
                                <button type="button" class="badge bg-light text-dark border btn-quick-topic" data-topic="Kalkulus dan Turunan">Kalkulus & Integral</button>
                                <button type="button" class="badge bg-light text-dark border btn-quick-topic" data-topic="Vektor dan Geometri">Vektor Dimensi Tiga</button>
                            <?php else: ?>
                                <button type="button" class="badge bg-light text-dark border btn-quick-topic" data-topic="Konsep Dasar dan Teori">Konsep Dasar</button>
                                <button type="button" class="badge bg-light text-dark border btn-quick-topic" data-topic="Analisis Studi Kasus">Studi Kasus</button>
                                <button type="button" class="badge bg-light text-dark border btn-quick-topic" data-topic="Penerapan Praktis">Penerapan Praktis</button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-xs font-semibold text-secondary">Jumlah Butir Soal *</label>
                            <select name="count" class="form-select text-sm font-semibold text-primary">
                                <option value="3">3 Butir Soal (Cepat)</option>
                                <option value="5" selected>5 Butir Soal (Standar Kuis)</option>
                                <option value="10">10 Butir Soal (Paket Ujian)</option>
                                <option value="15">15 Butir Soal (Lengkap)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-xs font-semibold text-secondary">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select text-sm">
                                <option value="campuran" selected>Campuran (Proporsional)</option>
                                <option value="mudah">Mudah</option>
                                <option value="sedang">Sedang</option>
                                <option value="sulit">Sulit (HOTS)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-xs font-semibold text-secondary">Bobot Nilai per Soal</label>
                            <input type="number" step="0.5" name="score_weight" class="form-control text-sm font-mono" value="2.0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-white btn-sm px-4 font-bold shadow-sm" id="btnSubmitAutoGenerate">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Generate & Simpan Soal Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL BATCH IMPORT SOAL (AUTO-PARSE TEKS MASSAL)
     ========================================================================= -->
<div class="modal fade" id="modalBatchImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-light">
                <div>
                    <h5 class="modal-title font-bold text-dark mb-0">
                        <i class="bi bi-clipboard2-plus text-primary me-2"></i>Tempel Teks Kumpulan Soal (Auto-Parse)
                    </h5>
                    <small class="text-muted text-xs">Otomatis memisahkan pertanyaan, opsi A-E, dan kunci jawaban dari teks arsip Anda</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('teacher/batchImportQuestions') ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="question_bank_id" value="<?= $bank['id'] ?>">

                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label text-xs font-semibold text-secondary mb-0">Tempelkan Teks Kumpulan Soal di Bawah *</label>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2 text-xs" id="btnLoadBatchSample">
                            <i class="bi bi-file-earmark-text me-1"></i>Muat Contoh Teks
                        </button>
                    </div>
                    <textarea name="raw_text" id="textareaBatchRaw" class="form-control text-sm font-mono" rows="10" placeholder="1. Pertanyaan soal...
A. Opsi A
B. Opsi B
C. Opsi C
D. Opsi D
E. Opsi E
Kunci: B
Pembahasan: Penjelasan jawaban...

2. Pertanyaan kedua...
A. Opsi A
B. Opsi B
C. Opsi C
D. Opsi D
E. Opsi E
Kunci: A" required></textarea>
                    <small class="text-muted text-xs mt-1.5 d-block">
                        <i class="bi bi-info-circle me-1"></i>Format otomatis mendukung penomoran (1., 2.), opsi A. hingga E., Kunci (Kunci: A), dan Pembahasan (Pembahasan: ...).
                    </small>
                </div>
                <div class="modal-footer border-top bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Urai & Simpan Semua Soal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts Khusus Otomasi Soal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tombol Rekomendasi Topik Cepat di Generator Otomatis
    const topicButtons = document.querySelectorAll('.btn-quick-topic');
    const inputTopic = document.getElementById('inputAutoTopic');
    topicButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (inputTopic) {
                inputTopic.value = this.getAttribute('data-topic');
                inputTopic.focus();
            }
        });
    });

    // 2. Tombol Muat Contoh Teks Soal Massal
    const btnLoadBatchSample = document.getElementById('btnLoadBatchSample');
    const textareaBatch = document.getElementById('textareaBatchRaw');
    if (btnLoadBatchSample && textareaBatch) {
        btnLoadBatchSample.addEventListener('click', function() {
            textareaBatch.value = `1. Manakah dari struktur data berikut yang beroperasi dengan mekanisme LIFO (Last In First Out)?
A. Stack (Tumpukan)
B. Queue (Antrean)
C. Linked List
D. Binary Search Tree
E. Heap
Kunci: A
Pembahasan: Stack menerapkan mekanisme LIFO di mana elemen terakhir yang dimasukkan akan dikeluarkan pertama kali.

2. Komponen prosesor komputer yang bertugas melakukan operasi perhitungan matematika dan pembandingan logika adalah...
A. ALU (Arithmetic Logic Unit)
B. Control Unit (CU)
C. Register Memory
D. Cache L1
E. BIOS Chip
Kunci: A
Pembahasan: ALU bertanggung jawab untuk seluruh komputasi aritmatika dan evaluasi logika dalam arsitektur komputer.

3. Protokol lapisan transport yang berorientasi sambungan (connection-oriented) dan menjamin pengiriman paket secara andal adalah...
A. TCP (Transmission Control Protocol)
B. UDP (User Datagram Protocol)
C. IP (Internet Protocol)
D. ICMP (Internet Control Message Protocol)
E. ARP (Address Resolution Protocol)
Kunci: A
Pembahasan: TCP menjamin keandalan pengiriman paket data melalui three-way handshake dan konfirmasi acknowledgement (ACK).`;
        });
    }

    // 3. Tombol Rekomendasi Contoh Soal Cepat di Modal Add MC
    const btnSampleQuestion = document.getElementById('btnSampleQuestionPrompt');
    const addMcQuestionText = document.getElementById('addMcQuestionText');
    if (btnSampleQuestion && addMcQuestionText) {
        const samplePrompts = [
            "Manakah dari struktur data berikut yang beroperasi dengan prinsip LIFO (Last In First Out)?",
            "Komponen prosesor komputer yang bertugas melakukan operasi perhitungan aritmatika dan evaluasi logika adalah...",
            "Dalam arsitektur model referensi OSI, protokol TCP dan UDP beroperasi pada lapisan (layer)...",
            "Klausa SQL standar yang digunakan untuk mengambil data secara terfilter dan mengelompokkan hasil agregasi adalah...",
            "Sebuah balok bermassa 5 kg ditarik dengan gaya 20 N sejauh 10 meter. Usaha total yang dilakukan gaya tersebut adalah..."
        ];
        let sampleIndex = 0;
        btnSampleQuestion.addEventListener('click', function() {
            addMcQuestionText.value = samplePrompts[sampleIndex % samplePrompts.length];
            sampleIndex++;
            addMcQuestionText.focus();
        });
    }

    // 4. SMART AUTO-FILL OPSI (A-E) & KUNCI VIA AJAX
    const btnAutoOptions = document.getElementById('btnAutoGenerateOptions');
    if (btnAutoOptions) {
        btnAutoOptions.addEventListener('click', async function() {
            const qText = addMcQuestionText ? addMcQuestionText.value.trim() : '';
            if (!qText) {
                alert('Silakan tuliskan teks pertanyaan terlebih dahulu sebelum menekan tombol Generate Opsi Otomatis.');
                if (addMcQuestionText) addMcQuestionText.focus();
                return;
            }

            const originalHtml = btnAutoOptions.innerHTML;
            btnAutoOptions.disabled = true;
            btnAutoOptions.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Mengisi Opsi A-E...';

            try {
                const formData = new FormData();
                formData.append('question_text', qText);
                formData.append('question_bank_id', '<?= $bank['id'] ?>');
                formData.append('difficulty', document.querySelector('#modalAddMC select[name="difficulty"]')?.value || 'sedang');

                const resp = await fetch('<?= base_url('teacher/generateOptionsAjax') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const json = await resp.json();
                if (json.success && json.options) {
                    const optA = document.querySelector('#modalAddMC input[name="option_a"]');
                    const optB = document.querySelector('#modalAddMC input[name="option_b"]');
                    const optC = document.querySelector('#modalAddMC input[name="option_c"]');
                    const optD = document.querySelector('#modalAddMC input[name="option_d"]');
                    const optE = document.querySelector('#modalAddMC input[name="option_e"]');
                    const selKey = document.querySelector('#modalAddMC select[name="correct_answer"]');
                    const expField = document.querySelector('#modalAddMC textarea[name="explanation"]');

                    if (optA) optA.value = json.options.A || '';
                    if (optB) optB.value = json.options.B || '';
                    if (optC) optC.value = json.options.C || '';
                    if (optD) optD.value = json.options.D || '';
                    if (optE) optE.value = json.options.E || '';
                    if (selKey && json.correct_answer) selKey.value = json.correct_answer;
                    if (expField && json.explanation) expField.value = json.explanation;

                    // Efek visual highlight hijau
                    [optA, optB, optC, optD, optE].forEach(input => {
                        if (input) {
                            input.classList.add('border-success', 'bg-success-subtle');
                            setTimeout(() => {
                                input.classList.remove('border-success', 'bg-success-subtle');
                            }, 2000);
                        }
                    });

                    btnAutoOptions.innerHTML = '<i class="bi bi-check-circle-fill text-white me-1"></i> Opsi Berhasil Terisi!';
                    btnAutoOptions.classList.remove('btn-primary');
                    btnAutoOptions.classList.add('btn-success', 'text-white');
                    setTimeout(() => {
                        btnAutoOptions.disabled = false;
                        btnAutoOptions.innerHTML = originalHtml;
                        btnAutoOptions.classList.remove('btn-success', 'text-white');
                        btnAutoOptions.classList.add('btn-primary');
                    }, 2500);
                } else {
                    alert(json.message || 'Gagal membuat opsi otomatis.');
                    btnAutoOptions.disabled = false;
                    btnAutoOptions.innerHTML = originalHtml;
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kendala jaringan saat menghasilkan opsi otomatis.');
                btnAutoOptions.disabled = false;
                btnAutoOptions.innerHTML = originalHtml;
            }
        });
    }

    // 5. Loading state pada submit generate otomatis
    const formAutoGenerate = document.getElementById('formAutoGenerate');
    const btnSubmitAutoGenerate = document.getElementById('btnSubmitAutoGenerate');
    if (formAutoGenerate && btnSubmitAutoGenerate) {
        formAutoGenerate.addEventListener('submit', function() {
            btnSubmitAutoGenerate.disabled = true;
            btnSubmitAutoGenerate.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses Generate Soal...';
        });
    }
});
</script>
