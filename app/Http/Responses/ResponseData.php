<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ContractValidator;
use Wappointment\System\Data;

abstract class ResponseData extends Data implements \JsonSerializable
{
    private readonly array $values;

    final public function __construct(array $values)
    {
        try {
            $this->values = ContractValidator::validate($values, static::schema(), 'response');
        } catch (\InvalidArgumentException $error) {
            throw new \UnexpectedValueException(static::class . ': ' . $error->getMessage(), 0, $error);
        }
    }

    abstract public static function schema(): array;

    public static function from(array $data): static
    {
        return new static($data);
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function jsonSerialize(): array
    {
        return self::wireValue($this->values, static::schema());
    }

    private static function wireValue(mixed $value, array $schema): mixed
    {
        if (isset($schema['anyOf'])) {
            foreach ($schema['anyOf'] as $alternative) {
                try {
                    ContractValidator::validate($value, $alternative);
                } catch (\InvalidArgumentException) {
                    continue;
                }
                return self::wireValue($value, $alternative);
            }
        }
        if (($schema['type'] ?? null) === 'object') {
            if ($value === []) {
                return new \stdClass();
            }
            foreach ($value as $key => $item) {
                $property = $schema['properties'][$key] ?? $schema['additionalProperties'] ?? [];
                $value[$key] = self::wireValue($item, is_array($property) ? $property : []);
            }
        } elseif (($schema['type'] ?? null) === 'array') {
            foreach ($value as $key => $item) {
                $value[$key] = self::wireValue($item, $schema['items'] ?? []);
            }
        }
        return $value;
    }
}
