<?php

declare(strict_types=1);

$requiredPaths = [
    __DIR__ . '/../public/index.php',
    __DIR__ . '/../routes/api.php',
    __DIR__ . '/../config/app.php',
    __DIR__ . '/../src/Bootstrap/ApplicationFactory.php',
    __DIR__ . '/../src/Core/Routing/Router.php',
    __DIR__ . '/../src/Modules/Health/Controllers/HealthController.php',
];

foreach ($requiredPaths as $path) {
    if (! file_exists($path)) {
        fwrite(STDERR, sprintf("Missing required file: %s\n", $path));
        exit(1);
    }
}

fwrite(STDOUT, "Healthcheck scaffold passed.\n");
exit(0);
