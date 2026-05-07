<?php

declare(strict_types=1);

namespace App\Support;

final class JWT
{
    public static function encode(array $payload): string
    {
        return base64_encode((string) json_encode($payload));
    }
}
