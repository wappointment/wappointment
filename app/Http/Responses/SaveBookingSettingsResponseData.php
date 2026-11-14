<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class SaveBookingSettingsResponseData extends ResponseData
{
    public static function schema(): array
    {
        return ApiSchemas::object([
            'service_id' => ['type' => 'integer', 'minimum' => 1],
            'calendar_id' => ['type' => 'integer', 'minimum' => 1],
            'location_id' => ['type' => 'integer', 'minimum' => 1],
        ]);
    }
}
