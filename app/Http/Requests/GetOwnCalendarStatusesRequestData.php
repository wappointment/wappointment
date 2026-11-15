<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\In;

class GetOwnCalendarStatusesRequestData extends RequestData
{
    public function __construct(
        #[Min(1)] public readonly int $id,
        #[In(['self', 'admin'])] public readonly string $scope = 'self',
    ) {}
}
