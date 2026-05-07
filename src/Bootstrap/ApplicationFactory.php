<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Core\Application;
use App\Core\Config\ConfigRepository;
use App\Core\Container;
use App\Core\Database\ConnectionFactory;
use App\Core\Database\DatabaseManager;
use App\Core\Middleware\CorsMiddleware;
use App\Core\Routing\Router;

final class ApplicationFactory
{
    public static function create(string $basePath): Application
{
    self::loadEnvironment($basePath . DIRECTORY_SEPARATOR . '.env');

    $container = new Container();
    $config = new ConfigRepository($basePath . DIRECTORY_SEPARATOR . 'config');
    $router = new Router($container);

    date_default_timezone_set((string) $config->get('app.timezone', 'Asia/Jakarta'));

    // 1. Daftarkan Config & Router
    $container->instance(ConfigRepository::class, $config);
    $container->instance(Router::class, $router);

    // 2. Daftarkan Database
    // Kita simpan ke variabel $db dulu agar bisa dipakai di middleware bawahnya
    $db = new DatabaseManager($config, new ConnectionFactory());
    $container->instance(DatabaseManager::class, $db);

    // 3. Daftarkan Middleware
    // Tambahkan CorsMiddleware
    $container->instance(CorsMiddleware::class, new CorsMiddleware($config));

    // Daftarkan AuthMiddleware (Wajib Login)
    $container->instance(\App\Core\Middleware\AuthMiddleware::class, new \App\Core\Middleware\AuthMiddleware($db));

    // TAMBAHKAN BARIS INI: Daftarkan OptionalAuthMiddleware (Login Tidak Wajib)
    $container->instance(\App\Core\Middleware\OptionalAuthMiddleware::class, new \App\Core\Middleware\OptionalAuthMiddleware($db));

    // 4. Load Routes
    $routes = require $basePath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
    $routes($router);

    return new Application(
        $container, 
        $router, 
        $container->get(CorsMiddleware::class)
    );
}

    private static function loadEnvironment(string $envPath): void
    {
        if (! file_exists($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = self::normalizeEnvironmentValue(trim($value));

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    private static function normalizeEnvironmentValue(string $value): mixed
    {
        $normalized = strtolower($value);

        return match ($normalized) {
            'true' => true,
            'false' => false,
            'null' => null,
            'empty' => '',
            default => trim($value, "\"'"),
        };
    }
}
