<?php

declare(strict_types=1);

namespace App\Support; // Sesuaikan namespace ke App\Support agar terbaca autoloader

use RuntimeException;
use App\Core\Config\ConfigRepository;

class FileUploader
{
    private int    $maxSize;
    private array  $allowedTypes;
    private string $uploadPath;

    private const MIME_WHITELIST = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'pdf'  => 'application/pdf',
        'mp4'  => 'video/mp4',
    ];

    /**
     * Kita inject ConfigRepository agar nilainya sinkron dengan file config/app.php
     */
    public function __construct(ConfigRepository $config)
    {
        // Ambil dari config, jika tidak ada pakai default
        $this->maxSize    = (int) $config->get('upload.max_size', 5242880); 
        
        // Pastikan path menggunakan DIRECTORY_SEPARATOR agar aman di Mac/Linux
        $this->uploadPath = rtrim(dirname(__DIR__, 2) . '/' . $config->get('upload.path', 'storage/uploads'), '/');
        
        $allowedExtensions = explode(',', (string) $config->get('upload.allowed_types', 'jpg,jpeg,png,pdf'));
        
        $this->allowedTypes = array_filter(
            array_map(fn($ext) => self::MIME_WHITELIST[trim($ext)] ?? null, $allowedExtensions)
        );
    }

    public function upload(array $file, string $subfolder = 'general'): array
    {
        // 1. Validasi keberadaan file tmp
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new RuntimeException("File tidak valid atau tidak terupload dengan benar.");
        }

        // 2. Validasi ukuran
        if ($file['size'] > $this->maxSize) {
            $maxMb = round($this->maxSize / 1048576, 1);
            throw new RuntimeException("Ukuran file terlalu besar. Maksimal {$maxMb}MB");
        }

        // 3. Validasi tipe MIME secara aman
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new RuntimeException("Tipe file {$mimeType} tidak diizinkan.");
        }

        // 4. Validasi ekstensi
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!array_key_exists($ext, self::MIME_WHITELIST)) {
            throw new RuntimeException("Ekstensi .{$ext} tidak didukung.");
        }

        // 5. Buat direktori tujuan (Gunakan tahun/bulan agar rapi)
        $targetDir = $this->uploadPath . '/' . $subfolder . '/' . date('Y/m');
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            throw new RuntimeException("Gagal membuat folder penyimpanan.");
        }

        // 6. Nama file unik agar tidak menimpa file lama
        $namaFile = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $fullPath = $targetDir . '/' . $namaFile;

        // 7. Pindahkan file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new RuntimeException("Gagal memindahkan file ke storage.");
        }

        // Path relatif untuk disimpan ke Database (agar mudah dipanggil di Frontend)
        $relativePath = str_replace(dirname(__DIR__, 2) . '/', '', $fullPath);

        return [
            'original_name' => $file['name'],
            'file_path'     => $relativePath,
            'file_type'     => $mimeType,
            'file_size'     => $file['size'],
        ];
    }

    public function delete(string $relativePath): bool
    {
        $fullPath = dirname(__DIR__, 2) . '/' . $relativePath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}