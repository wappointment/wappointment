<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Email;
use Wappointment\Http\Attributes\Length;
use Wappointment\Http\Attributes\Map;

class ClientCreateRequestData extends RequestData
{
    public function __construct(
        #[Length(1, 100)] public readonly string $name,
        #[Email, Length(max: 191)] public readonly string $email,
        #[Map] public readonly array $options = [],
    ) {}
}
