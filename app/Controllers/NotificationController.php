<?php
/**
 * SmartExam AI - Notification Controller
 * Mengelola API endpoint notifikasi in-app untuk polling, mark read, dan render UI.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthMiddleware;
use App\Helpers\CsrfHelper;
use App\Models\NotificationModel;

class NotificationController extends Controller
{
    private NotificationModel $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    /**
     * AJAX Endpoint: Ambil daftar notifikasi pengguna saat ini (/notifications atau /notifications/ajax)
     */
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!AuthMiddleware::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = AuthMiddleware::id();
        $notifications = $this->notificationModel->getByUser($userId, 15);
        $unreadCount = $this->notificationModel->countUnread($userId);

        // Format waktu relatif (human readable time)
        $formatted = array_map(function ($n) {
            $createdTime = strtotime($n['created_at']);
            $diff = time() - $createdTime;
            if ($diff < 60) {
                $timeAgo = 'Baru saja';
            } elseif ($diff < 3600) {
                $timeAgo = floor($diff / 60) . ' mnt lalu';
            } elseif ($diff < 86400) {
                $timeAgo = floor($diff / 3600) . ' jam lalu';
            } else {
                $timeAgo = date('d M H:i', $createdTime);
            }

            return [
                'id'         => (int)$n['id'],
                'title'      => htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8'),
                'message'    => htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8'),
                'type'       => $n['type'],
                'icon'       => $n['icon'] ?: 'bi-bell',
                'link'       => $n['link'] ? base_url($n['link']) : null,
                'is_read'    => (int)$n['is_read'],
                'created_at' => $n['created_at'],
                'time_ago'   => $timeAgo
            ];
        }, $notifications);

        echo json_encode([
            'success'       => true,
            'unread_count'  => $unreadCount,
            'notifications' => $formatted
        ]);
        exit;
    }

    /**
     * AJAX Endpoint: Hitung jumlah unread saja (ringan untuk interval polling)
     */
    public function count(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!AuthMiddleware::isLoggedIn()) {
            echo json_encode(['success' => false, 'count' => 0]);
            exit;
        }

        $userId = AuthMiddleware::id();
        $count = $this->notificationModel->countUnread($userId);

        echo json_encode([
            'success' => true,
            'count'   => $count
        ]);
        exit;
    }

    /**
     * AJAX Endpoint: Tandai satu notifikasi telah dibaca
     */
    public function markRead(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isPost() || !AuthMiddleware::isLoggedIn()) {
            echo json_encode(['success' => false]);
            exit;
        }

        $userId = AuthMiddleware::id();
        $id = (int)$this->getPost('id', 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        $updated = $this->notificationModel->markAsRead($id, $userId);
        $newCount = $this->notificationModel->countUnread($userId);

        echo json_encode([
            'success'      => $updated,
            'unread_count' => $newCount
        ]);
        exit;
    }

    /**
     * AJAX Endpoint: Tandai semua notifikasi telah dibaca
     */
    public function markAllRead(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isPost() || !AuthMiddleware::isLoggedIn()) {
            echo json_encode(['success' => false]);
            exit;
        }

        $userId = AuthMiddleware::id();
        $this->notificationModel->markAllAsRead($userId);

        echo json_encode([
            'success'      => true,
            'unread_count' => 0
        ]);
        exit;
    }

    /**
     * Endpoint untuk trigger pengecekan pengingat jadwal ujian (H-1 dan 30 menit)
     * Dapat dipanggil oleh Cron Job, CLI, maupun AJAX / Webhook
     */
    public function triggerReminders(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $notifService = new \App\Helpers\NotificationService();
        $result = $notifService->checkAndDispatchUpcomingReminders();

        echo json_encode($result);
        exit;
    }

    /**
     * Endpoint untuk uji coba pengiriman pesan WhatsApp Gateway
     */
    public function testWhatsApp(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!AuthMiddleware::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
            exit;
        }

        $phone = $this->getPost('phone') ?: $this->getGet('phone');
        $user = AuthMiddleware::user();

        if (empty($phone)) {
            $profile = (new \App\Helpers\NotificationService())->getUserPhoneAndProfile((int)$user['id']);
            $phone = !empty($profile['phone']) ? $profile['phone'] : '081234567890';
        }

        $message = "🔔 *TES KONEKSI WHATSAPP GATEWAY*\n"
                 . "*SmartExam AI (UJOL)*\n"
                 . "----------------------------------------\n"
                 . "Halo *{$user['name']}*,\n\n"
                 . "Pesan ini adalah uji coba koneksi gateway WhatsApp untuk sistem pemberitahuan otomatis SmartExam AI.\n\n"
                 . "⏰ Waktu Uji: " . date('d-m-Y H:i:s') . " WIB\n"
                 . "📱 Provider: " . (defined('WA_GATEWAY_PROVIDER') ? WA_GATEWAY_PROVIDER : 'fonnte') . "\n"
                 . "✅ Status: Integrasi Notifikasi Berfungsi Normal!";

        $res = \App\Helpers\WhatsAppGateway::sendMessage($phone, $message);
        echo json_encode($res);
        exit;
    }
}
