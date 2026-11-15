<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Data\OwnCalendarStatusData;

class ListOwnCalendarStatusesResponseData extends ResponseData
{
    public function __construct(
        /** @var list<OwnCalendarStatusData> */
        #[CollectionOf(OwnCalendarStatusData::class)] public readonly array $statuses,
    ) {}
}
