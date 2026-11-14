<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class GetSettingResponseData extends ResponseData
{
    public static function schema(): array
    {
        return ApiSchemas::object(['name' => ['type' => 'string'], 'value' => []]);
    }
}
