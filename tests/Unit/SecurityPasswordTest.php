<?php
namespace Tests\Unit;

class SecurityPasswordTest extends TestCase
{
    public function testBcryptPasswordHashing(): void
    {
        $rawPassword = 'PasswordUjian2026!';
        $hashed = password_hash($rawPassword, PASSWORD_BCRYPT, ['cost' => 10]);

        $this->assertIsString($hashed);
        $this->assertStringContainsString('$2y$', $hashed, 'Hash harus menggunakan algoritma BCRYPT $2y$.');
        $this->assertTrue(password_verify($rawPassword, $hashed));
        $this->assertFalse(password_verify('WrongPassword999', $hashed));
    }

    public function testHashDifferentEveryTime(): void
    {
        $password = 'Secret123';
        $hash1 = password_hash($password, PASSWORD_BCRYPT);
        $hash2 = password_hash($password, PASSWORD_BCRYPT);

        $this->assertNotEquals($hash1, $hash2, 'Salt acak harus menghasilkan hash berbeda untuk teks yang sama.');
        $this->assertTrue(password_verify($password, $hash1));
        $this->assertTrue(password_verify($password, $hash2));
    }
}
