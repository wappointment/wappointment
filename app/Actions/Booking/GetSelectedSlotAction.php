<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\BookingSlotData;
use Wappointment\Http\Requests\CreateBookingRequestData;
use Wappointment\System\Optional;

class GetSelectedSlotAction
{
    public function __construct(private GetAvailableSlotsAction $availability) {}

    public function handle(BookingSlotData|CreateBookingRequestData $input, int $exclude = 0, ?int $bufferOverride = null): array
    {
        $start = $input->start;
        if ($start instanceof Optional) {
            throw new \InvalidArgumentException('A booking start time is required.');
        }
        foreach ($this->availability->handle($input, $exclude, $bufferOverride)['slots'] as $slot) {
            if ($slot['start'] === $start) {
                return $slot;
            }
        }
        throw new BookingException(__('This time is no longer available. Please choose another time.', 'wappointment'), 409);
    }
}
