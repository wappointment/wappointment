<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\BookingRequestData;
use Wappointment\Repositories\BookingRepository;

class ValidateBookingSelectionAction
{
    public function __construct(private BookingRepository $repository) {}

    public function handle(BookingRequestData $input): array
    {
        $catalog = $this->repository->catalog();
        $service = $this->find($catalog['services'], $input->service_id);
        $calendar = $this->find($catalog['calendars'], $input->calendar_id);
        $location = $this->find($catalog['locations'], $input->location_id);
        $duration = $input->duration;
        $timezone = $calendar['options']['timezone'] ?? 'UTC';
        if (!is_string($timezone) || (!in_array($timezone, \DateTimeZone::listIdentifiers(), true) && $timezone !== 'UTC')) {
            throw new BookingException(__('Invalid calendar timezone. Please contact the organizer.', 'wappointment'), 409);
        }
        $this->assertRelation($catalog['calendar_service'], 'calendar_id', $calendar['id'], 'service_id', $service['id']);
        $this->assertRelation($catalog['service_location'], 'service_id', $service['id'], 'location_id', $location['id']);
        $options = $service['options'];
        $durations = $options['durations'] ?? [];
        if (!in_array($duration, array_map(static fn(array $row): int => (int) $row['duration'], $durations), true)) {
            throw new BookingException(__('This duration is not available.', 'wappointment'));
        }
        if (count($durations) !== 1 || !empty($options['slots']) || !empty($options['woo_sellable'])
            || !empty($options['woo_prod_id']) || !empty($options['countries'])
            || array_diff($options['fields'] ?? [], ['name', 'email', 'phone'])
            || array_diff($location['options']['fields'] ?? [], ['name', 'email', 'phone'])) {
            throw new BookingException(__('This service uses features that have not yet been migrated to v3. Please contact the organizer.', 'wappointment'), 409);
        }
        if (!empty($calendar['options']['cal_urls']) || !empty($calendar['options']['dotcom'])
            || !empty($location['options']['countries']) || (int) $location['type'] === 5) {
            throw new BookingException(__('Calendar sync or automatic video meetings must be migrated before this calendar can accept bookings.', 'wappointment'), 409);
        }
        return [$service, $calendar, $location];
    }

    private function assertRelation(array $rows, string $left, mixed $leftId, string $right, mixed $rightId): void
    {
        foreach ($rows as $row) {
            if ((int) $row[$left] === (int) $leftId && (int) $row[$right] === (int) $rightId) {
                return;
            }
        }
        throw new BookingException(__('This service, calendar and location cannot be booked together.', 'wappointment'));
    }

    private function find(array $rows, int $id): array
    {
        foreach ($rows as $row) {
            if ((int) $row['id'] === $id) {
                return $row;
            }
        }
        throw new BookingException(__('The selected booking option is unavailable.', 'wappointment'), 404);
    }
}
