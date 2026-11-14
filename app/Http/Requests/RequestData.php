<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ContractValidator;
use Wappointment\System\Data;

abstract class RequestData extends Data
{
    public static function fromHttp(array $data): static
    {
        $data = ContractValidator::validate((object) $data, static::schema(), 'request', true, true);
        return static::from($data);
    }

    protected static function normalizeIntegers(): bool
    {
        return true;
    }
}
