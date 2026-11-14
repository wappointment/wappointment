<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Http\ContractValidator;

abstract class Data implements \JsonSerializable
{
    public static function schema(): array
    {
        return DataDefinition::schema(static::class);
    }

    public static function from(array $data): static
    {
        $data = ContractValidator::validate($data, static::schema(), 'data', static::normalizeIntegers());
        $reflection = new \ReflectionClass(static::class);
        $arguments = [];
        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $property = $reflection->getProperty($parameter->getName());
            $name = DataDefinition::name($property);
            if (array_key_exists($name, $data)) {
                $arguments[$parameter->getName()] = DataDefinition::hydrate($data[$name], $property);
            }
        }
        $instance = new static(...$arguments);
        $instance->validate();
        return $instance;
    }

    public function toArray(): array
    {
        return $this->serialize(false);
    }

    public function jsonSerialize(): array
    {
        return $this->serialize(true);
    }

    protected static function normalizeIntegers(): bool
    {
        return false;
    }

    protected function validate(): void {}

    private function serialize(bool $json): array
    {
        $result = [];
        $schema = static::schema();
        $reflection = new \ReflectionClass($this);
        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $property = $reflection->getProperty($parameter->getName());
            $value = $property->getValue($this);
            if ($value instanceof Optional) {
                continue;
            }
            $name = DataDefinition::name($property);
            $result[$name] = self::serializeValue($value, $schema['properties'][$name], $json);
        }
        return $result;
    }

    private static function serializeValue(mixed $value, array $schema, bool $json): mixed
    {
        if ($value instanceof self) {
            $serialized = $json ? $value->jsonSerialize() : $value->toArray();
            return $json && $serialized === [] ? new \stdClass() : $serialized;
        }
        if (is_array($value)) {
            if ($json && $value === [] && ($schema['type'] ?? null) === 'object') {
                return new \stdClass();
            }
            foreach ($value as $key => $item) {
                $value[$key] = self::serializeValue($item, $schema['items'] ?? [], $json);
            }
        }
        return $value;
    }
}
