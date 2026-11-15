<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Email;
use Wappointment\System\Data;

class BookingCustomerData extends Data
{
    public function __construct(
        public readonly string $name,
        #[Email] public readonly string $email,
        public readonly string $phone,
        public readonly bool $force_email,
    ) {}
}
