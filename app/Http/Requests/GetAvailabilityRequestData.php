<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Date;
use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\System\Optional;

class GetAvailabilityRequestData extends RequestData
{
    public function __construct(
        #[Date] public readonly string $date,
        #[Min(1)] public readonly int|Optional $service_id = Optional::Missing,
        #[Min(1)] public readonly int|Optional $calendar_id = Optional::Missing,
        #[Min(1)] public readonly int|Optional $location_id = Optional::Missing,
        #[Min(5), Max(1440)] public readonly int|Optional $duration = Optional::Missing,
        #[Pattern('^[a-f0-9]{32}$')] public readonly string|Optional $edit_key = Optional::Missing,
    ) {}

    protected function validate(): void
    {
        $selection = [$this->service_id, $this->calendar_id, $this->location_id, $this->duration];
        $selectionCount = count(array_filter($selection, static fn(int|Optional $value): bool => !$value instanceof Optional));
        $hasEditKey = is_string($this->edit_key);
        if (($hasEditKey && $selectionCount !== 0) || (!$hasEditKey && $selectionCount !== count($selection))) {
            throw new \InvalidArgumentException('Supply either the complete booking selection or an edit_key, not both.');
        }
    }
}
