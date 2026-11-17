<?php
namespace Tests\Unit;

use App\Helpers\CsrfHelper;

class CsrfHelperTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    public function testGenerateTokenCreatesHexToken(): void
    {
        $token = CsrfHelper::generateToken();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token), 'CSRF token harus 64 karakter hex (32 bytes).');
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    public function testValidateTokenMatchesSessionToken(): void
    {
        $token = CsrfHelper::generateToken();

        $this->assertTrue(CsrfHelper::validateToken($token));
        $this->assertFalse(CsrfHelper::validateToken('invalid_token_12345'));
        $this->assertFalse(CsrfHelper::validateToken(''));
    }

    public function testTokenFieldGeneratesHiddenInput(): void
    {
        $html = CsrfHelper::tokenField();

        $this->assertStringContainsString('type="hidden"', $html);
        $this->assertStringContainsString('name="csrf_token"', $html);
        $this->assertStringContainsString('value="', $html);
    }
}
