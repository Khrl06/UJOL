<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="<?= APP_NAME ?> - Sistem Ujian Online Cerdas & Terpercaya SMA/SMK/MA Berbasis AI Proctoring dan Dual Camera.">
    <meta name="keywords" content="ujian online, smartexam, ujol, cbt, proctoring ai, asesmen sma, tryout utbk">
    <title><?= isset($pageTitle) ? e($pageTitle) : (APP_NAME . ' - Platform Ujian Online Cerdas') ?></title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons 1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Modern Stylesheet -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">

    <style>
    /* ========================================================
       Landing Page Exclusive Styling
       ======================================================== */
    :root {
        --ujol-gradient-primary: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
        --ujol-gradient-accent: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
    }

    body.landing-body {
        background-color: #0b1329;
        color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        overflow-x: hidden;
    }

    /* Landing Navbar */
    .landing-navbar {
        background: rgba(11, 19, 41, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 16px 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .landing-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .landing-brand-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
    }

    .nav-link-custom {
        color: #cbd5e1 !important;
        font-size: 0.9rem;
        font-weight: 500;
        padding: 8px 14px !important;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .nav-link-custom:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.06);
    }

    /* Buttons */
    .btn-gradient-primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #ffffff;
        border: none;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(37, 99, 235, 0.35);
        transition: all 0.25s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-gradient-primary:hover {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.5);
        color: #ffffff;
    }

    .btn-gradient-secondary {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.15);
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 12px;
        transition: all 0.25s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        backdrop-filter: blur(8px);
    }

    .btn-gradient-secondary:hover {
        background: rgba(255, 255, 255, 0.16);
        border-color: rgba(255, 255, 255, 0.3);
        color: #ffffff;
        transform: translateY(-2px);
    }

    /* Glow Gradients */
    .glow-bg {
        position: absolute;
        width: 550px;
        height: 550px;
        border-radius: 50%;
        filter: blur(140px);
        opacity: 0.25;
        pointer-events: none;
        z-index: 0;
    }

    .glow-blue {
        background: #3b82f6;
        top: -100px;
        left: -100px;
    }

    .glow-purple {
        background: #8b5cf6;
        top: 20%;
        right: -100px;
    }

    .glow-cyan {
        background: #06b6d4;
        bottom: 10%;
        left: 30%;
    }

    /* Cards */
    .landing-card {
        background: rgba(255, 255, 255, 0.035);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 28px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        backdrop-filter: blur(12px);
        position: relative;
        overflow: hidden;
    }

    .landing-card:hover {
        transform: translateY(-6px);
        border-color: rgba(59, 130, 246, 0.4);
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35);
        background: rgba(255, 255, 255, 0.055);
    }

    .icon-box-feature {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 20px;
        flex-shrink: 0;
    }

    /* Footer */
    .landing-footer {
        background: #060b18;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding: 60px 0 30px 0;
        color: #94a3b8;
    }
    </style>
</head>
<body class="landing-body position-relative">

    <!-- Ambient Glow Backgrounds -->
    <div class="glow-bg glow-blue"></div>
    <div class="glow-bg glow-purple"></div>
    <div class="glow-bg glow-cyan"></div>

    <!-- Navigation Header -->
    <header class="landing-navbar">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <!-- Brand Logo -->
                <a href="<?= base_url() ?>" class="landing-brand">
                    <div class="landing-brand-icon">
                        <i class="bi bi-shield-shaded"></i>
                    </div>
                    <div>
                        <div class="font-bold text-white fs-5 leading-tight"><?= APP_NAME ?></div>
                        <div class="text-xs text-primary font-semibold" style="letter-spacing: 0.5px;">UJIAN ONLINE SMA/SMK/MA</div>
                    </div>
                </a>

                <!-- Nav Menu Links (Desktop) -->
                <nav class="d-none d-lg-flex align-items-center gap-1">
                    <a href="#hero" class="nav-link-custom">Beranda</a>
                    <a href="#fitur" class="nav-link-custom">Fitur Unggulan</a>
                    <a href="#alur" class="nav-link-custom">Alur Ujian</a>
                    <a href="#peran" class="nav-link-custom">Peran & Akses</a>
                    <a href="#keamanan" class="nav-link-custom">AI Proctoring</a>
                    <a href="javascript:void(0)" class="nav-link-custom text-info" id="nav-btn-chatbot">
                        <i class="bi bi-robot me-1 text-warning"></i>Tanya Asisten AI
                    </a>
                </nav>

                <!-- Auth Action Buttons -->
                <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($isLoggedIn)): ?>
                        <div class="dropdown">
                            <button class="btn btn-gradient-primary dropdown-toggle text-sm py-2 px-3.5" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                <span><?= e($currentUser['name'] ?? 'Akun Saya') ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow">
                                <li>
                                    <h6 class="dropdown-header text-uppercase text-xs text-muted">
                                        Peran: <?= strtoupper(e($userRole ?? 'Pengguna')) ?>
                                    </h6>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 text-sm" href="<?= base_url('dashboard') ?>">
                                        <i class="bi bi-grid-1x2-fill me-2 text-primary"></i>Dasbor Utama
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item py-2 text-sm text-danger" href="<?= base_url('logout') ?>">
                                        <i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= base_url('login') ?>" class="btn-gradient-primary text-sm py-2 px-4 shadow-sm">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Masuk ke Sistem</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Body -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Landing Footer -->
    <footer class="landing-footer position-relative">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-12 col-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="landing-brand-icon" style="width: 38px; height: 38px; font-size: 1.2rem;">
                            <i class="bi bi-shield-shaded"></i>
                        </div>
                        <span class="font-bold text-white fs-5"><?= APP_NAME ?></span>
                    </div>
                    <p class="text-sm text-slate-400 mb-3" style="max-width: 420px; line-height: 1.6;">
                        Sistem Ujian Online terintegrasi berbasis Artificial Intelligence untuk jenjang SMA, SMK, dan MA. Menghadirkan asesmen yang adil, transparan, dan terpercaya.
                    </p>
                    <div class="d-flex align-items-center gap-2 text-xs">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                            <i class="bi bi-broadcast me-1"></i>Sistem Berjalan Normal
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                            v<?= APP_VERSION ?>
                        </span>
                    </div>
                </div>

                <div class="col-6 col-lg-2">
                    <h6 class="font-bold text-white text-sm mb-3">Navigasi Cepat</h6>
                    <ul class="list-unstyled text-sm d-flex flex-column gap-2 mb-0">
                        <li><a href="#hero" class="text-muted text-decoration-none hover-white">Beranda</a></li>
                        <li><a href="#fitur" class="text-muted text-decoration-none hover-white">Fitur Unggulan</a></li>
                        <li><a href="#alur" class="text-muted text-decoration-none hover-white">Alur Pengerjaan</a></li>
                        <li><a href="#keamanan" class="text-muted text-decoration-none hover-white">AI Proctoring</a></li>
                        <li><a href="#peran" class="text-muted text-decoration-none hover-white">Akses Guru & Siswa</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-2">
                    <h6 class="font-bold text-white text-sm mb-3">Portal Masuk</h6>
                    <ul class="list-unstyled text-sm d-flex flex-column gap-2 mb-0">
                        <li><a href="<?= base_url('login') ?>" class="text-muted text-decoration-none hover-white">Login Siswa (NISN)</a></li>
                        <li><a href="<?= base_url('login') ?>" class="text-muted text-decoration-none hover-white">Login Guru Pengampu</a></li>
                        <li><a href="<?= base_url('login') ?>" class="text-muted text-decoration-none hover-white">Portal Administrator</a></li>
                        <li><a href="javascript:void(0)" class="text-info text-decoration-none" onclick="document.getElementById('ujol-chatbot-toggle')?.click()">Tanya Asisten AI</a></li>
                    </ul>
                </div>

                <div class="col-12 col-lg-3">
                    <h6 class="font-bold text-white text-sm mb-3">Keamanan & Kepatuhan</h6>
                    <p class="text-xs text-muted mb-3" style="line-height: 1.6;">
                        Platform dilengkapi verifikasi kamera ganda dan enkripsi lalu lintas data untuk menjaga integritas pelaksanaan evaluasi akademik sekolah.
                    </p>
                    <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                        <small class="text-white text-xs font-semibold d-block mb-1">
                            <i class="bi bi-shield-check text-success me-1"></i> Standar Asesmen Digital
                        </small>
                        <span class="text-muted" style="font-size: 0.72rem;">Sesuai regulasi kurikulum asesmen nasional & ujian satuan pendidikan.</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-top border-secondary border-opacity-25 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 text-xs text-muted">
                <div>
                    &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong>. Hak Cipta Dilindungi. Sistem Ujian Online SMA/SMK/MA.
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span><i class="bi bi-cpu me-1"></i>AI Proctoring Engine</span>
                    <span>&bull;</span>
                    <span><i class="bi bi-node-plus-fill me-1 text-success"></i>Node.js Smarty AI Active</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SmartExam AI Chatbot Widget (Node.js + Google Gemini) -->
    <?php \App\Core\View::partial('chatbot_widget'); ?>

    <script>
    // Smooth scroll offset & triggers
    document.addEventListener('DOMContentLoaded', function() {
        const navChatbotBtn = document.getElementById('nav-btn-chatbot');
        if (navChatbotBtn) {
            navChatbotBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const toggle = document.getElementById('ujol-chatbot-toggle');
                if (toggle) toggle.click();
            });
        }

        // Action trigger for any element with data-action="open-chatbot"
        document.querySelectorAll('[data-action="open-chatbot"]').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                const toggle = document.getElementById('ujol-chatbot-toggle');
                if (toggle) toggle.click();
            });
        });
    });
    </script>
</body>
</html>
