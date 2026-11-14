<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas as S;

class ListJobsResponseData extends ResponseData
{
    public static function schema(): array
    {
        $integer = ['anyOf' => [['type' => 'integer'], ['type' => 'string', 'pattern' => '^[0-9]+$']]];
        return S::pagination(S::object([
            'id' => S::id(), 'queue' => S::nullable(['type' => 'string']), 'payload' => ['type' => 'string'],
            'appointment_id' => S::nullable($integer), 'attempts' => $integer,
            'reserved_at' => S::nullable($integer), 'available_at' => $integer, 'created_at' => $integer,
        ]));
    }
}
