<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

final class AuthService
{
    public function placeholder(string $action): array
    {
        return [
            'success' => false,
            'message' => sprintf('Auth endpoint [%s] is not implemented yet.', $action),
            'meta' => [
                'driver' => 'jwt',
                'status' => 'placeholder',
            ],
        ];
    }
}
