<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas as S;

class GetBookingCatalogResponseData extends ResponseData
{
    public static function schema(): array
    {
        return S::object([
            'services' => S::list(S::object([
                'id' => S::id(), 'name' => ['type' => 'string'],
                'durations' => S::list(['type' => 'integer', 'minimum' => 5]),
                'fields' => S::list(['type' => 'string']),
            ])),
            'calendars' => S::list(S::object(['id' => S::id(), 'name' => ['type' => 'string'], 'timezone' => ['type' => 'string']])),
            'locations' => S::list(S::object([
                'id' => S::id(), 'name' => ['type' => 'string'], 'type' => ['type' => 'integer'],
                'fields' => S::list(['type' => 'string']),
            ])),
            'calendar_service' => S::list(S::object(['calendar_id' => S::id(), 'service_id' => S::id()])),
            'service_location' => S::list(S::object(['service_id' => S::id(), 'location_id' => S::id()])),
        ]);
    }
}
