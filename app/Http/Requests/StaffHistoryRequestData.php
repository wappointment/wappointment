<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Min;

class StaffHistoryRequestData extends RequestData
{
    public function __construct(#[Min(1)] public readonly int $user_id) {}
}
