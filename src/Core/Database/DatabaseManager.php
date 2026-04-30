<?php

declare(strict_types=1);

namespace App\Core\Database;

use App\Core\Config\ConfigRepository;
use PDO;

final class DatabaseManager
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly ConfigRepository $config,
        private readonly ConnectionFactory $factory,
    ) {
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $defaultConnection = (string) $this->config->get('database.default', 'mysql');
        $connectionConfig = (array) $this->config->get('database.connections.' . $defaultConnection, []);

        $this->connection = $this->factory->make($connectionConfig);

        return $this->connection;
    }
}
