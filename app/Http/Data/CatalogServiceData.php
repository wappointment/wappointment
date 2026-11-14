<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\System\Data;

class CatalogServiceData extends Data
{
    public function __construct(
        #[Min(1), Pattern('^[1-9][0-9]*$')] public readonly int|string $id,
        public readonly string $name,
        /** @var list<int> */
        #[CollectionOf('int', min: 5)] public readonly array $durations,
        /** @var list<string> */
        #[CollectionOf('string')] public readonly array $fields,
    ) {}
}
