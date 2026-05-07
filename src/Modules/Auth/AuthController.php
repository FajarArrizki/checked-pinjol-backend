<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Core\Database\DatabaseManager;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Support\JWT;
use PDO;

final class AuthController
{
    private PDO $db;

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->db = $databaseManager->connection();
    }

    public function register(Request $request): Response
    {
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($name === '' || $email === '' || $password === '') {
            return Response::error('Name, email, dan password wajib diisi', 422);
        }

        $check = $this->db->prepare('SELECT id_user FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);

        if ($check->fetch()) {
            return Response::error('Email sudah terdaftar', 409);
        }

        $statement = $this->db->prepare(
            'INSERT INTO users (nama, email, password) VALUES (?, ?, ?)'
        );
        $statement->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
        ]);

        return Response::created([
            'id' => (int) $this->db->lastInsertId(),
            'name' => $name,
            'email' => $email,
        ], 'Registrasi berhasil');
    }

    public function login(Request $request): Response
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return Response::error('Email dan password wajib diisi', 422);
        }

        $statement = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $statement->execute([$email]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (! $user || ! isset($user['password']) || ! password_verify($password, (string) $user['password'])) {
            return Response::error('Email atau password salah', 401);
        }

        $token = JWT::encode([
            'id' => (int) $user['id_user'],
            'type' => 'user',
        ]);

        return Response::success([
            'token' => $token,
            'type' => 'user',
        ], 'Login berhasil');
    }

    public function adminLogin(Request $request): Response
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return Response::error('Email dan password wajib diisi', 422);
        }

        $statement = $this->db->prepare('SELECT * FROM admin WHERE email = ? LIMIT 1');
        $statement->execute([$email]);
        $admin = $statement->fetch(PDO::FETCH_ASSOC);

        if (! $admin || ! isset($admin['password']) || ! password_verify($password, (string) $admin['password'])) {
            return Response::error('Email atau password admin salah', 401);
        }

        $token = JWT::encode([
            'id' => (int) $admin['id_admin'],
            'type' => 'admin',
        ]);

        return Response::success([
            'token' => $token,
            'type' => 'admin',
        ], 'Login admin berhasil');
    }

    public function me(Request $request): Response
    {
        $user = $request->user();

        if (! $user) {
            return Response::error('User belum login', 401);
        }

        return Response::success($user, 'Profil berhasil diambil');
    }

    public function changePassword(Request $request): Response
    {
        $user = $request->user();

        if (! $user) {
            return Response::error('User belum login', 401);
        }

        $currentPassword = (string) $request->input('current_password', '');
        $newPassword = (string) $request->input('new_password', '');

        if ($currentPassword === '' || $newPassword === '') {
            return Response::error('Current password dan new password wajib diisi', 422);
        }

        if (($user['type'] ?? 'user') === 'admin') {
            $statement = $this->db->prepare('SELECT * FROM admin WHERE id_admin = ? LIMIT 1');
            $statement->execute([(int) $user['id']]);
            $account = $statement->fetch(PDO::FETCH_ASSOC);
            $table = 'admin';
            $primaryKey = 'id_admin';
        } else {
            $statement = $this->db->prepare('SELECT * FROM users WHERE id_user = ? LIMIT 1');
            $statement->execute([(int) $user['id']]);
            $account = $statement->fetch(PDO::FETCH_ASSOC);
            $table = 'users';
            $primaryKey = 'id_user';
        }

        if (! $account || ! isset($account['password']) || ! password_verify($currentPassword, (string) $account['password'])) {
            return Response::error('Current password tidak sesuai', 401);
        }

        $update = $this->db->prepare("UPDATE {$table} SET password = ? WHERE {$primaryKey} = ?");
        $update->execute([
            password_hash($newPassword, PASSWORD_BCRYPT),
            (int) $user['id'],
        ]);

        return Response::success(null, 'Password berhasil diubah');
    }
}
