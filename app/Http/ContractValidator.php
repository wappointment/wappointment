<?php
declare(strict_types=1);

namespace Wappointment\Http;

class ContractValidator
{
    public static function validate(mixed $value, array $schema, string $path = 'data', bool $normalizeIntegers = false, bool $jsonObjects = false): mixed
    {
        if (isset($schema['anyOf'])) {
            foreach ($schema['anyOf'] as $alternative) {
                try {
                    return self::validate($value, $alternative, $path, $normalizeIntegers, $jsonObjects);
                } catch (\InvalidArgumentException) {
                    // A union matches when any one of its declared alternatives validates.
                }
            }
            throw new \InvalidArgumentException("Invalid {$path}: no matching schema.");
        }
        $type = $schema['type'] ?? null;
        if ($normalizeIntegers && $type === 'integer' && is_string($value)
            && preg_match('/^-?(0|[1-9][0-9]*)$/D', $value)
            && filter_var($value, FILTER_VALIDATE_INT) !== false) {
            $value = (int) $value;
        }
        $valid = match ($type) {
            null => true,
            'null' => $value === null,
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_int($value) || is_float($value),
            'boolean' => is_bool($value),
            'object' => $value instanceof \stdClass || (!$jsonObjects && is_array($value) && ($value === [] || !array_is_list($value))),
            'array' => is_array($value) && array_is_list($value),
            default => throw new \LogicException("Unsupported contract type: {$type}"),
        };
        if (!$valid) {
            throw new \InvalidArgumentException("Invalid {$path}: expected {$type}.");
        }
        if (is_float($value) && !is_finite($value)) {
            throw new \InvalidArgumentException("Invalid {$path}: expected a finite number.");
        }
        if ($value instanceof \stdClass) {
            $value = get_object_vars($value);
        } elseif (is_object($value) || is_resource($value)) {
            throw new \InvalidArgumentException("Invalid {$path}: expected a JSON value.");
        }
        if (isset($schema['enum']) && !in_array($value, $schema['enum'], true)) {
            throw new \InvalidArgumentException("Invalid {$path}: unsupported value.");
        }
        if (is_string($value)) {
            $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
            if (str_contains($value, "\0") || preg_match('//u', $value) !== 1
                || $length < ($schema['minLength'] ?? 0) || $length > ($schema['maxLength'] ?? PHP_INT_MAX)
                || (isset($schema['pattern']) && !preg_match('~' . $schema['pattern'] . '~uD', $value))) {
                throw new \InvalidArgumentException("Invalid {$path}: string constraints failed.");
            }
            if (($schema['format'] ?? null) === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                throw new \InvalidArgumentException("Invalid {$path}: expected an email address.");
            }
            if (($schema['format'] ?? null) === 'date') {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value) {
                    throw new \InvalidArgumentException("Invalid {$path}: expected a calendar date.");
                }
            }
        }
        if ((is_int($value) || is_float($value))
            && ($value < ($schema['minimum'] ?? -INF) || $value > ($schema['maximum'] ?? INF))) {
            throw new \InvalidArgumentException("Invalid {$path}: number is out of range.");
        }
        if ($type === 'object') {
            foreach ($schema['required'] ?? [] as $key) {
                if (!array_key_exists($key, $value)) {
                    throw new \InvalidArgumentException("Missing {$path}.{$key}.");
                }
            }
            foreach ($value as $key => $item) {
                $property = $schema['properties'][$key] ?? $schema['additionalProperties'] ?? true;
                if ($property === false) {
                    throw new \InvalidArgumentException("Unknown {$path}.{$key}.");
                }
                $value[$key] = self::validate($item, is_array($property) ? $property : [], "{$path}.{$key}", $normalizeIntegers, $jsonObjects);
            }
            if (count($value) < ($schema['minProperties'] ?? 0)) {
                throw new \InvalidArgumentException("Invalid {$path}: no fields supplied.");
            }
        }
        if ($type === 'array' || ($type === null && is_array($value))) {
            if (count($value) < ($schema['minItems'] ?? 0) || count($value) > ($schema['maxItems'] ?? PHP_INT_MAX)) {
                throw new \InvalidArgumentException("Invalid {$path}: array length is out of range.");
            }
            foreach ($value as $key => $item) {
                $value[$key] = self::validate($item, $schema['items'] ?? [], "{$path}[{$key}]", $normalizeIntegers, $jsonObjects);
            }
        }
        return $value;
    }
}
