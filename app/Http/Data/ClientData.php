<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\Http\Responses\ResponseData;

class ClientData extends ResponseData
{
    public function __construct(
        #[Min(1), Pattern('^[1-9][0-9]*$')] public readonly int|string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $options,
        public readonly ?string $deleted_at,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
    ) {}
}
