<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class SaveSettingsResponseData extends ResponseData
{
    public static function schema(): array
    {
        return ApiSchemas::object(['success' => ['type' => 'boolean'], 'message' => ['type' => 'string']]);
    }
}
