<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;

class SuperAdminMiddleware
{
    public function __construct(private DatabaseManager $db) {}

    public function handle(Request $request, Response $response): bool|Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return Response::error('Otorisasi diperlukan.', 401);
        }

        // 1. Decode Token
        $payload = json_decode(base64_decode($token), true);

        if (!$payload || !isset($payload['id'], $payload['role'])) {
            return Response::error('Sesi tidak valid.', 401);
        }

        // 2. CEK ROLE: Harus 'super_admin'
        // Catatan: Pastikan di kolom database Anda terdapat kolom 'role' atau sejenisnya
        if ($payload['role'] !== 'super_admin') {
            return Response::error('Akses terbatas. Anda memerlukan hak akses Super Admin.', 403);
        }

        // 3. Validasi ke Database
        $admin = $this->db->fetchOne(
            "SELECT id_admin, nama, role FROM `admin` WHERE `id_admin` = ? AND `role` = 'super_admin'", 
            [$payload['id']]
        );

        if (!$admin) {
            return Response::error('Akun Super Admin tidak ditemukan.', 403);
        }

        // 4. Set data user ke request
        $request->setUser([
            'id'    => $admin['id_admin'],
            'type'  => 'admin',
            'role'  => 'super_admin',
            'nama'  => $admin['nama']
        ]);

        return true;
    }
}