<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Repositories\BookingRepository;

class UpdateAppointmentStatusAction
{
    public function __construct(private BookingRepository $repository) {}

    public function handle(array $data): array
    {
        if (!in_array($data['status'] ?? null, [-1, 1], true)
            || filter_var($data['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new BookingException(__('Supply an appointment ID and a valid status.', 'wappointment'));
        }
        $this->repository->setStatus((int) $data['id'], $data['status']);
        return ['updated' => true];
    }
}
