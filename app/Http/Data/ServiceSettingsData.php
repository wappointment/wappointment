<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Attributes\Map;
use Wappointment\Http\Attributes\Min;
use Wappointment\System\Data;
use Wappointment\System\Optional;

class ServiceSettingsData extends Data
{
    public function __construct(
        public readonly string|Optional $name = Optional::Missing,
        #[Min(5)] public readonly int|Optional $duration = Optional::Missing,
        #[CollectionOf('string')] public readonly string|array|Optional $type = Optional::Missing,
        public readonly string|Optional $address = Optional::Missing,
        #[Map] public readonly array|Optional $options = Optional::Missing,
    ) {}
}
