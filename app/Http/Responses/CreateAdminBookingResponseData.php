<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Data\BookingData;

class CreateAdminBookingResponseData extends BookingData
{
    public function __construct(
        int $id,
        int $status,
        int $start,
        int $end,
        string $edit_key,
        public readonly bool $notification_sent,
    ) {
        parent::__construct($id, $status, $start, $end, $edit_key);
    }
}
