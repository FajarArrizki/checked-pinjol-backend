<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use RuntimeException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

final class Container
{
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];

    public function bind(string $abstract, Closure $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    public function singleton(string $abstract, Closure $factory): void
    {
        $this->singletons[$abstract] = $factory;
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function make(string $abstract): mixed
    {
        // 1. Cek jika sudah ada instance (Singleton/Instance manual)
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // 2. Jika terdaftar sebagai singleton
        if (isset($this->singletons[$abstract])) {
            $instance = ($this->singletons[$abstract])($this);
            $this->instances[$abstract] = $instance;
            return $instance;
        }

        // 3. Jika terdaftar sebagai binding biasa (transient)
        if (isset($this->bindings[$abstract])) {
            return ($this->bindings[$abstract])($this);
        }

        // 4. Auto-resolve via reflection
        return $this->resolve($abstract);
    }

    // Alias untuk PSR-11 compatibility (opsional)
    public function get(string $id): mixed
    {
        return $this->make($id);
    }

    private function resolve(string $class): mixed
    {
        try {
            $reflector = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new RuntimeException("Tidak dapat me-resolve [$class]: " . $e->getMessage());
        }

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("[$class] tidak dapat di-instantiate");
        }

        $constructor = $reflector->getConstructor();
        if ($constructor === null) {
            return new $class();
        }

        $dependencies = array_map(function (ReflectionParameter $param) use ($class) {
            $type = $param->getType();
            
            // Resolve class dependency
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                return $this->make($type->getName());
            }

            // Jika parameter punya default value (misal: $timeout = 30)
            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            throw new RuntimeException("Tidak dapat me-resolve parameter [{$param->getName()}] pada class [$class]");
        }, $constructor->getParameters());

        return $reflector->newInstanceArgs($dependencies);
    }
}