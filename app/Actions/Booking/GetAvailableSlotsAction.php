<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\Availability;
use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\BookingRequestData;
use Wappointment\Repositories\BookingRepository;
use Wappointment\System\Settings;

class GetAvailableSlotsAction
{
    public function __construct(
        private ValidateBookingSelectionAction $selection,
        private BookingRepository $repository,
        private Availability $availability,
        private Settings $settings
    ) {}

    public function handle(BookingRequestData $input, int $exclude = 0, ?int $bufferOverride = null): array
    {
        [$service, $calendar] = $this->selection->handle($input);
        $date = $input->text('date', 10);
        $duration = $input->integer('duration', 5, 1440);
        $buffer = (int) $this->settings->get('buffer_time', 0);
        $timezone = new \DateTimeZone($calendar['options']['timezone'] ?? 'UTC');
        $day = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone);
        if (!$day || $day->format('Y-m-d') !== $date) {
            throw new BookingException(__('Choose a valid date.', 'wappointment'));
        }
        foreach ($service['options']['durations'] as $row) {
            if ((int) $row['duration'] === $duration && isset($row['buffer_time'])) {
                $buffer = (int) $row['buffer_time'];
            }
        }
        $buffer = $bufferOverride ?? $buffer;
        [$busy, $extra] = $this->repository->intervals(
            (int) $calendar['id'], $day->getTimestamp(), $day->modify('+1 day')->getTimestamp(), $exclude
        );
        return [
            'timezone' => $timezone->getName(),
            'slots' => $this->availability->slots($date, $calendar, $duration, $buffer, $this->settings->all(), $busy, $extra, time()),
        ];
    }
}
