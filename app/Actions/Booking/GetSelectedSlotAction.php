<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\BookingRequestData;

class GetSelectedSlotAction
{
    public function __construct(private GetAvailableSlotsAction $availability) {}

    public function handle(BookingRequestData $input, int $exclude = 0, ?int $bufferOverride = null): array
    {
        $start = $input->integer('start', 1);
        foreach ($this->availability->handle($input, $exclude, $bufferOverride)['slots'] as $slot) {
            if ($slot['start'] === $start) {
                return $slot;
            }
        }
        throw new BookingException(__('This time is no longer available. Please choose another time.', 'wappointment'), 409);
    }
}
