<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\System\Data;

class CatalogLocationData extends Data
{
    public function __construct(
        #[Min(1), Pattern('^[1-9][0-9]*$')] public readonly int|string $id,
        public readonly string $name,
        public readonly int $type,
        /** @var list<string> */
        #[CollectionOf('string')] public readonly array $fields,
    ) {}
}
