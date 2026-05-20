<?php

declare(strict_types=1);

use App\Bootstrap\ApplicationFactory;
use App\Core\Http\Response;

// ============================================================================
// 1. CORS INTERCEPTOR (Mencegat Preflight OPTIONS & Menyuntikkan Izin Browser)
// ============================================================================
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowedOrigins = ['http://localhost:5173']; // Origin frontend Vite React kamu
    
    if (in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // Cache preflight selama 1 hari
    }
}

// Jika request adalah OPTIONS, langsung kunci di sini dan beri status 204 (No Content)
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    http_response_code(204);
    exit(0);
}
// ============================================================================

// 2. Load Helpers
require_once __DIR__ . '/../src/Support/helpers.php';

// 3. Load Composer Autoloader
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Autoloader tidak ditemukan. Silakan jalankan "composer install" di terminal.'
    ]);
    exit;
}

require_once $autoloadPath;

/**
 * 4. Inisialisasi & Jalankan Aplikasi
 */
try {
    // Inisialisasi Container, Config, Database, dan Router
    $app = ApplicationFactory::create(dirname(__DIR__));
    
    // Jalankan siklus Request -> Response
    $response = $app->run();
    
    // Kirim output ke browser
    $response->send();

} catch (\Throwable $e) {
    // Kita cek manual lewat index.php
    $file = (new ReflectionClass(\App\Core\Http\Response::class))->getFileName();
    
    header('Content-Type: application/json');
    echo json_encode([
        'debug_info' => [
            'file_yang_dibaca' => $file,
            'apakah_method_error_ada' => method_exists(\App\Core\Http\Response::class, 'error'),
            'pesan_error_asli' => $e->getMessage()
        ]
    ]);
    exit;
}