<?php
/**
 * SmartExam AI - AI Proctoring Service
 * Service terpusat yang memisahkan logika orkestrasi AI dari Controller.
 * Mengimplementasikan prinsip Privacy-by-Design dan Human-in-the-Loop.
 */

namespace App\Services\Proctoring;

use App\Models\ProctoringEventModel;
use App\Services\Proctoring\Providers\VisionAIProvider;

class AIProctoringService
{
    protected AIProviderInterface $provider;
    protected ProctoringEventModel $eventModel;
    protected ProctoringLogger $logger;

    public function __construct(
        ?AIProviderInterface $provider = null,
        ?ProctoringEventModel $eventModel = null,
        ?ProctoringLogger $logger = null
    ) {
        $this->logger = $logger ?? new ProctoringLogger();
        $this->provider = $provider ?? new VisionAIProvider();
        $this->eventModel = $eventModel ?? new ProctoringEventModel();
    }

    public function setProvider(AIProviderInterface $provider): void
    {
        $this->provider = $provider;
        $this->logger->info("AI Provider aktif diubah ke: " . $provider->getProviderName());
    }

    public function getProvider(): AIProviderInterface
    {
        return $this->provider;
    }

    /**
     * Pendelegasian: Deteksi Wajah
     */
    public function detectFace(array $frameData): array
    {
        try {
            return $this->provider->detectFace($frameData);
        } catch (\Throwable $e) {
            $this->logger->error("Kegagalan pada detectFace: " . $e->getMessage());
            return ['detected' => true, 'confidence' => 0.0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Pendelegasian: Deteksi Multi-Wajah
     */
    public function detectMultipleFace(array $frameData): array
    {
        try {
            return $this->provider->detectMultipleFace($frameData);
        } catch (\Throwable $e) {
            $this->logger->error("Kegagalan pada detectMultipleFace: " . $e->getMessage());
            return ['multiple_detected' => false, 'count' => 1, 'confidence' => 0.0];
        }
    }

    /**
     * Pendelegasian: Deteksi Wajah Menghilang
     */
    public function detectFaceMissing(array $frameData): array
    {
        try {
            return $this->provider->detectFaceMissing($frameData);
        } catch (\Throwable $e) {
            $this->logger->error("Kegagalan pada detectFaceMissing: " . $e->getMessage());
            return ['missing' => false, 'duration' => 0, 'confidence' => 0.0];
        }
    }

    /**
     * Pendelegasian: Analisis Komprehensif Frame
     */
    public function analyzeFrame(array $frameData, string $cameraSource = 'cam1'): array
    {
        try {
            return $this->provider->analyzeFrame($frameData, $cameraSource);
        } catch (\Throwable $e) {
            $this->logger->error("Kegagalan pada analyzeFrame: " . $e->getMessage());
            return ['success' => false, 'events' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Proses laporan anomali yang dikirim dari antarmuka Exam Engine siswa
     * Memvalidasi data, mengekstrak bukti snapshot berdasar Privacy-by-Design,
     * dan mencatat event dengan status awal 'REVIEW'.
     */
    public function processIncomingAnomaly(int $participantId, array $payload): array
    {
        try {
            // Periksa ketersediaan AI provider
            if (!$this->provider->isAvailable()) {
                $this->logger->warning("Laporan anomali diterima namun AI provider sedang offline.", ['participant_id' => $participantId]);
                // Tetap catat log internal, jangan gagalkan siswa
                return [
                    'success' => false,
                    'message' => 'Layanan AI proctoring sedang mengalami maintenance. Ujian tetap berlanjut.'
                ];
            }

            $camera = in_array($payload['camera'] ?? '', ['cam1', 'cam2', 'system']) ? $payload['camera'] : 'cam1';
            
            // Analisis data frame via Provider
            $analysisResult = $this->analyzeFrame($payload, $camera);
            $events = $analysisResult['events'] ?? [];

            // Jika payload membawa event langsung dari browser (misal BROWSER_ACTIVITY_EVENT atau CAMERA_DISCONNECTED)
            if (empty($events) && !empty($payload['event_type'])) {
                $events[] = [
                    'event_type'       => $payload['event_type'],
                    'camera'           => $camera,
                    'confidence_score' => (float)($payload['confidence_score'] ?? 90.00),
                    'duration'         => (int)($payload['duration'] ?? 1),
                    'severity'         => $payload['severity'] ?? 'medium',
                    'description'      => $payload['description'] ?? 'Anomali pengawasan terdeteksi.'
                ];
            }

            $recordedEventIds = [];

            // Simpan bukti foto jika ada (Privacy-by-Design: thumbnail terkompresi)
            $evidencePath = null;
            if (!empty($payload['snapshot_base64'])) {
                $evidencePath = $this->saveEvidenceSnapshot($participantId, $payload['snapshot_base64'], $payload['event_type'] ?? 'ANOMALY');
            }

            foreach ($events as $event) {
                // Semua insiden baru diberi status 'REVIEW' agar diverifikasi guru/admin
                $eventId = $this->recordEvent([
                    'exam_participant_id' => $participantId,
                    'camera'              => $event['camera'] ?? $camera,
                    'event_type'          => $event['event_type'],
                    'confidence_score'    => $event['confidence_score'] ?? 85.00,
                    'duration'            => $event['duration'] ?? 0,
                    'status'              => 'REVIEW', // Tidak memvonis langsung, diserahkan ke peninjau
                    'severity'            => $event['severity'] ?? 'medium',
                    'description'         => $event['description'] ?? 'Anomali terdeteksi oleh AI.',
                    'evidence_reference'  => $evidencePath,
                    'snapshot_url'        => $evidencePath
                ]);

                if ($eventId) {
                    $recordedEventIds[] = $eventId;
                }
            }

            return [
                'success'        => true,
                'recorded_count' => count($recordedEventIds),
                'event_ids'      => $recordedEventIds,
                'status'         => 'REVIEW'
            ];

        } catch (\Throwable $e) {
            $this->logger->error("Gagal memproses anomali proctoring: " . $e->getMessage(), [
                'participant_id' => $participantId,
                'trace'          => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem pengawasan internal. Ujian tidak terganggu.'
            ];
        }
    }

    /**
     * Catat satu baris event ke basis data
     */
    public function recordEvent(array $eventData): ?int
    {
        $id = $this->eventModel->createEvent($eventData);
        if ($id) {
            $this->logger->info("Event Proctoring Dicatat ID: {$id}", [
                'type'       => $eventData['event_type'] ?? 'UNKNOWN',
                'camera'     => $eventData['camera'] ?? 'cam1',
                'confidence' => $eventData['confidence_score'] ?? 0,
                'status'     => $eventData['status'] ?? 'REVIEW'
            ]);
        }
        return $id;
    }

    /**
     * Perbarui status event oleh Guru / Administrator (Human-in-the-Loop)
     * Status yang valid: NORMAL, REVIEW, REVIEWED, DISMISSED
     */
    public function updateEventStatus(int $eventId, string $newStatus, ?string $reviewerNotes = null): bool
    {
        $validStatuses = ['NORMAL', 'REVIEW', 'REVIEWED', 'DISMISSED'];
        $cleanStatus = strtoupper(trim($newStatus));

        if (!in_array($cleanStatus, $validStatuses, true)) {
            $this->logger->warning("Upaya pembaruan status tidak valid: {$newStatus} pada event ID: {$eventId}");
            return false;
        }

        $success = $this->eventModel->updateStatus($eventId, $cleanStatus, $reviewerNotes);
        if ($success) {
            $this->logger->info("Status event ID {$eventId} diperbarui menjadi: {$cleanStatus}", [
                'notes' => $reviewerNotes
            ]);
        }
        return $success;
    }

    /**
     * Privacy-by-Design: Simpan cuplikan bukti dalam ukuran resolusi rendah dan aman.
     * Tidak menyimpan rekaman video penuh tanpa henti.
     */
    protected function saveEvidenceSnapshot(int $participantId, string $base64Data, string $eventType): ?string
    {
        try {
            // Bersihkan header Data URI jika ada
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            }

            $decoded = base64_decode($base64Data);
            if (!$decoded) {
                return null;
            }

            // Security Hardening 1: Batasi ukuran berkas maksimal 2 MB
            if (strlen($decoded) > 2 * 1024 * 1024) {
                $this->logger->warning("Upload snapshot ditolak: ukuran melebihi batas 2MB.", ['participant_id' => $participantId]);
                return null;
            }

            // Security Hardening 2: Validasi binary format gambar & MIME Type
            $imageInfo = @getimagesizefromstring($decoded);
            if ($imageInfo === false) {
                $this->logger->warning("Upload snapshot ditolak: payload bukan berkas gambar valid.", ['participant_id' => $participantId]);
                return null;
            }

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            $detectedMime = $imageInfo['mime'] ?? '';
            if (!in_array($detectedMime, $allowedMimes, true)) {
                $this->logger->warning("Upload snapshot ditolak: MIME type {$detectedMime} tidak diizinkan.", ['participant_id' => $participantId]);
                return null;
            }

            $extension = match ($detectedMime) {
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'jpg'
            };

            $storageDir = defined('PUBLIC_PATH') 
                ? PUBLIC_PATH . '/assets/proctoring/snapshots' 
                : dirname(__DIR__, 3) . '/public/assets/proctoring/snapshots';

            if (!is_dir($storageDir)) {
                @mkdir($storageDir, 0755, true);
            }

            // Security Hardening 3: Nama file unik kriptografis untuk mencegah enumerasi & direktori traversal
            $cleanType = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $eventType));
            $randomEntropy = bin2hex(random_bytes(12));
            $fileName = sprintf("snap_p%d_%s_%s_%d.%s", $participantId, $cleanType, $randomEntropy, time(), $extension);
            $filePath = $storageDir . DIRECTORY_SEPARATOR . $fileName;

            // Simpan gambar secara aman
            if (@file_put_contents($filePath, $decoded) !== false) {
                return 'assets/proctoring/snapshots/' . $fileName;
            }

            return null;
        } catch (\Throwable $e) {
            $this->logger->error("Gagal menyimpan snapshot bukti: " . $e->getMessage());
            return null;
        }
    }
}
