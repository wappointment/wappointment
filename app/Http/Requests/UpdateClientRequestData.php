<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Email;
use Wappointment\Http\Attributes\Length;
use Wappointment\Http\Attributes\Map;
use Wappointment\Http\Attributes\Min;
use Wappointment\System\Optional;

class UpdateClientRequestData extends RequestData
{
    public function __construct(
        #[Min(1)] public readonly int $id,
        #[Length(1, 100)] public readonly string|Optional $name = Optional::Missing,
        #[Email, Length(max: 191)] public readonly string|Optional $email = Optional::Missing,
        #[Map] public readonly array|Optional $options = Optional::Missing,
    ) {}
}
