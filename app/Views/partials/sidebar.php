<?php
use App\Helpers\AuthMiddleware;
$role = AuthMiddleware::role() ?? 'guest';
$currentUser = AuthMiddleware::user() ?? [];

// Helper cek URL aktif untuk submenu admin
if (!function_exists('is_admin_active')) {
    function is_admin_active(string $path): string {
        $url = $_GET['url'] ?? '';
        if (empty($url)) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $url = trim(str_replace(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '', $uri), '/');
        }
        return ($url === $path || str_starts_with($url, $path . '/')) ? 'active' : '';
    }
}
?>
<aside id="sidebar">
    <!-- Brand Logo -->
    <div class="sidebar-header">
        <a href="<?= base_url() ?>" class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-shield-shaded"></i>
            </div>
            <div>
                <span class="text-white"><?= APP_NAME ?></span>
                <span class="badge bg-primary text-xs d-block font-semibold mt-0.5" style="font-size: 0.65rem; letter-spacing: 0.5px;">SMA / SEDERAJAT</span>
            </div>
        </a>
    </div>

    <!-- Navigation Menu Items -->
    <div class="sidebar-content">
        <ul class="sidebar-nav">

            <?php if ($role === 'admin'): ?>
                <!-- =======================================================
                     ADMIN / SUPER USER NAVIGATION
                     ======================================================= -->
                <li class="sidebar-heading">Utama</li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin') && !is_admin_active('admin/teachers') && !is_admin_active('admin/students') && !is_admin_active('admin/classes') && !is_admin_active('admin/subjects') && !is_admin_active('admin/banks') && !is_admin_active('admin/exams') && !is_admin_active('admin/monitoring') && !is_admin_active('admin/results') && !is_admin_active('admin/proctoring') && !is_admin_active('admin/reports') && !is_admin_active('admin/users') && !is_admin_active('admin/logs') && !is_admin_active('admin/settings') && !is_admin_active('admin/profile') ? 'active' : '' ?>" href="<?= base_url('admin/dashboard') ?>">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-heading">Master Data</li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/teachers') ?>" href="<?= base_url('admin/teachers') ?>">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>Guru</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/students') ?>" href="<?= base_url('admin/students') ?>">
                        <i class="bi bi-mortarboard-fill"></i>
                        <span>Peserta Didik</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/classes') ?>" href="<?= base_url('admin/classes') ?>">
                        <i class="bi bi-building"></i>
                        <span>Kelas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/subjects') ?>" href="<?= base_url('admin/subjects') ?>">
                        <i class="bi bi-journal-bookmark-fill"></i>
                        <span>Mata Pelajaran</span>
                    </a>
                </li>

                <li class="sidebar-heading">Manajemen Asesmen</li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/banks') ?>" href="<?= base_url('admin/banks') ?>">
                        <i class="bi bi-collection-fill"></i>
                        <span>Bank Soal</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/exams') ?>" href="<?= base_url('admin/exams') ?>">
                        <i class="bi bi-calendar-check-fill"></i>
                        <span>Ujian</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/monitoring') ?>" href="<?= base_url('admin/monitoring') ?>">
                        <i class="bi bi-broadcast-pin text-warning"></i>
                        <span>Monitoring</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/results') ?>" href="<?= base_url('admin/results') ?>">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        <span>Hasil Ujian</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/proctoring') ?>" href="<?= base_url('admin/proctoring') ?>">
                        <i class="bi bi-webcam-fill text-info"></i>
                        <span>AI Proctoring</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/reports') ?>" href="<?= base_url('admin/reports') ?>">
                        <i class="bi bi-clipboard-data-fill"></i>
                        <span>Laporan</span>
                    </a>
                </li>

                <li class="sidebar-heading">Sistem & Keamanan</li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/users') ?>" href="<?= base_url('admin/users') ?>">
                        <i class="bi bi-people-fill"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/logs') ?>" href="<?= base_url('admin/logs') ?>">
                        <i class="bi bi-shield-shaded"></i>
                        <span>Audit Log</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_admin_active('admin/settings') ?>" href="<?= base_url('admin/settings') ?>">
                        <i class="bi bi-sliders"></i>
                        <span>Pengaturan</span>
                    </a>
                </li>

            <?php elseif ($role === 'guru'): ?>
                <!-- =======================================================
                     GURU / TEACHER NAVIGATION
                     ======================================================= -->
                <li class="sidebar-heading">Menu Utama</li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_nav('teacher') ?>" href="<?= base_url('teacher/dashboard') ?>">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dasbor Guru</span>
                    </a>
                </li>

                <li class="sidebar-heading">Bank Soal & Asesmen</li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_nav('teacher/banks') ?>" href="<?= base_url('teacher/banks') ?>">
                        <i class="bi bi-folder2-open"></i>
                        <span>Bank Soal Saya</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_nav('teacher/exams') ?>" href="<?= base_url('teacher/exams') ?>">
                        <i class="bi bi-file-earmark-plus-fill"></i>
                        <span>Paket Ujian</span>
                    </a>
                </li>

                <li class="sidebar-heading">Pelaksanaan & Evaluasi</li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_nav('teacher/monitoring') ?>" href="<?= base_url('teacher/monitoring') ?>">
                        <i class="bi bi-broadcast text-danger"></i>
                        <span>Live Monitoring AI</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_nav('teacher/analytics') ?>" href="<?= base_url('teacher/analytics') ?>">
                        <i class="bi bi-graph-up-arrow text-info"></i>
                        <span>Analytics Ujian</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_nav('teacher/reports') ?>" href="<?= base_url('teacher/reports') ?>">
                        <i class="bi bi-file-earmark-spreadsheet text-success"></i>
                        <span>Laporan & Rekap</span>
                    </a>
                </li>

            <?php elseif ($role === 'siswa'): ?>
                <!-- =======================================================
                     PESERTA DIDIK / STUDENT NAVIGATION
                     ======================================================= -->
                <li class="sidebar-heading">Menu Utama</li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_nav('student') ?>" href="<?= base_url('student/dashboard') ?>">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dasbor Siswa</span>
                    </a>
                </li>

                <li class="sidebar-heading">Aktivitas Ujian</li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_nav('student/exams') ?>" href="<?= base_url('student/exams') ?>">
                        <i class="bi bi-calendar-week"></i>
                        <span>Jadwal Ujian Saya</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_nav('student/history') ?>" href="<?= base_url('student/history') ?>">
                        <i class="bi bi-award-fill"></i>
                        <span>Riwayat & Nilai</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Akun & Profil Link on Sidebar -->
            <li class="sidebar-heading">Akun & Keamanan</li>
            <li class="nav-item">
                <a class="nav-link <?= (is_active_nav('admin/profile') || is_active_nav('teacher/profile') || is_active_nav('student/profile') || is_active_nav('profile')) ? 'active' : '' ?>" href="<?= base_url('profile') ?>">
                    <i class="bi bi-person-circle"></i>
                    <span>Profil Saya</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?= base_url('logout') ?>">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar (Logout)</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-success rounded-circle" style="width: 8px; height: 8px;"></div>
            <span class="text-xs text-white-50">Sistem Aktif v<?= APP_VERSION ?></span>
        </div>
    </div>
</aside>
