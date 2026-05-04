<?php

declare(strict_types=1);

namespace App\Modules\Docs\Controllers;

use App\Core\Config\ConfigRepository;
use App\Core\Http\Request;
use App\Core\Http\Response;
use OpenApi\Generator;

final class DocsController
{
    public function __construct(private readonly ConfigRepository $config)
    {
    }

    public function ui(Request $request): Response
    {
        $title = sprintf('%s API Docs', (string) $this->config->get('app.name', 'API'));
        $specUrl = '/swagger/openapi.json';

        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>%s</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function () {
            window.ui = SwaggerUIBundle({
                url: '%s',
                dom_id: '#swagger-ui',
            });
        };
    </script>
</body>
</html>
HTML;

        return Response::make(
            sprintf($html, htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), $specUrl),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8'],
        );
    }

    public function spec(Request $request): Response
    {
        $this->loadSpecificationFiles();

        $openapi = Generator::scan([base_path('docs/openapi')]);

        return Response::make(
            $openapi->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            200,
            ['Content-Type' => 'application/json'],
        );
    }

    private function loadSpecificationFiles(): void
    {
        foreach (glob(base_path('docs/openapi/*.php')) ?: [] as $file) {
            require_once $file;
        }
    }
}
