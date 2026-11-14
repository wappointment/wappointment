<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;

class LocationSettingData extends BookingSettingData
{
    public function __construct(
        int|string $id,
        string $name,
        array $options,
        #[Min(0), Pattern('^[0-9]+$')] public readonly int|string $type,
    ) {
        parent::__construct($id, $name, $options);
    }
}
