<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Http\Requests\BookingRequestData;
use Wappointment\Http\Requests\BookingSlotData;
use Wappointment\Repositories\BookingRepository;

class GetRescheduleInputAction
{
    public function __construct(private BookingRepository $repository) {}

    public function handle(array $appointment, BookingRequestData $input): BookingRequestData
    {
        $buffer = (int) ($appointment['options']['buffer_time'] ?? 0);
        $duration = (int) (($this->repository->timestamp($appointment['end_at']) - $this->repository->timestamp($appointment['start_at'])) / 60) - $buffer;
        $selection = [
            'service_id' => (int) $appointment['service_id'], 'calendar_id' => (int) $appointment['staff_id'],
            'location_id' => (int) $appointment['location_id'], 'duration' => $duration,
            'date' => $input->toArray()['date'],
        ];
        if (isset($input->toArray()['start'])) {
            $selection['start'] = $input->toArray()['start'];
        }
        return BookingSlotData::from($selection);
    }
}
