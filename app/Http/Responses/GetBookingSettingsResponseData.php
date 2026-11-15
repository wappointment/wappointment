<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Data\BookingPageOptionData;
use Wappointment\Http\Data\BookingSettingData;
use Wappointment\Http\Data\LocationSettingData;

class GetBookingSettingsResponseData extends ResponseData
{
    public function __construct(
        public readonly ?BookingSettingData $service,
        public readonly ?BookingSettingData $calendar,
        public readonly ?LocationSettingData $location,
        #[Min(0)] public readonly int $booking_page,
        /** @var list<BookingPageOptionData> */
        #[CollectionOf(BookingPageOptionData::class)] public readonly array $booking_pages,
    ) {}
}
