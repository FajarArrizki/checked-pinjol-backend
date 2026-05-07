<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Middleware\CorsMiddleware;
use Throwable;

final class Application
{
    public function __construct(
        private readonly Container $container,
        private readonly Routing\Router $router,
        private readonly CorsMiddleware $corsMiddleware,
    ) {
    }

    public function run(): Response
    {
        // Menangkap request yang masuk
        $request = Request::capture();

        try {
            // Proses routing
            $response = $this->router->dispatch($request);
        } catch (Throwable $exception) {
            // Penanganan error global
            $response = $this->handleException($exception);
        }

        // Terapkan CORS dan kembalikan response
        return $this->corsMiddleware->handle($request, $response);
    }

    private function handleException(Throwable $e): Response
    {
        $debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $payload = [
            'success' => false,
            'message' => $debug ? $e->getMessage() : 'Terjadi kesalahan pada server',
        ];

        if ($debug) {
            $payload['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice($e->getTrace(), 0, 3)
            ];
        }

        return Response::json($payload, 500);
    }
}