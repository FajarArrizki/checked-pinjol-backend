<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Database\DatabaseManager;
use App\Core\Http\Request;
use App\Core\Http\Response;

class OptionalAuthMiddleware
{
    public function __construct(private DatabaseManager $db)
    {
    }

    public function handle(Request $request, Response $response): bool|Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return true;
        }

        $payload = json_decode(base64_decode($token), true);

        if (! is_array($payload) || ! isset($payload['id'], $payload['type'])) {
            return true;
        }

        $table = $payload['type'] === 'admin' ? 'admin' : 'users';
        $primaryKey = $payload['type'] === 'admin' ? 'id_admin' : 'id_user';

        $user = $this->db->fetchOne(
            "SELECT * FROM `{$table}` WHERE `{$primaryKey}` = ?",
            [$payload['id']]
        );

        if (! $user) {
            return true;
        }

        $request->setUser([
            'id' => $user[$primaryKey],
            'type' => $payload['type'],
            'nama' => $user['nama'] ?? 'User',
        ]);

        return true;
    }
}
