<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Database\DatabaseManager;
use App\Core\Auth\JWT;
use OpenApi\Attributes as OA;

#[OA\Info(title: "Auth API", version: "1.0.0")]
class AuthController
{
    public function __construct(private DatabaseManager $db)
    {
    }

    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Registrasi user baru',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama', 'email', 'no_hp', 'password'],
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                    new OA\Property(property: 'no_hp', type: 'string', example: '08123456789'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Registrasi berhasil'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
            new OA\Response(response: 409, description: 'Email sudah terdaftar')
        ]
    )]
    public function register(Request $request): Response
    {
        $errors = $request->validate([
            'nama'     => 'required|min:3|max:255',
            'email'    => 'required|email',
            'no_hp'    => 'required|min:9|max:20',
            'password' => 'required|min:6',
        ]);

        if (!empty($errors)) {
            return Response::error($errors[0], 422);
        }

        if ($this->db->fetchOne('SELECT id_user FROM user WHERE email = ?', [$request->input('email')])) {
            return Response::error('Email sudah terdaftar', 409);
        }

        $id = $this->db->insert('user', [
            'nama'          => sanitize($request->input('nama')),
            'email'         => $request->input('email'),
            'no_hp'         => $request->input('no_hp'),
            'password_hash' => bcryptHash($request->input('password')),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $user = $this->db->fetchOne(
            'SELECT id_user, nama, email, no_hp, created_at FROM user WHERE id_user = ?', 
            [$id]
        );

        return Response::created($user, 'Registrasi berhasil');
    }

    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Login user untuk mendapatkan token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'rahasia123')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login Berhasil'),
            new OA\Response(response: 401, description: 'Email atau password salah')
        ]
    )]
    public function login(Request $request): Response
    {
        $user = $this->db->fetchOne('SELECT * FROM user WHERE email = ?', [$request->input('email')]);

        if (!$user || !bcryptVerify($request->input('password'), (string) $user['password_hash'])) {
            return Response::error('Email atau password salah', 401);
        }

        $token = JWT::encode([
            'id'    => $user['id_user'],
            'email' => $user['email'],
            'nama'  => $user['nama'],
            'type'  => 'user',
            'role'  => 'user',
        ]);

        unset($user['password_hash']);
        return Response::success(['token' => $token, 'user' => $user], 'Login berhasil');
    }

    #[OA\Post(
        path: '/api/auth/admin-login',
        summary: 'Login khusus admin',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'admin_ganteng'),
                    new OA\Property(property: 'password', type: 'string', example: 'admin123')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login admin berhasil'),
            new OA\Response(response: 401, description: 'Username atau password salah')
        ]
    )]
    public function adminLogin(Request $request): Response
    {
        $uname = $request->input('username');
        $admin = $this->db->fetchOne(
            'SELECT * FROM admin WHERE (username = ? OR email = ?) AND is_active = 1',
            [$uname, $uname]
        );

        if (!$admin || !bcryptVerify($request->input('password'), (string) $admin['password_hash'])) {
            return Response::error('Username atau password salah', 401);
        }

        $token = JWT::encode([
            'id'    => $admin['id_admin'],
            'email' => $admin['email'],
            'nama'  => $admin['nama'],
            'role'  => $admin['role'],
            'type'  => 'admin',
        ]);

        unset($admin['password_hash']);
        return Response::success(['token' => $token, 'user' => $admin, 'type' => 'admin'], 'Login admin berhasil');
    }

    #[OA\Get(
        path: '/api/auth/me',
        summary: 'Mendapatkan profil pengguna saat ini',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Data profil berhasil diambil'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'User tidak ditemukan')
        ]
    )]
    public function me(Request $request): Response
    {
        $auth = $request->user(); 
        
        if (!$auth) return Response::error('Unauthorized', 401);

        $table = ($auth['type'] === 'admin') ? 'admin' : 'user';
        $pk    = ($auth['type'] === 'admin') ? 'id_admin' : 'id_user';
        $fields = ($auth['type'] === 'admin') 
            ? 'id_admin, nama, email, username, role, no_hp' 
            : 'id_user, nama, email, no_hp, created_at';

        $data = $this->db->fetchOne("SELECT $fields FROM $table WHERE $pk = ?", [$auth['id']]);

        return $data ? Response::success($data) : Response::notFound('User tidak ditemukan');
    }

    #[OA\Put(
        path: '/api/auth/change-password',
        summary: 'Mengubah password user/admin',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password_lama', 'password_baru'],
                properties: [
                    new OA\Property(property: 'password_lama', type: 'string'),
                    new OA\Property(property: 'password_baru', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Password berhasil diubah'),
            new OA\Response(response: 400, description: 'Password lama tidak cocok'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function changePassword(Request $request): Response
    {
        $auth = $request->user();
        if (!$auth) return Response::error('Unauthorized', 401);

        $isAdmin = $auth['type'] === 'admin';
        $table = $isAdmin ? 'admin' : 'user';
        $pk    = $isAdmin ? 'id_admin' : 'id_user';

        $record = $this->db->fetchOne("SELECT password_hash FROM $table WHERE $pk = ?", [$auth['id']]);

        if (!bcryptVerify($request->input('password_lama'), (string) $record['password_hash'])) {
            return Response::error('Password lama tidak cocok', 400);
        }

        $this->db->update($table, [
            'password_hash' => bcryptHash($request->input('password_baru')),
            'updated_at'    => date('Y-m-d H:i:s'),
        ], "$pk = ?", [$auth['id']]);

        return Response::success(null, 'Password berhasil diubah');
    }
}