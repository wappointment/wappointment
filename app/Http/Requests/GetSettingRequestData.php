<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Length;
use Wappointment\Http\Attributes\Pattern;

class GetSettingRequestData extends RequestData
{
    public function __construct(
        #[Length(1, 100), Pattern('^[a-zA-Z0-9_-]+$')] public readonly string $name,
    ) {}
}
