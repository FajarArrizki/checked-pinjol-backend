<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    | Di sini Anda menentukan koneksi mana yang akan digunakan sebagai default.
    | Sangat berguna jika Anda memiliki koneksi stagging dan produksi.
    */
    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    | Daftar koneksi yang tersedia. Untuk saat ini fokus pada koneksi MySQL.
    */
    'connections' => [

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => (int) env('DB_PORT', 3306),
            'database'  => env('DB_DATABASE', 'checked_pinjol'),
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci', // Lebih direkomendasikan daripada general_ci
            'options'   => [
                // Tambahkan opsi PDO jika diperlukan di masa depan
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ],
        ],

        // Anda bisa menambahkan koneksi 'sqlite' atau 'pgsql' di sini nanti
    ],
];