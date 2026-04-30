<?php

declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Container;
use App\Core\Http\Request;
use App\Core\Http\Response;

final class Router
{
    private array $routes = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $handler = $this->routes[$request->method()][$request->path()] ?? null;

        if ($handler === null) {
            return Response::json([
                'success' => false,
                'message' => 'Route not found',
            ], 404);
        }

        [$class, $method] = $handler;
        $controller = $this->container->get($class);

        return $controller->{$method}($request);
    }
}
