<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use App\Core\Auth\JWT;

class SuperAdminMiddleware
{
    public function __construct(private DatabaseManager $db) {}

    public function handle(Request $request, Response $response): bool|Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return Response::error('Otorisasi diperlukan.', 401);
        }

        $payload = JWT::decode($token);

        if (!$payload || !isset($payload['id'], $payload['role'])) {
            return Response::error('Sesi tidak valid.', 401);
        }

        if ($payload['role'] !== 'superadmin') {
            return Response::error('Akses terbatas. Anda memerlukan hak akses Super Admin.', 403);
        }

        // 3. Validasi ke Database
        $admin = $this->db->fetchOne(
            "SELECT id_admin, nama, role FROM `admin` WHERE `id_admin` = ? AND `role` = 'superadmin'", 
            [$payload['id']]
        );

        if (!$admin) {
            return Response::error('Akun Super Admin tidak ditemukan.', 403);
        }

        // 4. Set data user ke request
        $request->setUser([
            'id'    => $admin['id_admin'],
            'type'  => 'admin',
            'role'  => 'superadmin',
            'nama'  => $admin['nama']
        ]);

        return true;
    }
}
