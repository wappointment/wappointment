<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Data\AvailableSlotData;

class GetAvailabilityResponseData extends ResponseData
{
    /** @param list<AvailableSlotData> $slots */
    public function __construct(
        public readonly string $timezone,
        #[CollectionOf(AvailableSlotData::class)] public readonly array $slots,
    ) {}
}
