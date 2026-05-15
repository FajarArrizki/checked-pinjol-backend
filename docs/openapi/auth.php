<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/auth/register',
    tags: ['Auth'],
    summary: 'Registrasi user baru',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Budi'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'secret123'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'User berhasil diregistrasi',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Registrasi berhasil'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 12),
                            new OA\Property(property: 'name', type: 'string', example: 'Budi'),
                            new OA\Property(property: 'email', type: 'string', example: 'budi@example.com'),
                        ]
                    ),
                ]
            )
        )
    ]
)]
#[OA\Post(
    path: '/api/auth/login',
    tags: ['Auth'],
    summary: 'Login user',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'secret123'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Login berhasil',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Login berhasil'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJI...'),
                            new OA\Property(property: 'type', type: 'string', example: 'user'),
                        ]
                    ),
                ]
            )
        )
    ]
)]
#[OA\Post(
    path: '/api/auth/admin/login',
    tags: ['Auth'],
    summary: 'Login admin',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'secret123'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Login admin berhasil',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Login admin berhasil'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJI...'),
                            new OA\Property(property: 'type', type: 'string', example: 'admin'),
                        ]
                    ),
                ]
            )
        )
    ]
)]
#[OA\Get(
    path: '/api/auth/me',
    tags: ['Auth'],
    summary: 'Ambil profil user login',
    security: [['BearerAuth' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Profil user berhasil diambil',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Profil berhasil diambil'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 12),
                            new OA\Property(property: 'nama', type: 'string', example: 'Budi'),
                            new OA\Property(property: 'type', type: 'string', example: 'user'),
                        ]
                    ),
                ]
            )
        )
    ]
)]
#[OA\Post(
    path: '/api/auth/change-password',
    tags: ['Auth'],
    summary: 'Ubah password user',
    security: [['BearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'current_password', type: 'string', example: 'secret123'),
                new OA\Property(property: 'new_password', type: 'string', example: 'secret456'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Password berhasil diubah',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Password berhasil diubah'),
                ]
            )
        )
    ]
)]
final class AuthEndpoints
{
}
