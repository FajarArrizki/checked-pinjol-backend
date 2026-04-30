<?php

declare(strict_types=1);

return [
    'driver' => env('AUTH_DRIVER', 'jwt'),
    'jwt' => [
        'secret' => env('JWT_SECRET', 'change-me'),
        'ttl' => (int) env('JWT_TTL', 3600),
    ],
];
