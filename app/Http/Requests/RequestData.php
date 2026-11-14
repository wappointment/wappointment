<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ContractValidator;
use Wappointment\System\Data;

abstract class RequestData extends Data
{
    public function __construct(private readonly array $values) {}

    abstract public static function schema(): array;

    public static function from(array $data): static
    {
        $data = ContractValidator::validate($data, static::schema(), 'request', true);
        $constructor = (new \ReflectionClass(static::class))->getConstructor();
        return $constructor->getDeclaringClass()->getName() === self::class
            ? new static($data)
            : parent::from($data);
    }

    public function toArray(): array
    {
        $constructor = (new \ReflectionClass(static::class))->getConstructor();
        return $constructor->getDeclaringClass()->getName() === self::class
            ? $this->values
            : parent::toArray();
    }
}
