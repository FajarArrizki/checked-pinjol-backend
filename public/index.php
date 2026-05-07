<?php

declare(strict_types=1);

use App\Bootstrap\ApplicationFactory;
use App\Core\Http\Response;

// 1. Load Helpers
require_once __DIR__ . '/../src/Support/helpers.php';

// 2. Load Composer Autoloader
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    // Jika vendor belum ada, tampilkan pesan ramah daripada crash tidak jelas
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
 * 3. Inisialisasi & Jalankan Aplikasi
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