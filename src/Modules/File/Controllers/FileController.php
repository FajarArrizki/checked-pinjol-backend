<?php

declare(strict_types=1);

namespace App\Modules\File\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;

final class FileController
{
    public function show(Request $request, string $path): Response
    {
        $relativePath = ltrim($path, '/');

        $filePath = null;
        foreach ([4, 5, 3, 6] as $levelsUp) {
            $basePath = dirname(__DIR__, $levelsUp);
            $candidate = $basePath . DIRECTORY_SEPARATOR . $relativePath;

            if (is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if ($filePath === null) {
            return Response::notFound('File tidak ditemukan');
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            return Response::error('Gagal membaca file', 500);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };

        return Response::make($content, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
