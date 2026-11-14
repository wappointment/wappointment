<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class ListClientsResponseData extends ResponseData
{
    public static function schema(): array
    {
        return ApiSchemas::pagination(ApiSchemas::client());
    }
}
