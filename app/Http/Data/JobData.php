<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\System\Data;

class JobData extends Data
{
    public function __construct(
        #[Min(1), Pattern('^[1-9][0-9]*$')] public readonly int|string $id,
        public readonly ?string $queue,
        public readonly string $payload,
        #[Pattern('^[0-9]+$')] public readonly int|string|null $appointment_id,
        #[Pattern('^[0-9]+$')] public readonly int|string $attempts,
        #[Pattern('^[0-9]+$')] public readonly int|string|null $reserved_at,
        #[Pattern('^[0-9]+$')] public readonly int|string $available_at,
        #[Pattern('^[0-9]+$')] public readonly int|string $created_at,
    ) {}
}
