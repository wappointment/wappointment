<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas as S;

class ListAppointmentsResponseData extends ResponseData
{
    public static function schema(): array
    {
        return S::object(['appointments' => S::list(S::object([
            'id' => S::id(), 'start_at' => ['type' => 'string'], 'end_at' => ['type' => 'string'],
            'status' => ['anyOf' => [['type' => 'integer'], ['type' => 'string', 'pattern' => '^-?[0-9]+$']]],
            'client_name' => S::nullable(['type' => 'string']),
            'client_email' => S::nullable(['type' => 'string']),
            'service_name' => S::nullable(['type' => 'string']),
            'options' => S::object([], [], true),
        ]))]);
    }
}
