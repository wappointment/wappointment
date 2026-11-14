<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class UpdateClientResponseData extends ResponseData
{
    public static function schema(): array
    {
        return ApiSchemas::client();
    }
}
