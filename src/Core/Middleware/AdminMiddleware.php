<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use App\Core\Auth\JWT;

/**
 * Middleware khusus untuk membatasi akses hanya bagi Administrator.
 */
class AdminMiddleware
{
    public function __construct(private DatabaseManager $db) {}

    /**
     * Menangani validasi token dan pengecekan role admin.
     */
    public function handle(Request $request, Response $response): bool|Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return Response::error('Token tidak ditemukan. Silakan login sebagai admin.', 401);
        }

        $payload = JWT::decode($token);

        if (!$payload || !isset($payload['id'], $payload['type'])) {
            return Response::error('Sesi tidak valid. Silakan login ulang.', 401);
        }

        // 2. CEK ROLE: Harus Admin
        if ($payload['type'] !== 'admin') {
            return Response::error('Akses ditolak. Area ini hanya untuk Administrator.', 403);
        }

        // 3. Validasi ke Database (Memastikan akun admin masih aktif/ada)
        $admin = $this->db->fetchOne(
            "SELECT id_admin, nama, email FROM `admin` WHERE `id_admin` = ?", 
            [$payload['id']]
        );

        if (!$admin) {
            return Response::error('Identitas Admin tidak ditemukan atau akun telah dinonaktifkan.', 401);
        }

        // 4. Suntikkan data admin ke Request agar bisa dipakai di Controller
        $request->setUser([
            'id'    => $admin['id_admin'],
            'type'  => 'admin',
            'nama'  => $admin['nama'],
            'email' => $admin['email']
        ]);

        return true; // Lolos ke Controller
    }
}
