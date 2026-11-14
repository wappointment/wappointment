<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class GetAvailabilityResponseData extends ResponseData
{
    public static function schema(): array
    {
        return ApiSchemas::object([
            'timezone' => ['type' => 'string'],
            'slots' => ApiSchemas::list(ApiSchemas::object([
                'start' => ['type' => 'integer', 'minimum' => 1],
                'end' => ['type' => 'integer', 'minimum' => 1],
                'reserved_until' => ['type' => 'integer', 'minimum' => 1],
            ])),
        ]);
    }
}
