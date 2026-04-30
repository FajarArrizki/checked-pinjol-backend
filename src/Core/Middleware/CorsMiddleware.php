<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Config\ConfigRepository;
use App\Core\Http\Request;
use App\Core\Http\Response;

final class CorsMiddleware
{
    public function __construct(private readonly ConfigRepository $config)
    {
    }

    public function handle(Request $request, Response $response): Response
    {
        $allowedOrigins = (array) $this->config->get('cors.allowed_origins', []);
        $origin = $request->header('Origin', $allowedOrigins[0] ?? '*');

        if ($request->method() === 'OPTIONS') {
            return Response::json(['success' => true], 200)
                ->withHeader('Access-Control-Allow-Origin', $origin ?? '*')
                ->withHeader('Access-Control-Allow-Methods', implode(', ', (array) $this->config->get('cors.allowed_methods', [])))
                ->withHeader('Access-Control-Allow-Headers', implode(', ', (array) $this->config->get('cors.allowed_headers', [])));
        }

        return $response
            ->withHeader('Access-Control-Allow-Origin', $origin ?? '*')
            ->withHeader('Access-Control-Allow-Methods', implode(', ', (array) $this->config->get('cors.allowed_methods', [])))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', (array) $this->config->get('cors.allowed_headers', [])));
    }
}
