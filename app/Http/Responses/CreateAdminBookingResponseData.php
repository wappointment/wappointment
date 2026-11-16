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
        int $service_id,
        int $calendar_id,
        int $location_id,
        int $duration,
        public readonly bool $notification_queued,
    ) {
        parent::__construct($id, $status, $start, $end, $edit_key, $service_id, $calendar_id, $location_id, $duration);
    }
}
