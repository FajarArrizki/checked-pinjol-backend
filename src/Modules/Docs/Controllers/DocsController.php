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
     * Menghasilkan spesifikasi OpenAPI secara dinamis dengan standar Swagger-PHP v4.
     */
    public function spec(Request $request): Response
    {
        try {
            // Tempat menyimpan konfigurasi global Swagger (seperti OpenApiSpec.php)
            $specPath = base_path('docs/openapi');
            
            // Lokasi kode modul utama backend kamu
            $modulesPath = base_path('App/Modules'); 

            $scanTargets = [];

            if (is_dir($specPath)) {
                $scanTargets[] = $specPath;
                // Memuat file manual pendukung jika diperlukan
                $this->loadSpecificationFiles($specPath);
            }

            if (is_dir($modulesPath)) {
                $scanTargets[] = $modulesPath;
            } else {
                // Fallback ke folder 'App' jika folder 'App/Modules' tidak terdeteksi langsung
                $scanTargets[] = base_path('App');
            }

            // Jika tidak ada target folder yang valid sama sekali
            if (empty($scanTargets)) {
                return Response::error('Target scanning komponen OpenAPI tidak ditemukan.', 500);
            }

            /** * SOLUSI TOTAL UNTUK V4 & INTELEPHENSE:
             * Menggunakan generator instansiasi objek statis bawaan v4 yang 
             * dikenali dengan baik oleh type-hinting VS Code editor.
             */
            $openapi = \OpenApi\Generator::scan($scanTargets);

            return Response::make(
                $openapi->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Throwable $e) {
            return Response::error(
                'Gagal menghasilkan dokumentasi: ' . $e->getMessage() . ' di file ' . $e->getFile() . ' baris ' . $e->getLine(), 
                500
            );
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