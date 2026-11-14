<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\ApiSchemas as S;

class GetSettingsResponseData extends ResponseData
{
    public static function schema(): array
    {
        // Persisted settings can include older extension-owned keys; each group is an explicit map.
        $map = S::object([], [], true);
        return S::object(['settings' => S::object(['general' => $map, 'notifications' => $map, 'advanced' => $map])]);
    }
}
