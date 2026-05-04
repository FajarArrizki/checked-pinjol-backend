<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Checked Pinjol Backend API',
    description: 'OpenAPI documentation for the Checked Pinjol backend.'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local development server'
)]
#[OA\Tag(name: 'Health', description: 'Health check endpoints')]
final class OpenApiSpec
{
}
