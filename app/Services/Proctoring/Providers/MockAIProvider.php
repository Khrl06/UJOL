<?php
/**
 * SmartExam AI - Mock AI Provider
 * Digunakan untuk unit testing, simulasi, dan fallback saat model eksternal tidak aktif.
 */

namespace App\Services\Proctoring\Providers;

use App\Services\Proctoring\AIProviderInterface;

class MockAIProvider implements AIProviderInterface
{
    protected string $providerName = 'SmartExam Mock AI Provider (Test)';
    protected bool $isAvailable = true;

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

    public function setAvailable(bool $available): void
    {
        $this->isAvailable = $available;
    }

    public function detectFace(array $frameData): array
    {
        return [
            'detected'   => true,
            'face_count' => 1,
            'confidence' => 95.0,
            'box'        => ['x' => 100, 'y' => 80, 'w' => 120, 'h' => 140]
        ];
    }

    public function detectMultipleFace(array $frameData): array
    {
        $count = (int)($frameData['face_count'] ?? 1);
        return [
            'multiple_detected' => $count > 1,
            'count'             => $count,
            'confidence'        => $count > 1 ? 90.0 : 0.0
        ];
    }

    public function detectFaceMissing(array $frameData): array
    {
        $missing = empty($frameData['face_count']);
        return [
            'missing'    => $missing,
            'duration'   => (int)($frameData['duration'] ?? 0),
            'confidence' => $missing ? 92.0 : 0.0
        ];
    }

    public function analyzeFrame(array $frameData, string $cameraSource = 'cam1'): array
    {
        return [
            'success'   => true,
            'provider'  => $this->providerName,
            'timestamp' => date('Y-m-d H:i:s'),
            'events'    => []
        ];
    }
}
