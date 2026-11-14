<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Http\Requests\BookingSlotData;
use Wappointment\Repositories\BookingRepository;
use Wappointment\System\Optional;

class GetRescheduleInputAction
{
    public function __construct(private BookingRepository $repository) {}

    public function handle(array $appointment, string $date, int|Optional $start = Optional::Missing): BookingSlotData
    {
        $buffer = (int) ($appointment['options']['buffer_time'] ?? 0);
        $duration = (int) (($this->repository->timestamp($appointment['end_at']) - $this->repository->timestamp($appointment['start_at'])) / 60) - $buffer;
        $selection = [
            'service_id' => (int) $appointment['service_id'], 'calendar_id' => (int) $appointment['staff_id'],
            'location_id' => (int) $appointment['location_id'], 'duration' => $duration,
            'date' => $date,
        ];
        if (!$start instanceof Optional) {
            $selection['start'] = $start;
        }
        return BookingSlotData::from($selection);
    }
}
