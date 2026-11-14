<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Map;
use Wappointment\System\Data;

class SettingsGroupsData extends Data
{
    public function __construct(
        #[Map] public readonly array $general,
        #[Map] public readonly array $notifications,
        #[Map] public readonly array $advanced,
    ) {}
}
