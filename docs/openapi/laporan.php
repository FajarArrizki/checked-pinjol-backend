<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/laporan/kode/{kode}',
    tags: ['Laporan'],
    summary: 'Cek status laporan berdasarkan kode',
    parameters: [new OA\Parameter(name: 'kode', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Status laporan berhasil diambil',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Status laporan berhasil diambil'),
                    new OA\Property(property: 'data', type: 'object')
                ]
            )
        )
    ]
)]
#[OA\Post(
    path: '/api/laporan',
    tags: ['Laporan'],
    summary: 'Kirim laporan baru',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'judul', type: 'string', example: 'Penagihan tidak wajar'),
                new OA\Property(property: 'deskripsi', type: 'string', example: 'Saya menerima penagihan di luar jam wajar'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Laporan berhasil dikirim',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Laporan berhasil dikirim'),
                    new OA\Property(property: 'data', type: 'object')
                ]
            )
        )
    ]
)]
#[OA\Get(
    path: '/api/laporan',
    tags: ['Laporan'],
    summary: 'Daftar laporan user',
    security: [['BearerAuth' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Daftar laporan berhasil diambil',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Daftar laporan berhasil diambil'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                ]
            )
        )
    ]
)]
#[OA\Get(
    path: '/api/laporan/{id}',
    tags: ['Laporan'],
    summary: 'Detail laporan user',
    security: [['BearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Detail laporan berhasil diambil',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Detail laporan berhasil diambil'),
                    new OA\Property(property: 'data', type: 'object')
                ]
            )
        )
    ]
)]
final class LaporanEndpoints
{
}
