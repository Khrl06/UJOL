<?php
use App\Helpers\CsrfHelper;
$isTeacherView = !empty($isTeacher);
$totalEvents = $totalEvents ?? count($events ?? []);
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? [];
$exams = $exams ?? [];
$events = $events ?? [];
$stats = $stats ?? [];
$postActionUrl = $isTeacherView ? base_url('teacher/proctoring/updateStatus') : base_url('admin/proctoring/updateStatus');
$currentUrl = current_url();
?>

<!-- Header Modul -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-dark rounded-pill px-3 py-1 text-xs font-semibold">
                <i class="bi bi-cpu-fill text-warning me-1"></i> AI Proctoring Engine
            </span>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs font-semibold">
                <i class="bi bi-shield-check me-1"></i> Human-in-the-Loop Active
            </span>
            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 text-xs font-semibold">
                <i class="bi bi-lock-fill me-1"></i> Privacy-by-Design
            </span>
        </div>
        <h3 class="font-bold text-dark mb-0">AI-Assisted Proctoring & Review Insiden</h3>
        <p class="text-muted text-xs mb-0 mt-0.5">
            Sistem pendukung keputusan pengawasan ujian. Keputusan akhir penentuan kecurangan sepenuhnya berada di tangan <?= $isTeacherView ? 'Guru Pengawas' : 'Administrator' ?>.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $isTeacherView ? base_url('teacher/dashboard') : base_url('admin/dashboard') ?>" class="btn btn-outline-secondary rounded-3 btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dasbor
        </a>
    </div>
</div>

<!-- Banner Edukasi & Prinsip Privasi -->
<div class="card-custom p-3.5 mb-4 border-start border-4 border-warning bg-warning-subtle text-dark">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-shield-exclamation text-warning fs-3 flex-shrink-0 mt-0.5"></i>
        <div class="text-xs leading-relaxed">
            <strong class="d-block mb-0.5 font-bold fs-6">Prinsip Pengawasan Human-in-the-Loop & Privasi Peserta:</strong>
            AI Proctoring hanya berfungsi mendeteksi sinyal anomali (*decision support*) dan menghitung skor keyakinan (*confidence score*).
            <strong>Sistem tidak pernah memvonis atau mendiskualifikasi siswa secara otomatis</strong>. Silakan periksa bukti tangkapan layar (*evidence snapshot*), durasi, dan catatan anomali di bawah ini sebelum menetapkan status <strong>REVIEWED (Konfirmasi)</strong> atau <strong>DISMISSED (Abaikan / False Positive)</strong>.
        </div>
    </div>
</div>

<!-- 4 Kartu Statistik Ringkasan Status Review -->
<div class="row g-3 mb-4">
    <!-- Total Insiden -->
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-xs text-muted d-block">Total Insiden</span>
                    <strong class="fs-4 font-mono text-dark"><?= (int)($stats['total'] ?? 0) ?></strong>
                </div>
                <div class="p-2.5 bg-primary-subtle text-primary rounded-3">
                    <i class="bi bi-journal-text fs-4"></i>
                </div>
            </div>
            <small class="text-muted text-xs mt-1 d-block">Seluruh insiden tercatat</small>
        </div>
    </div>

    <!-- Menunggu Review (REVIEW) -->
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-xs text-warning font-semibold d-block">Perlu Review</span>
                    <strong class="fs-4 font-mono text-warning"><?= (int)($stats['REVIEW'] ?? 0) ?></strong>
                </div>
                <div class="p-2.5 bg-warning-subtle text-warning rounded-3">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
            <small class="text-muted text-xs mt-1 d-block">Menunggu keputusan peninjau</small>
        </div>
    </div>

    <!-- Dikonfirmasi Pelanggaran (REVIEWED) -->
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-xs text-danger font-semibold d-block">Telah Dikonfirmasi</span>
                    <strong class="fs-4 font-mono text-danger"><?= (int)($stats['REVIEWED'] ?? 0) ?></strong>
                </div>
                <div class="p-2.5 bg-danger-subtle text-danger rounded-3">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                </div>
            </div>
            <small class="text-muted text-xs mt-1 d-block">Dikonfirmasi sebagai pelanggaran</small>
        </div>
    </div>

    <!-- Diabaikan / Wajar (DISMISSED & NORMAL) -->
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-xs text-success font-semibold d-block">Diabaikan (Wajar)</span>
                    <strong class="fs-4 font-mono text-success"><?= (int)(($stats['DISMISSED'] ?? 0) + ($stats['NORMAL'] ?? 0)) ?></strong>
                </div>
                <div class="p-2.5 bg-success-subtle text-success rounded-3">
                    <i class="bi bi-shield-slash fs-4"></i>
                </div>
            </div>
            <small class="text-muted text-xs mt-1 d-block">False positive / aktivitas wajar</small>
        </div>
    </div>
</div>

<!-- Toolbar Filter Pencarian -->
<div class="card-custom p-3.5 mb-4 shadow-sm">
    <form action="<?= $isTeacherView ? base_url('teacher/monitoring') : base_url('admin/proctoring') ?>" method="GET" class="row g-2.5 align-items-center">
        <!-- Filter Paket Ujian -->
        <div class="col-12 col-md-3">
            <label class="form-label text-xs font-semibold text-secondary mb-1">Paket Ujian</label>
            <select name="exam_id" class="form-select form-select-sm rounded-2 font-medium">
                <option value="">-- Semua Paket Ujian --</option>
                <?php foreach ($exams as $ex): ?>
                    <option value="<?= $ex['id'] ?>" <?= (!empty($filters['exam_id']) && (int)$filters['exam_id'] === (int)$ex['id']) ? 'selected' : '' ?>>
                        <?= e($ex['title']) ?> (ID: <?= $ex['id'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Status Review -->
        <div class="col-6 col-md-2">
            <label class="form-label text-xs font-semibold text-secondary mb-1">Status Review</label>
            <select name="status" class="form-select form-select-sm rounded-2 font-medium">
                <option value="">-- Semua Status --</option>
                <option value="REVIEW" <?= ($filters['status'] ?? '') === 'REVIEW' ? 'selected' : '' ?>>REVIEW (Perlu Cek)</option>
                <option value="REVIEWED" <?= ($filters['status'] ?? '') === 'REVIEWED' ? 'selected' : '' ?>>REVIEWED (Dikonfirmasi)</option>
                <option value="DISMISSED" <?= ($filters['status'] ?? '') === 'DISMISSED' ? 'selected' : '' ?>>DISMISSED (Diabaikan)</option>
                <option value="NORMAL" <?= ($filters['status'] ?? '') === 'NORMAL' ? 'selected' : '' ?>>NORMAL (Wajar)</option>
            </select>
        </div>

        <!-- Filter Jenis Event -->
        <div class="col-6 col-md-2">
            <label class="form-label text-xs font-semibold text-secondary mb-1">Jenis Anomali</label>
            <select name="event_type" class="form-select form-select-sm rounded-2 font-medium">
                <option value="">-- Semua Event --</option>
                <option value="FACE_MISSING" <?= ($filters['event_type'] ?? '') === 'FACE_MISSING' ? 'selected' : '' ?>>FACE_MISSING</option>
                <option value="MULTIPLE_FACE" <?= ($filters['event_type'] ?? '') === 'MULTIPLE_FACE' ? 'selected' : '' ?>>MULTIPLE_FACE</option>
                <option value="FACE_TOO_FAR" <?= ($filters['event_type'] ?? '') === 'FACE_TOO_FAR' ? 'selected' : '' ?>>FACE_TOO_FAR</option>
                <option value="CAMERA_BLOCKED" <?= ($filters['event_type'] ?? '') === 'CAMERA_BLOCKED' ? 'selected' : '' ?>>CAMERA_BLOCKED</option>
                <option value="CAMERA_DISCONNECTED" <?= ($filters['event_type'] ?? '') === 'CAMERA_DISCONNECTED' ? 'selected' : '' ?>>CAMERA_DISCONNECTED</option>
                <option value="SUSPICIOUS_OBJECT" <?= ($filters['event_type'] ?? '') === 'SUSPICIOUS_OBJECT' ? 'selected' : '' ?>>SUSPICIOUS_OBJECT</option>
                <option value="BROWSER_ACTIVITY_EVENT" <?= ($filters['event_type'] ?? '') === 'BROWSER_ACTIVITY_EVENT' ? 'selected' : '' ?>>BROWSER_ACTIVITY_EVENT</option>
            </select>
        </div>

        <!-- Filter Kamera -->
        <div class="col-6 col-md-2">
            <label class="form-label text-xs font-semibold text-secondary mb-1">Sumber Kamera</label>
            <select name="camera" class="form-select form-select-sm rounded-2 font-medium">
                <option value="">-- Semua Sumber --</option>
                <option value="cam1" <?= ($filters['camera'] ?? '') === 'cam1' ? 'selected' : '' ?>>Kamera 1 (Wajah)</option>
                <option value="cam2" <?= ($filters['camera'] ?? '') === 'cam2' ? 'selected' : '' ?>>Kamera 2 (Meja)</option>
                <option value="system" <?= ($filters['camera'] ?? '') === 'system' ? 'selected' : '' ?>>Sistem (Browser)</option>
            </select>
        </div>

        <!-- Input Pencarian & Tombol Aksi -->
        <div class="col-6 col-md-3 d-flex align-items-end gap-2">
            <div class="flex-grow-1">
                <label class="form-label text-xs font-semibold text-secondary mb-1">Pencarian</label>
                <input type="text" name="q" class="form-control form-control-sm rounded-2" placeholder="Nama siswa / NISN..." value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3" title="Terapkan Filter">
                <i class="bi bi-search"></i>
            </button>
            <a href="<?= $isTeacherView ? base_url('teacher/monitoring') : base_url('admin/proctoring') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-2.5" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tabel Daftar Insiden Pengawasan AI -->
<div class="card-custom p-0 overflow-hidden shadow-sm mb-4">
    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
        <h6 class="font-bold text-dark mb-0">
            <i class="bi bi-camera-video-fill text-primary me-2"></i>Daftar Log Insiden Pengawasan (<?= number_format($totalEvents) ?> Kejadian)
        </h6>
        <span class="text-xs text-muted">Halaman <?= $page ?> dari <?= $totalPages ?></span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-xs">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 py-3" style="width: 50px;">ID</th>
                    <th style="width: 100px;">Bukti Visual</th>
                    <th>Peserta & Kelas</th>
                    <th>Paket Ujian</th>
                    <th>Jenis Anomali</th>
                    <th>Kamera</th>
                    <th>Confidence AI</th>
                    <th>Durasi</th>
                    <th>Waktu Kejadian</th>
                    <th>Status Review</th>
                    <th class="text-end pe-3" style="width: 160px;">Keputusan Peninjau</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
                            <strong class="d-block text-sm">Tidak Ada Insiden Proctoring Ditemukan</strong>
                            <span class="text-xs">Seluruh sesi ujian berjalan lancar atau tidak ada data yang cocok dengan kriteria filter.</span>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $evt): ?>
                        <?php
                            // Format badge tipe event
                            $badgeColor = 'secondary';
                            $typeLabel = $evt['event_type'];
                            switch ($evt['event_type']) {
                                case 'FACE_MISSING':
                                    $badgeColor = 'danger';
                                    $typeLabel = 'Wajah Hilang';
                                    break;
                                case 'MULTIPLE_FACE':
                                    $badgeColor = 'danger';
                                    $typeLabel = 'Banyak Wajah';
                                    break;
                                case 'CAMERA_BLOCKED':
                                    $badgeColor = 'danger';
                                    $typeLabel = 'Kamera Ditutup';
                                    break;
                                case 'CAMERA_DISCONNECTED':
                                    $badgeColor = 'dark';
                                    $typeLabel = 'Kamera Terputus';
                                    break;
                                case 'SUSPICIOUS_OBJECT':
                                    $badgeColor = 'warning text-dark';
                                    $typeLabel = 'Objek Mencurigakan';
                                    break;
                                case 'FACE_TOO_FAR':
                                    $badgeColor = 'warning text-dark';
                                    $typeLabel = 'Terlalu Jauh';
                                    break;
                                case 'BROWSER_ACTIVITY_EVENT':
                                    $badgeColor = 'info text-dark';
                                    $typeLabel = 'Pindah Tab Browser';
                                    break;
                            }

                            // Format badge status review
                            $statusBadge = 'bg-secondary';
                            $statusText = $evt['status'];
                            switch ($evt['status']) {
                                case 'REVIEW':
                                    $statusBadge = 'bg-warning text-dark';
                                    $statusText = 'Perlu Review';
                                    break;
                                case 'REVIEWED':
                                    $statusBadge = 'bg-danger text-white';
                                    $statusText = 'Dikonfirmasi';
                                    break;
                                case 'DISMISSED':
                                    $statusBadge = 'bg-secondary text-white';
                                    $statusText = 'Diabaikan';
                                    break;
                                case 'NORMAL':
                                    $statusBadge = 'bg-success text-white';
                                    $statusText = 'Wajar (Normal)';
                                    break;
                            }

                            $confidence = (float)($evt['confidence_score'] ?? 0.0);
                            $evidenceUrl = !empty($evt['evidence_reference']) ? base_url($evt['evidence_reference']) : (!empty($evt['snapshot_url']) ? base_url($evt['snapshot_url']) : null);
                        ?>
                        <tr>
                            <td class="ps-3 font-mono font-bold text-muted">#<?= $evt['id'] ?></td>
                            <td>
                                <?php if ($evidenceUrl): ?>
                                    <a href="javascript:void(0)" onclick="previewEvidence('<?= e($evidenceUrl) ?>', '<?= e($evt['event_type']) ?>', '<?= e($evt['student_name']) ?>', '<?= $evt['event_time'] ?>')" title="Klik untuk perbesar bukti">
                                        <img src="<?= e($evidenceUrl) ?>" alt="Snapshot Bukti" class="rounded-2 border shadow-sm" style="width: 68px; height: 50px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/68x50?text=No+Img'">
                                    </a>
                                <?php else: ?>
                                    <div class="rounded-2 border bg-light d-flex align-items-center justify-content-center text-muted" style="width: 68px; height: 50px;" title="Tidak ada snapshot tersimpan">
                                        <i class="bi bi-camera-video-off fs-6"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="d-block text-dark font-semibold"><?= e($evt['student_name']) ?></strong>
                                <span class="text-muted text-xs">NISN: <?= e($evt['nisn']) ?> | <?= e($evt['class_name']) ?></span>
                            </td>
                            <td>
                                <span class="d-block text-dark font-medium line-clamp-1" title="<?= e($evt['exam_title']) ?>"><?= e($evt['exam_title']) ?></span>
                                <span class="text-muted" style="font-size: 11px;">Partisipan ID: #<?= $evt['exam_participant_id'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $badgeColor ?> rounded-pill px-2.5 py-1 text-xs">
                                    <?= e($typeLabel) ?>
                                </span>
                                <small class="d-block text-muted text-xs mt-0.5"><?= e($evt['event_type']) ?></small>
                            </td>
                            <td>
                                <?php if ($evt['camera'] === 'cam1'): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 px-2 py-0.5">Cam 1 (Wajah)</span>
                                <?php elseif ($evt['camera'] === 'cam2'): ?>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-2 px-2 py-0.5">Cam 2 (Meja)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border rounded-2 px-2 py-0.5">Sistem (Browser)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 50px;">
                                        <div class="progress-bar <?= $confidence >= 85 ? 'bg-danger' : ($confidence >= 70 ? 'bg-warning' : 'bg-success') ?>" style="width: <?= $confidence ?>%"></div>
                                    </div>
                                    <span class="font-mono font-bold text-xs"><?= number_format($confidence, 1) ?>%</span>
                                </div>
                            </td>
                            <td>
                                <span class="font-mono text-dark font-semibold"><?= (int)$evt['duration'] ?> dtk</span>
                            </td>
                            <td>
                                <span class="d-block text-dark font-medium"><?= date('H:i:s', strtotime($evt['event_time'])) ?></span>
                                <small class="text-muted"><?= date('d M Y', strtotime($evt['event_time'])) ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $statusBadge ?> rounded-pill px-2.5 py-1 text-xs font-semibold">
                                    <?= e($statusText) ?>
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <!-- Tombol Aksi Cepat Review (Human-in-the-Loop) -->
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-danger btn-sm px-2 py-1" title="Konfirmasi Pelanggaran (REVIEWED)" onclick="openReviewModal(<?= $evt['id'] ?>, 'REVIEWED', '<?= e($evt['student_name']) ?>', '<?= e($evt['event_type']) ?>')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" title="Abaikan / False Positive (DISMISSED)" onclick="openReviewModal(<?= $evt['id'] ?>, 'DISMISSED', '<?= e($evt['student_name']) ?>', '<?= e($evt['event_type']) ?>')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-2 py-1" title="Detail & Catatan" onclick="openReviewModal(<?= $evt['id'] ?>, '<?= e($evt['status']) ?>', '<?= e($evt['student_name']) ?>', '<?= e($evt['event_type']) ?>', '<?= e($evt['description']) ?>')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="p-3 border-top d-flex justify-content-between align-items-center bg-light">
            <span class="text-xs text-muted">Menampilkan <?= count($events) ?> dari total <?= number_format($totalEvents) ?> data</span>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $currentUrl ?>?page=<?= $p ?>&exam_id=<?= $filters['exam_id'] ?? '' ?>&status=<?= $filters['status'] ?? '' ?>&event_type=<?= $filters['event_type'] ?? '' ?>&camera=<?= $filters['camera'] ?? '' ?>&q=<?= urlencode($filters['search'] ?? '') ?>">
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL REVIEW KEPUTUSAN GURU / ADMINISTRATOR -->
<div class="modal fade" id="modalReviewAction" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-dark text-white">
                <h6 class="modal-title font-bold" id="reviewModalTitle">
                    <i class="bi bi-shield-check text-warning me-2"></i>Peninjauan Insiden Proctoring
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= $postActionUrl ?>" method="POST">
                <?= CsrfHelper::tokenField() ?>
                <input type="hidden" name="event_id" id="reviewEventId" value="">
                <input type="hidden" name="redirect_url" value="<?= e($currentUrl) ?>">

                <div class="modal-body p-4 text-start">
                    <p class="text-xs text-muted mb-3" id="reviewMetaText">
                        Meninjau insiden anomali peserta:
                    </p>

                    <div class="mb-3">
                        <label class="form-label text-xs font-bold text-dark">Keputusan Peninjauan (Status) *</label>
                        <select name="status" id="reviewStatusSelect" class="form-select rounded-2 font-semibold text-xs" required>
                            <option value="REVIEWED">🔴 REVIEWED — Konfirmasi sebagai Indikasi Pelanggaran</option>
                            <option value="DISMISSED">⚪ DISMISSED — Abaikan (False Positive / Wajar)</option>
                            <option value="NORMAL">🟢 NORMAL — Aktivitas Normal</option>
                            <option value="REVIEW">🟡 REVIEW — Biarkan dalam Antrean Peninjauan</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-xs font-bold text-dark">Catatan / Keterangan Peninjau (Opsional):</label>
                        <textarea name="notes" id="reviewNotesInput" class="form-control text-xs" rows="3" placeholder="Contoh: Terlihat peserta menengok ke samping karena dipanggil pengawas, bukan kecurangan."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-bold">Simpan Keputusan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW BUKTI SNAPSHOT FOTO -->
<div class="modal fade" id="modalEvidencePreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-dark text-white py-2.5">
                <h6 class="modal-title font-bold text-sm" id="evidenceTitle">
                    <i class="bi bi-camera-fill text-warning me-2"></i>Bukti Snapshot Tangkapan Layar
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-black">
                <img id="evidenceModalImage" src="" alt="Evidence Full" class="img-fluid rounded-2 shadow border border-secondary" style="max-height: 500px;">
            </div>
            <div class="modal-footer border-top bg-light py-2 justify-content-between text-xs text-muted">
                <span id="evidenceMeta">Peserta: - | Waktu: -</span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Javascript Modal Helpers -->
<script>
function openReviewModal(eventId, initialStatus, studentName, eventType, existingDesc = '') {
    document.getElementById('reviewEventId').value = eventId;
    document.getElementById('reviewStatusSelect').value = initialStatus || 'REVIEW';
    document.getElementById('reviewMetaText').innerHTML = `Meninjau Insiden <strong>#${eventId}</strong> (${eventType}) pada peserta: <strong>${studentName}</strong>`;
    document.getElementById('reviewNotesInput').value = '';

    const modal = new bootstrap.Modal(document.getElementById('modalReviewAction'));
    modal.show();
}

function previewEvidence(imgUrl, eventType, studentName, time) {
    document.getElementById('evidenceModalImage').src = imgUrl;
    document.getElementById('evidenceTitle').innerHTML = `<i class="bi bi-camera-fill text-warning me-2"></i>Bukti Snapshot: ${eventType}`;
    document.getElementById('evidenceMeta').innerHTML = `Peserta: <strong>${studentName}</strong> | Waktu: <strong>${time}</strong>`;

    const modal = new bootstrap.Modal(document.getElementById('modalEvidencePreview'));
    modal.show();
}
</script>
