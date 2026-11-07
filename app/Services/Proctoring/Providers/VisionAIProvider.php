<?php
/**
 * SmartExam AI - Vision AI Provider Implementation
 * Provider pengolahan visi komputer untuk deteksi wajah, kamera terhalang, dan anomali visual.
 */

namespace App\Services\Proctoring\Providers;

use App\Services\Proctoring\AIProviderInterface;

class VisionAIProvider implements AIProviderInterface
{
    protected string $providerName = 'SmartExam Vision Edge AI v1.0';
    protected bool $isAvailable = true;

    // Batasan & Ambang Batas (Thresholds)
    protected float $minFaceAreaRatio = 0.045; // Wajah < 4.5% dari frame dianggap FACE_TOO_FAR
    protected float $blockedLuminanceThreshold = 14.0; // Rata-rata luminansi < 14 dianggap CAMERA_BLOCKED
    protected int $missingDurationThreshold = 3; // > 3 detik wajah hilang

    public function __construct(bool $isAvailable = true)
    {
        $this->isAvailable = $isAvailable;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function setAvailable(bool $status): void
    {
        $this->isAvailable = $status;
    }

    /**
     * Deteksi keberadaan wajah
     */
    public function detectFace(array $frameData): array
    {
        if (!$this->isAvailable()) {
            return ['detected' => false, 'confidence' => 0.0, 'error' => 'AI Service Unavailable'];
        }

        $faceCount = (int)($frameData['face_count'] ?? 0);
        $detected = ($faceCount > 0);
        $confidence = $detected ? min(98.5, (float)($frameData['confidence'] ?? 92.0)) : 0.0;

        return [
            'detected'   => $detected,
            'face_count' => $faceCount,
            'confidence' => $confidence,
            'box'        => $frameData['box'] ?? null
        ];
    }

    /**
     * Deteksi anomali lebih dari satu wajah
     */
    public function detectMultipleFace(array $frameData): array
    {
        if (!$this->isAvailable()) {
            return ['multiple_detected' => false, 'count' => 0, 'confidence' => 0.0];
        }

        $faceCount = (int)($frameData['face_count'] ?? 0);
        $isMultiple = ($faceCount > 1);
        $confidence = $isMultiple ? min(99.0, 75.0 + ($faceCount * 10.0)) : 0.0;

        return [
            'multiple_detected' => $isMultiple,
            'count'             => $faceCount,
            'confidence'        => $confidence
        ];
    }

    /**
     * Deteksi wajah tidak terlihat / menghilang
     */
    public function detectFaceMissing(array $frameData): array
    {
        if (!$this->isAvailable()) {
            return ['missing' => false, 'duration' => 0, 'confidence' => 0.0];
        }

        $faceCount = (int)($frameData['face_count'] ?? 0);
        $duration = (int)($frameData['duration'] ?? 0);
        $isMissing = ($faceCount === 0);

        $confidence = 0.0;
        if ($isMissing) {
            // Semakin lama wajah tidak terlihat, semakin tinggi tingkat keyakinan AI
            $confidence = min(99.5, 70.0 + ($duration * 5.0));
        }

        return [
            'missing'    => $isMissing,
            'duration'   => $duration,
            'confidence' => $confidence
        ];
    }

    /**
     * Analisis frame secara menyeluruh untuk mengekstrak anomali pengawasan
     */
    public function analyzeFrame(array $frameData, string $cameraSource = 'cam1'): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'error'   => 'AI Provider is currently unavailable',
                'events'  => []
            ];
        }

        $detectedEvents = [];
        $faceCount = (int)($frameData['face_count'] ?? 1);
        $duration = (int)($frameData['duration'] ?? 0);
        $luminance = isset($frameData['luminance']) ? (float)$frameData['luminance'] : 50.0;
        $faceRatio = isset($frameData['face_ratio']) ? (float)$frameData['face_ratio'] : 0.15;
        $isDisconnected = !empty($frameData['is_disconnected']);
        $suspiciousObjects = $frameData['suspicious_objects'] ?? [];

        // 1. Deteksi CAMERA_DISCONNECTED
        if ($isDisconnected) {
            $detectedEvents[] = [
                'event_type'       => 'CAMERA_DISCONNECTED',
                'camera'           => $cameraSource,
                'confidence_score' => 100.00,
                'duration'         => max(1, $duration),
                'severity'         => 'high',
                'description'      => "Koneksi aliran kamera {$cameraSource} terputus atau dinonaktifkan."
            ];
        }

        // 2. Deteksi CAMERA_BLOCKED (Lensa tertutup atau sangat gelap)
        if ($luminance <= $this->blockedLuminanceThreshold) {
            $detectedEvents[] = [
                'event_type'       => 'CAMERA_BLOCKED',
                'camera'           => $cameraSource,
                'confidence_score' => 95.00,
                'duration'         => max(1, $duration),
                'severity'         => 'high',
                'description'      => "Lensa kamera {$cameraSource} terdeteksi terhalang, ditutup, atau pencahayaan ruangan ekstrem gelap (Luminansi: {$luminance} lux)."
            ];
        }

        // Untuk Kamera 1 (Kamera Wajah Peserta):
        if ($cameraSource === 'cam1') {
            // 3. Deteksi FACE_MISSING
            if ($faceCount === 0 && $duration >= $this->missingDurationThreshold) {
                $conf = min(99.0, 75.0 + ($duration * 4.0));
                $detectedEvents[] = [
                    'event_type'       => 'FACE_MISSING',
                    'camera'           => 'cam1',
                    'confidence_score' => round($conf, 2),
                    'duration'         => $duration,
                    'severity'         => 'high',
                    'description'      => "Wajah peserta tidak terdeteksi pada kamera utama selama {$duration} detik berturut-turut."
                ];
            }

            // 4. Deteksi MULTIPLE_FACE
            if ($faceCount > 1) {
                $conf = min(98.0, 80.0 + ($faceCount * 6.0));
                $detectedEvents[] = [
                    'event_type'       => 'MULTIPLE_FACE',
                    'camera'           => 'cam1',
                    'confidence_score' => round($conf, 2),
                    'duration'         => max(1, $duration),
                    'severity'         => 'high',
                    'description'      => "Terdeteksi {$faceCount} wajah orang di dalam area pandang kamera utama."
                ];
            }

            // 5. Deteksi FACE_TOO_FAR
            if ($faceCount === 1 && $faceRatio > 0 && $faceRatio < $this->minFaceAreaRatio) {
                $pct = round($faceRatio * 100, 1);
                $detectedEvents[] = [
                    'event_type'       => 'FACE_TOO_FAR',
                    'camera'           => 'cam1',
                    'confidence_score' => 84.50,
                    'duration'         => max(1, $duration),
                    'severity'         => 'medium',
                    'description'      => "Posisi peserta terlalu jauh dari kamera (Area wajah hanya {$pct}% dari layar). Diperlukan posisi duduk yang lebih dekat."
                ];
            }
        }

        // 6. Deteksi SUSPICIOUS_OBJECT
        if (!empty($suspiciousObjects)) {
            $objName = is_array($suspiciousObjects) ? implode(', ', $suspiciousObjects) : (string)$suspiciousObjects;
            $detectedEvents[] = [
                'event_type'       => 'SUSPICIOUS_OBJECT',
                'camera'           => $cameraSource,
                'confidence_score' => 82.00,
                'duration'         => max(1, $duration),
                'severity'         => 'medium',
                'description'      => "Terdeteksi potensi objek mencurigakan ({$objName}) pada area pantauan kamera {$cameraSource}."
            ];
        }

        // 7. Deteksi BROWSER_ACTIVITY_EVENT
        if (!empty($frameData['browser_event'])) {
            $bEvt = $frameData['browser_event']; // misal: 'tab_switch', 'window_blur', 'exit_fullscreen'
            $bDesc = $frameData['browser_event_desc'] ?? 'Peserta terdeteksi berpindah fokus tab browser atau membuka aplikasi lain.';
            $detectedEvents[] = [
                'event_type'       => 'BROWSER_ACTIVITY_EVENT',
                'camera'           => 'system',
                'confidence_score' => 99.00,
                'duration'         => max(1, $duration),
                'severity'         => 'medium',
                'description'      => "[Browser Event: {$bEvt}] {$bDesc}"
            ];
        }

        return [
            'success'   => true,
            'provider'  => $this->providerName,
            'timestamp' => date('Y-m-d H:i:s'),
            'events'    => $detectedEvents
        ];
    }
}
