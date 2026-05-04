<?php

declare(strict_types=1);

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$publicPath = __DIR__ . '/public';
$requestedFile = realpath($publicPath . $path);

// Let the built-in server serve real files directly from /public.
if ($requestedFile !== false && str_starts_with($requestedFile, realpath($publicPath)) && is_file($requestedFile)) {
    return false;
}

require $publicPath . '/index.php';
