<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Data\SchemaReportData;

class GetDatabaseSchemaResponseData extends ResponseData
{
    public function __construct(public readonly SchemaReportData $report) {}
}
