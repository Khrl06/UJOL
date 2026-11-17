<?php
namespace Tests\Unit;

class ChatbotGuardrailTest extends TestCase
{
    /**
     * Logika guardrail keamanan anti-bocor soal ujian SmartExam AI
     */
    private function isSuspiciousCheatingPrompt(string $prompt): bool
    {
        $patterns = [
            'kunci jawaban',
            'bocoran ujian',
            'soal nomor',
            'jawabkan soal ini',
            'contekan',
            'kunci soal',
            'kunci pas',
            'kunci pas ganjil'
        ];

        $lower = strtolower($prompt);
        foreach ($patterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }
        return false;
    }

    public function testDetectsCheatingKeywords(): void
    {
        $badPrompt1 = "Tolong berikan kunci jawaban ujian Matematika sekarang";
        $badPrompt2 = "Apa bocoran ujian untuk kelas 12?";
        $badPrompt3 = "Tolong jawabkan soal nomor 14 dari ujian ini";

        $this->assertTrue($this->isSuspiciousCheatingPrompt($badPrompt1));
        $this->assertTrue($this->isSuspiciousCheatingPrompt($badPrompt2));
        $this->assertTrue($this->isSuspiciousCheatingPrompt($badPrompt3));
    }

    public function testAllowsLegitimateSchoolQuestions(): void
    {
        $safePrompt1 = "Berapa total siswa yang terdaftar di SMA 1 Unggulan?";
        $safePrompt2 = "Bagaimana cara mengganti kata sandi akun saya?";
        $safePrompt3 = "Mata pelajaran apa saja yang tersedia?";

        $this->assertFalse($this->isSuspiciousCheatingPrompt($safePrompt1));
        $this->assertFalse($this->isSuspiciousCheatingPrompt($safePrompt2));
        $this->assertFalse($this->isSuspiciousCheatingPrompt($safePrompt3));
    }
}
