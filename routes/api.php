<?php

declare(strict_types=1);

use App\Core\Routing\Router;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Health\Controllers\HealthController;

return static function (Router $router): void {
    $router->get('/api/health', [HealthController::class, 'index']);

    // Auth route placeholders for future JWT implementation.
    $router->post('/api/auth/register', [AuthController::class, 'register']);
    $router->post('/api/auth/login', [AuthController::class, 'login']);
    $router->get('/api/auth/me', [AuthController::class, 'me']);
};
