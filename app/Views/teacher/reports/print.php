<?php
$reportType = $reportType ?? 'rekap_nilai';
$examName = $examName ?? 'Semua Paket Ujian';
$teacher = $teacher ?? [];
$filters = $filters ?? [];
$reportData = $reportData ?? [];
?>
<!-- Bar Aksi Layar (Disembunyikan saat dicetak) -->
<div class="no-print d-flex justify-content-between align-items-center bg-dark text-white p-3 mb-4 rounded-3 shadow">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-printer-fill fs-5 text-warning"></i>
        <div>
            <strong class="d-block text-sm">Mode Pratinjau Dokumen Resmi</strong>
            <small class="text-white-50 text-xs">Tekan tombol di samping untuk mencetak atau simpan langsung sebagai format PDF.</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-warning btn-sm font-bold px-4 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
        </button>
        <button type="button" class="btn btn-outline-light btn-sm px-3" onclick="window.close()">
            Tutup
        </button>
    </div>
</div>

<!-- KOP DOKUMEN RESMI SEKOLAH -->
<div class="border-bottom border-3 border-dark pb-3 mb-4 text-center">
    <h4 class="font-bold text-dark mb-1 text-uppercase tracking-wider">SMARTEXAM AI - SISTEM ASESMEN BERBASIS KOMPUTER</h4>
    <h6 class="text-secondary mb-1">LAPORAN EVALUASI & REKAPITULASI RESMI UJIAN ONLINE</h6>
    <small class="text-muted">Jalan Pendidikan No. 10, Jakarta • Telp: (021) 555-0199 • Email: info@smartexam.sch.id</small>
</div>

<!-- METADATA LAPORAN -->
<div class="row g-2 mb-4 text-xs">
    <div class="col-8">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <td style="width: 140px;" class="text-muted">Jenis Laporan</td>
                <td>: <strong class="text-uppercase text-dark"><?= str_replace('_', ' ', $reportType) ?></strong></td>
            </tr>
            <tr>
                <td class="text-muted">Paket Ujian</td>
                <td>: <strong><?= e($examName) ?></strong></td>
            </tr>
            <tr>
                <td class="text-muted">Guru Pengampu</td>
                <td>: <?= e($user['name'] ?? 'Guru Pengawas') ?></td>
            </tr>
        </table>
    </div>
    <div class="col-4 text-end">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <td class="text-muted">Tanggal Cetak</td>
                <td>: <?= date('d F Y, H:i:s') ?> WIB</td>
            </tr>
            <tr>
                <td class="text-muted">Total Data</td>
                <td>: <strong class="font-mono"><?= count($reportData) ?> Baris</strong></td>
            </tr>
            <tr>
                <td class="text-muted">Otoritas</td>
                <td>: <span class="badge bg-success-subtle text-success border">Valid & Terverifikasi</span></td>
            </tr>
        </table>
    </div>
</div>

<!-- TABEL CETAK -->
<?php if (empty($reportData)): ?>
    <div class="p-5 text-center text-muted border rounded">
        <span>Tidak ada data yang tersedia untuk dicetak sesuai filter yang dipilih.</span>
    </div>
<?php else: ?>

    <?php if ($reportType === 'rekap_nilai'): ?>
        <table class="table table-bordered table-sm align-middle text-xs mb-4">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 35px;">No</th>
                    <th>NISN</th>
                    <th>Nama Lengkap Siswa</th>
                    <th>Kelas</th>
                    <th>Paket Ujian</th>
                    <th>Total Soal</th>
                    <th>Benar</th>
                    <th>Salah</th>
                    <th>Kosong</th>
                    <th>Skor Akhir</th>
                    <th>KKM</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData as $idx => $r): ?>
                    <tr>
                        <td class="text-center"><?= is_numeric($idx) ? $idx + 1 : 1 ?></td>
                        <td class="font-mono text-center"><?= e($r['nisn']) ?></td>
                        <td><strong><?= e($r['student_name']) ?></strong></td>
                        <td class="text-center"><?= e($r['class_name']) ?></td>
                        <td><?= e($r['exam_title']) ?></td>
                        <td class="text-center font-mono"><?= (int)$r['total_questions'] ?></td>
                        <td class="text-center font-mono font-bold"><?= (int)$r['correct_answers'] ?></td>
                        <td class="text-center font-mono"><?= (int)$r['wrong_answers'] ?></td>
                        <td class="text-center font-mono text-muted"><?= (int)$r['unattempted'] ?></td>
                        <td class="text-end font-mono font-bold"><?= number_format((float)$r['final_score'], 1) ?></td>
                        <td class="text-center font-mono"><?= number_format((float)$r['passing_grade'], 1) ?></td>
                        <td class="text-center font-bold <?= ($r['passing_status'] === 'LULUS') ? 'text-success' : 'text-danger' ?>">
                            <?= $r['passing_status'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($reportType === 'rekap_peserta'): ?>
        <table class="table table-bordered table-sm align-middle text-xs mb-4">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 35px;">No</th>
                    <th>NISN</th>
                    <th>Nama Lengkap Siswa</th>
                    <th>Kelas</th>
                    <th>Paket Ujian</th>
                    <th>Status Sesi</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                    <th>Durasi</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData as $idx => $r): ?>
                    <tr>
                        <td class="text-center"><?= is_numeric($idx) ? $idx + 1 : 1 ?></td>
                        <td class="font-mono text-center"><?= e($r['nisn']) ?></td>
                        <td><strong><?= e($r['student_name']) ?></strong></td>
                        <td class="text-center"><?= e($r['class_name']) ?></td>
                        <td><?= e($r['exam_title']) ?></td>
                        <td class="text-center text-uppercase font-semibold"><?= e($r['attempt_status']) ?></td>
                        <td class="font-mono text-center"><?= $r['start_time'] ? date('d/m/Y H:i', strtotime($r['start_time'])) : '-' ?></td>
                        <td class="font-mono text-center"><?= $r['submit_time'] ? date('d/m/Y H:i', strtotime($r['submit_time'])) : '-' ?></td>
                        <td class="text-center font-mono"><?= $r['duration_spent_minutes'] !== null ? $r['duration_spent_minutes'] . ' Menit' : '-' ?></td>
                        <td class="font-mono text-center"><?= e($r['ip_address'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($reportType === 'rekap_ujian'): ?>
        <table class="table table-bordered table-sm align-middle text-xs mb-4">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 35px;">No</th>
                    <th>Judul Paket Ujian</th>
                    <th>Mata Pelajaran</th>
                    <th>Soal</th>
                    <th>KKM</th>
                    <th>Durasi</th>
                    <th>Tanggal Pelaksanaan</th>
                    <th>Peserta</th>
                    <th>Selesai</th>
                    <th>Rata-Rata</th>
                    <th>Kelulusan (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData as $idx => $r): ?>
                    <tr>
                        <td class="text-center"><?= is_numeric($idx) ? $idx + 1 : 1 ?></td>
                        <td><strong><?= e($r['exam_title']) ?></strong></td>
                        <td><?= e($r['subject_name']) ?></td>
                        <td class="text-center font-mono"><?= (int)$r['total_questions'] ?></td>
                        <td class="text-center font-mono"><?= number_format((float)$r['passing_grade'], 1) ?></td>
                        <td class="text-center font-mono"><?= (int)$r['duration_minutes'] ?> Mnt</td>
                        <td class="font-mono text-center"><?= $r['start_date'] ? date('d/m/Y', strtotime($r['start_date'])) : '-' ?></td>
                        <td class="text-center font-mono"><?= (int)$r['total_participants'] ?></td>
                        <td class="text-center font-mono"><?= (int)$r['completed_count'] ?></td>
                        <td class="text-end font-mono font-bold"><?= number_format((float)$r['avg_score'], 1) ?></td>
                        <td class="text-center font-mono font-bold"><?= $r['passing_percentage'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($reportType === 'rekap_proctoring'): ?>
        <table class="table table-bordered table-sm align-middle text-xs mb-4">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 35px;">No</th>
                    <th>Waktu Kejadian</th>
                    <th>NISN & Peserta</th>
                    <th>Kelas</th>
                    <th>Paket Ujian</th>
                    <th>Jenis Anomali</th>
                    <th>Kamera</th>
                    <th>Confidence</th>
                    <th>Durasi</th>
                    <th>Status Review</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData as $idx => $r): ?>
                    <tr>
                        <td class="text-center"><?= is_numeric($idx) ? $idx + 1 : 1 ?></td>
                        <td class="font-mono text-center"><?= date('d/m/Y H:i:s', strtotime($r['event_time'])) ?></td>
                        <td>
                            <strong><?= e($r['student_name']) ?></strong>
                            <small class="text-muted d-block font-mono"><?= e($r['nisn']) ?></small>
                        </td>
                        <td class="text-center"><?= e($r['class_name']) ?></td>
                        <td><?= e($r['exam_title']) ?></td>
                        <td class="font-mono"><?= e($r['event_type']) ?></td>
                        <td class="text-center"><?= e($r['camera']) ?></td>
                        <td class="text-center font-mono font-bold"><?= number_format((float)$r['confidence_score'], 1) ?>%</td>
                        <td class="text-center font-mono"><?= (int)$r['duration'] ?> dtk</td>
                        <td class="text-center font-bold"><?= $r['review_status'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php endif; ?>

<!-- TANDA TANGAN RESMI PENGESAHAN DOKUMEN -->
<div class="row g-4 mt-5 pt-4 text-xs" style="page-break-inside: avoid;">
    <div class="col-6 text-center">
        <span class="d-block text-muted mb-5">Mengetahui,<br>Kepala Sekolah</span>
        <strong class="d-block text-dark text-decoration-underline font-semibold mt-4">Drs. H. Mulyadi, M.Pd.</strong>
        <span class="text-muted">NIP. 19680512 199403 1 004</span>
    </div>
    <div class="col-6 text-center">
        <span class="d-block text-muted mb-5">Jakarta, <?= date('d F Y') ?><br>Guru Pengawas / Pengampu</span>
        <strong class="d-block text-dark text-decoration-underline font-semibold mt-4"><?= e($user['name'] ?? 'Guru Pengawas') ?></strong>
        <span class="text-muted">NIP. <?= e($teacher['nip'] ?? '19850720 201001 2 015') ?></span>
    </div>
</div>
