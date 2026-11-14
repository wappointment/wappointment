<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

class SaveSettingsResponseData extends ResponseData
{
    public function __construct(public readonly bool $success, public readonly string $message) {}
}
