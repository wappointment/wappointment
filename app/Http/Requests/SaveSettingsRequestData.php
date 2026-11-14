<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class SaveSettingsRequestData extends RequestData
{
    public static function schema(): array
    {
        // Settings are a named map; preserve existing keys while rejecting unrecognized options.
        return ApiSchemas::object(ApiSchemas::settings(), [], false) + ['minProperties' => 1];
    }
}
