<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class UpdateAppointmentStatusRequestData extends RequestData
{
    public static function schema(): array
    {
        return ApiSchemas::object([
            'id' => ['type' => 'integer', 'minimum' => 1],
            'status' => ['type' => 'integer', 'enum' => [-1, 1]],
        ]);
    }
}
