<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Data\OwnedCalendarSettingsData;

class SaveOwnCalendarSettingsResponseData extends ResponseData
{
    public function __construct(public readonly OwnedCalendarSettingsData $calendar) {}
}
