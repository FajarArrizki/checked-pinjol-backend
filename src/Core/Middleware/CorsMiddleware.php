<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Config\ConfigRepository;

final class CorsMiddleware
{
    /**
     * Tambahkan Constructor agar ApplicationFactory tidak error
     * saat memanggil new CorsMiddleware($config)
     */
    public function __construct(
        private ConfigRepository $config
    ) {
    }

    public function handle(Request $request, Response $response): Response
    {
        // Ambil origin dari request header
        $origin = $request->header('origin');

        // Menggunakan data dari config (lebih aman daripada env() langsung di middleware)
        $allowedOrigins = explode(',', (string) $this->config->get('cors.allowed_origins', '*'));
        
        $currentOrigin = '*';
        if (!in_array('*', $allowedOrigins) && $origin && in_array($origin, $allowedOrigins)) {
            $currentOrigin = $origin;
        }

        $methods = $this->config->get('cors.allowed_methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $headers = $this->config->get('cors.allowed_headers', 'Content-Type, Authorization, X-Requested-With');

        // Jika request adalah OPTIONS (Preflight)
        if ($request->method() === 'OPTIONS') {
            // Gunakan Response::json yang sudah kita perbaiki tadi
            $preflight = Response::json(['success' => true], 204);
            
            return $preflight
                ->withHeader('Access-Control-Allow-Origin', $currentOrigin)
                ->withHeader('Access-Control-Allow-Methods', (string) $methods)
                ->withHeader('Access-Control-Allow-Headers', (string) $headers)
                ->withHeader('Access-Control-Max-Age', '86400');
        }

        // Tambahkan header ke response normal
        return $response
            ->withHeader('Access-Control-Allow-Origin', $currentOrigin)
            ->withHeader('Access-Control-Allow-Methods', (string) $methods)
            ->withHeader('Access-Control-Allow-Headers', (string) $headers);
    }
}