<?php

declare(strict_types=1);

use App\Bootstrap\ApplicationFactory;

require_once __DIR__ . '/../src/Support/helpers.php';

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

return ApplicationFactory::create(base_path());
