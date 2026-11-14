<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

class GetSettingResponseData extends ResponseData
{
    public function __construct(public readonly string $name, public readonly mixed $value) {}
}
