<?php
/**
 * SmartExam AI - Dashboard Gateway Controller
 * Mengarahkan rute umum /dashboard ke dasbor spesifik sesuai peran aktif pengguna.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthMiddleware;

class DashboardController extends Controller
{
    /**
     * Gateway routing dasbor cerdas
     */
    public function index(): void
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::redirectToDashboard();
    }

    /**
     * Gateway routing profil pengguna sesuai role aktif
     */
    public function profile(): void
    {
        AuthMiddleware::requireAuth();
        $role = AuthMiddleware::role();
        match ($role) {
            'admin' => $this->redirect('admin/profile'),
            'guru'  => $this->redirect('teacher/profile'),
            'siswa' => $this->redirect('student/profile'),
            default => $this->redirect('login'),
        };
    }
}
