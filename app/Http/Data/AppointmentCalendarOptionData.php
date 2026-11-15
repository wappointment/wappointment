<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\Http\Attributes\Length;
use Wappointment\System\Data;

class AppointmentCalendarOptionData extends Data
{
    public function __construct(
        #[Min(1), Pattern('^[1-9][0-9]*$')] public readonly int|string $id,
        public readonly string $name,
        #[Length(max: 64)] public readonly string $timezone = 'UTC',
    ) {}
}
