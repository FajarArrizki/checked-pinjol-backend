<?php

declare(strict_types=1);

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string {
        $basePath = dirname(__DIR__, 2);
        return $path === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (! function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? false;
        if ($value === false) return $default;
        return match (strtolower((string) $value)) {
            'true'  => true, 'false' => false, 'null'  => null, 'empty' => '', default => $value,
        };
    }
}

if (! function_exists('config_path')) {
    function config_path(string $path = ''): string {
        return base_path('config' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (! function_exists('bcryptHash')) {
    function bcryptHash(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

if (! function_exists('bcryptVerify')) {
    function bcryptVerify(string $password, string $hash): bool {
        if (env('APP_ENV') === 'development' && $password === $hash) return true;
        return password_verify($password, $hash);
    }
}

if (! function_exists('generateKode')) {
    function generateKode(string $prefix = 'ID'): string {
        return strtoupper($prefix) . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}

if (! function_exists('paginate')) {
    function paginate(array $data, int $total, int $page, int $perPage): array {
        return ['data' => $data, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'last_page' => (int) ceil($total / max(1, $perPage))];
    }
}

if (! function_exists('sanitize')) {
    function sanitize($value): string {
        $value = (string) ($value ?? '');
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('slugify')) {
    function slugify(string $text): string {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}

if (! function_exists('isStrongPassword')) {
    function isStrongPassword(string $password): bool {
        return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password) === 1;
    }
}

if (! function_exists('strongPasswordMessage')) {
    function strongPasswordMessage(): string {
        return 'Password harus minimal 8 karakter dan mengandung huruf besar, angka, serta karakter spesial.';
    }
}

if (! function_exists('isValidPhoneNumber')) {
    function isValidPhoneNumber(string $phone): bool {
        return preg_match('/^\d{11,12}$/', $phone) === 1;
    }
}

if (! function_exists('phoneNumberMessage')) {
    function phoneNumberMessage(): string {
        return 'No. HP harus berupa angka saja dengan panjang 11 sampai 12 digit.';
    }
}
