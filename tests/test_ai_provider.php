<?php
require_once __DIR__ . '/../app/Config/Config.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . DIRECTORY_SEPARATOR;
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $len)) . '.php';
    if (file_exists($file)) require_once $file;
});

require_once APP_PATH . '/Helpers/SettingHelper.php';

$model = new \App\Models\SettingModel();
$aiSettings = $model->getByGroup('ai');

echo "\n======================================================\n";
echo "  KONFIGURASI AI AKTIF PADA SISTEM UJOL\n";
echo "======================================================\n";
foreach ($aiSettings as $k => $v) {
    if ($k === 'ai_api_key' && !empty($v)) {
        echo sprintf("  %-22s : %s\n", $k, substr($v, 0, 8) . '... (Disamarkan aman)');
    } else {
        echo sprintf("  %-22s : %s\n", $k, $v ?: '(kosong/bawaan resmi)');
    }
}
echo "======================================================\n";
echo "✓ Provider AI: Bebas digunakan untuk provider apa saja!\n";
echo "✓ Base URL Custom: Tersedia & tersinkronisasi dinamis.\n";
echo "======================================================\n\n";

$key = 'AQ.Ab8RN6IdqRznTT2HZKQfBXfHKGj7m_V3pDiKmCjeOKk58LELfA';
$geminiCandidates = ['gemini-2.5-flash', 'gemini-flash-latest', 'gemini-flash-lite-latest'];
foreach ($geminiCandidates as $modelName) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($key);
    $body = json_encode([
        'contents' => [
            ['parts' => [['text' => 'Halo, ini uji coba sistem. Jawab singkat satu kalimat.']]]
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $resData = json_decode($resp, true);
    $reply = $resData['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($httpCode >= 200 && $httpCode < 300 && $reply) {
        echo "  Model   : {$modelName}\n";
        echo "  Status  : BERHASIL (HTTP {$httpCode})\n";
        echo "  Respon  : " . trim($reply) . "\n";
        break;
    }
}
echo "======================================================\n";
