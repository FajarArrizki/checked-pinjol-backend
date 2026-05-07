<?php

declare(strict_types=1);

namespace App\Modules\Health\Controllers;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use App\Core\Config\ConfigRepository;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Sistem', description: 'Monitoring kesehatan sistem dan status layanan')]
final class HealthController
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly ConfigRepository $config
    ) {}

    #[OA\Get(
        path: '/health',
        summary: 'Cek status kesehatan sistem',
        description: 'Memeriksa apakah API dan Database berjalan dengan normal.',
        tags: ['Sistem'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sistem berjalan normal',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'System is healthy'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'app', type: 'string'),
                                new OA\Property(property: 'env', type: 'string'),
                                new OA\Property(property: 'version', type: 'string'),
                                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                                new OA\Property(
                                    property: 'services',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'database', type: 'string', example: 'ok'),
                                        new OA\Property(property: 'api', type: 'string', example: 'ok')
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 503,
                description: 'Layanan tidak tersedia (Database error)'
            )
        ]
    )]
    public function check(Request $request): Response
    {
        $dbStatus = 'ok';
        $isHealthy = true;

        try {
            // Cek koneksi database secara nyata
            $this->db->fetchOne('SELECT 1');
        } catch (\Throwable $e) {
            $dbStatus = 'error: ' . $e->getMessage();
            $isHealthy = false;
        }

        $statusCode = $isHealthy ? 200 : 503;

        return Response::json([
            'success' => $isHealthy,
            'message' => $isHealthy ? 'System is healthy' : 'System is experiencing issues',
            'data'    => [
                'app'       => $this->config->get('app.name', 'Checked Pinjol API'),
                'env'       => $this->config->get('app.env', 'production'),
                'version'   => '1.0.0',
                'timestamp' => date(DATE_ATOM),
                'services'  => [
                    'database' => $dbStatus,
                    'api'      => 'ok'
                ],
            ]
        ], $statusCode);
    }
}