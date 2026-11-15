<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\System\Data;

class HistoryAppointmentData extends Data
{
    public function __construct(
        #[Min(1)] public readonly int $id,
        public readonly string $start_at,
        public readonly string $end_at,
        public readonly int|float $duration_minutes,
        public readonly int $status,
        public readonly string $edit_key,
        public readonly string $client_name,
        public readonly string $client_email,
        public readonly string $client_phone,
        public readonly string $customer_timezone,
        public readonly string $service_name,
        public readonly string $calendar_name,
        public readonly string $calendar_timezone,
        public readonly string $location_name,
    ) {}
}
