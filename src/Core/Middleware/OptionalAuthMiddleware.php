<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;

/**
 * Middleware untuk autentikasi opsional.
 * Tidak akan menghentikan request jika token tidak ada/salah.
 */
class OptionalAuthMiddleware
{
    public function __construct(private DatabaseManager $db) {}

    public function handle(Request $request, Response $response): bool|Response
    {
        $token = $request->bearerToken();

        // Jika tidak ada token, biarkan berlanjut (Data user akan tetap NULL)
        if (!$token) {
            return true;
        }

        // Coba decode token
        $payload = json_decode(base64_decode($token), true);

        // Jika format token salah, abaikan saja dan lanjut
        if (!$payload || !isset($payload['id'], $payload['type'])) {
            return true;
        }

        // Cari identitas di DB sesuai tipe
        $table = ($payload['type'] === 'admin') ? 'admin' : 'users';
        $pk    = ($payload['type'] === 'admin') ? 'id_admin' : 'id_user';
        
        $user = $this->db->fetchOne("SELECT * FROM `{$table}` WHERE `{$pk}` = ?", [$payload['id']]);

        // Jika user ditemukan di DB, suntikkan ke request
        if ($user) {
            $request->setUser([
                'id'   => $user[$pk],
                'type' => $payload['type'],
                'nama' => $user['nama'] ?? 'User'
            ]);
        }

        // Selalu return true karena ini opsional
        return true;
    }
}