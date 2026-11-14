<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\BookingRequestData;
use Wappointment\Repositories\BookingRepository;

class GetAppointmentByKeyAction
{
    public function __construct(private BookingRepository $repository) {}

    public function handle(BookingRequestData $input): array
    {
        $key = $input->text('edit_key', 32);
        if (!preg_match('/^[a-f0-9]{32}$/D', $key)) {
            throw new BookingException(__('Invalid appointment link.', 'wappointment'));
        }
        return $this->repository->appointment($key);
    }
}
