<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\System\Data;

class OwnedCalendarSettingsData extends Data
{
    public function __construct(
        #[Min(1)] public readonly int $id,
        public readonly string $name,
        public readonly ?CalendarWeeklySettingsData $settings,
        public readonly ?string $problem,
    ) {}
}
