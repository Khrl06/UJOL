<?php
/**
 * SmartExam AI - AI Provider Interface
 * Kontrak abstraksi untuk provider model AI agar mudah diganti tanpa mengubah Controller/Aplikasi.
 */

namespace App\Services\Proctoring;

interface AIProviderInterface
{
    /**
     * Nama identitas provider model AI (misal: "Vision Edge Engine v1.0", "MediaPipe Face Detector")
     */
    public function getProviderName(): string;

    /**
     * Periksa apakah service / runtime AI sedang aktif dan siap menerima data
     */
    public function isAvailable(): bool;

    /**
     * Deteksi keberadaan wajah peserta pada frame
     * @param array $frameData Metadata frame (luminance, faceCount, bbox, raw/base64)
     * @return array ['detected' => bool, 'confidence' => float, 'metadata' => array]
     */
    public function detectFace(array $frameData): array;

    /**
     * Deteksi apakah terdapat lebih dari satu wajah di dalam frame
     * @param array $frameData
     * @return array ['multiple_detected' => bool, 'count' => int, 'confidence' => float]
     */
    public function detectMultipleFace(array $frameData): array;

    /**
     * Deteksi apakah wajah peserta menghilang / tidak terdeteksi
     * @param array $frameData
     * @return array ['missing' => bool, 'duration' => int, 'confidence' => float]
     */
    public function detectFaceMissing(array $frameData): array;

    /**
     * Analisis komprehensif frame untuk mendeteksi seluruh anomali proctoring
     * @param array $frameData
     * @param string $cameraSource 'cam1' (wajah) atau 'cam2' (meja)
     * @return array ['events' => array, 'confidence' => float, 'metrics' => array]
     */
    public function analyzeFrame(array $frameData, string $cameraSource = 'cam1'): array;
}
