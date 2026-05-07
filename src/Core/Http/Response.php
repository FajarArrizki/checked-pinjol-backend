<?php

declare(strict_types=1);

namespace App\Core\Http;

class Response
{
    /**
     * Constructor dengan nilai default agar fleksibel saat diinstansiasi di Router.
     */
    public function __construct(
        private string $content = '',
        private int $statusCode = 200,
        private array $headers = []
    ) {
    }

    /**
     * Mengambil status code (digunakan oleh Router untuk validasi middleware).
     */
    public function getStatus(): int
    {
        return $this->statusCode;
    }

    /**
     * Menambahkan header secara berantai (Fluent Interface).
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Shortcut untuk mengirim response JSON.
     */
    public static function json(mixed $data, int $status = 200): self
    {
        $content = json_encode($data);
        $headers = ['Content-Type' => 'application/json'];
    
        return new self($content, $status, $headers);
    }

    /**
     * Dipanggil oleh AuthController::login atau register saat sukses.
     */
    public static function success(mixed $data = null, string $message = 'Success'): self
    {
        return self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], 200);
    }

    /**
     * Dipanggil oleh AuthController::register saat berhasil membuat user baru.
     */
    public static function created(mixed $data = null, string $message = 'Created'): self
    {
        return self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], 201);
    }

    /**
     * Dipanggil saat terjadi error (Email terdaftar, Password salah, dll).
     */
    public static function error(string $message, int $status = 400): self
    {
        return self::json([
            'success' => false,
            'message' => $message
        ], $status);
    }

    /**
     * Dipanggil jika endpoint atau user tidak ditemukan.
     */
    public static function notFound(string $message = 'Not Found'): self
    {
        return self::error($message, 404);
    }

    /**
     * Mengirim response ke browser.
     */
    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $key => $value) {
                header("$key: $value");
            }
        }
        echo $this->content;
    }

    public static function make(string $content, int $status = 200, array $headers = []): self
    {
        return new self($content, $status, $headers);
    }
}