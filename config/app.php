<?php

declare(strict_types=1);

return [
    // Menggunakan helper env() agar lebih bersih dan mendukung konversi tipe data
    'name'     => env('APP_NAME', 'Checked Pinjol API'),
    
    // Default ke production untuk keamanan
    'env'      => env('APP_ENV', 'production'),
    
    // Debug otomatis false jika tidak diatur di .env
    'debug'    => (bool) env('APP_DEBUG', false),
    
    'url'      => env('APP_URL', 'http://localhost:8000'),
    
    'version'  => '1.0.0',
    
    // Memberikan fleksibilitas timezone namun tetap ada default Jakarta
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    'locale'   => env('APP_LOCALE', 'id'),
];