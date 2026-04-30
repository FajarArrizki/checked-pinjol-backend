<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Container
{
    private array $instances = [];

    public function instance(string $id, object $service): void
    {
        $this->instances[$id] = $service;
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (! class_exists($id)) {
            throw new RuntimeException(sprintf('Service [%s] is not registered.', $id));
        }

        $reflection = new \ReflectionClass($id);
        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            $instance = new $id();
            $this->instances[$id] = $instance;

            return $instance;
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (! $type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                throw new RuntimeException(sprintf('Unable to resolve dependency [%s] for [%s].', $parameter->getName(), $id));
            }

            $dependencies[] = $this->get($type->getName());
        }

        $instance = $reflection->newInstanceArgs($dependencies);
        $this->instances[$id] = $instance;

        return $instance;
    }
}
