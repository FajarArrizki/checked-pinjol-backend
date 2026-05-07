<?php

declare(strict_types=1);

namespace App\Docs\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Checked Pinjol API',
    description: 'REST API untuk aplikasi pengecekan pinjaman online (pinjol)',
    contact: new OA\Contact(name: 'Tim Checked Pinjol')
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Development Server'
) ]
#[OA\SecurityScheme(
    securityScheme: 'BearerAuth',
    type: 'http',
    name: 'Authorization',
    in: 'header',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]

#[OA\Tag(name: 'Health', description: 'Cek status kesehatan server dan database')]
#[OA\Tag(name: 'Auth', description: 'Autentikasi user dan admin')]
#[OA\Tag(name: 'Pinjol', description: 'Data pinjaman online')]
#[OA\Tag(name: 'Laporan', description: 'Sistem pengaduan dan pelaporan pinjol bermasalah')]
#[OA\Tag(name: 'Artikel', description: 'Konten edukasi dan tips finansial')]
#[OA\Tag(name: 'Simulasi', description: 'Kalkulator simulasi pinjaman')]
#[OA\Tag(name: 'Admin', description: 'Panel kontrol khusus administrator')]
#[OA\Tag(name: 'Ulasan', description: 'Rating dan testimoni pengguna')]
#[OA\Tag(name: 'Regulasi', description: 'Kriteria regulasi pinjol')]
#[OA\Tag(name: 'Docs', description: 'Isi dari panduan modul itu sendiri')]

final class OpenApiSpec
{
}

