<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Support/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';

foreach (glob(base_path('docs/openapi/*.php')) ?: [] as $file) {
    require_once $file;
}

$openapi = OpenApi\Generator::scan([base_path('docs/openapi')]);
$json = $openapi->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (! str_contains($json, '"/api/health"')) {
    fwrite(STDERR, "OpenAPI spec does not contain /api/health.\n");
    exit(1);
}

fwrite(STDOUT, "OpenAPI smoke test passed.\n");
exit(0);
