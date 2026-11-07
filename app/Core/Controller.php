<?php
/**
 * SmartExam AI - Base Controller
 * Kelas induk untuk seluruh Controller aplikasi.
 */

namespace App\Core;

abstract class Controller
{
    /**
     * Render view template
     */
    protected function view(string $viewPath, array $data = [], ?string $layout = 'main'): void
    {
        View::render($viewPath, $data, $layout);
    }

    /**
     * Instansiasi dan return objek Model
     */
    protected function model(string $modelName): object
    {
        $class = "App\\Models\\" . $modelName;
        if (class_exists($class)) {
            return new $class();
        }
        die("Model class <code>{$class}</code> tidak ditemukan.");
    }

    /**
     * Kirim respons dalam format JSON
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect URL ke path tertentu
     */
    protected function redirect(string $path): void
    {
        if (function_exists('redirect')) {
            redirect($path);
        } else {
            $url = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
                ? $path 
                : BASE_URL . '/' . ltrim($path, '/');
            header("Location: " . $url);
            exit;
        }
    }

    /**
     * Cek apakah method request adalah POST
     */
    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /**
     * Cek apakah method request adalah GET
     */
    protected function isGet(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET';
    }

    /**
     * Ambil data dari $_POST dengan proteksi null-coalescing
     */
    protected function getPost(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Ambil data dari $_GET dengan proteksi null-coalescing
     */
    protected function getQuery(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
}
