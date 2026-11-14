<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class BookingSlotData extends BookingRequestData
{
    public static function schema(): array
    {
        return ApiSchemas::object(ApiSchemas::selection() + ['start' => ['type' => 'integer', 'minimum' => 1]], array_keys(ApiSchemas::selection()));
    }
}
