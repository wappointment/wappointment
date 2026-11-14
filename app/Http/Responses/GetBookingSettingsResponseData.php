<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas as S;

class GetBookingSettingsResponseData extends ResponseData
{
    public static function schema(): array
    {
        $record = [
            'id' => S::id(), 'name' => ['type' => 'string'],
            'options' => S::object([], [], true),
        ];
        $integer = ['anyOf' => [['type' => 'integer'], ['type' => 'string', 'pattern' => '^[0-9]+$']]];
        return S::object([
            'service' => S::nullable(S::object($record)),
            'calendar' => S::nullable(S::object($record)),
            'location' => S::nullable(S::object($record + ['type' => $integer])),
            'booking_page' => ['type' => 'integer', 'minimum' => 0],
        ]);
    }
}
