<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class SaveBookingSettingsRequestData extends RequestData
{
    public static function schema(): array
    {
        $day = ApiSchemas::list(['type' => 'array', 'minItems' => 2, 'maxItems' => 2, 'items' => [
            'type' => 'integer', 'minimum' => 0, 'maximum' => 1440,
        ]]);
        $day['maxItems'] = 24;
        $properties = [
            'service_name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'calendar_name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'location_name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'location_type' => ['type' => 'integer', 'enum' => [1, 2, 4]],
            'duration' => ['type' => 'integer', 'minimum' => 5, 'maximum' => 1440],
            'booking_days' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 365],
            'timezone' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'phone_required' => ['type' => 'boolean'],
            'regav' => ApiSchemas::object(array_fill_keys(
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], $day
            )),
            'booking_page' => ['type' => 'integer', 'minimum' => 0],
        ];
        return ApiSchemas::object($properties, array_diff(array_keys($properties), ['booking_page']));
    }
}
