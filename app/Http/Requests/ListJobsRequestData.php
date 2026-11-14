<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class ListJobsRequestData extends RequestData
{
    public static function schema(): array
    {
        return ApiSchemas::paginationRequest();
    }
}
