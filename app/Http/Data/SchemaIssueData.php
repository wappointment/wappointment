<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\In;
use Wappointment\System\Data;

class SchemaIssueData extends Data
{
    public function __construct(
        public readonly string $table,
        #[In(['missing_table', 'engine', 'column', 'index', 'foreign_key'])]
        public readonly string $kind,
        public readonly string $member,
        public readonly string $expected,
        public readonly string $actual,
    ) {}
}
