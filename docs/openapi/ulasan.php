<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/ulasan',
    tags: ['Ulasan'],
    summary: 'Daftar ulasan',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Daftar ulasan berhasil diambil',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Daftar ulasan berhasil diambil'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                ]
            )
        )
    ]
)]
#[OA\Post(
    path: '/api/ulasan',
    tags: ['Ulasan'],
    summary: 'Kirim ulasan baru',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'rating', type: 'integer', example: 5),
                new OA\Property(property: 'komentar', type: 'string', example: 'Aplikasi cukup membantu'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Ulasan berhasil dikirim',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Ulasan berhasil dikirim'),
                    new OA\Property(property: 'data', type: 'object')
                ]
            )
        )
    ]
)]
final class UlasanEndpoints
{
}
