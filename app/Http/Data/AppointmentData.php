<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Map;
use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\System\Data;

class AppointmentData extends Data
{
    public function __construct(
        #[Min(1), Pattern('^[1-9][0-9]*$')] public readonly int|string $id,
        public readonly int|string|null $staff_id,
        public readonly int|string|null $service_id,
        public readonly int|string|null $location_id,
        public readonly string $start_at,
        public readonly string $end_at,
        #[Pattern('^-?[0-9]+$')] public readonly int|string $status,
        public readonly ?string $client_name,
        public readonly ?string $client_email,
        public readonly ?string $service_name,
        #[Map] public readonly array $options,
    ) {}
}
