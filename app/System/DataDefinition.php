<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Attributes\Constraint;
use Wappointment\Http\Attributes\MapInputName;

final class DataDefinition
{
    private static array $schemas = [];

    public static function schema(string $class): array
    {
        if (isset(self::$schemas[$class])) {
            return self::$schemas[$class];
        }
        $reflection = new \ReflectionClass($class);
        $parameters = [];
        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }
        $properties = [];
        $required = [];
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }
            if (!$property->isReadOnly() || !isset($parameters[$property->getName()])) {
                throw new \LogicException("Data property {$class}::{$property->getName()} must be readonly and initialized by its constructor.");
            }
            $name = self::name($property);
            $schema = self::typeSchema($property->getType());
            $rules = [];
            $collection = $property->getAttributes(CollectionOf::class)[0] ?? null;
            if ($collection !== null) {
                $definition = $collection->newInstance();
                $items = self::namedSchema($definition->type);
                if ($definition->min !== null) {
                    $items['minimum'] = $definition->min;
                }
                $rules = ['type' => 'array', 'items' => $items];
            }
            foreach ($property->getAttributes(Constraint::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $rules = array_replace($rules, $attribute->newInstance()->rules());
            }
            $properties[$name] = self::constrain($schema, $rules);
            if (!$parameters[$property->getName()]->isDefaultValueAvailable()) {
                $required[] = $name;
            }
        }
        foreach ($parameters as $parameter) {
            if (!$reflection->hasProperty($parameter->getName()) || !$reflection->getProperty($parameter->getName())->isPublic()) {
                throw new \LogicException("Data constructor parameter {$class}::{$parameter->getName()} must have a public readonly property.");
            }
        }
        return self::$schemas[$class] = [
            'type' => 'object', 'properties' => $properties,
            'required' => $required, 'additionalProperties' => false,
        ];
    }

    public static function name(\ReflectionProperty $property): string
    {
        $attribute = $property->getAttributes(MapInputName::class)[0] ?? null;
        return $attribute?->newInstance()->name ?? $property->getName();
    }

    public static function hydrate(mixed $value, \ReflectionProperty $property): mixed
    {
        if ($value === null || $value instanceof Optional) {
            return $value;
        }
        $collection = $property->getAttributes(CollectionOf::class)[0] ?? null;
        if ($collection !== null) {
            $type = $collection->newInstance()->type;
            return is_subclass_of($type, Data::class)
                ? array_map(static fn(array $item): Data => $type::from($item), $value)
                : $value;
        }
        $type = $property->getType();
        foreach ($type instanceof \ReflectionUnionType ? $type->getTypes() : [$type] as $alternative) {
            if ($alternative instanceof \ReflectionNamedType && is_subclass_of($alternative->getName(), Data::class)) {
                $class = $alternative->getName();
                return $class::from($value);
            }
        }
        return $value;
    }

    private static function typeSchema(?\ReflectionType $type): array
    {
        if ($type instanceof \ReflectionUnionType) {
            $types = array_values(array_filter($type->getTypes(), static fn(\ReflectionNamedType $part): bool => $part->getName() !== Optional::class));
            $schemas = array_map(static fn(\ReflectionNamedType $part): array => self::namedSchema($part->getName()), $types);
            return count($schemas) === 1 ? $schemas[0] : ['anyOf' => $schemas];
        }
        if (!$type instanceof \ReflectionNamedType) {
            throw new \LogicException('Data properties must have named or union types.');
        }
        $schema = self::namedSchema($type->getName());
        return $type->allowsNull() && !in_array($type->getName(), ['mixed', 'null'], true)
            ? ['anyOf' => [$schema, ['type' => 'null']]]
            : $schema;
    }

    private static function namedSchema(string $type): array
    {
        if (is_subclass_of($type, Data::class)) {
            return $type::schema();
        }
        return match ($type) {
            'int' => ['type' => 'integer'],
            'float' => ['type' => 'number'],
            'bool' => ['type' => 'boolean'],
            'string' => ['type' => 'string'],
            'array' => ['type' => 'array', 'items' => []],
            'null' => ['type' => 'null'],
            'mixed' => [],
            default => throw new \LogicException("Unsupported Data property type: {$type}"),
        };
    }

    private static function constrain(array $schema, array $rules): array
    {
        if (isset($schema['anyOf'])) {
            return ['anyOf' => array_map(
                static fn(array $alternative): array => ($alternative['type'] ?? null) === 'null' ? $alternative : self::constrain($alternative, $rules),
                $schema['anyOf']
            )];
        }
        if (isset($rules['type']) && ($schema['type'] ?? null) !== 'array') {
            return $schema;
        }
        return array_replace($schema, $rules);
    }
}
