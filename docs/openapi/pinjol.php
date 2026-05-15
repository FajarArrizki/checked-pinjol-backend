<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/pinjol/cek',
    tags: ['Pinjol'],
    summary: 'Cek legalitas pinjol',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Hasil cek legalitas',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Hasil pengecekan berhasil diambil'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                ]
            )
        )
    ]
)]
#[OA\Get(
    path: '/api/pinjol',
    tags: ['Pinjol'],
    summary: 'Daftar pinjol',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Daftar pinjol berhasil diambil',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Daftar pinjol berhasil diambil'),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'nama', type: 'string', example: 'Pinjol Aman'),
                                new OA\Property(property: 'status', type: 'string', example: 'legal'),
                            ]
                        )
                    ),
                ]
            )
        )
    ]
)]
#[OA\Get(
    path: '/api/pinjol/{id}',
    tags: ['Pinjol'],
    summary: 'Detail pinjol',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Detail pinjol berhasil diambil',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Detail pinjol berhasil diambil'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nama', type: 'string', example: 'Pinjol Aman'),
                            new OA\Property(property: 'status', type: 'string', example: 'legal'),
                            new OA\Property(property: 'website', type: 'string', example: 'https://pinjolaman.id'),
                        ]
                    ),
                ]
            )
        )
    ]
)]
final class PinjolEndpoints
{
}
