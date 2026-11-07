<!-- ========================================================
     SmartExam AI (UJOL) - Landing Page View
     ======================================================== -->

<!-- SECTION 1: HERO SECTION -->
<section id="hero" class="py-5 position-relative" style="padding-top: 80px !important; padding-bottom: 80px !important;">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center g-5">
            <!-- Left Hero Column: Headline & CTA -->
            <div class="col-12 col-lg-7 text-center text-lg-start">
                <!-- Eyebrow Pill -->
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3" style="background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.3);">
                    <span class="badge rounded-circle p-1 bg-success" style="width: 8px; height: 8px;"></span>
                    <span class="text-xs font-semibold text-primary" style="letter-spacing: 0.3px;">
                        Platform Ujian Online Cerdas Berbasis AI Proctoring & Dual Camera
                    </span>
                </div>

                <!-- Main Hero Headline -->
                <h1 class="display-4 font-bold text-white mb-3" style="line-height: 1.18; letter-spacing: -0.5px;">
                    Asesmen Digital Sekolah yang Lebih <span class="text-transparent bg-clip-text" style="background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Cerdas, Adil, & Terpercaya</span>
                </h1>

                <!-- Subtitle Description -->
                <p class="text-slate-300 text-base mb-4" style="line-height: 1.7; max-width: 580px; font-size: 1.05rem;">
                    <strong><?= APP_NAME ?></strong> menghadirkan ekosistem ujian online modern jenjang SMA/SMK/MA. Dilengkapi pengawasan kecerdasan buatan, sistem anti-curang dual camera 360°, autosave jawaban instan, dan asisten virtual pintar 24 jam.
                </p>

                <!-- Call-to-Action Buttons -->
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3 mb-4">
                    <?php if (!empty($isLoggedIn)): ?>
                        <a href="<?= base_url('dashboard') ?>" class="btn-gradient-primary py-2.5 px-4 text-base">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span>Buka Dasbor Saya (<?= ucfirst(e($userRole ?? '')) ?>)</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('login') ?>" class="btn-gradient-primary py-2.5 px-4 text-base shadow-lg">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Masuk ke Ruang Ujian</span>
                        </a>
                    <?php endif; ?>

                    <button type="button" class="btn-gradient-secondary py-2.5 px-4 text-base" data-action="open-chatbot">
                        <i class="bi bi-robot text-warning"></i>
                        <span>Tanya Smarty AI</span>
                    </button>

                    <a href="#alur" class="btn btn-link text-slate-400 text-sm text-decoration-none d-inline-flex align-items-center gap-1.5 px-2">
                        <span>Alur Pengerjaan</span>
                        <i class="bi bi-arrow-down-short fs-5"></i>
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="pt-3 border-top border-secondary border-opacity-25 d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-4 text-xs text-slate-400">
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-shield-check text-success fs-6"></i>
                        <span>AI Anti-Curang Teruji</span>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-cloud-check text-info fs-6"></i>
                        <span>100% Autosave Jawaban</span>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-camera-video text-warning fs-6"></i>
                        <span>Verifikasi Dual Camera</span>
                    </div>
                </div>
            </div>

            <!-- Right Hero Column: Interactive Tech Card Mockup -->
            <div class="col-12 col-lg-5">
                <div class="position-relative">
                    <!-- Main Showcase Card -->
                    <div class="landing-card shadow-2xl" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(59, 130, 246, 0.25);">
                        <!-- Card Header Mockup -->
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-secondary border-opacity-25">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-circle bg-danger" style="width: 10px; height: 10px;"></span>
                                <span class="badge rounded-circle bg-warning" style="width: 10px; height: 10px;"></span>
                                <span class="badge rounded-circle bg-success" style="width: 10px; height: 10px;"></span>
                                <span class="text-xs text-muted font-mono ms-2">SmartExam AI Proctor v1.0</span>
                            </div>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill text-xs">
                                <i class="bi bi-broadcast me-1"></i>LIVE PROCTORING
                            </span>
                        </div>

                        <!-- Mockup Monitor Viewport -->
                        <div class="p-3 rounded-3 mb-3" style="background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.06);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-xs text-slate-300 font-semibold">
                                    <i class="bi bi-person-bounding-box text-primary me-1"></i>Verifikasi Wajah Peserta
                                </span>
                                <span class="badge bg-success font-mono text-xs">99.8% VERIFIED</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px; background: rgba(255, 255, 255, 0.1);">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: 98%"></div>
                            </div>
                            <small class="text-slate-400" style="font-size: 0.72rem;">Peserta aktif di depan layar, posisi tegak, tidak ada anomali.</small>
                        </div>

                        <!-- System Parameters Grid -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2.5 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                                    <div class="text-xs text-muted mb-1"><i class="bi bi-camera text-info me-1"></i>Kamera Depan</div>
                                    <span class="badge bg-info-subtle text-info text-xs font-semibold">Stream Aktif</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2.5 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                                    <div class="text-xs text-muted mb-1"><i class="bi bi-phone text-warning me-1"></i>Kamera Samping</div>
                                    <span class="badge bg-warning-subtle text-warning text-xs font-semibold">Sudut 360° Siaga</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2.5 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                                    <div class="text-xs text-muted mb-1"><i class="bi bi-window-sidebar text-danger me-1"></i>Tab Lock</div>
                                    <span class="text-xs text-success font-semibold">0 Pelanggaran</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2.5 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                                    <div class="text-xs text-muted mb-1"><i class="bi bi-cloud-arrow-up text-primary me-1"></i>Autosave</div>
                                    <span class="text-xs text-primary font-semibold font-mono">Realtime (0ms)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Trigger -->
                        <div class="d-flex align-items-center justify-content-between pt-2 text-xs text-slate-400">
                            <span><i class="bi bi-robot text-warning me-1"></i>Asisten Smarty AI Terhubung</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0.5 px-2 text-xs rounded-pill" data-action="open-chatbot">
                                Uji Tanya AI
                            </button>
                        </div>
                    </div>

                    <!-- Floating Badge: Anti-Curang -->
                    <div class="position-absolute d-none d-sm-flex align-items-center gap-2 p-2.5 rounded-3 shadow-lg" 
                         style="top: -20px; right: -15px; background: #1e293b; border: 1px solid rgba(59, 130, 246, 0.4); animation: floatSlow 4s ease-in-out infinite;">
                        <div class="bg-success text-white rounded-circle p-1.5 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            <i class="bi bi-shield-lock-fill text-xs"></i>
                        </div>
                        <div class="text-xs">
                            <strong class="text-white d-block leading-tight">Integritas 100%</strong>
                            <span class="text-slate-400" style="font-size: 0.68rem;">Deteksi Tab & Kamera</span>
                        </div>
                    </div>

                    <!-- Floating Badge: Smarty AI Bot -->
                    <div class="position-absolute d-none d-sm-flex align-items-center gap-2 p-2.5 rounded-3 shadow-lg" 
                         style="bottom: -15px; left: -15px; background: #1e293b; border: 1px solid rgba(234, 179, 8, 0.4); animation: floatSlow 5s ease-in-out infinite reverse;">
                        <div class="bg-warning text-dark rounded-circle p-1.5 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            <i class="bi bi-robot text-xs"></i>
                        </div>
                        <div class="text-xs">
                            <strong class="text-white d-block leading-tight">Smarty AI Siaga</strong>
                            <span class="text-slate-400" style="font-size: 0.68rem;">Node.js & Gemini 24/7</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 2: LIVE METRICS STRIP -->
<section class="py-4 position-relative border-top border-bottom border-secondary border-opacity-25" style="background: rgba(15, 23, 42, 0.65);">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="display-6 font-mono font-bold text-white mb-1">
                    <?= number_format($stats['total_students'] ?? 0) ?>+
                </div>
                <div class="text-xs text-slate-400 font-semibold text-uppercase tracking-wider">
                    Peserta Didik Terdaftar
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="display-6 font-mono font-bold text-primary mb-1">
                    <?= number_format($stats['total_teachers'] ?? 0) ?>+
                </div>
                <div class="text-xs text-slate-400 font-semibold text-uppercase tracking-wider">
                    Guru & Pengawas Ujian
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="display-6 font-mono font-bold text-info mb-1">
                    <?= number_format($stats['total_banks'] ?? 0) ?>
                </div>
                <div class="text-xs text-slate-400 font-semibold text-uppercase tracking-wider">
                    Bank Soal Terverifikasi
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="display-6 font-mono font-bold text-success mb-1">
                    <?= number_format($stats['total_exams'] ?? 0) ?>
                </div>
                <div class="text-xs text-slate-400 font-semibold text-uppercase tracking-wider">
                    Paket Ujian Terlaksana
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 3: FITUR UNGGULAN -->
<section id="fitur" class="py-5 position-relative" style="padding-top: 80px !important; padding-bottom: 80px !important;">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5" style="max-width: 680px; margin: 0 auto;">
            <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill mb-2" style="background: rgba(14, 165, 233, 0.12); border: 1px solid rgba(14, 165, 233, 0.3);">
                <i class="bi bi-stars text-info text-xs"></i>
                <span class="text-xs font-semibold text-info">KECERDASAN ASESMEN MODERN</span>
            </div>
            <h2 class="display-6 font-bold text-white mb-2">6 Fitur Unggulan SmartExam AI</h2>
            <p class="text-slate-400 text-sm">
                Dirancang khusus untuk mendukung kelancaran penilaian harian, asesmen sumatif, hingga simulasi UTBK dengan standar integritas sekolah unggulan.
            </p>
        </div>

        <!-- 6 Feature Cards Grid -->
        <div class="row g-4">
            <!-- 1. AI Proctoring -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="landing-card h-100">
                    <div class="icon-box-feature bg-primary text-white shadow-sm">
                        <i class="bi bi-robot"></i>
                    </div>
                    <h5 class="font-bold text-white mb-2">AI Proctoring Otomatis</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Sistem AI memantau aktivitas browser siswa secara cerdas, mendeteksi perpindahan tab, keberadaan wajah di kamera, serta mencatat anomali secara realtime.
                    </p>
                </div>
            </div>

            <!-- 2. Dual-Camera Surveillance -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="landing-card h-100">
                    <div class="icon-box-feature bg-info text-white shadow-sm">
                        <i class="bi bi-webcam-fill"></i>
                    </div>
                    <h5 class="font-bold text-white mb-2">Dual-Camera 360°</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Menggabungkan webcam depan laptop untuk verifikasi wajah dan kamera samping (via smartphone) untuk memantau area meja dan keyboard secara transparan.
                    </p>
                </div>
            </div>

            <!-- 3. Smart Question Generator -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="landing-card h-100">
                    <div class="icon-box-feature bg-success text-white shadow-sm">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <h5 class="font-bold text-white mb-2">AI Question Generator</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Guru dapat membuat butir soal pilihan ganda lengkap dengan opsi A-E dan kunci jawaban secara instan cukup dengan memasukkan topik materi ajar.
                    </p>
                </div>
            </div>

            <!-- 4. Live Monitoring Pengawas -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="landing-card h-100">
                    <div class="icon-box-feature bg-warning text-dark shadow-sm">
                        <i class="bi bi-broadcast"></i>
                    </div>
                    <h5 class="font-bold text-white mb-2">Live Monitoring Guru</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Dasbor pengawasan langsung bagi guru pengampu untuk melihat status pengerjaan seluruh peserta per detik, sisa waktu, dan skor risiko pelanggaran.
                    </p>
                </div>
            </div>

            <!-- 5. Autosave & Anti-Loss -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="landing-card h-100">
                    <div class="icon-box-feature bg-danger text-white shadow-sm">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </div>
                    <h5 class="font-bold text-white mb-2">100% Realtime Autosave</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Jawaban yang dipilih langsung tersimpan ke server database tanpa perlu tombol simpan manual. Aman jika terjadi gangguan sinyal atau mati lampu.
                    </p>
                </div>
            </div>

            <!-- 6. Smarty AI Virtual Assistant -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="landing-card h-100" style="border-color: rgba(234, 179, 8, 0.35);">
                    <div class="icon-box-feature bg-gradient text-white shadow-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <h5 class="font-bold text-white mb-2">Smarty AI (Asisten 24/7)</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Chatbot interaktif ditenagai Node.js dan Google Gemini AI yang siap menjawab panduan login, tata tertib asesmen, dan solusi kendala perangkat kapan saja.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 4: ALUR UJIAN SISWA -->
<section id="alur" class="py-5 position-relative" style="background: rgba(15, 23, 42, 0.5); padding-top: 80px !important; padding-bottom: 80px !important;">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5" style="max-width: 680px; margin: 0 auto;">
            <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill mb-2" style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3);">
                <i class="bi bi-signpost-split text-success text-xs"></i>
                <span class="text-xs font-semibold text-success">PANDUAN PRAKTIS PESERTA</span>
            </div>
            <h2 class="display-6 font-bold text-white mb-2">Alur Pengerjaan Ujian yang Mudah</h2>
            <p class="text-slate-400 text-sm">
                Hanya butuh 4 langkah mudah bagi peserta didik untuk mengikuti asesmen digital di SmartExam AI.
            </p>
        </div>

        <!-- 4 Step Cards -->
        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="landing-card h-100 position-relative">
                    <span class="badge bg-primary rounded-pill font-mono px-3 py-1.5 text-xs mb-3">Langkah 01</span>
                    <h5 class="font-bold text-white mb-2">Masuk ke Portal</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Gunakan akun NISN dan kata sandi yang telah terdaftar dari pihak sekolah untuk login ke sistem.
                    </p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="landing-card h-100 position-relative">
                    <span class="badge bg-info rounded-pill font-mono px-3 py-1.5 text-xs mb-3">Langkah 02</span>
                    <h5 class="font-bold text-white mb-2">Cek Jadwal & Kamera</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Buka menu Jadwal Ujian Saya dan lakukan verifikasi perangkat dual camera sebelum waktu ujian dimulai.
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="landing-card h-100 position-relative">
                    <span class="badge bg-warning text-dark rounded-pill font-mono px-3 py-1.5 text-xs mb-3">Langkah 03</span>
                    <h5 class="font-bold text-white mb-2">Kerjakan Soal</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Jawab pertanyaan dengan tenang. Gunakan penanda ragu-ragu jika belum yakin, jawaban tersimpan otomatis.
                    </p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="landing-card h-100 position-relative">
                    <span class="badge bg-success rounded-pill font-mono px-3 py-1.5 text-xs mb-3">Langkah 04</span>
                    <h5 class="font-bold text-white mb-2">Kumpulkan & Nilai</h5>
                    <p class="text-slate-400 text-sm mb-0" style="line-height: 1.6;">
                        Klik kumpulkan ujian untuk melihat skor evaluasi capaian KKM dan rekap transkrip nilai langsung.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 5: ROLE ACCESS BREAKDOWN -->
<section id="peran" class="py-5 position-relative" style="padding-top: 80px !important; padding-bottom: 80px !important;">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5" style="max-width: 680px; margin: 0 auto;">
            <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill mb-2" style="background: rgba(168, 85, 247, 0.12); border: 1px solid rgba(168, 85, 247, 0.3);">
                <i class="bi bi-people-fill text-purple text-xs"></i>
                <span class="text-xs font-semibold" style="color: #c084fc;">HAK AKSES TERPADU</span>
            </div>
            <h2 class="display-6 font-bold text-white mb-2">Dukungan Lengkap untuk Setiap Peran</h2>
            <p class="text-slate-400 text-sm">
                SmartExam AI menyajikan pengalaman antarmuka yang disesuaikan secara spesifik untuk Siswa, Guru, dan Administrator.
            </p>
        </div>

        <div class="row g-4">
            <!-- Peserta Didik -->
            <div class="col-12 col-lg-4">
                <div class="landing-card h-100 border-top border-4 border-success">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge-role-siswa rounded-pill px-3 py-1 text-xs">ROLE SISWA</span>
                    </div>
                    <h4 class="font-bold text-white mb-3">Peserta Didik</h4>
                    <p class="text-slate-400 text-sm mb-4">
                        Ruang asesmen yang fokus, nyaman, dan transparan tanpa distraksi visual.
                    </p>
                    <ul class="list-unstyled text-sm text-slate-300 d-flex flex-column gap-2.5 mb-4">
                        <li><i class="bi bi-check2 text-success me-2"></i>Jadwal ujian aktif & teratur</li>
                        <li><i class="bi bi-check2 text-success me-2"></i>Uji kamera instan sebelum mulai</li>
                        <li><i class="bi bi-check2 text-success me-2"></i>Autosave jawaban tanpa rasa khawatir</li>
                        <li><i class="bi bi-check2 text-success me-2"></i>Transkrip nilai & riwayat capaian KKM</li>
                    </ul>
                    <a href="<?= base_url('login') ?>" class="btn btn-outline-success w-100 rounded-3 text-sm py-2">
                        Masuk sebagai Siswa
                    </a>
                </div>
            </div>

            <!-- Guru Pengampu -->
            <div class="col-12 col-lg-4">
                <div class="landing-card h-100 border-top border-4 border-info">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge-role-guru rounded-pill px-3 py-1 text-xs">ROLE GURU</span>
                    </div>
                    <h4 class="font-bold text-white mb-3">Guru & Pengawas</h4>
                    <p class="text-slate-400 text-sm mb-4">
                        Alat bantu perancangan butir soal cerdas dan pengawasan asesmen realtime.
                    </p>
                    <ul class="list-unstyled text-sm text-slate-300 d-flex flex-column gap-2.5 mb-4">
                        <li><i class="bi bi-check2 text-info me-2"></i>Bank soal manual & AI Generator</li>
                        <li><i class="bi bi-check2 text-info me-2"></i>Penjadwalan paket ujian & token akses</li>
                        <li><i class="bi bi-check2 text-info me-2"></i>Live Monitoring pengawasan siswa</li>
                        <li><i class="bi bi-check2 text-info me-2"></i>Analitik butir soal & rekap rapor</li>
                    </ul>
                    <a href="<?= base_url('login') ?>" class="btn btn-outline-info w-100 rounded-3 text-sm py-2">
                        Masuk sebagai Guru
                    </a>
                </div>
            </div>

            <!-- Administrator -->
            <div class="col-12 col-lg-4">
                <div class="landing-card h-100 border-top border-4 border-danger">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge-role-admin rounded-pill px-3 py-1 text-xs">ROLE ADMIN</span>
                    </div>
                    <h4 class="font-bold text-white mb-3">Administrator</h4>
                    <p class="text-slate-400 text-sm mb-4">
                        Kendali penuh tata kelola master data sekolah dan keamanan sistem.
                    </p>
                    <ul class="list-unstyled text-sm text-slate-300 d-flex flex-column gap-2.5 mb-4">
                        <li><i class="bi bi-check2 text-danger me-2"></i>Manajemen master Guru, Siswa, & Kelas</li>
                        <li><i class="bi bi-check2 text-danger me-2"></i>Audit log aktivitas seluruh user</li>
                        <li><i class="bi bi-check2 text-danger me-2"></i>Konfigurasi AI Proctoring & server</li>
                        <li><i class="bi bi-check2 text-danger me-2"></i>Ekspor laporan institusi terpadu</li>
                    </ul>
                    <a href="<?= base_url('login') ?>" class="btn btn-outline-danger w-100 rounded-3 text-sm py-2">
                        Portal Administrator
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 6: KEAMANAN & AI PROCTORING HIGHLIGHT -->
<section id="keamanan" class="py-5 position-relative" style="background: rgba(15, 23, 42, 0.4); padding-top: 80px !important; padding-bottom: 80px !important;">
    <div class="container">
        <div class="p-4 p-lg-5 rounded-4 position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.45) 0%, rgba(15, 23, 42, 0.85) 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-8">
                    <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill mb-3" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);">
                        <i class="bi bi-shield-lock-fill text-danger text-xs"></i>
                        <span class="text-xs font-semibold text-danger">STANDAR INTEGRITAS AKADEMIK TINGGI</span>
                    </div>
                    <h3 class="font-bold text-white mb-3">Siap Mewujudkan Ujian Sekolah yang Jujur dan Akuntabel</h3>
                    <p class="text-slate-300 text-sm mb-4" style="line-height: 1.7; max-width: 620px;">
                        Kombinasi AI Proctoring, pelacak perpindahan tab browser, dan sistem verifikasi dua kamera memastikan setiap nilai yang diperoleh peserta didik mencerminkan kemampuan murni secara objektif.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= base_url('login') ?>" class="btn-gradient-primary text-sm py-2 px-4 shadow-sm">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Masuk ke UJOL Sekarang</span>
                        </a>
                        <button type="button" class="btn-gradient-secondary text-sm py-2 px-4" data-action="open-chatbot">
                            <i class="bi bi-chat-dots-fill text-warning"></i>
                            <span>Konsultasi dengan Smarty AI</span>
                        </button>
                    </div>
                </div>
                <div class="col-12 col-lg-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                         style="width: 140px; height: 140px; background: rgba(59, 130, 246, 0.15); border: 2px dashed rgba(59, 130, 246, 0.4);">
                        <i class="bi bi-patch-check-fill text-primary display-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
