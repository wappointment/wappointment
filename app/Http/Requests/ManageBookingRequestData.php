<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class ManageBookingRequestData extends BookingRequestData
{
    public static function schema(): array
    {
        return ['anyOf' => [
            ApiSchemas::object(['action' => ['type' => 'string', 'enum' => ['view', 'cancel']], 'edit_key' => ApiSchemas::editKey()]),
            ApiSchemas::object([
                'action' => ['type' => 'string', 'enum' => ['reschedule']], 'edit_key' => ApiSchemas::editKey(),
                'date' => ['type' => 'string', 'format' => 'date'], 'start' => ['type' => 'integer', 'minimum' => 1],
            ]),
        ]];
    }
}
