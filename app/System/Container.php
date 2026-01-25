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
            $type = $parameter->getType();

            if (!$type || $type->isBuiltin()) {
                if (isset($parameters[$parameter->getName()])) {
                    $dependencies[] = $parameters[$parameter->getName()];
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve {$parameter->getName()}");
                }
            } else {
                $typeName = $type->getName();
                if (isset($parameters[$typeName])) {
                    $dependencies[] = $parameters[$typeName];
                } else {
                    $dependencies[] = $this->make($typeName, $parameters);
                }
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
