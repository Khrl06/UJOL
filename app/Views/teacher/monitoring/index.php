<?php
use App\Helpers\CsrfHelper;
$csrfToken = CsrfHelper::generateToken();
$exam = $liveData['exam'] ?? null;
$summary = $liveData['summary'] ?? ['total' => 0, 'ongoing' => 0, 'submitted' => 0, 'registered' => 0, 'needs_review' => 0, 'high_risk' => 0, 'medium_risk' => 0, 'low_risk' => 0];
$participants = $liveData['participants'] ?? [];
?>

<!-- Header & Bar Kontrol Live Monitoring -->
<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
            <span class="badge bg-danger rounded-pill px-3 py-1 text-xs font-bold d-inline-flex align-items-center">
                <span class="spinner-grow spinner-grow-sm me-1.5" role="status" aria-hidden="true" style="width: 0.65rem; height: 0.65rem;"></span>
                LIVE MONITORING
            </span>
            <span class="badge bg-dark rounded-pill px-2.5 py-1 text-xs font-semibold">
                <i class="bi bi-broadcast me-1 text-info"></i> Transport: Polling AJAX (WebSocket-Ready)
            </span>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs font-semibold">
                <i class="bi bi-shield-check me-1"></i> Human-in-the-Loop Active
            </span>
        </div>
        <h3 class="font-bold text-dark mb-0">Live Exam Monitoring Dashboard</h3>
        <p class="text-muted text-xs mb-0 mt-0.5">
            Pantau kehadiran, progres pengerjaan, status kamera ganda, dan sinyal AI peserta secara real-time.
        </p>
    </div>

    <!-- Toolbar Kontrol: Pemilih Ujian, Auto Refresh, & Tombol Manual -->
    <div class="d-flex align-items-center gap-2.5 flex-wrap">
        <!-- Pemilih Paket Ujian -->
        <form method="GET" action="<?= base_url('teacher/monitoring') ?>" class="m-0">
            <select name="exam_id" class="form-select form-select-sm rounded-3 font-semibold text-xs border-primary shadow-sm" onchange="this.form.submit()" style="min-width: 240px;">
                <?php if (empty($myExams)): ?>
                    <option value="">-- Belum Ada Paket Ujian --</option>
                <?php else: ?>
                    <?php foreach ($myExams as $ex): ?>
                        <option value="<?= $ex['id'] ?>" <?= ((int)$selectedExamId === (int)$ex['id']) ? 'selected' : '' ?>>
                            <?= e($ex['title']) ?> (ID: <?= $ex['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </form>

        <!-- Toggle Auto Refresh -->
        <div class="form-check form-switch card-custom py-1.5 px-3 mb-0 d-flex align-items-center gap-2 border bg-light shadow-sm" style="height: 33px;">
            <input class="form-check-input ms-0 me-1" type="checkbox" role="switch" id="toggleAutoRefresh" checked>
            <label class="form-check-label text-xs font-bold text-dark mb-0" for="toggleAutoRefresh">Auto 4s</label>
        </div>

        <!-- Tombol Manual Refresh -->
        <button type="button" class="btn btn-outline-primary btn-sm rounded-3 px-3 d-flex align-items-center gap-1.5 shadow-sm" id="btnManualRefresh" title="Segarkan Data Sekarang">
            <i class="bi bi-arrow-clockwise" id="iconRefresh"></i>
            <span class="font-semibold text-xs">Segarkan</span>
        </button>

        <?php if (!empty($selectedExamId)): ?>
            <a href="<?= base_url('teacher/exams/' . $selectedExamId) ?>" class="btn btn-outline-secondary btn-sm rounded-3 px-2.5 shadow-sm" title="Pengaturan Ujian">
                <i class="bi bi-gear-fill"></i>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($selectedExamId) || empty($exam)): ?>
    <div class="card-custom p-5 text-center shadow-sm">
        <i class="bi bi-journal-x text-muted display-4 d-block mb-3"></i>
        <h5 class="font-bold text-dark mb-1">Belum Ada Paket Ujian yang Dipilih</h5>
        <p class="text-muted text-xs mb-3">Silakan buat atau pilih paket ujian Anda untuk memulai sesi Live Monitoring pengawasan.</p>
        <a href="<?= base_url('teacher/exams') ?>" class="btn btn-primary btn-sm rounded-3 px-4">
            <i class="bi bi-arrow-left me-1"></i> Ke Daftar Paket Ujian
        </a>
    </div>
<?php else: ?>

<!-- Top Summary Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Peserta Terdaftar -->
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-primary shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-xs text-muted d-block">Total Peserta</span>
                    <strong class="fs-4 font-mono text-dark" id="statTotalParticipants"><?= (int)$summary['total'] ?></strong>
                </div>
                <div class="p-2.5 bg-primary-subtle text-primary rounded-3">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
            <small class="text-muted text-xs mt-1 d-block">Terdaftar dalam paket ujian</small>
        </div>
    </div>

    <!-- Sedang Mengerjakan (Active / Ongoing) -->
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-success shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-xs text-success font-semibold d-block">Sedang Mengerjakan</span>
                    <strong class="fs-4 font-mono text-success" id="statOngoingParticipants"><?= (int)$summary['ongoing'] ?></strong>
                </div>
                <div class="p-2.5 bg-success-subtle text-success rounded-3">
                    <i class="bi bi-laptop-fill fs-4"></i>
                </div>
            </div>
            <small class="text-muted text-xs mt-1 d-block">Sesi aktif di Exam Engine</small>
        </div>
    </div>

    <!-- Status AI: NEEDS REVIEW -->
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-warning shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-xs text-warning font-semibold d-block">Perlu Review (AI)</span>
                    <strong class="fs-4 font-mono text-warning" id="statNeedsReviewParticipants"><?= (int)$summary['needs_review'] ?></strong>
                </div>
                <div class="p-2.5 bg-warning-subtle text-warning rounded-3">
                    <i class="bi bi-exclamation-diamond-fill fs-4"></i>
                </div>
            </div>
            <small class="text-muted text-xs mt-1 d-block">Terdapat event anomali baru</small>
        </div>
    </div>

    <!-- Tingkat Risiko Tinggi (HIGH RISK) -->
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-danger shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-xs text-danger font-semibold d-block">Risiko Tinggi (High)</span>
                    <strong class="fs-4 font-mono text-danger" id="statHighRiskParticipants"><?= (int)$summary['high_risk'] ?></strong>
                </div>
                <div class="p-2.5 bg-danger-subtle text-danger rounded-3">
                    <i class="bi bi-shield-fill-exclamation fs-4"></i>
                </div>
            </div>
            <small class="text-muted text-xs mt-1 d-block">Butuh atensi segera pengawas</small>
        </div>
    </div>
</div>

<!-- Toolbar Filter Grid Peserta -->
<div class="card-custom p-3 mb-4 shadow-sm">
    <div class="row g-2.5 align-items-center">
        <!-- Pencarian Nama / NISN -->
        <div class="col-12 col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="filterSearch" class="form-control" placeholder="Cari nama peserta / NISN...">
            </div>
        </div>

        <!-- Filter Status AI -->
        <div class="col-6 col-md-2">
            <select id="filterAiStatus" class="form-select form-select-sm">
                <option value="">-- Semua Status AI --</option>
                <option value="NEEDS REVIEW">NEEDS REVIEW (Perlu Cek)</option>
                <option value="NORMAL">NORMAL (Wajar)</option>
                <option value="REVIEWED">REVIEWED (Ditinjau)</option>
            </select>
        </div>

        <!-- Filter Tingkat Risiko -->
        <div class="col-6 col-md-2">
            <select id="filterRiskLevel" class="form-select form-select-sm">
                <option value="">-- Semua Risiko --</option>
                <option value="HIGH">Risiko Tinggi (HIGH)</option>
                <option value="MEDIUM">Risiko Sedang (MEDIUM)</option>
                <option value="LOW">Risiko Rendah (LOW)</option>
            </select>
        </div>

        <!-- Filter Status Ujian -->
        <div class="col-6 col-md-2">
            <select id="filterExamStatus" class="form-select form-select-sm">
                <option value="">-- Status Ujian --</option>
                <option value="ongoing">Sedang Ujian (Ongoing)</option>
                <option value="registered">Belum Mulai (Registered)</option>
                <option value="submitted">Selesai (Submitted)</option>
            </select>
        </div>

        <!-- Counter Terfilter -->
        <div class="col-6 col-md-2 text-md-end">
            <span class="text-xs text-muted font-medium">Menampilkan: <strong class="text-dark" id="filteredCount"><?= count($participants) ?></strong> peserta</span>
        </div>
    </div>
</div>

<!-- GRID PESERTA UJIAN (BOOTSTRAP CARDS) -->
<div class="row g-3.5" id="participantGridContainer">
    <?php if (empty($participants)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
            <strong class="d-block text-sm">Belum Ada Peserta yang Didaftarkan</strong>
            <span class="text-xs">Silakan alokasikan kelas atau peserta pada pengaturan ujian.</span>
        </div>
    <?php else: ?>
        <?php foreach ($participants as $p): ?>
            <?php
                // AI Status Badge Styling (Strict terminology)
                $aiBadgeClass = 'bg-success text-white';
                if ($p['ai_status'] === 'NEEDS REVIEW') {
                    $aiBadgeClass = 'bg-warning text-dark border border-warning';
                } elseif ($p['ai_status'] === 'REVIEWED') {
                    $aiBadgeClass = 'bg-primary text-white';
                }

                // Risk Badge Styling
                $riskBadgeClass = 'bg-success-subtle text-success border border-success-subtle';
                if ($p['risk_level'] === 'HIGH') {
                    $riskBadgeClass = 'bg-danger text-white';
                } elseif ($p['risk_level'] === 'MEDIUM') {
                    $riskBadgeClass = 'bg-warning-subtle text-dark border border-warning';
                }

                // Cam 1 Badge
                $cam1Badge = 'bg-success-subtle text-success border border-success-subtle';
                if ($p['camera_1'] === 'DISCONNECTED' || $p['camera_1'] === 'BLOCKED') {
                    $cam1Badge = 'bg-danger text-white';
                } elseif ($p['camera_1'] === 'INACTIVE') {
                    $cam1Badge = 'bg-secondary-subtle text-secondary';
                }

                // Cam 2 Badge
                $cam2Badge = 'bg-success-subtle text-success border border-success-subtle';
                if ($p['camera_2'] === 'DISCONNECTED') {
                    $cam2Badge = 'bg-danger text-white';
                } elseif ($p['camera_2'] === 'NOT_REQUIRED') {
                    $cam2Badge = 'bg-light text-muted border';
                } elseif ($p['camera_2'] === 'INACTIVE') {
                    $cam2Badge = 'bg-secondary-subtle text-secondary';
                }

                // Status border accent
                $cardBorderAccent = 'border-top border-4 ';
                if ($p['risk_level'] === 'HIGH') {
                    $cardBorderAccent .= 'border-danger';
                } elseif ($p['ai_status'] === 'NEEDS REVIEW') {
                    $cardBorderAccent .= 'border-warning';
                } elseif ($p['exam_status'] === 'ongoing') {
                    $cardBorderAccent .= 'border-primary';
                } else {
                    $cardBorderAccent .= 'border-secondary';
                }
            ?>
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3 participant-card-wrapper" 
                 data-id="<?= $p['participant_id'] ?>"
                 data-name="<?= strtolower(e($p['student_name'])) ?>"
                 data-nisn="<?= e($p['nisn']) ?>"
                 data-ai="<?= e($p['ai_status']) ?>"
                 data-risk="<?= e($p['risk_level']) ?>"
                 data-status="<?= e($p['exam_status']) ?>">
                <div class="card-custom h-100 p-3.5 d-flex flex-column justify-content-between shadow-sm hover-lift <?= $cardBorderAccent ?> transition-all">
                    <div>
                        <!-- Header Kartu: Nama, Kelas & Status Sesi -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="overflow-hidden">
                                <h6 class="font-bold text-dark text-sm mb-0 text-truncate" title="<?= e($p['student_name']) ?>">
                                    <?= e($p['student_name']) ?>
                                </h6>
                                <span class="text-xs text-muted d-block">
                                    NISN: <?= e($p['nisn']) ?> • <strong class="text-secondary"><?= e($p['class_name']) ?></strong>
                                </span>
                            </div>
                            <div>
                                <?php if ($p['exam_status'] === 'ongoing'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 text-xs font-semibold d-inline-flex align-items-center">
                                        <span class="spinner-grow spinner-grow-sm me-1" style="width: 0.45rem; height: 0.45rem;"></span> Mengerjakan
                                    </span>
                                <?php elseif ($p['exam_status'] === 'submitted' || $p['exam_status'] === 'graded'): ?>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5 text-xs">Selesai</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 text-xs">Belum Mulai</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Progress Soal Bar -->
                        <div class="mb-3 pt-1">
                            <div class="d-flex justify-content-between align-items-center text-xs mb-1 font-medium">
                                <span class="text-muted">Progress Soal:</span>
                                <strong class="text-dark font-mono progress-text"><?= $p['progress']['text'] ?> (<?= $p['progress']['percentage'] ?>%)</strong>
                            </div>
                            <div class="progress rounded-pill" style="height: 7px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     role="progressbar" 
                                     style="width: <?= $p['progress']['percentage'] ?>%" 
                                     aria-valuenow="<?= $p['progress']['percentage'] ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- Grid Informasi Metrik Pemantauan (Sesuai Spesifikasi) -->
                        <div class="bg-light p-2.5 rounded-3 mb-3 border">
                            <div class="row g-2 text-xs">
                                <!-- Sisa Waktu -->
                                <div class="col-6">
                                    <span class="text-muted d-block" style="font-size: 11px;">Sisa Waktu:</span>
                                    <strong class="font-mono text-dark time-text d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-clock-history text-primary"></i> <?= $p['time']['remaining_formatted'] ?>
                                    </strong>
                                </div>
                                <!-- Jumlah Events -->
                                <div class="col-6 text-end">
                                    <span class="text-muted d-block" style="font-size: 11px;">Insiden AI:</span>
                                    <span class="badge bg-dark rounded-pill px-2 py-0.5 font-mono events-badge">
                                        <i class="bi bi-bell-fill text-warning me-1"></i><?= $p['events_count'] ?> Event
                                    </span>
                                </div>

                                <!-- Camera 1 Status -->
                                <div class="col-6 pt-1 border-top">
                                    <span class="text-muted d-block" style="font-size: 11px;">Camera 1 (Wajah):</span>
                                    <span class="badge <?= $cam1Badge ?> rounded-2 px-2 py-0.5 font-bold cam1-badge">
                                        <?= $p['camera_1'] ?>
                                    </span>
                                </div>

                                <!-- Camera 2 Status -->
                                <div class="col-6 text-end pt-1 border-top">
                                    <span class="text-muted d-block" style="font-size: 11px;">Camera 2 (Meja):</span>
                                    <span class="badge <?= $cam2Badge ?> rounded-2 px-2 py-0.5 font-bold cam2-badge">
                                        <?= $p['camera_2'] ?>
                                    </span>
                                </div>

                                <!-- AI Status (NORMAL, NEEDS REVIEW, REVIEWED) -->
                                <div class="col-6 pt-1 border-top">
                                    <span class="text-muted d-block" style="font-size: 11px;">Status AI:</span>
                                    <span class="badge <?= $aiBadgeClass ?> rounded-pill px-2.5 py-0.5 font-bold ai-badge">
                                        <?= $p['ai_status'] ?>
                                    </span>
                                </div>

                                <!-- Risk Indicator (LOW, MEDIUM, HIGH) -->
                                <div class="col-6 text-end pt-1 border-top">
                                    <span class="text-muted d-block" style="font-size: 11px;">Tingkat Risiko:</span>
                                    <span class="badge <?= $riskBadgeClass ?> rounded-pill px-2.5 py-0.5 font-bold risk-badge">
                                        <?= $p['risk_level'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Detail & Peninjauan -->
                    <div class="pt-2 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100 rounded-3 py-1.5 font-semibold text-xs d-flex align-items-center justify-content-center gap-1.5 shadow-sm" onclick="monitoringClient.openParticipantDetail(<?= $p['participant_id'] ?>)">
                            <i class="bi bi-search"></i>
                            <span>Detail & Review Insiden</span>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php endif; ?>

<!-- MODAL DRAWER DETAIL PESERTA, BUKTI REKAMAN, & CATATAN PENGAWAS -->
<div class="modal fade" id="modalParticipantLiveDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 bg-primary rounded-3 text-white">
                        <i class="bi bi-person-video3 fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-bold text-base mb-0" id="detailModalStudentName">Nama Peserta</h5>
                        <span class="text-white-50 text-xs" id="detailModalMeta">NISN: - • Kelas: -</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="row g-3.5">
                    <!-- Kolom Kiri: Status Koneksi & Catatan Pengawas -->
                    <div class="col-12 col-lg-4">
                        <!-- Status Kamera Card -->
                        <div class="card-custom p-3.5 mb-3 bg-white shadow-sm">
                            <h6 class="font-bold text-dark text-xs text-uppercase tracking-wider mb-2.5">
                                <i class="bi bi-camera-video me-1 text-primary"></i> Status Perangkat Pengawasan
                            </h6>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom text-xs">
                                <span class="text-muted">Kamera 1 (Wajah Peserta):</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-bold px-2 py-0.5 rounded-2" id="detailCam1Status">ACTIVE</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom text-xs">
                                <span class="text-muted">Kamera 2 (Meja Kerja):</span>
                                <span class="badge bg-secondary-subtle text-secondary border font-bold px-2 py-0.5 rounded-2" id="detailCam2Status">NOT_REQUIRED</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 text-xs">
                                <span class="text-muted">IP Address Peserta:</span>
                                <span class="font-mono text-dark font-semibold" id="detailIpAddress">-</span>
                            </div>
                        </div>

                        <!-- Catatan Pengawas (Add Note) Card -->
                        <div class="card-custom p-3.5 bg-white shadow-sm">
                            <h6 class="font-bold text-dark text-xs text-uppercase tracking-wider mb-2">
                                <i class="bi bi-pencil-square me-1 text-warning"></i> Catatan Pengawas Ujian
                            </h6>
                            <p class="text-muted text-xs mb-2">Tambahkan catatan supervisi khusus untuk sesi peserta ini:</p>

                            <!-- Riwayat Catatan yang Ada -->
                            <div class="p-2.5 bg-light rounded-2 border text-xs mb-2.5 font-mono text-dark" id="detailExistingNotes" style="max-height: 120px; overflow-y: auto; white-space: pre-wrap;">Belum ada catatan pengawas.</div>

                            <!-- Form Tambah Catatan Baru -->
                            <div class="input-group input-group-sm">
                                <input type="text" id="inputNewSupervisorNote" class="form-control" placeholder="Tulis catatan (misal: Izin ke toilet)...">
                                <button class="btn btn-primary font-semibold" type="button" id="btnSaveSupervisorNote">
                                    <i class="bi bi-send-fill me-1"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Timeline Riwayat Insiden Proctoring & Bukti Snapshot -->
                    <div class="col-12 col-lg-8">
                        <div class="card-custom p-3.5 bg-white shadow-sm h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="font-bold text-dark text-xs text-uppercase tracking-wider mb-0">
                                    <i class="bi bi-shield-exclamation me-1 text-danger"></i> Riwayat Event Pengawasan AI (<span id="detailEventCount">0</span>)
                                </h6>
                                <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill px-2.5 py-0.5 text-xs font-semibold">
                                    Human-in-the-Loop Review
                                </span>
                            </div>

                            <!-- Tabel / Timeline Event -->
                            <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0 text-xs">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Bukti Visual</th>
                                            <th>Waktu & Durasi</th>
                                            <th>Jenis Anomali</th>
                                            <th>Kamera</th>
                                            <th>AI Confidence</th>
                                            <th>Status</th>
                                            <th class="text-end">Aksi Guru</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailEventsTableBody">
                                        <!-- Diisi secara dinamis melalui JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-3 px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW BUKTI TANGKAPAN LAYAR RESOLUSI PENUH -->
<div class="modal fade" id="modalLiveEvidenceZoom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-2.5">
                <h6 class="modal-title font-bold text-sm" id="zoomModalTitle">Bukti Tangkapan Layar</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-black">
                <img id="zoomModalImage" src="" alt="Bukti Foto" class="img-fluid rounded border border-secondary shadow" style="max-height: 520px;">
            </div>
            <div class="modal-footer bg-light py-2 text-xs text-muted justify-content-between">
                <span id="zoomModalMeta">-</span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     JAVASCRIPT LIVE MONITORING CLIENT ENGINE (POLLING & WEBSOCKET-READY)
     ========================================================================= -->
<script>
class LiveMonitoringClient {
    constructor(config) {
        this.examId = config.examId;
        this.statusUrl = config.statusUrl;
        this.participantDetailUrl = config.participantDetailUrl;
        this.addNoteUrl = config.addNoteUrl;
        this.updateReviewUrl = config.updateReviewUrl;
        this.csrfToken = config.csrfToken;
        this.pollIntervalMs = config.pollIntervalMs || 4000;
        this.pollTimer = null;
        this.isPollingActive = true;
        this.currentOpenParticipantId = null;

        this.init();
    }

    init() {
        // Pasang event listener kontrol toolbar
        const toggleSwitch = document.getElementById('toggleAutoRefresh');
        if (toggleSwitch) {
            toggleSwitch.addEventListener('change', (e) => {
                this.isPollingActive = e.target.checked;
                if (this.isPollingActive) {
                    this.startPolling();
                } else {
                    this.stopPolling();
                }
            });
        }

        const btnRefresh = document.getElementById('btnManualRefresh');
        if (btnRefresh) {
            btnRefresh.addEventListener('click', () => {
                this.fetchLiveStatus(true);
            });
        }

        // Pasang filter input listeners
        const searchInput = document.getElementById('filterSearch');
        const aiSelect = document.getElementById('filterAiStatus');
        const riskSelect = document.getElementById('filterRiskLevel');
        const statusSelect = document.getElementById('filterExamStatus');

        [searchInput, aiSelect, riskSelect, statusSelect].forEach(el => {
            if (el) el.addEventListener('input', () => this.applyLocalFilters());
        });

        // Pasang listener tombol simpan catatan pengawas
        const btnSaveNote = document.getElementById('btnSaveSupervisorNote');
        if (btnSaveNote) {
            btnSaveNote.addEventListener('click', () => this.saveSupervisorNote());
        }

        // Mulai polling pertama
        if (this.examId > 0 && this.isPollingActive) {
            this.startPolling();
        }
    }

    startPolling() {
        this.stopPolling();
        this.pollTimer = setInterval(() => {
            this.fetchLiveStatus(false);
        }, this.pollIntervalMs);
    }

    stopPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }

    async fetchLiveStatus(isManual = false) {
        if (!this.examId) return;

        const refreshIcon = document.getElementById('iconRefresh');
        if (refreshIcon) refreshIcon.classList.add('spin-animation');

        try {
            const res = await fetch(`${this.statusUrl}/${this.examId}/live-status`);
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            const data = await res.json();

            if (data && data.success) {
                this.updateDashboardMetrics(data.summary);
                this.updateParticipantCards(data.participants);
            }
        } catch (err) {
            console.warn('[LiveMonitoring] Polling error:', err);
        } finally {
            if (refreshIcon) {
                setTimeout(() => refreshIcon.classList.remove('spin-animation'), 500);
            }
        }
    }

    updateDashboardMetrics(summary) {
        if (!summary) return;
        document.getElementById('statTotalParticipants').textContent = summary.total || 0;
        document.getElementById('statOngoingParticipants').textContent = summary.ongoing || 0;
        document.getElementById('statNeedsReviewParticipants').textContent = summary.needs_review || 0;
        document.getElementById('statHighRiskParticipants').textContent = summary.high_risk || 0;
    }

    updateParticipantCards(participants) {
        if (!participants || !Array.isArray(participants)) return;

        participants.forEach(p => {
            const cardEl = document.querySelector(`.participant-card-wrapper[data-id="${p.participant_id}"]`);
            if (!cardEl) return;

            // Perbarui atribut filter dataset
            cardEl.dataset.ai = p.ai_status;
            cardEl.dataset.risk = p.risk_level;
            cardEl.dataset.status = p.exam_status;

            // Perbarui elemen teks & progress bar
            const progText = cardEl.querySelector('.progress-text');
            if (progText) progText.textContent = `${p.progress.text} (${p.progress.percentage}%)`;

            const progBar = cardEl.querySelector('.progress-bar');
            if (progBar) {
                progBar.style.width = `${p.progress.percentage}%`;
                progBar.setAttribute('aria-valuenow', p.progress.percentage);
            }

            const timeText = cardEl.querySelector('.time-text');
            if (timeText) {
                timeText.innerHTML = `<i class="bi bi-clock-history text-primary"></i> ${p.time.remaining_formatted}`;
            }

            const eventsBadge = cardEl.querySelector('.events-badge');
            if (eventsBadge) {
                eventsBadge.innerHTML = `<i class="bi bi-bell-fill text-warning me-1"></i>${p.events_count} Event`;
            }

            // Perbarui badge Kamera 1
            const cam1Badge = cardEl.querySelector('.cam1-badge');
            if (cam1Badge) {
                cam1Badge.textContent = p.camera_1;
                cam1Badge.className = `badge rounded-2 px-2 py-0.5 font-bold cam1-badge ${
                    p.camera_1 === 'DISCONNECTED' || p.camera_1 === 'BLOCKED' ? 'bg-danger text-white' : 
                    (p.camera_1 === 'ACTIVE' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary')
                }`;
            }

            // Perbarui badge Kamera 2
            const cam2Badge = cardEl.querySelector('.cam2-badge');
            if (cam2Badge) {
                cam2Badge.textContent = p.camera_2;
                cam2Badge.className = `badge rounded-2 px-2 py-0.5 font-bold cam2-badge ${
                    p.camera_2 === 'DISCONNECTED' ? 'bg-danger text-white' : 
                    (p.camera_2 === 'ACTIVE' ? 'bg-success-subtle text-success border border-success-subtle' : 
                    (p.camera_2 === 'NOT_REQUIRED' ? 'bg-light text-muted border' : 'bg-secondary-subtle text-secondary'))
                }`;
            }

            // Perbarui badge AI Status (NORMAL, NEEDS REVIEW, REVIEWED)
            const aiBadge = cardEl.querySelector('.ai-badge');
            if (aiBadge) {
                aiBadge.textContent = p.ai_status;
                aiBadge.className = `badge rounded-pill px-2.5 py-0.5 font-bold ai-badge ${
                    p.ai_status === 'NEEDS REVIEW' ? 'bg-warning text-dark border border-warning' : 
                    (p.ai_status === 'REVIEWED' ? 'bg-primary text-white' : 'bg-success text-white')
                }`;
            }

            // Perbarui badge Risk Indicator (LOW, MEDIUM, HIGH)
            const riskBadge = cardEl.querySelector('.risk-badge');
            if (riskBadge) {
                riskBadge.textContent = p.risk_level;
                riskBadge.className = `badge rounded-pill px-2.5 py-0.5 font-bold risk-badge ${
                    p.risk_level === 'HIGH' ? 'bg-danger text-white' : 
                    (p.risk_level === 'MEDIUM' ? 'bg-warning-subtle text-dark border border-warning' : 'bg-success-subtle text-success border border-success-subtle')
                }`;
            }
        });

        this.applyLocalFilters();
    }

    applyLocalFilters() {
        const searchVal = (document.getElementById('filterSearch').value || '').trim().toLowerCase();
        const aiVal = document.getElementById('filterAiStatus').value;
        const riskVal = document.getElementById('filterRiskLevel').value;
        const statusVal = document.getElementById('filterExamStatus').value;

        const cards = document.querySelectorAll('.participant-card-wrapper');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const nisn = card.dataset.nisn || '';
            const ai = card.dataset.ai || '';
            const risk = card.dataset.risk || '';
            const status = card.dataset.status || '';

            let match = true;

            if (searchVal && !name.includes(searchVal) && !nisn.includes(searchVal)) {
                match = false;
            }
            if (aiVal && ai !== aiVal) {
                match = false;
            }
            if (riskVal && risk !== riskVal) {
                match = false;
            }
            if (statusVal && status !== statusVal) {
                match = false;
            }

            if (match) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const countEl = document.getElementById('filteredCount');
        if (countEl) countEl.textContent = visibleCount;
    }

    async openParticipantDetail(participantId) {
        this.currentOpenParticipantId = participantId;

        try {
            const res = await fetch(`${this.participantDetailUrl}?id=${participantId}`);
            const json = await res.json();
            if (!json || !json.success) {
                alert('Gagal mengambil detail peserta.');
                return;
            }

            const p = json.data.participant;
            const events = json.data.events;

            document.getElementById('detailModalStudentName').textContent = p.student_name;
            document.getElementById('detailModalMeta').textContent = `NISN: ${p.nisn} • Kelas: ${p.class_name} • Sesi: ${p.exam_status}`;
            document.getElementById('detailIpAddress').textContent = p.ip_address;
            document.getElementById('detailExistingNotes').textContent = p.supervisor_notes || 'Belum ada catatan pengawas.';
            document.getElementById('detailEventCount').textContent = events.length;

            // Render daftar events
            const tbody = document.getElementById('detailEventsTableBody');
            tbody.innerHTML = '';

            if (events.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-shield-check fs-3 text-success d-block mb-1"></i>
                            <span>Tidak ada anomali proctoring yang tercatat untuk peserta ini.</span>
                        </td>
                    </tr>
                `;
            } else {
                events.forEach(evt => {
                    const tr = document.createElement('tr');

                    // Thumbnail bukti
                    const evidenceImg = evt.evidence_reference 
                        ? `<a href="javascript:void(0)" onclick="monitoringClient.zoomEvidence('${evt.evidence_reference}', '${evt.event_type}', '${p.student_name}')">
                             <img src="${evt.evidence_reference}" class="rounded border" style="width: 50px; height: 38px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/50x38?text=No+Img'">
                           </a>`
                        : `<span class="badge bg-light text-muted border"><i class="bi bi-camera-video-off"></i></span>`;

                    // Status Badge
                    let statusBadge = 'bg-secondary';
                    if (evt.status === 'REVIEW') statusBadge = 'bg-warning text-dark font-bold';
                    elseif (evt.status === 'REVIEWED') statusBadge = 'bg-danger text-white font-bold';
                    elseif (evt.status === 'DISMISSED') statusBadge = 'bg-secondary text-white';

                    tr.innerHTML = `
                        <td>${evidenceImg}</td>
                        <td>
                            <strong class="d-block font-mono text-dark">${evt.formatted_time}</strong>
                            <small class="text-muted">${evt.duration} dtk</small>
                        </td>
                        <td>
                            <span class="badge bg-dark rounded-pill px-2 py-0.5">${evt.event_type}</span>
                            <small class="d-block text-muted text-truncate" style="max-width: 140px;" title="${evt.description || ''}">${evt.description || '-'}</small>
                        </td>
                        <td><span class="badge bg-light text-dark border">${evt.camera}</span></td>
                        <td><span class="font-mono font-bold text-danger">${evt.confidence_score}%</span></td>
                        <td><span class="badge ${statusBadge} rounded-pill px-2 py-0.5">${evt.status}</span></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" title="Mark as Reviewed (Konfirmasi)" onclick="monitoringClient.submitReviewAction(${evt.id}, 'REVIEWED')">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Dismiss Event (Abaikan)" onclick="monitoringClient.submitReviewAction(${evt.id}, 'DISMISSED')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            const modal = new bootstrap.Modal(document.getElementById('modalParticipantLiveDetail'));
            modal.show();

        } catch (e) {
            console.error('[ParticipantDetail] Error:', e);
        }
    }

    zoomEvidence(imgUrl, eventType, studentName) {
        document.getElementById('zoomModalImage').src = imgUrl;
        document.getElementById('zoomModalTitle').textContent = `Bukti Snapshot: ${eventType}`;
        document.getElementById('zoomModalMeta').textContent = `Peserta: ${studentName}`;

        const modal = new bootstrap.Modal(document.getElementById('modalLiveEvidenceZoom'));
        modal.show();
    }

    async submitReviewAction(eventId, newStatus) {
        const formData = new FormData();
        formData.append('csrf_token', this.csrfToken);
        formData.append('event_id', eventId);
        formData.append('status', newStatus);
        formData.append('notes', 'Ditindaklanjuti via Live Monitoring Dashboard.');
        formData.append('is_ajax', '1');

        try {
            const res = await fetch(this.updateReviewUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (json && json.success) {
                // Refresh modal detail dan polling cards
                if (this.currentOpenParticipantId) {
                    this.openParticipantDetail(this.currentOpenParticipantId);
                }
                this.fetchLiveStatus(true);
            } else {
                alert(json.message || 'Gagal mengubah status event.');
            }
        } catch (e) {
            console.error('[ReviewAction] Error:', e);
        }
    }

    async saveSupervisorNote() {
        const input = document.getElementById('inputNewSupervisorNote');
        const note = (input.value || '').trim();
        if (!note || !this.currentOpenParticipantId) return;

        const formData = new FormData();
        formData.append('csrf_token', this.csrfToken);
        formData.append('participant_id', this.currentOpenParticipantId);
        formData.append('note', note);

        try {
            const res = await fetch(this.addNoteUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (json && json.success) {
                input.value = '';
                // Muat ulang detail peserta
                this.openParticipantDetail(this.currentOpenParticipantId);
            } else {
                alert(json.message || 'Gagal menyimpan catatan.');
            }
        } catch (e) {
            console.error('[SaveNote] Error:', e);
        }
    }
}

// Inisialisasi Instance Client Monitoring
document.addEventListener('DOMContentLoaded', () => {
    window.monitoringClient = new LiveMonitoringClient({
        examId: <?= (int)($selectedExamId ?? 0) ?>,
        statusUrl: '<?= base_url('teacher/exams') ?>',
        participantDetailUrl: '<?= base_url('teacher/proctoring/participantDetail') ?>',
        addNoteUrl: '<?= base_url('teacher/proctoring/addNote') ?>',
        updateReviewUrl: '<?= base_url('teacher/proctoring/updateStatus') ?>',
        csrfToken: '<?= $csrfToken ?>',
        pollIntervalMs: 4000
    });
});
</script>

<style>
.spin-animation {
    animation: spin 0.8s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.hover-lift:hover {
    transform: translateY(-3px);
}
.transition-all {
    transition: all 0.2s ease-in-out;
}
</style>
