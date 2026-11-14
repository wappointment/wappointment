<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Data\ErrorStatusData;

class ErrorResponseData extends ResponseData
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly ErrorStatusData $data,
    ) {}
}
