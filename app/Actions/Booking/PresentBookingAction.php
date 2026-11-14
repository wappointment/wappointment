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
        return [
            'id' => (int) $appointment['id'], 'status' => (int) $appointment['status'],
            'start' => $this->repository->timestamp($appointment['start_at']),
            'end' => $this->repository->timestamp($appointment['end_at']) - (int) ($options['buffer_time'] ?? 0) * 60,
            'edit_key' => $appointment['edit_key'],
        ];
    }
}
