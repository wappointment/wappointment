<?php
declare(strict_types=1);

namespace Wappointment\System;

class Container
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];

    private function __construct() {}

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, callable $concrete): void
    {
        $this->bind($abstract, function() use ($abstract, $concrete) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $concrete();
            }
            return $this->instances[$abstract];
        });
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]();
        }

        return $this->resolve($abstract, $parameters);
    }

    private function resolve(string $class, array $parameters = []): mixed
    {
        // Special case: resolve Container to its singleton instance
        if ($class === self::class) {
            return $this;
        }

        $reflector = new \ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$class} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (!$constructor) {
            return new $class;
        }

        $constructorParams = $constructor->getParameters();
        $dependencies = [];

        foreach ($constructorParams as $parameter) {
            $dependencies[] = $this->resolveParameter($parameter, $parameters);
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveParameter(\ReflectionParameter $parameter, array $parameters): mixed
    {
        $type = $parameter->getType();

        // Handle built-in types (string, int, etc.)
        if (!$type || $type->isBuiltin()) {
            return $this->resolveBuiltInParameter($parameter, $parameters);
        }

        // Handle class/interface types
        $typeName = $type->getName();
        return $parameters[$typeName] ?? $this->make($typeName, $parameters);
    }

    private function resolveBuiltInParameter(\ReflectionParameter $parameter, array $parameters): mixed
    {
        $paramName = $parameter->getName();

        if (isset($parameters[$paramName])) {
            return $parameters[$paramName];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new \Exception("Cannot resolve parameter {$paramName}");
    }
}
