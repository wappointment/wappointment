<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Repositories\BookingRepository;
use Wappointment\System\Settings;

class AssertBookingChangeAllowedAction
{
    public function __construct(private BookingRepository $repository, private Settings $settings) {}

    public function handle(array $appointment, string $action): void
    {
        if ((int) $appointment['status'] < 0 || !empty($appointment['recurrent']) || !empty($appointment['parent'])) {
            throw new BookingException(__('This appointment cannot be changed online.', 'wappointment'), 409);
        }
        $suffix = $action === 'cancel' ? 'cancellation' : 'rescheduling';
        $deadline = $this->repository->timestamp($appointment['start_at'])
            - (int) ((float) $this->settings->get('hours_before_' . $suffix . '_allowed', 24) * 3600);
        $pendingCancellation = $action === 'cancel' && (int) $appointment['status'] === 0;
        if (!$this->settings->get('allow_' . $suffix) || (!$pendingCancellation && time() >= $deadline)) {
            throw new BookingException(__('The time limit for changing this appointment has passed or changes are disabled.', 'wappointment'), 403);
        }
    }
}
