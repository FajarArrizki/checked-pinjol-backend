<?php

declare(strict_types=1);

namespace App\Core\Http;

class Response
{
    /**
     * Constructor dengan nilai default agar fleksibel.
     * Menggunakan mixed untuk content agar bisa menerima array/string sebelum di-encode.
     */
    public function __construct(
        private mixed $content = '',
        private int $statusCode = 200,
        private array $headers = []
    ) {
    }

    /**
     * Mengambil status code (digunakan oleh Router atau Middleware).
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
     * Menambahkan flag agar path file (slash) tidak di-escape menjadi \/.
     */
    public static function json(mixed $data, int $status = 200): self
    {
        // JSON_UNESCAPED_SLASHES penting agar path storage/uploads/ tidak rusak
        $content = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $headers = ['Content-Type' => 'application/json'];
    
        return new self($content, $status, $headers);
    }

    /**
     * Digunakan saat operasi sukses (misal: login atau upload file).
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
     * Digunakan saat berhasil membuat data baru (HTTP 201).
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
     * Digunakan untuk menangani error aplikasi.
     */
    public static function error(string $message, int $status = 400): self
    {
        return self::json([
            'success' => false,
            'message' => $message
        ], $status);
    }

    /**
     * Shortcut untuk error 404.
     */
    public static function notFound(string $message = 'Not Found'): self
    {
        return self::error($message, 404);
    }

    /**
     * Factory method manual jika ingin kustom konten mentah.
     */
    public static function make(string $content, int $status = 200, array $headers = []): self
    {
        return new self($content, $status, $headers);
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
}