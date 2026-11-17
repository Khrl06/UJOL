<?php
namespace Tests\Unit;

use App\Helpers\SanitizeHelper;

class SanitizeHelperTest extends TestCase
{
    public function testCleanStripsDangerousHtmlTags(): void
    {
        $input = '<script>alert("xss")</script>Halo Siswa';
        $cleaned = SanitizeHelper::clean($input);

        $this->assertStringContainsString('&lt;script&gt;', $cleaned);
        $this->assertStringContainsString('&lt;/script&gt;', $cleaned);
        $this->assertFalse(str_contains($cleaned, '<script>'));
    }

    public function testCleanTrimsWhitespace(): void
    {
        $input = '   Ujian Akhir Semester   ';
        $cleaned = SanitizeHelper::clean($input);

        $this->assertEquals('Ujian Akhir Semester', $cleaned);
    }

    public function testCleanEscapesQuotes(): void
    {
        $input = 'Nama "Murid" & \'Guru\'';
        $cleaned = SanitizeHelper::clean($input);

        $this->assertStringContainsString('&quot;Murid&quot;', $cleaned);
        $this->assertStringContainsString('&#039;Guru&#039;', $cleaned);
    }

    public function testCleanHandlesNestedArraysRecursively(): void
    {
        $data = [
            'username' => ' <admin> ',
            'profile'  => [
                'bio'   => '<b>Pengawas Ujian</b>',
                'tags'  => [' <i>Matematika</i> ']
            ]
        ];

        $cleaned = SanitizeHelper::clean($data);

        $this->assertIsArray($cleaned);
        $this->assertEquals('&lt;admin&gt;', $cleaned['username']);
        $this->assertEquals('&lt;b&gt;Pengawas Ujian&lt;/b&gt;', $cleaned['profile']['bio']);
        $this->assertEquals('&lt;i&gt;Matematika&lt;/i&gt;', $cleaned['profile']['tags'][0]);
    }

    public function testEmailSanitization(): void
    {
        $email = ' siswa.unggul+test@sekolah.sch.id ';
        $sanitized = SanitizeHelper::email($email);

        $this->assertEquals('siswa.unggul+test@sekolah.sch.id', $sanitized);
    }
}
