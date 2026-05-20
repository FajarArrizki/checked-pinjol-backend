<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Config\ConfigRepository;

final class CorsMiddleware
{
    /**
     * Constructor untuk injeksi konfigurasi via ApplicationFactory
     */
    public function __construct(
        private readonly ConfigRepository $config
    ) {
    }

    /**
     * Sesuai dengan arsitektur Application.php kamu:
     * Parameter ke-2 WAJIB App\Core\Http\Response, BUKAN callable $next.
     */
    public function handle(Request $request, Response $response): Response
    {
        // 1. Ambil data origin dari request header browser
        $origin = $request->header('origin');

        // 2. Load konfigurasi origins yang diizinkan
        $allowedOrigins = explode(',', (string) $this->config->get('cors.allowed_origins', '*'));
        
        $currentOrigin = '*';
        if (!in_array('*', $allowedOrigins) && $origin && in_array($origin, $allowedOrigins)) {
            $currentOrigin = $origin;
        }

        $methods = $this->config->get('cors.allowed_methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $headers = $this->config->get('cors.allowed_headers', 'Content-Type, Authorization, X-Requested-With');

        // 3. JIKA REQUEST ADALAH OPTIONS (Preflight dari Browser)
        if ($request->method() === 'OPTIONS') {
            // Kita manipulasi objek response bawaan menjadi kosong dengan status 204
            return Response::make('', 204)
                ->withHeader('Access-Control-Allow-Origin', $currentOrigin)
                ->withHeader('Access-Control-Allow-Methods', (string) $methods)
                ->withHeader('Access-Control-Allow-Headers', (string) $headers)
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Max-Age', '86400');
        }

        // 4. JIKA REQUEST BIASA (POST/GET)
        // Langsung suntikkan header CORS ke objek $response yang dilemparkan oleh Application.php
        return $response
            ->withHeader('Access-Control-Allow-Origin', $currentOrigin)
            ->withHeader('Access-Control-Allow-Methods', (string) $methods)
            ->withHeader('Access-Control-Allow-Headers', (string) $headers)
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    }
}