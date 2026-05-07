<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/regulasi', tags: ['Regulasi'], summary: 'Daftar regulasi', responses: [new OA\Response(response: 200, description: 'Daftar regulasi berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'success', type: 'boolean', example: true), new OA\Property(property: 'message', type: 'string', example: 'Daftar regulasi berhasil diambil'), new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))]))])]
#[OA\Get(
    path: '/api/regulasi/{id}',
    tags: ['Regulasi'],
    summary: 'Detail regulasi',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Detail regulasi berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'success', type: 'boolean', example: true), new OA\Property(property: 'message', type: 'string', example: 'Detail regulasi berhasil diambil'), new OA\Property(property: 'data', type: 'object')]))]
)]
final class RegulasiEndpoints
{
}
