<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Data\CalendarWeeklySettingsData;

class SaveOwnCalendarSettingsRequestData extends RequestData
{
    public function __construct(
        #[Min(1)] public readonly int $id,
        public readonly CalendarWeeklySettingsData $settings,
    ) {}
}
