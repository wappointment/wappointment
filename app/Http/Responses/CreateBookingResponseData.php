<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas;

class CreateBookingResponseData extends ResponseData
{
    public static function schema(): array
    {
        $schema = ApiSchemas::booking();
        $schema['properties']['notification_sent'] = ['type' => 'boolean'];
        $schema['required'][] = 'notification_sent';
        return $schema;
    }
}
