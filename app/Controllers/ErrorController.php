<?php
/**
 * SmartExam AI - Error Controller
 * Menangani tampilan error HTTP seperti 404 Not Found dan 403 Forbidden.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthMiddleware;

class ErrorController extends Controller
{
    /**
     * Halaman 404 Not Found
     */
    public function notFound(string $message = 'Halaman yang Anda tuju tidak ditemukan.'): void
    {
        http_response_code(404);

        $layout = AuthMiddleware::isLoggedIn() ? 'main' : 'auth';

        $this->view('errors/404', [
            'pageTitle' => '404 - Halaman Tidak Ditemukan',
            'message'   => $message,
            'user'      => AuthMiddleware::user()
        ], $layout);
    }

    /**
     * Halaman 403 Forbidden (Akses Ditolak / Hak Akses Tidak Sesuai)
     */
    public function forbidden(string $message = 'Anda tidak memiliki izin untuk mengakses halaman ini.', ?string $requiredRole = null): void
    {
        http_response_code(403);

        $layout = AuthMiddleware::isLoggedIn() ? 'main' : 'auth';

        $this->view('errors/403', [
            'pageTitle'    => '403 - Akses Ditolak',
            'message'      => $message,
            'requiredRole' => $requiredRole,
            'user'         => AuthMiddleware::user(),
            'currentRole'  => AuthMiddleware::role()
        ], $layout);
    }
}
