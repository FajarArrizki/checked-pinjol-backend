<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Middleware\CorsMiddleware;

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
        $request = Request::capture();

        try {
            $response = $this->router->dispatch($request);
        } catch (\Throwable $exception) {
            $response = Response::json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $exception->getMessage(),
            ], 500);
        }

        return $this->corsMiddleware->handle($request, $response);
    }
}
