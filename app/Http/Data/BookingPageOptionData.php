<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\System\Data;

class BookingPageOptionData extends Data
{
    public function __construct(
        #[Min(1)] public readonly int $id,
        public readonly string $title,
        public readonly string $url,
    ) {}
}
