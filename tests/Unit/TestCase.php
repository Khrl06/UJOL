<?php
namespace Tests\Unit;

if (class_exists(\PHPUnit\Framework\TestCase::class)) {
    abstract class TestCase extends \PHPUnit\Framework\TestCase
    {
    }
} else {
    abstract class TestCase
    {
        public function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
        {
            if ($expected !== $actual) {
                throw new \AssertionError($message ?: "Expected " . var_export($expected, true) . ", got " . var_export($actual, true));
            }
        }

        public function assertNotEquals(mixed $expected, mixed $actual, string $message = ''): void
        {
            if ($expected === $actual) {
                throw new \AssertionError($message ?: "Failed asserting that values are not equal.");
            }
        }

        public function assertTrue(mixed $condition, string $message = ''): void
        {
            if ($condition !== true) {
                throw new \AssertionError($message ?: "Failed asserting that condition is true.");
            }
        }

        public function assertFalse(mixed $condition, string $message = ''): void
        {
            if ($condition !== false) {
                throw new \AssertionError($message ?: "Failed asserting that condition is false.");
            }
        }

        public function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
        {
            if (!str_contains($haystack, $needle)) {
                throw new \AssertionError($message ?: "Failed asserting that '{$haystack}' contains '{$needle}'.");
            }
        }

        public function assertIsString(mixed $actual, string $message = ''): void
        {
            if (!is_string($actual)) {
                throw new \AssertionError($message ?: "Failed asserting that value is of type string.");
            }
        }

        public function assertIsArray(mixed $actual, string $message = ''): void
        {
            if (!is_array($actual)) {
                throw new \AssertionError($message ?: "Failed asserting that value is of type array.");
            }
        }

        public function assertArrayHasKey(mixed $key, array $array, string $message = ''): void
        {
            if (!array_key_exists($key, $array)) {
                throw new \AssertionError($message ?: "Failed asserting that array has key '{$key}'.");
            }
        }

        public function assertNotEmpty(mixed $actual, string $message = ''): void
        {
            if (empty($actual)) {
                throw new \AssertionError($message ?: "Failed asserting that value is not empty.");
            }
        }
    }
}
