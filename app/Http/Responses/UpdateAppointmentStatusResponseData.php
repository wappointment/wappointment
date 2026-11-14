<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class UpdateAppointmentStatusResponseData extends ResponseData
{
    public static function schema(): array
    {
        return ApiSchemas::object(['updated' => ['type' => 'boolean', 'enum' => [true]]]);
    }
}
