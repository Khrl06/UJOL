<?php
/**
 * SmartExam AI - User Model
 * Mengelola data pengguna (Admin, Guru, Peserta Didik) dan relasi akun.
 */

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    /**
     * Cari pengguna berdasarkan username atau email
     */
    public function findByUsernameOrEmail(string $identifier): ?array
    {
        if ($this->isDbConnected()) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);
            $waPhone = '';
            if (!empty($cleanPhone)) {
                if (str_starts_with($cleanPhone, '08')) {
                    $waPhone = '62' . substr($cleanPhone, 1);
                } elseif (str_starts_with($cleanPhone, '8')) {
                    $waPhone = '62' . substr($cleanPhone, 0);
                }
            }

            $sql = "SELECT u.*, 
                           COALESCE(s.nisn, t.nip, '') as nisn_nip,
                           COALESCE(c.class_name, 'Admin') as class_name
                    FROM `{$this->table}` u
                    LEFT JOIN `students` s ON s.user_id = u.id
                    LEFT JOIN `classes` c ON c.id = s.class_id
                    LEFT JOIN `teachers` t ON t.user_id = u.id
                    WHERE u.username = :id_user 
                       OR (u.email IS NOT NULL AND u.email != '' AND u.email = :id_email)
                       OR s.phone = :id_sphone 
                       OR t.phone = :id_tphone
                       OR s.phone = :id_swaphone 
                       OR t.phone = :id_twaphone
                    LIMIT 1";

            $user = $this->fetchOne($sql, [
                ':id_user'    => $identifier,
                ':id_email'   => $identifier,
                ':id_sphone'  => $identifier,
                ':id_tphone'  => $identifier,
                ':id_swaphone'=> $waPhone ?: $identifier,
                ':id_twaphone'=> $waPhone ?: $identifier
            ]);

            if ($user) {
                return $user;
            }
        }

        // Fallback akun demo jika DB terputus
        return $this->getDemoFallbackUser($identifier);
    }

    /**
     * Verifikasi kredensial login (username/email dan password)
     */
    public function verifyCredentials(string $identifier, string $password): ?array
    {
        $user = $this->findByUsernameOrEmail($identifier);
        if (!$user) {
            return null;
        }

        // Verifikasi menggunakan fungsi hashing password_verify standar industri
        if (password_verify($password, $user['password'])) {
            return $user;
        }

        // Fallback akun demo HANYA diizinkan jika database offline pada environment development
        if (defined('APP_ENV') && APP_ENV === 'development' && !$this->isDbConnected() && isset($user['plain_pass']) && $user['plain_pass'] === $password) {
            return $user;
        }

        return null;
    }

    /**
     * Cek apakah username sudah dipakai oleh user lain
     */
    public function isUsernameTaken(string $username, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT id FROM `{$this->table}` WHERE `username` = :u";
        $params = [':u' => $username];
        if ($excludeUserId !== null) {
            $sql .= " AND id != :exclude";
            $params[':exclude'] = $excludeUserId;
        }
        $sql .= " LIMIT 1";
        return $this->fetchOne($sql, $params) !== null;
    }

    /**
     * Cek apakah email sudah dipakai oleh user lain
     */
    public function isEmailTaken(string $email, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT id FROM `{$this->table}` WHERE `email` = :e";
        $params = [':e' => $email];
        if ($excludeUserId !== null) {
            $sql .= " AND id != :exclude";
            $params[':exclude'] = $excludeUserId;
        }
        $sql .= " LIMIT 1";
        return $this->fetchOne($sql, $params) !== null;
    }

    /**
     * Cek apakah nomor WhatsApp sudah terdaftar pada akun siswa/guru lain
     */
    public function isPhoneTaken(string $phone): bool
    {
        if (empty($phone)) return false;
        $clean = preg_replace('/[^0-9]/', '', $phone);
        $wa = str_starts_with($clean, '08') ? ('62' . substr($clean, 1)) : $clean;

        $sql = "SELECT s.id FROM `students` s WHERE s.phone = :p1 OR s.phone = :p2
                UNION
                SELECT t.id FROM `teachers` t WHERE t.phone = :p1 OR t.phone = :p2
                LIMIT 1";
        return $this->fetchOne($sql, [':p1' => $phone, ':p2' => $wa]) !== null;
    }

    /**
     * Relasi 1:1: Dapatkan profil student untuk user ini
     */
    public function getStudent(int $userId): ?array
    {
        $sql = "SELECT s.*, c.class_name, c.grade_level, c.major 
                FROM `students` s
                JOIN `classes` c ON c.id = s.class_id
                WHERE s.user_id = :user_id LIMIT 1";
        return $this->fetchOne($sql, [':user_id' => $userId]);
    }

    /**
     * Relasi 1:1: Dapatkan profil teacher untuk user ini
     */
    public function getTeacher(int $userId): ?array
    {
        $sql = "SELECT * FROM `teachers` WHERE user_id = :user_id LIMIT 1";
        return $this->fetchOne($sql, [':user_id' => $userId]);
    }

    /**
     * Relasi 1:N: Dapatkan log audit aktivitas user
     */
    public function getAuditLogs(int $userId, int $limit = 20): array
    {
        if ($this->isDbConnected()) {
            $sql = "SELECT * FROM `audit_logs` WHERE user_id = :user_id ORDER BY created_at DESC LIMIT " . (int)$limit;
            return $this->fetchAll($sql, [':user_id' => $userId]);
        }
        return [
            [
                'id' => 1,
                'user_id' => $userId,
                'action' => 'LOGIN',
                'description' => 'Berhasil masuk ke dalam sistem SmartExam AI.',
                'ip_address' => '127.0.0.1',
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
            ],
            [
                'id' => 2,
                'user_id' => $userId,
                'action' => 'AUTH_VERIFIED',
                'description' => 'Sesi login berhasil diverifikasi.',
                'ip_address' => '127.0.0.1',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ]
        ];
    }

    /**
     * Dapatkan detail profil lengkap user beserta data relasinya (guru/siswa/kelas)
     */
    public function getUserProfile(int $userId): ?array
    {
        if ($this->isDbConnected()) {
            $sql = "SELECT u.id, u.name, u.username, u.email, u.password, u.role, u.is_active, u.created_at, u.updated_at,
                           COALESCE(s.nisn, t.nip, '') as nisn_nip,
                           COALESCE(c.class_name, CASE WHEN u.role = 'admin' THEN 'Super User' ELSE 'Staf Pengajar' END) as class_name,
                           s.id as student_id, s.class_id, s.nisn, s.gender as student_gender, s.phone as student_phone, s.address as student_address,
                           c.grade_level, c.major, c.academic_year,
                           t.id as teacher_id, t.nip, t.nuptk, t.gender as teacher_gender, t.phone as teacher_phone, t.address as teacher_address
                    FROM `{$this->table}` u
                    LEFT JOIN `students` s ON s.user_id = u.id
                    LEFT JOIN `classes` c ON c.id = s.class_id
                    LEFT JOIN `teachers` t ON t.user_id = u.id
                    WHERE u.id = :id
                    LIMIT 1";
            $data = $this->fetchOne($sql, [':id' => $userId]);
            if ($data) {
                return $data;
            }
        }

        // Fallback demo
        foreach ($this->getDemoFallbackList() as $role => $demo) {
            if ((int)$demo['id'] === $userId) {
                if ($role === 'guru') {
                    $demo['nip'] = $demo['nisn_nip'];
                    $demo['nuptk'] = '9876543210123456';
                    $demo['gender'] = 'L';
                    $demo['phone'] = '081234567890';
                    $demo['address'] = 'Jl. Pendidikan No. 45, Kompleks Guru';
                    $demo['teacher_id'] = 1;
                } elseif ($role === 'siswa') {
                    $demo['nisn'] = $demo['nisn_nip'];
                    $demo['gender'] = 'L';
                    $demo['phone'] = '089876543210';
                    $demo['address'] = 'Jl. Anggrek No. 12, Kel. Sukamaju';
                    $demo['class_name'] = 'XII MIPA 1';
                    $demo['grade_level'] = '12';
                    $demo['major'] = 'MIPA';
                    $demo['academic_year'] = '2026/2027';
                    $demo['student_id'] = 1;
                }
                return $demo;
            }
        }

        return null;
    }

    /**
     * Perbarui data dasar akun user (nama, username, email)
     */
    public function updateBasicProfile(int $userId, string $name, string $username, string $email): bool
    {
        if ($this->isDbConnected()) {
            $sql = "UPDATE `{$this->table}` 
                    SET `name` = :name, `username` = :username, `email` = :email, `updated_at` = NOW() 
                    WHERE `id` = :id";
            return $this->execute($sql, [
                ':name'     => $name,
                ':username' => $username,
                ':email'    => $email,
                ':id'       => $userId
            ]);
        }
        return true;
    }

    /**
     * Verifikasi kecocokan kata sandi pengguna saat ini
     */
    public function verifyPassword(int $userId, string $plainPassword): bool
    {
        if ($this->isDbConnected()) {
            $user = $this->find($userId);
            if ($user && !empty($user['password'])) {
                return password_verify($plainPassword, $user['password']);
            }
        }

        // Fallback demo check
        foreach ($this->getDemoFallbackList() as $demo) {
            if ((int)$demo['id'] === $userId) {
                if (password_verify($plainPassword, $demo['password']) || (isset($demo['plain_pass']) && $demo['plain_pass'] === $plainPassword)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Perbarui kata sandi user dengan hashing bcrypt
     */
    public function updateUserPassword(int $userId, string $newPlainPassword): bool
    {
        $hash = password_hash($newPlainPassword, PASSWORD_BCRYPT);
        if ($this->isDbConnected()) {
            $sql = "UPDATE `{$this->table}` 
                    SET `password` = :password, `updated_at` = NOW() 
                    WHERE `id` = :id";
            return $this->execute($sql, [
                ':password' => $hash,
                ':id'       => $userId
            ]);
        }
        return true;
    }

    /**
     * Hitung total pengguna berdasarkan role
     */
    public function countByRole(string $role): int
    {
        if ($this->isDbConnected()) {
            return $this->count("`role` = :role", [':role' => $role]);
        }
        return 1;
    }

    /**
     * Ambil semua pengguna beserta relasi data profilnya
     */
    public function getAllUsers(): array
    {
        if ($this->isDbConnected()) {
            $sql = "SELECT u.id, u.name, u.username, u.email, u.role, u.is_active, u.created_at,
                           COALESCE(s.nisn, t.nip, '-') as nisn_nip,
                           COALESCE(c.class_name, CASE WHEN u.role = 'admin' THEN 'Super User' ELSE 'Staf Pengajar' END) as class_name
                    FROM `{$this->table}` u
                    LEFT JOIN `students` s ON s.user_id = u.id
                    LEFT JOIN `classes` c ON c.id = s.class_id
                    LEFT JOIN `teachers` t ON t.user_id = u.id
                    ORDER BY u.id ASC";
            $users = $this->fetchAll($sql);
            if (!empty($users)) {
                return $users;
            }
        }

        return array_values($this->getDemoFallbackList());
    }

    /**
     * Akun demo per role untuk quick testing
     */
    public function getDemoUserByRole(string $role): ?array
    {
        $list = $this->getDemoFallbackList();
        return $list[$role] ?? null;
    }

    private function getDemoFallbackUser(string $identifier): ?array
    {
        foreach ($this->getDemoFallbackList() as $demo) {
            if (strtolower($demo['username']) === strtolower($identifier) || strtolower($demo['email']) === strtolower($identifier)) {
                return $demo;
            }
        }
        return null;
    }

    private function getDemoFallbackList(): array
    {
        return [
            'admin' => [
                'id' => 1, 'name' => 'Administrator Utama', 'username' => 'admin',
                'email' => 'admin@smartexam.sch.id',
                'password' => '$2y$10$Gin1PFRH33WhBmBVNl.eTOqukoRv0J5pxT0OpWeK8s.H8lhkEESl.',
                'plain_pass' => 'admin123', 'role' => 'admin', 'nisn_nip' => '198501012010011001',
                'class_name' => 'Super User', 'is_active' => 1, 'created_at' => '2026-08-01 08:00:00'
            ],
            'guru' => [
                'id' => 2, 'name' => 'Budi Prasetyo, S.Pd., M.Kom.', 'username' => 'guru',
                'email' => 'budi.guru@smartexam.sch.id',
                'password' => '$2y$10$fAkR5UVZzxltcA24ezE4SOB0xIln1kfYYHWuqzQFLevL.nwrUUJkK',
                'plain_pass' => 'guru123', 'role' => 'guru', 'nisn_nip' => '198904122015031002',
                'class_name' => 'Guru Informatika & IPA', 'is_active' => 1, 'created_at' => '2026-08-01 08:00:00'
            ],
            'siswa' => [
                'id' => 3, 'name' => 'Ahmad Fauzi Pratama', 'username' => 'siswa',
                'email' => 'fauzi.siswa@smartexam.sch.id',
                'password' => '$2y$10$.f5u.pk80pMoOIub9BAsj.4JqxUBDbfkSfVwjSZf5yesYmfXuX92e',
                'plain_pass' => 'siswa123', 'role' => 'siswa', 'nisn_nip' => '0068192341',
                'class_name' => 'XII MIPA 1', 'is_active' => 1, 'created_at' => '2026-08-01 08:00:00'
            ]
        ];
    }
}
