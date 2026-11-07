<?php
use App\Helpers\AuthMiddleware;
$currentUser = AuthMiddleware::user() ?? [];
$role = AuthMiddleware::role() ?? 'guest';

$roleBadgeClass = match($role) {
    'admin' => 'badge-role-admin',
    'guru'  => 'badge-role-guru',
    'siswa' => 'badge-role-siswa',
    default => 'bg-secondary'
};

$roleLabel = match($role) {
    'admin' => 'SUPER USER / ADMIN',
    'guru'  => 'GURU PENGAJAR',
    'siswa' => 'PESERTA DIDIK',
    default => 'GUEST'
};
?>
<header class="top-navbar">
    <div class="d-flex align-items-center gap-3">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarToggle" class="btn btn-light border text-secondary btn-sm p-2 rounded-3" type="button" aria-label="Toggle Sidebar" title="Buka/Tutup Menu">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Current Section / School Indicator -->
        <div class="d-none d-md-flex align-items-center gap-2">
            <span class="badge bg-light text-primary border px-2 py-1 font-semibold">
                <i class="bi bi-mortarboard-fill me-1"></i> SMA Negeri 1 Unggulan
            </span>
            <span class="text-muted text-xs">| Tahun Ajaran 2026/2027</span>
        </div>
    </div>

    <!-- Right Controls -->
    <div class="d-flex align-items-center gap-3">
        <!-- Live Clock Widget -->
        <div class="d-none d-lg-flex align-items-center gap-2 text-secondary text-sm bg-light px-3 py-1.5 rounded-pill border">
            <i class="bi bi-clock-history text-primary"></i>
            <span id="liveClock" class="font-semibold text-xs">Memuat waktu...</span>
        </div>

        <!-- Notification Bell Dropdown -->
        <div class="dropdown" id="notificationDropdownContainer">
            <button class="btn btn-light border position-relative p-2 rounded-3 text-secondary" 
                    type="button" 
                    id="notifMenuButton" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false" 
                    title="Notifikasi Pengguna">
                <i class="bi bi-bell-fill fs-6 text-primary"></i>
                <!-- Badge Unread Counter -->
                <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white text-xs d-none" style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                    0
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-0" 
                 aria-labelledby="notifMenuButton" 
                 style="width: 320px; max-width: 90vw; overflow: hidden; z-index: 1050;">
                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between px-3 py-2.5 bg-light border-bottom">
                    <span class="font-bold text-dark text-sm"><i class="bi bi-bell-fill me-1 text-primary"></i> Notifikasi</span>
                    <button type="button" id="btnMarkAllNotifRead" class="btn btn-link btn-sm text-decoration-none text-muted p-0" style="font-size: 0.72rem;">
                        Tandai dibaca
                    </button>
                </div>
                <!-- Notification Items List -->
                <div id="notifListContainer" style="max-height: 320px; overflow-y: auto;">
                    <div class="text-center py-4 text-muted text-xs">
                        <div class="spinner-border spinner-border-sm text-primary mb-1" role="status"></div>
                        <div>Memuat notifikasi...</div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="px-3 py-2 bg-light border-top text-center">
                    <small class="text-muted text-xs">Pemberitahuan Sistem UJOL</small>
                </div>
            </div>
        </div>

        <!-- Role Badge -->
        <span class="badge <?= $roleBadgeClass ?> rounded-pill px-3 py-1.5 text-xs shadow-sm d-none d-sm-inline-block">
            <i class="bi bi-shield-lock-fill me-1"></i><?= $roleLabel ?>
        </span>

        <!-- User Dropdown Menu -->
        <div class="dropdown">
            <button class="btn btn-light border-0 d-flex align-items-center gap-2 py-1 px-2 rounded-3" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-bold" style="width: 38px; height: 38px; font-size: 0.95rem;">
                    <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="d-none d-md-block text-start lh-sm pe-1">
                    <div class="font-semibold text-sm text-dark"><?= e($currentUser['name'] ?? 'User') ?></div>
                    <small class="text-muted text-xs"><?= e($currentUser['nisn_nip'] ?? $currentUser['username'] ?? '') ?></small>
                </div>
                <i class="bi bi-chevron-down text-muted text-xs ms-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 py-2" aria-labelledby="userMenuButton" style="min-width: 230px;">
                <li class="px-3 py-2 border-bottom mb-1">
                    <div class="font-bold text-dark"><?= e($currentUser['name'] ?? 'Pengguna') ?></div>
                    <div class="text-muted text-xs"><?= e($currentUser['email'] ?? '') ?></div>
                    <span class="badge <?= $roleBadgeClass ?> rounded-pill text-xs mt-1"><?= $roleLabel ?></span>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 text-dark text-sm font-semibold py-2" href="<?= base_url('profile') ?>">
                        <i class="bi bi-person-circle text-primary fs-6"></i>
                        <span>Profil Saya</span>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 text-danger text-sm font-semibold" href="<?= base_url('logout') ?>">
                        <i class="bi bi-box-arrow-right"></i> Keluar (Logout)
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<script>
(function() {
    const notifBtn = document.getElementById('notifMenuButton');
    const notifBadge = document.getElementById('notifBadge');
    const notifContainer = document.getElementById('notifListContainer');
    const btnMarkAll = document.getElementById('btnMarkAllNotifRead');

    if (!notifBtn || !notifBadge) return;

    const NOTIF_URL = '<?= base_url('notifications/ajax') ?>';
    const MARK_READ_URL = '<?= base_url('notifications/markRead') ?>';
    const MARK_ALL_URL = '<?= base_url('notifications/markAllRead') ?>';

    let lastUnreadCount = -1;

    // Web Audio Sound Chime
    function playNotificationSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.35);
        } catch (e) {}
    }

    // HTML5 Browser Desktop Notification
    function triggerWebPush(item) {
        if (!("Notification" in window)) return;
        if (Notification.permission === "granted" && item) {
            try {
                const notif = new Notification(item.title, {
                    body: item.message,
                    icon: 'https://cdn-icons-png.flaticon.com/512/3602/3602145.png'
                });
                if (item.link) {
                    notif.onclick = function() {
                        window.focus();
                        window.location.href = item.link;
                    };
                }
            } catch (e) {}
        }
    }

    // Load notifikasi dari server
    async function fetchNotifications(renderList = true) {
        try {
            const res = await fetch(NOTIF_URL, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (!data.success) return;

            // Update badge counter
            const unread = data.unread_count || 0;
            const items = data.notifications || [];

            // Jika ada notifikasi baru masuk saat user aktif
            if (lastUnreadCount !== -1 && unread > lastUnreadCount) {
                playNotificationSound();
                if (items.length > 0) {
                    triggerWebPush(items[0]);
                }
            }
            lastUnreadCount = unread;

            if (unread > 0) {
                notifBadge.textContent = unread > 99 ? '99+' : unread;
                notifBadge.classList.remove('d-none');
            } else {
                notifBadge.classList.add('d-none');
            }

            // Render list jika requested
            if (renderList && notifContainer) {
                renderNotificationList(items);
            }
        } catch (e) {
            console.warn('[Notifications] Gagal memuat notifikasi:', e);
        }
    }

    // Render list HTML
    function renderNotificationList(items) {
        if (!items || items.length === 0) {
            notifContainer.innerHTML = `
                <div class="text-center py-4 text-muted text-xs">
                    <i class="bi bi-bell-slash fs-4 d-block mb-1 text-secondary opacity-50"></i>
                    <div>Tidak ada notifikasi</div>
                </div>
            `;
            return;
        }

        const typeColorMap = {
            'info': 'text-primary bg-primary bg-opacity-10',
            'success': 'text-success bg-success bg-opacity-10',
            'warning': 'text-warning bg-warning bg-opacity-10',
            'danger': 'text-danger bg-danger bg-opacity-10'
        };

        let html = '';
        items.forEach(item => {
            const isUnread = item.is_read === 0;
            const bgClass = isUnread ? 'bg-light' : '';
            const colorClass = typeColorMap[item.type] || 'text-primary bg-primary bg-opacity-10';

            html += `
                <div class="p-2.5 border-bottom notif-item ${bgClass} position-relative" style="cursor: pointer; transition: background 0.15s;" data-id="${item.id}" data-link="${item.link || ''}">
                    <div class="d-flex align-items-start gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center p-2 ${colorClass}" style="width: 32px; height: 32px; flex-shrink: 0;">
                            <i class="bi ${item.icon || 'bi-bell'} fs-6"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="d-flex align-items-center justify-content-between mb-0.5">
                                <span class="font-bold text-dark text-xs text-truncate">${item.title}</span>
                                <small class="text-muted" style="font-size: 0.68rem;">${item.time_ago}</small>
                            </div>
                            <div class="text-secondary text-xs lh-sm text-truncate" style="max-width: 220px;" title="${item.message}">
                                ${item.message}
                            </div>
                        </div>
                        ${isUnread ? '<span class="position-absolute end-0 top-50 translate-middle-y me-2 rounded-circle bg-primary" style="width: 7px; height: 7px;"></span>' : ''}
                    </div>
                </div>
            `;
        });

        notifContainer.innerHTML = html;

        // Tambah event click listener pada item notifikasi
        notifContainer.querySelectorAll('.notif-item').forEach(el => {
            el.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');
                const link = this.getAttribute('data-link');

                // Mark read
                try {
                    const formData = new FormData();
                    formData.append('id', id);
                    await fetch(MARK_READ_URL, {
                        method: 'POST',
                        body: formData
                    });
                } catch (e) {}

                if (link && link !== 'null' && link !== '') {
                    window.location.href = link;
                } else {
                    fetchNotifications(true);
                }
            });
        });
    }

    // Event listener dropdown buka
    notifBtn.addEventListener('show.bs.dropdown', function() {
        fetchNotifications(true);
    });

    // Mark all as read
    if (btnMarkAll) {
        btnMarkAll.addEventListener('click', async function(e) {
            e.stopPropagation();
            try {
                const res = await fetch(MARK_ALL_URL, {
                    method: 'POST'
                });
                const data = await res.json();
                if (data.success) {
                    notifBadge.classList.add('d-none');
                    fetchNotifications(true);
                }
            } catch (e) {}
        });
    }

    // Polling setiap 30 detik untuk alert berkala
    fetchNotifications(true);
    setInterval(() => {
        fetchNotifications(false);
    }, 30000);
})();
</script>
