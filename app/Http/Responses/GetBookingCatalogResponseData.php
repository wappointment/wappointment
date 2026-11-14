<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Data\CalendarServiceData;
use Wappointment\Http\Data\CatalogCalendarData;
use Wappointment\Http\Data\CatalogLocationData;
use Wappointment\Http\Data\CatalogServiceData;
use Wappointment\Http\Data\ServiceLocationData;

class GetBookingCatalogResponseData extends ResponseData
{
    public function __construct(
        /** @var list<CatalogServiceData> */
        #[CollectionOf(CatalogServiceData::class)] public readonly array $services,
        /** @var list<CatalogCalendarData> */
        #[CollectionOf(CatalogCalendarData::class)] public readonly array $calendars,
        /** @var list<CatalogLocationData> */
        #[CollectionOf(CatalogLocationData::class)] public readonly array $locations,
        /** @var list<CalendarServiceData> */
        #[CollectionOf(CalendarServiceData::class)] public readonly array $calendar_service,
        /** @var list<ServiceLocationData> */
        #[CollectionOf(ServiceLocationData::class)] public readonly array $service_location,
    ) {}
}
