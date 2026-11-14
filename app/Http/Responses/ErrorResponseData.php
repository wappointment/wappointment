<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class ErrorResponseData extends ResponseData
{
    public static function schema(): array
    {
        return ApiSchemas::object([
            'code' => ['type' => 'string'],
            'message' => ['type' => 'string'],
            'data' => ApiSchemas::object(['status' => ['type' => 'integer', 'minimum' => 400, 'maximum' => 599]]),
        ]);
    }
}
