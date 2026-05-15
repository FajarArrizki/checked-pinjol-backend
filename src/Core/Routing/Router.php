<?php

declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Container;
use App\Core\Http\Request;
use App\Core\Http\Response;

/**
 * Router modern dengan dukungan Regex, Middleware, dan Grouping.
 */
final class Router
{
    private array $routes = [];
    private string $prefix = '';
    private array $groupMiddlewares = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function delete(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }
    public function put(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function patch(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middlewares);
    }

    public function group(array $options, callable $callback): void
    {
        $prevPrefix = $this->prefix;
        $prevMiddleware = $this->groupMiddlewares;

        $this->prefix .= $options['prefix'] ?? '';
        $this->groupMiddlewares = array_merge($this->groupMiddlewares, $options['middleware'] ?? []);

        $callback($this);

        $this->prefix = $prevPrefix;
        $this->groupMiddlewares = $prevMiddleware;
    }

    private function addRoute(string $method, string $path, mixed $handler, array $middlewares): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->prefix . $path,
            'handler' => $handler,
            'middlewares' => array_merge($this->groupMiddlewares, $middlewares),
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = rtrim($request->path(), '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $routePath = rtrim($route['path'], '/') ?: '/';
            $pattern = preg_replace_callback('/:(\w+)/', function($m) use ($routePath) {
                $paramName = $m[1];
                $isLastParam = str_ends_with($routePath, ':' . $paramName);

                return $isLastParam
                    ? '(?P<' . $paramName . '>.+)'
                    : '(?P<' . $paramName . '>[^/]+)';
            }, $routePath);
            
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, function($k) {
                    return !is_int($k);
                }, ARRAY_FILTER_USE_KEY);

                // --- PERBAIKAN DI SINI ---
                // 1. Inisialisasi response awal
                $response = new Response(''); 

                // 2. Jalankan Middleware Chain
                foreach ($route['middlewares'] as $middlewareClass) {
                    $mw = $this->container->get($middlewareClass);
                    
                    // Kirim instance $response saat ini ke middleware
                    $result = $mw->handle($request, $response); 
                    
                    if ($result instanceof Response) {
                        // Jika middleware mengembalikan status selain 200 (error), langsung return
                        if ($result->getStatus() !== 200) {
                            return $result;
                        }
                        // Update $response dengan hasil modifikasi middleware (misal: penambahan header CORS)
                        $response = $result;
                    }
                }

                // 3. Jalankan Handler (Controller)
                // Di sini kamu bisa meneruskan $response jika controller membutuhkan context response awal,
                // namun standar controller biasanya mengembalikan instance Response baru.
                return $this->callHandler($route['handler'], $request, $params);
            }
        }

        return Response::error("Endpoint [{$method}] {$uri} tidak ditemukan", 404);
    }

    private function callHandler(mixed $handler, Request $request, array $params): Response
    {
        if (is_callable($handler)) {
            return $handler($request, ...array_values($params));
        }

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = $this->container->get($class);
            return $controller->{$method}($request, ...array_values($params));
        }

        throw new \RuntimeException("Handler tidak valid untuk route.");
    }
}
