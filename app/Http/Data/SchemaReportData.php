<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Attributes\In;
use Wappointment\System\Data;

class SchemaReportData extends Data
{
    public function __construct(
        public readonly string $reference,
        #[In(['empty', 'compatible', 'incompatible'])] public readonly string $state,
        /** @var list<SchemaIssueData> */
        #[CollectionOf(SchemaIssueData::class)] public readonly array $issues,
        /** @var list<string> */
        #[CollectionOf('string')] public readonly array $additionalTables,
    ) {}
}
