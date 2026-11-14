<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ContractValidator;
use Wappointment\System\Data;

abstract class ResponseData extends Data
{
    public static function from(array $data): static
    {
        try {
            return parent::from($data);
        } catch (\InvalidArgumentException $error) {
            throw new \UnexpectedValueException(static::class . ': ' . $error->getMessage(), 0, $error);
        }
    }

    public function jsonSerialize(): array
    {
        try {
            $this->validate();
            ContractValidator::validate($this->toArray(), static::schema(), 'response');
            return parent::jsonSerialize();
        } catch (\InvalidArgumentException $error) {
            throw new \UnexpectedValueException(static::class . ': ' . $error->getMessage(), 0, $error);
        }
    }
}
