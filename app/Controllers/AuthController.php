<?php
/**
 * SmartExam AI - Authentication Controller
 * Menangani otentikasi login, validasi form, remember me, quick demo switcher, dan logout.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthMiddleware;
use App\Helpers\CsrfHelper;
use App\Helpers\SessionHelper;
use App\Helpers\SanitizeHelper;
use App\Models\AuditLogModel;
use App\Models\UserModel;

class AuthController extends Controller
{
    private UserModel $userModel;
    private AuditLogModel $auditModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->auditModel = new AuditLogModel();
    }

    /**
     * Halaman Login & Pemrosesan Otentikasi
     */
    public function login(): void
    {
        // Jika sudah login, langsung alihkan ke dashboard role masing-masing
        if (AuthMiddleware::isLoggedIn()) {
            AuthMiddleware::redirectToDashboard();
            return;
        }

        $errors = [];
        $oldUsername = '';

        // Tangani request POST form login
        if ($this->isPost()) {
            // 1. Validasi CSRF Token
            $csrfToken = $this->getPost('csrf_token', '');
            if (!CsrfHelper::validateToken($csrfToken)) {
                SessionHelper::setFlash('danger', 'Sesi formulir kedaluwarsa atau token tidak valid. Silakan coba kembali.');
                $this->redirect('login');
                return;
            }

            // 2. Ambil dan sanitasi input form
            $username = SanitizeHelper::clean($this->getPost('username', $this->getPost('email', '')));
            $password = (string)$this->getPost('password', '');
            $remember = !empty($this->getPost('remember_me'));
            $oldUsername = $username;

            // 3. Validasi Kelengkapan Field
            if (empty($username)) {
                $errors['username'] = 'Alamat email atau username wajib diisi.';
            }
            if (empty($password)) {
                $errors['password'] = 'Kata sandi wajib diisi.';
            }

            if (empty($errors)) {
                // 4. Verifikasi kredensial ke database / model
                $user = $this->userModel->verifyCredentials($username, $password);

                if ($user) {
                    // 5. Cek apakah status akun aktif
                    if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
                        $this->auditModel->logActivity($user['id'], 'LOGIN_BLOCKED', 'Percobaan login pada akun yang dinonaktifkan.');
                        SessionHelper::setFlash('danger', 'Akun Anda saat ini berstatus <strong>Nonaktif</strong>. Silakan hubungi Administrator sekolah.');
                        $this->redirect('login');
                        return;
                    }

                    // 6. Login berhasil: simpan ke sesi dengan session regeneration & remember me
                    AuthMiddleware::login($user, $remember);

                    // Catat jejak audit
                    $this->auditModel->logActivity($user['id'], 'LOGIN_SUCCESS', 'Pengguna berhasil masuk ke sistem melalui formulir otentikasi.');

                    SessionHelper::setFlash('success', 'Selamat datang kembali, <strong>' . e($user['name']) . '</strong>!');
                    AuthMiddleware::redirectToDashboard();
                    return;
                } else {
                    // Kredensial tidak valid
                    $this->auditModel->logActivity(null, 'LOGIN_FAILED', 'Percobaan login gagal dengan identitas: ' . $username);
                    SessionHelper::setFlash('danger', 'Email/username atau kata sandi yang Anda masukkan salah.');
                }
            } else {
                SessionHelper::setFlash('warning', 'Mohon lengkapi semua kolom yang wajib diisi.');
            }
        }

        // Tampilkan view login
        $this->view('auth/login', [
            'pageTitle'     => 'Masuk ke Sistem - ' . APP_NAME,
            'isDbConnected' => $this->userModel->isDbConnected(),
            'errors'        => $errors,
            'oldUsername'   => $oldUsername,
        ], 'auth');
    }

    /**
     * Halaman Pendaftaran Akun Baru & Pengiriman OTP ke Email
     */
    public function register(): void
    {
        if (AuthMiddleware::isLoggedIn()) {
            AuthMiddleware::redirectToDashboard();
            return;
        }

        $classModel = new \App\Models\ClassModel();
        $classes = $classModel->all('class_name ASC');

        $errors = [];
        $old = [
            'role'   => 'siswa',
            'gender' => 'L'
        ];

        if ($this->isPost()) {
            $csrfToken = $this->getPost('csrf_token', '');
            if (!CsrfHelper::validateToken($csrfToken)) {
                SessionHelper::setFlash('danger', 'Sesi formulir kedaluwarsa. Silakan ulangi pengisian.');
                $this->redirect('register');
                return;
            }

            $name     = trim(SanitizeHelper::clean($this->getPost('name', '')));
            $username = strtolower(trim(SanitizeHelper::clean($this->getPost('username', ''))));
            $phone    = trim(SanitizeHelper::clean($this->getPost('phone', '')));
            $role     = in_array($this->getPost('role'), ['siswa', 'guru']) ? $this->getPost('role') : 'siswa';
            $gender   = in_array($this->getPost('gender'), ['L', 'P']) ? $this->getPost('gender') : 'L';
            $classId  = (int)$this->getPost('class_id', 0);
            $nisn     = trim(SanitizeHelper::clean($this->getPost('nisn', '')));
            $nip      = trim(SanitizeHelper::clean($this->getPost('nip', '')));
            $password = (string)$this->getPost('password', '');
            $confirm  = (string)$this->getPost('password_confirm', '');

            $old = compact('name', 'username', 'phone', 'role', 'gender', 'classId', 'nisn', 'nip');

            // Validasi Input
            if (empty($name) || strlen($name) < 3) {
                $errors['name'] = 'Nama lengkap minimal 3 karakter.';
            }

            if (empty($username) || strlen($username) < 3) {
                $errors['username'] = 'Username minimal 3 karakter alfanumerik.';
            } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
                $errors['username'] = 'Username hanya boleh huruf, angka, titik, atau garis bawah.';
            } elseif ($this->userModel->isUsernameTaken($username)) {
                $errors['username'] = 'Username tersebut sudah terdaftar. Gunakan username lain.';
            }

            if (empty($phone)) {
                $errors['phone'] = 'Nomor WhatsApp wajib diisi untuk verifikasi kode OTP.';
            } elseif (!preg_match('/^[0-9+\-\s]{9,16}$/', $phone)) {
                $errors['phone'] = 'Nomor WhatsApp tidak valid (minimal 9 digit angka).';
            } elseif ($this->userModel->isPhoneTaken($phone)) {
                $errors['phone'] = 'Nomor WhatsApp tersebut sudah terdaftar di sistem. Gunakan nomor lain.';
            }

            if (empty($password) || strlen($password) < 6) {
                $errors['password'] = 'Kata sandi minimal 6 karakter.';
            } elseif ($password !== $confirm) {
                $errors['password_confirm'] = 'Konfirmasi kata sandi tidak cocok.';
            }

            // Validasi khusus Role Siswa
            if ($role === 'siswa') {
                if ($classId <= 0) {
                    $errors['class_id'] = 'Pilih kelas / rombel Anda.';
                }
                if (!empty($nisn)) {
                    $studentModel = new \App\Models\StudentModel();
                    if ($studentModel->findByNisn($nisn)) {
                        $errors['nisn'] = 'NISN tersebut sudah terdaftar di sistem.';
                    }
                }
            }

            // Validasi khusus Role Guru
            if ($role === 'guru') {
                if (!empty($nip)) {
                    $teacherModel = new \App\Models\TeacherModel();
                    if ($teacherModel->findByNip($nip)) {
                        $errors['nip'] = 'NIP tersebut sudah terdaftar di sistem.';
                    }
                }
            }

            // Jika validasi sukses, buat OTP dan kirim ke WhatsApp
            if (empty($errors)) {
                $registrationPayload = [
                    'name'       => $name,
                    'username'   => $username,
                    'email'      => null, // Akun dibuat via WhatsApp tanpa wajib email
                    'phone'      => $phone,
                    'role'       => $role,
                    'gender'     => $gender,
                    'class_id'   => $classId,
                    'nisn'       => $nisn,
                    'nip'        => $nip,
                    'password'   => $password, // Akan di-hash saat verifikasi OTP berhasil
                    'is_active'  => 1
                ];

                $waOtpService = new \App\Helpers\WhatsAppOtpService();
                $otpResult = $waOtpService->sendRegistrationOtp($phone, $name, $registrationPayload, 15);

                SessionHelper::set('pending_registration_phone', $phone);
                SessionHelper::setFlash('info', 'Kode verifikasi OTP 6-digit telah dikirimkan ke nomor WhatsApp <strong>' . e($phone) . '</strong>. Silakan masukkan kode untuk menyelesaikan pendaftaran.');

                $this->redirect('verify-otp');
                return;
            } else {
                SessionHelper::setFlash('warning', 'Mohon periksa dan lengkapi data formulir yang diperlukan.');
            }
        }

        $this->view('auth/register', [
            'pageTitle' => 'Pendaftaran Akun Baru - ' . APP_NAME,
            'classes'   => $classes,
            'errors'    => $errors,
            'old'       => $old
        ], 'auth');
    }

    /**
     * Halaman Verifikasi Kode OTP WhatsApp
     */
    public function verifyOtp(): void
    {
        if (AuthMiddleware::isLoggedIn()) {
            AuthMiddleware::redirectToDashboard();
            return;
        }

        $sessionData = SessionHelper::get('pending_registration_otp');
        $pendingPhone = SessionHelper::get('pending_registration_phone', $sessionData['display_phone'] ?? ($sessionData['phone'] ?? ''));

        if (empty($pendingPhone)) {
            SessionHelper::setFlash('warning', 'Tidak ada proses verifikasi pendaftaran yang aktif.');
            $this->redirect('register');
            return;
        }

        $error = '';

        if ($this->isPost()) {
            $csrfToken = $this->getPost('csrf_token', '');
            if (!CsrfHelper::validateToken($csrfToken)) {
                SessionHelper::setFlash('danger', 'Sesi verifikasi kedaluwarsa. Silakan ulangi.');
                $this->redirect('verify-otp');
                return;
            }

            $phone = trim($this->getPost('phone', $pendingPhone));
            $otpCode = trim($this->getPost('otp_code', ''));

            if (empty($otpCode) || strlen($otpCode) !== 6) {
                $error = 'Kode OTP harus berupa 6-digit angka.';
            } else {
                $waOtpService = new \App\Helpers\WhatsAppOtpService();
                $regData = $waOtpService->verifyRegistrationOtp($phone, $otpCode);

                if ($regData) {
                    // OTP Valid -> Buat akun di database secara permanen
                    $role = $regData['role'] ?? 'siswa';
                    $userData = [
                        'name'      => $regData['name'],
                        'username'  => $regData['username'],
                        'email'     => null,
                        'password'  => $regData['password'],
                        'is_active' => 1
                    ];

                    $createdId = 0;
                    if ($role === 'siswa') {
                        $studentModel = new \App\Models\StudentModel();
                        $studentData = [
                            'class_id' => $regData['class_id'],
                            'nisn'     => $regData['nisn'] ?: ('NISN' . time()),
                            'gender'   => $regData['gender'] ?? 'L',
                            'phone'    => $regData['phone'] ?: null,
                            'address'  => null
                        ];
                        $createdId = $studentModel->createWithUser($userData, $studentData);
                    } else {
                        $teacherModel = new \App\Models\TeacherModel();
                        $teacherData = [
                            'nip'     => $regData['nip'] ?: null,
                            'nuptk'   => null,
                            'gender'  => $regData['gender'] ?? 'L',
                            'phone'   => $regData['phone'] ?: null,
                            'address' => null
                        ];
                        $createdId = $teacherModel->createWithUser($userData, $teacherData);
                    }

                    if ($createdId > 0) {
                        // Catat Jejak Audit
                        $this->auditModel->logActivity(null, 'REGISTRATION_SUCCESS', "Pengguna {$regData['name']} ({$regData['phone']}) berhasil mendaftar sebagai {$role} melalui verifikasi OTP WhatsApp.");

                        // Kirim notifikasi ucapan selamat via WhatsApp
                        if (!empty($regData['phone'])) {
                            $welcomeWa = "🎉 *SELAMAT DATANG DI SMARTEXAM AI (UJOL)*\n\n"
                                       . "Halo *{$regData['name']}*,\n"
                                       . "Akun {$role} Anda telah berhasil diverifikasi dan diaktifkan via WhatsApp!\n\n"
                                       . "👤 Username: `{$regData['username']}`\n"
                                       . "📱 No. WhatsApp: {$regData['phone']}\n\n"
                                       . "Silakan login di: " . (defined('BASE_URL') ? BASE_URL : 'http://localhost/UJOL') . "/login\n\n"
                                       . "Selamat mengikuti ujian online dengan jujur dan berprestasi!";
                            \App\Helpers\WhatsAppGateway::sendMessage($regData['phone'], $welcomeWa);
                        }

                        SessionHelper::remove('pending_registration_phone');
                        SessionHelper::remove('pending_registration_otp');

                        SessionHelper::setFlash('success', 'Akun Anda berhasil diverifikasi via WhatsApp dan diaktifkan! Silakan masuk dengan kredensial baru Anda.');
                        $this->redirect('login');
                        return;
                    } else {
                        $error = 'Terjadi kendala saat menyimpan data akun ke sistem. Silakan hubungi admin.';
                    }
                } else {
                    $error = 'Kode OTP salah atau telah kedaluwarsa. Silakan periksa kembali pesan WhatsApp Anda atau klik Kirim Ulang.';
                }
            }
        }

        $this->view('auth/verify_otp', [
            'pageTitle' => 'Verifikasi OTP WhatsApp - ' . APP_NAME,
            'phone'     => $pendingPhone,
            'error'     => $error
        ], 'auth');
    }

    /**
     * Kirim Ulang Kode OTP ke WhatsApp
     */
    public function resendOtp(): void
    {
        if (!$this->isPost()) {
            $this->redirect('verify-otp');
            return;
        }

        $csrfToken = $this->getPost('csrf_token', '');
        if (!CsrfHelper::validateToken($csrfToken)) {
            SessionHelper::setFlash('danger', 'Sesi formulir kedaluwarsa.');
            $this->redirect('verify-otp');
            return;
        }

        $sessionData = SessionHelper::get('pending_registration_otp');
        $phone = trim($this->getPost('phone', $sessionData['display_phone'] ?? ($sessionData['phone'] ?? '')));

        if (!empty($phone) && !empty($sessionData['data'])) {
            $waOtpService = new \App\Helpers\WhatsAppOtpService();
            $waOtpService->sendRegistrationOtp($phone, $sessionData['data']['name'] ?? 'Pengguna', $sessionData['data'], 15);

            SessionHelper::setFlash('success', 'Kode OTP baru telah berhasil dikirimkan ulang ke nomor WhatsApp Anda.');
        } else {
            SessionHelper::setFlash('warning', 'Data pendaftaran tidak ditemukan. Silakan isi kembali formulir.');
            $this->redirect('register');
            return;
        }

        $this->redirect('verify-otp');
    }

    /**
     * Demo route dinonaktifkan - dialihkan ke halaman login biasa
     */
    public function demo(string $role = 'admin'): void
    {
        $this->redirect('login');
    }

    /**
     * Proses Logout Pengguna
     */
    public function logout(): void
    {
        AuthMiddleware::logout();
        SessionHelper::setFlash('success', 'Anda telah berhasil keluar dari sesi.');
        $this->redirect('login');
    }
}
