<?php

declare(strict_types=1);

namespace App\Modules\Docs\Controllers;

use App\Core\Config\ConfigRepository;
use App\Core\Http\{Request, Response};

final class DocsController
{

    public function __construct(private readonly ConfigRepository $config)
    {
    }

    public function ui(Request $request): Response
    {
        $appName = (string) $this->config->get('app.name', 'Checked Pinjol');
        $title   = "{$appName} API Documentation";
        
        // URL menuju endpoint spec() di bawah
        $specUrl = '/api/docs/openapi.json';

        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>%s</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body { margin: 0; padding: 0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function () {
            window.ui = SwaggerUIBundle({
                url: '%s',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout"
            });
        };
    </script>
</body>
</html>
HTML;

        return Response::make(
            sprintf($html, htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), $specUrl),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    /**
     * GET /api/docs/openapi.json
     * Menghasilkan spesifikasi OpenAPI secara dinamis.
     */
    public function spec(Request $request): Response
    {
        try {
            $specPath = base_path('docs/openapi');
            $srcPath  = base_path('src'); // Tambahkan ini
            
            if (!is_dir($specPath)) {
                return Response::error('Folder spesifikasi OpenAPI tidak ditemukan', 500);
            }

            // Memuat file manual jika diperlukan
            $this->loadSpecificationFiles($specPath);

            $openapi = \OpenApi\Generator::scan([
                $specPath, 
                $srcPath
            ]);

            return Response::make(
                $openapi->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return Response::error('Gagal menghasilkan dokumentasi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Memastikan semua file PHP di folder spesifikasi terload.
     */
    private function loadSpecificationFiles(string $path): void
    {
        $files = glob($path . '/*.php') ?: [];
        foreach ($files as $file) {
            require_once $file;
        }
    }
}