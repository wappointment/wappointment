<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Data\OwnedCalendarSettingsData;

class GetOwnCalendarSettingsResponseData extends ResponseData
{
    public function __construct(
        /** @var list<OwnedCalendarSettingsData> */
        #[CollectionOf(OwnedCalendarSettingsData::class)] public readonly array $calendars,
    ) {}
}
