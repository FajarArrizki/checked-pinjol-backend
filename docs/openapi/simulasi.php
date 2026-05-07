<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/simulasi',
    tags: ['Simulasi'],
    summary: 'Hitung simulasi pinjaman',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'jumlah_pinjaman', type: 'number', example: 1000000),
                new OA\Property(property: 'tenor_hari', type: 'integer', example: 30),
                new OA\Property(property: 'bunga_harian', type: 'number', example: 0.8),
            ]
        )
    ),
    responses: [new OA\Response(response: 200, description: 'Simulasi berhasil dihitung', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'success', type: 'boolean', example: true), new OA\Property(property: 'message', type: 'string', example: 'Simulasi berhasil dihitung'), new OA\Property(property: 'data', type: 'object')]))]
)]
#[OA\Get(path: '/api/simulasi/riwayat', tags: ['Simulasi'], summary: 'Riwayat simulasi user', security: [['BearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Riwayat simulasi berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'success', type: 'boolean', example: true), new OA\Property(property: 'message', type: 'string', example: 'Riwayat simulasi berhasil diambil'), new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))]))])]
#[OA\Delete(
    path: '/api/simulasi/{id}',
    tags: ['Simulasi'],
    summary: 'Hapus riwayat simulasi',
    security: [['BearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Riwayat simulasi berhasil dihapus', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'success', type: 'boolean', example: true), new OA\Property(property: 'message', type: 'string', example: 'Riwayat simulasi berhasil dihapus')]))]
)]
final class SimulasiEndpoints
{
}
