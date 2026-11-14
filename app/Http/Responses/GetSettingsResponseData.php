<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Data\SettingsGroupsData;

class GetSettingsResponseData extends ResponseData
{
    public function __construct(public readonly SettingsGroupsData $settings) {}
}
