<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/artikel/kategori', tags: ['Artikel'], summary: 'Daftar kategori artikel', responses: [new OA\Response(response: 200, description: 'Kategori artikel berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'success', type: 'boolean', example: true), new OA\Property(property: 'message', type: 'string', example: 'Kategori artikel berhasil diambil'), new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string', example: 'Edukasi'))]))])]
#[OA\Get(path: '/api/artikel', tags: ['Artikel'], summary: 'Daftar artikel', responses: [new OA\Response(response: 200, description: 'Daftar artikel berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'success', type: 'boolean', example: true), new OA\Property(property: 'message', type: 'string', example: 'Daftar artikel berhasil diambil'), new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))]))])]
#[OA\Get(
    path: '/api/artikel/{id}',
    tags: ['Artikel'],
    summary: 'Detail artikel',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Detail artikel berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [new OA\Property(property: 'success', type: 'boolean', example: true), new OA\Property(property: 'message', type: 'string', example: 'Detail artikel berhasil diambil'), new OA\Property(property: 'data', type: 'object')]))]
)]
final class ArtikelEndpoints
{
}
