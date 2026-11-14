<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class CreateBookingRequestData extends BookingRequestData
{
    public static function schema(): array
    {
        return ApiSchemas::object(ApiSchemas::selection() + [
            'start' => ['type' => 'integer', 'minimum' => 1],
            'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100],
            'email' => ['type' => 'string', 'format' => 'email', 'maxLength' => 191],
            'phone' => ['type' => 'string', 'maxLength' => 40],
        ], ['service_id', 'calendar_id', 'location_id', 'duration', 'date', 'start', 'name']);
    }
}
