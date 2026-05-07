<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    // Properti untuk menyimpan data user yang login (diisi oleh AuthMiddleware)
    private ?array $user = null;

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $headers,
        private readonly array $query,
        private readonly array $body,
        private readonly string $rawBody,
    ) {
    }

    /**
     * Digunakan oleh AuthMiddleware untuk menyuntikkan data user setelah token valid.
     */
    public function setUser(array $userData): void
    {
        $this->user = $userData;
    }

    /**
     * Digunakan di Controller untuk mengambil data user yang sedang login.
     */
    public function user(): ?array
    {
        return $this->user;
    }

    /**
     * Menangkap request yang masuk dan mengubahnya menjadi object Request.
     */
    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        
        $rawBody = file_get_contents('php://input') ?: '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        // Default body dari $_POST (untuk form-data / upload file)
        $body = $_POST;

        // Jika request berupa JSON, decode raw body-nya
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        return new self(
            $method,
            $uriPath,
            self::getAllHeaders(),
            $_GET,
            $body,
            $rawBody,
        );
    }

    /**
     * Mengambil semua header request.
     */
    private static function getAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return array_change_key_case(getallheaders(), CASE_LOWER);
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $key = strtolower(str_replace('_', '-', substr($name, 5)));
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    // Helper methods
    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }

    /**
     * Mengambil input berdasarkan key (mendukung POST dan GET).
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array 
    { 
        return array_merge($this->query, $this->body); 
    }

    /**
     * Mengambil satu header spesifik.
     */
    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * MENGAMBIL TOKEN DARI HEADER AUTHORIZATION (Bearer Token)
     */
    public function bearerToken(): ?string
    {
        $auth = $this->header('authorization') ?? '';
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    /**
     * Validasi sederhana untuk input request.
     */
    public function validate(array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            $ruleArray = explode('|', $rule);

            foreach ($ruleArray as $singleRule) {
                if ($singleRule === 'required' && (is_null($value) || $value === '')) {
                    $errors[] = "Field {$field} wajib diisi.";
                }
                if ($singleRule === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Format {$field} tidak valid.";
                }
                if (str_starts_with($singleRule, 'min:')) {
                    $min = (int) explode(':', $singleRule)[1];
                    if (strlen((string)$value) < $min) {
                        $errors[] = "Field {$field} minimal {$min} karakter.";
                    }
                }
            }
        }
        return $errors;
    }
}