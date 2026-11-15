<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\In;
use Wappointment\System\Data;

class CalendarEventData extends Data
{
    public function __construct(
        public readonly string $id,
        #[In(['appointment_pending', 'appointment_confirmed', 'appointment_cancelled', 'availability_busy', 'availability_free', 'external'])]
        public readonly string $type,
        public readonly string $title,
        public readonly string $start_at,
        public readonly string $end_at,
        public readonly ?int $appointment_id = null,
    ) {}
}
