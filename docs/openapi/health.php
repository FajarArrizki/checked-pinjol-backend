<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/health',
    tags: ['Health'],
    summary: 'Health check endpoint',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Backend is running',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Checked Pinjol backend is running.'),
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'app', type: 'string', example: 'checked-pinjol-backend'),
                            new OA\Property(property: 'env', type: 'string', example: 'local'),
                            new OA\Property(property: 'time', type: 'string', format: 'date-time', example: '2026-05-04T22:00:00+07:00'),
                        ],
                        type: 'object'
                    ),
                ],
                type: 'object'
            )
        ),
    ]
)]
final class HealthEndpoint
{
}
