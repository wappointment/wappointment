<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\Min;

class SaveBookingSettingsResponseData extends ResponseData
{
    public function __construct(
        #[Min(1)] public readonly int $service_id,
        #[Min(1)] public readonly int $calendar_id,
        #[Min(1)] public readonly int $location_id,
    ) {}
}
