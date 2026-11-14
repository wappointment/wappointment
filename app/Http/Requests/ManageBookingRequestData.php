<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Date;
use Wappointment\Http\Attributes\In;
use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\System\Optional;

class ManageBookingRequestData extends RequestData
{
    public function __construct(
        #[In(['view', 'cancel', 'reschedule'])] public readonly string $action,
        #[Pattern('^[a-f0-9]{32}$')] public readonly string $edit_key,
        #[Date] public readonly string|Optional $date = Optional::Missing,
        #[Min(1)] public readonly int|Optional $start = Optional::Missing,
    ) {}

    protected function validate(): void
    {
        $rescheduling = $this->action === 'reschedule';
        if ($rescheduling !== is_string($this->date) || $rescheduling !== is_int($this->start)) {
            throw new \InvalidArgumentException('Only rescheduling requires a date and start time.');
        }
    }
}
