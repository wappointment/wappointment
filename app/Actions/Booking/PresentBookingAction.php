<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Repositories\BookingRepository;

class PresentBookingAction
{
    public function __construct(private BookingRepository $repository) {}

    public function handle(array $appointment): array
    {
        $options = is_array($appointment['options']) ? $appointment['options'] : json_decode($appointment['options'], true, 512, JSON_THROW_ON_ERROR);
        $buffer = (int) ($options['buffer_time'] ?? 0);
        return [
            'id' => (int) $appointment['id'], 'status' => (int) $appointment['status'],
            'start' => $this->repository->timestamp($appointment['start_at']),
            'end' => $this->repository->timestamp($appointment['end_at']) - $buffer * 60,
            'edit_key' => $appointment['edit_key'],
            'service_id' => (int) $appointment['service_id'],
            'calendar_id' => (int) $appointment['staff_id'],
            'location_id' => (int) $appointment['location_id'],
            'duration' => (int) (($this->repository->timestamp($appointment['end_at']) -
                $this->repository->timestamp($appointment['start_at'])) / 60) - $buffer,
        ];
    }
}
