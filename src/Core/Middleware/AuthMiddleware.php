<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;

class AuthMiddleware
{
    public function __construct(private DatabaseManager $db) {}

    public function handle(Request $request, Response $response): bool|Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return Response::error('Token tidak ditemukan', 401);
        }

        // Contoh validasi sederhana (Gantilah dengan JWT atau pengecekan DB yang kuat)
        // Di sini kita asumsikan payload ada di dalam token base64
        $payload = json_decode(base64_decode($token), true);

        if (!$payload || !isset($payload['id'], $payload['type'])) {
            return Response::error('Token tidak valid', 401);
        }

        // Cari identitas di DB
        $table = ($payload['type'] === 'admin') ? 'admin' : 'users';
        $pk    = ($payload['type'] === 'admin') ? 'id_admin' : 'id_user';
        $user  = $this->db->fetchOne("SELECT * FROM `{$table}` WHERE `{$pk}` = ?", [$payload['id']]);

        if (!$user) {
            return Response::error('User tidak ditemukan', 401);
        }

        // SUNTIKKAN DATA USER KE REQUEST
        // Menggunakan method setUser() yang kita tambahkan di Request.php sebelumnya
        $request->setUser([
            'id'   => $user[$pk],
            'type' => $payload['type'],
            'nama' => $user['nama'] ?? 'User'
        ]);

        return true; // Lolos verifikasi
    }
}