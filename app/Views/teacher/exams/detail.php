<?php
use App\Helpers\CsrfHelper;
use App\Models\QuestionModel;
$exam = array_merge([
    'subject_name' => '',
    'subject_code' => '',
    'status' => 'draft',
    'title' => '',
    'description' => '',
    'exam_date' => '',
    'start_time' => '',
    'end_time' => '',
    'duration_minutes' => 0,
    'total_questions' => 0,
    'passing_grade' => 75,
    'token' => '',
    'enable_dual_camera' => 0,
    'enable_ai_proctoring' => 0,
    'is_random_questions' => 0,
    'is_random_options' => 0,
    'question_bank_title' => '',
    'bank_title' => '',
    'submitted_count' => 0,
    'total_participants' => 0
], $exam ?? []);
$participants = $participants ?? [];
$questions = $questions ?? [];
?>

<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="badge bg-primary rounded-pill px-3 py-1 text-xs font-semibold">
                <i class="bi bi-journal-bookmark me-1"></i><?= e($exam['subject_name']) ?> (<?= e($exam['subject_code']) ?>)
            </span>
            <?php
                $statusUpper = strtoupper($exam['status']);
                $badgeStatus = match($exam['status']) {
                    'published' => 'bg-success text-white',
                    'ongoing'   => 'bg-danger text-white',
                    'completed' => 'bg-info text-dark',
                    'cancelled' => 'bg-dark text-white',
                    default     => 'bg-secondary text-white'
                };
            ?>
            <span class="badge <?= $badgeStatus ?> rounded-pill px-3 py-1 text-xs font-semibold">
                <?= $statusUpper ?>
            </span>
        </div>
        <h3 class="font-bold text-dark mb-0"><?= e($exam['title']) ?></h3>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('teacher/exams') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Ujian
        </a>

        <!-- Publish / Unpublish Action -->
        <?php if ($exam['status'] === 'draft'): ?>
            <form action="<?= base_url('teacher/publishExam') ?>" method="POST" class="d-inline">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="id" value="<?= $exam['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm rounded-3 px-3 font-semibold shadow-sm">
                    <i class="bi bi-send-check-fill me-1"></i> Publish Ujian
                </button>
            </form>
        <?php elseif ($exam['status'] === 'published'): ?>
            <form action="<?= base_url('teacher/unpublishExam') ?>" method="POST" class="d-inline">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="id" value="<?= $exam['id'] ?>">
                <button type="submit" class="btn btn-outline-warning btn-sm rounded-3 px-3 font-semibold text-dark">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Unpublish (Draft)
                </button>
            </form>
        <?php endif; ?>

        <a href="<?= base_url('teacher/exams/' . $exam['id'] . '/monitoring') ?>" class="btn btn-danger btn-sm rounded-3 px-3 font-semibold shadow-sm">
            <i class="bi bi-broadcast-pin me-1"></i> Live Monitoring Peserta
        </a>

        <a href="<?= base_url('teacher/exams/' . $exam['id'] . '/edit') ?>" class="btn btn-warning text-dark rounded-3 btn-sm px-3 font-semibold">
            <i class="bi bi-pencil-square me-1"></i> Edit Pengaturan
        </a>
    </div>
</div>

<!-- Top Stats & Token Banner Card -->
<div class="row g-3 mb-4">
    <!-- Token Banner Card -->
    <div class="col-12 col-md-5 col-xl-4">
        <div class="card-custom p-4 bg-dark text-white h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs font-semibold text-light text-uppercase tracking-wider">Token Akses Ujian</span>
                    <form action="<?= base_url('teacher/regenerateToken') ?>" method="POST" class="d-inline">
                        <?= CsrfHelper::tokenField() ?>
                        <input type="hidden" name="id" value="<?= $exam['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-light py-0.5 px-2 text-xs" title="Regenerasi Token Baru" onclick="return confirm('Apakah Anda yakin ingin mengganti token ujian ini?')">
                            <i class="bi bi-arrow-repeat"></i> Ganti
                        </button>
                    </form>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="display-6 font-mono font-bold text-warning letter-spacing-2" id="examTokenText"><?= e($exam['token']) ?></span>
                    <button type="button" class="btn btn-sm btn-outline-warning p-1.5" onclick="copyToken()" title="Salin Token">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <p class="text-white-50 text-xs mb-0">Bagikan token ini kepada peserta didik saat ujian hendak dimulai.</p>
            </div>
            <div class="pt-3 mt-3 border-top border-secondary text-xs text-light d-flex justify-content-between">
                <span>Durasi: <strong><?= $exam['duration_minutes'] ?> Menit</strong></span>
                <span>KKM: <strong><?= $exam['passing_grade'] ?></strong></span>
            </div>
        </div>
    </div>

    <!-- Quick Meta Info Cards -->
    <div class="col-12 col-md-7 col-xl-8">
        <div class="card-custom p-4 h-100">
            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted text-xs d-block mb-1"><i class="bi bi-calendar-event text-primary me-1"></i>Rentang Waktu Akses</small>
                        <div class="font-bold text-dark text-sm">
                            <?= date('d M Y', strtotime($exam['start_date'])) ?> s/d <?= date('d M Y', strtotime($exam['end_date'])) ?>
                        </div>
                        <div class="text-muted text-xs">Pukul <?= substr($exam['start_time'], 0, 5) ?> - <?= substr($exam['end_time'], 0, 5) ?> WIB</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted text-xs d-block mb-1"><i class="bi bi-people-fill text-success me-1"></i>Total Peserta Terdaftar</small>
                        <div class="font-bold text-dark text-sm">
                            <?= count($participants) ?> Siswa
                        </div>
                        <div class="text-success text-xs font-semibold"><?= $exam['submitted_count'] ?? 0 ?> Siswa Selesai Mengerjakan</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted text-xs d-block mb-1"><i class="bi bi-journal-text text-info me-1"></i>Sumber Bank Soal</small>
                        <div class="font-bold text-dark text-sm text-truncate">
                            <?= e($exam['question_bank_title'] ?: 'Bank Soal Guru') ?>
                        </div>
                        <div class="text-muted text-xs">Total: <?= count($questions) ?> Butir Soal</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted text-xs d-block mb-1"><i class="bi bi-shield-check text-warning me-1"></i>Keamanan & Proctoring</small>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <?php if (!empty($exam['enable_dual_camera'])): ?>
                                <span class="badge bg-info-subtle text-info text-xs"><i class="bi bi-webcam-fill me-1"></i>Dual Camera</span>
                            <?php endif; ?>
                            <?php if (!empty($exam['enable_ai_proctoring'])): ?>
                                <span class="badge bg-warning-subtle text-warning text-xs"><i class="bi bi-robot me-1"></i>AI Proctor</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 gap-2" id="examDetailTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-3 text-sm font-semibold px-4" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants-pane" type="button" role="tab">
            <i class="bi bi-people-fill me-1"></i> Daftar Peserta (<?= count($participants) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-3 text-sm font-semibold px-4" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions-pane" type="button" role="tab">
            <i class="bi bi-list-check me-1"></i> Butir Soal Ujian (<?= count($questions) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-3 text-sm font-semibold px-4" id="rules-tab" data-bs-toggle="tab" data-bs-target="#rules-pane" type="button" role="tab">
            <i class="bi bi-sliders me-1"></i> Ringkasan & Aturan
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="examDetailTabContent">
    <!-- 1. TAB PESERTA DIDIK -->
    <div class="tab-pane fade show active" id="participants-pane" role="tabpanel">
        <div class="card-custom p-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="font-bold text-dark mb-0">Daftar Peserta Didik Terdaftar</h5>
                    <small class="text-muted text-xs">Peserta yang berhak mengikuti sesi ujian dengan token di atas.</small>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-3 px-3 font-semibold" data-bs-toggle="modal" data-bs-target="#modalAssignClasses">
                    <i class="bi bi-person-plus-fill me-1"></i> + Tambah Rombel Kelas
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-xs text-uppercase text-muted">
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama Lengkap Siswa</th>
                            <th>Rombel Kelas</th>
                            <th>Status Pengerjaan</th>
                            <th>Nilai Akhir</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php if (!empty($participants)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($participants as $p): ?>
                                <?php
                                    $statusP = match($p['status']) {
                                        'submitted' => '<span class="badge bg-success-subtle text-success text-xs">Selesai</span>',
                                        'ongoing'   => '<span class="badge bg-warning-subtle text-warning text-xs">Sedang Mengerjakan</span>',
                                        'graded'    => '<span class="badge bg-info-subtle text-info text-xs">Ternilai</span>',
                                        default     => '<span class="badge bg-secondary-subtle text-secondary text-xs">Terdaftar</span>'
                                    };
                                ?>
                                <tr>
                                    <td class="text-muted font-mono text-xs"><?= $no++ ?></td>
                                    <td class="font-mono text-xs text-dark"><?= e($p['nisn']) ?></td>
                                    <td>
                                        <div class="font-semibold text-dark"><?= e($p['student_name']) ?></div>
                                        <small class="text-muted text-xs"><?= e($p['student_email'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border font-mono text-xs">
                                            <?= e($p['class_name']) ?>
                                        </span>
                                    </td>
                                    <td><?= $statusP ?></td>
                                    <td>
                                        <?php if ($p['final_score'] !== null): ?>
                                            <span class="font-bold <?= (float)$p['final_score'] >= (float)$exam['passing_grade'] ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format((float)$p['final_score'], 1) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <form action="<?= base_url('teacher/removeParticipant') ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus siswa ini dari daftar ujian?')">
                                            <?= CsrfHelper::tokenField() ?>
                                            <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                                            <input type="hidden" name="student_id" value="<?= $p['student_id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0.5 px-2 text-xs" title="Hapus dari Ujian">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x display-6 text-muted d-block mb-2"></i>
                                    Belum ada peserta yang dialokasikan ke ujian ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. TAB BUTIR SOAL -->
    <div class="tab-pane fade" id="questions-pane" role="tabpanel">
        <div class="card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="font-bold text-dark mb-0">Tinjauan Butir Soal Terpilih</h5>
                    <small class="text-muted text-xs">Butir soal diambil dari bank soal: <strong><?= e($exam['question_bank_title']) ?></strong></small>
                </div>
                <a href="<?= base_url('teacher/bankQuestions?bank_id=' . $exam['question_bank_id']) ?>" class="btn btn-outline-secondary btn-sm rounded-3 px-3 text-xs">
                    <i class="bi bi-pencil-square me-1"></i> Buka Editor Bank Soal
                </a>
            </div>

            <div class="d-flex flex-column gap-3">
                <?php if (!empty($questions)): ?>
                    <?php $qNum = 1; ?>
                    <?php foreach ($questions as $q): ?>
                        <?php $options = QuestionModel::decodeOptions($q['options']); ?>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-dark rounded-pill px-2.5 py-1 text-xs">No. <?= $qNum++ ?></span>
                                <div class="d-flex gap-1">
                                    <span class="badge bg-primary-subtle text-primary text-xs"><?= strtoupper($q['question_type']) ?></span>
                                    <span class="badge bg-secondary-subtle text-secondary text-xs">Bobot: <?= $q['score_weight'] ?></span>
                                </div>
                            </div>
                            <div class="text-dark text-sm font-medium mb-2"><?= nl2br(e($q['question_text'])) ?></div>
                            <?php if ($q['question_type'] === 'multiple_choice' && !empty($options)): ?>
                                <div class="row g-1">
                                    <?php foreach (['A', 'B', 'C', 'D', 'E'] as $k): ?>
                                        <?php if (isset($options[$k])): ?>
                                            <div class="col-12 col-md-6">
                                                <div class="text-xs p-1.5 rounded <?= strtoupper($q['correct_answer']) === $k ? 'bg-success-subtle text-success font-bold' : 'text-muted' ?>">
                                                    <strong><?= $k ?>.</strong> <?= e($options[$k]) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                        Tidak ada butir soal dalam bank soal terkait.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 3. TAB RINGKASAN & ATURAN -->
    <div class="tab-pane fade" id="rules-pane" role="tabpanel">
        <div class="card-custom p-4">
            <h5 class="font-bold text-dark mb-3 pb-2 border-bottom">Konfigurasi Parameter Asesmen</h5>
            <div class="row g-3 text-sm">
                <div class="col-12 col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Acak Urutan Soal:</span>
                            <span class="font-semibold text-dark"><?= !empty($exam['is_random_questions']) ? 'Aktif (Ya)' : 'Tidak (Sesuai Urutan Bank)' ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Acak Opsi Pilihan Ganda:</span>
                            <span class="font-semibold text-dark"><?= !empty($exam['is_random_options']) ? 'Aktif (Ya)' : 'Tidak' ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Maksimal Percobaan:</span>
                            <span class="font-semibold text-dark"><?= $exam['max_attempts'] ?> Kali</span>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Dual Camera Monitoring:</span>
                            <span class="font-semibold <?= !empty($exam['enable_dual_camera']) ? 'text-success' : 'text-secondary' ?>">
                                <?= !empty($exam['enable_dual_camera']) ? 'Diwajibkan (Depan & Samping)' : 'Nonaktif' ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">AI Proctoring (Deteksi Pelanggaran):</span>
                            <span class="font-semibold <?= !empty($exam['enable_ai_proctoring']) ? 'text-success' : 'text-secondary' ?>">
                                <?= !empty($exam['enable_ai_proctoring']) ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Standar KKM:</span>
                            <span class="font-semibold text-primary"><?= $exam['passing_grade'] ?> Poin</span>
                        </li>
                    </ul>
                </div>
            </div>

            <?php if (!empty($exam['description'])): ?>
                <div class="mt-4 pt-3 border-top">
                    <h6 class="font-bold text-dark mb-1">Tata Tertib & Petunjuk Pengerjaan:</h6>
                    <div class="p-3 bg-light rounded-3 border text-xs text-muted">
                        <?= nl2br(e($exam['description'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Kelas Peserta -->
<div class="modal fade" id="modalAssignClasses" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-primary text-white">
                <h5 class="modal-title font-bold">
                    <i class="bi bi-person-plus-fill me-2"></i>Alokasikan Rombel Kelas Tambahan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('teacher/assignParticipants') ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">

                <div class="modal-body p-4">
                    <p class="text-xs text-muted mb-3">Pilih rombel kelas yang ingin ditambahkan ke daftar peserta ujian ini:</p>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($classes as $c): ?>
                            <div class="p-2.5 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="class_ids[]" value="<?= $c['id'] ?>" id="assign_class_<?= $c['id'] ?>">
                                    <label class="form-check-label font-bold text-sm text-dark cursor-pointer" for="assign_class_<?= $c['id'] ?>">
                                        <?= e($c['class_name']) ?>
                                    </label>
                                    <small class="text-muted text-xs d-block"><?= e($c['major']) ?></small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary rounded-pill text-xs">
                                    <?= $c['total_students'] ?? 0 ?> Siswa
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-semibold">Tambahkan Peserta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyToken() {
    const token = document.getElementById('examTokenText').innerText;
    navigator.clipboard.writeText(token).then(() => {
        alert('Token ujian berhasil disalin: ' + token);
    });
}
</script>
