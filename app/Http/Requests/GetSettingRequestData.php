<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class GetSettingRequestData extends RequestData
{
    public static function schema(): array
    {
        return ApiSchemas::object(['name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100, 'pattern' => '^[a-zA-Z0-9_-]+$']]);
    }
}
