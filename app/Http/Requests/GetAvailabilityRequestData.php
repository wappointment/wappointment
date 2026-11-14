<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class GetAvailabilityRequestData extends BookingRequestData
{
    public static function schema(): array
    {
        $properties = ApiSchemas::selection();
        return ['anyOf' => [
            ApiSchemas::object($properties),
            ApiSchemas::object(['date' => $properties['date'], 'edit_key' => ApiSchemas::editKey()]),
        ]];
    }
}
