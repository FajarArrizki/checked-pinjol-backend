<?php

declare(strict_types=1);

use App\Core\Routing\Router;
use App\Modules\Docs\Controllers\DocsController;
use App\Modules\Health\Controllers\HealthController;

return static function (Router $router): void {
    $router->get('/swagger', [DocsController::class, 'ui']);
    $router->get('/swagger/openapi.json', [DocsController::class, 'spec']);

    $router->get('/api/health', [HealthController::class, 'index']);
};
