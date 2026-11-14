<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class DeleteClientResponseData extends ResponseData
{
    public static function schema(): array
    {
        return ApiSchemas::object(['success' => ['type' => 'boolean'], 'message' => ['type' => 'string']]);
    }
}
